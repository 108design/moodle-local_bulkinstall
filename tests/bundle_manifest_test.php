<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace local_bulkinstall;

use local_bulkinstall\local\bundle_manifest;

/**
 * Tests for compatibility manifests and signed publisher bundles.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 * @covers     \local_bulkinstall\local\bundle_manifest
 */
final class bundle_manifest_test extends \advanced_testcase {
    /** A complete valid manifest is normalised and returned. */
    public function test_parse_valid_manifest(): void {
        $manifest = (new bundle_manifest())->parse($this->manifest_json());

        $this->assertSame('moodle-plugin-bundle', $manifest['format']);
        $this->assertSame(1, $manifest['formatversion']);
        $this->assertSame('example-tools', $manifest['id']);
        $this->assertSame('mod_example.zip', $manifest['plugins'][0]['file']);
        $this->assertSame(str_repeat('a', 64), $manifest['plugins'][0]['sha256']);
    }

    /** Undocumented properties are rejected to keep version 1 unambiguous. */
    public function test_unknown_top_level_property_is_rejected(): void {
        $data = json_decode($this->manifest_json());
        $data->vendor = 'Example';

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage('vendor');
        (new bundle_manifest())->parse(json_encode($data));
    }

    /** Files that collide only by case are still duplicates. */
    public function test_case_colliding_file_is_rejected(): void {
        $data = json_decode($this->manifest_json());
        $second = clone $data->plugins[0];
        $second->file = 'MOD_EXAMPLE.ZIP';
        $second->component = 'local_example';
        $data->plugins[] = $second;

        $this->expectException(\moodle_exception::class);
        (new bundle_manifest())->parse(json_encode($data));
    }

    /** Every component may occur only once. */
    public function test_duplicate_component_is_rejected(): void {
        $data = json_decode($this->manifest_json());
        $second = clone $data->plugins[0];
        $second->file = 'different.zip';
        $data->plugins[] = $second;

        $this->expectException(\moodle_exception::class);
        (new bundle_manifest())->parse(json_encode($data));
    }

    /** Malformed JSON is rejected as a public manifest error. */
    public function test_invalid_json_is_rejected(): void {
        $this->expectException(\moodle_exception::class);
        (new bundle_manifest())->parse('{');
    }

    /** A publisher bundle is accepted only when its canonical Ed25519 signature is intact. */
    public function test_parse_valid_signed_manifest_and_reject_tampering(): void {
        $data = [
            'format' => 'moodle-plugin-bundle',
            'formatversion' => 2,
            'id' => 'example-signed-tools',
            'name' => 'Example Signed Tools',
            'version' => '2.0.0',
            'description' => 'Signed fixture',
            'publisher' => '108design',
            'keyid' => 'bundle-2026-01',
            'plugins' => [[
                'file' => 'mod_example.zip',
                'component' => 'mod_example',
                'sha256' => str_repeat('a', 64),
            ]],
            'signature' => 'bxTh8Pf6G8lneBSolYcg5eZ6OInxKk8MJwxLFZJ-TdR_V4YBJXK4S_QX1nZfEJYswu3ckq45wHprUOeCuEx2Aw',
        ];
        $manifest = (new bundle_manifest())->parse(json_encode($data, JSON_THROW_ON_ERROR));
        $this->assertSame(2, $manifest['formatversion']);

        $data['name'] = 'Tampered';
        $this->expectException(\moodle_exception::class);
        (new bundle_manifest())->parse(json_encode($data, JSON_THROW_ON_ERROR));
    }

    /** Return one valid fixture. */
    private function manifest_json(): string {
        return json_encode([
            'format' => 'moodle-plugin-bundle',
            'formatversion' => 1,
            'id' => 'example-tools',
            'name' => 'Example Tools',
            'version' => '1.0.0',
            'description' => 'Example bundle',
            'plugins' => [[
                'file' => 'mod_example.zip',
                'component' => 'mod_example',
                'sha256' => strtoupper(str_repeat('a', 64)),
            ]],
        ], JSON_THROW_ON_ERROR);
    }
}
