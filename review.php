<?php
/**
 * File documentation for review.php.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */
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
$selectionaction = optional_param('selectionaction', '', PARAM_ALPHA);
$plan = null;
if ($selectionaction !== '') {
    require_sesskey();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new invalid_parameter_exception('Selection changes require POST');
    }
    if ($selectionaction === 'apply') {
        try {
            $manager->exclude_blocked($storageid, required_param('selectiontoken', PARAM_ALPHANUM));
            redirect($PAGE->url);
        } catch (moodle_exception $exception) {
            if ($exception->errorcode !== 'selectionchanged') {
                throw $exception;
            }
            redirect($PAGE->url, get_string('selectionchanged', 'local_bulkinstall'), null,
                \core\output\notification::NOTIFY_ERROR);
        }
    } else if ($selectionaction === 'preview') {
        $plan = $manager->exclusion_plan($storageid);
    } else {
        throw new invalid_parameter_exception('Unknown selection action');
    }
}
$analysis = $manager->analyse($storageid);
if ($plan !== null) {
    $analysis = $plan['analysis'];
}
$PAGE->requires->css(new moodle_url('/local/bulkinstall/styles/review.css', [
    'rev' => get_config('local_bulkinstall', 'version'),
]));
$PAGE->requires->js_call_amd('local_bulkinstall/review', 'init');

echo $OUTPUT->header();
echo html_writer::start_div('local-bulkinstall-review');
echo $OUTPUT->heading(get_string('reviewtitle', 'local_bulkinstall'), 2);
echo html_writer::tag('p', get_string('reviewintro', 'local_bulkinstall')
    . ' ' . \local_bulkinstall\local\admin_help::icon('review'), ['class' => 'text-muted mb-4']);

if ($plan !== null) {
    echo html_writer::start_tag('section', [
        'class' => 'bi-selection-preview', 'aria-labelledby' => 'bi-selection-preview-heading',
    ]);
    echo html_writer::tag('h3', get_string('selectionpreview', 'local_bulkinstall'),
        ['id' => 'bi-selection-preview-heading']);
    echo html_writer::tag('p', get_string('selectionpreviewintro', 'local_bulkinstall'));
    echo html_writer::start_tag('ul', ['class' => 'mb-4']);
    foreach ($plan['removed'] as $package) {
        echo html_writer::tag('li', html_writer::tag('strong', s($package['filename']))
            . html_writer::alist(array_map('s', $package['errors'])));
    }
    echo html_writer::end_tag('ul');
}
if ($analysis['partial']) {
    echo html_writer::tag('p', get_string('selectionpartial', 'local_bulkinstall', count($analysis['excluded'])),
        ['class' => 'font-weight-bold']);
    if ($plan === null) {
        echo html_writer::start_tag('details', ['class' => 'mb-4']);
        echo html_writer::tag('summary', get_string('selectionexcluded', 'local_bulkinstall'));
        echo html_writer::alist(array_map(static fn(array $file): string => s($file['filename']), $analysis['excluded']));
        echo html_writer::end_tag('details');
    }
    if (is_array($analysis['bundle']['activation'] ?? null)) {
        echo html_writer::tag('p', get_string($analysis['activation'] === null ? 'selectionbundlemanual'
            : 'selectionbundleactivation', 'local_bulkinstall'), ['class' => 'text-muted mb-4']);
    }
}

if ($plan !== null) {
    echo html_writer::end_tag('section');
}

if ($analysis['bundle'] !== null) {
    $bundle = $analysis['bundle'];
    $bundletable = new html_table();
    $bundletable->attributes['class'] = 'generaltable local-bulkinstall-bundle mb-4';
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

$presentation = \local_bulkinstall\local\review_presentation::export($analysis);
if ($plan !== null && $plan['canapply']) {
    $presentation['headline'] = get_string('selectionpreviewready', 'local_bulkinstall');
    $presentation['groupmessage'] = get_string('selectionpreviewreadybody', 'local_bulkinstall');
}
echo $OUTPUT->render_from_template('local_bulkinstall/review', $presentation);

if ($plan !== null) {
    echo html_writer::start_div('bi-blocked-actions mb-4');
    if ($plan['canapply']) {
        echo $OUTPUT->single_button(new moodle_url('/local/bulkinstall/review.php', [
            'storage' => $storageid, 'selectionaction' => 'apply', 'selectiontoken' => $plan['token'],
            'sesskey' => sesskey(),
        ]), get_string('selectionconfirm', 'local_bulkinstall'), 'post');
    } else {
        echo html_writer::tag('p', get_string('selectionunavailable', 'local_bulkinstall'));
    }
    echo $OUTPUT->single_button(new moodle_url('/local/bulkinstall/install.php', [
        'storage' => $storageid, 'cancel' => 1, 'sesskey' => sesskey(),
    ]), get_string('cancel'), 'post');
    echo html_writer::end_div();
} else if ($analysis['caninstall']) {
    $confirmform = new \local_bulkinstall\form\confirm_form(
        new moodle_url('/local/bulkinstall/install.php'),
        ['storage' => $storageid, 'reviewtoken' => $manager->review_token($analysis)]
    );
    $confirmform->display();
} else {
    echo html_writer::start_div('bi-blocked-actions');
    echo html_writer::tag('button', get_string('installbatch', 'local_bulkinstall'),
        ['type' => 'button', 'class' => 'btn btn-primary', 'disabled' => 'disabled']);
    if (empty($analysis['batcherrors']) && array_filter($analysis['packages'],
            static fn(array $package): bool => !empty($package['errors']))) {
        echo $OUTPUT->single_button(new moodle_url('/local/bulkinstall/review.php', [
            'storage' => $storageid, 'selectionaction' => 'preview', 'sesskey' => sesskey(),
        ]), get_string('selectionexclude', 'local_bulkinstall'), 'post');
    }
    $discardurl = new moodle_url('/local/bulkinstall/install.php', [
        'storage' => $storageid,
        'cancel' => 1,
        'sesskey' => sesskey(),
    ]);
    echo $OUTPUT->single_button($discardurl, get_string('cancel'), 'post');
    echo html_writer::end_div();
}

echo html_writer::end_div();
echo $OUTPUT->footer();
