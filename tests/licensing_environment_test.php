<?php
namespace local_bulkinstall;

defined('MOODLE_INTERNAL') || die();

/** Regression checks for deliberate installation environment selection. */
final class licensing_environment_test extends \basic_testcase {
    public function test_manual_and_site_link_activation_require_an_explicit_environment(): void {
        $page = file_get_contents(__DIR__ . '/../activation.php');
        $state = file_get_contents(__DIR__ . '/../classes/local/license_state.php');

        $this->assertStringContainsString("required_param('environment', PARAM_ALPHA)", $page);
        $this->assertStringContainsString("'sitelinkenvironment'", $state);
        $this->assertStringContainsString("'required' => 'required'", $page);
        $this->assertStringNotContainsString('DEBUG_DEVELOPER', $state);
        $this->assertStringNotContainsString("str_ends_with(\$host, '.test')", $state);
    }
}
