<?php
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md for the full terms.
namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

/** The sole, explicit SM/MB LE to standalone Pro exception to the installed edition policy. */
final class edition_transition {
    private const SOURCES = [
        'local_scriptmanager' => ['version' => 2026010105, 'family' => 'scriptmanager-schema-v2'],
        'local_menubuilder' => ['version' => 2026010102, 'family' => 'default'],
    ];

    /** Authenticate the target with installed publisher pins before reading its migration contract. */
    public static function inspect(string $zipfile, string $root, string $component, int $version): ?array {
        global $CFG, $USER;
        if (in_array($component, ['local_storefront', 'enrol_storefront'], true)) {
            return storefront_transition::inspect($zipfile, $root, $component, $version);
        }
        if (in_array($component, ['local_frontpage', 'mod_frontpage'], true)) {
            return frontpage_transition::inspect($zipfile, $root, $component, $version);
        }
        if (!isset(self::SOURCES[$component])) {
            return null;
        }
        $guard = \design108\update_guard\v1\guard::class;
        $policy = $guard::load_policy($root, $component);
        if ($policy['edition'] !== 'le') {
            return null;
        }
        // Try the normal LE update first. Only a real Pro candidate enters this exceptional route.
        try {
            $guard::verify_replacement($zipfile, $root, $component, $version, 'bi');
            return null;
        } catch (\Throwable) {
            // The projected policy retains exactly the installed publisher authority and guard floor.
        }
        $sourcefamily = self::SOURCES[$component]['family'];
        if (!in_array('bi', $policy['channels'], true) || ($policy['upgrade_family'] ?? 'default') !== $sourcefamily) {
            throw new \RuntimeException('transition-source-policy');
        }
        $projected = $policy;
        $projected['edition'] = 'pro';
        // Both supported Pro packages belong to the default family. The installed policy still supplies the trust pins.
        $projected['upgrade_family'] = 'default';
        $projected['profiles'] = ['normal'];
        $manifest = $guard::verify($zipfile, $projected, $component, $version);
        $zip = new \ZipArchive();
        if ($zip->open($zipfile) !== true) {
            throw new \RuntimeException('transition-archive');
        }
        try {
            $prefix = substr($component, 6) . '/';
            $raw = $zip->getFromName($prefix . 'edition-migrations.json');
            $manifestbytes = $zip->getFromName($prefix . 'release-manifest.json');
            if (!is_string($raw) || !isset($manifest['files']['edition-migrations.json'])) {
                throw new \RuntimeException('transition-contract-missing');
            }
            $contract = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
        } finally {
            $zip->close();
        }
        $source = (int)get_config($component, 'version');
        $disk = $guard::metadata((string)file_get_contents($root . '/version.php'));
        if (($contract['schema'] ?? null) !== 1 || ($contract['component'] ?? '') !== $component
                || ($contract['from'] ?? '') !== 'le' || ($contract['to'] ?? '') !== 'pro'
                || ($contract['from_family'] ?? 'default') !== $sourcefamily
                || ($contract['to_family'] ?? 'default') !== $projected['upgrade_family']
                || ($contract['feature'] ?? '') !== substr($component, 6) . '.pro'
                || !in_array($source, $contract['source_versions'] ?? [], true)
                || $source !== self::SOURCES[$component]['version'] || $disk['version'] !== $source
                || ($contract['source_schema_sha256'] ?? '') !== hash_file('sha256', $root . '/db/install.xml')
                || ($contract['target_schema_sha256'] ?? '') !== ($manifest['files']['db/install.xml'] ?? '')
                || !isset($manifest['files']['db/le_upgrade.php'])
                || get_config($component, 'frontpagemigrationcompleted')
                || get_config($component, 'moduleowner') !== $component) {
            throw new \RuntimeException('transition-source-contract');
        }
        global $DB;
        $tables = $contract['tables'] ?? [];
        if ($component === 'local_scriptmanager' && (!$tables || array_key_exists('table_renames', $contract))) {
            throw new \RuntimeException('transition-source-contract');
        }
        $dbman = $DB->get_manager();
        foreach ($tables as $table => $expected) {
            if (!$dbman->table_exists($table)) {
                throw new \RuntimeException('transition-source-schema');
            }
            $actual = array_keys($DB->get_columns($table));
            sort($actual); sort($expected);
            if ($actual !== $expected) { throw new \RuntimeException('transition-source-schema'); }
        }
        $base = [
            'component' => $component, 'sourceversion' => $source, 'targetversion' => $version,
            'sourcefingerprint' => self::fingerprint($root), 'owner' => $component,
            'sha256' => hash_file('sha256', $zipfile),
            'manifestsha256' => hash('sha256', $manifestbytes),
            'feature' => $contract['feature'], 'site' => (string)$CFG->wwwroot, 'userid' => (int)$USER->id,
        ];
        try {
            $base['licence'] = self::licence($component, $version);
            $base['ready'] = true;
        } catch (\Throwable) {
            $base['licence'] = null;
            $base['ready'] = false;
        }
        return $base;
    }

