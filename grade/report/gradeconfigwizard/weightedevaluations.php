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
require_once ($CFG->dirroot . '/grade/lib.php');
require_once ($CFG->dirroot . '/grade/edit/tree/lib.php');
require_once ($CFG->dirroot.'/grade/report/gradeconfigwizard/classes/gradebook_action_bar_renderer_weightevaluation.php');

require_login();

$courseid = required_param('id', PARAM_INT);

$url = new moodle_url('/grade/report/gradeconfigwizard/weightedevaluations.php', array('id' => $courseid));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

/// Make sure they can even access this course
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    throw new \moodle_exception('invalidcourseid');
}

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/grade:manage', $context);

$strgrades = get_string('grades');
$strgraderreport = get_string('graderreport', 'grades');
$strheadinggradereport = get_string('gradereportweighteval', 'gradereport_gradeconfigwizard');

$actionbar = new gradebook_action_bar_renderer_weightevaluation($context);

print_grade_page_head($courseid, 'report', 'gradeconfigwizard', $strheadinggradereport,
    false, false, true, null, null, null, $actionbar);

$gtree = new grade_tree($courseid, false, false);
$gradeitems = $gtree->get_items();

$availablegradeitems = \gradereport_gradeconfigwizard\gradebookmanager::get_grade_items($course->id);

$data = [
    'availablegradeitems' => $availablegradeitems,
    'urlsaveexit' => new moodle_url('/grade/report/gradeconfigwizard/processgradebook.php', ['id' => $courseid, 'type' => 'weighted']),
    'urlcancelweighteval' => $CFG->wwwroot . "/grade/report/gradeconfigwizard/index.php?id=" . $courseid,
    'hidecategory' => false,
    'hidecategorytotals'=> true,
];

echo $OUTPUT->render_from_template('gradereport_gradeconfigwizard/weightedevaluations', $data);
//echo $OUTPUT->render_from_template('gradereport_gradeconfigwizard/weightedevaluations_example', $data);

echo $OUTPUT->footer();
