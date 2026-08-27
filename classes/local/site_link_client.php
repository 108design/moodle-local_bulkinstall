<?php
namespace local_bulkinstall\local;

defined('MOODLE_INTERNAL') || die();

/** Minimal Storefront device-link client; the resulting LAS ticket is independently signed and verified. */
final class site_link_client {
    public function start(string $installationid, string $canonicalurl, string $environment,
            string $version, string $returnurl): array {
        return $this->request([
            'action' => 'start',
            'installation_id' => $installationid,
            'canonical_url' => $canonicalurl,
            'environment' => $environment,
            'component' => license_config::COMPONENT,
            'version' => $version,
            'return_url' => $returnurl,
        ]);
    }

    public function poll(string $linkid, string $devicesecret): array {
        return $this->request([
            'action' => 'poll',
            'link_id' => $linkid,
            'device_secret' => $devicesecret,
        ]);
    }

    private function request(array $payload): array {
        if (!extension_loaded('curl')) {
            throw new license_client_exception('runtime_unavailable',
                'PHP cURL is required for the account link.');
        }
        $url = license_config::STOREFRONT_LINK_URL;
        if (parse_url($url, PHP_URL_SCHEME) !== 'https') {
            throw new license_client_exception('invalid_storefront_url',
                'The Storefront account-link service must use HTTPS.');
        }
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $handle = curl_init($url);
        if ($handle === false) {
            throw new license_client_exception('transport_unavailable',
                'The Storefront connection could not be initialised.');
        }
        $options = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTPS')) {
            $options[CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS;
        }
        curl_setopt_array($handle, $options);
        $response = curl_exec($handle);
        if (!is_string($response)) {
            $detail = clean_param(curl_error($handle), PARAM_TEXT);
            curl_close($handle);
            throw new license_client_exception('transport_failed', $detail);
        }
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);
        try {
            $decoded = json_decode($response, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new license_client_exception('invalid_storefront_json',
                'Storefront returned invalid JSON.', $status);
        }
        if (!is_array($decoded)) {
            throw new license_client_exception('invalid_storefront_json',
                'Storefront returned invalid JSON.', $status);
        }
        if ($status < 200 || $status >= 300) {
            throw new license_client_exception(is_string($decoded['code'] ?? null)
                ? $decoded['code'] : 'storefront_error', is_string($decoded['detail'] ?? null)
                ? $decoded['detail'] : 'Storefront rejected the request.', $status);
        }
        return $decoded;
    }
}
