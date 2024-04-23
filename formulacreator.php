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

require_once('../../../config.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->libdir.'/grade/grade_item.php');
require_once($CFG->dirroot.'/grade/edit/tree/lib.php');
require_once($CFG->libdir.'/mathslib.php');
require_once($CFG->libdir . '/xmlize.php');
require_once($CFG->dirroot.'/grade/report/gradeconfigwizard/classes/gradebookmanager.php');
require_once($CFG->dirroot.'/grade/report/gradeconfigwizard/classes/gradebook_action_bar_renderer_formulacreator.php');

require_login();

// Url params.
$courseid = required_param('id', PARAM_INT);
$gradeitemid = optional_param('gradeitemid', null, PARAM_INT);

$formulaxml = optional_param('formula', '', PARAM_RAW);

$gradeitemidtarget = $gradeitemid;


// Make sure they can even access this course.
if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    throw new \moodle_exception('invalidcourseid');
}

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/grade:manage', $context);


$strgrades             = get_string('grades');
$strgraderreport       = get_string('graderreport', 'grades');

$actionbar = new gradebook_action_bar_renderer_formulacreator($context);


$gtree = new grade_tree($courseid, false, false);
$gradeitems = $gtree->get_items();

$availablegradeitems = [];

if (!$gradeitem = grade_item::fetch(['id' => $gradeitemid, 'courseid' => $course->id])) {
    throw new \moodle_exception('invaliditemid');
}
$allgradeitem = grade_item::fetch_all(['courseid' => $course->id]);

if ($formulaxml !== "") {
    $gradeitems = \gradereport_gradeconfigwizard\formulamanager::preprocess_formula_xml($formulaxml, $allgradeitem);
    $formula = \gradereport_gradeconfigwizard\formulamanager::generate_formula_moodle($gradeitems);
    $formula = calc_formula::unlocalize($formula);
    $normalizedformula = grade_item::normalize_formula($formula, $course->id);
    $normalizedformula = str_replace('.#', ',#', $normalizedformula);
    $oldgradeitem = $gradeitem->get_calculation();
    $gradeitem->set_calculation($normalizedformula);
    $status = grade_regrade_final_grades($course->id, null, null, null);
    if (is_array($status)) {
        $errormsg = reset($status);
        $gradeitem->set_calculation($oldgradeitem ?? "");
        redirect( $CFG->wwwroot . "/grade/report/gradeconfigwizard/index.php?id=" . $courseid .
            "&gradeitemid=" . $gradeitemid, $errormsg, null, \core\output\notification::NOTIFY_ERROR);

    } else {
        redirect( $CFG->wwwroot . "/grade/report/gradeconfigwizard/index.php?id=" . $courseid .
            "&gradeitemid=" . $gradeitemid, 'Succesfull Update', null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

$availablegradeitems = \gradereport_gradeconfigwizard\gradebookmanager::get_grade_items($course->id);

// Erase element correspendong to the edition.
$availablegradeitems = gradereport_gradeconfigwizard\formulamanager::remove_gradeitem($availablegradeitems, $gradeitemidtarget);
// Erase total course item.
array_pop($availablegradeitems);

$data = [
    'availablegradeitems' => $availablegradeitems,
    'urlsaveexit' => $CFG->wwwroot . "/grade/report/gradeconfigwizard/index.php?id=" . $courseid,
    'courseid' => $courseid,
    'gradeitemid' => $gradeitemidtarget,
    'urlcancelformulacreator' => $CFG->wwwroot . "/grade/report/gradeconfigwizard/index.php?id=" . $courseid,
];

$url = new moodle_url('/grade/report/gradeconfigwizard/formulacreator.php',
    ['id' => $courseid, 'gradeitemid' => $gradeitemid]);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

print_grade_page_head($courseid, 'report', 'gradeconfigwizard', get_string('heading', 'gradereport_gradeconfigwizard' ),
    false, false, true, null, null, null, $actionbar);

echo $OUTPUT->render_from_template('gradereport_gradeconfigwizard/formulacreator', $data);

echo $OUTPUT->footer();
