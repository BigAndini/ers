Feature: ERS Default Page
    In order ensure the app works
    As a developer
    I need to access the default landing page

    Scenario: Accessing the landing page
        Given I am on "/"
        Then I should see "EJC Registration System"
        And I should see "by European Juggling Association All rights reserved"
