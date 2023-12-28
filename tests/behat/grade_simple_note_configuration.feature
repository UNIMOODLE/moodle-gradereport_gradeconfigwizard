@gradereport @gradereport_gradeconfigwizard @behat_grade_report_gradeconfigwizard
Feature: Grade setup wizard
    In order to check if the grade setup wizard works correctly

    Background:
        And the following "courses" exist:
        | shortname | fullname |
        | Course 1 | Course 1 |

        Given I log in as "admin"
        And I am on site homepage
        And I follow "Course 1"
        And I navigate to course grades
        And I navigate to "View > Grade setup wizard" in the course gradebook
        Then I change viewport size to "1200x1000"

    @javascript @single_subcategory_creation
    Scenario: Check if we can create a single subcategory
        When I create a new "category" under "Course 1" called "Subcategory 1"
        And I press "Save and exit"
        Then I should see "Subcategory 1" under "Course 1"

    @javascript @single_grade_item_creation
    Scenario: Check if we can create one new grade item
        When I create a new "grade item" under "Course 1" called "Grade item 1"
        And I press "Save and exit"
        Then I should see "Grade item 1" under "Course 1"

    @javascript @single_subcategory_creation
    Scenario: Check if we can create a hierarchy of 4 subcategories
        When I create a new "category" under "Course 1" called "Subcategory 1"
        And I create a new "category" under "Subcategory 1" called "Subcategory 2"
        And I create a new "category" under "Subcategory 2" called "Subcategory 3"
        And I create a new "category" under "Subcategory 3" called "Subcategory 4"
        And I press "Save and exit"
        Then I should see "Subcategory 1" under "Course 1"
        And I should see "Subcategory 2" under "Subcategory 1"
        And I should see "Subcategory 3" under "Subcategory 2"
        And I should see "Subcategory 4" under "Subcategory 3"

    @javascript @grade_item_inside_subcategory_creation
    Scenario: Check if we can create one new subcategory and one new grade item under it
        When I create a new "category" under "Course 1" called "Subcategory 1"
        And I create a new "grade item" under "Subcategory 1" called "Grade item 1"
        And I press "Save and exit"
        Then I should see "Subcategory 1" under "Course 1"
        And I should see "Grade item 1" under "Subcategory 1"

    @javascript @disable_single_subcategory
    Scenario: Check if we can disable one subcategory
        When I create a new "category" under "Course 1" called "Subcategory 1"
        And I press "Save and exit"
        And I disable the "Subcategory 1" item
        And I reload the page
        Then I should see "[[DISABLED]]" under "Course 1"
        And I navigate to "Setup > Gradebook setup" in the course gradebook
        And I choose disable "[[DISABLED]]"
