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

use grade_item;

defined('MOODLE_INTERNAL') || die();


class  gradeconfigwizard_gradebookmanager_test extends \advanced_testcase {

    /**
     * Set up for every test.
     */
    public function setUp(): void {
        global $DB;
        $this->resetAfterTest(true);

        $student1grade1 = 80;
        $student1grade2 = 40;

        $this->course1 = $this->getDataGenerator()->create_course();

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->student1 = $this->getDataGenerator()->create_user();
        $this->teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course1->id, $teacherrole->id);
        $this->getDataGenerator()->enrol_user($this->student1->id, $this->course1->id, $studentrole->id);

        $assignment1 = $this->getDataGenerator()->create_module('assign', array('name' => "Test assign 1", 'course' => $this->course1->id));
        $assignment2 = $this->getDataGenerator()->create_module('assign', array('name' => "Test assign 2", 'course' => $this->course1->id));
        $modcontext1 = get_coursemodule_from_instance('assign', $assignment1->id, $this->course1->id);
        $modcontext2 = get_coursemodule_from_instance('assign', $assignment2->id, $this->course1->id);
        $assignment1->cmidnumber = $modcontext1->id;
        $assignment2->cmidnumber = $modcontext2->id;

        $this->student1grade1 = array('userid' => $this->student1->id, 'rawgrade' => $student1grade1);
        $this->student1grade2 = array('userid' => $this->student1->id, 'rawgrade' => $student1grade2);
        $studentgrades = array($this->student1->id => $this->student1grade1);
        assign_grade_item_update($assignment1, $studentgrades);
        $studentgrades = array($this->student1->id => $this->student1grade2);
        assign_grade_item_update($assignment2, $studentgrades);

        grade_get_setting($this->course1->id, 'report_gradeconfigwizard_showrank', 1);
    }

    /**
     * Test move_after.
     *
     * @covers \gradereport_gradeconfigwizard\gradebookmanager::move_gradeitem_after
     * @covers \gradereport_gradeconfigwizard\gradebookmanager::generate_eid_dragndrop
     * @covers \gradereport_gradeconfigwizard\gradebookmanager::_move_after
     */
    public function test_move_after() {
        $assigment1 = grade_item::fetch(array('itemname' => 'Test assign 1'));
        $assigment2 = grade_item::fetch(array('itemname' => 'Test assign 2'));
        gradebookmanager::move_after($assigment1->id, $assigment2->id, $this->course1->id);
        $assigment1new = grade_item::fetch(array('itemname' => 'Test assign 1'));
        $this->assertNotEquals($assigment1new->sortorder, $assigment1->sortorder);
    }

    /**
     * Test move_inside.
     *
     * @covers \gradereport_gradeconfigwizard\gradebookmanager::move_gradeitem_after
     * @covers \gradereport_gradeconfigwizard\gradebookmanager::generate_eid_dragndrop
     * @covers \gradereport_gradeconfigwizard\gradebookmanager::_move_after
     */
    public function test_move_inside() {
        $assigment1 = grade_item::fetch(array('itemname' => 'Test assign 1'));
        $assigment2 = grade_item::fetch(array('itemname' => 'Test assign 2'));
        gradebookmanager::move_inside($assigment1->id, $assigment2->id, $this->course1->id);
        $assigment1new = grade_item::fetch(array('itemname' => 'Test assign 1'));
        $this->assertNotEquals($assigment1new->sortorder, $assigment1->sortorder);
    }

    /**
     * Test create_disabledcategory.
     */
    public function test_create_disabledcategory() {
        $disabledcategory = gradebookmanager::create_disabledcategory($this->course1->id);
        $this->assertNotEmpty($disabledcategory, 'disabled category not created');
    }

    /**
     * Test get_disabledcategory.
     */
    public function test_get_disabledcategory() {
        gradebookmanager::create_disabledcategory($this->course1->id);
        $disabledcategory = gradebookmanager::get_disabledcategory($this->course1->id);
        $this->assertNotEmpty($disabledcategory, 'not get the disabled category');
    }

    /**
     * Test process.
     * @covers \gradereport_gradeconfigwizard\gradebookmanager::create_categories
     * @covers \gradereport_gradeconfigwizard\gradebookmanager::create_subcategory
     * @covers \gradereport_gradeconfigwizard\gradebookmanager::get_max_depth
     * @covers \gradereport_gradeconfigwizard\gradebookmanager::create_grade_items
     */
    public function test_process() {
        $relativepaths = [
            'rel1' => '3/rel1',
            'rel2' => '3/rel1/rel2',
        ];
        $randomnamedictionary = [
            'rel1' => 'C1',
            'rel2' => 'C2',
        ];
        $newgrades = [
            'g1' => 'G1',
            'g2' => 'G2',
        ];
        $gradeitemparents = [
            'g1' => 'rel1',
            'g2' => 'rel2',
        ];
        $gradebook = gradebookmanager::process($this->course1->id, $relativepaths,  $randomnamedictionary, $newgrades, $gradeitemparents);
        $this->assertEquals(1, $gradebook);
    }

    /**
     * Test create_grade_item.
     */
    public function test_create_grade_item() {
        $c1 = $this->getDataGenerator()->create_category(array('idnumber' => 'C1'));
        gradebookmanager::create_grade_item($c1->id, 'g1', $this->course1->id);
        $gradeitem = grade_item::fetch(array('itemname' => 'g1'));
        $this->assertNotEmpty($gradeitem, 'grade item not created');
    }

    /**
     * Test get_grade_items
     */
    public function test_get_grade_items() {
        $gradeitems = gradebookmanager::get_grade_items($this->course1->id);
        $this->assertNotEmpty($gradeitems, 'not get the items');
    }

    /**
     * Test get_grade_items
     */
    public function test_gradeconfigwizard_get_grade_items() {
        $gradeitems = gradebookmanager::gradeconfigwizard_get_grade_items($this->course1->id);
        $this->assertNotEmpty($gradeitems, 'not get items');
    }

}
