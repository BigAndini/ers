Feature: ERS AdminPanel
    In order ensure the app works
    As a developer
    I need to access the admin panel

    Scenario: Accessing the admin panel
        Given I am logged in as "test@inbaz.org" with "toosh8Laiz7f"
        And I am on "/admin/tax"
        Then I should see "Add tax group"
        And I follow "Add tax group"
        Then I should see "Add tax group"
        And I should see "Name"
        And I should see "Percentage"
        Then I fill in the following:
            | name | Default |
            | percentage | 19 |
        And I press "Add"
        Then the url should match "/admin/tax"
        And I should see "Default"
        And I should see "19"
        Then I follow "Edit"
        And I should see "Edit tax"
        Then I fill in the following:
            | name | Food |
            | percentage | 7 |
        And I press "Edit"
        Then the url should match "/admin/tax"
        And I should see "Food"
        And I should see "7"
        
