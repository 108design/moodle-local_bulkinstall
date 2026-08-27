<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.
namespace local_bulkinstall\local;

/** Minimal product-local LAS v1 client with pinned Ed25519 response verification. */
final class license_client {
    /** @return array<string, mixed> */
    public function features(): array {
        return $this->request('GET', '/features', null, null, self::uuid());
    }

    /** @return array<string, mixed> */
    public function activate(string $licensekey, string $installationid, string $canonicalurl,
            string $environment, string $version): array {
        return $this->request('POST', '/activations', [
            'licence_key' => $licensekey,
            'feature_code' => license_config::FEATURE_CODE,
            'installation_id' => $installationid,
            'canonical_url' => $canonicalurl,
            'environment' => $environment,
            'plugin' => ['component' => license_config::COMPONENT, 'version' => $version],
        ], null, self::uuid());
    }

    /** Redeem a one-time account-link ticket for this standalone plugin. */
    public function redeem_ticket(string $ticket, string $installationid, string $canonicalurl,
            string $environment, string $version, string $idempotencykey): array {
        return $this->request('POST', '/activation-tickets/redeem', [
            'activation_ticket' => $ticket,
            'installation_id' => $installationid,
            'canonical_url' => $canonicalurl,
            'environment' => $environment,
            'plugins' => [[
                'feature_code' => license_config::FEATURE_CODE,
                'component' => license_config::COMPONENT,
                'version' => $version,
            ]],
        ], null, $idempotencykey);
    }

    /** @return array<string, mixed> */
    public function refresh(string $activationid, string $refreshtoken): array {
        return $this->request('POST', '/activations/' . rawurlencode($activationid) . '/refresh',
            (object) [], $refreshtoken, self::uuid());
    }

    /** @return array<string, mixed> */
    public function deactivate(string $activationid, string $refreshtoken): array {
        return $this->request('DELETE', '/activations/' . rawurlencode($activationid),
            null, $refreshtoken, self::uuid());
    }

    /** Stable UUID used so a successful LAS rotation can be replayed after a local failure. */
    public static function deterministic_uuid(string $seed): string {
        $bytes = substr(hash('sha256', $seed, true), 0, 16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x50);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-'
            . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }

    /** @param array<string, mixed>|object|null $payload @return array<string, mixed> */
    private function request(string $method, string $path, array|object|null $payload,
            ?string $bearertoken, string $idempotencykey): array {
        if (!extension_loaded('curl') || !extension_loaded('sodium')) {
            throw new license_client_exception('runtime_unavailable',
                'PHP cURL and Sodium are required for licence activation.');
        }
        $url = license_config::BASE_URL . $path;
        if (parse_url($url, PHP_URL_SCHEME) !== 'https') {
            throw new license_client_exception('invalid_las_url', 'The licence service must use HTTPS.');
        }
        try {
            $body = $payload === null ? '' : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new license_client_exception('invalid_request_json', 'The activation request could not be encoded.');
        }
        $headers = ['Accept: application/json', 'Idempotency-Key: ' . $idempotencykey];
        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
        }
        if ($bearertoken !== null) {
            $headers[] = 'Authorization: Bearer ' . $bearertoken;
        }
        [$status, $responseheaders, $responsebody] = $this->send($method, $url, $body, $headers);
        $this->verify_response($status, $responseheaders, $responsebody);
        try {
            $decoded = json_decode($responsebody, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new license_client_exception('invalid_las_json', 'LAS returned invalid JSON.', $status);
        }
        if (!is_array($decoded)) {
            throw new license_client_exception('invalid_las_json', 'LAS returned invalid JSON.', $status);
        }
        if ($status < 200 || $status >= 300) {
            throw new license_client_exception(
                is_string($decoded['code'] ?? null) ? $decoded['code'] : 'las_error',
                is_string($decoded['detail'] ?? null) ? $decoded['detail'] : 'LAS rejected the request.',
                $status
            );
        }
        return $decoded;
    }

    /** @return array{int, array<string, string>, string} */
    private function send(string $method, string $url, string $body, array $headers): array {
        $responseheaders = [];
        $handle = curl_init($url);
        if ($handle === false) {
            throw new license_client_exception('transport_unavailable', 'The LAS connection could not be initialised.');
        }
        $options = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseheaders): int {
                $position = strpos($line, ':');
                if ($position !== false) {
                    $responseheaders[strtolower(trim(substr($line, 0, $position)))] =
                        trim(substr($line, $position + 1));
                }
                return strlen($line);
            },
        ];
        if ($body !== '') {
            $options[CURLOPT_POSTFIELDS] = $body;
        }
        if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTPS')) {
            $options[CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS;
        }
        curl_setopt_array($handle, $options);
        $responsebody = curl_exec($handle);
        if (!is_string($responsebody)) {
            $detail = clean_param(curl_error($handle), PARAM_TEXT);
            curl_close($handle);
            throw new license_client_exception('transport_failed', $detail);
        }
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);
        return [$status, $responseheaders, $responsebody];
    }

    private function verify_response(int $status, array $headers, string $body): void {
        foreach (['las-key-id', 'las-timestamp', 'las-content-sha256', 'las-signature'] as $required) {
            if (!is_string($headers[$required] ?? null)) {
                throw new license_client_exception('unsigned_las_response',
                    'LAS response signature headers are missing.', $status);
            }
        }
        $timestamp = $headers['las-timestamp'];
        if (!ctype_digit($timestamp) || abs(time() - (int) $timestamp) > license_config::CLOCK_SKEW) {
            throw new license_client_exception('invalid_las_timestamp',
                'LAS response timestamp is outside the permitted window.', $status);
        }
        $digest = hash('sha256', $body);
        if (!hash_equals($digest, strtolower($headers['las-content-sha256']))) {
            throw new license_client_exception('las_digest_mismatch', 'LAS response digest is invalid.', $status);
        }
        $signature = self::base64url_decode($headers['las-signature']);
        $publickey = license_config::public_key($headers['las-key-id']);
        $message = implode("\n", ['LAS1-RESPONSE', (string) $status, '', $timestamp, $digest]);
        if ($publickey === null || !is_string($signature) || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES
                || !sodium_crypto_sign_verify_detached($signature, $message, $publickey)) {
            throw new license_client_exception('invalid_las_signature',
                'LAS response signature verification failed.', $status);
        }
    }

    public static function base64url_decode(string $value): string|false {
        if (preg_match('/^[A-Za-z0-9_-]+$/D', $value) !== 1) {
            return false;
        }
        return base64_decode(strtr($value, '-_', '+/')
            . str_repeat('=', (4 - strlen($value) % 4) % 4), true);
    }

    private static function uuid(): string {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-'
            . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }
}
