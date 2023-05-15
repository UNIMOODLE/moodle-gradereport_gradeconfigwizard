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

$url = new moodle_url('/grade/report/gradeconfigwizard/weightedevaluations.php', array('id' => $courseid));
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
print_grade_page_head($courseid, 'report', 'gradeconfigwizard', 'Múltiples evaluaciones',
    false, false, true, null, null, null, $actionbar);

$gtree = new grade_tree($courseid, false, false);
$gradeitems = $gtree->get_items();

// $gseq = new grade_seq($courseid);
// $gradeitems = $gseq->elements;


// echo '<pre>'; var_dump($gradeitems); echo '</pre>'; die('f7d8f678d6f78d6'); //#DEBUG# remove


$availablegradeitems = [];

foreach ($gradeitems as $gradeitemkey => $gradeitem) {
	$availablegradeitem = [
		'iscourse' => false,
		'iscategory' => false,
		'ismod' => false,
		'ismanual' => false,
	];

	switch ($gradeitem->itemtype) {
		case 'course':
			$availablegradeitem['iscourse'] = true;
			break;
		
		case 'category':
			$availablegradeitem['iscategory'] = true;

			$gradecategory = $DB->get_record('grade_categories', ['id' => $gradeitem->iteminstance]);
			$availablegradeitem['categoryname'] = $gradecategory->fullname;
			break;
		
		case 'manual':
			$availablegradeitem['ismanual'] = true;
			break;
		
		case 'mod':
			$availablegradeitem['ismod'] = true;
			break;
		
		default:
			# code...
			break;
	}

	foreach ($gradeitem->required_fields as $requiredfield) {
		$availablegradeitem[$requiredfield] = $gradeitem->$requiredfield;
	}
	
	// $availablegradeitem['depth'] = $gradeitem->depth;

	$availablegradeitems[] = $availablegradeitem;
}

// echo '<pre>'; var_dump($availablegradeitems); echo '</pre>'; die('fd8798f7d89f7d8'); //#DEBUG# remove

$data = [
	'availablegradeitems' => $availablegradeitems
];

echo $OUTPUT->render_from_template('gradereport_gradeconfigwizard/weightedevaluations', $data);

/*
echo $OUTPUT->box_end();
*/

echo $OUTPUT->footer();