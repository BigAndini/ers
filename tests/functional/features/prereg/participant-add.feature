Feature: ERS Participant Add Page
    In order to ensure a participant can be added
    As a guest
    I need to access the participant add page

    Scenario: Adding a participant to the order
        Given I am on "/participant/add"
        Then I should see "Add Person"
        And I should see "Note for personal data"
        And I fill in "Andi" for "firstname"
        And I fill in "Nitsche" for "surname"
        And I fill in "29.03.1985" for "birthday"
        And I fill in "andi@inbaz.org" for "email"
        And I select "Germany" from "Country_id"
        Then I press "submit"
        And I go to "/participant"
        Then I should see "Andi Nitsche"
        And I should see "29.03.1985"
        And I should see "andi@inbaz.org"
        And I should see "Germany"
