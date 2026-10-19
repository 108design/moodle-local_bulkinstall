<?php
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

/** Native form controls with the established compact BI review layout. */
final class uninstall_view {
    private static function text(string $key, $data = null): string {
        return get_string('uninstall' . $key, 'local_bulkinstall', $data);
    }

    private static function start_form(string $action, string $extra = ''): string {
        return \html_writer::start_tag('form', ['method' => 'post',
            'action' => (new \moodle_url('/local/bulkinstall/uninstall.php'))->out(false)])
            . self::hidden('sesskey', sesskey()) . self::hidden('action', $action) . $extra;
    }

    private static function hidden(string $name, string $value): string {
        return \html_writer::empty_tag('input', ['type' => 'hidden', 'name' => $name, 'value' => $value]);
    }

    private static function button(string $text, string $class = 'btn-primary', array $attributes = []): string {
        return \html_writer::tag('button', $text, $attributes + ['type' => 'submit', 'class' => 'btn ' . $class]);
    }

    private static function action(string $action, string $label, string $class = 'btn-secondary', string $token = ''): string {
        return self::start_form($action, $token === '' ? '' : self::hidden('token', $token))
            . self::button($label, $class) . \html_writer::end_tag('form');
    }

    /** Selection filters change visibility only; the full selected set always reaches review. */
    public static function selection(array $inventory): string {
        $types = [];
        $rows = '';
        foreach ($inventory as $component => $row) {
            if ($row['standard'] || $row['versiondb'] === null) {
                continue;
            }
            $types[$row['type']] = \core_plugin_manager::instance()->plugintype_name_plural($row['type']);
            $reason = $row['blockers'] ? self::error(['code' => $row['blockers'][0],
                'component' => $component, 'detail' => '']) : '';
            $id = 'bi-uninstall-' . $component;
            $attrs = ['type' => 'checkbox', 'id' => $id, 'name' => 'components[]', 'value' => $component,
                'data-uninstall-select' => '', 'aria-describedby' => $id . '-status'];
            if ($reason !== '') {
                $attrs['disabled'] = 'disabled';
            }
            $label = \html_writer::tag('label', s($row['name']), ['for' => $id, 'class' => 'mb-0']);
            $identity = \html_writer::div(\html_writer::empty_tag('input', $attrs) . $label, 'bu-choice')
                . \html_writer::tag('code', s($component));
            $meta = self::version($row)
                . \html_writer::tag('p', self::usage($row), ['class' => 'bi-meta mt-2']);
            $status = $reason ?: self::text($row['status'] === 'missing' ? 'missing' : 'candidate');
            $body = \html_writer::div($identity, 'bi-identity') . \html_writer::div($meta, 'bi-version')
                . \html_writer::div(s($status), 'bi-result', ['id' => $id . '-status']);
            $rows .= \html_writer::div(\html_writer::div($body, 'bi-rowhead'),
                'bi-row ' . ($reason ? 'bi-error' : 'bi-ready'), [
                    'data-uninstall-row' => '', 'data-type' => $row['type'], 'data-status' => $row['status'],
                    'data-search' => \core_text::strtolower($row['name'] . ' ' . $component),
                ]);
        }
        asort($types);
        $search = \html_writer::tag('label', self::text('search'), ['for' => 'bu-search'])
            . \html_writer::empty_tag('input', ['type' => 'search', 'id' => 'bu-search', 'class' => 'form-control',
                'data-uninstall-search' => '']);
        $type = \html_writer::tag('label', self::text('type'), ['for' => 'bu-type'])
            . \html_writer::select(['' => self::text('alltypes')] + $types, 'filtertype', '', false,
                ['id' => 'bu-type', 'data-uninstall-type' => '', 'class' => 'form-select custom-select']);
        $state = \html_writer::tag('label', self::text('state'), ['for' => 'bu-state'])
            . \html_writer::select(['' => self::text('allstates'), 'uptodate' => self::text('installed'),
                'missing' => self::text('missing')], 'filterstate', '', false,
                ['id' => 'bu-state', 'data-uninstall-state' => '', 'class' => 'form-select custom-select']);
        $filters = \html_writer::div(\html_writer::div($search) . \html_writer::div($type)
            . \html_writer::div($state), 'bu-filters', ['data-uninstall-filters' => '', 'hidden' => 'hidden']);
        $tools = \html_writer::div(self::button(self::text('selectvisible'), 'btn-secondary',
                ['type' => 'button', 'data-uninstall-all' => ''])
            . self::button(self::text('clear'), 'btn-secondary', ['type' => 'button', 'data-uninstall-clear' => ''])
            . \html_writer::tag('span', '', ['data-uninstall-count' => '', 'aria-live' => 'polite',
                'data-template' => self::text('count')]), 'bi-blocked-actions',
                ['data-uninstall-tools' => '', 'hidden' => 'hidden']);
        $files = \html_writer::div(self::hidden('removefiles', '0') . \html_writer::tag('label',
            \html_writer::empty_tag('input', ['type' => 'checkbox', 'name' => 'removefiles', 'value' => '1',
                'checked' => 'checked']) . ' ' . self::text('removefiles'), ['class' => 'mb-0'])
            . ' ' . admin_help::icon('uninstallfiles'), 'bu-choice mb-4');
        return self::start_form('preview') . $filters . $tools
            . \html_writer::tag('p', self::text('filterhelp'), ['class' => 'bi-safety mb-3'])
            . ($rows ?: \html_writer::tag('p', self::text('empty')))
            . \html_writer::tag('p', self::text('nomatches'), ['hidden' => 'hidden', 'data-uninstall-empty' => ''])
            . \html_writer::div($files . self::button(self::text('review')), 'bi-footer')
            . \html_writer::end_tag('form');
    }

