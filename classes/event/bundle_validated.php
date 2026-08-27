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
 * Event emitted after a newly uploaded batch has been preflighted.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
class bundle_validated extends \core\event\base {
    /**
     * Initialise event metadata.
     */
    protected function init(): void {
        $this->data['contextlevel'] = CONTEXT_SYSTEM;
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Return the event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventbundlevalidated', 'local_bulkinstall');
    }

    /**
     * Describe the event.
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id '{$this->userid}' validated {$this->other['filecount']} plugin packages; " .
            "the batch passed: {$this->other['passed']}. Components: {$this->other['components']}.";
    }
}
