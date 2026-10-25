<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for lib.php.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */
defined('MOODLE_INTERNAL') || die();

/** Contextual help for the explicitly registered administration pages only. */
function local_bulkinstall_render_navbar_output(\renderer_base $renderer): string {
    return \local_bulkinstall\local\admin_help::navbar($renderer);
}
