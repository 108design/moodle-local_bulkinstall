<?php
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_bulkinstall;

use local_bulkinstall\local\uninstall_plan;

/** Pure dependency planning: no database fixtures or Moodle state resets. */
final class uninstall_plan_test extends \PHPUnit\Framework\TestCase {
    private function plugin(array $requires = [], array $children = [], array $blockers = []): array {
        return ['requires' => $requires, 'children' => $children, 'blockers' => $blockers];
    }

    public function test_requiring_plugin_is_removed_before_its_dependency(): void {
        $p = uninstall_plan::build(['local_a' => $this->plugin(), 'local_b' => $this->plugin(['local_a'])],
            ['local_a', 'local_b']);
        $this->assertSame(['local_b', 'local_a'], $p['order']);
        $this->assertSame([], $p['errors']);
    }

    public function test_unselected_dependent_blocks_and_is_never_added(): void {
        $p = uninstall_plan::build(['local_a' => $this->plugin(), 'local_b' => $this->plugin(['local_a'])], ['local_a']);
        $this->assertSame(['local_a'], $p['selected']);
        $this->assertSame(['code' => 'required', 'component' => 'local_a', 'detail' => 'local_b'], $p['errors'][0]);
    }

    public function test_subplugins_are_included_and_selected_child_is_not_run_twice(): void {
        $p = uninstall_plan::build(['local_a' => $this->plugin([], ['child_b']),
            'child_b' => $this->plugin(['local_a'])], ['local_a', 'child_b']);
        $this->assertSame(['local_a'], $p['order']);
        $this->assertSame(['child_b' => 'local_a', 'local_a' => 'local_a'], $p['affected']);
        $this->assertSame([], $p['errors']);
    }

    public function test_external_dependent_of_child_blocks_parent(): void {
        $p = uninstall_plan::build(['local_a' => $this->plugin([], ['child_b']),
            'child_b' => $this->plugin(), 'local_c' => $this->plugin(['child_b'])], ['local_a']);
        $this->assertSame('child_b', $p['errors'][0]['component']);
        $this->assertSame('local_c', $p['errors'][0]['detail']);
    }

    public function test_standard_self_and_unknown_components_cannot_bypass_selection_ui(): void {
        $p = uninstall_plan::build(['mod_forum' => $this->plugin([], [], ['standard']),
            'local_bulkinstall' => $this->plugin([], [], ['self'])],
            ['mod_forum', 'local_bulkinstall', 'local_unknown']);
        $this->assertSame(['self', 'unknown', 'standard'], array_column($p['errors'], 'code'));
    }

    public function test_protected_child_blocks_entire_parent(): void {
        $p = uninstall_plan::build(['local_a' => $this->plugin([], ['child_b']),
            'child_b' => $this->plugin([], [], ['standard'])], ['local_a']);
        $this->assertSame('standard', $p['errors'][0]['code']);
        $this->assertSame('child_b', $p['errors'][0]['component']);
    }

    public function test_cycles_are_blocked_but_independent_steps_are_identifiable(): void {
        $p = uninstall_plan::build(['local_a' => $this->plugin(['local_b']),
            'local_b' => $this->plugin(['local_a']), 'local_c' => $this->plugin()], ['local_a','local_b','local_c']);
        $this->assertSame(['local_c'], $p['order']);
        $this->assertSame(['local_a','local_b'], array_column($p['errors'], 'component'));
    }

    public function test_duplicates_are_normalised_and_empty_and_oversized_selections_are_blocked(): void {
        $this->assertCount(1, uninstall_plan::build(['local_a' => $this->plugin()], ['local_a','local_a'])['selected']);
        $this->assertSame('selection', uninstall_plan::build([], [])['errors'][0]['code']);
        $this->assertSame('selection', uninstall_plan::build([], array_map(fn($i) => 'local_a'.$i, range(1,51)))['errors'][0]['code']);
    }

    public function test_invalid_parent_cycles_never_silently_drop_selected_plugins(): void {
        $p = uninstall_plan::build(['local_a' => $this->plugin([], ['local_b']),
            'local_b' => $this->plugin([], ['local_a']), 'local_c' => $this->plugin()],
            ['local_a', 'local_b', 'local_c']);
        $this->assertSame(['local_a', 'local_b'], array_column($p['errors'], 'component'));
        $this->assertSame(['local_c'], $p['order']);
    }

}
