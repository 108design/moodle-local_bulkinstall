<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace local_bulkinstall\task;

/**
 * Removes abandoned upload staging directories.
 *
 * @package    local_bulkinstall
 * @copyright  2026 Andreas Giesen <andreas@108design.com>
 * @license    See LICENSE.md for the full terms.
 */
class cleanup_staging extends \core\task\scheduled_task {
    /** @return string */
    public function get_name(): string {
        return get_string('taskcleanupstaging', 'local_bulkinstall');
    }

    /** Execute cleanup. */
    public function execute(): void {
        $removed = \local_bulkinstall\local\staging_manager::cleanup_expired();
        mtrace(get_string('taskcleanupresult', 'local_bulkinstall', $removed));
    }
}
