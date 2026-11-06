<?php
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md for the full terms.
namespace local_bulkinstall\local;
defined('MOODLE_INTERNAL') || die();

/** Authenticated Free Core migration and optional compatible free companion-family migration. */
final class storefront_transition {
    private const SOURCES = ['local_storefront' => 2026010102, 'enrol_storefront' => 2026010101];

    public static function inspect(string $zipfile, string $root, string $component, int $version): ?array {
        global $CFG, $DB, $USER;
        $guard = \design108\update_guard\v1\guard::class;
        $policy = $guard::load_policy($root, $component);
        $core = $component === 'local_storefront';
        if ($core ? $policy['edition'] !== 'le' : ($policy['upgrade_family'] ?? 'default') !== 'le-storefront') { return null; }
        try { $guard::verify_replacement($zipfile, $root, $component, $version, 'bi'); return null; }
        catch (\Throwable) { /* An exceptional route still requires installed publisher authority. */ }
        if (!in_array('bi', $policy['channels'], true) || ($core && ($policy['upgrade_family'] ?? 'default') !== 'default')) {
            throw new \RuntimeException('transition-source-policy');
        }
        $edition = $core ? 'free-core' : 'free';$family = $core ? 'default' : 'legacy-storefront';
        $projected = $policy;$projected['edition'] = $edition;$projected['upgrade_family'] = $family;$projected['profiles'] = ['normal'];
        $manifest = $guard::verify($zipfile, $projected, $component, $version);
        $zip = new \ZipArchive();if ($zip->open($zipfile) !== true) { throw new \RuntimeException('transition-archive'); }
        try {
            $raw = $zip->getFromName('storefront/edition-migrations.json');
            $manifestbytes = $zip->getFromName('storefront/release-manifest.json');
            if (!is_string($raw) || !isset($manifest['files']['edition-migrations.json'])) { throw new \RuntimeException('transition-contract-missing'); }
            $contract = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } finally { $zip->close(); }
        $source = (int)get_config($component, 'version');$disk = $guard::metadata((string)file_get_contents($root . '/version.php'));
        if (($contract['schema'] ?? null) !== 1 || ($contract['component'] ?? '') !== $component
                || ($contract['from'] ?? '') !== $policy['edition'] || ($contract['to'] ?? '') !== $edition
                || ($contract['from_family'] ?? 'default') !== ($policy['upgrade_family'] ?? 'default')
                || ($contract['to_family'] ?? 'default') !== $family || ($contract['feature'] ?? '') !== 'storefront.free'
                || $source !== self::SOURCES[$component] || $disk['version'] !== $source
                || !in_array($source, $contract['source_versions'] ?? [], true)
                || get_config($component, 'distribution') !== 'course'
                || ($contract['source_schema_sha256'] ?? '') !== hash_file('sha256', $root . '/db/install.xml')
                || ($contract['target_schema_sha256'] ?? '') !== ($manifest['files']['db/install.xml'] ?? '')
                || !isset($manifest['files']['db/le_upgrade.php'])) { throw new \RuntimeException('transition-source-contract'); }
        foreach ($contract['tables'] ?? [] as $table => $expected) {
            $actual = array_keys($DB->get_columns($table));sort($actual);sort($expected);
            if ($actual !== $expected) { throw new \RuntimeException('transition-source-schema'); }
        }
        $base = ['component' => $component, 'sourceversion' => $source, 'targetversion' => $version,
            'sourcefingerprint' => edition_transition::fingerprint($root), 'owner' => $component,
            'sha256' => hash_file('sha256', $zipfile), 'manifestsha256' => hash('sha256', $manifestbytes),
            'feature' => 'storefront.free', 'site' => (string)$CFG->wwwroot, 'userid' => (int)$USER->id];
        if ($core) { $base['companion'] = self::companion($contract['companion_schema_sha256'] ?? ''); }
        try { $base['licence'] = self::licence($component, $version);$base['ready'] = true; }
        catch (\Throwable) { $base['licence'] = null;$base['ready'] = false; }
        return $base;
    }

