Feature: ERS Product Page
    In order ensure the app works
    As a developer
    I need to access the product page of the first product

    Scenario: Accessing the product page of the first product
        Given I am on "/product/add/1"
        Then I should see "Week Ticket"
        And I should see "The prices will increase after"
