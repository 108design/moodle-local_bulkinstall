<?php
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_bulkinstall;

use local_bulkinstall\local\review_presentation as presentation;

/** Pure presentation cases: no database fixture reset or installation side effects. */
final class review_presentation_test extends \PHPUnit\Framework\TestCase {
    public function test_installed_release_never_labels_a_different_database_build(): void {
        $info = (object)['versiondb' => '2026101701', 'versiondisk' => 2026101701, 'release' => '1.6.0'];
        $this->assertSame('1.6.0', presentation::installed_release($info));
        $info->versiondisk++;
        $this->assertSame('', presentation::installed_release($info));
        $info->versiondb = null;
        $this->assertSame('', presentation::installed_release($info));
        $this->assertSame('', presentation::installed_release(null));
    }

    public function test_release_build_pairs_and_fallbacks_never_duplicate_metadata(): void {
        $pair = presentation::version(' 1.7.0 ', 2026101800);
        $this->assertSame('1.7.0', $pair['label']);
        $this->assertSame('2026101800', $pair['build']);
        $this->assertFalse($pair['fallback']);
        foreach (['', '2026101800'] as $label) {
            $fallback = presentation::version($label, 2026101800);
            $this->assertSame('2026101800', $fallback['label']);
            $this->assertSame('', $fallback['build']);
        }
        $this->assertSame('—', presentation::version(null, null)['label']);
    }

    private function package(array $changes = []): array {
        return array_replace(['displayname' => 'Example', 'component' => 'local_example', 'filename' => 'example.zip',
            'installedversion' => 2026101700, 'installedrelease' => '1.0.0', 'version' => 2026101800,
            'release' => '1.1.0', 'action' => 'update', 'errors' => [], 'warnings' => [], 'messages' => [],
            'dependencies' => [], 'requires' => 2024100700, 'supported' => [405, 503]], $changes);
    }

    private function analysis(array $packages, array $errors = []): array {
        return ['packages' => $packages, 'batcherrors' => $errors, 'caninstall' => !$errors
                && !array_filter($packages, static fn($p) => $p['errors']),
            'updates' => true, 'bundle' => null, 'totaluncompressed' => 100];
    }

    public function test_filter_counts_are_disjoint_and_do_not_change_group_authority(): void {
        $analysis = $this->analysis([$this->package(), $this->package(['warnings' => ['Warning']]),
            $this->package(['errors' => ['Error'], 'warnings' => ['Warning']])]);
        $view = presentation::export($analysis);
        $this->assertFalse($view['caninstall']);
        $this->assertSame(['all' => 3, 'error' => 1, 'warning' => 1, 'ready' => 1],
            array_column($view['filters'], 'count', 'state'));
        $this->assertSame(['ready', 'warning', 'error'], array_column($view['rows'], 'state'));
        $this->assertCount(2, $view['rows'][2]['messages']);
    }

    public function test_signed_selected_label_survives_handover_but_package_metadata_is_retained(): void {
        $package = $this->package(['selectedrelease' => 'Signed target', 'release' => 'Package release']);
        $row = presentation::export($this->analysis([$package]))['rows'][0];
        $this->assertSame('Signed target', $row['target']['label']);
        $this->assertSame('2026101800', $row['target']['build']);
        $this->assertSame('Package release', $row['packagerelease']);
    }

    public function test_unknown_blocking_error_cannot_be_hidden_by_recognised_warning(): void {
        $p = $this->package(['errors' => ['Unexpected integrity failure'], 'warnings' => ['Same build'],
            'diagnostics' => [['message' => 'Same build', 'code' => 'sameversion']]]);
        $this->assertSame('Unexpected integrity failure', presentation::summary($p));
        $p['errors'] = [];
        $this->assertSame(get_string('reviewreasonsameversion', 'local_bulkinstall'), presentation::summary($p));
    }

    public function test_batch_only_error_blocks_even_when_all_packages_are_ready(): void {
        $view = presentation::export($this->analysis([$this->package()], ['Signed evidence expired']));
        $this->assertFalse($view['caninstall']);
        $this->assertTrue($view['blocked']);
        $this->assertSame([['text' => 'Signed evidence expired']], $view['batcherrors']);
        $this->assertSame(get_string('reviewgroupbatchblockedbody', 'local_bulkinstall'), $view['groupmessage']);
    }
}
