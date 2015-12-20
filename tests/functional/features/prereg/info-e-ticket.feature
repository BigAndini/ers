Feature: ERS e-ticket info page
    In order ensure the e-ticket translations are working
    As a guest
    I need to access the e-ticket info page

    Scenario: Accessing the e-ticket info page
        Given I am on "/info/e-ticket"
        Then I should see "E-Ticket for adult in en"

    # english e-tickets
    Scenario: Accessing the e-ticket info page for english adult e-tickets
        Given I am on "/info/e-ticket?lang=en"
        Then I should see "E-Ticket for adult in en"
    
    Scenario: Accessing the e-ticket info page for english U18 e-tickets
        Given I am on "/info/e-ticket?lang=en&agegroup=3"
        Then I should see "E-Ticket for U18 in en"
    
    Scenario: Accessing the e-ticket info page for english U16 e-tickets
        Given I am on "/info/e-ticket?lang=en&agegroup=4"
        Then I should see "E-Ticket for U16 in en"

    Scenario: Accessing the e-ticket info page for english U6 e-tickets
        Given I am on "/info/e-ticket?lang=en&agegroup=5"
        Then I should see "E-Ticket for U6 in en"

    # german e-ticket translations
    Scenario: Accessing the e-ticket info page for german adult e-tickets
        Given I am on "/info/e-ticket?lang=de"
        Then I should see "E-Ticket for adult in de"

    Scenario: Accessing the e-ticket info page for german U18 e-tickets
        Given I am on "/info/e-ticket?lang=de&agegroup=3"
        Then I should see "E-Ticket for U18 in de"

    Scenario: Accessing the e-ticket info page for german U16 e-tickets
        Given I am on "/info/e-ticket?lang=de&agegroup=4"
        Then I should see "E-Ticket for U16 in de"

    Scenario: Accessing the e-ticket info page for german U6 e-tickets
        Given I am on "/info/e-ticket?lang=de&agegroup=5"
        Then I should see "E-Ticket for U6 in de"

    # italian e-ticket translations
    Scenario: Accessing the e-ticket info page for italian adult e-tickets
        Given I am on "/info/e-ticket?lang=it"
        Then I should see "E-Ticket for adult in it"

    Scenario: Accessing the e-ticket info page for italian U18 e-tickets
        Given I am on "/info/e-ticket?lang=it&agegroup=3"
        Then I should see "E-Ticket for U18 in it"

    Scenario: Accessing the e-ticket info page for italian U16 e-tickets
        Given I am on "/info/e-ticket?lang=it&agegroup=4"
        Then I should see "E-Ticket for U16 in it"

    Scenario: Accessing the e-ticket info page for italian U6 e-tickets
        Given I am on "/info/e-ticket?lang=it&agegroup=5"
        Then I should see "E-Ticket for U6 in it"

    # french e-ticket translations
    Scenario: Accessing the e-ticket info page for french adult e-tickets
        Given I am on "/info/e-ticket?lang=fr"
        Then I should see "E-Ticket for adult in fr"

    Scenario: Accessing the e-ticket info page for french U18 e-tickets
        Given I am on "/info/e-ticket?lang=fr&agegroup=3"
        Then I should see "E-Ticket for U18 in fr"

    Scenario: Accessing the e-ticket info page for french U16 e-tickets
        Given I am on "/info/e-ticket?lang=fr&agegroup=4"
        Then I should see "E-Ticket for U16 in fr"

    Scenario: Accessing the e-ticket info page for french U6 e-tickets
        Given I am on "/info/e-ticket?lang=fr&agegroup=5"
        Then I should see "E-Ticket for U6 in fr"

    # spanish e-ticket translations
    Scenario: Accessing the e-ticket info page for spanish adult e-tickets
        Given I am on "/info/e-ticket?lang=es"
        Then I should see "E-Ticket for adult in es"

    Scenario: Accessing the e-ticket info page for spanish U18 e-tickets
        Given I am on "/info/e-ticket?lang=es&agegroup=3"
        Then I should see "E-Ticket for U18 in es"

    Scenario: Accessing the e-ticket info page for spanish U16 e-tickets
        Given I am on "/info/e-ticket?lang=es&agegroup=4"
        Then I should see "E-Ticket for U16 in es"

    Scenario: Accessing the e-ticket info page for spanish U6 e-tickets
        Given I am on "/info/e-ticket?lang=es&agegroup=5"
        Then I should see "E-Ticket for U6 in es"