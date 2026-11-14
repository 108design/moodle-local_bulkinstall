<?php
// This file is part of Moodle - https://moodle.org/
// @copyright 2026 Andreas Giesen <andreas@108design.com>
// @license 108design source-available software license; see LICENSE.md
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md for the full terms.
namespace local_bulkinstall\local;
defined('MOODLE_INTERNAL') || die();

/** Early self-protection is independent of account connection and activation. */
final class update_guard {
    public static function after_config(\core\hook\after_config $hook): void {
        if (!class_exists(\design108\update_guard\v1\guard::class, false)) {
            require_once(__DIR__ . '/../../update-guard.php');
        }
        \design108\update_guard\v1\guard::enter('local_bulkinstall', dirname(__DIR__, 2));
    }
}
