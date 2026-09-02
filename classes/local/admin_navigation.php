<?php
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>

namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

/** Stable product navigation; licensing is always a normal tab. */
final class admin_navigation {
    public static function render(string $active): string {
        $tabs = [
            new \tabobject('install', new \moodle_url('/local/bulkinstall/index.php'),
                get_string('menulabel', 'local_bulkinstall')),
            new \tabobject('format', new \moodle_url('/local/bulkinstall/bundleformat.php'),
                get_string('bundleformattitle', 'local_bulkinstall')),
            new \tabobject('licenses', new \moodle_url('/local/bulkinstall/activation.php'),
                get_string('licenseactivation', 'local_bulkinstall')),
        ];
        return \html_writer::div(print_tabs([$tabs], $active, null, null, true));
    }
}
