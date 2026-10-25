<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for classes/local/review_presentation.php.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen
 * @license   See LICENSE.md for the full terms.
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

/** Presentation only: never changes validation results or installation authority. */
final class review_presentation {
    /** A release belongs to the database build only if the code on disk matches it. */
    public static function installed_release(?object $info): string {
        if ($info === null || $info->versiondb === null || $info->versiondisk === null
                || (string)$info->versiondb !== (string)$info->versiondisk) {
            return '';
        }
        return trim((string)($info->release ?? ''));
    }

    /** Shared visual contract with Plugin Manager; missing metadata never invents a release. */
    public static function version(?string $label, ?int $build): array {
        $label = trim((string)$label);
        $human = $label !== '' && $label !== (string)$build;
        return [
            'label' => $human ? $label : ($build === null ? '—' : (string)$build),
            'build' => $human && $build !== null ? (string)$build : '',
            'fallback' => !$human,
            'note' => $build === null ? get_string('reviewnotinstalled', 'local_bulkinstall')
                : (!$human ? get_string('reviewbuild', 'local_bulkinstall') : ''),
        ];
    }

    /** Prioritise the first actual blocking result; never infer state from translated text. */
    public static function summary(array $package): string {
        $messages = $package['errors'] ?: $package['warnings'];
        if (!$messages) {
            return get_string('reviewnomessages', 'local_bulkinstall');
        }
        $message = reset($messages);
        foreach ($package['diagnostics'] ?? [] as $diagnostic) {
            if ($diagnostic['message'] === $message) {
                return get_string('reviewreason' . $diagnostic['code'], 'local_bulkinstall',
                    (object)($diagnostic['data'] ?? []));
            }
        }
        foreach ($package['messages'] ?? [] as $native) {
            if ($native['text'] === $message && in_array($native['code'] ?? '', ['targetexists', 'maturity'], true)) {
                return get_string('reviewreason' . $native['code'], 'local_bulkinstall');
            }
        }
        // Unrecognised diagnostics remain visible verbatim, wrapped rather than truncated.
        return $message;
    }

    /** Export escaped-by-template data for uploads, bundles and verified inline updates. */
    public static function export(array $analysis): array {
        global $CFG;
        $counts = ['all' => count($analysis['packages']), 'error' => 0, 'warning' => 0, 'ready' => 0];
        $rows = [];
        foreach ($analysis['packages'] as $index => $package) {
            $errors = count($package['errors']);
            $warnings = count($package['warnings']);
            $state = $errors ? 'error' : ($warnings ? 'warning' : 'ready');
            $counts[$state]++;
            $dependencies = [];
            foreach ($package['dependencies'] as $component => $required) {
                $dependencies[] = ['component' => $component,
                    'required' => $required ? (string)$required : get_string('reviewanyversion', 'local_bulkinstall')];
            }
            $messages = [];
            foreach (['errors' => 'error', 'warnings' => 'warning'] as $key => $tone) {
                foreach ($package[$key] as $text) {
                    $messages[] = ['text' => $text, 'tone' => $tone,
                        'prefix' => get_string($tone === 'error' ? 'reviewerror' : 'reviewwarning', 'local_bulkinstall')];
                }
            }
            // Include Core informational evidence as well, without duplicating warnings/errors.
            foreach ($package['messages'] ?? [] as $native) {
                if ($native['level'] === \core\update\validator::INFO) {
                    $messages[] = ['text' => $native['text'], 'tone' => 'info',
                        'prefix' => get_string('reviewinfo', 'local_bulkinstall')];
                }
            }
            $rows[] = [
                'id' => 'bi-package-' . $index, 'state' => $state,
                'name' => $package['displayname'] ?: ($package['component'] ?: $package['filename']),
                'component' => $package['component'] ?? '—', 'filename' => $package['filename'],
                'action' => get_string('action' . $package['action'], 'local_bulkinstall'),
                'actionicon' => ['install' => '+', 'update' => '↑', 'downgrade' => '↓', 'reinstall' => '↻'][$package['action']] ?? '?',
                'installed' => self::version($package['installedrelease'] ?? '', $package['installedversion']),
                'target' => self::version($package['selectedrelease'] ?? $package['release'], $package['version']),
                'packagerelease' => $package['release'] ?? '—',
                'symbol' => ['error' => '×', 'warning' => '!', 'ready' => '✓'][$state],
                'status' => get_string('reviewstate' . $state, 'local_bulkinstall'),
                'summary' => self::summary($package),
                'counttext' => $errors && $warnings ? get_string('reviewmessagecounts', 'local_bulkinstall',
                    (object)['errors' => $errors, 'warnings' => $warnings])
                    : ($errors || $warnings ? get_string($errors ? 'reviewerrorcount' : 'reviewwarningcount',
                        'local_bulkinstall', $errors ?: $warnings) : ''),
                'messages' => $messages, 'dependencies' => $dependencies,
                'targetpath' => $package['targetpath'] ?? '',
                'requires' => $package['requires'] ?? '',
                'supported' => $package['supported'] ? implode('–', $package['supported']) : '',
                'sha256' => $package['sha256'] ?? '',
            ];
        }
        $filters = [];
        foreach ($counts as $state => $count) {
            if ($state === 'all' || $count) {
                $filters[] = ['state' => $state, 'count' => $count, 'active' => $state === 'all',
                    'label' => get_string('reviewfilter' . $state, 'local_bulkinstall')];
            }
        }
        $caninstall = !empty($analysis['caninstall']);
        return [
            'rows' => $rows, 'filters' => $filters, 'caninstall' => $caninstall,
            'blocked' => !$caninstall, 'haswarnings' => $counts['warning'] > 0,
            'headline' => get_string($caninstall ? 'reviewgroupready' : 'reviewgroupblocked', 'local_bulkinstall'),
            'groupmessage' => get_string($caninstall ? 'reviewgroupreadybody'
                : ($counts['error'] ? 'reviewgroupblockedbody' : 'reviewgroupbatchblockedbody'),
                'local_bulkinstall', $counts['error']),
            'warningcount' => get_string('reviewwarningpackages', 'local_bulkinstall', $counts['warning']),
            'batcherrors' => array_map(static fn($text) => ['text' => $text], $analysis['batcherrors']),
            'source' => get_string($analysis['updates'] ? 'reviewsourceupdates'
                : ($analysis['bundle'] ? 'reviewsourcesbundle' : 'reviewsourceupload'), 'local_bulkinstall'),
            'firststep' => get_string($analysis['updates'] ? 'reviewstepmanager' : 'reviewstepselected', 'local_bulkinstall'),
            'meta' => get_string('reviewbatchmeta', 'local_bulkinstall', (object)[
                'count' => $counts['all'], 'size' => display_size($analysis['totaluncompressed']),
                'moodle' => $CFG->release,
            ]),
        ];
    }
}
