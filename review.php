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
admin_externalpage_setup('local_bulkinstall');
require_capability('moodle/site:config', context_system::instance());

if (!empty($CFG->disableupdateautodeploy)) {
    throw new moodle_exception('featuredisabled', 'tool_installaddon');
}

$PAGE->set_url(new moodle_url('/local/bulkinstall/review.php', ['storage' => $storageid]));
$PAGE->set_title(get_string('reviewtitle', 'local_bulkinstall'));
$PAGE->set_heading(get_string('pagetitle', 'local_bulkinstall'));

$manager = new \local_bulkinstall\local\staging_manager();
$analysis = $manager->analyse($storageid);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reviewtitle', 'local_bulkinstall'), 2);
echo html_writer::tag('p', get_string('reviewintro', 'local_bulkinstall'));

if ($analysis['bundle'] !== null) {
    $bundle = $analysis['bundle'];
    $bundletable = new html_table();
    $bundletable->attributes['class'] = 'generaltable local-bulkinstall-bundle';
    $bundletable->data = [
        [get_string('bundlearchive', 'local_bulkinstall'), s($bundle['archivefilename'])],
        [get_string('bundleid', 'local_bulkinstall'), s($bundle['id'])],
        [get_string('bundlename', 'local_bulkinstall'), s($bundle['name'])],
        [get_string('bundleversion', 'local_bulkinstall'), s($bundle['version'])],
    ];
    if ($bundle['description'] !== null && $bundle['description'] !== '') {
        $bundletable->data[] = [
            get_string('bundledescription', 'local_bulkinstall'),
            s($bundle['description']),
        ];
    }
    echo $OUTPUT->heading(get_string('bundledetected', 'local_bulkinstall'), 3);
    echo html_writer::table($bundletable);
    if (empty($bundle['publisherfilenamevalid'])) {
        echo $OUTPUT->notification(
            get_string('warningbundlepublisherfilename', 'local_bulkinstall', $bundle['archivefilename']),
            \core\output\notification::NOTIFY_WARNING
        );
    }
}

foreach ($analysis['batcherrors'] as $batcherror) {
    echo $OUTPUT->notification($batcherror, \core\output\notification::NOTIFY_ERROR);
}

$table = new html_table();
$table->attributes['class'] = 'generaltable local-bulkinstall-review';
$table->head = [
    get_string('filename', 'local_bulkinstall'),
    get_string('component', 'local_bulkinstall'),
    get_string('installedversion', 'local_bulkinstall'),
    get_string('packageversion', 'local_bulkinstall'),
    get_string('release', 'local_bulkinstall'),
    get_string('action', 'local_bulkinstall'),
    get_string('validation', 'local_bulkinstall'),
];

foreach ($analysis['packages'] as $package) {
    $details = [];
    foreach ($package['errors'] as $message) {
        $details[] = html_writer::span(get_string('errorprefix', 'local_bulkinstall', s($message)), 'text-danger');
    }
    foreach ($package['warnings'] as $message) {
        $details[] = html_writer::span(get_string('warningprefix', 'local_bulkinstall', s($message)), 'text-warning');
    }
    if (empty($details)) {
        $details[] = html_writer::span(get_string('validationok', 'local_bulkinstall'), 'text-success');
    }
    $table->data[] = [
        s($package['filename']),
        $package['component'] ? s($package['component']) : '—',
        $package['installedversion'] !== null ? (string)$package['installedversion'] : '—',
        $package['version'] !== null ? (string)$package['version'] : '—',
        $package['release'] !== null ? s($package['release']) : '—',
        get_string('action' . $package['action'], 'local_bulkinstall'),
        html_writer::alist($details),
    ];
}
echo html_writer::table($table);

$summary = (object)[
    'count' => count($analysis['packages']),
    'size' => display_size($analysis['totaluncompressed']),
];
echo html_writer::tag('p', get_string('reviewsummary', 'local_bulkinstall', $summary));

if ($analysis['caninstall']) {
    echo $OUTPUT->notification(get_string('sequentialwarning', 'local_bulkinstall'),
        \core\output\notification::NOTIFY_WARNING);
    $confirmform = new \local_bulkinstall\form\confirm_form(
        new moodle_url('/local/bulkinstall/install.php'),
        ['storage' => $storageid]
    );
    $confirmform->display();
} else {
    echo $OUTPUT->notification(get_string('batchblocked', 'local_bulkinstall'),
        \core\output\notification::NOTIFY_ERROR);
    $discardurl = new moodle_url('/local/bulkinstall/install.php', [
        'storage' => $storageid,
        'cancel' => 1,
        'sesskey' => sesskey(),
    ]);
    echo $OUTPUT->single_button($discardurl, get_string('discardbatch', 'local_bulkinstall'), 'post');
}

echo $OUTPUT->footer();
