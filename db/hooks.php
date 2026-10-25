<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for db/hooks.php.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */
defined('MOODLE_INTERNAL') || die();

$callbacks = [];

// Decorative product identity is restricted to the native administration surfaces.
$callbacks[] = [
    'hook' => \core\hook\output\before_standard_head_html_generation::class,
    'callback' => [\local_bulkinstall\local\admin_branding::class, 'head'],
];
$callbacks[] = [
    'hook' => \core\hook\output\before_http_headers::class,
    'callback' => [\local_bulkinstall\local\admin_branding::class, 'prepare'],
];

$callbacks[] = [
    'hook' => \core\hook\output\before_http_headers::class,
    'callback' => [\local_bulkinstall\local\admin_help::class, 'prepare'],
];
$callbacks[] = [
    'hook' => \core\hook\output\before_standard_footer_html_generation::class,
    'callback' => [\local_bulkinstall\local\admin_help::class, 'footer'],
];
