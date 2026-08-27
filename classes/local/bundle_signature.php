<?php
namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

/** Verifies publisher signatures on prepared bundle manifests. */
final class bundle_signature {
    public const PUBLISHER = '108design';
    public const KEY_ID = 'bundle-2026-01';
    public const PUBLIC_KEY_BASE64 = 'IYjEqgvZ1g6dg28GMPLLfjmLgXtSLyN3SC8aHGUZJds=';

    public static function verify(array $manifest, string $signature): bool {
        if (!extension_loaded('sodium') || preg_match('/^[A-Za-z0-9_-]{86}$/D', $signature) !== 1) {
            return false;
        }
        $publickey = base64_decode(self::PUBLIC_KEY_BASE64, true);
        $rawsignature = self::base64url_decode($signature);
        return is_string($publickey) && strlen($publickey) === SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES
            && is_string($rawsignature) && strlen($rawsignature) === SODIUM_CRYPTO_SIGN_BYTES
            && sodium_crypto_sign_verify_detached($rawsignature, self::canonical($manifest), $publickey);
    }

    /** Canonical JSON contract shared with the offline signer. */
    public static function canonical(array $manifest): string {
        $document = [
            'format' => bundle_manifest::FORMAT,
            'formatversion' => 2,
            'id' => (string) $manifest['id'],
            'name' => (string) $manifest['name'],
            'version' => (string) $manifest['version'],
            'description' => $manifest['description'] === null ? null : (string) $manifest['description'],
            'publisher' => self::PUBLISHER,
            'keyid' => self::KEY_ID,
            'plugins' => array_values(array_map(static fn(array $plugin): array => [
                'file' => (string) $plugin['file'],
                'component' => (string) $plugin['component'],
                'sha256' => strtolower((string) $plugin['sha256']),
            ], $manifest['plugins'])),
        ];
        return json_encode($document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private static function base64url_decode(string $value): string|false {
        return base64_decode(strtr($value, '-_', '+/')
            . str_repeat('=', (4 - strlen($value) % 4) % 4), true);
    }
}
