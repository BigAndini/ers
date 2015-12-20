Feature: ERS Participant List Page
    In order ensure the participants can be viewed
    As a guest
    I need to access the participant list page

    Scenario: Accessing the participant list page
        Given I am on "/participant"
        Then I should see "My Persons"
