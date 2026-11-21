<?php
// This file is part of Moodle - https://moodle.org/
// @copyright 2026 Andreas Giesen <andreas@108design.com>
// @license 108design source-available software license; see LICENSE.md
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md for the full terms.
/**
 * Package update information and compatibility.
 *
 * @package local_bulkinstall
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license See LICENSE.md for the containing product terms.
 */
namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

/** Compatibility for an old cached hook registration; intentionally passive. */
final class update_guard {
    /** Allow old cached callbacks to finish without changing the native request. */
    public static function after_config(\core\hook\after_config $hook): void {
    }
}
