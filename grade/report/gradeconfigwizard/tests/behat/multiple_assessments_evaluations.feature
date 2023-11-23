@gradereport @gradereport_gradeconfigwizard @multiple_assessments_evaluations
Feature: Multiple_assessments_evaluations
    In order to check if the multiple weighted assessments works properly

    Background:
        Given the following "courses" exist:
        | shortname | fullname |
        | Course 1 | Course 1 |
        And the following "activities" exist:
        | activity  | name          | intro              | course       | idnumber |
        | assign    | Test assign 1 | Assign description | Course 1     |          |
        | assign    | Test assign 2 | Assign description | Course 1     |          |
        | assign    | Test assign 3 | Assign description | Course 1     |          |
        | assign    | Test assign 4 | Assign description | Course 1     |          |
        | assign    | Test assign 5 | Assign description | Course 1     |          |
        And I log in as "admin"
        And I am on site homepage
        And I follow "Course 1"
        And I navigate to course grades
        And I navigate to "View > Grade setup wizard" in the course gradebook
        And I press "Access to the weighted assessment editor"

    @javascript @mulitple_categories_creation
    Scenario: Check if I can create multiples categories with differents resits, cut-off marks, items and weights
        When I create a category named "Category 1" with weight "100"
        And I add the following items to the "Category 1" category:
            | Items            | Weight   | Cut-Off Mark   | Resit                   |
            | Test assign 1    | 5        | 15             | Test assign 3           |
            | Test assign 2    | 10       | 20             | Test assign 4           |
        And I add a cut-off mark of "10" to the "Category 1" category with resit "Test assign 5"
        And I press "Save and exit"
        Then I navigate to "Setup > Gradebook setup" in the course gradebook
        And I check formula in the item "Test assign 1 total" from "Course 1"
        And I check formula in the item "Test assign 2 total" from "Course 1"
        And I check formula in the item "Category 1 total" from "Course 1"
