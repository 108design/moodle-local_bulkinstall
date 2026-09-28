<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace local_bulkinstall;

use local_bulkinstall\local\package_inspector;

/**
 * Tests ordinary plugin archive inspection.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
final class package_inspector_test extends \advanced_testcase {
    /** Marketplace archives may wrap plugins in safe, versioned repository directories. */
    public function test_versioned_marketplace_roots_are_canonicalised_for_core_validation(): void {
        $directory = make_unique_writable_directory(make_request_directory());
        try {
            $cases = [
                ['moodle-format_flexsections-5.0.4', 'format_flexsections'],
                ['moodle-tiny_generico-master', 'tiny_generico'],
                ['moodle-tiny_widgethub-1.5.3', 'tiny_widgethub'],
            ];
            foreach ($cases as [$root, $component]) {
                $path = $this->create_package($directory, $root, [405, 502], $component);
                $result = (new package_inspector())->inspect($path, $component . '.zip');

                $this->assertSame($component, $result['component']);
                $this->assertSame([], $result['errors'], 'Unexpected validation error for ' . $root);
            }
        } finally {
            remove_dir($directory);
        }
    }

    /** A declared support-range mismatch is classified separately from hard validation errors. */
    public function test_supported_range_error_is_explicitly_classified(): void {
        global $CFG;

        $directory = make_unique_writable_directory(make_request_directory());
        $originalbranch = $CFG->branch;
        try {
            $CFG->branch = '502';
            $path = $this->create_package($directory, 'example', [401, 405]);
            $result = (new package_inspector())->inspect($path, 'local_example.zip');
            $expected = get_string('errorunsupportedbranch', 'local_bulkinstall', (object) [
                'branch' => 502,
                'minimum' => 401,
                'maximum' => 405,
            ]);

            $this->assertContains($expected, $result['errors']);
            $this->assertSame([$expected], $result['compatibilityerrors']);
            $this->assertNotEmpty($result['messages'], 'Moodle core validation must still run for an overridable error.');
        } finally {
            $CFG->branch = $originalbranch;
            remove_dir($directory);
        }
    }

    /** Create a minimal ordinary plugin package. */
    private function create_package(
        string $directory,
        string $root,
        array $supported,
        string $component = 'local_example'
    ): string {
        [$type, $name] = \core_component::normalize_component($component);
        $path = $directory . '/' . clean_param($component, PARAM_FILE) . '.zip';
        $version = "<?php\n"
            . "\$plugin->component = '{$component}';\n"
            . "\$plugin->version = 2026090200;\n"
            . "\$plugin->requires = 2024100700;\n"
            . "\$plugin->supported = [{$supported[0]}, {$supported[1]}];\n"
            . "\$plugin->maturity = MATURITY_STABLE;\n"
            . "\$plugin->release = '1.0.0';\n";
        $this->assertTrue(get_file_packer('application/zip')->archive_to_pathname([
            $root . '/version.php' => [$version],
            $root . '/lang/en/' . $type . '_' . $name . '.php' => ["<?php\n\$string['pluginname'] = 'Example';\n"],
        ], $path));
        return $path;
    }
}
