Feature: ERS Product Page
    In order ensure the app works
    As a developer
    I need to access the product page

    Scenario: Accessing the product page
        Given I am on "/product"
        Then I should see "Tickets for"
        And I should see "The prices will increase after"
