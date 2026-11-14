<?php
// This file is part of Moodle - https://moodle.org/
// @copyright 2026 Andreas Giesen <andreas@108design.com>
// @license 108design source-available software license; see LICENSE.md
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md for the full terms.
namespace local_bulkinstall\local;
defined('MOODLE_INTERNAL') || die();

/** Protects installed publisher/edition state before the complete BI batch is deployed. */
final class replacement_guard {
    /** @var \Closure|null Optional installed-root resolver for isolated acceptance fixtures. */
    private ?\Closure $rootresolver;
    /** Authenticated transition snapshots are reused only within one analysis or locked freeze. */
    private array $transitions = [];

    public function reset_verification(): void { $this->transitions = []; }

    public function __construct(?callable $rootresolver = null) {
        $this->rootresolver = $rootresolver === null ? null : \Closure::fromCallable($rootresolver);
    }

    /** Describe only an authenticated, supported transition; failure remains a hard guard error. */
    public function transition(string $zipfile, string $component, int $version): ?array {
        $root = $this->installed_root($component);
        if ($root === null) { return null; }
        if (!class_exists(\design108\update_guard\v1\guard::class, false)) {
            require_once(__DIR__ . '/../../update-guard.php');
        }
        if (!\design108\update_guard\v1\guard::is_protected($root, $component)) { return null; }
        $key = $root . '|' . $component . '|' . $version . '|' . hash_file('sha256', $zipfile);
        if (!array_key_exists($key, $this->transitions)) {
            $this->transitions[$key] = edition_transition::inspect($zipfile, $root, $component, $version);
        }
        return $this->transitions[$key];
    }

    /** Return global proof errors: selection exclusion must never bypass a failed guard. */
    public function analyse(array $packages): array {
        $errors = [];
        foreach ($packages as $package) {
            if (!is_string($package['component'] ?? null) || !is_int($package['version'] ?? null)) {
                continue; // The ordinary inspector already blocks malformed package metadata.
            }
            try {
                $this->verify($package['path'], $package['component'], $package['version']);
            } catch (\Throwable $exception) {
                $errors[] = $exception instanceof \moodle_exception && $exception->errorcode === 'errortransitionlicence'
                    ? get_string('errortransitionlicence', 'local_bulkinstall')
                    : get_string('errorupdateguard', 'local_bulkinstall', $package['component']);
            }
        }
        return array_values(array_unique($errors));
    }

    /**
     * Freeze every selected archive, then check all hashes and guarded replacements.
     * Caller holds BI's deployment lock; no deployment descriptor escapes on partial failure.
     */
    public function freeze(array $packages): array {
        $this->reset_verification();
        if (storefront_transition::errors($packages)) { throw new \moodle_exception('storefronttransitionblocked', 'local_bulkinstall'); }
        $choices = array_filter(array_column($packages, 'activitychoice'));
        $choice = $choices ? reset($choices) : null;
        if (frontpage_transition::errors($packages, $choice)) {
            throw new \moodle_exception('frontpagechoicerequired', 'local_bulkinstall');
        }
        $directory = make_request_directory();
        $installables = [];
        $manifests = [];
        $planned = [];
        try {
            foreach ($packages as $index => $package) {
                $path = $directory . '/bi-verified-' . $index . '.zip';
                if (!is_file($package['path']) || is_link($package['path'])
                        || !copy($package['path'], $path)
                        || !is_string($package['sha256'] ?? null)
                        || !hash_equals($package['sha256'], (string)hash_file('sha256', $path))) {
                    throw new \moodle_exception('errorintegritychanged', 'local_bulkinstall');
                }
                // Resolve the installed policy afresh after the deployment lock is acquired.
                try {
                    $transition = $this->transition($path, $package['component'], $package['version']);
                    if ($transition !== ($package['transition'] ?? null)) {
                        throw new \RuntimeException('transition-review-changed');
                    }
                    $manifest = $this->verify($path, $package['component'], $package['version']);
                    if ($manifest !== null) { $manifests[$package['component']] = $manifest; }
                } catch (\Throwable $exception) {
                    throw new \moodle_exception('errorupdateguard', 'local_bulkinstall', '', $package['component']);
                }
                $planned[$package['component']] = $package['version'];
                $installables[] = (object)['component' => $package['component'], 'zipfilepath' => $path];
            }
            if ($manifests) {
                global $CFG;
                $available = [];
                foreach (\core\plugin_manager::instance()->get_plugins() as $type => $plugins) {
                    foreach ($plugins as $name => $plugin) {
                        if ($plugin->versiondisk !== null) { $available[$type . '_' . $name] = (int)$plugin->versiondisk; }
                    }
                }
                try {
                    \design108\update_guard\v1\guard::assert_requirements($manifests,
                        array_replace($available, $planned), (int)$CFG->version);
                } catch (\Throwable $exception) {
                    throw new \moodle_exception('updateguard_dependency', 'local_bulkinstall');
                }
            }
        } catch (\Throwable $exception) {
            remove_dir($directory);
            throw $exception;
        }
        return $installables;
    }

    /** Resolve trust from the existing installed target, never from the candidate policy. */
    private function verify(string $zipfile, string $component, int $version): ?array {
        $root = $this->installed_root($component);
        if ($root === null) { return null; }
        if (!class_exists(\design108\update_guard\v1\guard::class, false)) {
            require_once(__DIR__ . '/../../update-guard.php');
        }
        if (!\design108\update_guard\v1\guard::is_protected($root, $component)) { return null; }
        $transition = $this->transition($zipfile, $component, $version);
        if ($transition !== null) {
            if (!$transition['ready']) {
                throw new \moodle_exception('errortransitionlicence', 'local_bulkinstall');
            }
            // inspect() already authenticated the complete archive under the installed policy.
            // Re-read only the exact authenticated manifest; a changed archive has a different cache key.
            $zip = new \ZipArchive();
            if ($zip->open($zipfile) !== true) { throw new \RuntimeException('transition-archive'); }
            try { $raw = $zip->getFromName(substr($component, strpos($component, '_') + 1) . '/release-manifest.json'); }
            finally { $zip->close(); }
            if (!is_string($raw) || !hash_equals($transition['manifestsha256'], hash('sha256', $raw))
                    || !hash_equals($transition['sha256'], (string)hash_file('sha256', $zipfile))) {
                throw new \RuntimeException('transition-archive-changed');
            }
            return json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        }
        return \design108\update_guard\v1\guard::verify_replacement($zipfile, $root, $component, $version, 'bi');
    }

    /** Only a plugin recorded as installed supplies a policy; new installation stays ordinary. */
    private function installed_root(string $component): ?string {
        if ($this->rootresolver !== null) {
            return ($this->rootresolver)($component);
        }
        $info = \core\plugin_manager::instance()->get_plugin_info($component);
        return $info !== null && $info->versiondb !== null && is_string($info->rootdir ?? null)
            ? $info->rootdir : null;
    }
}
