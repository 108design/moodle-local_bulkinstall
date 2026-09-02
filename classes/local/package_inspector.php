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
use core\update\validator;

/**
 * Inspects one ordinary Moodle plugin ZIP package.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
class package_inspector {
    /** Maximum number of archive entries in one package. */
    public const MAX_ENTRIES = 10000;

    /** Maximum uncompressed bytes in one package (256 MiB). */
    public const MAX_UNCOMPRESSED_BYTES = 268435456;

    /**
     * Inspect a ZIP.
     *
     * @param string $path Absolute staged ZIP path
     * @param string $filename Original filename
     * @return array
     */
    public function inspect(string $path, string $filename): array {
        global $CFG;

        $result = [
            'filename' => $filename,
            'path' => $path,
            'component' => null,
            'type' => null,
            'name' => null,
            'version' => null,
            'release' => null,
            'requires' => null,
            'supported' => null,
            'dependencies' => [],
            'installedversion' => null,
            'action' => 'unknown',
            'uncompressedbytes' => 0,
            'errors' => [],
            'compatibilityerrors' => [],
            'warnings' => [],
            'messages' => [],
        ];

        if (!is_file($path) || !is_readable($path)) {
            $this->error($result, get_string('errorstagedfilemissing', 'local_bulkinstall'));
            return $result;
        }

        require_once($CFG->libdir . '/filelib.php');
        $packer = get_file_packer('application/zip');
        try {
            $entries = $packer->list_files($path);
        } catch (\Throwable $exception) {
            $this->error($result, get_string('errorinvalidzip', 'local_bulkinstall'));
            return $result;
        }
        if (empty($entries) || !is_array($entries)) {
            $this->error($result, get_string('errorinvalidzip', 'local_bulkinstall'));
            return $result;
        }
        if (count($entries) > self::MAX_ENTRIES) {
            $this->error($result, get_string('errortoomanyentries', 'local_bulkinstall', self::MAX_ENTRIES));
            return $result;
        }

        $root = null;
        $versionpath = null;
        foreach ($entries as $entry) {
            $pathname = (string)$entry->pathname;
            if (!$this->path_is_safe($pathname)) {
                $this->error($result, get_string('errorunsafepath', 'local_bulkinstall', $pathname));
                return $result;
            }
            $normalised = trim($pathname, '/');
            if ($normalised === '') {
                $this->error($result, get_string('errorinvalidlayout', 'local_bulkinstall'));
                return $result;
            }
            $parts = explode('/', $normalised);
            if ($root === null) {
                $root = $parts[0];
            } else if ($root !== $parts[0]) {
                $this->error($result, get_string('errormultipleroots', 'local_bulkinstall'));
                return $result;
            }
            if (!$entry->is_directory) {
                $size = max(0, (int)$entry->size);
                $result['uncompressedbytes'] += $size;
                if ($result['uncompressedbytes'] > self::MAX_UNCOMPRESSED_BYTES) {
                    $this->error($result, get_string(
                        'errorpackageuncompressedlimit',
                        'local_bulkinstall',
                        display_size(self::MAX_UNCOMPRESSED_BYTES)
                    ));
                    return $result;
                }
                if ($pathname === $root . '/version.php') {
                    $versionpath = $pathname;
                }
            }
        }

        // Moodle Marketplace archives commonly use a safe, versioned repository directory such as
        // moodle-format_flexsections-5.0.4. The component declared in version.php, not that transport-level
        // directory name, determines Moodle's canonical installation directory.
        if ($root === null || !preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,254}$/', $root)) {
            $this->error($result, get_string('errorinvalidroot', 'local_bulkinstall', (string)$root));
            return $result;
        }
        if ($versionpath === null) {
            $this->error($result, get_string('errormissingversionphp', 'local_bulkinstall'));
            return $result;
        }

        $metadir = make_unique_writable_directory(make_request_directory());
        try {
            $extracted = $packer->extract_to_pathname($path, $metadir, [$versionpath]);
            if (empty($extracted) || !is_file($metadir . '/' . $versionpath)) {
                $this->error($result, get_string('errorextractmetadata', 'local_bulkinstall'));
                return $result;
            }
            $metadata = (new version_parser())->parse(file_get_contents($metadir . '/' . $versionpath));
        } catch (\Throwable $exception) {
            $this->error($result, get_string('errorextractmetadata', 'local_bulkinstall'));
            return $result;
        } finally {
            remove_dir($metadir);
        }

        $result['component'] = $metadata['component'];
        $result['version'] = $metadata['version'];
        $result['release'] = $metadata['release'];
        $result['requires'] = $metadata['requires'];
        $result['supported'] = $metadata['supported'];
        $result['dependencies'] = $metadata['dependencies'];

        if (empty($metadata['component'])) {
            $this->error($result, get_string('errormissingcomponent', 'local_bulkinstall'));
            return $result;
        }
        [$type, $name] = \core_component::normalize_component($metadata['component']);
        $result['type'] = $type;
        $result['name'] = $name;
        if ($type === 'core' || $metadata['component'] !== $type . '_' . $name) {
            $this->error($result, get_string('errorinvalidcomponent', 'local_bulkinstall', $metadata['component']));
            return $result;
        }
        $types = \core_component::get_plugin_types();
        if (!array_key_exists($type, $types)) {
            $this->error($result, get_string('errorunknownplugintype', 'local_bulkinstall', $type));
            return $result;
        }
        if (!\core_component::is_valid_plugin_name($type, $name)) {
            $this->error($result, get_string('errorinvalidcomponent', 'local_bulkinstall', $metadata['component']));
            return $result;
        }
        if ($metadata['version'] === null) {
            $this->error($result, get_string('errormissingversion', 'local_bulkinstall'));
            return $result;
        }
        if ($metadata['requires'] !== null && $metadata['requires'] > (int)$CFG->version) {
            $this->error($result, get_string('errorrequiresmoodle', 'local_bulkinstall', $metadata['requires']));
        }
        if ($metadata['supported'] !== null) {
            $branch = (int)$CFG->branch;
            if ($branch < $metadata['supported'][0] || $branch > $metadata['supported'][1]) {
                $this->compatibility_error($result, get_string('errorunsupportedbranch', 'local_bulkinstall', (object)[
                    'branch' => $branch,
                    'minimum' => $metadata['supported'][0],
                    'maximum' => $metadata['supported'][1],
                ]));
            }
        }
        if (!$metadata['dependenciescomplete']) {
            $this->warning($result, get_string('warningdynamicdependencies', 'local_bulkinstall'));
        }

        $pluginmanager = plugin_manager::instance();
        $plugininfo = $pluginmanager->get_plugin_info($metadata['component']);
        if ($plugininfo !== null && $plugininfo->versiondb !== null) {
            $result['installedversion'] = (int)$plugininfo->versiondb;
            if ($metadata['version'] < $result['installedversion']) {
                $result['action'] = 'downgrade';
                $this->error($result, get_string('errordowngrade', 'local_bulkinstall', $result['installedversion']));
            } else if ($metadata['version'] === $result['installedversion']) {
                $result['action'] = 'reinstall';
                $this->warning($result, get_string('warningsameversion', 'local_bulkinstall'));
            } else {
                $result['action'] = 'update';
            }
        } else {
            $result['action'] = 'install';
        }

        // A declared support-range mismatch is the one error the private service edition may explicitly waive.
        // Still run Moodle's full archive validation now so that such a waiver can never hide an unrelated error.
        $harderrors = array_diff($result['errors'], $result['compatibilityerrors']);
        if (empty($harderrors)) {
            $this->run_core_validation($result, $path, $name, $type, $pluginmanager);
        }
        return $result;
    }

    /**
     * Run Moodle's own full archive validator and preserve its messages.
     */
    private function run_core_validation(
        array &$result,
        string $path,
        string $canonicalroot,
        string $type,
        plugin_manager $pluginmanager
    ): void {
        global $CFG;

        $workdir = make_unique_writable_directory(make_request_directory());
        try {
            // Mirror Moodle's own install_plugins() path: the transport-level archive root may be a versioned
            // Marketplace/repository name, but validation and deployment use the canonical name from component.
            $zipcontents = $pluginmanager->unzip_plugin_file($path, $workdir, $canonicalroot);
            if (empty($zipcontents)) {
                $this->error($result, get_string('errorinvalidzip', 'local_bulkinstall'));
                return;
            }
            $validator = validator::instance($workdir, $zipcontents);
            $validator->assert_plugin_type($type);
            $validator->assert_moodle_version($CFG->version);
            $valid = $validator->execute();
            foreach ($validator->get_messages() as $message) {
                $text = $validator->message_code_name($message->msgcode);
                $info = $validator->message_code_info($message->msgcode, $message->addinfo);
                if ($info !== '') {
                    $text .= ': ' . $info;
                } else if (is_string($message->addinfo) && $message->addinfo !== '') {
                    $text .= ': ' . $message->addinfo;
                }
                $result['messages'][] = ['level' => $message->level, 'text' => $text];
                if ($message->level === validator::WARNING) {
                    $this->warning($result, $text);
                }
            }
            if (!$valid) {
                foreach ($result['messages'] as $message) {
                    if ($message['level'] === validator::ERROR) {
                        $this->error($result, $message['text']);
                    }
                }
                if (empty($result['errors'])) {
                    $this->error($result, get_string('errorcorevalidation', 'local_bulkinstall'));
                }
            }
        } catch (\Throwable $exception) {
            $this->error($result, get_string('errorcorevalidation', 'local_bulkinstall'));
        } finally {
            remove_dir($workdir);
        }
    }

    /** @return bool */
    private function path_is_safe(string $path): bool {
        if ($path === '' || str_contains($path, "\0") || str_contains($path, '\\') ||
                str_starts_with($path, '/') || preg_match('/^[A-Za-z]:/', $path)) {
            return false;
        }
        foreach (explode('/', trim($path, '/')) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return false;
            }
        }
        return true;
    }

    /** Add a unique error. */
    private function error(array &$result, string $message): void {
        if (!in_array($message, $result['errors'], true)) {
            $result['errors'][] = $message;
        }
    }

    /** Add a declared support-range error that the private service edition may explicitly override. */
    private function compatibility_error(array &$result, string $message): void {
        $this->error($result, $message);
        if (!in_array($message, $result['compatibilityerrors'], true)) {
            $result['compatibilityerrors'][] = $message;
        }
    }

    /** Add a unique warning. */
    private function warning(array &$result, string $message): void {
        if (!in_array($message, $result['warnings'], true)) {
            $result['warnings'][] = $message;
        }
    }
}
