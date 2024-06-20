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


use grade_item;

/**
 * Class formulamanager_test
 *
 * Test cases for the FormulaManager class in the grade configuration wizard.
 */
final class formulamanager_test extends \advanced_testcase {

    /**
     * The object course1
     * @var object $course1
     */
    public $course1;

    /**
     * The object teacher
     * @var object $teacher
     */
    public $teacher;

    /**
     * The object student1
     * @var object $student1
     */
    public $student1;

    /**
     * The object student2
     * @var object $student2
     */
    public $student2;

    /**
     * The object student1grade1
     * @var object $student1grade1
     */
    public $student1grade1;

    /**
     * The object student2grade1
     * @var object $student2grade1
     */
    public $student2grade1;

    /**
     * The object student1grade2
     * @var object $student1grade2
     */
    public $student1grade2;

    /**
     * Set up for every test
     */
    public function setUp(): void {
        global $DB;
        $this->resetAfterTest(true);

        $student1grade1 = 80;
        $student1grade2 = 40;
        $student2grade1 = 60;

        $this->course1 = $this->getDataGenerator()->create_course();

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->student1 = $this->getDataGenerator()->create_user();
        $this->teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course1->id, $teacherrole->id);
        $this->getDataGenerator()->enrol_user($this->student1->id, $this->course1->id, $studentrole->id);

