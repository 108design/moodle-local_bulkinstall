<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace local_bulkinstall\event;

/**
 * Event emitted immediately before the batch is handed to Moodle core.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
class bundle_installation_started extends \core\event\base {
    /** Initialise event metadata. */
    protected function init(): void {
        $this->data['contextlevel'] = CONTEXT_SYSTEM;
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /** @return string */
    public static function get_name(): string {
        return get_string('eventbundleinstallationstarted', 'local_bulkinstall');
    }

    /** @return string */
    public function get_description(): string {
        return "The user with id '{$this->userid}' started deployment of {$this->other['filecount']} plugin packages. " .
            "Components: {$this->other['components']}.";
    }
}
