<?php
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md for the full terms.
namespace local_bulkinstall\local;
defined('MOODLE_INTERNAL') || die();

/** Frontpage Free Core migration and its independently optional paid Course Activity. */
final class frontpage_transition {
    private const SOURCES = ['local_frontpage' => 2026010103, 'mod_frontpage' => 2026010102];

    public static function inspect(string $zipfile, string $root, string $component, int $version): ?array {
        global $CFG, $DB, $USER;
        $guard = \design108\update_guard\v1\guard::class;
        $policy = $guard::load_policy($root, $component);
        if ($policy['edition'] !== 'le') { return null; }
        try { $guard::verify_replacement($zipfile, $root, $component, $version, 'bi'); return null; }
        catch (\Throwable) { /* Only installed publisher authority may authorise a different edition. */ }
        if (!in_array('bi', $policy['channels'], true) || ($policy['upgrade_family'] ?? 'default') !== 'default') {
            throw new \RuntimeException('transition-source-policy');
        }
        $edition = $component === 'local_frontpage' ? 'free-core' : 'pro';
        $feature = $component === 'local_frontpage' ? 'frontpage.free' : 'frontpage.course_activity';
        $projected = $policy;
        $projected['edition'] = $edition; $projected['profiles'] = ['normal'];
        $manifest = $guard::verify($zipfile, $projected, $component, $version);
        $zip = new \ZipArchive();
        if ($zip->open($zipfile) !== true) { throw new \RuntimeException('transition-archive'); }
        try {
            $prefix = 'frontpage/';
            $raw = $zip->getFromName($prefix . 'edition-migrations.json');
            $manifestbytes = $zip->getFromName($prefix . 'release-manifest.json');
            if (!is_string($raw) || !isset($manifest['files']['edition-migrations.json'])) {
                throw new \RuntimeException('transition-contract-missing');
            }
            $contract = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
        } finally { $zip->close(); }
        $source = (int)get_config($component, 'version');
        $disk = $guard::metadata((string)file_get_contents($root . '/version.php'));
        if (($contract['schema'] ?? null) !== 1 || ($contract['component'] ?? '') !== $component
                || ($contract['from'] ?? '') !== 'le' || ($contract['to'] ?? '') !== $edition
                || ($contract['feature'] ?? '') !== $feature || $source !== self::SOURCES[$component]
                || $disk['version'] !== $source || !in_array($source, $contract['source_versions'] ?? [], true)
                || get_config($component, 'distributionorigin') !== 'marketplace'
                || ($contract['source_schema_sha256'] ?? '') !== hash_file('sha256', $root . '/db/install.xml')
                || ($contract['target_schema_sha256'] ?? '') !== ($manifest['files']['db/install.xml'] ?? '')
                || !isset($manifest['files']['db/le_upgrade.php'])) {
            throw new \RuntimeException('transition-source-contract');
        }
        foreach ($contract['tables'] ?? [] as $table => $expected) {
            $actual = array_keys($DB->get_columns($table)); sort($actual); sort($expected);
            if ($actual !== $expected) { throw new \RuntimeException('transition-source-schema'); }
        }
        $base = ['component' => $component, 'sourceversion' => $source, 'targetversion' => $version,
            'sourcefingerprint' => edition_transition::fingerprint($root), 'owner' => $component,
            'sha256' => hash_file('sha256', $zipfile), 'manifestsha256' => hash('sha256', $manifestbytes),
            'feature' => $feature, 'site' => (string)$CFG->wwwroot, 'userid' => (int)$USER->id];
        if ($component === 'local_frontpage') { $base['companion'] = self::companion(); }
        try { $base['licence'] = self::licence($component, $version); $base['ready'] = true; }
        catch (\Throwable) { $base['licence'] = null; $base['ready'] = false; }
        return $base;
    }

    /** The unchanged installed LE module is part of the Core review, not an automatic paid dependency. */
    private static function companion(): ?array {
        global $CFG;
        $version = (int)get_config('mod_frontpage', 'version');
        if (!$version) { return null; }
        $root = $CFG->dirroot . '/mod/frontpage';
        $guard = \design108\update_guard\v1\guard::class;
        $policy = $guard::load_policy($root, 'mod_frontpage');
        $disk = $guard::metadata((string)file_get_contents($root . '/version.php'));
        if ($policy['edition'] !== 'le' || $version !== self::SOURCES['mod_frontpage']
                || $disk['version'] !== $version || get_config('mod_frontpage', 'distributionorigin') !== 'marketplace') {
            throw new \RuntimeException('transition-companion-source');
        }
        return ['version' => $version, 'fingerprint' => edition_transition::fingerprint($root),
            'schema_sha256' => hash_file('sha256', $root . '/db/install.xml'), 'keys' => $policy['keys']];
    }

