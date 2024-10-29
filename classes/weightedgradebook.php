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
 * Class weightedgradebook
 *
 * Represents a weighted gradebook for a specific course.
 */
class weightedgradebook {

    /**
     * @var int The ID of the course associated with the gradebook.
     */
    private $courseid;

    /**
     * Constructor for the weightedgradebook class.
     *
     * Initializes a new instance of the weightedgradebook class
     * with the provided course ID.
     *
     * @param int $courseid The ID of the course associated with the gradebook.
     */
    public function __construct(int $courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Processes the categories and items of the gradebook.
     *
     * This method processes the provided array of categories and their respective
     * subcategories and items. It requires grade-related libraries, sets the course
     * aggregation method, creates categories and subcategories, and moves items
     * to their corresponding categories.
     *
     * @param array $categories An array containing the categories and items of the gradebook.
     * @return bool Returns true on successful processing.
     */
    public function process(array $categories): bool {
        $this->set_course_aggregation();

        foreach ($categories as $category) {
            $categoryid = $this->create_category($category);
            if (isset($category['subcategories'])) {
                foreach ($category['subcategories'] as $subcategory) {
                    $subcategoryid = $this->create_subcategory($subcategory, $categoryid);
                    if (isset($subcategory['items'])) {
                        foreach ($subcategory['items'] as $item) {
                            $this->move_item($item, $subcategoryid);
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Sets the course aggregation method.
     *
     * This method sets the aggregation method for the course grade category
     * to GRADE_AGGREGATE_MAX.
     *
     * @return void
     */
    private function set_course_aggregation(): void {
        $coursegradecat = new \grade_category(['depth' => 1, 'courseid' => $this->courseid, 'fullname' => '?'], true);
        $properties = [
            'aggregation' => GRADE_AGGREGATE_MAX,
        ];
        \grade_category::set_properties($coursegradecat, $properties);
        $coursegradecat->update();
    }

    /**
     * Creates a category in the gradebook.
     *
     * This method creates a category in the gradebook with the provided name
     * and sets the aggregation method to GRADE_AGGREGATE_WEIGHTED_MEAN.
     *
     * @param array $category An array containing information about the category.
     * @return int The ID of the newly created category.
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
        return $gc->insert();
    }

    /**
     * Creates a subcategory in the gradebook.
     *
     * This method creates a subcategory in the gradebook with the provided name,
     * sets the aggregation method to GRADE_AGGREGATE_WEIGHTED_MEAN, and assigns
     * it to the specified parent category.
     *
     * @param array $subcategory An array containing information about the subcategory.
     * @param int $parentid The ID of the parent category.
     * @return int The ID of the newly created subcategory.
     */
    private function create_subcategory(array $subcategory, int $parentid): int {
        $gc = new \grade_category(['courseid' => $this->courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => clean_param($subcategory['name'], PARAM_TEXT),
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($gc, $properties);
        $id = $gc->insert();
        $gc->set_parent($parentid);

        $gradeitem = $gc->load_grade_item();
        \grade_item::set_properties($gradeitem, ['weightoverride' => 1, 'aggregationcoef' => $subcategory['weight']]);
        $gradeitem->update();

        return $id;
    }

    /**
     * Moves an item to a category in the gradebook.
     *
     * This method moves an item to the specified parent category in the gradebook
     * and assigns the provided weight to the item.
     *
     * @param array $item An array containing information about the item.
     * @param int $parentid The ID of the parent category.
     * @return void
     */
    private function move_item(array $item, int $parentid): void {
        $gi = new \grade_item(['id' => $item['id']], true);
        $gi->set_parent($parentid);
        $parameters = ['aggregationcoef' => $item['weight']];
        \grade_item::set_properties($gi, $parameters);
        $gi->update();
    }

}
