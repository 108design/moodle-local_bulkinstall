<?php
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
 * No database changes are required by the current release.
 *
 * @param int $oldversion Previously installed plugin version.
 * @return bool
 */
function xmldb_local_bulkinstall_upgrade(int $oldversion): bool {
    return true;
}
