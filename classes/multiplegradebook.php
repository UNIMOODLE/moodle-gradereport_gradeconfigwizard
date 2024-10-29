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
 * @package gradereport_gradeconfigwizard
 * @copyright 2023 Proyecto UNIMOODLE
 * @author UNIMOODLE Group (Coordinator) &lt;direccion.area.estrategia.digital@uva.es&gt;
 * @author Joan Carbassa (IThinkUPC) &lt;joan.carbassa@ithinkupc.com&gt;
 * @author Yerai Rodríguez (IThinkUPC) &lt;yerai.rodriguez@ithinkupc.com&gt;
 * @author Marc Geremias (IThinkUPC) &lt;marc.geremias@ithinkupc.com&gt;
 * @author Miguel Gutiérrez (UPCnet) &lt;miguel.gutierrez.jariod@upcnet.es&gt;
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradereport_gradeconfigwizard;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/grade/grade_category.php');
require_once($CFG->libdir . '/grade/grade_item.php');
require_once($CFG->libdir . '/grade/constants.php');

/**
 * Class multiplegradebook
 *
 * Represents a multiple gradebook for a specific course.
 */
class multiplegradebook {

    /**
     * @var int The ID of the course associated with the gradebook.
     */
    private $courseid;

    /**
     * Constructor for the multiplegradebook class.
     *
     * Initializes a new instance of the multiplegradebook class with the provided course ID.
     * The course ID is assigned to the property $courseid.
     *
     * @param int $courseid The ID of the course associated with the gradebook.
     */
    public function __construct(int $courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Processes the provided categories for the gradebook.
     *
     * This method processes the array of categories provided as input. It requires the gradebook libraries,
     * sets the course aggregation, and iterates through each category. For each category, it creates the category
     * and its associated grade items. If the category contains a recommended item, it creates a provisional category,
     * moves the grade item to that category, and sets the cutoff mark calculation. If the category contains items,
     * it iterates through each item, creates the item category if necessary, moves the grade items, and sets the
     * cutoff mark calculation. Finally, it returns true to indicate successful processing.
     *
     * @param array $categories An array of categories to be processed.
     * @return bool True if processing is successful, false otherwise.
     */
    public function process(array $categories): bool {
        $this->set_course_aggregation();

        foreach ($categories as $category) {
            $categoryid = $this->create_category($category);

            if (isset($category['recitem'])) {
                $provisionalcategoryid = $this->create_provisional_category($categoryid);
                $this->move_grade_item_to_category($category['recitem']['recitemid'], $categoryid);
                $gc = new \grade_category(['id' => $provisionalcategoryid], true);
                $provgradeitem = $gc->get_grade_item();
                $this->set_cutoff_mark_calculation($categoryid, $provgradeitem->id, $category['recitem']);
                $categoryid = $provisionalcategoryid;
            }

            if (isset($category['items'])) {
                foreach ($category['items'] as $item) {
                    if (isset($item['recitem'])) {
                        $gicategoryid = $this->create_item_category($item['id'], $item['weight'], $categoryid);
                        $this->move_grade_item_to_category($item['id'], $gicategoryid);
                        $this->move_grade_item_to_category($item['recitem']['recitemid'], $gicategoryid);
                        $this->set_cutoff_mark_calculation($gicategoryid, $item['id'], $item['recitem']);
                    } else {
                        $this->move_grade_item_to_category($item['id'], $categoryid);
                        $this->change_item_weight($item['id'], $item['weight']);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Sets the aggregation method for the course grade category.
     *
     * This method initializes a new grade category object representing the course grade category
     * with a depth of 1 and the provided course ID. It sets the aggregation method to weighted mean
     * and updates the category in the database.
     *
     * @return void
     */
    private function set_course_aggregation(): void {
        $coursegradecat = new \grade_category(['depth' => 1, 'courseid' => $this->courseid, 'fullname' => '?'], true);
        $properties = [
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($coursegradecat, $properties);
        $coursegradecat->update();
    }

    /**
     * Creates a new grade category based on the provided category data.
     *
     * This method creates a new grade category using the provided category data array.
     * It initializes a new grade category object with the course ID, applies default and forced
     * settings, sets the full name and aggregation method, inserts the category into the database,
     * and then loads the corresponding grade item. It sets the weight override and aggregation
     * coefficient properties for the grade item before updating it in the database. Finally, it
     * returns the ID of the newly created category.
     *
     * @param array $category The data array containing information about the category.
     *                        Requires 'name' for the category name and 'weight' for the weight.
     * @return int The ID of the newly created grade category.
     */
    private function create_category(array $category): int {
        $gc = new \grade_category(['courseid' => $this->courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => clean_param($category['name'], PARAM_TEXT),
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($gc, $properties);
        $id = $gc->insert();

        $gradeitem = $gc->load_grade_item();
        \grade_item::set_properties($gradeitem, ['weightoverride' => 1, 'aggregationcoef' => $category['weight']]);
        $gradeitem->update();

        return $id;
    }

    /**
     * Creates a provisional grade category under the specified parent category.
     *
     * This method creates a new provisional grade category under the specified parent category.
     * It initializes a new grade category object with the course ID, applies default and forced
     * settings, sets the full name and aggregation method, inserts the category into the database,
     * and then sets its parent category before updating it in the database. Finally, it returns
     * the ID of the newly created provisional category.
     *
     * @param int $parentid The ID of the parent category under which to create the provisional category.
     * @return int The ID of the newly created provisional grade category.
     */
    private function create_provisional_category(int $parentid): int {
        $gc = new \grade_category(['courseid' => $this->courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => clean_param(get_string('maincontent', 'gradereport_gradeconfigwizard'), PARAM_TEXT),
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($gc, $properties);
        $id = $gc->insert();

        $gc->set_parent($parentid);
        $gc->update();

        return $id;
    }

    /**
     * Sets the cutoff mark calculation for a grade item within a category.
     *
     * This method sets the cutoff mark calculation for a grade item within the specified category.
     * It loads the grade item associated with the provided item ID, ensures the presence of an ID
     * number for both the grade item and its referenced item, generates an ID number if not already
     * present, and then sets the calculation formula based on the provided information. Finally, it
     * updates the grade item in the database.
     *
     * @param int $categoryid The ID of the category containing the grade item.
     * @param int $itemid The ID of the grade item for which to set the cutoff mark calculation.
     * @param array $recitem An array containing information about the referenced item and the cutoff grade.
     *                       It must include 'recitemid' and 'recitemgrade' keys.
     * @return void
     */
    private function set_cutoff_mark_calculation(int $categoryid, int $itemid, array $recitem): void {
        $gc = new \grade_category(['id' => $categoryid], true);
        $gi = $gc->load_grade_item();

        $gradeitem = new \grade_item(['id' => $itemid], true);
        if (empty($gradeitem->idnumber)) {
            $gradeitem->add_idnumber(\gradereport_gradeconfigwizard\formulamanager::generate_id_number($itemid));
            $gradeitem->update();
        }

        $recgi = new \grade_item(['id' => $recitem['recitemid']], true);
        if (empty($recgi->idnumber)) {
            $recgi->add_idnumber(\gradereport_gradeconfigwizard\formulamanager::generate_id_number($recitem['recitemid']));
            $recgi->update();
        }

        $gi->set_calculation(
            "=IF([[" . $gradeitem->idnumber . "]]>=" . $recitem['recitemgrade'] .
                ",[[" . $gradeitem->idnumber . "]],[[" . $recgi->idnumber . "]])"
        );
        $gi->update();
    }

    /**
     * Moves a grade item to the specified category.
     *
     * This method moves the grade item identified by the provided grade item ID
     * to the category identified by the provided category ID. It loads the grade
     * item, sets its parent category, and updates the grade item in the database.
     *
     * @param int $gradeitemid The ID of the grade item to move.
     * @param int $categoryid The ID of the category to which the grade item will be moved.
     * @return void
     */
    private function move_grade_item_to_category(int $gradeitemid, int $categoryid): void {
        $gi = new \grade_item(['id' => $gradeitemid], true);
        $gi->set_parent($categoryid);
        $gi->update();
    }

    /**
     * Changes the weight of a grade item.
     *
     * This method changes the weight of the grade item identified by the provided item ID.
     * It loads the grade item, sets its weight override and aggregation coefficient properties
     * to the provided weight, and updates the grade item in the database.
     *
     * @param int $itemid The ID of the grade item to change the weight.
     * @param int $weight The new weight to assign to the grade item.
     * @return void
     */
    private function change_item_weight(int $itemid, int $weight): void {
        $gi = new \grade_item(['id' => $itemid], true);
        \grade_item::set_properties($gi, ['weightoverride' => 1, 'aggregationcoef' => $weight]);
        $gi->update();
    }

    /**
     * Creates a category for a grade item within a parent category.
     *
     * This method creates a new category for the grade item identified by the provided
     * grade item ID. It assigns the category to the specified parent category, sets its
     * properties including fullname, aggregation method, and parent category ID, and inserts
     * the category into the database. Additionally, it updates the grade item associated
     * with the newly created category.
     *
     * @param int $gradeitemid The ID of the grade item for which to create a category.
     * @param int $weight The weight to assign to the grade item within the new category.
     * @param int $parentid The ID of the parent category for the new category.
     * @return int The ID of the newly created category.
     */
    private function create_item_category(int $gradeitemid, int $weight, int $parentid): int {
        $gi = new \grade_item(['id' => $gradeitemid], true);
        $gc = new \grade_category(['courseid' => $this->courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => clean_param($gi->get_name(), PARAM_TEXT),
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
            'parent' => clean_param($parentid, PARAM_INT),
        ];
        \grade_category::set_properties($gc, $properties);
        $id = $gc->insert();

        $gradeitem = $gc->load_grade_item();
        \grade_item::set_properties($gradeitem, ['weightoverride' => 1, 'aggregationcoef' => $weight]);
        $gradeitem->update();

        return $id;
    }

}
