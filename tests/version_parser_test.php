<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace local_bulkinstall;

use local_bulkinstall\local\version_parser;

/**
 * Tests for safe version.php metadata parsing.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
final class version_parser_test extends \basic_testcase {
    /** Parse the normal literal declarations used by Moodle plugins. */
    public function test_parse_literal_metadata(): void {
        $source = <<<'PHP'
<?php
// A comment that must not influence parsing.
$plugin->component = 'local_example';
$plugin->version = 2026082300;
$plugin->requires = 2024100700;
$plugin->supported = [405, 502];
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.2.3';
$plugin->dependencies = [
    'mod_example' => 2026010100,
    'local_helper' => ANY_VERSION,
];
PHP;
        $metadata = (new version_parser())->parse($source);

        $this->assertSame('local_example', $metadata['component']);
        $this->assertSame(2026082300, $metadata['version']);
        $this->assertSame(2024100700, $metadata['requires']);
        $this->assertSame([405, 502], $metadata['supported']);
        $this->assertSame('MATURITY_STABLE', $metadata['maturity']);
        $this->assertSame('1.2.3', $metadata['release']);
        $this->assertSame([
            'mod_example' => 2026010100,
            'local_helper' => 0,
        ], $metadata['dependencies']);
        $this->assertTrue($metadata['dependenciescomplete']);
    }

    /** Dynamic expressions must not be evaluated and are marked incomplete. */
    public function test_dynamic_dependency_is_not_evaluated(): void {
        $source = <<<'PHP'
<?php
$plugin->component = 'local_example';
$plugin->version = 2026082300;
$plugin->dependencies = ['mod_example' => get_required_version()];
PHP;
        $metadata = (new version_parser())->parse($source);

        $this->assertSame([], $metadata['dependencies']);
        $this->assertFalse($metadata['dependenciescomplete']);
    }

    /** Old array syntax remains readable for supported Moodle packages. */
    public function test_parse_old_array_syntax(): void {
        $source = <<<'PHP'
<?php
$plugin->component = "block_example";
$plugin->version = 2026082300;
$plugin->supported = array(405, 502);
$plugin->dependencies = array('local_helper' => 2026010100);
PHP;
        $metadata = (new version_parser())->parse($source);

        $this->assertSame([405, 502], $metadata['supported']);
        $this->assertSame(['local_helper' => 2026010100], $metadata['dependencies']);
        $this->assertTrue($metadata['dependenciescomplete']);
    }
}
