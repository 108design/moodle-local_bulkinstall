<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$storageid = required_param('storage', PARAM_ALPHANUM);
$cancel = optional_param('cancel', false, PARAM_BOOL);
admin_externalpage_setup('local_bulkinstall');
require_capability('moodle/site:config', context_system::instance());
require_sesskey();

$manager = new \local_bulkinstall\local\staging_manager();
if ($cancel) {
    $manager->cleanup($storageid);
    redirect(new moodle_url('/local/bulkinstall/index.php'));
}
if (!empty($CFG->disableupdateautodeploy)) {
    throw new moodle_exception('featuredisabled', 'tool_installaddon');
}

\core_php_time_limit::raise(0);
$analysis = $manager->analyse($storageid);
if (!$analysis['caninstall']) {
    redirect(
        new moodle_url('/local/bulkinstall/review.php', ['storage' => $storageid]),
        get_string('batchchanged', 'local_bulkinstall'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

$installables = $manager->installables($analysis);
$components = array_column($analysis['packages'], 'component');
$activation = $analysis['bundle']['activation'] ?? null;
if (is_array($activation)) {
    // Persist only signed, validated coordination metadata. No ticket, key or account data is stored in a bundle.
    set_config('pendingjourney', json_encode([
        'bundleid' => (string) $analysis['bundle']['id'],
        'bundlename' => (string) $analysis['bundle']['name'],
        'bundleversion' => (string) $analysis['bundle']['version'],
        'storeproduct' => (string) $activation['storeproduct'],
        'entitlements' => array_values($activation['entitlements']),
        'timecreated' => time(),
    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), 'local_lmh108');
}
\local_bulkinstall\event\bundle_installation_started::create([
    'context' => context_system::instance(),
    'other' => [
        'filecount' => count($installables),
        'components' => implode(', ', $components),
    ],
])->trigger();

// The core helper redirects or terminates. Ensure staging is removed in every exit path.
register_shutdown_function(static function() use ($manager, $storageid): void {
    $manager->cleanup_directory($storageid);
});
$manager->forget($storageid);

require_once($CFG->libdir . '/upgradelib.php');
$PAGE->set_url(new moodle_url('/local/bulkinstall/install.php', ['storage' => $storageid]));
$PAGE->set_pagelayout('maintenance');
$PAGE->set_popup_notification_allowed(false);
$reviewurl = new moodle_url('/local/bulkinstall/review.php', ['storage' => $storageid]);
upgrade_install_plugins(
    $installables,
    true,
    get_string('installingbatch', 'local_bulkinstall'),
    null,
    $reviewurl
);
