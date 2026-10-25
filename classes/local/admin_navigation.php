<?php
/**
 * File documentation for classes/local/admin_navigation.php.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>

namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

/** Bulk Installer is free to use without account registration or activation. */
final class admin_navigation {
    public static function render(string $active): string {
        $installlabel = get_string('menulabel', 'local_bulkinstall');
        $formatlabel = get_string('bundleformattitle', 'local_bulkinstall');
        $tabs = [
            new \tabobject('install', new \moodle_url('/local/bulkinstall/index.php'),
                self::tab_label('fa-upload', $installlabel), $installlabel),
            new \tabobject('format', new \moodle_url('/local/bulkinstall/bundleformat.php'),
                self::tab_label('fa-file-code', $formatlabel), $formatlabel),
        ];
        if (has_capability('local/bulkinstall:uninstall', \context_system::instance())) {
            $label = get_string('uninstalltab', 'local_bulkinstall');
            $tabs[] = new \tabobject('uninstall', new \moodle_url('/local/bulkinstall/uninstall.php'),
                self::tab_label('fa-trash', $label), $label);
        }
        // Keep the active parent linked on preview/results pages as well.
        foreach ($tabs as $tab) {
            $tab->linkedwhenselected = true;
        }
        return \html_writer::div(print_tabs([$tabs], $active, null, null, true));
    }

    private static function tab_label(string $icon, string $label): string {
        return \html_writer::tag('i', '', ['class' => 'fa ' . $icon . ' icon', 'aria-hidden' => 'true'])
            . ' ' . s($label);
    }
}
