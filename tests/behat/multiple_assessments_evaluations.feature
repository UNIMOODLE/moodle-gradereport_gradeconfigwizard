@gradereport @gradereport_gradeconfigwizard @multiple_assessments_evaluations
Feature: Multiple_assessments_evaluations
    In order to check if the multiple weighted assessments works properly

    Background:
        Given the following "courses" exist:
        | shortname | fullname |
        | Course 1  | Course 1 |
        And the following "activities" exist:
        | activity  | name           | intro              | course       | idnumber |
        | assign    | Test assign 1  | Assign description | Course 1     |          |
        | assign    | Test assign 2  | Assign description | Course 1     |          |
        | assign    | Test assign 3  | Assign description | Course 1     |          |
        | assign    | Test assign 4  | Assign description | Course 1     |          |
        | assign    | Test assign 5  | Assign description | Course 1     |          |
        | assign    | Test assign 6  | Assign description | Course 1     |          |
        | assign    | Test assign 7  | Assign description | Course 1     |          |
        | assign    | Test assign 8  | Assign description | Course 1     |          |
        | assign    | Test assign 9  | Assign description | Course 1     |          |
        | assign    | Test assign 10 | Assign description | Course 1     |          |
        And I log in as "admin"
        And I am on site homepage
        And I follow "Course 1"
        And I navigate to course grades
        And I navigate to "View > Grade setup wizard" in the course gradebook
        And I press "Access to the multiple assessment editor"

    @javascript @single_category_creation
    Scenario: Check if I can create a category with differents resits, cut-off marks, items and weights
        When I create a category named "Category 1" with weight "100"
        And I add the following items to the "Category 1" category:
            | Items            | Weight   | Cut-Off Mark   | Resit                   |
            | Test assign 1    | 5        | 15             | Test assign 3           |
            | Test assign 2    | 10       | 20             | Test assign 4           |
        And I add a cut-off mark of "10" to the "Category 1" category with resit "Test assign 5"
        And I press "Save and exit"
        Then I navigate to "Setup > Gradebook setup" in the course gradebook
        And I navigate to formula in the item "Test assign 1 total"
        And I check formula is correct in "Course 1" for elements:
            | Totals                   | Formulas                                         | Elements                      |
            | Test assign 1 total      | =IF([[test_assign_1_!1]]>=15,[[test_assign_1_!1]],[[test_assign_3_!2]])| Test assign 1,Test assign 3 |
        Then I navigate to "Setup > Gradebook setup" in the course gradebook
        And I navigate to formula in the item "Test assign 2 total"
        And I check formula is correct in "Course 1" for elements:
            | Totals                   | Formulas                                         | Elements                      |
            | Test assign 2 total      | =IF([[test_assign_2_!1]]>=20,[[test_assign_2_!1]],[[test_assign_4_!2]])| Test assign 2,Test assign 4 |
        Then I navigate to "Setup > Gradebook setup" in the course gradebook
        And I navigate to formula in the item "Category 1 total"
        And I check formula is correct in "Course 1" for elements:
            | Totals                   | Formulas                                         | Elements                      |
            | Category 1 total      | =IF([[main_content_total_!1]]>=10,[[main_content_total_!1]],[[test_assign_5_!2]])| main_content_total,Test assign 5 |

    @javascript @mulitple_categories_creation @current_bug_fix @current_bug_fix1
    Scenario: If I try to save a category with a cutt-off mark for an item without having a selected resit item, the Save button should be disabled
        Given I create a category named "Category 1" with weight "1"
        And I add the following items to the "Category 1" category:
            | Items            | Weight   | Cut-Off Mark   | Resit                   |
            | Test assign 1    | 1        | 50             | -                       |
            | Test assign 2    | 1        | 50             | -                       |
        And I add a cut-off mark of "50" to the "Category 1" category with resit "Test assign 5"
        Then the "Save and exit" "button" should be disabled