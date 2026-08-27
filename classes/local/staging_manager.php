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
    /** Maximum packages per batch. */
    public const MAX_PACKAGES = 50;

    /** Maximum uncompressed bytes across a batch (512 MiB). */
    public const MAX_BATCH_UNCOMPRESSED_BYTES = 536870912;

    /** Expire abandoned staging directories after 24 hours. */
    public const MAX_AGE = 86400;

    /** Session property used for ownership and integrity metadata. */
    private const SESSION_KEY = 'local_bulkinstall_staging';

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
     * @return array
     */
    public function analyse(string $storageid): array {
        $record = $this->get_record($storageid);
        $directory = $this->directory($storageid);
        $inspector = new package_inspector();
        $packages = [];
        $totaluncompressed = 0;

        foreach ($record['files'] as $file) {
            $path = $directory . DIRECTORY_SEPARATOR . $file['storedname'];
            $package = $inspector->inspect($path, $file['filename']);
            if (!is_file($path) || !hash_equals($file['sha256'], (string)hash_file('sha256', $path))) {
                $package['errors'][] = get_string('errorintegritychanged', 'local_bulkinstall');
            }
            $package['storedname'] = $file['storedname'];
            $package['sha256'] = $file['sha256'];
            $package['expectedcomponent'] = $file['expectedcomponent'] ?? null;
            if ($package['expectedcomponent'] !== null && $package['component'] !== null &&
                    $package['component'] !== $package['expectedcomponent']) {
                $this->add_error($package, get_string('errorbundlecomponentmismatch', 'local_bulkinstall', (object)[
                    'file' => $file['filename'],
                    'expected' => $package['expectedcomponent'],
                    'actual' => $package['component'],
                ]));
            }
            $totaluncompressed += $package['uncompressedbytes'];
            $packages[] = $package;
        }

        $batcherrors = [];
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

        $pluginmanager = plugin_manager::instance();
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

        $caninstall = empty($batcherrors);
        foreach ($packages as $package) {
            if (!empty($package['errors'])) {
                $caninstall = false;
                break;
            }
        }

        return [
            'mode' => $record['mode'] ?? 'packages',
            'bundle' => $record['bundle'] ?? null,
            'packages' => $packages,
            'batcherrors' => $batcherrors,
            'totaluncompressed' => $totaluncompressed,
            'caninstall' => $caninstall,
        ];
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
        return $record;
    }

    /**
     * Delete staged files and session metadata.
     *
     * @param string $storageid
     */
    public function cleanup(string $storageid): void {
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
