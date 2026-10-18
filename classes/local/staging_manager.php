<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace local_bulkinstall\local;

use core\plugin_manager;

/**
 * Owns temporary uploaded packages for the current administrator session.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
class staging_manager {
    /** Optional Plugin Manager handover contract; ordinary uploads have no Manager dependency. */
    public const UPDATE_API_VERSION = 1;
    /** Maximum packages per batch. */
    public const MAX_PACKAGES = 50;

    /** Maximum uncompressed bytes across a batch (512 MiB). */
    public const MAX_BATCH_UNCOMPRESSED_BYTES = 536870912;

    /** Expire abandoned staging directories after 24 hours. */
    public const MAX_AGE = 86400;

    /** Session property used for ownership and integrity metadata. */
    private const SESSION_KEY = 'local_bulkinstall_staging';

    /** Raw inspections reused only while computing one exclusion preview, never across confirmations. */
    private ?array $previewinspections = null;

    /**
     * Copy draft files to a request-independent staging directory.
     *
     * @param \stored_file[] $files
     * @return string Storage id
     */
    public function stage(array $files): string {
        global $SESSION;

        $files = array_values(array_filter($files, static fn($file) => !$file->is_directory()));
        if (empty($files)) {
            throw new \moodle_exception('errornofiles', 'local_bulkinstall');
        }
        if (count($files) > self::MAX_PACKAGES) {
            throw new \moodle_exception('errortoomanypackages', 'local_bulkinstall', '', self::MAX_PACKAGES);
        }

        do {
            $storageid = bin2hex(random_bytes(16));
            $directory = $this->base_directory() . DIRECTORY_SEPARATOR . $storageid;
        } while (file_exists($directory));
        if (!make_writable_directory($directory, false)) {
            throw new \moodle_exception('errorcreatestaging', 'local_bulkinstall');
        }

        $record = ['created' => time(), 'mode' => 'packages', 'bundle' => null, 'files' => []];
        $uploads = [];
        try {
            foreach ($files as $index => $file) {
                $storedname = sprintf('upload-%03d.zip', $index + 1);
                $path = $directory . DIRECTORY_SEPARATOR . $storedname;
                if (!$file->copy_content_to($path)) {
                    throw new \moodle_exception('errorcopystagedfile', 'local_bulkinstall', '', $file->get_filename());
                }
                $uploads[] = [
                    'storedname' => $storedname,
                    'filename' => $file->get_filename(),
                    'sha256' => hash_file('sha256', $path),
                    'bytes' => (int)$file->get_filesize(),
                    'expectedcomponent' => null,
                ];
            }

            $bundlereader = new bundle_reader();
            $bundleuploads = array_values(array_filter($uploads, function(array $upload) use ($bundlereader, $directory): bool {
                return $bundlereader->is_bundle($directory . DIRECTORY_SEPARATOR . $upload['storedname']);
            }));
            if ($bundleuploads) {
                if (count($uploads) !== 1) {
                    throw new \moodle_exception('errorbundlemixedupload', 'local_bulkinstall');
                }
                $outerpath = $directory . DIRECTORY_SEPARATOR . $uploads[0]['storedname'];
                $expanded = $bundlereader->expand($outerpath, $directory, $uploads[0]['filename']);
                $record['mode'] = 'bundle';
                $record['bundle'] = $expanded['bundle'];
                $record['files'] = $expanded['files'];
                unlink($outerpath);
            } else {
                $record['files'] = $uploads;
            }
        } catch (\Throwable $exception) {
            remove_dir($directory);
            throw $exception;
        }

        if (!isset($SESSION->{self::SESSION_KEY}) || !is_array($SESSION->{self::SESSION_KEY})) {
            $SESSION->{self::SESSION_KEY} = [];
        }
        $SESSION->{self::SESSION_KEY}[$storageid] = $record;
        return $storageid;
    }

    /**
     * Inspect and cross-check every package in a staged batch.
     *
     * @param string $storageid
     * @param bool $overridecompatibility Ignore only declared supported-range errors; private service use only
     * @param array|null $selection Internal preview subset of this session's original staged file identifiers
     * @return array
     */
    public function analyse(string $storageid, bool $overridecompatibility = false, ?array $selection = null): array {
        $record = $this->get_record($storageid);
        $selection = $selection ?? $record['selection'] ?? array_column($record['files'], 'storedname');
        if (array_diff($selection, array_column($record['files'], 'storedname'))
                || count(array_unique($selection)) !== count($selection)) {
            throw new \invalid_parameter_exception('Unknown staged selection');
        }
        $selectedfiles = array_values(array_filter($record['files'],
            static fn(array $file): bool => in_array($file['storedname'], $selection, true)));
        $directory = $this->directory($storageid);
        $inspector = new package_inspector();
        $packages = [];
        $totaluncompressed = 0;
        $prooferrors = [];
        $verifiedreleases = [];
        if (isset($record['updateproof'])) {
            try {
                $verifiedreleases = $this->verify_update_proof($record['updateproof'], $record['files']);
            } catch (\Throwable $exception) {
                $prooferrors[] = get_string('errorupdateproof', 'local_bulkinstall');
            }
        }

        foreach ($selectedfiles as $file) {
            $path = $directory . DIRECTORY_SEPARATOR . $file['storedname'];
            if ($this->previewinspections !== null) {
                $this->previewinspections[$path] ??= $inspector->inspect($path, $file['filename']);
                $package = $this->previewinspections[$path];
            } else {
                $package = $inspector->inspect($path, $file['filename']);
            }
            if (!is_file($path) || !hash_equals($file['sha256'], (string)hash_file('sha256', $path))) {
                $package['errors'][] = get_string('errorintegritychanged', 'local_bulkinstall');
            }
            $package['storedname'] = $file['storedname'];
            $package['sha256'] = $file['sha256'];
            $package['expectedcomponent'] = $file['expectedcomponent'] ?? null;
            // Only a freshly verified catalogue can supply the selected display label.
            if (isset($verifiedreleases[$package['component'] ?? ''])
                    && $verifiedreleases[$package['component']]['version'] === $package['version']) {
                $package['selectedrelease'] = $verifiedreleases[$package['component']]['label'];
                // A reduced handover must satisfy both the ZIP and the signed catalogue dependencies.
                foreach ($verifiedreleases[$package['component']]['dependencies'] as $component => $minimum) {
                    $package['dependencies'][$component] = max($minimum, $package['dependencies'][$component] ?? 0);
                }
            }
            if ($package['expectedcomponent'] !== null && $package['component'] !== null &&
                    $package['component'] !== $package['expectedcomponent']) {
                $this->add_error($package, get_string('errorbundlecomponentmismatch', 'local_bulkinstall', (object)[
                    'file' => $file['filename'],
                    'expected' => $package['expectedcomponent'],
                    'actual' => $package['component'],
                ]));
            }
            if (isset($file['expectedversion']) && $package['version'] !== $file['expectedversion']) {
                $this->add_error($package, get_string('errorupdateversion', 'local_bulkinstall'));
            }
            if (isset($record['updateproof']) && $package['action'] !== 'update') {
                $this->add_error($package, get_string('errorupdatechanged', 'local_bulkinstall'));
            }
            if ($overridecompatibility && !empty($package['compatibilityerrors'])) {
                $package['errors'] = array_values(array_diff(
                    $package['errors'],
                    $package['compatibilityerrors']
                ));
            }
            $totaluncompressed += $package['uncompressedbytes'];
            $packages[] = $package;
        }

        $batcherrors = $prooferrors;
        if (!$selectedfiles) {
            $batcherrors[] = get_string('selectionempty', 'local_bulkinstall');
        }
        if ($totaluncompressed > self::MAX_BATCH_UNCOMPRESSED_BYTES) {
            $batcherrors[] = get_string(
                'errorbatchuncompressedlimit',
                'local_bulkinstall',
                display_size(self::MAX_BATCH_UNCOMPRESSED_BYTES)
            );
        }

        $bycomponent = [];
        foreach ($packages as $index => $package) {
            if (!empty($package['component'])) {
                $bycomponent[$package['component']][] = $index;
            }
        }

        $pluginmanager = plugin_manager::instance();
        $activation = $record['bundle']['activation'] ?? null;
        $partial = count($selectedfiles) !== count($record['files']);
        if ($partial && is_array($activation)) {
            // Keep the original signed manifest intact. Derive only the effective coordination hints.
            $activation['entitlements'] = array_values(array_filter($activation['entitlements'],
                static fn(array $entitlement): bool => !array_diff($entitlement['components'], array_keys($bycomponent))));
            if (!$activation['entitlements']) {
                $activation = null;
            }
        }
        if (is_array($activation) && !empty($activation['licencemanager']['required'])) {
            $required = (int) ($activation['licencemanager']['minimumversion'] ?? 0);
            $available = null;
            if (isset($bycomponent['local_lmh108']) && count($bycomponent['local_lmh108']) === 1) {
                $available = (int) $packages[$bycomponent['local_lmh108'][0]]['version'];
            } else {
                $info = $pluginmanager->get_plugin_info('local_lmh108');
                if ($info !== null && $info->versiondb !== null) {
                    $available = (int) $info->versiondb;
                }
            }
            if ($partial && ($available === null || $available < $required)) {
                // A partial code-only installation must not queue an unusable activation journey.
                $activation = null;
            } else if ($available === null) {
                $batcherrors[] = get_string('errorbundlelmhmissing', 'local_bulkinstall');
            } else if ($available < $required) {
                $batcherrors[] = get_string('errorbundlelmhversion', 'local_bulkinstall', (object) [
                    'required' => $required,
                    'available' => $available,
                ]);
            }
        }
        foreach ($bycomponent as $component => $indexes) {
            if (count($indexes) > 1) {
                foreach ($indexes as $index) {
                    $this->add_error(
                        $packages[$index],
                        get_string('errorduplicatecomponent', 'local_bulkinstall', $component)
                    );
                }
            }
        }

        foreach ($packages as $index => $package) {
            foreach ($package['dependencies'] as $dependency => $requiredversion) {
                $availableversion = null;
                $source = null;
                if (isset($bycomponent[$dependency]) && count($bycomponent[$dependency]) === 1) {
                    $dependencypackage = $packages[$bycomponent[$dependency][0]];
                    $availableversion = $dependencypackage['version'];
                    $source = get_string('dependencyinbatch', 'local_bulkinstall');
                } else {
                    $dependencyinfo = $pluginmanager->get_plugin_info($dependency);
                    if ($dependencyinfo !== null && $dependencyinfo->versiondb !== null) {
                        $availableversion = (int)$dependencyinfo->versiondb;
                        $source = get_string('dependencyinstalled', 'local_bulkinstall');
                    }
                }
                if ($availableversion === null) {
                    $this->add_error($packages[$index], get_string(
                        'errormissingdependency',
                        'local_bulkinstall',
                        $dependency
                    ));
                } else if ($requiredversion > 0 && $availableversion < $requiredversion) {
                    $this->add_error($packages[$index], get_string(
                        'errordependencyversion',
                        'local_bulkinstall',
                        (object)[
                            'component' => $dependency,
                            'required' => $requiredversion,
                            'available' => $availableversion,
                            'source' => $source,
                        ]
                    ));
                }
            }
        }

        $canoverridecompatibility = empty($batcherrors);
        $hascompatibilityerror = false;
        foreach ($packages as $package) {
            if (!empty($package['compatibilityerrors'])) {
                $hascompatibilityerror = true;
            }
            if (!empty(array_diff($package['errors'], $package['compatibilityerrors']))) {
                $canoverridecompatibility = false;
            }
        }
        $canoverridecompatibility = $canoverridecompatibility && $hascompatibilityerror;

        $caninstall = empty($batcherrors);
        foreach ($packages as $package) {
            if (!empty($package['errors'])) {
                $caninstall = false;
                break;
            }
        }

        // Re-resolve the exact reduced signed selection: no implicit re-addition of excluded updates.
        if ($caninstall && isset($record['updateproof']) && $partial) {
            try {
                $this->verify_update_proof($record['updateproof'], $selectedfiles);
            } catch (\Throwable $exception) {
                $batcherrors[] = get_string('errorupdateproof', 'local_bulkinstall');
                $caninstall = false;
            }
        }

        return [
            'mode' => $record['mode'] ?? 'packages',
            'bundle' => $record['bundle'] ?? null,
            'updates' => isset($record['updateproof']),
            'packages' => $packages,
            'batcherrors' => $batcherrors,
            'totaluncompressed' => $totaluncompressed,
            'caninstall' => $caninstall,
            'canoverridecompatibility' => $canoverridecompatibility,
            'activation' => $activation,
            'partial' => $partial,
            'excluded' => array_values(array_filter($record['files'],
                static fn(array $file): bool => !in_array($file['storedname'], $selection, true))),
            'selection' => array_column($selectedfiles, 'storedname'),
            'revision' => (int)($record['revision'] ?? 0),
        ];
    }

    /** Bind a confirmation to the actual reviewed selection and its consequential check results. */
    public function review_token(array $analysis): string {
        $packages = array_map(static fn(array $package): array => array_intersect_key($package, array_flip([
            'storedname', 'sha256', 'component', 'version', 'installedversion', 'action', 'dependencies', 'errors', 'warnings',
        ])), $analysis['packages']);
        return hash('sha256', json_encode([$analysis['revision'], $analysis['selection'], $packages,
            $analysis['batcherrors'], $analysis['activation']], JSON_THROW_ON_ERROR));
    }

    /** Preview a fixed point: removing blockers can expose further missing/too-old dependencies. */
    public function exclusion_plan(string $storageid): array {
        $this->previewinspections = [];
        try {
            return $this->compute_exclusion_plan($storageid);
        } finally {
            $this->previewinspections = null;
        }
    }

    /** Resolve dependencies repeatedly without extracting the same ZIP again in this preview. */
    private function compute_exclusion_plan(string $storageid): array {
        $before = $this->analyse($storageid);
        $analysis = $before;
        $removed = [];
        while (empty($analysis['batcherrors'])) {
            $blocked = array_values(array_filter($analysis['packages'], static fn(array $package): bool =>
                !empty($package['errors'])));
            if (!$blocked) {
                break;
            }
            foreach ($blocked as $package) {
                $removed[] = $package;
            }
            $selection = array_values(array_diff($analysis['selection'], array_column($blocked, 'storedname')));
            $analysis = $this->analyse($storageid, false, $selection);
        }
        $token = hash('sha256', $this->review_token($before) . $this->review_token($analysis)
            . json_encode(array_column($removed, 'errors'), JSON_THROW_ON_ERROR));
        return ['analysis' => $analysis, 'removed' => $removed, 'token' => $token,
            'canapply' => !empty($removed) && $analysis['caninstall']];
    }

    /** Apply only a freshly recomputed, explicitly confirmed plan owned by this session. */
    public function exclude_blocked(string $storageid, string $token): void {
        global $SESSION;
        $plan = $this->exclusion_plan($storageid);
        if (!$plan['canapply'] || !hash_equals($plan['token'], $token)) {
            throw new \moodle_exception('selectionchanged', 'local_bulkinstall');
        }
        $record = $this->get_record($storageid);
        $record['selection'] = $plan['analysis']['selection'];
        $record['revision'] = (int)($record['revision'] ?? 0) + 1;
        $SESSION->{self::SESSION_KEY}[$storageid] = $record;
    }

    /**
     * Convert a successful analysis into the structure expected by Moodle core.
     *
     * @param array $analysis
     * @return array
     */
    public function installables(array $analysis): array {
        if (empty($analysis['caninstall'])) {
            throw new \coding_exception('A failed batch cannot be converted to installables.');
        }
        $installables = [];
        foreach ($analysis['packages'] as $package) {
            $installables[] = (object)[
                'component' => $package['component'],
                'zipfilepath' => $package['path'],
            ];
        }
        return $installables;
    }

    /**
     * Return staged record owned by this session.
     *
     * @param string $storageid
     * @return array
     */
    public function get_record(string $storageid): array {
        global $SESSION;

        $this->validate_storage_id($storageid);
        if (!isset($SESSION->{self::SESSION_KEY}[$storageid])) {
            throw new \moodle_exception('errorstorageexpired', 'local_bulkinstall');
        }
        $record = $SESSION->{self::SESSION_KEY}[$storageid];
        if (!is_array($record) || empty($record['files']) || !is_dir($this->directory($storageid))) {
            throw new \moodle_exception('errorstorageexpired', 'local_bulkinstall');
        }
        if (time() - (int)$record['created'] >= self::MAX_AGE) {
            $this->cleanup($storageid);
            throw new \moodle_exception('errorstorageexpired', 'local_bulkinstall');
        }
        return $record;
    }

    /**
     * Receive downloaded ordinary ZIPs and retain their signed catalogue provenance.
     * This does not create a bundle, activate products, deploy code or write application tables.
     */
    public function stage_verified_updates(array $files, string $cataloguejson, string $fingerprint): string {
        global $SESSION;
        require_capability('moodle/site:config', \context_system::instance());
        if (!$files || count($files) > self::MAX_PACKAGES) {
            throw new \moodle_exception('errorupdateproof', 'local_bulkinstall');
        }
        $entries = [];
        foreach ($files as $index => $file) {
            if (!is_string($file['component'] ?? null) || !is_int($file['version'] ?? null)
                    || !is_string($file['path'] ?? null) || !is_file($file['path']) || is_link($file['path'])) {
                throw new \moodle_exception('errorupdateproof', 'local_bulkinstall');
            }
            $entries[] = ['storedname' => sprintf('update-%03d.zip', $index + 1),
                'filename' => $file['component'] . '-' . $file['version'] . '.zip',
                'expectedcomponent' => $file['component'], 'expectedversion' => $file['version'],
                'sourcepath' => $file['path']];
        }
        $proof = ['catalogue' => $cataloguejson, 'fingerprint' => $fingerprint];
        $releases = $this->verify_update_proof($proof, $entries);
        $storageid = bin2hex(random_bytes(16));
        $directory = $this->directory($storageid);
        if (!make_writable_directory($directory, false)) {
            throw new \moodle_exception('errorcreatestaging', 'local_bulkinstall');
        }
        try {
            foreach ($entries as &$entry) {
                $path = $directory . '/' . $entry['storedname'];
                if (!copy($entry['sourcepath'], $path)) {
                    throw new \moodle_exception('errorcopystagedfile', 'local_bulkinstall', '', $entry['filename']);
                }
                unset($entry['sourcepath']);
                $entry['sha256'] = $releases[$entry['expectedcomponent']]['sha256'];
                if (!hash_equals($entry['sha256'], (string)hash_file('sha256', $path))) {
                    throw new \moodle_exception('errorupdateproof', 'local_bulkinstall');
                }
                $entry['bytes'] = (int)filesize($path);
            }
            unset($entry);
            $SESSION->{self::SESSION_KEY}[$storageid] = ['created' => time(), 'mode' => 'updates',
                'bundle' => null, 'updateproof' => $proof, 'files' => $entries];
            return $storageid;
        } catch (\Throwable $exception) {
            remove_dir($directory);
            throw $exception;
        }
    }

    /** The optional Manager is required only for catalogue-based staging, never for uploads/bundles. */
    private function verify_update_proof(array $proof, array $files): array {
        global $CFG;
        if (!class_exists('\\local_lmh108\\local\\update_catalogue')
                || !class_exists('\\local_lmh108\\local\\update_plan')) {
            throw new \moodle_exception('errorupdateproof', 'local_bulkinstall');
        }
        $catalogue = \local_lmh108\local\update_catalogue::verify($proof['catalogue'], time(),
            (int)get_config('local_lmh108', 'updatepublished'));
        $snapshot = \local_lmh108\local\update_plan::snapshot();
        if (!hash_equals($proof['fingerprint'], \local_lmh108\local\update_plan::fingerprint($snapshot))) {
            throw new \moodle_exception('errorupdatechanged', 'local_bulkinstall');
        }
        $selection = [];
        foreach ($files as $file) {
            if (isset($selection[$file['expectedcomponent']])) {
                throw new \moodle_exception('errorupdateproof', 'local_bulkinstall');
            }
            $selection[$file['expectedcomponent']] = $file['expectedversion'];
        }
        $plan = \local_lmh108\local\update_plan::resolve($catalogue['releases'],
            \local_lmh108\local\update_plan::installed($snapshot), $selection, (int)$CFG->version,
            (int)$CFG->branch, PHP_VERSION, (bool)get_config('local_lmh108', 'updatebeta'));
        if (count($plan) !== count($files)) {
            throw new \moodle_exception('errorupdateproof', 'local_bulkinstall');
        }
        foreach ($files as $file) {
            if (isset($file['sha256']) && !hash_equals($plan[$file['expectedcomponent']]['sha256'], $file['sha256'])) {
                throw new \moodle_exception('errorupdateproof', 'local_bulkinstall');
            }
        }
        return $plan;
    }

    /**
     * Delete staged files and session metadata.
     *
     * @param string $storageid
     */
    public function cleanup(string $storageid): void {
        global $SESSION;
        if (!isset($SESSION->{self::SESSION_KEY}[$storageid])) {
            throw new \moodle_exception('errorstorageexpired', 'local_bulkinstall');
        }
        $this->cleanup_directory($storageid);
        $this->forget($storageid);
    }

    /**
     * Delete only the staged directory.
     *
     * @param string $storageid
     */
    public function cleanup_directory(string $storageid): void {
        $directory = $this->directory($storageid);
        if (is_dir($directory)) {
            remove_dir($directory);
        }
    }

    /**
     * Forget session ownership metadata without deleting packages still in use.
     *
     * @param string $storageid
     */
    public function forget(string $storageid): void {
        global $SESSION;

        $this->validate_storage_id($storageid);
        if (isset($SESSION->{self::SESSION_KEY}[$storageid])) {
            unset($SESSION->{self::SESSION_KEY}[$storageid]);
        }
    }

    /**
     * Remove abandoned directories. Used by the scheduled task.
     *
     * @return int Number removed
     */
    public static function cleanup_expired(): int {
        $base = make_temp_directory('local_bulkinstall');
        $removed = 0;
        $now = time();
        foreach (new \DirectoryIterator($base) as $item) {
            if ($item->isDot() || !$item->isDir() || !preg_match('/^[a-f0-9]{32}$/', $item->getFilename())) {
                continue;
            }
            if ($now - $item->getMTime() > self::MAX_AGE) {
                remove_dir($item->getPathname());
                $removed++;
            }
        }
        return $removed;
    }

    /** @return string */
    private function base_directory(): string {
        return make_temp_directory('local_bulkinstall');
    }

    /** @return string */
    private function directory(string $storageid): string {
        $this->validate_storage_id($storageid);
        return $this->base_directory() . DIRECTORY_SEPARATOR . $storageid;
    }

    /** Validate an opaque storage id. */
    private function validate_storage_id(string $storageid): void {
        if (!preg_match('/^[a-f0-9]{32}$/', $storageid)) {
            throw new \moodle_exception('errorinvalidstorage', 'local_bulkinstall');
        }
    }

    /** Add one unique package error. */
    private function add_error(array &$package, string $message): void {
        if (!in_array($message, $package['errors'], true)) {
            $package['errors'][] = $message;
        }
    }
}
