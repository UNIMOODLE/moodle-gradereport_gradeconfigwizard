@gradereport @gradereport_gradeconfigwizard @formula_creator
Feature: I need to generate the corresponding formula
  In order to evaluate
  As a teacher
  The different elements that assess the course

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And the following "grade categories" exist:
      | fullname       | course |
      | Category 1     | C1     |
    And the following "grade categories" exist:
      | fullname       | course | gradecategory |
      | Sub category 1 | C1     | Category 1    |
    And the following "activities" exist:
      | activity | course | idnumber | name        | gradecategory  |
      | assign   | C1     |          | Assigment 1 | ?              |
      | assign   | C1     |          | Assigment 2 | ?              |
      | assign   | C1     |          | Assigment 3 | Category 1     |
      | assign   | C1     |          | Assigment 4 | Category 1     |
      | assign   | C1     |          | Assigment 5 | Sub category 1 |
      | assign   | C1     |          | Assigment 6 | Sub category 1 |
    And the following "grade items" exist:
      | itemname      | course | category         |
      | Grade Item 1  | C1     | ?                |
      | Grade Item 2  | C1     | Category 1       |
      | Grade Item 3  | C1     | Sub category 1   |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to course grades


  @javascript @calculate_mean_grade
  Scenario: Calculate the mean grade of a Grade Item 2 (included in Category 1) and Assigment 2.
    The result is assigned to the Grade Item 1

    When I navigate to "View > Grader report" in the course gradebook
    And I turn editing mode on
    And I change window size to "large"
    And I give the grade "30.00" to the user "Student 1" for the grade item "Grade Item 2"
    And I give the grade "50.00" to the user "Student 1" for the grade item "Assigment 2"
    And I press "Save changes"

    When I navigate to "View > Grade setup wizard" in the course gradebook
    And I choose Edit calculation in the dropdown "Grade Item 1"
    And I click on "Grade Item 2" "checkbox"
    And I click on "Assigment 2" "checkbox"
    And In Select aggregation method I choose "meangrades"
    And I click on "Generate formula" "button"
    And I click on "Save formula" "button"
    And I should see "Succesfull Update"

    When I navigate to "View > User report" in the course gradebook
    And In select student "Student 1"
    Then the following should exist in the "user-grade" table:
      | Grade item    | Grade  |
      | Grade Item 2  | 30.00  |
      | Grade Item 1  | 40.00  |
      | Assigment 2   | 50.00  |

    When I navigate to "View > Grader report" in the course gradebook
    And I click on "Edit calculation for Grade Item 1" "icon"
    And I check formula is correct in "Course 1" for elements:
      | Totals                    | Formulas                                        | Elements                 |
      | Grade Item 1              | =average([[grade_item_2_!1]],[[assigment_2_!2]])| Grade Item 2,Assigment 2 |


  @javascript @calculate_sum_grade
  Scenario: Calculate the sum grade of a Grade Item 1 and Assigment 2.
  The result is assigned to the Grade Item 2

    When I navigate to "View > Grader report" in the course gradebook
    And I turn editing mode on
    And I change window size to "large"
    And I give the grade "30.00" to the user "Student 1" for the grade item "Grade Item 1"
    And I give the grade "50.00" to the user "Student 1" for the grade item "Assigment 2"
    And I press "Save changes"

    When I navigate to "View > Grade setup wizard" in the course gradebook
    And I choose Edit calculation in the dropdown "Grade Item 2"
    And I click on "Grade Item 1" "checkbox"
    And I click on "Assigment 2" "checkbox"
    And In Select aggregation method I choose "sum"
    And I click on "Generate formula" "button"
    And I click on "Save formula" "button"
    And I should see "Succesfull Update"

    When I navigate to "View > User report" in the course gradebook
    And In select student "Student 1"
    Then the following should exist in the "user-grade" table:
      | Grade item    | Grade  |
      | Grade Item 1  | 30.00  |
      | Grade Item 2  | 80.00  |
      | Assigment 2   | 50.00  |

    When I navigate to "View > Grader report" in the course gradebook
    And I click on "Edit calculation for Grade Item 2" "icon"
    And I check formula is correct in "Course 1" for elements:
      | Totals                    | Formulas                                    | Elements                 |
      | Grade Item 2              | =sum([[grade_item_1_!1]],[[assigment_2_!2]])| Grade Item 1,Assigment 2 |


  @javascript @calculate_weight_grade
  Scenario: Calculate the weight mean grade of a Grade Item 1 and Assigment 2, values will be converted to integers.
  The result is assigned to Grade Item 2

    When I navigate to "View > Grader report" in the course gradebook
    And I turn editing mode on
    And I change window size to "large"
    And I give the grade "30.00" to the user "Student 1" for the grade item "Grade Item 1"
    And I give the grade "50.00" to the user "Student 1" for the grade item "Assigment 2"
    And I press "Save changes"

    When I navigate to "View > Grade setup wizard" in the course gradebook
    And I choose Edit calculation in the dropdown "Grade Item 2"
    And I click on "Grade Item 1" "checkbox"
    And I click on "Assigment 2" "checkbox"
    And In Select aggregation method I choose "weightedmeangrades"
    And I add weight "2" value to item "Grade Item 1"
    And I add weight "3" value to item "Assigment 2"
    And I click on "Generate formula" "button"
    And I click on "Save formula" "button"
    And I should see "Succesfull Update"

    When I navigate to "View > User report" in the course gradebook
    And In select student "Student 1"
    Then the following should exist in the "user-grade" table:
      | Grade item    | Grade  |
      | Grade Item 1  | 30.00  |
      | Grade Item 2  | 42.00  |
      | Assigment 2   | 50.00  |

    When I navigate to "View > Grader report" in the course gradebook
    And I click on "Edit calculation for Grade Item 2" "icon"
    And I check formula is correct in "Course 1" for elements:
      | Totals                    | Formulas                                                | Elements                 |
      | Grade Item 2              | =sum([[grade_item_1_!1]]*2,[[assigment_2_!2]]*3)/5      | Grade Item 1,Assigment 2 |

  @javascript @calculate_highest_grade
  Scenario: Calculate the highest grade of a Grade Item 2 (from Category 1) and Grade Item 3 (from Sub category 1)
  The result is assigned to the Grade Item 1

    When I navigate to "View > Grader report" in the course gradebook
    And I turn editing mode on
    And I change window size to "large"
    And I give the grade "70.00" to the user "Student 1" for the grade item "Grade Item 2"
    And I give the grade "90.00" to the user "Student 1" for the grade item "Grade Item 3"
    And I press "Save changes"

    When I navigate to "View > Grade setup wizard" in the course gradebook
    And I choose Edit calculation in the dropdown "Grade Item 1"
    And I click on "Grade Item 2" "checkbox"
    And I click on "Grade Item 3" "checkbox"
    And In Select aggregation method I choose "highest"
    And I click on "Generate formula" "button"
    And I click on "Save formula" "button"
    And I should see "Succesfull Update"

    When I navigate to "View > User report" in the course gradebook
    And In select student "Student 1"
    Then the following should exist in the "user-grade" table:
      | Grade item    | Grade  |
      | Grade Item 1  | 90.00  |
      | Grade Item 2  | 70.00  |
      | Grade Item 3  | 90.00  |

    When I navigate to "View > Grader report" in the course gradebook
    And I click on "Edit calculation for Grade Item 1" "icon"
    And I check formula is correct in "Course 1" for elements:
      | Totals                    | Formulas                                     | Elements                  |
      | Grade Item 1              | =max([[grade_item_2_!1]],[[grade_item_3_!2]])| Grade Item 2,Grade Item 3 |

  @javascript @calculate_lowest_grade
  Scenario: Calculate the lowest grade of a Grade Item 2 (from Category 1) and Sub category 1 total
  The result is assigned to Category 1 total

    When I navigate to "View > Grader report" in the course gradebook
    And I turn editing mode on
    And I change window size to "large"
    And I give the grade "65.00" to the user "Student 1" for the grade item "Grade Item 2"
    And I give the grade "50.00" to the user "Student 1" for the grade item "Sub category 1 total"
    And I press "Save changes"

    When I navigate to "View > Grade setup wizard" in the course gradebook
    And I choose Edit calculation in the dropdown "Total Category 1"
    And I click on "Grade Item 2" "checkbox"
    And I click on "Total Sub category 1" "checkbox"
    And In Select aggregation method I choose "lowest"
    And I click on "Generate formula" "button"
    And I click on "Save formula" "button"
    And I should see "Succesfull Update"

    When I navigate to "View > User report" in the course gradebook
    And In select student "Student 1"
    Then the following should exist in the "user-grade" table:
      | Grade item           | Grade  |
      | Grade Item 2         | 65.00  |
      | Category 1 total     | 50.00  |
      | Sub category 1 total | 50.00  |

    When I navigate to "View > Grader report" in the course gradebook
    And I click on "Edit calculation for Category 1 Category total" "icon"
    And I check formula is correct in "Course 1" for elements:
      | Totals                    | Formulas                                             | Elements                          |
      | Total Category 1          | =min([[grade_item_2_!1]],[[sub_category_1_total_!2]])| Grade Item 2,sub_category_1_total |



  @javascript @calculate_mean_grade_hirarchy
  Scenario: Calculate the mean grade of a Grade Item 2 (from Category 1), Total Sub category 1,
  Grade Item 4 (from Category 2) and Total Category 2. The result is assigned to Total Category 1
    When the following "grade categories" exist:
      | fullname       | course |
      | Category 2     | C1     |
    And the following "grade items" exist:
      | itemname      | course | category         |
      | Grade Item 4  | C1     | Category 2       |
    And I navigate to "View > Grader report" in the course gradebook
    And I turn editing mode on
    And I change window size to "large"
    And I give the grade "65.00" to the user "Student 1" for the grade item "Grade Item 2"
    And I give the grade "80.00" to the user "Student 1" for the grade item "Grade Item 4"
    And I give the grade "50.00" to the user "Student 1" for the grade item "Sub category 1 total"
    And I give the grade "40.00" to the user "Student 1" for the grade item "Category 2 total"
    And I press "Save changes"

    When I navigate to "View > Grade setup wizard" in the course gradebook
    And I choose Edit calculation in the dropdown "Total Category 1"
    And I click on "Grade Item 2" "checkbox"
    And I click on "Grade Item 4" "checkbox"
    And I click on "Sub category 1" "checkbox"
    And I click on "Category 2" "checkbox"

    And In Select aggregation method I choose "meangrades"
    And I click on "Generate formula" "button"
    And I click on "Save formula" "button"
    And I should see "Succesfull Update"

    When I navigate to "View > User report" in the course gradebook
    And In select student "Student 1"
    Then the following should exist in the "user-grade" table:
      | Grade item           | Grade  |
      | Grade Item 2         | 65.00  |
      | Grade Item 4         | 80.00  |
      | Category 1 total     | 58.75  |
      | Category 2 total     | 40.00  |
      | Sub category 1 total | 50.00  |

    When I navigate to "View > Grader report" in the course gradebook
    And I click on "Edit calculation for Category 1 Category total" "icon"
    And I check formula is correct in "Course 1" for elements:
      | Totals                    | Formulas                                             | Elements                          |
      | Total Category 1          | =average([[grade_item_2_!1]],[[grade_item_4_!2]],[[sub_category_1_total_!3]],[[category_2_total_!4]])| Grade Item 2,Grade Item 4,sub_category_1_total,category_2_total |


  @javascript @validate_error_throw_broken_formula
  Scenario: Probably circular reference or broken calculation formula message case,
  dependencies generated with Total Sub category 1. Can't take Total Category 1 reference
    When I navigate to "View > Grader report" in the course gradebook
    And I turn editing mode on
    And I change window size to "large"
    And I give the grade "65.00" to the user "Student 1" for the grade item "Grade Item 2"
    And I give the grade "50.00" to the user "Student 1" for the grade item "Category 1 total"
    And I press "Save changes"

    When I navigate to "View > Grade setup wizard" in the course gradebook
    And I choose Edit calculation in the dropdown "Total Sub category 1"
    And I click on "Grade Item 2" "checkbox"
    And I click on "Category 1" "checkbox"
    And In Select aggregation method I choose "sum"
    And I click on "Generate formula" "button"
    And I click on "Save formula" "button"

    And I should see "Probably circular reference or broken calculation formula"

  @javascript @validate_error_throw_circular_reference
  Scenario: Probably circular reference or broken calculation formula message case,
  dependencies generated with items Total Category 2 and Total Sub category 1

    When the following "grade categories" exist:
      | fullname       | course |
      | Category 2     | C1     |
    And the following "grade items" exist:
      | itemname      | course | category         |
      | Grade Item 4  | C1     | Category 2       |
    And I navigate to "View > Grader report" in the course gradebook
    And I turn editing mode on
    And I change window size to "large"
    And I give the grade "65.00" to the user "Student 1" for the grade item "Grade Item 2"
    And I give the grade "80.00" to the user "Student 1" for the grade item "Grade Item 4"
    And I give the grade "50.00" to the user "Student 1" for the grade item "Category 1 total"
    And I give the grade "40.00" to the user "Student 1" for the grade item "Category 2 total"
    And I press "Save changes"

    When I navigate to "View > Grade setup wizard" in the course gradebook
    And I choose Edit calculation in the dropdown "Total Sub category 1"
    And I click on "Grade Item 2" "checkbox"
    And I click on "Category 2" "checkbox"
    And In Select aggregation method I choose "highest"
    And I click on "Generate formula" "button"
    And I click on "Save formula" "button"
    And I should see "Succesfull Update"

    When I navigate to "View > Grader report" in the course gradebook
    And I click on "Edit calculation for Sub category 1 Category total" "icon"
    And I check formula is correct in "Course 1" for elements:
      | Totals                    | Formulas                                         | Elements                      |
      | Total Sub category 1      | =max([[grade_item_2_!1]],[[category_2_total_!2]])| Grade Item 2,category_2_total |

    When I navigate to "View > Grade setup wizard" in the course gradebook
    And I choose Edit calculation in the dropdown "Total Category 2"
    And I click on "Grade Item 2" "checkbox"
    And I click on "Sub category 1" "checkbox"
    And In Select aggregation method I choose "highest"
    And I click on "Generate formula" "button"
    And I click on "Save formula" "button"
    And I should see "Probably circular reference or broken calculation formula"

    # Check if the value before maintains having the error generating the new formula
    When I navigate to "View > Grader report" in the course gradebook
    And I click on "Edit calculation for Sub category 1 Category total" "icon"
    And I check formula is correct in "Course 1" for elements:
      | Totals                    | Formulas                                         | Elements                      |
      | Total Sub category 1      | =max([[grade_item_2_!1]],[[category_2_total_!2]])| Grade Item 2,category_2_total |







