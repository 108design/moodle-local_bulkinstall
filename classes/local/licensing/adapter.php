<?php
namespace local_bulkinstall\local\licensing;

defined('MOODLE_INTERNAL') || die();

use local_bulkinstall\local\license_config;
use local_bulkinstall\local\license_state;

/** Token-free observe/export contract for the optional 108design License Manager. */
final class adapter {
    public static function observe(string $entitlement): array {
        if ($entitlement !== license_config::FEATURE_CODE) {
            return ['active' => false, 'configured' => false, 'source' => 'local_bulkinstall'];
        }
        $claims = [];
        $error = '';
        if (license_state::standalone_is_configured()) {
            try {
                $claims = license_state::standalone_claims(false);
            } catch (\Throwable $exception) {
                $error = clean_param($exception->getMessage(), PARAM_TEXT);
            }
        }
        return [
            'active' => $claims !== [],
            'configured' => license_state::standalone_is_configured(),
            'source' => 'local_bulkinstall',
            'claims' => $claims,
            'activationurl' => (new \moodle_url('/local/bulkinstall/activation.php'))->out(false),
            'error' => $error,
        ];
    }

    public static function export_for_hub(string $entitlement): array {
        if ($entitlement !== license_config::FEATURE_CODE) {
            throw new \invalid_parameter_exception('Unknown Bulk installation entitlement.');
        }
        return license_state::standalone_hub_export();
    }

    public static function retire_after_hub_adoption(string $entitlement): void {
        if ($entitlement !== license_config::FEATURE_CODE) {
            throw new \invalid_parameter_exception('Unknown Bulk installation entitlement.');
        }
        license_state::retire_standalone_copy();
    }
}
