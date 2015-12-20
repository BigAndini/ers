Feature: ERS Help Page
    In order ensure the app works
    As a developer
    I need to access the help page

    Scenario: Accessing the help page
        Given I am on "/info/help"
        Then I should see "Help page for the EJC Registration System (ERS)"