    /** All affected rows, native warnings, usage, code removal and execution order are visible. */
    public static function preview(array $preview, bool $proposal): string {
        global $OUTPUT;
        $plan = $preview['plan'];
        $html = '';
        if ($proposal) {
            $html .= \html_writer::div(\html_writer::tag('h3', self::text('exclusionheading'))
                . \html_writer::tag('p', self::text('exclusionintro'))
                . self::excluded($preview['excluded']), 'bi-selection-preview');
        } else if (!empty($preview['excluded'])) {
            $html .= \html_writer::tag('details', \html_writer::tag('summary', self::text('excluded'))
                . self::excluded($preview['excluded']), ['class' => 'mb-4']);
        }
        $html .= \html_writer::div(\html_writer::tag('strong', self::text($plan['canuninstall'] ? ($proposal ? 'proposalready' : 'ready') : 'blocked'))
            . \html_writer::tag('p', self::text('affectedcount', count($plan['affected']))),
                'bi-group mb-4' . ($plan['canuninstall'] ? '' : ' bi-group-blocked'));
        foreach ($plan['errors'] as $error) {
            $html .= $OUTPUT->notification(s(self::error($error)), \core\output\notification::NOTIFY_ERROR, false);
        }
        foreach ($plan['rows'] as $row) {
            $native = trim(html_to_text($row['warning'], 0));
            $identity = \html_writer::tag('h3', s($row['name'])) . \html_writer::tag('code', s($row['component']));
            if ($row['owner'] !== $row['component']) {
                $identity .= \html_writer::tag('p', s(self::text('child', $row['owner'])), ['class' => 'bi-meta']);
            }
            $files = !$row['files']['present'] ? 'filesabsent'
                : ($plan['removefiles'] && $row['removable'] ? 'filesauto' : 'filesmanual');
            $body = \html_writer::div($identity, 'bi-identity')
                . \html_writer::div(self::version($row)
                    . \html_writer::tag('p', self::usage($row), ['class' => 'bi-meta mt-2']), 'bi-version')
                . \html_writer::div(self::text($files), 'bi-result');
            $detail = $native === '' ? '' : \html_writer::div(s($native), 'bi-details pb-3');
            $html .= \html_writer::div(\html_writer::div($body, 'bi-rowhead') . $detail,
                'bi-row ' . ($row['blockers'] ? 'bi-error' : 'bi-warning'));
        }
        $html .= \html_writer::tag('p', self::text('usagelimit'), ['class' => 'bi-safety mt-3 mb-4']);
        if ($plan['order']) {
            $html .= \html_writer::tag('h3', self::text('order'), ['class' => 'h5 mt-4'])
                . \html_writer::alist(array_map('s', $plan['order']), [], 'ol');
        }
        $html .= $OUTPUT->notification(self::text('consequences'),
            \core\output\notification::NOTIFY_WARNING, false);
        if ($proposal) {
            $html .= \html_writer::div(($plan['canuninstall']
                ? self::action('apply', self::text('applyexclusion'), 'btn-primary', $preview['token']) : '')
                . self::action('cancel', get_string('cancel')), 'bi-blocked-actions');
        } else if ($plan['canuninstall']) {
            $html .= self::start_form('execute', self::hidden('token', $preview['token']))
                . \html_writer::div(\html_writer::tag('label', \html_writer::empty_tag('input', [
                    'type' => 'checkbox', 'name' => 'acknowledge', 'value' => '1', 'required' => 'required',
                ]) . ' ' . self::text('acknowledge'), ['class' => 'mb-0']), 'bu-choice mb-4')
                . self::button(self::text('confirm', count($plan['affected'])), 'btn-danger')
                . \html_writer::end_tag('form')
                . \html_writer::div(self::action('cancel', get_string('cancel')), 'mt-3');
        } else {
            $html .= \html_writer::div(self::button(self::text('confirm', count($plan['affected'])), 'btn-danger',
                    ['disabled' => 'disabled', 'type' => 'button'])
                . ($plan['selected'] ? self::action('exclude', self::text('exclude')) : '')
                . self::action('cancel', get_string('cancel')), 'bi-blocked-actions');
        }
        return $html;
    }

