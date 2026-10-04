@local @local_bulkinstall
Feature: Access the bulk plugin installation page
  In order to deploy multiple plugin packages
  As a site administrator
  I need a bulk installation entry in the plugin administration

  Background:
    Given the following config values are set as admin:
      | disableupdateautodeploy | 0 |

  Scenario: Administrator opens bulk installation
    Given I log in as "admin"
    When I navigate to "Plugins > Bulk installation" in site administration
    Then I should see "Upload plugin packages"
    And I should see "Plugin ZIP packages"
