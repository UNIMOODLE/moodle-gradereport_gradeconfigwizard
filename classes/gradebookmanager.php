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

use grade_edit_tree;
use grade_tree;
use gradereport_summary\local\entities\grade_items;
use moodle_url;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/edit/tree/lib.php');
require_once($CFG->libdir . '/grade/grade_category.php');



/**
 * Class that manages operations related to the grade book.
 *
 */
class gradebookmanager {

    /**
     * Moves a grade item after another grade item within the same course.
     *
     * This method moves the grade item with the specified ID after another grade item
     * with the target ID within the same course. It invokes the `move_gradeitem_after`
     * method with the provided parameters and specifies `false` as the value for the
     * $moveasparent parameter, indicating that the item should be moved after the target.
     *
     * @param int $draggedgradeitemid The ID of the grade item to be moved.
     * @param int $targetidgradeitemid The ID of the grade item after which the dragged item will be placed.
     * @param int $courseid The ID of the course containing the grade items.
     */
    public static function move_after(int $draggedgradeitemid, int $targetidgradeitemid, int $courseid) {
        self::move_gradeitem_after($draggedgradeitemid, $targetidgradeitemid, $courseid, false);
    }

    /**
     * Moves a grade item inside another grade item within the same course.
     *
     * This method moves the grade item with the specified ID inside another grade item
     * with the target ID within the same course. It invokes the `move_gradeitem_after`
     * method with the provided parameters and specifies `true` as the value for the
     * $moveasparent parameter, indicating that the item should be moved inside the target.
     *
     * @param int $draggedgradeitemid The ID of the grade item to be moved.
     * @param int $targetidgradeitemid The ID of the grade item inside which the dragged item will be placed.
     * @param int $courseid The ID of the course containing the grade items.
     */
    public static function move_inside(int $draggedgradeitemid, int $targetidgradeitemid, int $courseid) {
        self::move_gradeitem_after($draggedgradeitemid, $targetidgradeitemid, $courseid, true);
    }

    /**
     * Moves a grade item after another grade item within the same course.
     *
     * This method moves the grade item with the specified ID after another grade item
     * with the target ID within the same course. It fetches the grade items using their
     * respective IDs, generates unique identifiers for them, and then calls the `_move_after`
     * method to perform the actual movement.
     *
     * @param int $draggedgradeitemid The ID of the grade item to be moved.
     * @param int $targetidgradeitemid The ID of the grade item after which the dragged item will be placed.
     * @param int $courseid The ID of the course containing the grade items.
     * @param bool $first (Optional) Indicates whether to move the dragged item as the first item after the target.
     */
    private static function move_gradeitem_after(
        int $draggedgradeitemid,
        int $targetidgradeitemid,
        int $courseid,
        bool $first = false
    ) {
        $draggedgradeitem = \grade_item::fetch(['id' => $draggedgradeitemid]);
        $targetgradeitem = \grade_item::fetch(['id' => $targetidgradeitemid]);

        $eid       = self::generate_eid_dragndrop($draggedgradeitem);
        $moveafter = self::generate_eid_dragndrop($targetgradeitem);

        self::move_after_grade($eid, $moveafter, $courseid, $first);
    }

    /**
     * Moves a grade item after another grade item within the same course.
     *
     * This method moves the grade item identified by its unique identifier ($eid) after
     * another grade item specified by the identifier $moveafter within the same course
     * identified by $courseid. If $first is set to true, the item is moved as the first
     * item after the target; otherwise, it's moved after the target.
     *
     * @param string $eid The unique identifier of the grade item to be moved.
     * @param string $moveafter The unique identifier of the grade item after which the dragged item will be placed.
     * @param int $courseid The ID of the course containing the grade items.
     * @param bool $first (Optional) Indicates whether to move the dragged item as the first item after the target.
     * @throws moodle_exception If the provided element IDs are invalid.
     */
    private static function move_after_grade($eid, $moveafter, $courseid, $first = false) {

        $gtree = new \grade_tree($courseid, false, false);

        if (!$element = $gtree->locate_element($eid)) {
            throw new \moodle_exception('invalidelementid', '');
        }
        $object = $element['object'];

        if (!$afterel = $gtree->locate_element($moveafter)) {
            throw new \moodle_exception('invalidelementid', '');
        }

        $after = $afterel['object'];
        $sortorder = $after->get_sortorder();

        if (!$first) {
            $parent = $after->get_parent_category();
            $object->set_parent($parent->id);
        } else {
            $object->set_parent($after->id);
        }

        $object->move_after_sortorder($sortorder);
    }

