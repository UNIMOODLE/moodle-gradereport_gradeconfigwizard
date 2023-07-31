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
require_once $CFG->libdir.'/grade/grade_item.php';
require_once $CFG->dirroot.'/grade/edit/tree/lib.php';
require_once $CFG->libdir.'/mathslib.php';
require_once($CFG->libdir . '/xmlize.php');
require_once $CFG->dirroot.'/grade/report/gradeconfigwizard/lib.php';


//url params
$courseid = required_param('id', PARAM_INT);
$gradeitemid = optional_param('gradeitemid',null, PARAM_INT);

$formulaxml = optional_param('formula', '', PARAM_RAW);

$gradeitemidtarget = $gradeitemid;



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


$gtree = new grade_tree($courseid, false, false);
$gradeitems = $gtree->get_items();

$availablegradeitems = [];

if (!$grade_item = grade_item::fetch(array('id'=>$gradeitemid, 'courseid'=>$course->id))) {
    throw new \moodle_exception('invaliditemid');
}
$all_grade_item = grade_item::fetch_all(array('courseid'=>$course->id));

if($formulaxml !== ""){
    $item_nota = [
        'id' => '',
        'idnumber' => '',
        'displayname' => '',
        'weight' => '',
        'operation' => '',
    ];
    $array = xmlize($formulaxml);
    // operation name
    $op = key($array);
    $elements= $array[$op]['#'];
    $grade_items = [];

    for ($i = 0; $i < sizeof($elements['ITEM']); $i++){
        $items= $elements['ITEM'][$i];
        foreach ($items as $item){
            $item_nota['operation'] = $op;
            if($op === "WEIGHTEDMEANGRADES") $item_nota['weight'] = $item['WEIGHT'][0]['#'];
            $item_nota['id'] = $item['GRADEITEMID'][0]['#'];
            $item_nota['idnumber'] = obtain_idnumber($item['GRADEITEMID'][0]['#'],$all_grade_item);
            $item_nota['displayname'] = $item['DISPLAYNAME'][0]['#'];
            $grade_items[] = $item_nota;
        }
    }

    $formula = generate_formula_moodle($grade_items);
    $formula = calc_formula::unlocalize($formula);
    $normalized_formula = grade_item::normalize_formula($formula, $course->id);
    $normalized_formula = str_replace('.#', ',#', $normalized_formula);
    $old_grade_item = $grade_item->get_calculation();
    $grade_item->set_calculation($normalized_formula);
    $status = grade_regrade_final_grades($course->id, null, null, null);
    if (is_array($status)){
        $errormsg = reset($status);
        $grade_item->set_calculation($old_grade_item ?? "");
        redirect( $CFG->wwwroot . "/grade/report/gradeconfigwizard/index.php?id=" . $courseid . "&gradeitemid=" . $gradeitemid, $errormsg, null, \core\output\notification::NOTIFY_ERROR);

    }
    else{
        redirect( $CFG->wwwroot . "/grade/report/gradeconfigwizard/index.php?id=" . $courseid . "&gradeitemid=" . $gradeitemid, 'Succesfull Update', null, \core\output\notification::NOTIFY_SUCCESS);
    }
}


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

// Elimino elemento correspondiente a la edicion del calculo (no autoaparece en la seleccion)
$pos = search_selfitem($availablegradeitems, $gradeitemidtarget);
array_splice($availablegradeitems, $pos, 1);


//print_object($availablegradeitems);
// echo '<pre>'; var_dump($availablegradeitems); echo '</pre>'; die('fd8798f7d89f7d8'); //#DEBUG# remove

$data = [
    'availablegradeitems' => $availablegradeitems,
    'urlsaveexit' => $CFG->wwwroot . "/grade/report/gradeconfigwizard/index.php?id=" . $courseid,
    'courseid' => $courseid,
    'gradeitemid' => $gradeitemidtarget,
];

$url = new moodle_url('/grade/report/gradeconfigwizard/formulacreator.php', array('id' => $courseid), array('gradeitemid' => $gradeitemid));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

print_grade_page_head($courseid, 'report', 'gradeconfigwizard', 'Editor fórmulas mejorado',
    false, false, true, null, null, null, $actionbar);

echo $OUTPUT->render_from_template('gradereport_gradeconfigwizard/formulacreator', $data);



/*
echo $OUTPUT->box_end();
*/

echo $OUTPUT->footer();