<?php
namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/las_deployment.php';

/** Pinned LAS trust anchor and immutable product identity. */
final class license_config {
    public const BASE_URL = \DESIGN108_LAS_BASE_URL;
    public const STOREFRONT_LINK_URL = \DESIGN108_STOREFRONT_LINK_URL;
    public const FEATURE_CODE = 'bulkinstall.free';
    public const COMPONENT = 'local_bulkinstall';
    public const ISSUER = \DESIGN108_LAS_ISSUER;
    public const AUDIENCE = '108design-moodle-plugin';
    public const KEY_ID = \DESIGN108_LAS_KEY_ID;
    public const PUBLIC_KEY_BASE64 = \DESIGN108_LAS_PUBLIC_KEY_BASE64;
    public const CLOCK_SKEW = 300;

    /** Ship old and new authorised pins together before rotating the signing key. */
    public const TRUSTED_PUBLIC_KEYS = [self::KEY_ID => self::PUBLIC_KEY_BASE64];

    public static function public_key(string $keyid): ?string {
        if (!isset(self::TRUSTED_PUBLIC_KEYS[$keyid])) {
            return null;
        }
        $key = base64_decode(self::TRUSTED_PUBLIC_KEYS[$keyid], true);
        return is_string($key) && strlen($key) === SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES ? $key : null;
    }
}
