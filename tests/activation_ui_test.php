<?php
// This file is part of a 108design source-available software product.

namespace local_bulkinstall;

defined('MOODLE_INTERNAL') || die();

/** Static regression checks for the two-step free-activation interface. */
final class activation_ui_test extends \advanced_testcase {
    public function test_account_link_actions_are_numbered_and_spaced(): void {
        $page = file_get_contents(__DIR__ . '/../activation.php');
        $client = file_get_contents(__DIR__ . '/../classes/local/site_link_client.php');
        $css = file_get_contents(__DIR__ . '/../styles.css');

        $this->assertStringContainsString("get_string('opensitelink', 'local_bulkinstall')", $page);
        $this->assertStringContainsString("get_string('checksitelink', 'local_bulkinstall')", $page);
        $this->assertStringContainsString('local-bulkinstall-actions', $page);
        $this->assertStringContainsString("requires->css(new moodle_url('/local/bulkinstall/styles.css'", $page);
        $this->assertStringContainsString("'return_url' => \$returnurl", $client);
        $this->assertStringContainsString('gap: 0.5rem', $css);
    }

    public function test_license_management_is_a_stable_tab_not_an_install_page_button(): void {
        $activation = file_get_contents(__DIR__ . '/../activation.php');
        $index = file_get_contents(__DIR__ . '/../index.php');
        $navigation = file_get_contents(__DIR__ . '/../classes/local/admin_navigation.php');

        $this->assertStringNotContainsString("get_string('managelicense', 'local_bulkinstall')", $index);
        $this->assertStringContainsString("admin_navigation::render('install')", $index);
        $this->assertStringContainsString("admin_navigation::render('licenses')", $activation);
        $this->assertStringContainsString("new \\tabobject('licenses'", $navigation);
        $this->assertStringContainsString("get_string('licensestatushub', 'local_bulkinstall')", $activation);
        $this->assertStringContainsString("get_string('managewithlicensemanager', 'local_bulkinstall')", $activation);
        $this->assertStringContainsString("get_string('completehandover', 'local_bulkinstall')", $activation);
        $this->assertStringContainsString("get_string(\$hubrecord ? 'completehandover' : 'activateinlicensemanager'",
            $activation);
    }
}
