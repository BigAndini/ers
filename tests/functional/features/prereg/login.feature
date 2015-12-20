Feature: ERS Profile functions
    In order ensure the profile functions are working
    As a user
    I need to be able to login, use profile and logout
        
    Scenario: Login with a user
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

    Scenario: Logout from user profile
        Given I am on "/profile"
        Then I should see "Change my user data"
        #And I follow "Logout"
        And I go to "/user/logout"
        And I go to "/user/login"
        And I should see "Sign In"
        And I should see "Stay logged in"