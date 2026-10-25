<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace local_bulkinstall;

use local_bulkinstall\local\legacy_activation_settings;

defined('MOODLE_INTERNAL') || die();

/**
 * Native configuration cleanup preserves unrelated state and is safe to repeat.
 * @package local_bulkinstall
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license See LICENSE.md for the full terms.
 */
final class legacy_activation_settings_test extends \advanced_testcase {
    public function test_obsolete_standalone_state_is_removed_only_from_this_component(): void {
        $this->resetAfterTest();
        $names = ['licensingactivestate', 'activationid', 'environment', 'refreshtoken', 'assertion',
            'lastcheck', 'lasterror', 'emergencyresetavailable', 'sitetoken', 'installationid',
            'sitelinkid', 'sitelinksecret', 'sitelinkcode', 'sitelinkurl', 'sitelinkexpires', 'sitelinkenvironment'];
        foreach ($names as $name) {
            set_config($name, 'synthetic-old-' . $name, 'local_bulkinstall');
            set_config($name, 'synthetic-control-' . $name, 'local_reviewer_cleanup_control');
        }
        set_config('preserve_current_setting', 'keep', 'local_bulkinstall');
        legacy_activation_settings::clear();
        foreach ($names as $name) {
            $this->assertFalse(get_config('local_bulkinstall', $name));
            $this->assertSame('synthetic-control-' . $name, get_config('local_reviewer_cleanup_control', $name));
        }
        $this->assertSame('keep', get_config('local_bulkinstall', 'preserve_current_setting'));
        legacy_activation_settings::clear();
        $this->assertSame('keep', get_config('local_bulkinstall', 'preserve_current_setting'));
    }
}
