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
require_once($CFG->libdir . '/filelib.php');

admin_externalpage_setup('local_bulkinstall');
require_capability('moodle/site:config', context_system::instance());

if (!empty($CFG->disableupdateautodeploy)) {
    throw new moodle_exception('featuredisabled', 'tool_installaddon');
}

$PAGE->set_url(new moodle_url('/local/bulkinstall/index.php'));
$PAGE->set_title(get_string('pagetitle', 'local_bulkinstall'));
$PAGE->set_heading(get_string('pagetitle', 'local_bulkinstall'));

$coremaxbytes = get_max_upload_file_size((int)($CFG->maxbytes ?? 0));
$maxbytes = min($coremaxbytes, \local_bulkinstall\local\staging_manager::MAX_BATCH_UNCOMPRESSED_BYTES);
$fileoptions = [
    'subdirs' => 0,
    'maxbytes' => $maxbytes,
    'maxfiles' => \local_bulkinstall\local\staging_manager::MAX_PACKAGES,
    'accepted_types' => ['.zip'],
    'areamaxbytes' => \local_bulkinstall\local\staging_manager::MAX_BATCH_UNCOMPRESSED_BYTES,
];
$draftitemid = file_get_submitted_draft_itemid('packages');
$form = new \local_bulkinstall\form\upload_form(null, ['fileoptions' => $fileoptions]);
$form->set_data((object)['packages' => $draftitemid]);

if ($data = $form->get_data()) {
    require_sesskey();
    \core_php_time_limit::raise(0);

    $usercontext = context_user::instance($USER->id);
    $files = get_file_storage()->get_area_files(
        $usercontext->id,
        'user',
        'draft',
        (int)$data->packages,
        'filename',
        false
    );
    $manager = new \local_bulkinstall\local\staging_manager();
    $storageid = $manager->stage($files);
    try {
        $analysis = $manager->analyse($storageid);
    } catch (Throwable $exception) {
        $manager->cleanup($storageid);
        throw $exception;
    }
    get_file_storage()->delete_area_files($usercontext->id, 'user', 'draft', (int)$data->packages);

    $components = array_filter(array_column($analysis['packages'], 'component'));
    \local_bulkinstall\event\bundle_validated::create([
        'context' => context_system::instance(),
        'other' => [
            'filecount' => count($analysis['packages']),
            'passed' => $analysis['caninstall'] ? 'yes' : 'no',
            'components' => implode(', ', $components),
        ],
    ])->trigger();

    redirect(new moodle_url('/local/bulkinstall/review.php', ['storage' => $storageid]));
}

echo $OUTPUT->header();
echo \local_bulkinstall\local\admin_navigation::render('install');
$pendingjourney = (string) get_config('local_lmh108', 'pendingjourney');
if ($pendingjourney !== '' && core_component::get_plugin_directory('local', 'lmh108') !== null) {
    echo $OUTPUT->notification(html_writer::link(new moodle_url('/local/lmh108/activate_account.php'),
        get_string('continuebundleactivation', 'local_bulkinstall'), ['class' => 'btn btn-primary']),
        \core\output\notification::NOTIFY_INFO);
}
echo $OUTPUT->heading(get_string('uploadheading', 'local_bulkinstall'), 2);
echo html_writer::tag('p', get_string('uploadintro', 'local_bulkinstall'));
echo html_writer::tag('p', html_writer::link(
    new moodle_url('/local/bulkinstall/bundleformat.php'),
    get_string('bundleformatlink', 'local_bulkinstall')
));
echo $OUTPUT->notification(get_string('backupnotice', 'local_bulkinstall'),
    \core\output\notification::NOTIFY_INFO);
$form->display();
echo $OUTPUT->footer();
