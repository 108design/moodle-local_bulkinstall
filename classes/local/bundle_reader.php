<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace local_bulkinstall\local;

/**
 * Detects and safely expands an outer plugin bundle ZIP.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
class bundle_reader {
    /** Manifest name at the archive root. */
    public const MANIFEST_FILENAME = 'bundle.json';

    /** Frankenstyle-inspired filename for signed 108design bundle archives. */
    public const PUBLISHER_FILENAME_PATTERN = '/^bundle_[a-z0-9][a-z0-9._-]*\.zip$/D';

    /**
     * Whether an outer archive follows the signed 108design bundle naming convention.
     *
     * @param string $filename Original upload filename
     * @return bool
     */
    public static function valid_publisher_filename(string $filename): bool {
        return preg_match(self::PUBLISHER_FILENAME_PATTERN, $filename) === 1;
    }

    /**
     * Whether the ZIP advertises itself as a bundle by containing bundle.json
     * at its root. Invalid ZIPs are left to the ordinary package inspector.
     *
     * @param string $path ZIP path
     * @return bool
     */
    public function is_bundle(string $path): bool {
        try {
            foreach ($this->entries($path) as $entry) {
                if ((string)$entry->pathname === self::MANIFEST_FILENAME && !$entry->is_directory) {
                    return true;
                }
            }
        } catch (\Throwable $exception) {
            return false;
        }
        return false;
    }

    /**
     * Validate and extract the plugin ZIPs from a bundle.
     *
     * @param string $path Outer ZIP path
     * @param string $stagingdirectory Existing staging directory
     * @param string $originalfilename Original outer ZIP filename
     * @return array Bundle metadata and staged file records
     */
    public function expand(string $path, string $stagingdirectory, string $originalfilename): array {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');
        $entries = $this->entries($path);
        if (count($entries) > staging_manager::MAX_PACKAGES + 1) {
            throw new \moodle_exception(
                'errorbundleentries',
                'local_bulkinstall',
                '',
                staging_manager::MAX_PACKAGES
            );
        }

        $entrynames = [];
        $manifestcount = 0;
        $nestedzipbytes = 0;
        foreach ($entries as $entry) {
            $entryname = (string)$entry->pathname;
            if ($entry->is_directory || $entryname === '' || basename($entryname) !== $entryname ||
                    str_contains($entryname, '\\') || str_contains($entryname, "\0")) {
                throw new \moodle_exception('errorbundlerootonly', 'local_bulkinstall', '', $entryname);
            }
            $key = strtolower($entryname);
            if (isset($entrynames[$key])) {
                throw new \moodle_exception('errorbundleduplicateentry', 'local_bulkinstall', '', $entryname);
            }
            $entrynames[$key] = $entryname;
            if ($entryname === self::MANIFEST_FILENAME) {
                $manifestcount++;
                if ((int)$entry->size > bundle_manifest::MAX_BYTES) {
                    throw new \moodle_exception('errorbundlemanifestread', 'local_bulkinstall');
                }
            } else if (!str_ends_with(strtolower($entryname), '.zip')) {
                throw new \moodle_exception('errorbundleunexpectedfile', 'local_bulkinstall', '', $entryname);
            } else {
                $nestedzipbytes += max(0, (int)$entry->size);
                if ($nestedzipbytes > staging_manager::MAX_BATCH_UNCOMPRESSED_BYTES) {
                    throw new \moodle_exception(
                        'errorbundlenestedziplimit',
                        'local_bulkinstall',
                        '',
                        display_size(staging_manager::MAX_BATCH_UNCOMPRESSED_BYTES)
                    );
                }
            }
        }
        if ($manifestcount !== 1) {
            throw new \moodle_exception('errorbundlemanifestmissing', 'local_bulkinstall');
        }

        $workdirectory = make_unique_writable_directory(make_request_directory());
        try {
            $packer = get_file_packer('application/zip');
            $extracted = $packer->extract_to_pathname($path, $workdirectory, [self::MANIFEST_FILENAME]);
            $manifestpath = $workdirectory . DIRECTORY_SEPARATOR . self::MANIFEST_FILENAME;
            if (empty($extracted) || !is_file($manifestpath) ||
                    filesize($manifestpath) > bundle_manifest::MAX_BYTES) {
                throw new \moodle_exception('errorbundlemanifestread', 'local_bulkinstall');
            }
            $manifest = (new bundle_manifest())->parse((string)file_get_contents($manifestpath));
            $listednames = array_column($manifest['plugins'], 'file');
            $archivenames = array_values(array_filter(
                array_values($entrynames),
                static fn(string $name): bool => $name !== self::MANIFEST_FILENAME
            ));
            sort($listednames);
            sort($archivenames);
            if ($listednames !== $archivenames) {
                throw new \moodle_exception('errorbundlefilelist', 'local_bulkinstall');
            }

            $extracted = $packer->extract_to_pathname($path, $workdirectory, $listednames);
            if (empty($extracted)) {
                throw new \moodle_exception('errorbundleextract', 'local_bulkinstall');
            }

            $files = [];
            foreach ($manifest['plugins'] as $index => $plugin) {
                $source = $workdirectory . DIRECTORY_SEPARATOR . $plugin['file'];
                if (!is_file($source)) {
                    throw new \moodle_exception('errorbundleextract', 'local_bulkinstall');
                }
                $hash = hash_file('sha256', $source);
                if (!hash_equals($plugin['sha256'], (string)$hash)) {
                    throw new \moodle_exception(
                        'errorbundlehashmismatch',
                        'local_bulkinstall',
                        '',
                        $plugin['file']
                    );
                }
                $storedname = sprintf('package-%03d.zip', $index + 1);
                $destination = $stagingdirectory . DIRECTORY_SEPARATOR . $storedname;
                if (!rename($source, $destination)) {
                    throw new \moodle_exception('errorbundleextract', 'local_bulkinstall');
                }
                $files[] = [
                    'storedname' => $storedname,
                    'filename' => $plugin['file'],
                    'sha256' => $hash,
                    'bytes' => (int)filesize($destination),
                    'expectedcomponent' => $plugin['component'],
                ];
            }

            return [
                'bundle' => [
                    'archivefilename' => $originalfilename,
                    'archivesha256' => hash_file('sha256', $path),
                    'format' => $manifest['format'],
                    'formatversion' => $manifest['formatversion'],
                    'id' => $manifest['id'],
                    'name' => $manifest['name'],
                    'version' => $manifest['version'],
                    'description' => $manifest['description'],
                    'publisherfilenamevalid' => $manifest['formatversion'] !== 2
                        || self::valid_publisher_filename($originalfilename),
                ],
                'files' => $files,
            ];
        } finally {
            remove_dir($workdirectory);
        }
    }

    /** Return archive entries or throw a bundle-specific error. */
    private function entries(string $path): array {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');
        try {
            $entries = get_file_packer('application/zip')->list_files($path);
        } catch (\Throwable $exception) {
            throw new \moodle_exception('errorinvalidzip', 'local_bulkinstall');
        }
        if (empty($entries) || !is_array($entries)) {
            throw new \moodle_exception('errorinvalidzip', 'local_bulkinstall');
        }
        return $entries;
    }
}
