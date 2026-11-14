<?php
// This file is part of Moodle - https://moodle.org/
// @copyright 2026 Andreas Giesen <andreas@108design.com>
// @license 108design source-available software license; see LICENSE.md
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md for the full terms.
namespace local_bulkinstall\local;
defined('MOODLE_INTERNAL') || die();

/** Explicit free acquisition for two fixed verified Core owners, never an arbitrary uploaded component. */
final class free_core_transition {
    public static function claim_core(array $transition, ?callable $claimrequest = null, ?callable $redeemrequest = null): void {
        $component = $transition['component'] ?? '';
        $features = ['local_frontpage' => ['frontpage.free', 2026110502], 'local_storefront' => ['storefront.free', 2026110600]];
        if (!isset($features[$component])) { throw new \invalid_parameter_exception('Unsupported Free Core owner.'); }
        [$feature, $minimum] = $features[$component];
        if (($transition['feature'] ?? '') !== $feature
                || !is_int($transition['targetversion'] ?? null) || $transition['targetversion'] < $minimum
                || preg_match('/^[a-f0-9]{64}$/D', (string)($transition['manifestsha256'] ?? '')) !== 1) {
            throw new \invalid_parameter_exception('Invalid Free Core transition.');
        }
        $hub = \local_lmh108\local\license_state::class;
        $installation = (string)get_config('local_lmh108', 'installationid');
        if ((int)get_config('local_lmh108', 'version') < 2026110501
                || preg_match('/^[a-f0-9]{64}$/D', $installation) !== 1
                || !in_array(get_config('local_lmh108', 'siteenvironment'), ['production', 'development'], true)) {
            throw new \RuntimeException('transition-manager-identity');
        }
        $version = (string)$transition['targetversion'];
        $configkey = 'freecoreclaim_' . substr(hash('sha256', $installation . ':' . $version . ':'
            . $transition['manifestsha256']), 0, 20);
        try {
            edition_transition::licence($component, (int)$version);
            unset_config($configkey, 'local_bulkinstall');
            return;
        } catch (\Throwable) { /* A missing/stale assertion needs deliberate acquisition. */ }
        $policy = \local_lmh108\local\feature_policy::apply($component . '/core',
            ['mode' => 'entitled', 'entitlements' => [$feature]], (int)$version);
        if ($policy['mode'] !== 'entitled' || !in_array($feature, $policy['entitlements'], true)) {
            throw new \RuntimeException('transition-target-policy');
        }
        if ($component === 'local_storefront') { storefront_transition::assert_policy((int)$version); }
        $existing = $hub::record($feature);
        if (!$hub::delegation_available() || ($existing && $existing->sourcecomponent !== $component)
                || $hub::site_token() === '') { throw new \RuntimeException('transition-account-required'); }
        $requestid = (string)get_config('local_bulkinstall', $configkey);
        if (preg_match('/^[a-f0-9-]{36}$/Di', $requestid) !== 1) {
            $requestid = \local_lmh108\local\license_client::idempotency_uuid(
                substr($component, 6) . '-free-transition:' . $installation . ':' . bin2hex(random_bytes(16)));
            set_config($configkey, $requestid, 'local_bulkinstall');
        }
        // Transport injection keeps offline regression tests on real assertion/replay validators.
        // Browser callers cannot supply callbacks; an injected response still needs an authentic signed assertion.
        $claimrequest ??= static fn(string $token, string $component, string $target, string $request, bool $neutral): array =>
            (new \local_lmh108\local\site_link_client())->claim($token, $component, $target, $request, $neutral);
        $redeemrequest ??= static fn(string $ticket, string $identity, string $url, array $plugins, string $request): array =>
            (new \local_lmh108\local\license_client())->redeem_ticket(
                $ticket, $identity, $url, 'unrestricted', $plugins, $request);
        $response = $claimrequest($hub::site_token(), $component, $version, $requestid, true);
        $ticket = $response['activation_ticket'] ?? '';
        if (!is_string($ticket) || preg_match('/^LASA1-[A-Za-z0-9_-]{43}$/D', $ticket) !== 1
                || ($response['environment'] ?? '') !== 'unrestricted') {
            throw new \RuntimeException('transition-free-ticket');
        }
        $mappings = [$feature => ['feature_code' => $feature, 'component' => $component, 'version' => $version]];
        \local_lmh108\local\ticket_redemption::run(hash('sha256', $ticket . "\0" . $installation . "\0unrestricted"),
            ['mappings' => $mappings, 'url' => $hub::canonical_url()],
            static function(string $code) use ($hub): array {
                $record = $hub::record($code);
                try { $claims = $hub::claims($code, false); } catch (\Throwable) { $claims = null; }
                return ['fingerprint' => hash('sha256', $record ? json_encode([
                    $record->id, $record->activationid, $record->refreshtoken, $record->assertion,
                ], JSON_THROW_ON_ERROR) : 'absent'), 'claims' => $claims];
            },
            static fn(array $request): array => $redeemrequest(
                $ticket, $installation, $request['url'], array_values($request['mappings']),
                \local_lmh108\local\license_client::idempotency_uuid('ticket:' . $ticket . ':' . $installation)),
            static function(array $activation, string $code, string $component) use ($hub, $installation): array {
                $claims = $hub::accept_handover($activation, $code, 'unrestricted', $component, $installation);
                if (!\local_lmh108\local\licensing_runtime::is_permanent_free($claims)
                        || ($claims['status'] ?? '') !== 'active') { throw new \RuntimeException('transition-free-assertion'); }
                return $claims;
            });
        edition_transition::licence($component, (int)$version);
        unset_config($configkey, 'local_bulkinstall');
    }
}