    private static function excluded(array $excluded): string {
        $items = [];
        foreach ($excluded as $component => $errors) {
            $items[] = \html_writer::tag('strong', s($component))
                . \html_writer::alist(array_map(static fn(array $error): string => s(self::error($error)), $errors));
        }
        return \html_writer::alist($items);
    }

    private static function version(array $row): string {
        $pair = review_presentation::version($row['release'], $row['versiondb']);
        return \html_writer::tag('strong', s($pair['label']), ['class' => $pair['fallback'] ? 'bi-build-only' : ''])
            . ($pair['build'] === '' ? '' : \html_writer::tag('small', s($pair['build'])));
    }

    private static function usage(array $row): string {
        $items = [];
        foreach ($row['usage'] as $key => $count) {
            $items[] = self::text($key, $count);
        }
        return $items ? s(implode(' · ', $items)) : self::text('usageunknown');
    }

    private static function error(array $error): string {
        return self::text('error' . $error['code'], (object)[
            'component' => $error['component'], 'detail' => $error['detail'],
        ]);
    }

    /** A result never claims rollback or database/file atomicity. */
    public static function results(array $receipt): string {
        global $OUTPUT;
        $html = $OUTPUT->notification(self::text('result' . $receipt['state']),
            $receipt['state'] === 'complete' ? \core\output\notification::NOTIFY_SUCCESS
                : \core\output\notification::NOTIFY_WARNING, false);
        $done = [];
        foreach ($receipt['results'] as $result) {
            $done = array_merge($done, $result['affected']);
            $html .= \html_writer::div(\html_writer::tag('h3', s($result['component']), ['class' => 'h5'])
                . \html_writer::tag('p', self::text('step' . $result['status']))
                . \html_writer::alist(array_map('s', $result['affected']))
                . (empty($result['manual']) ? '' : \html_writer::tag('p', self::text('manualcomponents',
                    s(implode(', ', $result['manual']))))), 'bi-selection-preview');
        }
        $remaining = array_diff($receipt['affected'], $done);
        if ($remaining) {
            $html .= \html_writer::tag('h3', self::text('notrun'), ['class' => 'h5'])
                . \html_writer::alist(array_map('s', $remaining));
        }
        if (!empty($receipt['current'])) {
            $html .= \html_writer::tag('p', self::text('uncertain', s($receipt['current'])));
        }
        return $html;
    }
    /** Only the current completion stage offers an action; history is always read-only. */
    public static function completion(array $state): string {
        $html = \html_writer::tag('h3', self::text('nextstep') . ' ' . admin_help::icon('uninstallfiles'), ['class' => 'h5 mt-4']);
        if ($state['pending']) {
            $items = [];
            foreach ($state['pending'] as $component => $path) {
                $items[] = s($component) . ': ' . \html_writer::tag('code', s($path));
            }
            $html .= \html_writer::tag('p', self::text('remainingfiles')) . \html_writer::alist($items)
                . \html_writer::tag('p', self::text('manualnotice'));
        }
        if ($state['uncertain']) {
            $html .= \html_writer::tag('p', self::text('completionuncertain'), ['class' => 'bi-safety']);
        } else if ($state['ready']) {
            $html .= \html_writer::tag('p', self::text('finishnotice'));
        }
        return $html . self::start_form($state['ready'] ? 'finish' : 'recheck',
                self::hidden('receipt', $state['receipt']['id']))
            . self::button(self::text($state['ready'] ? 'finish' : 'recheck'))
            . \html_writer::end_tag('form');
    }

}
