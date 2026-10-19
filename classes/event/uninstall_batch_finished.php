<?php
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_bulkinstall\event;

/** Audit the explicit administrative uninstall batch without recording content or private file paths. */
class uninstall_batch_finished extends \core\event\base {
    protected function init(): void {
        $this->data['contextlevel'] = CONTEXT_SYSTEM;
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name(): string {
        return get_string('eventuninstallbatchfinished', 'local_bulkinstall');
    }

    public function get_description(): string {
        return "The user with id '{$this->userid}' finished an uninstall batch. Components: "
            . implode(', ', $this->other['components']) . '. State: ' . $this->other['state'] . '.';
    }
}