    /** Bind the actual existing companion without requiring an exchange of compatible LE code. */
    private static function companion(string $schemahash): ?array {
        global $CFG;
        $version = (int)get_config('enrol_storefront', 'version');if (!$version) { return null; }
        $root = $CFG->dirroot . '/enrol/storefront';$guard = \design108\update_guard\v1\guard::class;
        $policy = $guard::load_policy($root, 'enrol_storefront');$authority = $guard::load_policy($CFG->dirroot . '/local/storefront', 'local_storefront');
        $disk = $guard::metadata((string)file_get_contents($root . '/version.php'));
        if ($disk['version'] !== $version || $policy['edition'] !== 'free'
                || hash_file('sha256', $root . '/db/install.xml') !== $schemahash
                || (($policy['upgrade_family'] ?? '') === 'le-storefront' && ($version < 2026010101 || get_config('enrol_storefront', 'distribution') !== 'course'))
                || !in_array($policy['upgrade_family'] ?? '', ['le-storefront', 'legacy-storefront'], true)
                || !array_intersect_assoc($policy['keys'], $authority['keys'])) { throw new \RuntimeException('transition-companion-contract'); }
        return ['version' => $version, 'fingerprint' => edition_transition::fingerprint($root),
            'schema_sha256' => hash_file('sha256', $root . '/db/install.xml'), 'family' => $policy['upgrade_family']];
    }

    public static function licence(string $component, int $targetversion): array {
        if (!isset(self::SOURCES[$component]) || (int)get_config('local_lmh108', 'version') < 2026110501) { throw new \RuntimeException('transition-manager-required'); }
        $hub = \local_lmh108\local\license_state::class;
        if (preg_match('/^[a-f0-9]{64}$/D', (string)get_config('local_lmh108', 'installationid')) !== 1
                || !in_array(get_config('local_lmh108', 'siteenvironment'), ['production', 'development'], true)) { throw new \RuntimeException('transition-manager-identity'); }
        $record = $hub::record('storefront.free');
        if (!$hub::delegation_available() || !$record || $record->sourcecomponent !== 'local_storefront') { throw new \RuntimeException('transition-licence-owner'); }
        $claims = $hub::claims('storefront.free', false);
        if (($claims['status'] ?? '') !== 'active' || !\local_lmh108\local\licensing_runtime::is_permanent_free($claims)) { throw new \RuntimeException('transition-free-required'); }
        $coreversion = $component === 'enrol_storefront' ? max(2026110600, (int)get_config('local_storefront', 'version')) : $targetversion;
        self::assert_policy($coreversion);
        return ['licenceid' => $claims['licence_id'], 'activationid' => $claims['activation_id'],
            'installationid' => $claims['installation_id'], 'environment' => $claims['environment'],
            'permanent_free' => true, 'coreversion' => $coreversion, 'assertionsha256' => hash('sha256', $record->assertion)];
    }

    public static function assert_policy(int $coreversion): void {
        $core = \local_lmh108\local\feature_policy::apply('local_storefront/core',
            ['mode' => 'entitled', 'entitlements' => ['storefront.free']], $coreversion);
        $moodle = \local_lmh108\local\feature_policy::apply('local_storefront/fulfilment/moodle',
            ['mode' => 'free', 'entitlements' => []], $coreversion);
        if ($core['mode'] !== 'entitled' || !in_array('storefront.free', $core['entitlements'], true) || $moodle['mode'] !== 'free') {
            throw new \RuntimeException('transition-target-policy');
        }
    }

    /** Validate the actual prospective Core and companion set at analyse, freeze and receipt creation. */
    public static function errors(array $packages): array {
        $crossing = array_filter($packages, static fn(array $p): bool => in_array($p['transition']['component'] ?? '', array_keys(self::SOURCES), true));
        if (!$crossing) { return []; }
        $version = (int)get_config('local_storefront', 'version');
        foreach ($packages as $p) { if (($p['component'] ?? '') === 'local_storefront') { $version = (int)$p['version']; } }
        if ($version < 2026110600) { return [get_string('storefronttransitioncorefirst', 'local_bulkinstall')]; }
        try { self::assert_policy($version); }catch (\Throwable) { return [get_string('storefronttransitionblocked', 'local_bulkinstall')]; }
        return [];
    }
}
