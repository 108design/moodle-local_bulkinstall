<?php
namespace local_bulkinstall\local\licensing;

defined('MOODLE_INTERNAL') || die();

/** Optional manifest discovered by local_lmh108; Bulk installation never depends on the Hub. */
final class feature_provider {
    public static function manifest(): array {
        $base = [
            'label' => get_string('licenseproductname', 'local_bulkinstall'),
            'mode' => 'entitled',
            'entitlements' => ['bulkinstall.free'],
            'policyarea' => 'other',
            'policyproduct' => 'bulkinstall',
            'policyproductlabel' => get_string('pluginname', 'local_bulkinstall'),
            'policyorder' => 5,
            'degradation' => 'Fail closed for installation; preserve staged data until normal expiry.',
            'discovery' => 'Show only to site administrators.',
            'adapter' => adapter::class,
            'autofreeclaim' => true,
        ];
        return ['local_bulkinstall/install' => $base + ['operation' => 'install']];
    }
}
