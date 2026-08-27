<?php
namespace local_bulkinstall\task;

defined('MOODLE_INTERNAL') || die();

final class refresh_license extends \core\task\scheduled_task {
    public function get_name(): string {
        return get_string('taskrefreshlicense', 'local_bulkinstall');
    }

    public function execute(): void {
        try {
            \local_bulkinstall\local\license_state::refresh_if_due();
        } catch (\Throwable $exception) {
            mtrace('Bulk installation licence refresh: ' . $exception->getMessage());
        }
    }
}
