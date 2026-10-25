<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for classes/local/uninstall_plan.php.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen
 * @license   See LICENSE.md for the full terms.
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

/** Pure dependency planning. The complete affected set is always returned for confirmation. */
final class uninstall_plan {
    /** @return array{selected: array, affected: array, order: array, errors: array} */
    public static function build(array $inventory, array $selected): array {
        $selected = array_values(array_unique($selected));
        sort($selected, SORT_STRING);
        $errors = [];
        if (!$selected || count($selected) > 50) {
            $errors[] = ['code' => 'selection', 'component' => '', 'detail' => ''];
        }
        $closures = [];
        foreach ($selected as $component) {
            $closures[$component] = self::closure($inventory, $component, []);
        }
        // A selected child is handled by its selected parent, never uninstalled twice.
        $roots = $selected;
        foreach ($selected as $component) {
            foreach ($closures as $parent => $children) {
                if ($parent !== $component && in_array($component, $children, true)) {
                    $roots = array_values(array_diff($roots, [$component]));
                    break;
                }
            }
        }
        $owners = [];
        foreach ($roots as $root) {
            foreach ($closures[$root] as $component) {
                if (isset($owners[$component]) && $owners[$component] !== $root) {
                    $errors[] = ['code' => 'cycle', 'component' => $component, 'detail' => ''];
                }
                $owners[$component] = $root;
                foreach ($inventory[$component]['blockers'] ?? ['unknown'] as $reason) {
                    $errors[] = ['code' => $reason, 'component' => $component, 'detail' => ''];
                }
            }
        }
        // Even malformed/cyclic ownership metadata must never silently drop a selected component.
        foreach ($selected as $component) {
            if (!isset($owners[$component])) {
                $errors[] = ['code' => 'cycle', 'component' => $component, 'detail' => ''];
            }
        }
        $edges = array_fill_keys($roots, []);
        foreach ($inventory as $component => $info) {
            foreach ($info['requires'] as $required) {
                if (!isset($owners[$required])) {
                    continue;
                }
                if (!isset($owners[$component])) {
                    $errors[] = ['code' => 'required', 'component' => $required, 'detail' => $component];
                } else if ($owners[$component] !== $owners[$required]) {
                    // Requiring plugins have to disappear BEFORE the dependency.
                    $edges[$owners[$component]][$owners[$required]] = true;
                }
            }
        }
        $order = [];
        while ($edges) {
            $incoming = [];
            foreach ($edges as $targets) {
                $incoming += $targets;
            }
            $ready = array_values(array_diff(array_keys($edges), array_keys($incoming)));
            sort($ready, SORT_STRING);
            if (!$ready) {
                foreach (array_keys($edges) as $component) {
                    $errors[] = ['code' => 'cycle', 'component' => $component, 'detail' => ''];
                }
                break;
            }
            foreach ($ready as $component) {
                $order[] = $component;
                unset($edges[$component]);
            }
        }
        if (!$roots && $selected) {
            $errors[] = ['code' => 'cycle', 'component' => '', 'detail' => ''];
        }
        ksort($owners, SORT_STRING);
        return ['selected' => $selected, 'affected' => $owners, 'order' => $order, 'errors' => $errors];
    }

    /** Return transitive subplugins, including the requested component, with a recursion guard. */
    private static function closure(array $inventory, string $component, array $visited): array {
        if (isset($visited[$component])) {
            return [];
        }
        $visited[$component] = true;
        $result = [$component];
        foreach ($inventory[$component]['children'] ?? [] as $child) {
            $result = array_merge($result, self::closure($inventory, $child, $visited));
        }
        return array_values(array_unique($result));
    }
}
