<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace local_bulkinstall;

use local_bulkinstall\local\bundle_reader;

/**
 * Tests for safe outer bundle expansion.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
final class bundle_reader_test extends \advanced_testcase {
    /** A valid bundle expands only its declared nested ZIP. */
    public function test_expand_valid_bundle(): void {
        $directory = make_unique_writable_directory(make_request_directory());
        try {
            [$bundlepath, $pluginhash] = $this->create_bundle($directory);
            $staging = make_unique_writable_directory($directory);
            $reader = new bundle_reader();

            $this->assertTrue($reader->is_bundle($bundlepath));
            $result = $reader->expand($bundlepath, $staging, 'example-bundle.zip');

            $this->assertSame('example-tools', $result['bundle']['id']);
            $this->assertTrue($result['bundle']['publisherfilenamevalid']);
            $this->assertSame('mod_example.zip', $result['files'][0]['filename']);
            $this->assertSame('mod_example', $result['files'][0]['expectedcomponent']);
            $this->assertSame($pluginhash, $result['files'][0]['sha256']);
            $this->assertFileExists($staging . '/package-001.zip');
        } finally {
            remove_dir($directory);
        }
    }

    /** Signed publisher bundles use their own Frankenstyle-inspired outer filename. */
    public function test_publisher_filename_convention(): void {
        $this->assertTrue(bundle_reader::valid_publisher_filename('bundle_course_tools-1.2.0.zip'));
        $this->assertTrue(bundle_reader::valid_publisher_filename('bundle_108design-free-starter-2026.08.24.1.zip'));
        $this->assertFalse(bundle_reader::valid_publisher_filename('course-tools-1.2.0.zip'));
        $this->assertFalse(bundle_reader::valid_publisher_filename('BUNDLE_course_tools-1.2.0.zip'));
        $this->assertFalse(bundle_reader::valid_publisher_filename('bundle_.zip'));
        $this->assertFalse(bundle_reader::valid_publisher_filename('bundle_course tools-1.2.0.zip'));
    }

    /** The manifest digest is checked before package inspection. */
    public function test_hash_mismatch_is_rejected(): void {
        $directory = make_unique_writable_directory(make_request_directory());
        try {
            [$bundlepath] = $this->create_bundle($directory, str_repeat('0', 64));
            $staging = make_unique_writable_directory($directory);

            $this->expectException(\moodle_exception::class);
            (new bundle_reader())->expand($bundlepath, $staging, 'bad-bundle.zip');
        } finally {
            remove_dir($directory);
        }
    }

    /** Build a small outer ZIP and its nested ordinary plugin ZIP. */
    private function create_bundle(string $directory, ?string $manifesthash = null): array {
        $packer = get_file_packer('application/zip');
        $pluginpath = $directory . '/mod_example.zip';
        $this->assertTrue($packer->archive_to_pathname([
            'example/version.php' => ["<?php\n\$plugin->component = 'mod_example';\n"],
        ], $pluginpath));
        $pluginhash = hash_file('sha256', $pluginpath);
        $manifest = json_encode([
            'format' => 'moodle-plugin-bundle',
            'formatversion' => 1,
            'id' => 'example-tools',
            'name' => 'Example Tools',
            'version' => '1.0.0',
            'plugins' => [[
                'file' => 'mod_example.zip',
                'component' => 'mod_example',
                'sha256' => $manifesthash ?? $pluginhash,
            ]],
        ], JSON_THROW_ON_ERROR);
        $bundlepath = $directory . '/bundle.zip';
        $this->assertTrue($packer->archive_to_pathname([
            'bundle.json' => [$manifest],
            'mod_example.zip' => $pluginpath,
        ], $bundlepath));
        return [$bundlepath, $pluginhash];
    }
}
