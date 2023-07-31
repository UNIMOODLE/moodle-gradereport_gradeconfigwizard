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

$courseid = required_param('id', PARAM_INT);

// Check if there is a new created category


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
		// echo '<pre>'; var_dump($gradeitem); echo '</pre>'; //#DEBUG# remove
		$availablegradeitem = [
			'iscourse' => false,
			'iscategory' => false,
			'ismod' => false,
			'ismanual' => false,
			'istotal' => false,
			'categorydepth' => 0,
			'categorydepthloop' => [],
			'weight' => (float) $gradeitem->aggregationcoef2 * 100,
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

				$availablegradeitem['gradecategoryid'] = $gradecategory->id;
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

				if (array_key_exists($gradeitem->iteminstance, $categoriesinfo)) {
					$gradecategory = $categoriesinfo[$gradeitem->iteminstance];
				} else {
					$gradecategory = $DB->get_record('grade_categories', ['id' => $gradeitem->iteminstance]);
					$categoriesinfo[$gradeitem->iteminstance] = $gradecategory;
				}

				$availablegradeitem['gradecategoryid'] = $gradecategory->id;
				$availablegradeitem['gradeitemid'] = $gradeitem->id;

				$availablegradeitem['displayname'] = $gradecategory->fullname;
				$availablegradeitem['categorydepth'] = $gradecategory->depth - 1;
				$availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');

				$availablegradeitemtotal = $availablegradeitem;
				$availablegradeitemtotal['iscategory'] = false;
				$availablegradeitemtotal['istotal'] = true;
				$availablegradeitemtotal['categorydepth'] += 1;
				$availablegradeitemtotal['categorydepthloop'] = array_fill(0, $availablegradeitemtotal['categorydepth'], '');
				array_push($categorytotalsstack, $availablegradeitemtotal);

				break;
			
			case 'manual':
				$availablegradeitem['ismanual'] = true;

				$gradecategory = $categoriesinfo[$gradeitem->categoryid];

				$availablegradeitem['gradecategoryid'] = $gradecategory->id;
				$availablegradeitem['gradeitemid'] = $gradeitem->id;

				$availablegradeitem['parentgradecategoryid'] = $gradeitem->categoryid;
				$availablegradeitem['categorydepth'] = $gradecategory->depth;
				$availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');
				break;
			
			case 'mod':
				$availablegradeitem['ismod'] = true;

				$gradecategory = $categoriesinfo[$gradeitem->categoryid];

				$availablegradeitem['gradecategoryid'] = $gradecategory->id;
				$availablegradeitem['gradeitemid'] = $gradeitem->id;

				$availablegradeitem['parentgradecategoryid'] = $gradeitem->categoryid;
				$availablegradeitem['categorydepth'] = $gradecategory->depth;
				$availablegradeitem['categorydepthloop'] = array_fill(0, $availablegradeitem['categorydepth'], '');
				break;
			
		}

		$nexttotalitem = end($categorytotalsstack);
		while ($nexttotalitem && $nexttotalitem['categorydepth'] > $availablegradeitem['categorydepth'] && $nexttotalitem['categoryid'] !== $availablegradeitem['categoryid']) {
			$availablegradeitems[] = array_pop($categorytotalsstack);
			$nexttotalitem = end($categorytotalsstack);
		}

		$previousgradeitem = $availablegradeitem;

		$availablegradeitems[] = $availablegradeitem;
	}

	while ($categorytotalsstack) {
		$availablegradeitems[] = array_pop($categorytotalsstack);
	}

	return $availablegradeitems;
}

$tpldata->availablegradeitems = gradeconfigwizard_get_grade_items($courseid);

$tpldata->urlformulacreator = $CFG->wwwroot . "/grade/report/gradeconfigwizard/formulacreator.php?id=" . $courseid;
$tpldata->urlmultipleevaluations = $CFG->wwwroot . "/grade/report/gradeconfigwizard/multipleevaluations.php?id=" . $courseid;
$tpldata->urlweightedevaluations = $CFG->wwwroot . "/grade/report/gradeconfigwizard/weightedevaluations.php?id=" . $courseid;
$tpldata->urlgradereport = $CFG->wwwroot . "/grade/report/grader/index.php?id=" . $courseid;
$tpldata->actionurl = $CFG->wwwroot . "/grade/report/gradeconfigwizard/index.php?id=" . $courseid;

// $tpldata->table = html_writer::table($grade_edit_tree->table);

echo $OUTPUT->render_from_template('gradereport_gradeconfigwizard/edit_tree', $tpldata);

echo $OUTPUT->box_end();

echo $OUTPUT->footer();
