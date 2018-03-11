Feature: ERS Order Page
    In order ensure the app works
    As a developer
    I need to access the order page

    Scenario: Accessing the order page
        Given I am on "/order"
        Then I should see "Shopping Cart"
        And I should see "Buyer"
        And I should see "Payment type"
        And I should see "Checkout"
        And I should see "Order amount:"
        And I should see "Reset My Shopping Cart and My Persons"
 