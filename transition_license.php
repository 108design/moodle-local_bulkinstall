<?php
// This file is part of Moodle - https://moodle.org/
// @copyright 2026 Andreas Giesen <andreas@108design.com>
// @license 108design source-available software license; see LICENSE.md
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md for the full terms.
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$storageid = required_param('storage', PARAM_ALPHANUM);
$component = required_param('component', PARAM_COMPONENT);
admin_externalpage_setup('local_bulkinstall');
require_capability('moodle/site:config', context_system::instance());
if (!empty($CFG->disableupdateautodeploy)) {
    throw new moodle_exception('featuredisabled', 'tool_installaddon');
}
$reviewurl = new moodle_url('/local/bulkinstall/review.php', ['storage' => $storageid]);
$PAGE->set_url(new moodle_url('/local/bulkinstall/transition_license.php', ['storage' => $storageid, 'component' => $component]));
$manager = new \local_bulkinstall\local\staging_manager();
$analysis = $manager->analyse($storageid);
$transition = null;
foreach ($analysis['packages'] as $package) {
    if ($package['component'] === $component) { $transition = $package['transition'] ?? null; }
}
if ($transition === null) { throw new moodle_exception('errorupdateguard', 'local_bulkinstall', $reviewurl, $component); }
$freecore = in_array($component, ['local_frontpage', 'local_storefront'], true);
$companion = $component === 'enrol_storefront';
$form = new \local_bulkinstall\form\transition_license_form(null, [
    'freecore' => $freecore,
    'storage' => $storageid, 'component' => $component, 'reviewtoken' => $manager->review_token($analysis),
]);
if ($form->is_cancelled()) { redirect($reviewurl); }
$error = '';
$ready = (int)get_config('local_lmh108', 'version') >= 2026110501
    && preg_match('/^[a-f0-9]{64}$/D', (string)get_config('local_lmh108', 'installationid')) === 1
    && in_array(get_config('local_lmh108', 'siteenvironment'), ['production', 'development'], true)
    && \local_lmh108\local\license_state::delegation_available();
if ($ready && !$companion && ($data = $form->get_data())) {
    require_sesskey();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
            || !hash_equals($manager->review_token($analysis), $data->reviewtoken)) {
        redirect($reviewurl, get_string('batchchanged', 'local_bulkinstall'));
    }
    try {
        $hub = \local_lmh108\local\license_state::class;
        $existing = $hub::record($transition['feature']);
        $owner = in_array($component, ['local_frontpage', 'mod_frontpage'], true) ? 'local_frontpage' : ($companion ? 'local_storefront' : $component);
        if ($existing && $existing->sourcecomponent !== $owner) {
            throw new moodle_exception('errortransitionlicence', 'local_bulkinstall');
        }
        if ($freecore) {
            \local_bulkinstall\local\free_core_transition::claim_core($transition);
        } else {
            $hub::activate($data->licencekey, $transition['feature'], $hub::require_site_environment(),
                $owner, (string)$transition['targetversion']);
        }
        unset($data->licencekey);
        redirect($reviewurl);
    } catch (\Throwable) {
        // Never echo a provider response or a submitted bearer key into the page or logs.
        unset($data->licencekey);
        if (!$freecore) { $form->clear_submitted_key(); }
        $error = get_string('transitionactivationfailed', 'local_bulkinstall');
    }
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($freecore || $companion
    ? 'frontpagefreepreparetitle' : 'transitionpreparelicence', 'local_bulkinstall'));
echo html_writer::tag('p', get_string($freecore || $companion
    ? 'frontpagefreeprepare' : 'transitionactivationintro', 'local_bulkinstall'));
echo \local_bulkinstall\local\admin_help::icon('review');
if ($companion) { echo $OUTPUT->notification(get_string('storefrontcompanionconsequences', 'local_bulkinstall'), 'info'); }
if ($error !== '') { echo $OUTPUT->notification($error); }
if ($ready && !$companion && !$transition['ready']) { $form->display(); }
if (!$ready) {
    echo $OUTPUT->notification(get_string('transitionmanagerrequired', 'local_bulkinstall'));
    if (get_config('local_lmh108', 'version')) {
        echo html_writer::link(new moodle_url('/local/lmh108/index.php'), get_string('transitionmanager', 'local_bulkinstall'));
    }
}
if (($freecore || $companion) && get_config('local_lmh108', 'version')) {
    echo html_writer::tag('p', html_writer::link(new moodle_url('/local/lmh108/index.php'),
        get_string('transitionmanager', 'local_bulkinstall')));
}
echo html_writer::tag('p', html_writer::link($reviewurl, get_string('back')));
echo $OUTPUT->footer();