    /** Fresh real Manager assertions, evaluated for the prospective owning Core version. */
    public static function licence(string $component, int $version): array {
        if (!isset(self::SOURCES[$component]) || (int)get_config('local_lmh108', 'version') < 2026110501
                || preg_match('/^[a-f0-9]{64}$/D', (string)get_config('local_lmh108', 'installationid')) !== 1
                || !in_array(get_config('local_lmh108', 'siteenvironment'), ['production', 'development'], true)) {
            throw new \RuntimeException('transition-manager-required');
        }
        $hub = \local_lmh108\local\license_state::class;
        $free = $component === 'local_frontpage';
        $feature = $free ? 'frontpage.free' : 'frontpage.course_activity';
        $record = $hub::record($feature);
        if (!$hub::delegation_available() || !$record || $record->sourcecomponent !== 'local_frontpage') {
            throw new \RuntimeException('transition-licence-owner');
        }
        $claims = $hub::claims($feature, false);
        if (($claims['status'] ?? '') !== 'active') {
            throw new \RuntimeException('transition-licence-required');
        }
        if ($free) {
            if (!\local_lmh108\local\licensing_runtime::is_permanent_free($claims)) {
                throw new \RuntimeException('transition-free-activation-required');
            }
        } else if (($claims['exp'] ?? 0) <= time()
                || !in_array($claims['licence_source'] ?? '', ['purchase', 'manual', 'partner'], true)
                || ($claims['environment'] ?? '') !== $hub::site_environment()) {
            throw new \RuntimeException('transition-paid-licence-required');
        }
        $coreversion = $free ? $version : max(2026110502, (int)get_config('local_frontpage', 'version'));
        $definition = \local_lmh108\local\feature_policy::apply('local_frontpage/' . ($free ? 'core' : 'course_activity'),
            ['mode' => 'entitled', 'entitlements' => [$feature]], $coreversion);
        if ($definition['mode'] !== 'entitled' || !in_array($feature, $definition['entitlements'], true)) {
            throw new \RuntimeException('transition-target-policy');
        }
        return ['licenceid' => $claims['licence_id'], 'activationid' => $claims['activation_id'],
            'installationid' => $claims['installation_id'], 'environment' => $claims['environment'],
            'expires' => $claims['exp'], 'validuntil' => $claims['valid_until'] ?? null,
            'assertionsha256' => hash('sha256', $record->assertion)];
    }

    /** Return whole-batch semantic errors after signature inspection and before any writer. */
    public static function choice_required(array $packages): bool {
        global $CFG;
        if (!in_array('local_frontpage', array_column($packages, 'component'), true)) { return false; }
        foreach ($packages as $package) {
            if (($package['transition']['component'] ?? '') === 'local_frontpage'
                    && !empty($package['transition']['companion'])) { return true; }
        }
        if (!in_array('mod_frontpage', array_column($packages, 'component'), true)
                || get_config('mod_frontpage', 'distributionorigin') !== 'marketplace') { return false; }
        try {
            $policy = \design108\update_guard\v1\guard::load_policy($CFG->dirroot . '/mod/frontpage', 'mod_frontpage');
            return $policy['edition'] === 'le';
        } catch (\Throwable) { return false; }
    }

    public static function errors(array $packages, ?string $choice): array {
        $transitions = array_filter(array_column($packages, 'transition'));
        $bycomponent = array_column($transitions, null, 'component');
        $core = $bycomponent['local_frontpage'] ?? null;
        $paid = $bycomponent['mod_frontpage'] ?? null;
        if (self::choice_required($packages)) {
            if (!in_array($choice, ['keep_le', 'paid'], true)) { return [get_string('frontpagechoicerequired', 'local_bulkinstall')]; }
            if (($choice === 'paid') !== ($paid !== null)) { return [get_string('frontpagechoicepackages', 'local_bulkinstall')]; }
        }
        if ($paid) {
            $versions = array_column($packages, 'version', 'component');
            $coreversion = (int)($versions['local_frontpage'] ?? get_config('local_frontpage', 'version'));
            $effective = \local_lmh108\local\feature_policy::apply('local_frontpage/course_activity',
                ['mode' => 'entitled', 'entitlements' => ['frontpage.course_activity']], $coreversion);
            if ($effective['mode'] !== 'entitled' || !in_array('frontpage.course_activity', $effective['entitlements'], true)) {
                return [get_string('frontpagepolicyblocked', 'local_bulkinstall')];
            }
        }
        if ($paid && !$core && (int)get_config('local_frontpage', 'version') < 2026110502) {
            return [get_string('frontpagecorefirst', 'local_bulkinstall')];
        }
        return [];
    }

    /** Explicit Free Core acquisition for this authenticated future target; no installed-manifest assumption. */
    public static function claim_core(array $transition, ?callable $claimrequest = null, ?callable $redeemrequest = null): void {
        if (($transition['component'] ?? '') !== 'local_frontpage') { throw new \invalid_parameter_exception('Invalid Frontpage owner.'); }
        free_core_transition::claim_core($transition, $claimrequest, $redeemrequest);
    }
}
