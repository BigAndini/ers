Feature: ERS Buyer Pages
    In order to ensure somebody can buy something
    As a guest
    I need to add/edit/delete a buyer

    Scenario: Add a buyer info
        Given I am on "/buyer/add"
        Then I should see "Add Buyer"
        And I fill in "Andi" for "firstname"
        And I fill in "Nitsche" for "surname"
        And I fill in "buyer@inbaz.org" for "email"
        Then I press "submit"
        Then the url should match "/order/buyer"
        Then I should see "Andi Nitsche"
        And I should see "andi@inbaz.org"
        And I follow "edit"
        Then the url should match "/buyer/edit/[0-9]*"
        And I fill in "Andreas" for "firstname"
        And I fill in "Nitsche2" for "surname"
        And I fill in "buyer2@inbaz.org" for "email"
        Then I press "submit"
        Then the url should match "/order/buyer"
        Then I should see "Andreas Nitsche2"
        And I should see "test@inbaz.org"
        Then I go to "/participant"
        And I should see "Andreas Nitsche2"
        And I should see "test@inbaz.org"
        And I follow "delete"
        Then the url should match "/participant/delete/[0-9]*"
        And I press "del"
        And I go to "/order/buyer"
        And I should not see "Andreas Nitsche2"
        And I should not see "test@inbaz.org"