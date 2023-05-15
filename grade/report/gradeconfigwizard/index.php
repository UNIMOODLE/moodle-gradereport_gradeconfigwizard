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

$tpldata->urlformulacreator = $CFG->wwwroot . "/grade/report/gradeconfigwizard/formulacreator.php?id=" . $courseid;
$tpldata->urlmultipleevaluations = $CFG->wwwroot . "/grade/report/gradeconfigwizard/multipleevaluations.php?id=" . $courseid;
$tpldata->urlweightedevaluations = $CFG->wwwroot . "/grade/report/gradeconfigwizard/weightedevaluations.php?id=" . $courseid;

$tpldata->table = html_writer::table($grade_edit_tree->table);

echo $OUTPUT->render_from_template('gradereport_gradeconfigwizard/edit_tree', $tpldata);

echo $OUTPUT->box_end();

echo $OUTPUT->footer();