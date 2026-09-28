<?php
// This file is part of a 108design source-available software product.

namespace local_bulkinstall;

defined('MOODLE_INTERNAL') || die();

/** Free software keeps native administration and package integrity checks. */
final class activation_ui_test extends \basic_testcase {
    public function test_installation_has_no_activation_gate_but_keeps_security_controls(): void {
        foreach (['index.php', 'review.php', 'install.php'] as $file) {
            $source = file_get_contents(__DIR__ . '/../' . $file);
            $this->assertStringNotContainsString('license_state::', $source);
            $this->assertStringContainsString('require_capability(', $source);
        }
        $this->assertStringContainsString('require_sesskey()', file_get_contents(__DIR__ . '/../install.php'));
        $this->assertSame([], \local_bulkinstall\local\licensing\feature_provider::manifest());
        $this->assertStringNotContainsString("tabobject('licenses'",
            file_get_contents(__DIR__ . '/../classes/local/admin_navigation.php'));
        $this->assertStringNotContainsString('refresh_license', file_get_contents(__DIR__ . '/../db/tasks.php'));
    }
}
