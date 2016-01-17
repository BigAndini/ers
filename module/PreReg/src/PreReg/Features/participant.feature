Feature: ERS Participant Add Page
    In order to ensure a participant can be added
    As a guest
    I need to access the participant add page

    Scenario: Add/edit/delete a participant that does not exist, yet.
        Given I am on "/participant/add"
        Then I should see "Add Person"
        And I should see "Note for personal data"
        And I fill in "Andreas" for "firstname"
        And I fill in "Nietsche" for "surname"
        And I fill in "28.03.1985" for "birthday"
        And I fill in "andix@inbaz.org" for "email"
        And I select "Germany" from "Country_id"
        Then I press "submit"
        And I go to "/participant"
        Then I should see "Andreas Nietsche"
        And I should see "28.03.1985"
        And I should see "andix@inbaz.org"
        And I should see "Germany"
        Then I follow "edit"
        And the url should match "/participant/edit/[0-9]*"
        And I should see "Edit Person"
        And I fill in "Andi" for "firstname"
        And I fill in "Nitsche" for "surname"
        And I fill in "29.03.1985" for "birthday"
        And I fill in "andi@inbaz.org" for "email"
        And I select "Germany" from "Country_id"
        And I press "submit"
        And the url should match "/participant"
        Then I should see "Andi Nitsche"
        And I should see "29.03.1985"
        And I should see "andi@inbaz.org"
        And I should see "Germany"
        Then I follow "delete"
        Then the url should match "/participant/delete/[0-9]*"
        And I press "del"
        And I go to "/participant"
        And I should not see "Andi Nitsche"
        And I should not see "andi@inbaz.org"
        
    # Scenario: Add/edit/delete a participant that already exists inactive
    # Scenario: Add/edit/delete a participant that already exists active