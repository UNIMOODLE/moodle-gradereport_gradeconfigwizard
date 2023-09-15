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

/**
 * The gradebook gradeconfigwizard report
 *
 * @package   gradereport_gradeconfigwizard
 * @copyright 2007 Nicolas Connault
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/edit/tree/lib.php';
require_once($CFG->libdir . '/grade/grade_category.php');

use gradereport_gradeconfigwizard\gradebookmanager;

$courseid = required_param('id', PARAM_INT);
$action = optional_param('action', null, PARAM_TEXT);
$actiongradeitemid = optional_param('actiongradeitemid', null, PARAM_INT);


// check if "Disabled items" category exists
$disabledgradecategorygradeitem = false;
if ($action) {
    $disabledgradecategorygradeitem = gradebookmanager::get_disabledcategory($courseid, true);
}


switch ($action) {
	case 'disablegradeitem':
	case 'disablegradeitemcategory':
		if (!empty($actiongradeitemid)) {
            if ($disabledgradecategorygradeitem === false) {
                $disabledgradecategorygradeitem = gradebookmanager::create_disabledcategory($courseid);
            }
            gradebookmanager::move_after($actiongradeitemid, $disabledgradecategorygradeitem->id, $courseid, true);
            $actiongradeitem = \grade_item::fetch(['id' => $actiongradeitemid]);
            \grade_item::set_properties($actiongradeitem, ['weightoverride' => 1, 'aggregationcoef' => 0]);
            $actiongradeitem->update();
		}
		break;
}


// Check if there is a new created category

/*
$categories_data_json =  null;
$gradeitemrowhtml = null;
$gradeitemcategoryrow = null;
if(isset($_POST['categories_data_json'])) $categories_data_json = $_POST['categories_data_json'];
if(isset($_POST['gradeitemrowhtml'])) $gradeitemrowhtml = $_POST['gradeitemrowhtml'];
if(isset($_POST['gradeitemcategoryrow'])) $gradeitemcategoryrow = $_POST['gradeitemcategoryrow'];

if(isset($_POST['categories_data_json']) || $_POST['gradeitemrowhtml']){
    $gradebookcreations = new gradereport_gradeconfigwizard\gradebookcreations($courseid, $categories_data_json, $gradeitemcategoryrow, $gradeitemrowhtml);
    $gradebookcreations->process();
    //echo json_encode($_POST);
    //die();
}*/

$draggedid   = optional_param('draggedid', null, PARAM_ALPHANUM);
$targetid    = optional_param('targetid', null, PARAM_ALPHANUM);
$first       = optional_param('first', false,  PARAM_BOOL); // If First is set to 1, it means the target is the first child of the category $moveafter

if ($draggedid && $targetid) {
    gradereport_gradeconfigwizard\gradebookmanager::move_after($draggedid, $targetid, $courseid, $first);
}


$relativepaths =  null;
$subcategoryname = null;
$gradeitemparent = null;
$randomnames_dictionary = null;
if(isset($_POST['relativepaths'])) $relativepaths = $_POST['relativepaths'];
if(isset($_POST['randomnames_dictionary'])) $randomnames_dictionary = $_POST['randomnames_dictionary'];
if(isset($_POST['subcategoryname'])) $subcategoryname = $_POST['subcategoryname'];
if(isset($_POST['gradeitemparent'])) $gradeitemparent = $_POST['gradeitemparent'];

if(isset($_POST['relativepaths']) || isset($_POST['randomnames_dictionary']) || isset($_POST['subcategoryname'])){
    $gradebookcreations = new gradereport_gradeconfigwizard\gradebookcreations(
        $courseid,                      // Course id where the gradebook will be configured
        $relativepaths,                 // Relative paths of the categories to create
        $randomnames_dictionary,        // Dictionary of random names
        $subcategoryname,                 // Grade items info to create
        $gradeitemparent);

    $gradebookcreations->process();
}

$url = new moodle_url('/grade/report/gradeconfigwizard/index.php', array('id' => $courseid));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

/// Make sure they can even access this course
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/grade:manage', $context);

$strgrades             = get_string('grades');
$strgraderreport       = get_string('graderreport', 'grades');

$actionbar = new \core_grades\output\gradebook_setup_action_bar($context);
$actionbar = null;
print_grade_page_head($courseid, 'report', 'gradeconfigwizard', 'Configurador de notas simple',
    false, false, true, null, null, null, $actionbar);
// print_grade_page_head($courseid, 'settings', 'setup', get_string('gradebooksetup', 'grades'),
//     false, false, true, null, null, null, $actionbar);

echo $OUTPUT->box_start('gradetreebox generalbox');

$gtree = new grade_tree($courseid, false, false);
$movingeid = false;
$gpr = new grade_plugin_return(array('type'=>'edit', 'plugin'=>'tree', 'courseid'=>$courseid));

$grade_edit_tree = new grade_edit_tree($gtree, $movingeid, $gpr);

$tpldata = (object) [
];

// unset($grade_edit_tree->table->head[1]);
// echo '<pre>'; var_dump($grade_edit_tree->table); echo '</pre>'; die('byebye'); //#DEBUG# remove


function gradeconfigwizard_get_grade_items($courseid) {
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

    //return (new moveitems())->generate_eid_dragndrop($availablegradeitems);
    return $availablegradeitems;
}

$tpldata->availablegradeitems = gradeconfigwizard_get_grade_items($courseid);

$tpldata->urlformulacreator = $CFG->wwwroot . "/grade/report/gradeconfigwizard/formulacreator.php?id=" . $courseid;
$tpldata->urlmultipleevaluations = $CFG->wwwroot . "/grade/report/gradeconfigwizard/multipleevaluations.php?id=" . $courseid;
$tpldata->urlweightedevaluations = $CFG->wwwroot . "/grade/report/gradeconfigwizard/weightedevaluations.php?id=" . $courseid;
$tpldata->urlgradereport = $CFG->wwwroot . "/grade/report/grader/index.php?id=" . $courseid;
$tpldata->actionurl = $CFG->wwwroot . "/grade/report/gradeconfigwizard/index.php?id=" . $courseid;
$tpldata->courseid = $courseid;
$tpldata->wwwroot = $CFG->wwwroot;
// $tpldata->table = html_writer::table($grade_edit_tree->table);

echo $OUTPUT->render_from_template('gradereport_gradeconfigwizard/edit_tree', $tpldata);

echo $OUTPUT->box_end();

echo $OUTPUT->footer();
