<?php
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

/** Explicit, session-owned uninstall plans and bounded execution receipts. No automatic retries. */
final class uninstall_manager {
    public const TTL = 3600;

    /** Recheck permissions and server policy at every entry, including execution. */
    public static function require_access(): void {
        global $CFG;
        require_capability('moodle/site:config', \context_system::instance());
        require_capability('local/bulkinstall:uninstall', \context_system::instance());
        if (!empty($CFG->disableupdateautodeploy) || !empty($CFG->uninstallclionly)
                || !empty($CFG->upgraderunning)) {
            throw new \moodle_exception('uninstallpolicy', 'local_bulkinstall');
        }
    }

    /** Installed extension catalogue; unknown/standard origin is never treated as contributed. */
    public function inventory(): array {
        global $CFG, $DB;
        \core_plugin_manager::reset_caches();
        $pm = \core_plugin_manager::instance();
        $inventory = [];
        $parents = [];
        // Newer Core hides deprecated plugin types by default; their dependency declarations still matter.
        $parameters = (new \ReflectionMethod($pm, 'get_plugins'))->getNumberOfParameters();
        $groups = $parameters === 0 ? $pm->get_plugins() : $pm->get_plugins(true);
        foreach ($groups as $plugins) {
            foreach ($plugins as $info) {
                $component = $info->component;
                $parents[$component] = $info->get_parent_plugin();
                $blockers = [];
                if ($component === 'local_bulkinstall') {
                    $blockers[] = 'self';
                }
                if (!$this->is_extension($info)) {
                    $blockers[] = 'standard';
                }
                if (!in_array($info->get_status(), [$pm::PLUGIN_STATUS_UPTODATE, $pm::PLUGIN_STATUS_MISSING], true)
                        || $info->versiondb === null) {
                    $blockers[] = 'pending';
                }
                if (!$info->is_uninstall_allowed() || method_exists($info, 'get_uninstall_url')
                        || (method_exists($info, 'is_deprecated') && $info->is_deprecated())) {
                    $blockers[] = 'native';
                }
                $usage = [];
                if ($info->type === 'mod') {
                    $usage['activities'] = $DB->count_records_sql('SELECT COUNT(cm.id)
                          FROM {course_modules} cm JOIN {modules} m ON m.id = cm.module WHERE m.name = ?', [$info->name]);
                } else if ($info->type === 'block') {
                    $usage['blocks'] = $DB->count_records('block_instances', ['blockname' => $info->name]);
                }
                $children = array_keys($pm->get_subplugins_of_plugin($component));
                sort($children, SORT_STRING);
                $requires = array_keys($info->get_other_required_plugins());
                sort($requires, SORT_STRING);
                $inventory[$component] = [
                    'component' => $component,
                    'name' => preg_match('/^\[.*\]$/', $info->displayname) ? $component : $info->displayname,
                    'release' => review_presentation::installed_release($info), 'type' => $info->type,
                    'standard' => in_array('standard', $blockers, true), 'versiondb' => $info->versiondb,
                    'versiondisk' => $info->versiondisk, 'status' => $info->get_status(),
                    'rootdir' => $info->rootdir, 'children' => $children, 'requires' => $requires,
                    'blockers' => $blockers, 'usage' => $usage, 'warning' => $info->get_uninstall_extra_warning(),
                ];
            }
        }
        // Core's ordinary child list excludes deprecated types on newer Moodle versions.
        // Their files still live below the parent: include them so protection and review cover the full tree.
        foreach ($parents as $component => $parent) {
            if ($parent && isset($inventory[$parent])) {
                $inventory[$parent]['children'][] = $component;
                $inventory[$parent]['children'] = array_values(array_unique($inventory[$parent]['children']));
                sort($inventory[$parent]['children'], SORT_STRING);
            }
        }
        ksort($inventory, SORT_STRING);
        return $inventory;
    }

    /** Custom subplugin types may have no direct source flag; verify their actual parent recursively. */
    private function is_extension(\core\plugininfo\base $info, array $visited = []): bool {
        $pm = \core_plugin_manager::instance();
        if (isset($visited[$info->component]) || $info->is_standard()
                || $pm::is_deleted_standard_plugin($info->type, $info->name)) {
            return false;
        }
        if ($info->source === $pm::PLUGIN_SOURCE_EXTENSION) {
            return true;
        }
        $visited[$info->component] = true;
        $parent = $info->get_parent_plugin();
        $parentinfo = $parent ? $pm->get_plugin_info($parent) : null;
        return $parentinfo !== null && $this->is_extension($parentinfo, $visited);
    }

    /** Fresh full plan. File paths/digests and usage counts are bound to the confirmation. */
    public function analyse(array $selected, bool $removefiles): array {
        $inventory = $this->inventory();
        $plan = uninstall_plan::build($inventory, $selected);
        $pm = \core_plugin_manager::instance();
        $rows = [];
        foreach ($plan['affected'] as $component => $owner) {
            if (!isset($inventory[$component])) {
                continue;
            }
            $row = $inventory[$component];
            $row['owner'] = $owner;
            $row['files'] = $this->files($row['rootdir']);
            if (!$row['files']['safe']) {
                $plan['errors'][] = ['code' => 'path', 'component' => $component, 'detail' => ''];
            }
            $row['removable'] = $row['files']['present'] && $row['files']['safe']
                && $pm->is_plugin_folder_removable($component) && !$pm->plugin_external_source($component)
                && !$pm->plugin_external_source($owner);
            $rows[$component] = $row;
        }
        $plan['rows'] = $rows;
        $plan['removefiles'] = $removefiles;
        $plan['canuninstall'] = !$plan['errors'];
        // Include all dependency declarations and installed versions, not only selected rows.
        $state = $plan;
        $state['rows'] = $this->confirmation_rows($rows);
        $plan['fingerprint'] = hash('sha256', json_encode([$this->confirmation_rows($inventory), $state],
            JSON_THROW_ON_ERROR));
        return $plan;
    }

    /** Compare warning content, excluding random form IDs and other presentation-only HTML attributes. */
    private function confirmation_rows(array $rows): array {
        foreach ($rows as &$row) {
            $row['warning'] = trim(html_to_text($row['warning'], 0));
        }
        unset($row);
        return $rows;
    }

    /** Store one latest preview per administrator session, with a short-lived one-use secret. */
    public function prepare(array $selected, bool $removefiles): array {
        global $SESSION;
        self::require_access();
        $plan = $this->analyse($selected, $removefiles);
        $SESSION->local_bulkinstall_uninstall = [
            'token' => bin2hex(random_bytes(32)), 'created' => time(), 'plan' => $plan,
        ];
        return $SESSION->local_bulkinstall_uninstall;
    }

    /** Cancelling a preview has no effect on code, installed data, or an existing receipt. */
    public function cancel(): void {
        global $SESSION;
        unset($SESSION->local_bulkinstall_uninstall);
    }

    /** Validate one exact preview; no changed-state or token failure may cause a partial run. */
    public function confirmed_plan(string $token): array {
        global $SESSION;
        self::require_access();
        $preview = $SESSION->local_bulkinstall_uninstall ?? null;
        if (!$preview || time() - $preview['created'] > self::TTL
                || !hash_equals($preview['token'], $token)) {
            throw new \moodle_exception('uninstallstale', 'local_bulkinstall');
        }
        if (is_file($this->receipt_directory() . '/' . substr(hash('sha256', $token), 0, 32) . '.json')) {
            throw new \moodle_exception('uninstallstale', 'local_bulkinstall');
        }
        $fresh = $this->analyse($preview['plan']['selected'], $preview['plan']['removefiles']);
        if (!$fresh['canuninstall'] || !hash_equals($preview['plan']['fingerprint'], $fresh['fingerprint'])) {
            throw new \moodle_exception('uninstallstale', 'local_bulkinstall');
        }
        return $fresh;
    }

    /** Proposed blocker exclusion reaches a fixed point and never adds another component. */
    public function exclusion_preview(): array {
        global $SESSION;
        self::require_access();
        $preview = $this->preview();
        $original = $this->analyse($preview['plan']['selected'], $preview['plan']['removefiles']);
        $selected = $original['selected'];
        $excluded = $preview['excluded'] ?? [];
        $reduced = $original;
        do {
            $before = $selected;
            foreach ($reduced['errors'] as $error) {
                $owner = $reduced['affected'][$error['component']] ?? $error['component'];
                foreach ($selected as $component) {
                    if (($reduced['affected'][$component] ?? $component) === $owner) {
                        $excluded[$component][] = $error;
                        $selected = array_values(array_diff($selected, [$component]));
                    }
                }
            }
            $reduced = $this->analyse($selected, $original['removefiles']);
        } while ($before !== $selected && $selected && $reduced['errors']);
        $proposal = ['token' => bin2hex(random_bytes(32)), 'created' => time(), 'plan' => $reduced,
            'excluded' => $excluded, 'originalfingerprint' => $original['fingerprint']];
        $SESSION->local_bulkinstall_uninstall['exclusion'] = $proposal;
        return $proposal;
    }

    /** Apply only the exact, freshly rechecked reduced-selection proposal. */
    public function apply_exclusion(string $token): array {
        global $SESSION;
        self::require_access();
        $preview = $this->preview();
        $proposal = $preview['exclusion'] ?? null;
        if (!$proposal || time() - $proposal['created'] > self::TTL || !hash_equals($proposal['token'], $token)) {
            throw new \moodle_exception('uninstallstale', 'local_bulkinstall');
        }
        $original = $this->analyse($preview['plan']['selected'], $preview['plan']['removefiles']);
        $reduced = $this->analyse($proposal['plan']['selected'], $proposal['plan']['removefiles']);
        if (!$reduced['canuninstall'] || !hash_equals($proposal['originalfingerprint'], $original['fingerprint'])
                || !hash_equals($proposal['plan']['fingerprint'], $reduced['fingerprint'])) {
            throw new \moodle_exception('uninstallstale', 'local_bulkinstall');
        }
        $result = $this->prepare($reduced['selected'], $reduced['removefiles']);
        $result['excluded'] = $proposal['excluded'];
        $SESSION->local_bulkinstall_uninstall = $result;
        return $result;
    }

    /** Retrieve a preview without changing its original selection or confirmation authority. */
    public function preview(): array {
        global $SESSION;
        $preview = $SESSION->local_bulkinstall_uninstall ?? null;
        if (!$preview || time() - $preview['created'] > self::TTL) {
            throw new \moodle_exception('uninstallstale', 'local_bulkinstall');
        }
        return $preview;
    }

    /**
     * Serial execution through Core, sharing BI's deployment lock. A failed/uncertain step stops the run.
     * Receipts are persisted BEFORE destructive calls, so a crashed request cannot replay confirmation.
     */
    public function execute(string $token, ?callable $progress = null): array {
        global $SESSION, $USER;
        self::require_access();
        uninstall_diagnostics::phase('lock');
        $lock = \core\lock\lock_config::get_lock_factory('local_bulkinstall')->get_lock('install_batch', 0);
        if (!$lock) {
            throw new \moodle_exception('errorinstallbusy', 'local_bulkinstall');
        }
        try {
            uninstall_diagnostics::phase('confirmation');
            $plan = $this->confirmed_plan($token);
            $receipt = [
                'id' => substr(hash('sha256', $token), 0, 32), 'created' => time(),
                'state' => 'running', 'selected' => $plan['selected'], 'affected' => array_keys($plan['affected']),
                'order' => $plan['order'],
                'paths' => array_column($plan['rows'], 'rootdir', 'component'),
                'results' => [], 'current' => null,
            ];
            $this->save_receipt($receipt);
            $SESSION->local_bulkinstall_uninstall_receipt = ['id' => $receipt['id'], 'userid' => (int)$USER->id];
            $this->cancel();
            \core_php_time_limit::raise(0);
            $finished = false;
            uninstall_diagnostics::executing(true);
            register_shutdown_function(function() use (&$receipt, &$finished): void {
                if (!$finished) {
                    $receipt['state'] = 'interrupted';
                    try {
                        $this->save_receipt($receipt);
                    } catch (\Throwable $exception) {
                        uninstall_diagnostics::exception($exception, true);
                    }
                }
            });
            uninstall_diagnostics::phase('batch_event');
            \local_bulkinstall\event\uninstall_batch_started::create([
                'context' => \context_system::instance(),
                'other' => ['components' => $receipt['affected'], 'state' => 'running'],
            ])->trigger();
            foreach ($plan['order'] as $position => $component) {
                $receipt['current'] = $component;
                $this->save_receipt($receipt);
                $buffers = ob_get_level();
                $group = [];
                $removedcode = false;
                try {
                    uninstall_diagnostics::phase('step_check', $component);
                    self::require_access();
                    $pm = \core_plugin_manager::instance();
                    \core_plugin_manager::reset_caches();
                    if (!$pm->can_uninstall_plugin($component)) {
                        throw new \moodle_exception('uninstallblocked', 'local_bulkinstall');
                    }
                    $group = array_filter($plan['rows'], static fn(array $row): bool => $row['owner'] === $component);
                    // Recheck remaining group payloads and usage immediately before each destructive call.
                    $fresh = $this->analyse([$component], $plan['removefiles']);
                    if (!$fresh['canuninstall'] || $this->confirmation_rows($fresh['rows']) !== $this->confirmation_rows($group)) {
                        throw new \moodle_exception('uninstallstale', 'local_bulkinstall');
                    }
                    uninstall_diagnostics::phase('progress', $component);
                    if ($progress) {
                        $progress($position, count($plan['order']), $component);
                    }
                    $trace = new \progress_trace_buffer(new \null_progress_trace(), true);
                    uninstall_diagnostics::phase('native_uninstall', $component);
                    $ok = $pm->uninstall_plugin($component, $trace);
                    uninstall_diagnostics::phase('verify_uninstall', $component);
                    $trace->finished();
                    \core_plugin_manager::reset_caches();
                    if (!$ok || !$this->uninstalled(array_keys($group))) {
                        throw new \moodle_exception('uninstallincomplete', 'local_bulkinstall');
                    }
                    // Remove child code before its parent. Never delete a still-installed component.
                    $files = array_keys($group);
                    usort($files, static fn(string $a, string $b): int =>
                        strlen((string)$group[$b]['rootdir']) <=> strlen((string)$group[$a]['rootdir']));
                    $manual = [];
                    $afterfiles = [];
                    foreach ($files as $child) {
                        $afterfiles[$child] = $this->files($group[$child]['rootdir']);
                    }
                    foreach ($files as $child) {
                        $row = $group[$child];
                        if (!$row['files']['present']) {
                            continue;
                        }
                        $info = $pm->get_plugin_info($child);
                        $after = $afterfiles[$child];
                        if (!$plan['removefiles'] || !$row['removable'] || !$info || $info->versiondb !== null
                                || !$after['safe'] || $after['digest'] !== $row['files']['digest']
                                || !$pm->is_plugin_folder_removable($child)) {
                            $manual[] = $child;
                            continue;
                        }
                        $removedcode = true;
                        uninstall_diagnostics::phase('remove_files', $child);
                        $pm->remove_plugin_folder($info);
                        if (file_exists($row['rootdir'])) {
                            $manual[] = $child;
                        }
                    }
                    if ($removedcode) {
                        // Core purges caches before code removal; refresh this request after changing the tree.
                        uninstall_diagnostics::phase('refresh_components', $component);
                        \core_component::reset();
                    }
                    $receipt['results'][] = ['component' => $component, 'affected' => array_keys($group),
                        'status' => $manual ? 'manual' : 'done', 'manual' => $manual];
                    $receipt['current'] = null;
                    $this->save_receipt($receipt);
                    if ($manual) {
                        $receipt['state'] = 'stopped';
                        break;
                    }
                } catch (\Throwable $exception) {
                    uninstall_diagnostics::exception($exception, true);
                    if ($removedcode) {
                        // Also discard stale paths after an incomplete code-removal attempt. No disk purge.
                        \core_component::reset();
                    }
                    // Core's legacy uninstaller may throw while it has an output buffer open.
                    while (ob_get_level() > $buffers) {
                        $level = ob_get_level();
                        @ob_end_clean();
                        if (ob_get_level() >= $level) {
                            // A third-party non-removable buffer must not turn failure handling into an infinite loop.
                            break;
                        }
                    }
                    $receipt['results'][] = ['component' => $component, 'affected' => array_keys($group ?? []),
                        'status' => 'failed', 'message' => get_string('uninstallfailed', 'local_bulkinstall')];
                    $receipt['state'] = 'stopped';
                    break;
                }
            }
            if ($receipt['state'] === 'running') {
                $receipt['state'] = 'complete';
            }
            $receipt['current'] = null;
            $this->save_receipt($receipt);
            $finished = true;
            uninstall_diagnostics::phase('batch_event');
            \local_bulkinstall\event\uninstall_batch_finished::create([
                'context' => \context_system::instance(),
                'other' => ['components' => $receipt['affected'], 'state' => $receipt['state']],
            ])->trigger();
            uninstall_diagnostics::executing(false);
            return $receipt;
        } finally {
            $lock->release();
        }
    }

    /** Show only the current session's receipt, with a current-user binding in Core session and an independent receipt-ID/expiry check. */
    public function receipt(): ?array {
        uninstall_diagnostics::phase('receipt_read');
        global $SESSION, $USER;
        $reference = $SESSION->local_bulkinstall_uninstall_receipt ?? null;
        if (!is_array($reference) || ($reference['userid'] ?? null) !== (int)$USER->id) {
            return null;
        }
        $id = $reference['id'] ?? '';
        if (!preg_match('/^[a-f0-9]{32}$/D', $id)) {
            return null;
        }
        $file = $this->receipt_directory() . '/' . $id . '.json';
        if (!is_file($file)) {
            return null;
        }
        $receipt = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        return hash_equals($id, $receipt['id']) && time() - $receipt['created'] <= DAYSECS ? $receipt : null;
    }

    /** Read-only completion check of this session's receipt; never retries uninstall or removes files. */
    public function completion(?string $receiptid = null): array {
        global $CFG;
        self::require_access();
        $receipt = $this->receipt();
        uninstall_diagnostics::phase('completion');
        if (!$receipt || ($receiptid !== null && !hash_equals($receipt['id'], $receiptid))) {
            throw new \moodle_exception('uninstallreceiptunavailable', 'local_bulkinstall');
        }
        \core_plugin_manager::reset_caches();
        $pm = \core_plugin_manager::instance();
        $pending = [];
        $uncertain = !empty($receipt['current']) || !in_array($receipt['state'], ['complete', 'stopped'], true)
            || empty($receipt['results']);
        foreach ($receipt['results'] as $result) {
            if (!in_array($result['status'], ['done', 'manual'], true)) {
                $uncertain = true;
                continue;
            }
            foreach ($result['affected'] as $component) {
                $info = $pm->get_plugin_info($component);
                // A reinstall must never result in instructions to delete an installed plugin.
                if (($info && $info->versiondb !== null) || !array_key_exists($component, $receipt['paths'] ?? [])) {
                    $uncertain = true;
                    continue;
                }
                $root = $receipt['paths'][$component];
                if ($info && $info->rootdir && $info->rootdir !== $root) {
                    $uncertain = true;
                    continue;
                }
                $state = $this->files($root);
                // For missing paths, also verify the nearest existing ancestor has not become a link.
                $ancestor = $root;
                while ($ancestor && !file_exists($ancestor) && !is_link($ancestor)) {
                    $parent = dirname($ancestor);
                    if ($parent === $ancestor) {
                        break;
                    }
                    $ancestor = $parent;
                }
                $normalise = static fn(string $path): string => PHP_OS_FAMILY === 'Windows'
                    ? strtolower(str_replace('\\', '/', $path)) : $path;
                if (!$state['safe'] || ($ancestor && ($normalise((string)realpath($ancestor)) !== $normalise($ancestor)
                        || is_link($ancestor)))) {
                    $uncertain = true;
                    continue;
                }
                if ($state['present']) {
                    $pending[$component] = str_replace('\\', '/', substr($root, strlen($CFG->dirroot) + 1));
                }
            }
        }
        // Do not change the original receipt: it remains a record of what happened during that run.
        return ['receipt' => $receipt, 'pending' => $pending, 'uncertain' => $uncertain,
            'ready' => !$uncertain && !$pending];
    }

    /** Explicitly bounded temporary diagnostics; no plugin-owned participant-data queries or storage. */
    public static function cleanup_expired(): void {
        $directory = make_temp_directory('local_bulkinstall/uninstall');
        foreach (glob($directory . '/*') ?: [] as $file) {
            if (preg_match('/^[a-f0-9]{32}\.json(?:\.tmp)?$/D', basename($file))
                    && is_file($file) && filemtime($file) < time() - DAYSECS) {
                unlink($file);
            }
        }
    }

    private function receipt_directory(): string {
        return make_temp_directory('local_bulkinstall/uninstall');
    }

    private function save_receipt(array $receipt): void {
        uninstall_diagnostics::phase('receipt_write', $receipt['current'] ?? null);
        $file = $this->receipt_directory() . '/' . $receipt['id'] . '.json';
        $temporary = $file . '.tmp';
        $data = json_encode($receipt, JSON_THROW_ON_ERROR);
        if (file_put_contents($temporary, $data, LOCK_EX) !== strlen($data) || !rename($temporary, $file)) {
            throw new \moodle_exception('uninstallreceiptfailed', 'local_bulkinstall');
        }
    }

    private function uninstalled(array $components): bool {
        $pm = \core_plugin_manager::instance();
        foreach ($components as $component) {
            $info = $pm->get_plugin_info($component);
            if ($info && $info->versiondb !== null) {
                return false;
            }
        }
        return true;
    }

    /** Exact containment and payload check, rejecting symlinks/junctions anywhere below a plugin. */
    private function files(?string $root): array {
        global $CFG;
        $result = ['safe' => true, 'present' => false, 'digest' => ''];
        if (!$root) {
            return $result;
        }
        $base = realpath($CFG->dirroot);
        $normalise = static function(string $path): string {
            $path = str_replace('\\', '/', $path);
            return PHP_OS_FAMILY === 'Windows' ? strtolower($path) : $path;
        };
        $logical = $normalise($root);
        if (!$base || strpos($logical, $normalise($base) . '/') !== 0) {
            return ['safe' => false, 'present' => file_exists($root), 'digest' => ''];
        }
        if (!file_exists($root)) {
            // A dangling link must not be accepted as a missing plugin.
            $result['safe'] = !is_link($root);
            return $result;
        }
        $real = realpath($root);
        if (!$real || $normalise($real) !== $logical || !is_dir($real) || is_link($root)) {
            return ['safe' => false, 'present' => true, 'digest' => ''];
        }
        try {
            $hashes = [];
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root,
                \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $file) {
                $path = $file->getPathname();
                if ($file->isLink() || !$file->getRealPath()
                        || $normalise($file->getRealPath()) !== $normalise($path)) {
                    return ['safe' => false, 'present' => true, 'digest' => ''];
                }
                if ($file->isFile()) {
                    $hash = hash_file('sha256', $path);
                    if ($hash === false) {
                        return ['safe' => false, 'present' => true, 'digest' => ''];
                    }
                    $hashes[substr($normalise($path), strlen($logical))] = $hash;
                }
            }
            ksort($hashes, SORT_STRING);
            return ['safe' => true, 'present' => true,
                'digest' => hash('sha256', json_encode($hashes, JSON_THROW_ON_ERROR))];
        } catch (\Throwable $exception) {
            return ['safe' => false, 'present' => true, 'digest' => ''];
        }
    }
}
