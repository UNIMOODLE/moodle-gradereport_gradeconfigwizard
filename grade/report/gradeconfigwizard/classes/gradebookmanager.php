<?php
/// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see &lt;http://www.gnu.org/licenses/&gt;.
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

namespace gradereport_gradeconfigwizard;
use grade_edit_tree;
use grade_tree;
use gradereport_summary\local\entities\grade_items;
use moodle_url;
require_once(__DIR__ . '/../../../../config.php');
require_once ($CFG->dirroot.'/grade/lib.php');
require_once ($CFG->dirroot.'/grade/edit/tree/lib.php');
require_once($CFG->libdir . '/grade/grade_category.php');

class gradebookmanager {

    // Gradebook Move
    public static function move_after(int $draggedgradeitemid, int $targetidgradeitemid, int $courseid) {
        self::__move_after($draggedgradeitemid, $targetidgradeitemid, $courseid, false);
    }
    public static function move_inside(int $draggedgradeitemid, int $targetidgradeitemid, int $courseid) {
        self::__move_after($draggedgradeitemid, $targetidgradeitemid, $courseid, true);
    }


    public static function __move_after(int $draggedgradeitemid, int $targetidgradeitemid, int $courseid, bool $first = false) {
        $draggedgradeitem = \grade_item::fetch(['id' => $draggedgradeitemid]);
        $targetgradeitem = \grade_item::fetch(['id' => $targetidgradeitemid]);

        $eid       = self::generate_eid_dragndrop($draggedgradeitem);
        $moveafter = self::generate_eid_dragndrop($targetgradeitem);

        self::_move_after($eid, $moveafter, $courseid, $first);
    }

