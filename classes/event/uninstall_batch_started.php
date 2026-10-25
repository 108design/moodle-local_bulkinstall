<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for classes/event/uninstall_batch_started.php.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen
 * @license   See LICENSE.md for the full terms.
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_bulkinstall\event;

/** Audit the explicit administrative uninstall batch without recording content or private file paths. */
class uninstall_batch_started extends \core\event\base {
    protected function init(): void {
        $this->data['contextlevel'] = CONTEXT_SYSTEM;
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name(): string {
        return get_string('eventuninstallbatchstarted', 'local_bulkinstall');
    }

    public function get_description(): string {
        return "The user with id '{$this->userid}' started an uninstall batch. Components: "
            . implode(', ', $this->other['components']) . '. State: ' . $this->other['state'] . '.';
    }
}
