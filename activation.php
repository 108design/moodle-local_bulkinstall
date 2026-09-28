<?php
// Legacy bookmark: Bulk Installer no longer needs an account or activation.
require_once(__DIR__ . '/../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());
redirect(new moodle_url('/local/bulkinstall/index.php'));
