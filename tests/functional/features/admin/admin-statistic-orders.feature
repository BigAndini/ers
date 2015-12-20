Feature: ERS AdminPanel Statistic Orders
    In order ensure the app works
    As an admin
    I need to access the admin statistic orders page

    #Scenario: Accessing the admin statistic orders page not logged in
    #    Given I am on "/admin/statistic/orders"
    #    Then I should see "You are not authorized to access"

    Scenario: Accessing the admin statistic orders page needs a login
        Given I am on "/user/login"
        When I fill in the following:
            | identity | test@inbaz.org |
            | credential | toosh8Laiz7f |
        And I check "remember_me"
        And I press "submit"
        Then the response status code should be 200
        And I should not see "Authentication failed. Please try again."
        And I should see "My Profile"
        And I should see "Logout"

    Scenario: After a login it should be possible to access the users profile page
        Given I am on "/profile"
        Then I should see "My Profile"
        And I should see "Logout"
        And I should see "Change my user data"

    Scenario: Accessing the admin statistic orders page
        Given I am logged in as "test@inbaz.org" with "toosh8Laiz7f"
        And I am on "/admin/statistic/orders"
        Then I should not see "You are not authorized to access"
        And I should see "Matching"
        And I should see "ERS AdminPanel"
        And I should see "Statistics: Orders"
        And I should see "Only active orders are included in this view."
        
