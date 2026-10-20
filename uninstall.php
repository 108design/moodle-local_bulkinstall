<?php
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
use local_bulkinstall\local\admin_help;
use local_bulkinstall\local\admin_navigation;
use local_bulkinstall\local\uninstall_manager;
use local_bulkinstall\local\uninstall_view;
use local_bulkinstall\local\uninstall_diagnostics;

// This standalone logger also observes failures before Moodle's normal bootstrap completes.
require_once(__DIR__ . '/classes/local/uninstall_diagnostics.php');
$requestid = uninstall_diagnostics::start();
if (!headers_sent()) {
    header('X-Bulk-Installer-Request-ID: ' . $requestid);
}
try {
    if (!defined('IGNORE_COMPONENT_CACHE')) {
        define('IGNORE_COMPONENT_CACHE', true);
    }
    require_once(__DIR__ . '/../../config.php');
    require_once($CFG->libdir . '/adminlib.php');


    uninstall_diagnostics::phase('access');
    admin_externalpage_setup('local_bulkinstall_uninstall');
    uninstall_manager::require_access();
    $PAGE->set_url(new moodle_url('/local/bulkinstall/uninstall.php'));
    $PAGE->set_title(get_string('uninstalltab', 'local_bulkinstall'));
    $PAGE->set_cacheable(false);
    $PAGE->set_heading(get_string('pagetitle', 'local_bulkinstall'));
    $PAGE->requires->css(new moodle_url('/local/bulkinstall/styles/review.css'));
    $PAGE->requires->css(new moodle_url('/local/bulkinstall/styles/uninstall.css'));
    $PAGE->requires->js_call_amd('local_bulkinstall/uninstall', 'init');

    $manager = new uninstall_manager();
    $action = optional_param('action', '', PARAM_ALPHA);
    uninstall_diagnostics::phase($action !== '' ? $action : 'view');
    $preview = null;
    $receipt = null;
    $proposal = false;
    $completion = null;
    $history = optional_param('results', false, PARAM_BOOL);
    if ($action !== '') {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            throw new invalid_parameter_exception('Uninstaller actions require POST');
        }
        require_sesskey();
        switch ($action) {
            case 'preview':
                $preview = $manager->prepare(optional_param_array('components', [], PARAM_COMPONENT),
                    optional_param('removefiles', false, PARAM_BOOL));
                break;
            case 'exclude':
                $preview = $manager->exclusion_preview();
                $proposal = true;
                break;
            case 'apply':
                $preview = $manager->apply_exclusion(required_param('token', PARAM_ALPHANUM));
                break;
            case 'cancel':
                $manager->cancel();
                uninstall_diagnostics::finish();
                redirect($PAGE->url);
                break;
            case 'recheck':
            case 'finish':
                $completion = $manager->completion(required_param('receipt', PARAM_ALPHANUM));
                if ($action === 'finish' && $completion['ready']) {
                    uninstall_diagnostics::finish();
                    redirect(new moodle_url('/admin/index.php'));
                }
                $receipt = $completion['receipt'];
                break;
            case 'execute':
                // Required acknowledgements are checked server-side, never just in browser JavaScript.
                if (!optional_param('acknowledge', false, PARAM_BOOL)) {
                    throw new moodle_exception('uninstallackrequired', 'local_bulkinstall');
                }
                $token = required_param('token', PARAM_ALPHANUM);
                $previous = $manager->receipt();
                if ($previous && hash_equals($previous['id'], substr(hash('sha256', $token), 0, 32))) {
                    uninstall_diagnostics::finish();
                    redirect(new moodle_url($PAGE->url, ['completion' => 1]));
                }
                uninstall_diagnostics::phase('confirmation');
                $manager->confirmed_plan($token);
                uninstall_diagnostics::phase('render');
                echo $OUTPUT->header();
                echo admin_navigation::render('uninstall');
                echo html_writer::start_div('local-bulkinstall-review local-bulkinstall-uninstall');
                echo $OUTPUT->heading(get_string('uninstallprogress', 'local_bulkinstall'), 2);
                $bar = new progress_bar('bi-uninstall-progress', 500, true);
                $receipt = $manager->execute($token, static function(int $done, int $total, string $component) use ($bar): void {
                    $bar->update($done, $total, s($component));
                });
                $bar->update(count($receipt['results']), count($receipt['order']),
                    get_string('uninstallresult' . $receipt['state'], 'local_bulkinstall'));
                uninstall_diagnostics::phase('render');
                echo uninstall_view::results($receipt);
                echo uninstall_view::completion($manager->completion($receipt['id']));
                echo html_writer::end_div();
                echo $OUTPUT->footer();
                uninstall_diagnostics::finish();
                exit;
            default:
                throw new invalid_parameter_exception('Unknown uninstall action');
        }
    }
    if ($history && $action === '') {
        $receipt = $manager->receipt();
    } else if (optional_param('completion', false, PARAM_BOOL) && $action === '') {
        $completion = $manager->completion();
        $receipt = $completion['receipt'];
    }
    uninstall_diagnostics::phase('render');
    echo $OUTPUT->header();
    echo admin_navigation::render('uninstall');
    echo html_writer::start_div('local-bulkinstall-review local-bulkinstall-uninstall');
    if ($history && $action === '') {
        echo $OUTPUT->heading(get_string('uninstallhistoryheading', 'local_bulkinstall'), 2);
        echo $receipt !== null ? uninstall_view::results($receipt)
            : $OUTPUT->notification(get_string('uninstallreceiptunavailable', 'local_bulkinstall'),
                \core\output\notification::NOTIFY_WARNING, false);
    } else if ($receipt !== null) {
        echo $OUTPUT->heading(get_string('uninstallcompletionheading', 'local_bulkinstall'), 2);
        echo uninstall_view::results($receipt);
        echo uninstall_view::completion($completion);
    } else {
        echo $OUTPUT->heading(get_string('uninstallheading', 'local_bulkinstall'), 2);
        echo html_writer::tag('p', get_string('uninstallintro', 'local_bulkinstall') . ' ' . admin_help::icon('uninstall'),
            ['class' => 'text-muted mb-4']);
        if ($preview !== null) {
            echo uninstall_view::preview($preview, $proposal);
        } else {
            echo uninstall_view::selection($manager->inventory());
            if ($manager->receipt() !== null) {
                echo html_writer::link(new moodle_url($PAGE->url, ['results' => 1]),
                    get_string('uninstalllastresult', 'local_bulkinstall'), ['class' => 'd-inline-block mt-4']);
            }
        }
    }

    echo html_writer::end_div();
    echo $OUTPUT->footer();

    uninstall_diagnostics::finish();
} catch (Throwable $exception) {
    uninstall_diagnostics::exception($exception);
    throw $exception; // Preserve Moodle's normal error handling and every existing safety check.
}
