<?php
namespace local_bulkinstall\local\licensing;
defined('MOODLE_INTERNAL') || die();
/** No paid or permanent-Free activation. Historical adapter state remains untouched. */
final class feature_provider {
    public static function manifest(): array { return []; }
}
