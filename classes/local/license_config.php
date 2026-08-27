<?php
namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

/** Pinned LAS trust anchor and immutable product identity. */
final class license_config {
    public const BASE_URL = 'https://l-a-s.108design.com/v1';
    public const STOREFRONT_LINK_URL = 'https://cms.108design.com/local/storefront/site_link_api.php';
    public const FEATURE_CODE = 'bulkinstall.free';
    public const COMPONENT = 'local_bulkinstall';
    public const ISSUER = 'https://l-a-s.108design.com';
    public const AUDIENCE = '108design-moodle-plugin';
    public const KEY_ID = 'las-development-2026-01';
    public const PUBLIC_KEY_BASE64 = 'YvqrCEmbfxzmPDsOnrEN4NQNo0JqMOqc4Y/pYaoLr6s=';
    public const CLOCK_SKEW = 300;

    public static function public_key(string $keyid): ?string {
        if ($keyid !== self::KEY_ID) {
            return null;
        }
        $key = base64_decode(self::PUBLIC_KEY_BASE64, true);
        return is_string($key) && strlen($key) === SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES ? $key : null;
    }
}
