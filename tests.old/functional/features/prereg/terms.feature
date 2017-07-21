Feature: ERS Terms Page
    In order ensure the app works
    As a developer
    I need to access the terms page

    Scenario: Accessing the terms page
        Given I am on "/info/terms"
        Then I should see "Terms and Conditions"
