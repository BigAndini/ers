Feature: ERS AdminPanel
    In order ensure the app works
    As a developer
    I need to access the admin panel

    Scenario: Accessing the admin panel
        Given I am logged in as "test@inbaz.org" with "toosh8Laiz7f"
        And I am on "/admin"
        Then I should see "Admin"
        And I should see "Onsite"
        And I should see "add variants to your products"
        
