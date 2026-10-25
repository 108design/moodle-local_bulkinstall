<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for activation.php.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */
// Legacy bookmark: Bulk Installer no longer needs an account or activation.
require_once(__DIR__ . '/../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());
redirect(new moodle_url('/local/bulkinstall/index.php'));
