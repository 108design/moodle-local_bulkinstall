<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_bulkinstall\local\license_state;

require_login();
require_capability('moodle/site:config', context_system::instance());
admin_externalpage_setup('local_bulkinstall_activation');
$url = new moodle_url('/local/bulkinstall/activation.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('licenseactivation', 'local_bulkinstall'));
$PAGE->set_heading(get_string('pluginname', 'local_bulkinstall'));
$PAGE->requires->css(new moodle_url('/local/bulkinstall/styles.css', [
    'v' => (int) get_config('local_bulkinstall', 'version'),
]));
$accountlinked = optional_param('accountlinked', 0, PARAM_BOOL);
$message = '';
$type = \core\output\notification::NOTIFY_SUCCESS;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_sesskey();
    try {
        $action = required_param('action', PARAM_ALPHA);
        if ($action === 'start') {
            license_state::start_site_link();
            $message = get_string('sitelinkstarted', 'local_bulkinstall');
        } else if ($action === 'poll') {
            $result = license_state::poll_site_link();
            $message = ($result['status'] ?? '') === 'approved'
                ? get_string('licenseactivated', 'local_bulkinstall')
                : get_string('sitelinkpending', 'local_bulkinstall');
        } else if ($action === 'manual') {
            license_state::activate(required_param('licensekey', PARAM_RAW_TRIMMED),
                required_param('environment', PARAM_ALPHA));
            $message = get_string('licenseactivated', 'local_bulkinstall');
        } else if ($action === 'refresh') {
            license_state::refresh_if_due(true);
            $message = get_string('licenserefreshed', 'local_bulkinstall');
        } else if ($action === 'deactivate') {
            license_state::deactivate();
            $message = get_string('licensedeactivated', 'local_bulkinstall');
        }
    } catch (Throwable $exception) {
        $message = clean_param($exception->getMessage(), PARAM_TEXT);
        $type = \core\output\notification::NOTIFY_ERROR;
    }
}
$active = license_state::is_active();
$pendingcode = (string) get_config('local_bulkinstall', 'sitelinkcode');
$pendingurl = (string) get_config('local_bulkinstall', 'sitelinkurl');
$pendingexpires = (int) get_config('local_bulkinstall', 'sitelinkexpires');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('licenseactivation', 'local_bulkinstall'), 2);
if ($message !== '') {
    echo $OUTPUT->notification(s($message), $type);
} else if ($accountlinked && !$active) {
    echo $OUTPUT->notification(get_string('sitelinkaccountconnected', 'local_bulkinstall'), 'info');
}
echo html_writer::div(get_string($active ? 'licensestatusactive' : 'licensestatusinactive', 'local_bulkinstall'),
    'alert ' . ($active ? 'alert-success' : 'alert-warning'));
if ($active) {
    $actions = $OUTPUT->single_button(new moodle_url($url, ['action' => 'refresh']),
        get_string('refreshlicense', 'local_bulkinstall'), 'post')
        . html_writer::link(new moodle_url('/local/bulkinstall/index.php'),
            get_string('openbulkinstall', 'local_bulkinstall'), ['class' => 'btn btn-primary'])
        . $OUTPUT->single_button(new moodle_url($url, ['action' => 'deactivate']),
        get_string('deactivatelicense', 'local_bulkinstall'), 'post', [
            'type' => \core\output\single_button::BUTTON_SECONDARY,
        ]);
    echo html_writer::div($actions, 'local-bulkinstall-actions');
} else {
    echo html_writer::tag('p', get_string('sitelinkintro', 'local_bulkinstall'));
    if ($pendingcode !== '' && $pendingurl !== '' && $pendingexpires >= time()) {
        echo html_writer::tag('p', get_string('sitelinkcode', 'local_bulkinstall',
            html_writer::tag('code', s($pendingcode))));
        echo html_writer::tag('p', get_string('sitelinkstep2', 'local_bulkinstall'));
        $actions = html_writer::link($pendingurl, get_string('opensitelink', 'local_bulkinstall'), [
                'class' => 'btn btn-primary', 'target' => '_blank', 'rel' => 'noopener noreferrer',
            ])
            . $OUTPUT->single_button(new moodle_url($url, ['action' => 'poll']),
                get_string('checksitelink', 'local_bulkinstall'), 'post');
        echo html_writer::div($actions, 'local-bulkinstall-actions');
    } else {
        echo $OUTPUT->single_button(new moodle_url($url, ['action' => 'start']),
            get_string('connectaccount', 'local_bulkinstall'), 'post');
    }
    $manual = html_writer::tag('summary', get_string('manualactivation', 'local_bulkinstall'))
        . html_writer::start_tag('form', ['method' => 'post', 'action' => $url->out(false), 'class' => 'mt-3'])
        . html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()])
        . html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'manual'])
        . html_writer::tag('label', get_string('licensekey', 'local_bulkinstall'), ['for' => 'id_licensekey'])
        . html_writer::empty_tag('input', ['type' => 'password', 'name' => 'licensekey', 'id' => 'id_licensekey',
            'class' => 'form-control mb-3', 'required' => 'required', 'autocomplete' => 'off'])
        . html_writer::select([
            'production' => get_string('environmentproduction', 'local_bulkinstall'),
            'development' => get_string('environmentdevelopment', 'local_bulkinstall'),
        ], 'environment', license_state::default_environment(), false, ['class' => 'form-control mb-3'])
        . html_writer::tag('button', get_string('activateplugin', 'local_bulkinstall'),
            ['type' => 'submit', 'class' => 'btn btn-outline-primary'])
        . html_writer::end_tag('form');
    echo html_writer::tag('details', $manual, ['class' => 'mt-4']);
}
echo $OUTPUT->footer();