    /**
     * Generates a unique identifier for a grade item for drag-and-drop operations.
     *
     * This method generates a unique identifier for the specified grade item, which is
     * used for drag-and-drop operations within the gradebook. The generated identifier
     * depends on the item type: 'manual' or 'mod' items are prefixed with 'ig' followed
     * by the item ID, while 'course' and 'category' items are prefixed with 'cg' followed
     * by the item instance.
     *
     * @param object $gradeitem The grade item for which to generate the unique identifier.
     * @return string The generated unique identifier for the grade item.
     */
    private static function generate_eid_dragndrop($gradeitem) {
        if ($gradeitem->itemtype == 'manual' || $gradeitem->itemtype == 'mod') {
            return 'ig' . $gradeitem->id;
        }
        if ($gradeitem->itemtype == 'course') {
            return 'cg' . $gradeitem->iteminstance;
        }
        if ($gradeitem->itemtype == 'category') {
            return 'cg' . $gradeitem->iteminstance;
        }
    }

    /**
     * Retrieves the disabled grade category for a given course.
     *
     * This method retrieves the disabled grade category, if it exists, for the specified course.
     * It first checks if the category is cached and returns it if caching is enabled and the category
     * is found in the cache. Otherwise, it fetches the category from the database based on the course ID
     * and the full name "[[DISABLED]]". If the category is found, it fetches the corresponding grade item
     * and caches it for future use.
     *
     * @param int $courseid The ID of the course for which to retrieve the disabled category.
     * @param bool $usecache (Optional) Indicates whether to use caching. Default is false.
     * @return mixed The disabled grade item if found, otherwise false.
     */
    public static function get_disabledcategory($courseid, $usecache = false) {
        static $disabledgradecategoriescache = [];
        if ($usecache && isset($disabledgradecategoriescache[$courseid])) {
            return $disabledgradecategoriescache[$courseid];
        }
        $disabledgradecategory = \grade_category::fetch(['courseid' => $courseid, 'fullname' => '[[DISABLED]]']);
        if ($disabledgradecategory === false) {
            return false;
        }
        $disabledgradeitem = \grade_item::fetch([
            'iteminstance' => $disabledgradecategory->id,
            'itemtype' => 'category', 'courseid' => $courseid,
        ]);
        if ($disabledgradeitem !== false) {
            $disabledgradecategoriescache[$courseid] = $disabledgradeitem;
        }
        return $disabledgradeitem;
    }

