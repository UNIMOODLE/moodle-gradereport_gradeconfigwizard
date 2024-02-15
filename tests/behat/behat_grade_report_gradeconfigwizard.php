<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// Project implemented by the &quot;Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU&quot;.
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.
/**
 * Display information about all the gradereport_gradeconfigwizard modules in the requested course. *
 * @package gradeconfigwizard
 * @copyright 2023 Proyecto UNIMOODLE
 * @author UNIMOODLE Group (Coordinator) &lt;direccion.area.estrategia.digital@uva.es&gt;
 * @author Joan Carbassa (IThinkUPC) &lt;joan.carbassa@ithinkupc.com&gt;
 * @author Yerai Rodríguez (IThinkUPC) &lt;yerai.rodriguez@ithinkupc.com&gt;
 * @author Marc Geremias (IThinkUPC) &lt;marc.geremias@ithinkupc.com&gt;
 * @author Miguel Gutiérrez (UPCnet) &lt;miguel.gutierrez.jariod@upcnet.es&gt;
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\DriverException as DriverException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Behat format Itinerary steps definitions.
 *
 * @package    grade_report_gradeconfigwizard
 * @category   test
 * @copyright  2023 Unimoodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_grade_report_gradeconfigwizard extends behat_base {

    /**
     * Go to the course grades
     *
     * @Given /^I navigate to course grades$/
     */
    public function i_navigate_to_course_grades() {
        $this->execute('behat_navigation::i_select_from_secondary_navigation', ["Grades"]);
    }

    /**
     * Given I drag row number :arg1 to row number :arg2
     *
     * @Given I drag row number :arg1 to row number :arg2
     */
    public function i_drag_row_number_to_row_number($arg1, $arg2) {
        // Get the table.
        $tablenode = $this->get_selected_node("table", "gradeconfigwizard-dashboard-table");

        // Get the row number $arg2.
        $row = $tablenode->find('css', 'tr')[$arg1];
        // Get the row number $arg2.
        $row2 = $tablenode->find('css', 'tr')[$arg2];

        // Check if are visible.
        if (!$row->isVisible() || !$row2->isVisible()) {
            throw new ExpectationException("The row is not visible", $this->getSession());
        }

        // Drag the row and drop it on the other row.
        $this->getSession()->getDriver()->dragTo($row->getXPath(), $row2->getXPath());
    }

    /**
     * Given I drag row with text :arg1 to row with text :arg2
     *
     * @Given I drag row with text :arg1 to row with text :arg2
     */
    public function i_drag_row_with_text_with_text($arg1, $arg2) {
        $source = $this->find("css", $arg1);
        $target = $this->find("css", $arg2);

        if (!$source->isVisible()) {
            throw new ExpectationException("'{$source}' is not visible", $this->getSession());
        }
        if (!$target->isVisible()) {
            throw new ExpectationException("'{$target}' is not visible", $this->getSession());
        }

        $this->getSession()->getDriver()->dragTo($source->getXpath(), $target->getXpath());
    }

    /*                           GRADE SETUP WIZARD                             */

    /**
     * Choose the Edit calculation of an evaluable element
     *
     * @When /^I choose disable "([^""]*)"$/
     */
    public function i_choose_disable($item) {
        $row = $this->getSession()->getPage()->find('css', 'tr:contains("'.$item.'")');
        $row->find('css', 'a:contains("Edit")')->click();
        $row->find('css', 'a:contains("Edit settings")')->click();
        $elem = $this->getSession()->getPage()->find('css', 'select[name="aggregation"] option[selected]');
        if ($elem->getText() !== 'Weighted mean of grades') {
            throw new ExpectationException("Not weighted value selected", $this->getSession());
        }
    }

    /**
     * Choose the Edit calculation of an evaluable element
     *
     * @When /^I choose Edit calculation in the dropdown "([^""]*)"$/
     */
    public function i_choose_edit_calculation($item) {
        $row = $this->getSession()->getPage()->find('css', 'tr:contains("'.$item.'")');
        $row->find('css', 'button:contains("Edit")')->click();
        $row->find('css', 'a:contains("Edit calculation")')->click();
    }


    /**
     * Choose the method in the custom-select "Select aggregation method"
     *
     * @When /^In Select aggregation method I choose "([^""]*)"$/
     */
    public function i_choose_select_aggregation_method($methodid) {
        $elem = $this->getSession()->getPage()->find('css', 'select:contains("Select aggregation method")');
        $elem->selectOption($methodid);
    }

    /**
     * Choose the student in the select
     *
     * @When /^In Select student "([^""]*)"$/
     */
    public function i_choose_student($student) {
        if ($this->getSession()->getPage()->find('css', 'select:contains("Choose...")')) {
            $elem = $this->getSession()->getPage()->find('css', 'select:contains("Choose...")');
            $elem->selectOption($student);
        } else {
            $user = "user";
            $this->execute('behat_grade::i_click_on_in_search_widget', [$student, $user]);
        }
    }

    /**
     * Choose the student in the box selector
     *
     * @When /^In select student "([^""]*)"$/
     */
    public function i_select_student($student) {
        $user = "user";
        $this->execute('behat_grade::i_click_on_in_search_widget', [$student, $user]);
    }

    /**
     * Choose the method in the custom-select "Select aggregation method"
     *
     * @When /^I add weight "([^""]*)" value to item "([^""]*)"$/
     */
    public function i_add_weight_value($value, $itemname) {
        $row = $this->getSession()->getPage()->find('css', sprintf('table tr:contains("%s")', $itemname));
        $elem = $row->find('css', 'input');
        $elem->setValue($value);
    }


    /**
     * Check formula value
     *
     * @When /^I check formula is correct in "([^""]*)" for elements:$/
     */
    public function i_check_formula_is_correct($course, TableNode $data) {
        GLOBAL $DB;
        $elem = $this->getSession()->getPage()->find('css', 'textarea');
        $valuetextarea = $elem->getValue();

        $course = $DB->get_record('course', ['fullname' => $course], '*', MUST_EXIST);

        $datahash = $data->getRowsHash();
        unset($datahash["Totals"]);
        $formula = "";
        $gtotcat = grade_item::fetch_all(['itemtype' => 'category', 'courseid' => $course->id]);
        foreach ($datahash as $key => $value) {
            $elements = [];
            $elementsid = [];
            $formula = $value[0];
            $elements = explode(',', $value[1]);
            foreach ($elements as $gitem) {
                try {
                    $g = grade_item::fetch(['itemname' => $gitem, 'courseid' => $course->id]);
                    if ($g) {
                        $elementsid[] = $g->id;
                    } else {
                        foreach ($gtotcat as $gcat) {
                            if (!is_null($gcat->idnumber)) {
                                if (mb_strpos($gcat->idnumber, $gitem) !== false) {
                                    $elementsid[] = $gcat->id;
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
            $i = 1;
            foreach ($elementsid as $id) {
                $idfy = '!'.$i;
                $formula = str_replace($idfy, $id, $formula);
                ++$i;
            }
        }
        if ($valuetextarea != $formula) {
            throw new ExpectationException("Formula is not correct, expected: " . $valuetextarea .
                " but found: " . $formula, $this->getSession());
        }
    }


    /**
     * Create a new category or grade item under a row
     *
     * @When /^I create a new "([^""]*)" under "([^""]*)" called "([^""]*)"$/
     */
    public function i_create_a_new_under_called($itemtype, $under, $name) {
        $row = $this->getSession()->getPage()->find('css', 'tr:contains("'.$under.'")');

        if ($row == null) {
            // If the row is temporal.
            $tablenode = $this->get_selected_node("table", "gradeconfigwizard-dashboard-table");
            $rows = $tablenode->findAll('css', 'tr');
            foreach ($rows as $tr) {
                $input = $tr->find('css', 'input');
                if ($input != null && $input->getValue() == $under) {
                    $row = $tr;
                    break;
                }
            }
        }

        $row->find('css', 'button:contains("Edit")')->click();
        $row->find('css', 'a:contains("Add '. $itemtype .'")')->click();
        $tablenode = $this->get_selected_node("table", "gradeconfigwizard-dashboard-table");
        $inputs = $tablenode->findAll('css', 'input');
        foreach ($inputs as $input) {
            if ($input->getValue() == "") {
                $input->setValue($name);
                break;
            }
        }
    }

    /**
     * Then I should see the row with selected text under
     * another row with selected text.
     *
     * @When /^I should see "([^""]*)" under "([^""]*)"$/
     */
    public function i_should_see_under_in_the_table($tosee, $under) {
        $tablenode = $this->get_selected_node("table", "gradeconfigwizard-dashboard-table");
        // Find the row with the text.
        $row = $tablenode->find('css', 'tr:contains("'.$under.'")');
        // Get the row under the row.
        $child = $row->find('xpath', 'following-sibling::tr[1]');

        // Check if the row contains the text.
        if (strpos($child->getText(), $tosee) === false ) {
            throw new ExpectationException("The under row does not contain the text", $this->getSession());
        }
    }

    /**
     * Disables the selected item
     *
     * @When /^I disable the "([^""]*)" item$/
     */
    public function i_disable_the_item($category) {
        $tablenode = $this->get_selected_node("table", "gradeconfigwizard-dashboard-table");
        // Find the row with the text.
        $row = $tablenode->find('css', 'tr:contains("'.$category.'")');

        $row->find('css', 'button:contains("Edit")')->click();
        $row->find('css', 'a:contains("Disable")')->click();
    }

    /**
     * Drag an item under another item
     *
     * @When /^I drag "([^""]*)" under "([^""]*)"$/
     */
    public function i_drag_under($draged, $under) {
        $dragged = $this->getSession()->getPage()->find('css', 'tr:contains("'.$draged.'")');
        $target = $this->getSession()->getPage()->find('css', 'tr:contains("'.$under.'")');
        $dragged->dragTo($target);
    }

    /*        MULTIPLE WEIGHTED EVALUATIONS        */

    /**
     * Function used to create a new category
     * on the multiple weighted evaluations.
     *
     * @When /^I create a pathway named "([^""]*)"$/
     */
    public function i_create_a_pathway_named($category) {
        // Write the name of the category in the input.
        $this->getSession()->getPage()->fillField("weightedevaluations-category-create-name", $category);
        // Find and press the "Create" button.
        $this->getSession()->getPage()->find('css', 'button:contains("Create")')->click();
    }

    // And I add a pathway to the "Category Creation 1" category named "Pathway 1" with weight "1".
    /**
     * Function used to create a new pathway
     * on the multiple weighted evaluations.
     *
     * @When /^I add a pathway to the "([^""]*)" category named "([^""]*)" with weight "([^""]*)"$/
     */
    public function i_add_a_pathway_to_the_category_named_with_weight($category, $pathway, $weight) {
        // Find the category.
        $tablenode = $this->get_selected_node("table", "weightedevaluations-table");
        // Press the "Add pathway" button.
        $tablenode->find('css', 'button:contains("Add pathway")')->click();

    }

    /**
     * Function used to create a new pathway
     * on the multiple weighted evaluations.
     *
     * @When /^I add categories to the "([^""]*)" with the following values:$/
     */
    public function i_add_categories_to_the_with_the_following_values($category, TableNode $data) {
        // Get the data.
        $datahash = $data->getRowsHash();
        unset($datahash["Categories"]); // Remove the first row.

        $sequentialarray = [];
        foreach ($datahash as $key => $value) {
            $sequentialarray[] = [$key, $value];
        }

        for ($i = 0; $i < count($datahash); $i++) {
            // Get the rows under the category.
            $tablenode = $this->get_selected_node("table", "weightedevaluations-table");
            $rows = $tablenode->findAll('css', 'tr');
            // Get all the rows under the category.
            $rowsunder = [];
            $bol = false;
            foreach ($rows as $row) {
                if (strpos($row->getText(), $category) !== false) {
                    $bol = true;
                }
                if ($bol) {
                    $rowsunder[] = $row;
                }
            }
            // Find the add pathway button of the category in the rowsunder.
            foreach ($rowsunder as $row) {
                if ($button = $row->find('css',
                    'button[class="add-subcategory-btn align-items-center block-add btn btn-secondary d-flex"]')) {
                    $button->click(); break;
                }
            }
        }

        $tablenode = $this->get_selected_node("table", "weightedevaluations-table");
        $inputs = $tablenode->findAll('css', 'input');
        $i = 0;

        foreach ($inputs as $input) {
            if ($input->isVisible() && ($input->getValue() == "" || $input->getValue() == 1)) {
                // Check if $i is within the bounds of the array.
                if (isset($sequentialarray[intval($i / 2)][0]) && isset($sequentialarray[intval($i / 2)][1])) {
                    if ($i % 2 == 0) {
                        $input->setValue($sequentialarray[intval($i / 2)][0]);
                    } else {
                        $input->setValue($sequentialarray[intval($i / 2)][1]);
                    }
                }
                $i++;
            }
        }

    }

    /**
     * Function used to create a new items
     * on the multiple weighted evaluations.
     *
     * @When /^I add the following items to the "([^""]*)" with the following values:$/
     */
    public function i_add_the_following_items_to_the_with_the_following_values($category, TableNode $data) {
        // Get the data.
        $datahash = $data->getRowsHash();
        unset($datahash["Items"]); // Remove the first row.

        // Find the closest row with the input "category" and press the "Add elements" button.
        $tablenode = $this->get_selected_node("table", "weightedevaluations-table");
        $rows = $tablenode->findAll('css', 'tr');
        $foundcategory = false;

        $var = false;
        foreach ($rows as $row) {
            // Get the input from the row.
            $input = $row->find('css', 'input');
            if ($input != null && $input->getValue() == $category) {
                $foundcategory = true;
                $var = true;
            } else if ($input != null && $foundcategory) {
                $var = false;
            }

            if ($var) {
                // Look for the "Add items" button within the current row.
                if ($button = $row->find('css', 'button:contains("Add")')) {
                    $button->click();
                    break;
                }
            }
        }
        // Check the corresponding checkboxes.
        foreach ($datahash as $key => $value) {
            $this->getSession()->getPage()->checkField($key);
        }
        // Press the "Add" button on the modal dialog.
        $addbutton = $this->getSession()->getPage()->find('css', 'div[class="modal-footer"]');
        $addbutton->find('css', 'button:contains("Add")')->click();

        $tablenode = $this->get_selected_node("table", "weightedevaluations-table");
        $inputs = $tablenode->findAll('css', 'tr[data-rowtype="subcategorygradeitem"]');
        $i = 0;
        $bol = false;
        foreach ($inputs as $input) {
            // Get the tds.
            $tds = $input->findAll('css', 'td');
            if (!$bol) {
                foreach ($tds as $td) {
                    if (strpos($td->getText(), array_keys($datahash)[0]) !== false) {
                        $bol = true;
                    }
                }
            }
            if ($bol) {
                foreach ($tds as $td) {
                    $input = $td->find('css', 'input');
                    if ($input != null && $input->isVisible()) {
                        $input->setValue(array_values($datahash)[$i]);
                        $i++;
                    }
                }
            }
        }
    }

    /**
     * Function used to check if an item is not
     * in the modal dialog.
     *
     * @Then /^I should not see "([^""]*)" in the modal dialog$/
     */
    public function i_should_not_see_in_the_modal_dialog($item) {
        // Get the checkboxes options.
        try {
            $this->getSession()->getPage()->checkField($item);
        } catch (Exception $e) {
            return false;
        }
        throw new ExpectationException("The item is in the modal dialog", $this->getSession());

    }

    /**
     * Function used to check if the button
     * has the class "disabled-box" aka is disabled.
     *
     * @Then /^I should not be able to press "([^""]*)"$/
     */
    public function i_should_not_be_able_to_press($button) {
        $buttonnode = $this->find_button($button);
        $classes = $buttonnode->getAttribute('class');
        if (strpos($classes, "disabled-box") === false ) {
            throw new ExpectationException("The button is not disabled", $this->getSession());
        }
    }

    /**
     * Function used to remove an item from the table
     *
     * @Then /^I remove the item "([^""]*)" from the table$/
     */
    public function i_remove_the_item_from_the_table($item) {
        $tablenode = $this->get_selected_node("table", "weightedevaluations-table");
        $rows = $tablenode->findAll('css', 'tr');
        foreach ($rows as $row) {
            if (strpos($row->getText(), $item) !== false) {
                $row->find('css', 'i.remove-item-icon')->click();
                break;
            }
        }
        $rows = $tablenode->findAll('css', 'input');
        foreach ($rows as $row) {
            if ($row->getValue() == $item) {
                $row->getParent()->find('css', 'i.remove-item-icon')->click();
                break;
            }
        }
    }


    /*        MULTIPLE ASSESSMENTS EVALUATIONS        */

    /**
     * Function used to create a new category
     * on the multiple assessments evaluations.
     * @When /^I create a category named "([^""]*)" with weight "([^""]*)"$/
     */
    public function i_create_a_category_named_with_weight($category, $weight) {
        // Write the name of the category in the input.
        $this->getSession()->getPage()->fillField("multipleevaluations-category-create-name", $category);
        // Find and press the "Create" button.
        $this->getSession()->getPage()->find('css', 'button:contains("Create")')->click();

        $tablenode = $this->get_selected_node("table", "multipleevaluation-table");
        $trs = $tablenode->findAll('css', 'tr');
        foreach ($trs as $tr) {
            if (strpos($tr->getText(), $category) !== false) {
                $tr->findAll('css', 'td')[1]->find('css', 'input')->setValue($weight);
                break;
            }
        }
    }


    /**
     * Function used to create a new items
     * on the multiple assessments evaluations.
     *
     * @When /^I add the following items to the "([^""]*)" category:$/
     */
    public function i_add_the_following_items_to_the_category($category, TableNode $data) {
        $datahash = $data->getRowsHash();
        unset($datahash["Items"]); // Remove the first row.

        $tablenode = $this->get_selected_node("table", "multipleevaluation-table");
        $trs = $tablenode->findAll('css', 'tr');
        $var = false;

        foreach ($trs as $tr) {
            if (strpos($tr->getText(), $category) !== false) {
                $var = true;
            }

            if ($var) {
                if (strpos($tr->getText(), "Add item") !== false && $tr->isVisible()) {
                    $tr->find('css', 'button')->click();
                }
            }
        }
        foreach ($datahash as $key => $value) {
            $this->getSession()->getPage()->checkField($key);
        }
        $this->getSession()->getPage()->pressButton("Add");

        foreach ($datahash as $key => $value) {
            // Get the weight from the hashtable.

            $row = $this->getSession()->getPage()->find('css', 'tr:contains("'.$key.'")');
            foreach ($row->findAll('css', 'td') as $td) {
                // Set the weight.
                if ($td->getAttribute('class') == "weight-2") {
                    $td->find('css', 'input')->setValue($datahash[$key][0]);
                }

                // Set the cut-off mark.
                if ($td->getAttribute('class') == "min-grade-2" && $datahash[$key][1] != "-") {
                    // Enable the input type checkbox.
                    $td->find('css', 'input[type="checkbox"]')->click();
                    // Set the cut-off mark.
                    $td->find('css', 'input[type="number"]')->setValue($datahash[$key][1]);
                }

                // Set the resit elements.
                if ($td->getAttribute('class') == "resit-2 add-btn") {
                    // Set the resit elements.
                    if ($td->find('css', 'button') != null && $datahash[$key][2] != "-") {
                        $td->find('css', 'button')->click();
                        $this->getSession()->getPage()->checkField($datahash[$key][2]);
                        $this->getSession()->getPage()->pressButton("Add");
                    }
                }
            }
        }
    }

    // And I add a cut-off mark of "10" to the "Category 1" category with resit "Test assignment 2".

    /**
     * Function used to add a cut-off mark
     * and a resit element to a category.
     *
     * @When /^I add a cut-off mark of "([^""]*)" to the "([^""]*)" category with resit "([^""]*)"$/
     */
    public function i_add_a_cut_off_mark_of_to_the_category_with_resit($mark, $category, $resit) {
        $tablenode = $this->get_selected_node("table", "multipleevaluation-table");
        $trs = $tablenode->findAll('css', 'tr');

        foreach ($trs as $tr) {
            if (strpos($tr->getText(), $category) !== false) {
                $tds = $tr->findAll('css', 'td');
                foreach ($tds as $td) {
                    // Set the cut-off mark.
                    if ($td->getAttribute('class') == "min-grade-1") {
                        $td->find('css', 'input[type="checkbox"]')->click();
                        $td->find('css', 'input[type="number"]')->setValue($mark);
                    }
                    // Set the resit elements.
                    if ($td->getAttribute('class') == "resit-1 add-btn") {
                        // Set the resit elements.
                        $td->find('css', 'button')->click();
                        $this->getSession()->getPage()->checkField($resit);
                        $this->getSession()->getPage()->pressButton("Add");
                    }
                }
                break;
            }
        }
    }

    /**
     * Function used to check if the formulas
     * are correct.
     *
     * @Then /^I should see the following formulas:$/
     */
    public function i_should_see_the_following_formulas(TableNode $data) {
        global $CFG;

        $datahash = $data->getRowsHash();
        unset($datahash["Totals"]);

        foreach ($datahash as $key => $value) {
            // Wait to be sure that the formulas are loaded.
            $this->getSession()->wait(1000);
            // Navigate to the formula.
            $row = $this->getSession()->getPage()->find('css', 'tr:contains("' . $key . '")');
            $row->find('css', 'a:contains("Edit")')->click();
            $dropdown = $row->find('css', 'div[class="dropdown-menu menu dropdown-menu-right show"]');
            $dropdown->find('css', 'a')->click();
            // Check if the formula is correct.
            $formula = $this->getSession()->getPage()->find('css', 'textarea')->getValue();
            if ($formula != $value) {
                throw new ExpectationException("The formula is not correct, expected: " . $value .
                    " but found: " . $formula, $this->getSession());
            }
            $this->getSession()->back();
        }
    }

    /**
     * Navigate to item formula
     *
     * @Then /^I navigate to formula in the item "([^""]*)"$/
     */
    public function i_navigate_to_formula_item($item) {
        GLOBAL $DB;
        $row = $this->getSession()->getPage()->find('css', 'tr:contains("'.$item.'")');
        $row->find('css', 'a:contains("Edit")')->click();
        $row->find('css', 'a:contains("Edit calculation")')->click();
    }

}
