<?php
/**
 * File documentation for db/upgrade.php.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the Bulk Installer plugin.
 *
 * Retire obsolete standalone activation settings from the activation-free product.
 *
 * @param int $oldversion Previously installed plugin version.
 * @return bool
 */
function xmldb_local_bulkinstall_upgrade(int $oldversion): bool {
    if ($oldversion < 2026102400) {
        \local_bulkinstall\local\legacy_activation_settings::clear();
        upgrade_plugin_savepoint(true, 2026102400, 'local', 'bulkinstall');
    }
    return true;
}