    /**
     * Creates a disabled grade category for the specified course.
     *
     * This method creates a disabled grade category for the given course. It initializes
     * a new grade category object with the provided course ID, applies default and forced
     * settings, sets properties such as the full name and aggregation method, inserts the
     * category into the database, and then loads the corresponding grade item. It sets the
     * weight override to 1 and the aggregation coefficient to 0 for the grade item before
     * updating it in the database.
     *
     * @param int $courseid The ID of the course for which to create the disabled category.
     * @return \grade_item The created disabled grade item.
     */
    public static function create_disabledcategory($courseid): \grade_item {
        $gc = new \grade_category(['courseid' => $courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => clean_param('[[DISABLED]]', PARAM_TEXT),
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($gc, $properties);
        $id = $gc->insert();

        $gradeitem = $gc->load_grade_item();
        \grade_item::set_properties($gradeitem, ['weightoverride' => 1, 'aggregationcoef' => 0]);
        $gradeitem->update();
        return $gradeitem;
    }


    /**
     * Processes the creation of new categories and grade items for a course.
     *
     * This method facilitates the creation of new categories and grade items for the specified course.
     * It first checks if there are new categories to create. If so, it invokes the `create_categories`
     * method to create the categories and updates the random names dictionary. Then, it checks if there
     * are new grade items to create. If so, it calls the `create_grade_items` method to create the grade
     * items under the specified parent item. Finally, it returns true to indicate that the processing
     * was successful.
     *
     * @param int $courseid The ID of the course for which to create categories and grade items.
     * @param array|null $relativepaths The relative paths of new categories to create, or null if none.
     * @param array $randomnamesdictionary The dictionary of random names used for category creation.
     * @param array|null $newgradeitems The new grade items to create, or null if none.
     * @param \grade_item|null $gradeitemparent The parent grade item under which new items will be created.
     * @return bool True on successful processing.
     */
    public static function process($courseid, $relativepaths, $randomnamesdictionary, $newgradeitems, $gradeitemparent) {
        // In case there are new categories to create.
        if ($relativepaths != null) {
            $randomnamesdictionary = self::create_categories($randomnamesdictionary, $relativepaths, $courseid);
        }
        // In case there are new grade items to create.
        if ($newgradeitems != null) {
            self::create_grade_items($gradeitemparent, $courseid, $randomnamesdictionary, $newgradeitems);
        }
        return true;
    }

    /**
     * Creates grade items under the specified parent grade item for the given course.
     *
     * This method iterates through the provided grade item parent array, which contains
     * the names or IDs of parent grade items. For each parent item, it checks if the
     * corresponding category is already created. If so, it creates the new grade item
     * under that category. Otherwise, it creates the grade item under the category identified
     * by its parent's random name dictionary entry.
     *
     * @param array $gradeitemparent The array containing the names or IDs of parent grade items.
     * @param int $courseid The ID of the course for which to create grade items.
     * @param array $randomnamesdictionary The dictionary of random names used for category creation.
     * @param array $newgradeitems The new grade items to create.
     */
    public static function create_grade_items($gradeitemparent, $courseid, $randomnamesdictionary, $newgradeitems) {
        foreach ($gradeitemparent as $key => $value) {
            // Check if the category is already created.
            $parts = explode('/', $value);
            if (count($parts) == 2) {
                // Case when the category parent is already created.
                self::create_grade_item($parts[1], $newgradeitems[$key], $courseid);
            } else {
                // Otherwise.
                self::create_grade_item($randomnamesdictionary[$parts[0]]['categoryid'], $newgradeitems[$key], $courseid);
            }
        }
    }

    /**
     * Creates a new grade item in the specified category for the given course.
     *
     * This method creates a new grade item in the provided category for the specified course.
     * It initializes a new grade item object with the given course ID and category ID, sets
     * the item type to 'manual', assigns the provided name to the item, and inserts the item
     * into the gradebook.
     *
     * @param int $categoryid The ID of the category under which to create the grade item.
     * @param string $name The name of the new grade item.
     * @param int $courseid The ID of the course for which to create the grade item.
     */
    public static function create_grade_item($categoryid, $name, $courseid) {
        $gradeitem = new \grade_item([
            'courseid' => clean_param($courseid, PARAM_INT),
            'categoryid' => clean_param($categoryid, PARAM_INT),
        ], false);

        $gradeitem->itemtype = 'manual';
        $gradeitem->itemname = $name;
        $gradeitem->insert();              // Add the grade item to the gradebook.
    }

    /**
     * Creates categories based on the provided relative paths for the given course.
     *
     * This method facilitates the creation of categories based on the provided relative paths
     * for the specified course. It initializes a new dictionary of category names with placeholder
     * category IDs, and then iterates through the relative paths to create categories in the correct
     * order of depth. For each relative path, it splits the path into its constituent parts and determines
     * the depth of the category. If the category is not in the current depth order, it skips to the next
     * iteration. If the parent category is already created, it creates a subcategory under it. Otherwise,
     * it creates a subcategory under the root category. It updates the dictionary with the created category
     * IDs and returns the updated random names dictionary.
     *
     * @param array $randomnamesdictionary The dictionary of random names used for category creation.
     * @param array $relativepaths The relative paths of categories to create.
     * @param int $courseid The ID of the course for which to create categories.
     * @return array The updated random names dictionary with category IDs.
     */
    public static function create_categories($randomnamesdictionary, $relativepaths, $courseid) {
        // Init dictionary names.
        $newdictionarynames = [];
        foreach ($randomnamesdictionary as $key => $value) {
            $newdictionarynames[$key]['categoryid'] = -1;
            $newdictionarynames[$key]['name'] = $value;
        }
        $randomnamesdictionary = $newdictionarynames;

        // Make the categories creation process by depth.
        for ($i = 1; $i <= self::get_max_depth($relativepaths); $i++) {
            foreach ($relativepaths as $items) {
                $parts = explode('/', $items);
                $depth = count($parts) - 1;
                // Parts:
                // 0: id of the already created root category
                // (n-1): name of th enew category ...

                if ($depth != $i) {
                    continue;
                } // In case the category is not in the current depth order.

                if (count($parts) == 2) {
                    $newcategoryid = self::create_subcategory(
                        $parts[0],
                        $randomnamesdictionary[$parts[count($parts) - 1]]['name'],
                        $courseid
                    );

                    $randomnamesdictionary[$parts[count($parts) - 1]]['categoryid'] = $newcategoryid;
                } else {
                    // Case when the category parent is a subcategory.
                    $newcategoryid = self::create_subcategory(
                        $randomnamesdictionary[$parts[count($parts) - 2]]['categoryid'],
                        $randomnamesdictionary[$parts[count($parts) - 1]]['name'],
                        $courseid
                    );
                    $randomnamesdictionary[$parts[count($parts) - 1]]['categoryid'] = $newcategoryid;
                }
            }
        }
        return $randomnamesdictionary;
    }

    /**
     * Creates a subcategory under the specified parent category for the given course.
     *
     * This method creates a new subcategory under the provided parent category for the specified course.
     * It initializes a new grade category object with the given course ID, applies default and forced settings,
     * sets the full name of the category to the provided name, and sets the aggregation method to weighted mean.
     * It then inserts the category into the database, sets its parent category, creates a corresponding grade
     * item for the category, assigns a weight override value of 1 to the grade item, and updates the grade item.
     * Finally, it returns the ID of the newly created subcategory.
     *
     * @param int $parentid The ID of the parent category under which to create the subcategory.
     * @param string $name The name of the new subcategory.
     * @param int $courseid The ID of the course for which to create the subcategory.
     * @return int The ID of the newly created subcategory.
     */
    private static function create_subcategory($parentid, $name, $courseid) {
        $gc = new \grade_category(['courseid' => $courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => clean_param($name, PARAM_TEXT),
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($gc, $properties);
        $id = $gc->insert();
        $gc->set_parent($parentid);

        $gradeitem = $gc->load_grade_item();
        \grade_item::set_properties($gradeitem, ['weightoverride' => 1]);
        $gradeitem->update();
        return $id;
    }

    /**
     * Get the maximum depth of relative paths.
     *
     * @param array $relativepaths The array of relative paths.
     * @return int The maximum depth of the relative paths.
     */
    private static function get_max_depth($relativepaths) {
        $maxdepth = 0;
        foreach ($relativepaths as $items) {
            $parts = explode('/', $items);
            if (count($parts) - 1 > $maxdepth) {
                $maxdepth = count($parts) - 1;
            }
        }
        return $maxdepth;
    }

    /**
     * Retrieves grade items for the specified course.
     *
     * This method retrieves grade items for the given course ID. It first initializes a grade tree object
     * for the course and retrieves all grade items from the tree. Then, it iterates through each grade item,
     * processes its data, and constructs an array of available grade items. The method categorizes grade items
     * into various types such as course, category, manual, and mod items and extracts relevant information
     * for each item including its category depth, parent category ID, and other properties. Finally, it returns
     * the array of available grade items.
     *
     * @param int $courseid The ID of the course for which to retrieve grade items.
     * @var object $DB The global database object.
     * @return array An array of available grade items for the specified course.
     */
    public static function get_grade_items($courseid) {
        global $DB;
        $gtree = new grade_tree($courseid, false, false);
        $gradeitems = $gtree->get_items();

        $availablegradeitems = [];

        $categoriesinfo = [];
        $categorytotalsstack = [];
        $course = $DB->get_record('course', ['id' => $courseid]);

        $previouscategorydepth = 0;
        $previousgradeitem = null;

        foreach ($gradeitems as $gradeitemkey => $gradeitem) {

            $availablegradeitem = [
                'iscourse' => false,
                'iscategory' => false,
                'ismod' => false,
                'ismanual' => false,
                'istotal' => false,
                'categorydepth' => 0,
                'categorydepthloop' => [],
                'parentgradecategoryid' => null,
                'parentcategorydepthloop' => [],
            ];

            foreach ($gradeitem->required_fields as $requiredfield) {
                $availablegradeitem[$requiredfield] = $gradeitem->$requiredfield;
            }

            switch ($gradeitem->itemtype) {
                case 'course':
                    $availablegradeitem['iscourse'] = true;

                    if (array_key_exists($gradeitem->iteminstance, $categoriesinfo)) {
                        $gradecategory = $categoriesinfo[$gradeitem->iteminstance];
                    } else {
                        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradeitem->iteminstance]);
                        $categoriesinfo[$gradeitem->iteminstance] = $gradecategory;
                    }

                    $availablegradeitem['gradecategoryid'] = null;
                    $availablegradeitem['gradeitemid'] = $gradeitem->id;

                    $availablegradeitem['displayname'] = $course->fullname;
                    $availablegradeitem['categorydepth'] = $gradecategory->depth - 1;
                    $availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');

                    $availablegradeitemtotal = $availablegradeitem;
                    $availablegradeitemtotal['iscourse'] = false;
                    $availablegradeitemtotal['istotal'] = true;
                    $availablegradeitemtotal['categorydepth'] += 1;
                    $availablegradeitemtotal['categorydepthloop'] = array_fill(0, $availablegradeitemtotal['categorydepth'], '');
                    $availablegradeitemtotal['parentcategorydepthloop'] = array_fill(
                        0,
                        $availablegradeitemtotal['categorydepth'] - 1,
                        ''
                    );
                    array_push($categorytotalsstack, $availablegradeitemtotal);

                    break;

                case 'category':
                    $availablegradeitem['iscategory'] = true;

                    // Get categories info (from cache if it exists).
                    if (array_key_exists($gradeitem->iteminstance, $categoriesinfo)) {
                        $gradecategory = $categoriesinfo[$gradeitem->iteminstance];
                    } else {
                        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradeitem->iteminstance]);
                        $categoriesinfo[$gradeitem->iteminstance] = $gradecategory;
                    }

                    // Set basic gradeitem data.
                    $availablegradeitem['parentgradecategoryid'] = $gradecategory->parent;
                    $availablegradeitem['gradecategoryid'] = $gradecategory->id;
                    $availablegradeitem['gradeitemid'] = $gradeitem->id;
                    $availablegradeitem['categoryname'] = $gradecategory->fullname;

                    // Categorydepthloop used to add padding to items related to it's depth.
                    $availablegradeitem['categorydepth'] = $gradecategory->depth - 1;
                    $availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');

                    // Create a "total" item related to this category and it to categorytotalsstack LIFO stack.
                    $availablegradeitemtotal = $availablegradeitem;
                    $availablegradeitemtotal['iscategory'] = false;
                    $availablegradeitemtotal['istotal'] = true;
                    $availablegradeitemtotal['parentgradecategoryid'] = $gradecategory->id;
                    $availablegradeitemtotal['gradecategoryid'] = null;
                    $availablegradeitemtotal['categorydepth'] += 1;
                    $availablegradeitemtotal['categorydepthloop'] = array_fill(0, $availablegradeitemtotal['categorydepth'], '');
                    $availablegradeitemtotal['parentcategorydepthloop'] = array_fill(
                        0,
                        $availablegradeitemtotal['categorydepth'] - 1,
                        ''
                    );
                    array_push($categorytotalsstack, $availablegradeitemtotal);

                    break;

                case 'manual':
                    $availablegradeitem['ismanual'] = true;

                    $gradecategory = $categoriesinfo[$gradeitem->categoryid];

                    $availablegradeitem['gradecategoryid'] = null;
                    $availablegradeitem['gradeitemid'] = $gradeitem->id;

                    $availablegradeitem['parentgradecategoryid'] = $gradeitem->categoryid;
                    $availablegradeitem['categorydepth'] = $gradecategory->depth;
                    $availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');
                    $availablegradeitemtotal['parentcategorydepthloop'] = array_fill(
                        0,
                        $availablegradeitemtotal['categorydepth'] - 1,
                        ''
                    );
                    break;

                case 'mod':
                    $availablegradeitem['ismod'] = true;

                    $gradecategory = $categoriesinfo[$gradeitem->categoryid];

                    $availablegradeitem['gradecategoryid'] = null;
                    $availablegradeitem['gradeitemid'] = $gradeitem->id;

                    $availablegradeitem['parentgradecategoryid'] = $gradeitem->categoryid;
                    $availablegradeitem['categorydepth'] = $gradecategory->depth;
                    $availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');
                    $availablegradeitemtotal['parentcategorydepthloop'] = array_fill(
                        0,
                        $availablegradeitemtotal['categorydepth'] - 1,
                        ''
                    );
                    break;
            }

            $removedtotalitemskeys = [];
            if (!$availablegradeitem['iscourse']) {
                for ($totalkey = count($categorytotalsstack) - 1; $totalkey >= 0; $totalkey--) {
                    $nexttotalitem = $categorytotalsstack[$totalkey];

                    if (
                        $availablegradeitem['iscategory'] &&
                        $nexttotalitem['parentgradecategoryid'] === $availablegradeitem['iteminstance']
                    ) {
                        // The category "total" item has been just added.
                        continue;
                    }

                    if ($nexttotalitem['categorydepth'] > $availablegradeitem['categorydepth']) {
                        $availablegradeitems[] = $nexttotalitem;
                        $removedtotalitemskeys[] = $totalkey;
                    }
                }
            }

            // Remove used items.
            foreach ($removedtotalitemskeys as $key) {
                unset($categorytotalsstack[$key]);
            }
            $categorytotalsstack = array_values($categorytotalsstack);
            $availablegradeitems[] = $availablegradeitem;
        }

        while ($categorytotalsstack) {
            $availablegradeitems[] = array_pop($categorytotalsstack);
        }

        return $availablegradeitems;
    }

