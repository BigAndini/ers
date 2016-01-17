Feature: ERS Impressum Page
    In order ensure the app works
    As a developer
    I need to access the impressum page

    Scenario: Accessing the impressum page
        Given I am on "/info/impressum"
        Then I should see "Stichting European Juggling Association (EJA)"