    /** Require an effective, non-trial, site-bound Pro assertion; a policy-free gate is not a licence. */
    public static function licence(string $component, int $targetversion): array {
        if (in_array($component, ['local_storefront', 'enrol_storefront'], true)) {
            return storefront_transition::licence($component, $targetversion);
        }
        if (in_array($component, ['local_frontpage', 'mod_frontpage'], true)) {
            return frontpage_transition::licence($component, $targetversion);
        }
        if (!isset(self::SOURCES[$component]) || (int)get_config('local_lmh108', 'version') < 2026110501) {
            throw new \RuntimeException('transition-manager-required');
        }
        $hub = \local_lmh108\local\license_state::class;
        $module = substr($component, 6);
        $feature = $module . '.pro';
        // Preview is read-only, including on an incomplete/old Manager installation.
        if (preg_match('/^[a-f0-9]{64}$/D', (string)get_config('local_lmh108', 'installationid')) !== 1
                || !in_array(get_config('local_lmh108', 'siteenvironment'), ['production', 'development'], true)) {
            throw new \RuntimeException('transition-manager-identity');
        }
        $record = $hub::record($feature);
        if (!$hub::delegation_available() || !$record || $record->sourcecomponent !== $component) {
            throw new \RuntimeException('transition-licence-owner');
        }
        $claims = $hub::claims($feature, false);
        if (($claims['status'] ?? '') !== 'active'
                || !in_array($claims['licence_source'] ?? '', ['purchase', 'manual', 'partner'], true)
                || ($claims['exp'] ?? 0) <= time()
                || ($claims['environment'] ?? '') !== $hub::site_environment()) {
            throw new \RuntimeException('transition-licence-required');
        }
        // Evaluate the signed policy at the prospective version, never at the low LE build.
        foreach (($module === 'scriptmanager' ? ['authoring', 'runtime'] : ['authoring', 'course_explorer']) as $operation) {
            $definition = \local_lmh108\local\feature_policy::apply($component . '/' . $operation,
                ['mode' => 'entitled', 'entitlements' => [$feature]], $targetversion);
            if ($definition['mode'] !== 'entitled' || !in_array($feature, $definition['entitlements'], true)) {
                throw new \RuntimeException('transition-target-policy');
            }
        }
        return [
            'licenceid' => $claims['licence_id'], 'activationid' => $claims['activation_id'],
            'installationid' => $claims['installation_id'], 'environment' => $claims['environment'],
            'expires' => $claims['exp'], 'validuntil' => $claims['valid_until'] ?? null,
            'assertionsha256' => hash('sha256', $record->assertion),
        ];
    }

    /** All source bytes participate in review binding; no symlink or newly added file is ignored. */
    public static function fingerprint(string $root): string {
        $files = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root,
                \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isLink()) {
                throw new \RuntimeException('transition-source-symlink');
            }
            if ($file->isFile()) {
                $files[str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1))]
                    = hash_file('sha256', $file->getPathname());
            }
        }
        ksort($files);
        return hash('sha256', json_encode($files, JSON_THROW_ON_ERROR));
    }

    /** Final receipts are atomic across the batch and survive session loss / a subsequent CLI DB upgrade. */
    public static function prepare(array $packages, bool $consent): void {
        global $DB;
        $transitions = array_filter(array_column($packages, 'transition'));
        if (!$transitions) {
            return;
        }
        if (!$consent) {
            throw new \moodle_exception('errortransitionconsent', 'local_bulkinstall');
        }
        if (storefront_transition::errors($packages)) { throw new \moodle_exception('storefronttransitionblocked', 'local_bulkinstall'); }
        $choices = array_filter(array_column($packages, 'activitychoice'));
        if (frontpage_transition::errors($packages, $choices ? reset($choices) : null)) {
            throw new \moodle_exception('frontpagechoicerequired', 'local_bulkinstall');
        }
        $transaction = $DB->start_delegated_transaction();
        foreach ($transitions as $transition) {
            if (empty($transition['ready']) || $transition['licence'] !== self::licence(
                    $transition['component'], $transition['targetversion'])) {
                throw new \moodle_exception('errortransitionlicence', 'local_bulkinstall');
            }
            $receipt = $transition + ['schema' => 1, 'status' => 'prepared', 'consentedat' => time()];
            if ($transition['component'] === 'local_frontpage') {
                $receipt['activitymode'] = empty($transition['companion']) ? 'none'
                    : (in_array('mod_frontpage', array_column($packages, 'component'), true) ? 'paid' : 'keep_le');
            }
            // User binding is retained in the consent digest and Moodle's installation event, not a second user store.
            $receipt['consentsha256'] = hash('sha256', json_encode($receipt, JSON_THROW_ON_ERROR));
            $receipt['licenceproofsha256'] = hash('sha256', json_encode($receipt['licence'], JSON_THROW_ON_ERROR));
            unset($receipt['userid'], $receipt['licence']);
            set_config('editiontransition', json_encode($receipt, JSON_THROW_ON_ERROR), $transition['component']);
        }
        $transaction->allow_commit();
    }
}
