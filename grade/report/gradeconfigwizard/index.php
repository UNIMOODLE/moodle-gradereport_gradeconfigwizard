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


require_once ('../../../config.php');
require_once ($CFG->dirroot.'/grade/lib.php');
require_once ($CFG->dirroot.'/grade/edit/tree/lib.php');
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
            //gradebookmanager::move_after($actiongradeitemid, $disabledgradecategorygradeitem->id, $courseid, true);
            gradebookmanager::move_inside($actiongradeitemid, $disabledgradecategorygradeitem->id, $courseid);
            $actiongradeitem = \grade_item::fetch(['id' => $actiongradeitemid]);
            \grade_item::set_properties($actiongradeitem, ['weightoverride' => 1, 'aggregationcoef' => 0]);
            $actiongradeitem->update();
		}
		break;
}


$draggedid   = optional_param('draggedid', null, PARAM_ALPHANUM);
$targetid    = optional_param('targetid', null, PARAM_ALPHANUM);
$move       = optional_param('move', null,  PARAM_ALPHANUM); // If First is set to 1, it means the target is the first child of the category $moveafter

if ($draggedid && $targetid) {
    if ($move == 'after'){
        gradereport_gradeconfigwizard\gradebookmanager::move_after($draggedid, $targetid, $courseid);
    } else if($move == 'inside'){
        gradereport_gradeconfigwizard\gradebookmanager::move_inside($draggedid, $targetid, $courseid);
    }
}


$relativepaths =  null;
$subcategoryname = null;
$gradeitemparent = null;
$randomnamesdictionary = null;
if (isset($_POST['relativepaths'])) $relativepaths = $_POST['relativepaths'];
if (isset($_POST['randomnames_dictionary'])) $randomnamesdictionary = $_POST['randomnames_dictionary'];
if (isset($_POST['subcategoryname'])) $subcategoryname = $_POST['subcategoryname'];
if (isset($_POST['gradeitemparent'])) $gradeitemparent = $_POST['gradeitemparent'];
if (isset($_POST['relativepaths']) || isset($_POST['randomnames_dictionary']) || isset($_POST['subcategoryname'])){
    gradereport_gradeconfigwizard\gradebookmanager::process($courseid,
    $relativepaths,
    $randomnamesdictionary,
    $subcategoryname,
    $gradeitemparent);
}

$url = new moodle_url('/grade/report/gradeconfigwizard/index.php', array('id' => $courseid));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

/// Make sure they can even access this course
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    throw new \moodle_exception('invalidcourseid');
}

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/grade:manage', $context);

$strgrades             = get_string('grades');
$strgraderreport       = get_string('graderreport', 'grades');
$strheadinggraderreport = get_string('gradereportheading', 'gradereport_gradeconfigwizard');

$actionbar = new \core_grades\output\gradebook_setup_action_bar($context);
$actionbar = null;
print_grade_page_head($courseid, 'report', 'gradeconfigwizard', $strheadinggraderreport,
    false, false, true, null, null, null, $actionbar);

echo $OUTPUT->box_start('gradetreebox generalbox');

$gtree = new grade_tree($courseid, false, false);
$movingeid = false;
$gpr = new grade_plugin_return(array('type'=>'edit', 'plugin'=>'tree', 'courseid'=>$courseid));

$gradeedittree = new grade_edit_tree($gtree, $movingeid, $gpr);

$tpldata = (object) [
];


$tpldata->availablegradeitems = \gradereport_gradeconfigwizard\gradebookmanager::gradeconfigwizard_get_grade_items($courseid);

$tpldata->urlformulacreator = $CFG->wwwroot . "/grade/report/gradeconfigwizard/formulacreator.php?id=" . $courseid;
$tpldata->urlmultipleevaluations = $CFG->wwwroot . "/grade/report/gradeconfigwizard/multipleevaluations.php?id=" . $courseid;
$tpldata->urlweightedevaluations = $CFG->wwwroot . "/grade/report/gradeconfigwizard/weightedevaluations.php?id=" . $courseid;
$tpldata->urlgradereport = $CFG->wwwroot . "/grade/report/grader/index.php?id=" . $courseid;
$tpldata->actionurl = $CFG->wwwroot . "/grade/report/gradeconfigwizard/index.php?id=" . $courseid;
$tpldata->courseid = $courseid;
$tpldata->wwwroot = $CFG->wwwroot;

echo $OUTPUT->render_from_template('gradereport_gradeconfigwizard/edit_tree', $tpldata);

echo $OUTPUT->box_end();

echo $OUTPUT->footer();
