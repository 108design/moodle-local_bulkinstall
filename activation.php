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
$confirmdeactivate = optional_param('confirmdeactivate', 0, PARAM_BOOL);
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
            if (!$confirmdeactivate) {
                throw new invalid_parameter_exception('Explicit deactivation confirmation is required.');
            }
            license_state::deactivate();
            $message = get_string('licensedeactivated', 'local_bulkinstall');
        }
    } catch (Throwable $exception) {
        $message = clean_param($exception->getMessage(), PARAM_TEXT);
        $type = \core\output\notification::NOTIFY_ERROR;
    }
}
$active = license_state::is_active();
$hubstate = '\\local_lmh108\\local\\license_state';
$hubinstalled = class_exists($hubstate) && method_exists($hubstate, 'record');
$hubrecord = $hubinstalled ? $hubstate::record(\local_bulkinstall\local\license_config::FEATURE_CODE) : null;
$hubmanaged = $hubrecord && (!method_exists($hubstate, 'delegation_available')
    || $hubstate::delegation_available());
$pendingcode = (string) get_config('local_bulkinstall', 'sitelinkcode');
$pendingurl = (string) get_config('local_bulkinstall', 'sitelinkurl');
$pendingexpires = (int) get_config('local_bulkinstall', 'sitelinkexpires');

echo $OUTPUT->header();
echo \local_bulkinstall\local\admin_navigation::render('licenses');
echo $OUTPUT->heading(get_string('licenseactivation', 'local_bulkinstall'), 2);
if ($message !== '') {
    echo $OUTPUT->notification(s($message), $type);
} else if ($accountlinked && !$active) {
    echo $OUTPUT->notification(get_string('sitelinkaccountconnected', 'local_bulkinstall'), 'info');
}
if ($hubmanaged) {
    $licenseintro = html_writer::tag('p', get_string('licensestatushub', 'local_bulkinstall'),
        ['class' => 'mb-0']);
    $licenseintro .= html_writer::link(new moodle_url('/local/lmh108/index.php'),
        get_string('openlicensemanager', 'local_bulkinstall'),
        ['class' => 'btn btn-outline-secondary btn-sm mt-3']);
    echo html_writer::div($licenseintro, 'alert alert-info', ['role' => 'status']);
}
$claims = null;
try {
    $claims = $active ? license_state::claims(true) : null;
} catch (Throwable) {
    // The visible state remains fail-closed when the signed assertion cannot be read.
}
$productlabel = class_exists('\\local_lmh108\\local\\feature_names')
    ? \local_lmh108\local\feature_names::licence_label(
        \local_bulkinstall\local\license_config::FEATURE_CODE,
        get_string('pluginname', 'local_bulkinstall'))
    : get_string('pluginname', 'local_bulkinstall');
$authority = $active
    ? get_string($hubmanaged ? 'licenseauthorityhub' : 'licenseauthorityplugin', 'local_bulkinstall')
    : '—';
$actions = '';
if ($hubmanaged) {
    $actions = html_writer::link(new moodle_url('/local/lmh108/index.php'),
        html_writer::tag('i', '', ['class' => 'fa fa-external-link icon', 'aria-hidden' => 'true'])
            . html_writer::span(get_string('openlicensemanager', 'local_bulkinstall'), 'sr-only'), [
            'class' => 'btn btn-link local-bulkinstall-license-action p-0',
            'title' => get_string('openlicensemanager', 'local_bulkinstall'),
            'aria-label' => get_string('openlicensemanager', 'local_bulkinstall'),
            'data-toggle' => 'tooltip',
            'data-placement' => 'top',
        ]);
} else if ($active) {
    $actions = $OUTPUT->single_button(new moodle_url($url, ['action' => 'refresh']),
        get_string('refreshlicense', 'local_bulkinstall'), 'post');
    if ($hubinstalled) {
        $actions .= html_writer::link(new moodle_url('/local/lmh108/adopt.php', [
                'gate' => 'local_bulkinstall/install',
                'feature' => \local_bulkinstall\local\license_config::FEATURE_CODE,
            ]), get_string('managewithlicensemanager', 'local_bulkinstall'), ['class' => 'btn btn-sm btn-secondary']);
    }
    $actions .= html_writer::link(new moodle_url($url, ['confirmdeactivate' => 1]),
        get_string('deactivatelicense', 'local_bulkinstall'), ['class' => 'btn btn-sm btn-outline-danger']);
} else if ($hubrecord || $hubinstalled) {
    $actions = html_writer::link(new moodle_url('/local/lmh108/index.php', [], 'free-activation'),
        get_string($hubrecord ? 'completehandover' : 'activateinlicensemanager', 'local_bulkinstall'),
        ['class' => 'btn btn-sm btn-primary']);
} else if ($pendingcode !== '' && $pendingurl !== '' && $pendingexpires >= time()) {
    $actions = html_writer::link($pendingurl, get_string('opensitelink', 'local_bulkinstall'),
        ['class' => 'btn btn-sm btn-primary'])
        . $OUTPUT->single_button(new moodle_url($url, ['action' => 'poll']),
            get_string('checksitelink', 'local_bulkinstall'), 'post');
} else {
    $actions = $OUTPUT->single_button(new moodle_url($url, ['action' => 'start']),
        get_string('connectaccount', 'local_bulkinstall'), 'post');
}
$table = new html_table();
$table->attributes['class'] = 'generaltable table table-bordered w-auto local-bulkinstall-compact-table '
    . 'local-bulkinstall-compact-action-table local-bulkinstall-license-table';
$table->head = [get_string('licenseproduct', 'local_bulkinstall'), get_string('status'),
    get_string('licenseauthority', 'local_bulkinstall'), get_string('actions')];
$table->align = ['', '', '', 'center'];
$table->data = [[format_string($productlabel),
    html_writer::span(
        html_writer::tag('i', '', ['class' => 'fa ' . ($active ? 'fa-circle-check' : 'fa-circle-xmark')
            . ' icon', 'aria-hidden' => 'true'])
            . html_writer::span(get_string($active ? 'licensestatusactivatedshort'
                : 'licensestatusnotactivatedshort', 'local_bulkinstall')),
        'badge rounded-pill badge-' . ($active ? 'success' : 'warning')),
    $authority, $actions]];
echo html_writer::div(html_writer::table($table), 'table-responsive local-bulkinstall-license-table-wrap');
if ($active && $confirmdeactivate && !$hubmanaged) {
    echo $OUTPUT->confirm(get_string('licensepolicydeactivation', 'local_bulkinstall'),
        new moodle_url($url, ['action' => 'deactivate', 'confirmdeactivate' => 1, 'sesskey' => sesskey()]), $url);
}
if (!$active && !$hubrecord && !$hubinstalled) {
    echo html_writer::tag('p', get_string('sitelinkintro', 'local_bulkinstall'));
    if ($pendingcode !== '' && $pendingurl !== '' && $pendingexpires >= time()) {
        echo html_writer::tag('p', get_string('sitelinkcode', 'local_bulkinstall',
            html_writer::tag('code', s($pendingcode))));
        echo html_writer::tag('p', get_string('sitelinkstep2', 'local_bulkinstall'));
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