    /**
     * Retrieves grade items for the specified course, including additional configuration details.
     *
     * This method retrieves grade items for the given course ID along with additional configuration details
     * such as weight, aggregation coefficient, and category depth. It initializes a grade tree object for the
     * course and retrieves all grade items from the tree. Then, it iterates through each grade item, processes
     * its data, and constructs an array of available grade items with the specified configuration details. The
     * method categorizes grade items into various types such as course, category, manual, and mod items and
     * extracts relevant information for each item including its category depth, parent category ID, and other
     * properties. Finally, it returns the array of available grade items with the additional configuration details.
     *
     * @param int $courseid The ID of the course for which to retrieve grade items.
     * @return array An array of available grade items for the specified course, including additional configuration details.
     */
    public static function gradeconfigwizard_get_grade_items($courseid) {
        global $DB;
        $gtree = new grade_tree($courseid, false, false);
        $gradeitems = $gtree->get_items();

        $availablegradeitems = [];

        $categoriesinfo = [];
        $categorytotalsstack = [];
        $course = $DB->get_record('course', ['id' => $courseid]);

        $previouscategorydepth = 0;
        $previousgradeitem = null;

        foreach ($gradeitems as $gradeitemkey => $gradeitem) {
            $weight = null;

            $aggcoef = $gradeitem->get_coefstring();
            if ($aggcoef == 'aggregationcoefweight' || $aggcoef == 'aggregationcoef' || $aggcoef == 'aggregationcoefextraweight') {
                $weight = grade_edit_tree::format_number($gradeitem->aggregationcoef);
            } else if ($aggcoef == 'aggregationcoefextraweightsum') {
                $weight = grade_edit_tree::format_number($gradeitem->aggregationcoef2 * 100.0);
            }

            $availablegradeitem = [
                'iscourse' => false,
                'iscategory' => false,
                'ismod' => false,
                'ismanual' => false,
                'istotal' => false,
                'categorydepth' => 0,
                'categorydepthloop' => [],
                'weight' => $weight,
                'parentgradecategoryid' => null,
            ];

            foreach ($gradeitem->required_fields as $requiredfield) {
                $availablegradeitem[$requiredfield] = $gradeitem->$requiredfield;
            }

            switch ($gradeitem->itemtype) {
                case 'course':
                    $availablegradeitem['iscourse'] = true;

                    if (array_key_exists($gradeitem->iteminstance, $categoriesinfo)) {
                        $gradecategory = $categoriesinfo[$gradeitem->iteminstance];
                    } else {
                        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradeitem->iteminstance]);
                        $categoriesinfo[$gradeitem->iteminstance] = $gradecategory;
                    }

                    $availablegradeitem['gradecategoryid'] = null;
                    $availablegradeitem['gradeitemid'] = $gradeitem->id;

                    $availablegradeitem['displayname'] = $course->fullname;
                    $availablegradeitem['categorydepth'] = $gradecategory->depth - 1;
                    $availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');

                    $availablegradeitemtotal = $availablegradeitem;
                    $availablegradeitemtotal['iscourse'] = false;
                    $availablegradeitemtotal['istotal'] = true;
                    $availablegradeitemtotal['categorydepth'] += 1;
                    $availablegradeitemtotal['categorydepthloop'] = array_fill(0, $availablegradeitemtotal['categorydepth'], '');
                    array_push($categorytotalsstack, $availablegradeitemtotal);

                    break;

                case 'category':
                    $availablegradeitem['iscategory'] = true;

                    // Get categories info (from cache if it exists).
                    if (array_key_exists($gradeitem->iteminstance, $categoriesinfo)) {
                        $gradecategory = $categoriesinfo[$gradeitem->iteminstance];
                    } else {
                        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradeitem->iteminstance]);
                        $categoriesinfo[$gradeitem->iteminstance] = $gradecategory;
                    }

                    // Set basic gradeitem data.
                    $availablegradeitem['parentgradecategoryid'] = $gradecategory->parent;
                    $availablegradeitem['gradecategoryid'] = $gradecategory->id;
                    $availablegradeitem['gradeitemid'] = $gradeitem->id;
                    $availablegradeitem['displayname'] = $gradecategory->fullname;

                    // Categorydepthloop used to add padding to items related to it's depth.
                    $availablegradeitem['categorydepth'] = $gradecategory->depth - 1;
                    $availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');

                    // Create a "total" item related to this category and it to categorytotalsstack LIFO stack.
                    $availablegradeitemtotal = $availablegradeitem;
                    $availablegradeitemtotal['iscategory'] = false;
                    $availablegradeitemtotal['istotal'] = true;
                    $availablegradeitemtotal['parentgradecategoryid'] = $gradecategory->id;
                    $availablegradeitemtotal['gradecategoryid'] = null;
                    $availablegradeitemtotal['categorydepth'] += 1;
                    $availablegradeitemtotal['categorydepthloop'] = array_fill(0, $availablegradeitemtotal['categorydepth'], '');
                    array_push($categorytotalsstack, $availablegradeitemtotal);

                    break;

                case 'manual':
                    $availablegradeitem['ismanual'] = true;

                    $gradecategory = $categoriesinfo[$gradeitem->categoryid];

                    $availablegradeitem['gradecategoryid'] = null;
                    $availablegradeitem['gradeitemid'] = $gradeitem->id;

                    $availablegradeitem['parentgradecategoryid'] = $gradeitem->categoryid;
                    $availablegradeitem['categorydepth'] = $gradecategory->depth;
                    $availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');
                    break;

                case 'mod':
                    $availablegradeitem['ismod'] = true;

                    $gradecategory = $categoriesinfo[$gradeitem->categoryid];

                    $availablegradeitem['gradecategoryid'] = null;
                    $availablegradeitem['gradeitemid'] = $gradeitem->id;

                    $availablegradeitem['parentgradecategoryid'] = $gradeitem->categoryid;
                    $availablegradeitem['categorydepth'] = $gradecategory->depth;
                    $availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');
                    break;
            }

            $removedtotalitemskeys = [];
            if (!$availablegradeitem['iscourse']) {
                for ($totalkey = count($categorytotalsstack) - 1; $totalkey >= 0; $totalkey--) {
                    $nexttotalitem = $categorytotalsstack[$totalkey];

                    if (
                        $availablegradeitem['iscategory'] &&
                        $nexttotalitem['parentgradecategoryid'] === $availablegradeitem['iteminstance']
                    ) {
                        // The category "total" item has been just added.
                        continue;
                    }

                    if ($nexttotalitem['categorydepth'] > $availablegradeitem['categorydepth']) {
                        $availablegradeitems[] = $nexttotalitem;
                        $removedtotalitemskeys[] = $totalkey;
                    }
                }
            }

            // Remove used items.
            foreach ($removedtotalitemskeys as $key) {
                unset($categorytotalsstack[$key]);
            }
            $categorytotalsstack = array_values($categorytotalsstack);

            $previousgradeitem = $availablegradeitem;

            $availablegradeitems[] = $availablegradeitem;
        }

        while ($categorytotalsstack) {
            $availablegradeitems[] = array_pop($categorytotalsstack);
        }

        return $availablegradeitems;
    }
}
