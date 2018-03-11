Feature: ERS Prouct Page
    In order to ensure somebody can buy something
    As a guest
    I need to add/edit/delete a product

    Scenario: Add a buyer info
        Given I am on "/product"
        Then I should see "Click on any item to add further information and place it in your shopping cart."
        # test add
        Then I go to "/product/add/1"
        And I should see "back to Overview"
        And I should see "The prices will increase after"
        # add participant
        # choose show
        # click on submit
        Then I should see "to your shopping cart"
        Then I follow "to shopping cart"
        Then I should see "Week Ticket (show: Friday, August 7th, 20:00)"
        # test edit
        Then I follow "edit"
        # choose another show
        # click on submit
        Then I follow "to shopping cart"
        Then I should see "Week Ticket (show: Saturday, August 8th, 21:00)"
        # test delete
        Then I follow "delete"
        And I press "del"
        Then the url should match "/order"
        And I should not see "Week Ticket"