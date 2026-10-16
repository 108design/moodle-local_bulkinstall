<?php
defined('MOODLE_INTERNAL') || die();

/** Contextual help for the explicitly registered administration pages only. */
function local_bulkinstall_render_navbar_output(\renderer_base $renderer): string {
    return \local_bulkinstall\local\admin_help::navbar($renderer);
}
