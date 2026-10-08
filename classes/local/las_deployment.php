<?php
namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

// Generated independent deployment boundary. No editable database trust configuration.
(static function(): void {
    $defaults = [
        'DESIGN108_LAS_BASE_URL' => 'https://l-a-s.108design.com/v1',
        'DESIGN108_LAS_ISSUER' => 'https://l-a-s.108design.com',
        'DESIGN108_STOREFRONT_LINK_URL' => 'https://cms.108design.com/local/storefront/site_link_api.php',
        'DESIGN108_LAS_KEY_ID' => 'las-development-2026-01',
        'DESIGN108_LAS_PUBLIC_KEY_BASE64' => 'YvqrCEmbfxzmPDsOnrEN4NQNo0JqMOqc4Y/pYaoLr6s=',
    ];
    $configured = array_filter(array_keys($defaults), 'defined');
    if ($configured && count($configured) !== count($defaults)) {
        throw new \RuntimeException('Incomplete server-side LAS deployment configuration.');
    }
    if (!defined('DESIGN108_LAS_IS_CUSTOM')) {
        define('DESIGN108_LAS_IS_CUSTOM', count($configured) > 0);
    }
    foreach ($defaults as $name => $value) {
        if (!defined($name)) { define($name, $value); }
    }
    foreach (['DESIGN108_LAS_BASE_URL', 'DESIGN108_LAS_ISSUER', 'DESIGN108_STOREFRONT_LINK_URL'] as $name) {
        $value = constant($name);
        $parts = is_string($value) ? parse_url($value) : false;
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])
                || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new \RuntimeException('Invalid server-side LAS HTTPS endpoint.');
        }
    }
    if (DESIGN108_LAS_BASE_URL !== DESIGN108_LAS_ISSUER . '/v1'
            || !is_string(DESIGN108_LAS_KEY_ID)
            || !preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,99}$/D', DESIGN108_LAS_KEY_ID)
            || !is_string(DESIGN108_LAS_PUBLIC_KEY_BASE64)
            || strlen((string)base64_decode(DESIGN108_LAS_PUBLIC_KEY_BASE64, true)) !== 32) {
        throw new \RuntimeException('Invalid server-side LAS trust configuration.');
    }
})();
