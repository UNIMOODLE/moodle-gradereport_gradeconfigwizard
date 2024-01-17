@gradereport @gradereport_gradeconfigwizard @multiple_weighted_evaluations
Feature: Multiple_weighted_evaluations
    In order to check if the multiple weighted assessments works properly

    Background:
        Given the following "courses" exist:
        | shortname | fullname |
        | Course 1 | Course 1 |
        And the following "activities" exist:
        | activity  | name          | intro              | course       | idnumber |
        | assign    | Test assign 1 | Assign description | Course 1     | assign1  |
        | assign    | Test assign 2 | Assign description | Course 1     | assign2  |
        | assign    | Test assign 3 | Assign description | Course 1     | assign3  |
        | assign    | Test assign 4 | Assign description | Course 1     | assign4  |
        And I log in as "admin"
        And I am on site homepage
        And I follow "Course 1"
        And I navigate to course grades
        And I navigate to "View > Grade setup wizard" in the course gradebook
        And I press "Access to the assessment pathways editor"

    @javascript @check_correct_modal_dialog
    Scenario: Check if when we add Test assign 1 to Category 1 we don't see Test assign 1 in the Category 2 modal dialog
        When I create a pathway named "Pathway Creation 1"
        When I create a pathway named "Pathway Creation 1"
        And I add categories to the "Pathway Creation 1" with the following values:
            | Categories    | Weight  |
            | Cateogry 1    | 5       |
        And I add the following items to the "Cateogry 1" with the following values:
            | Items            | Weight   |
            | Test assign 1    | 20       |
        And I press "Add"
        Then I should not see "Test assign 1" in the modal dialog

    @javascript @simple_category_creationTests
    Scenario: Check if the creation of two categories and 4 items works properly
        When I create a pathway named "Pathway Creation 1"
        And I add categories to the "Pathway Creation 1" with the following values:
            | Categories    | Weight   |
            | Category 1    | 5        |
            | Category 2    | 10       |
        And I add the following items to the "Category 1" with the following values:
            | Items            | Weight   |
            | Test assign 1    | 15       |
            | Test assign 2    | 20       |
        And I add the following items to the "Category 2" with the following values:
            | Items            | Weight   |
            | Test assign 3    | 25       |
            | Test assign 4    | 30       |
        And I press "Save and exit"
        Then I should see "Pathway Creation 1" under "Course 1"
        And I should see "Category 1" under "Pathway Creation 1"
        And I should see "Test assign 1" under "Category 1"
        And I should see "Test assign 2" under "Test assign 1"
        And I should see "Category 2" under "Total Category 1"
        And I should see "Test assign 3" under "Category 2"
        And I should see "Test assign 4" under "Test assign 3"
        And I should see "Total Category 2" under "Test assign 4"
        And I should see "Total Pathway Creation 1" under "Total Category 2"
        And I should see "Total Course 1" under "Total Pathway Creation 1"

    @javascript @check_disabled_save_and_exit_button
    Scenario: Check if I can't press "Save and exit" without adding any category
        Then I should not be able to press "Save and exit"

    @javascript @check_remove_category_item
    Scenario: Validate the correct behavior of the remove item button
        When I create a pathway named "Pathway Creation 1"
        Then I remove the item "Pathway Creation 1" from the table
        Then I should not be able to press "Save and exit"
