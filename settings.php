<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $page = new admin_externalpage(
        'local_bulkinstall',
        get_string('menulabel', 'local_bulkinstall'),
        new moodle_url('/local/bulkinstall/index.php'),
        'moodle/site:config',
        !empty($CFG->disableupdateautodeploy)
    );

    // Moodle sorts the direct children of the Plugins category alphabetically,
    // which would put Bulk installation before the two core installer links.
    // Restore insertion order and keep the four direct pages together before
    // the first plugin-type category. This works with the admin-tree layouts in
    // Moodle 4.5 as well as the split layout used by Moodle 5.2.
    $modules = $ADMIN->locate('modules');
    $overview = $ADMIN->locate('pluginsoverview');
    $firstcategory = $ADMIN->locate('modsettings');
    if ($modules instanceof admin_category && $overview !== null &&
            $firstcategory !== null && $ADMIN->prune('pluginsoverview')) {
        $modules->set_sorting(false);
        $ADMIN->add('modules', $page, 'modsettings');
        $ADMIN->add('modules', $overview, 'modsettings');
    } else {
        // Defensive fallback for a future Moodle admin-tree layout.
        $ADMIN->add('modules', $page, 'pluginsoverview');
    }

    $formatpage = new admin_externalpage(
        'local_bulkinstall_bundleformat',
        get_string('bundleformattitle', 'local_bulkinstall'),
        new moodle_url('/local/bulkinstall/bundleformat.php'),
        'moodle/site:config',
        true
    );
    $ADMIN->add('modules', $formatpage);
}
