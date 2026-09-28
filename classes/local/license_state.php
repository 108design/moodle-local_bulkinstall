<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.
namespace local_bulkinstall\local;

/** Local fail-closed activation state backed by a signed LAS assertion. */
final class license_state {
    private const HUB_STATE = '\\local_lmh108\\local\\license_state';

    public static function installation_id(): string {
        $installationid = (string) get_config('local_bulkinstall', 'installationid');
        if (preg_match('/^[0-9a-f]{64}$/D', $installationid) !== 1) {
            $hub = self::HUB_STATE;
            $installationid = self::hub_available() && method_exists($hub, 'installation_id')
                ? $hub::installation_id() : bin2hex(random_bytes(32));
            set_config('installationid', $installationid, 'local_bulkinstall');
        }
        return $installationid;
    }

    public static function canonical_url(): string {
        global $CFG;

        $parts = parse_url((string) $CFG->wwwroot);
        if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
            throw new license_client_exception('invalid_site_url', 'Moodle wwwroot is not a canonical URL.');
        }
        $scheme = strtolower((string) $parts['scheme']);
        $host = strtolower(rtrim((string) $parts['host'], '.'));
        $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';
        $path = isset($parts['path']) ? '/' . ltrim((string) $parts['path'], '/') : '';
        return rtrim($scheme . '://' . $host . $port . $path, '/');
    }

    public static function default_environment(): string {
        $hub = self::HUB_STATE;
        if (self::hub_available() && method_exists($hub, 'site_environment')) {
            $environment = (string) $hub::site_environment();
            if (in_array($environment, ['production', 'development'], true)) {
                return $environment;
            }
        }
        try {
            $environment = (string) self::standalone_claims(true)['environment'];
            return in_array($environment, ['production', 'development'], true) ? $environment : '';
        } catch (\Throwable) {
            return '';
        }
    }

    public static function is_active(): bool {
        try {
            return licensing_runtime::gate_allows(self::claims(false), 'permanent_free', false);
        } catch (\Throwable) {
            return false;
        }
    }

    public static function is_configured(): bool {
        $hub = self::HUB_STATE;
        if (self::hub_available() && $hub::record(license_config::FEATURE_CODE)) {
            return true;
        }
        return self::standalone_is_configured();
    }

    public static function standalone_is_active(): bool {
        try {
            return licensing_runtime::gate_allows(self::standalone_claims(false), 'permanent_free', false);
        } catch (\Throwable) {
            return false;
        }
    }

    public static function standalone_is_configured(): bool {
        $state = \local_bulkinstall\local\licensing_runtime::activation_state(license_config::COMPONENT);
        return (string) ($state['activationid'] ?? '') !== ''
            && (string) ($state['assertion'] ?? '') !== ''
            && (string) ($state['refreshtoken'] ?? '') !== '';
    }

    /** @return array<string, mixed> */
    public static function claims(bool $allowexpired = false): array {
        $hub = self::HUB_STATE;
        if (self::hub_available() && $hub::record(license_config::FEATURE_CODE)) {
            try {
                return $hub::claims(license_config::FEATURE_CODE, $allowexpired);
            } catch (\Throwable $exception) {
                // Observe/compatibility mode: a Hub disagreement must not break a valid standalone activation.
                if (!self::standalone_is_active()) {
                    throw $exception;
                }
            }
        }
        return self::standalone_claims($allowexpired);
    }

    /** Claims from the plugin-owned activation only; used by the optional Hub's observe adapter. */
    public static function standalone_claims(bool $allowexpired = false): array {
        $state = \local_bulkinstall\local\licensing_runtime::activation_state(license_config::COMPONENT);
        $assertion = (string) ($state['assertion'] ?? '');
        if ($assertion === '') {
            throw new license_client_exception('assertion_missing', 'No site licence assertion is stored.');
        }
        return self::validate_assertion(
            $assertion,
            $allowexpired,
            (string) ($state['activationid'] ?? ''),
            (string) ($state['environment'] ?? '')
        );
    }

    /** @return array<string, mixed> */
    public static function activate(string $licensekey, string $environment): array {
        return \local_bulkinstall\local\licensing_runtime::with_lock(license_config::FEATURE_CODE, static function() use ($licensekey, $environment) {
            return self::activate_locked($licensekey, $environment);
        });
    }

    private static function activate_locked(string $licensekey, string $environment): array {
        $licensekey = trim($licensekey);
        if (preg_match('/^LAS1-[A-Za-z0-9_-]{20,}$/D', $licensekey) !== 1) {
            throw new license_client_exception('invalid_licence_key', 'Enter a complete LAS1 licence key.');
        }
        if (!in_array($environment, ['production', 'development'], true)) {
            throw new license_client_exception('invalid_environment', 'Select production or development.');
        }
        $hub = self::HUB_STATE;
        if (self::hub_available() && $hub::encryption_key_available()) {
            return $hub::activate($licensekey, license_config::FEATURE_CODE, $environment,
                license_config::COMPONENT, (string) get_config('local_bulkinstall', 'version'));
        }
        $installationid = self::installation_id();
        $response = (new license_client())->activate(
            $licensekey,
            $installationid,
            self::canonical_url(),
            $environment,
            (string) get_config('local_bulkinstall', 'version')
        );
        $activationid = is_string($response['activation_id'] ?? null) ? $response['activation_id'] : '';
        $refreshtoken = is_string($response['refresh_token'] ?? null) ? $response['refresh_token'] : '';
        $assertion = is_string($response['assertion'] ?? null) ? $response['assertion'] : '';
        if (preg_match('/^[0-9a-f-]{36}$/Di', $activationid) !== 1
                || !str_starts_with($refreshtoken, 'LASR1-') || $assertion === '') {
            throw new license_client_exception('invalid_activation_response',
                'LAS returned an incomplete activation response.');
        }
        $claims = self::validate_assertion($assertion, false, $activationid, $environment);
        \local_bulkinstall\local\licensing_runtime::update_activation_state(license_config::COMPONENT, [
            'activationid' => $activationid,
            'environment' => (string) $claims['environment'],
            'refreshtoken' => self::encrypt($refreshtoken),
            'assertion' => $assertion,
            'lastcheck' => time(),
            'emergencyresetavailable' => null,
            'lasterror' => null,
        ]);
        return $claims;
    }

    /** Redeem the signed one-time ticket returned after account-link approval. */
    public static function activate_ticket(string $ticket, string $environment): array {
        return \local_bulkinstall\local\licensing_runtime::with_lock(license_config::FEATURE_CODE,
            static fn() => self::activate_ticket_locked($ticket, $environment));
    }

    private static function activate_ticket_locked(string $ticket, string $environment): array {
        $ticket = trim($ticket);
        if (preg_match('/^LASA1-[A-Za-z0-9_-]{43}$/D', $ticket) !== 1) {
            throw new license_client_exception('invalid_activation_ticket',
                'The account link returned an invalid activation ticket.');
        }
        if (!in_array($environment, ['production', 'development'], true)) {
            throw new license_client_exception('invalid_environment', 'Select production or development.');
        }
        $hub = self::HUB_STATE;
        if (self::hub_available() && $hub::encryption_key_available()) {
            return $hub::redeem_ticket($ticket, $environment)['claims'];
        }
        $installationid = self::installation_id();
        $idempotency = license_client::deterministic_uuid('ticket:' . $ticket . ':' . $installationid);
        $response = (new license_client())->redeem_ticket($ticket, $installationid,
            self::canonical_url(), $environment, (string) get_config('local_bulkinstall', 'version'), $idempotency);
        $activations = $response['activations'] ?? null;
        if (!is_array($activations) || count($activations) !== 1 || !is_array(reset($activations))) {
            throw new license_client_exception('invalid_ticket_response',
                'LAS returned an invalid activation set.');
        }
        $activation = reset($activations);
        if (($activation['licence']['feature_code'] ?? null) !== license_config::FEATURE_CODE) {
            throw new license_client_exception('invalid_ticket_response',
                'The activation ticket does not contain the Bulk installation entitlement.');
        }
        $activationid = is_string($activation['activation_id'] ?? null) ? $activation['activation_id'] : '';
        $refreshtoken = is_string($activation['refresh_token'] ?? null) ? $activation['refresh_token'] : '';
        $assertion = is_string($activation['assertion'] ?? null) ? $activation['assertion'] : '';
        if (preg_match('/^[0-9a-f-]{36}$/Di', $activationid) !== 1
                || !str_starts_with($refreshtoken, 'LASR1-') || $assertion === '') {
            throw new license_client_exception('invalid_ticket_response',
                'LAS returned an incomplete activation response.');
        }
        $claims = self::validate_assertion($assertion, false, $activationid, $environment);
        \local_bulkinstall\local\licensing_runtime::update_activation_state(license_config::COMPONENT, [
            'activationid' => $activationid,
            'environment' => (string) $claims['environment'],
            'refreshtoken' => self::encrypt($refreshtoken),
            'assertion' => $assertion,
            'lastcheck' => time(),
            'lasterror' => null,
        ]);
        return [license_config::FEATURE_CODE => $claims];
    }

    /** Start the optional passwordless account link while retaining manual LAS1 activation. */
    public static function start_site_link(string $environment): array {
        return \local_bulkinstall\local\licensing_runtime::with_lock(license_config::FEATURE_CODE,
            static fn() => self::start_site_link_locked($environment));
    }

    private static function start_site_link_locked(string $environment): array {
        if (!in_array($environment, ['production', 'development'], true)) {
            throw new license_client_exception('invalid_environment', 'Select production or development.');
        }
        $returnurl = new \moodle_url('/local/bulkinstall/activation.php', ['accountlinked' => 1]);
        $response = (new site_link_client())->start(self::installation_id(), self::canonical_url(),
            $environment, (string) get_config('local_bulkinstall', 'version'),
            $returnurl->out(false));
        foreach (['link_id', 'device_secret', 'user_code', 'verification_uri_complete'] as $field) {
            if (!is_string($response[$field] ?? null) || $response[$field] === '') {
                throw new license_client_exception('invalid_site_link_response',
                    'Storefront returned an incomplete site-link response.');
            }
        }
        set_config('sitelinkid', $response['link_id'], 'local_bulkinstall');
        set_config('sitelinksecret', self::encrypt($response['device_secret']), 'local_bulkinstall');
        set_config('sitelinkcode', $response['user_code'], 'local_bulkinstall');
        set_config('sitelinkurl', $response['verification_uri_complete'], 'local_bulkinstall');
        set_config('sitelinkexpires', time() + (int) ($response['expires_in'] ?? 900), 'local_bulkinstall');
        set_config('sitelinkenvironment', $environment, 'local_bulkinstall');
        return $response;
    }

    /** Poll once; administrators remain in control and no browser request blocks. */
    public static function poll_site_link(): array {
        return \local_bulkinstall\local\licensing_runtime::with_lock(license_config::FEATURE_CODE,
            static fn() => self::poll_site_link_locked());
    }

    private static function poll_site_link_locked(): array {
        $linkid = (string) get_config('local_bulkinstall', 'sitelinkid');
        $secret = (string) get_config('local_bulkinstall', 'sitelinksecret');
        if ($linkid === '' || $secret === '') {
            throw new license_client_exception('site_link_missing', 'No pending account link exists.');
        }
        $response = (new site_link_client())->poll($linkid, self::decrypt($secret));
        if (($response['status'] ?? '') !== 'approved') {
            return $response;
        }
        $token = is_string($response['site_token'] ?? null) ? $response['site_token'] : '';
        $ticket = is_string($response['activation_ticket'] ?? null) ? $response['activation_ticket'] : '';
        if (preg_match('/^LMS1-[A-Za-z0-9_-]{43}$/D', $token) !== 1 || $ticket === '') {
            throw new license_client_exception('invalid_site_link_response',
                'Storefront returned an incomplete approved site link.');
        }
        $environment = (string) get_config('local_bulkinstall', 'sitelinkenvironment');
        if (!in_array($environment, ['production', 'development'], true)
                || (isset($response['environment']) && $response['environment'] !== $environment)) {
            self::clear_pending_site_link();
            throw new license_client_exception('site_environment_mismatch',
                'The approved account link belongs to another environment.');
        }
        self::activate_ticket($ticket, $environment);
        $hub = self::HUB_STATE;
        if (self::hub_available() && method_exists($hub, 'adopt_site_token')) {
            $hub::adopt_site_token($token, self::installation_id());
        } else {
            \local_bulkinstall\local\licensing_runtime::update_activation_state(license_config::COMPONENT, [
                'sitetoken' => self::encrypt($token),
            ]);
        }
        self::clear_pending_site_link();
        return $response;
    }

    /** Site token is server-only and may be exported to the optional Hub during verified adoption. */
    public static function site_token(): string {
        $state = \local_bulkinstall\local\licensing_runtime::activation_state(license_config::COMPONENT);
        $stored = (string) ($state['sitetoken'] ?? '');
        return $stored === '' ? '' : self::decrypt($stored);
    }

    public static function clear_pending_site_link(): void {
        foreach (['sitelinkid', 'sitelinksecret', 'sitelinkcode', 'sitelinkurl', 'sitelinkexpires',
                'sitelinkenvironment'] as $name) {
            unset_config($name, 'local_bulkinstall');
        }
    }

    public static function refresh_if_due(bool $force = false): bool {
        return \local_bulkinstall\local\licensing_runtime::with_lock(license_config::FEATURE_CODE, static function() use ($force) {
            return self::refresh_if_due_locked($force);
        });
    }

    private static function refresh_if_due_locked(bool $force = false): bool {
        $hub = self::HUB_STATE;
        if (self::hub_available() && $hub::record(license_config::FEATURE_CODE)) {
            try {
                return $hub::refresh_feature(license_config::FEATURE_CODE, $force);
            } catch (\Throwable $exception) {
                if (!self::standalone_is_active()) {
                    throw $exception;
                }
                return false;
            }
        }
        if (!self::is_configured()) {
            return false;
        }
        $due = $force;
        try {
            $claims = self::claims(true);
            $due = $due || (int) $claims['check_after'] <= time();
        } catch (\Throwable) {
            $due = true;
        }
        if (!$due) {
            return false;
        }
        try {
            $state = \local_bulkinstall\local\licensing_runtime::activation_state(license_config::COMPONENT);
            $activationid = (string) ($state['activationid'] ?? '');
            $environment = (string) ($state['environment'] ?? '');
            $response = (new license_client())->refresh($activationid,
                self::decrypt((string) ($state['refreshtoken'] ?? '')));
            $newtoken = is_string($response['refresh_token'] ?? null) ? $response['refresh_token'] : '';
            $assertion = is_string($response['assertion'] ?? null) ? $response['assertion'] : '';
            if (!str_starts_with($newtoken, 'LASR1-') || $assertion === '') {
                throw new license_client_exception('invalid_refresh_response',
                    'LAS returned an incomplete refresh response.');
            }
            self::validate_assertion($assertion, false, $activationid, $environment);
            \local_bulkinstall\local\licensing_runtime::update_activation_state(license_config::COMPONENT, [
                'refreshtoken' => self::encrypt($newtoken),
                'assertion' => $assertion,
                'lastcheck' => time(),
                'lasterror' => null,
            ]);
            return true;
        } catch (\Throwable $exception) {
            if ($exception instanceof license_client_exception
                    && in_array($exception->lascode, [
                        'licence_inactive',
                        'licence_expired',
                        'licence_not_started',
                    ], true)) {
                // A signed, authoritative rejection must lock this installation immediately.
                // Transport/protocol failures retain the current assertion until its normal expiry.
                self::forget();
            } else {
                \local_bulkinstall\local\licensing_runtime::update_activation_state(license_config::COMPONENT, [
                    'lastcheck' => time(),
                    'lasterror' => clean_param($exception->getMessage(), PARAM_TEXT),
                ]);
            }
            throw $exception;
        }
    }

    public static function deactivate(): void {
        \local_bulkinstall\local\licensing_runtime::with_lock(license_config::FEATURE_CODE, static function() {
            self::deactivate_locked();
        });
    }

    private static function deactivate_locked(): void {
        $hub = self::HUB_STATE;
        if (self::hub_available() && $hub::record(license_config::FEATURE_CODE)) {
            $hub::deactivate(license_config::FEATURE_CODE);
            return;
        }
        if (self::is_configured()) {
            $state = \local_bulkinstall\local\licensing_runtime::activation_state(license_config::COMPONENT);
            $activationid = (string) ($state['activationid'] ?? '');
            $token = self::decrypt((string) ($state['refreshtoken'] ?? ''));
            (new license_client())->deactivate($activationid, $token);
        }
        self::forget();
    }

    public static function forget(): void {
        $hub = self::HUB_STATE;
        if (self::hub_available() && $hub::record(license_config::FEATURE_CODE)) {
            $hub::forget_feature(license_config::FEATURE_CODE);
            return;
        }
        \local_bulkinstall\local\licensing_runtime::clear_activation_state(license_config::COMPONENT);
    }

    /** @return array<string, string> Server-only export for optional Hub adoption. */
    public static function standalone_hub_export(): array {
        return \local_bulkinstall\local\licensing_runtime::with_lock(license_config::FEATURE_CODE,
            static fn() => self::standalone_hub_export_locked());
    }

    private static function standalone_hub_export_locked(): array {
        $claims = self::standalone_claims(false);
        $state = \local_bulkinstall\local\licensing_runtime::activation_state(license_config::COMPONENT);
        return [
            'activation_id' => (string) ($state['activationid'] ?? ''),
            'refresh_token' => self::decrypt((string) ($state['refreshtoken'] ?? '')),
            'assertion' => (string) ($state['assertion'] ?? ''),
            'environment' => (string) $claims['environment'],
            'installation_id' => self::installation_id(),
            'component' => license_config::COMPONENT,
            'site_token' => self::site_token(),
        ];
    }

    /** Restore a Hub-owned activation without changing it at LAS. */
    public static function accept_hub_return(array $export): array {
        return \local_bulkinstall\local\licensing_runtime::with_lock(license_config::FEATURE_CODE,
            static fn() => self::accept_hub_return_locked($export));
    }

    private static function accept_hub_return_locked(array $export): array {
        $installationid = (string) ($export['installation_id'] ?? '');
        if (!hash_equals(self::installation_id(), $installationid)) {
            throw new license_client_exception('installation_id_conflict',
                'The Hub activation targets another installation identity.');
        }
        $activationid = (string) ($export['activation_id'] ?? '');
        $token = (string) ($export['refresh_token'] ?? '');
        $assertion = (string) ($export['assertion'] ?? '');
        $environment = (string) ($export['environment'] ?? '');
        if (!str_starts_with($token, 'LASR1-')) {
            throw new license_client_exception('invalid_activation_response', 'The Hub return payload is incomplete.');
        }
        $claims = self::validate_assertion($assertion, false, $activationid, $environment);
        $updates = [
            'activationid' => $activationid,
            'environment' => (string) $claims['environment'],
            'refreshtoken' => self::encrypt($token),
            'assertion' => $assertion,
            'lastcheck' => time(),
            'lasterror' => null,
        ];
        if (is_string($export['site_token'] ?? null) && $export['site_token'] !== '') {
            $updates['sitetoken'] = self::encrypt($export['site_token']);
        }
        \local_bulkinstall\local\licensing_runtime::update_activation_state(license_config::COMPONENT, $updates);
        return $claims;
    }

    public static function retire_standalone_copy(): void {
        \local_bulkinstall\local\licensing_runtime::with_lock(license_config::FEATURE_CODE,
            static fn() => self::retire_standalone_copy_locked());
    }

    private static function retire_standalone_copy_locked(): void {
        \local_bulkinstall\local\licensing_runtime::clear_activation_state(license_config::COMPONENT);
    }

    public static function require_active(): void {
        if (!self::is_active()) {
            throw new \moodle_exception('licenserequired', 'local_bulkinstall',
                new \moodle_url('/local/bulkinstall/activation.php'));
        }
    }

    /** @return array<string, mixed> */
    private static function validate_assertion(string $assertion, bool $allowexpired,
            string $expectedactivation, string $expectedenvironment): array {
        $parts = explode('.', $assertion);
        if (count($parts) !== 3) {
            throw new license_client_exception('invalid_assertion', 'The stored licence assertion is malformed.');
        }
        [$encodedheader, $encodedclaims, $encodedsignature] = $parts;
        $headerjson = license_client::base64url_decode($encodedheader);
        $claimsjson = license_client::base64url_decode($encodedclaims);
        $signature = license_client::base64url_decode($encodedsignature);
        try {
            $header = is_string($headerjson) ? json_decode($headerjson, true, 16, JSON_THROW_ON_ERROR) : null;
            $claims = is_string($claimsjson) ? json_decode($claimsjson, true, 32, JSON_THROW_ON_ERROR) : null;
        } catch (\JsonException) {
            throw new license_client_exception('invalid_assertion', 'The stored licence assertion is malformed.');
        }
        if (!is_array($header) || !is_array($claims) || ($header['alg'] ?? null) !== 'EdDSA'
                || ($header['typ'] ?? null) !== 'JWT' || !is_string($header['kid'] ?? null)) {
            throw new license_client_exception('invalid_assertion_header', 'The licence assertion header is invalid.');
        }
        $publickey = license_config::public_key($header['kid']);
        if ($publickey === null || !is_string($signature) || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES
                || !sodium_crypto_sign_verify_detached($signature, $encodedheader . '.' . $encodedclaims, $publickey)) {
            throw new license_client_exception('invalid_assertion_signature',
                'The licence assertion signature is invalid.');
        }
        $strings = ['iss', 'aud', 'sub', 'activation_id', 'feature_code', 'installation_id',
            'canonical_url', 'environment', 'status'];
        foreach ($strings as $name) {
            if (!is_string($claims[$name] ?? null) || $claims[$name] === '') {
                throw new license_client_exception('invalid_assertion_claims',
                    'The licence assertion is missing a required claim.');
            }
        }
        foreach (['iat', 'nbf', 'exp', 'check_after', 'grace_until'] as $name) {
            if (!is_int($claims[$name] ?? null)) {
                throw new license_client_exception('invalid_assertion_claims',
                    'The licence assertion has an invalid time claim.');
            }
        }
        $matches = ($claims['iss'] === license_config::ISSUER)
            && ($claims['aud'] === license_config::AUDIENCE)
            && ($claims['feature_code'] === license_config::FEATURE_CODE)
            && ($claims['installation_id'] === self::installation_id())
            && ($claims['canonical_url'] === self::canonical_url())
            && ($expectedactivation === '' || ($claims['activation_id'] === $expectedactivation
                && $claims['sub'] === $expectedactivation))
            && ($expectedenvironment === '' || $claims['environment'] === $expectedenvironment)
            && in_array($claims['environment'], ['production', 'development'], true)
            && in_array($claims['status'], ['active', 'grace'], true);
        if (!$matches) {
            throw new license_client_exception('assertion_scope_mismatch',
                'The licence assertion does not belong to this installation and feature.');
        }
        $now = time();
        if ($claims['iat'] > $now + license_config::CLOCK_SKEW
                || $claims['nbf'] > $now + license_config::CLOCK_SKEW
                || (!$allowexpired && !\local_bulkinstall\local\licensing_runtime::is_permanent_free($claims) && $claims['exp'] < $now - license_config::CLOCK_SKEW)) {
            throw new license_client_exception('assertion_not_current', 'The licence assertion is not currently valid.');
        }
        $claims = \local_bulkinstall\local\licensing_runtime::effective_claims($claims);
        if (!$allowexpired && $claims['status'] === 'expired') {
            throw new license_client_exception('assertion_not_current', 'The signed licence term and grace period have ended.');
        }
        return $claims;
    }

    private static function encrypt(string $plaintext): string {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $key = self::encryption_key();
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $key);
        sodium_memzero($key);
        return rtrim(strtr(base64_encode($nonce . $ciphertext), '+/', '-_'), '=');
    }

    private static function decrypt(string $encoded): string {
        $decoded = license_client::base64url_decode($encoded);
        if (!is_string($decoded) || strlen($decoded) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new license_client_exception('refresh_token_unavailable',
                'The local activation refresh token is unavailable.');
        }
        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $key = self::encryption_key();
        $plaintext = sodium_crypto_secretbox_open(substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            $nonce, $key);
        sodium_memzero($key);
        if (!is_string($plaintext)) {
            throw new license_client_exception('refresh_token_unavailable',
                'The local activation refresh token cannot be decrypted.');
        }
        return $plaintext;
    }

    private static function encryption_key(): string {
        global $CFG;

        $configured = (string) ($CFG->local_bulkinstall_token_key ?? '');
        if ($configured !== '') {
            $key = base64_decode($configured, true);
            if (!is_string($key) || strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
                throw new license_client_exception('encryption_key_invalid',
                    'The configured Bulk installation token key must be a base64-encoded 256-bit value.');
            }
            return $key;
        }
        $salt = (string) ($CFG->passwordsaltmain ?? '');
        if ($salt !== '') {
            return sodium_crypto_generichash("local_bulkinstall\0" . $salt, '',
                SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        }

        // New Moodle installations do not necessarily define passwordsaltmain. Keep a site-local key outside the
        // database so a normal plugin/database backup cannot silently transfer an activation to another installation.
        $directory = rtrim((string) $CFG->dataroot, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'local_bulkinstall';
        if (!make_writable_directory($directory, false)) {
            throw new license_client_exception('encryption_key_unavailable',
                'The Bulk installation licence-key directory could not be created in moodledata.');
        }
        $path = $directory . DIRECTORY_SEPARATOR . 'licence-token.key';
        $handle = @fopen($path, 'c+b');
        if ($handle === false || !flock($handle, LOCK_EX)) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            throw new license_client_exception('encryption_key_unavailable',
                'The Bulk installation site-local licence key could not be locked.');
        }
        try {
            rewind($handle);
            $key = stream_get_contents($handle);
            if ($key === '') {
                $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
                rewind($handle);
                @chmod($path, 0600);
                $truncated = ftruncate($handle, 0);
                $written = $truncated ? fwrite($handle, $key) : false;
                if (!$truncated || $written !== strlen($key) || !fflush($handle)) {
                    ftruncate($handle, 0);
                    fflush($handle);
                    throw new license_client_exception('encryption_key_unavailable',
                        'The Bulk installation site-local licence key could not be stored.');
                }
            }
            if (!is_string($key) || strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
                throw new license_client_exception('encryption_key_invalid',
                    'The Bulk installation site-local licence key is invalid.');
            }
            return $key;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private static function hub_available(): bool {
        if (!(bool) get_config('local_lmh108', 'version') || !class_exists(self::HUB_STATE)) {
            return false;
        }
        $hub = self::HUB_STATE;
        return !method_exists($hub, 'delegation_available') || $hub::delegation_available();
    }

}