        $this->student2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->student2->id, $this->course1->id, $studentrole->id);

        $assignment1 = $this->getDataGenerator()->create_module(
            'assign',
            [
                'name' => "Test assign 1",
                'course' => $this->course1->id,
            ]
        );
        $assignment2 = $this->getDataGenerator()->create_module(
            'assign',
            [
                'name' => "Test assign 2",
                'course' => $this->course1->id,
            ]
        );
        $modcontext1 = get_coursemodule_from_instance('assign', $assignment1->id, $this->course1->id);
        $modcontext2 = get_coursemodule_from_instance('assign', $assignment2->id, $this->course1->id);
        $assignment1->cmidnumber = $modcontext1->id;
        $assignment2->cmidnumber = $modcontext2->id;

        $this->student1grade1 = [
            'userid' => $this->student1->id,
            'rawgrade' => $student1grade1,
        ];
        $this->student1grade2 = [
            'userid' => $this->student1->id,
            'rawgrade' => $student1grade2,
        ];
        $this->student2grade1 = [
            'userid' => $this->student2->id,
            'rawgrade' => $student2grade1,
        ];
        $studentgrades = [
            $this->student1->id => $this->student1grade1,
            $this->student2->id => $this->student2grade1,
        ];
        assign_grade_item_update($assignment1, $studentgrades);
        $studentgrades = [$this->student1->id => $this->student1grade2];
        assign_grade_item_update($assignment2, $studentgrades);

        grade_get_setting($this->course1->id, 'report_gradeconfigwizard_showrank', 1);
    }

    /**
     * Test generate_id_number.
     *
     * @covers \gradereport_gradeconfigwizard\formulamanager::generate_id_number
     */
    public function test_generate_id_number(): void {
        $assigment1 = grade_item::fetch(['itemname' => 'Test assign 1']);
        $data = formulamanager::generate_id_number($assigment1->id);
        $result = 'test_assign_1_' . $assigment1->id;
        $this->assertEquals($result, $data);
    }

    /**
     * Test preprocess_formula_xml.
     *
     * @covers \gradereport_gradeconfigwizard\formulamanager::obtain_idnumber
     * @covers \gradereport_gradeconfigwizard\formulamanager::select_grade_item
     * @covers \gradereport_gradeconfigwizard\formulamanager::normalize_string
     * @covers \gradereport_gradeconfigwizard\formulamanager::afegir_idnumber
     */
    public function test_preprocess_formula_xml(): void {
        $allgradeitem = grade_item::fetch_all(['courseid' => $this->course1->id]);
        $assigment1 = grade_item::fetch(['itemname' => 'Test assign 1']);
        $assigment2 = grade_item::fetch(['itemname' => 'Test assign 2']);

        $formula = <<<EOF
        <SUM>
            <ITEM>
                <DISPLAYNAME>$assigment1->itemname</DISPLAYNAME>
                <IDNUMBER>$assigment1->idnumber</IDNUMBER>
                <GRADEITEMID>$assigment1->id</GRADEITEMID>
            </ITEM>
            <ITEM>
                <DISPLAYNAME>$assigment2->itemname</DISPLAYNAME>
                <IDNUMBER>$assigment2->idnumber</IDNUMBER>
                <GRADEITEMID>$assigment2->id</GRADEITEMID>
            </ITEM>
        </SUM>
        EOF;

        $gradeitems = \gradereport_gradeconfigwizard\formulamanager::preprocess_formula_xml($formula, $allgradeitem);
        $result = [
            [
                'id' => $assigment1->id,
                'idnumber' => $assigment1->idnumber,
                'displayname' => $assigment1->itemname,
                'weight' => '',
                'operation' => 'SUM',
            ],
            [
                'id' => $assigment2->id,
                'idnumber' => $assigment2->idnumber,
                'displayname' => $assigment2->itemname,
                'weight' => '',
                'operation' => 'SUM',
            ],
        ];
        $this->assertEquals($result, $gradeitems);
    }

    /**
     * Test remove_gradeitem.
     *
     * @covers \gradereport_gradeconfigwizard\formulamanager::remove_gradeitem
     */
    public function test_remove_gradeitem(): void {
        $availablegradeitems = \gradereport_gradeconfigwizard\gradebookmanager::get_grade_items($this->course1->id);
        $assigment2 = grade_item::fetch(['itemname' => 'Test assign 2']);
        $arraysize = \gradereport_gradeconfigwizard\formulamanager::remove_gradeitem($availablegradeitems, $assigment2->id);
        $this->assertEquals(count($availablegradeitems) - 1, count($arraysize));
    }

    /**
     * Test generate_formula_moodle.
     *
     * @covers \gradereport_gradeconfigwizard\formulamanager::generate_formula_moodle
     */
    public function test_generate_formula_moodle(): void {
        $allgradeitem = grade_item::fetch_all(['courseid' => $this->course1->id]);

        $assigment1 = grade_item::fetch(['itemname' => 'Test assign 1']);
        $assigment2 = grade_item::fetch(['itemname' => 'Test assign 2']);

        $formula = <<<EOF
        <SUM>
            <ITEM>
                <DISPLAYNAME>$assigment1->itemname</DISPLAYNAME>
                <IDNUMBER>$assigment1->idnumber</IDNUMBER>
                <GRADEITEMID>$assigment1->id</GRADEITEMID>
            </ITEM>
            <ITEM>
                <DISPLAYNAME>$assigment2->itemname</DISPLAYNAME>
                <IDNUMBER>$assigment2->idnumber</IDNUMBER>
                <GRADEITEMID>$assigment2->id</GRADEITEMID>
            </ITEM>
        </SUM>
        EOF;

        $gradeitems = \gradereport_gradeconfigwizard\formulamanager::preprocess_formula_xml($formula, $allgradeitem);
        $processedformula = \gradereport_gradeconfigwizard\formulamanager::generate_formula_moodle($gradeitems);
        $result = '=sum([[' . $assigment1->idnumber . ']],[[' . $assigment2->idnumber . ']])';
        $this->assertEquals($result, $processedformula);
    }

    /**
     * Test generate_check_operation.
     *
     * @covers \gradereport_gradeconfigwizard\formulamanager::check_operation
     */
    public function test_check_operation(): void {
        $op1 = "HIGHEST";
        $res1 = formulamanager::check_operation($op1);
        $this->assertEquals('max', $res1);
        $op2 = "LOWEST";
        $res2 = formulamanager::check_operation($op2);
        $this->assertEquals('min', $res2);
        $op3 = "SUM";
        $res3 = formulamanager::check_operation($op3);
        $this->assertEquals('sum', $res3);
        $op4 = "WEIGHTEDMEANGRADES";
        $res4 = formulamanager::check_operation($op4);
        $this->assertEquals('sum', $res4);
        $op5 = "MEANGRADES";
        $res5 = formulamanager::check_operation($op5);
        $this->assertEquals('average', $res5);
    }
}