    // Ref: moodle/grade/edit/tree/index.php
    private static function _move_after($eid, $moveafter, $courseid, $first = false) {

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

    private static function generate_eid_dragndrop($gradeitem) {
        if ($gradeitem->itemtype == 'manual' || $gradeitem->itemtype == 'mod'){
            return 'ig'.$gradeitem->id;
        }
        if ($gradeitem->itemtype == 'course'){
            return 'cg'.$gradeitem->iteminstance;
        }
        if ($gradeitem->itemtype == 'category'){
            return 'cg'.$gradeitem->iteminstance;
        }
    }

    // Gradebook Disable

    public static function get_disabledcategory($courseid, $usecache = false) {
        static $disabledgradecategoriescache = [];
        if ($usecache && isset($disabledgradecategoriescache[$courseid])) {
            return $disabledgradecategoriescache[$courseid];
        }
        $disabledgradecategory = \grade_category::fetch(['courseid' => $courseid, 'fullname' => '[[DISABLED]]']);
        if ($disabledgradecategory === false) return false;
        $disabledgradeitem = \grade_item::fetch(['iteminstance' => $disabledgradecategory->id, 'itemtype' => 'category', 'courseid' => $courseid]);
        if ($disabledgradeitem !== false) {
            $disabledgradecategoriescache[$courseid] = $disabledgradeitem;
        }
        return $disabledgradeitem;
    }

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


    // Gradebook Creations
    public static function process($courseid, $relativepaths, $randomnamesdictionary, $newgradeitems, $gradeitemparent) {
        // In case there are new categories to create
        if ($relativepaths != null) {
            $randomnamesdictionary = self::create_categories($randomnamesdictionary, $relativepaths, $courseid);
        }
        // In case there are new grade items to create
        if ($newgradeitems != null) {
            self::create_grade_items($gradeitemparent, $courseid, $randomnamesdictionary, $newgradeitems);
        }
        // Notify the user that the gradebook has been configured correctly
        $redirecturl = new moodle_url('/grade/report/gradeconfigwizard/index.php', array('id' => $courseid));
        redirect($redirecturl, 'Gradebook configurado correctamente', null, \core\output\notification::NOTIFY_SUCCESS);
    }

    private static function create_grade_items($gradeitemparent, $courseid, $randomnamesdictionary, $newgradeitems) {

        foreach ($gradeitemparent as $key => $value) {
            // Check if the category is already created
            $parts = explode('/', $value);

            if (count($parts) == 2) {
                // Case when the category parent is already created
                self::create_grade_item($parts[1], $newgradeitems[$key], $courseid);
            } else {
                // Otherwise
                self::create_grade_item($randomnamesdictionary[$parts[0]]['categoryid'], $newgradeitems[$key], $courseid);
            }
        }
    }

    private static function create_grade_item($categoryid, $name, $courseid) {
        $gradeitem = new \grade_item(array(
            'courseid' => clean_param($courseid, PARAM_INT),
            'categoryid' => clean_param($categoryid, PARAM_INT),
        ), false);

        $gradeitem->itemtype = 'manual';
        $gradeitem->itemname = $name;
        $gradeitem->insert();              // Add the grade item to the gradebook
    }

    private static function create_categories($randomnamesdictionary, $relativepaths, $courseid) {
        // Init dictionary names
        $newdictionarynames = [];
        foreach ($randomnamesdictionary as $key => $value) {
            $newdictionarynames[$key]['categoryid'] = -1;
            $newdictionarynames[$key]['name'] = $value;
        }
        $randomnamesdictionary = $newdictionarynames;

        // Make the categories creation process by depth
        for ($i = 1; $i <= self::get_max_depth($relativepaths); $i++) {
            foreach ($relativepaths as $items) {
                $parts = explode('/', $items);
                $depth = count($parts) - 1;
                // Parts:
                // 0: id of the already created root category
                // (n-1): name of th enew category ...

                if ($depth != $i) continue; // In case the category is not in the current depth order

                if (count($parts) == 2) {
                    $newcategoryid = self::create_subcategory(
                        $parts[0],
                        $randomnamesdictionary[$parts[count($parts)-1]]['name'],
                        $courseid);

                    $randomnamesdictionary[$parts[count($parts)-1]]['categoryid'] = $newcategoryid;
                } else {
                    // Case when the category parent is a subcategory
                    $newcategoryid = self::create_subcategory(
                        $randomnamesdictionary[$parts[count($parts)-2]]['categoryid'],
                        $randomnamesdictionary[$parts[count($parts)-1]]['name'],
                        $courseid);
                    $randomnamesdictionary[$parts[count($parts)-1]]['categoryid'] = $newcategoryid;
                }
            }
        }
        return $randomnamesdictionary;
    }



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
                    $availablegradeitemtotal['parentcategorydepthloop'] = array_fill(0, $availablegradeitemtotal['categorydepth']-1, '');
                    array_push($categorytotalsstack, $availablegradeitemtotal);

                    break;

                case 'category':
                    $availablegradeitem['iscategory'] = true;

                    // Get categories info (from cache if it exists)
                    if (array_key_exists($gradeitem->iteminstance, $categoriesinfo)) {
                        $gradecategory = $categoriesinfo[$gradeitem->iteminstance];
                    } else {
                        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradeitem->iteminstance]);
                        $categoriesinfo[$gradeitem->iteminstance] = $gradecategory;
                    }

                    // Set basic gradeitem data
                    $availablegradeitem['parentgradecategoryid'] = $gradecategory->parent;
                    $availablegradeitem['gradecategoryid'] = $gradecategory->id;
                    $availablegradeitem['gradeitemid'] = $gradeitem->id;
                    //$availablegradeitem['displayname'] = $gradecategory->fullname;
                    $availablegradeitem['categoryname'] = $gradecategory->fullname;

                    // categorydepthloop used to add padding to items related to it's depth
                    $availablegradeitem['categorydepth'] = $gradecategory->depth - 1;
                    $availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');

                    // Create a "total" item related to this category and it to categorytotalsstack LIFO stack
                    $availablegradeitemtotal = $availablegradeitem;
                    $availablegradeitemtotal['iscategory'] = false;
                    $availablegradeitemtotal['istotal'] = true;
                    $availablegradeitemtotal['parentgradecategoryid'] = $gradecategory->id;
                    $availablegradeitemtotal['gradecategoryid'] = null;
                    $availablegradeitemtotal['categorydepth'] += 1;
                    $availablegradeitemtotal['categorydepthloop'] = array_fill(0, $availablegradeitemtotal['categorydepth'], '');
                    $availablegradeitemtotal['parentcategorydepthloop'] = array_fill(0, $availablegradeitemtotal['categorydepth']-1, '');
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
                    $availablegradeitemtotal['parentcategorydepthloop'] = array_fill(0, $availablegradeitemtotal['categorydepth']-1, '');
                    break;

                case 'mod':
                    $availablegradeitem['ismod'] = true;

                    $gradecategory = $categoriesinfo[$gradeitem->categoryid];

                    $availablegradeitem['gradecategoryid'] = null;
                    $availablegradeitem['gradeitemid'] = $gradeitem->id;

                    $availablegradeitem['parentgradecategoryid'] = $gradeitem->categoryid;
                    $availablegradeitem['categorydepth'] = $gradecategory->depth;
                    $availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');
                    $availablegradeitemtotal['parentcategorydepthloop'] = array_fill(0, $availablegradeitemtotal['categorydepth']-1, '');
                    break;

            }

            $removedtotalitemskeys = [];
            if (!$availablegradeitem['iscourse']) {
                for ($totalkey=count($categorytotalsstack) - 1; $totalkey >= 0; $totalkey--) {
                    $nexttotalitem = $categorytotalsstack[$totalkey];

                    if (
                        $availablegradeitem['iscategory'] &&
                        $nexttotalitem['parentgradecategoryid'] === $availablegradeitem['iteminstance']
                    ) {
                        // the category "total" item has been just added
                        continue;
                    }

                    if ($nexttotalitem['categorydepth'] > $availablegradeitem['categorydepth']) {
                        $availablegradeitems[] = $nexttotalitem;
                        $removedtotalitemskeys[] = $totalkey;
                    }
                }
            }

            // remove used items
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
     * Obtain grade items from a course
     *
     * @param  int $courseid      course id
     * @return array              array of grade items
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

                    // Get categories info (from cache if it exists)
                    if (array_key_exists($gradeitem->iteminstance, $categoriesinfo)) {
                        $gradecategory = $categoriesinfo[$gradeitem->iteminstance];
                    } else {
                        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradeitem->iteminstance]);
                        $categoriesinfo[$gradeitem->iteminstance] = $gradecategory;
                    }

                    // Set basic gradeitem data
                    $availablegradeitem['parentgradecategoryid'] = $gradecategory->parent;
                    $availablegradeitem['gradecategoryid'] = $gradecategory->id;
                    $availablegradeitem['gradeitemid'] = $gradeitem->id;
                    $availablegradeitem['displayname'] = $gradecategory->fullname;

                    // categorydepthloop used to add padding to items related to it's depth
                    $availablegradeitem['categorydepth'] = $gradecategory->depth - 1;
                    $availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');

                    // Create a "total" item related to this category and it to categorytotalsstack LIFO stack
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
                for ($totalkey=count($categorytotalsstack) - 1; $totalkey >= 0; $totalkey--) {
                    $nexttotalitem = $categorytotalsstack[$totalkey];

                    if (
                        $availablegradeitem['iscategory'] &&
                        $nexttotalitem['parentgradecategoryid'] === $availablegradeitem['iteminstance']
                    ) {
                        // the category "total" item has been just added
                        continue;
                    }

                    if ($nexttotalitem['categorydepth'] > $availablegradeitem['categorydepth']) {
                        $availablegradeitems[] = $nexttotalitem;
                        $removedtotalitemskeys[] = $totalkey;
                    }
                }
            }

            // remove used items
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
