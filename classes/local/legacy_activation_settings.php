<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Remove obsolete plugin-owned activation settings; no client or entitlement operation.
 *
 * @package local_bulkinstall
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license See LICENSE.md for the full terms.
 */
final class legacy_activation_settings {
    /** Historical standalone keys, including both atomic and older split state. */
    private const KEYS = [
        'licensingactivestate', 'activationid', 'environment', 'refreshtoken', 'assertion',
        'lastcheck', 'lasterror', 'emergencyresetavailable', 'sitetoken', 'installationid',
        'sitelinkid', 'sitelinksecret', 'sitelinkcode', 'sitelinkurl', 'sitelinkexpires',
        'sitelinkenvironment',
    ];

    /** Preserve current settings and all other components, including Plugin Manager records. */
    public static function clear(): void {
        foreach (self::KEYS as $name) {
            unset_config($name, 'local_bulkinstall');
        }
    }
}
