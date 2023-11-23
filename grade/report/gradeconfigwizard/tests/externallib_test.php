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

namespace gradereport_gradeconfigwizard;

use externallib_advanced_testcase;
use gradereport_gradeconfigwizard_external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

class externallib_test extends externallib_advanced_testcase {

    /**
     * Set up for every test
     */
    public function setUp(): void {
        global $DB;
        $this->resetAfterTest(true);

        $s1grade1 = 80;
        $s1grade2 = 40;
        $s2grade = 60;

        $this->course1 = $this->getDataGenerator()->create_course();
        $this->course2 = $this->getDataGenerator()->create_course();

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->student1 = $this->getDataGenerator()->create_user();
        $this->teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course1->id, $teacherrole->id);
        $this->getDataGenerator()->enrol_user($this->student1->id, $this->course1->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($this->student1->id, $this->course2->id, $studentrole->id);

        $this->student2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->student2->id, $this->course1->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($this->student2->id, $this->course2->id, $studentrole->id);

        $assignment1 = $this->getDataGenerator()->create_module('assign', array('name' => "Test assign", 'course' => $this->course1->id));
        $assignment2 = $this->getDataGenerator()->create_module('assign', array('name' => "Test assign", 'course' => $this->course2->id));
        $modcontext1 = get_coursemodule_from_instance('assign', $assignment1->id, $this->course1->id);
        $modcontext2 = get_coursemodule_from_instance('assign', $assignment2->id, $this->course2->id);
        $assignment1->cmidnumber = $modcontext1->id;
        $assignment2->cmidnumber = $modcontext2->id;

        $this->student1grade1 = array('userid' => $this->student1->id, 'rawgrade' => $s1grade1);
        $this->student1grade2 = array('userid' => $this->student1->id, 'rawgrade' => $s1grade2);
        $this->student2grade = array('userid' => $this->student2->id, 'rawgrade' => $s2grade);
        $studentgrades = array($this->student1->id => $this->student1grade1, $this->student2->id => $this->student2grade);
        assign_grade_item_update($assignment1, $studentgrades);
        $studentgrades = array($this->student1->id => $this->student1grade2);
        assign_grade_item_update($assignment2, $studentgrades);

        grade_get_setting($this->course1->id, 'report_gradeconfigwizard_showrank', 1);
    }

    /**
     * Test get_course_grades function case student
     */
    public function test_get_course_grades_student() {

        // A user can see his own grades in both courses.
        $this->setUser($this->student1);
        $studentgrades = gradereport_gradeconfigwizard_external::get_course_grades();
        $studentgrades = \external_api::clean_returnvalue(gradereport_gradeconfigwizard_external::get_course_grades_returns(), $studentgrades);

        $this->assertCount(0, $studentgrades['warnings']);
        $this->assertCount(2, $studentgrades['grades']);
        foreach ($studentgrades['grades'] as $grade) {
            if ($grade['courseid'] == $this->course1->id) {
                $this->assertEquals(80.00, $grade['grade']);
                $this->assertEquals(80.0000, $grade['rawgrade']);
                $this->assertEquals(1, $grade['rank']);
            } else {
                $this->assertEquals(40.00, $grade['grade']);
                $this->assertEquals(40.0000, $grade['rawgrade']);
                $this->assertArrayNotHasKey('rank', $grade);
            }
        }

        // Second student, no grade in one course.
        $this->setUser($this->student2);
        $studentgrades = gradereport_gradeconfigwizard_external::get_course_grades();
        $studentgrades = \external_api::clean_returnvalue(gradereport_gradeconfigwizard_external::get_course_grades_returns(), $studentgrades);

        $this->assertCount(0, $studentgrades['warnings']);
        $this->assertCount(2, $studentgrades['grades']);
        foreach ($studentgrades['grades'] as $grade) {
            if ($grade['courseid'] == $this->course1->id) {
                $this->assertEquals(60.00, $grade['grade']);
                $this->assertEquals(60.0000, $grade['rawgrade']);
                $this->assertEquals(2, $grade['rank']);
            } else {
                $this->assertEquals('-', $grade['grade']);
                $this->assertEmpty($grade['rawgrade']);
                $this->assertArrayNotHasKey('rank', $grade);
            }
        }
    }

    /**
     * Test get_course_grades function case admin
     */
    public function test_get_course_grades_admin() {

        // A admin must see all student grades.
        $this->setAdminUser();

        $studentgrades = gradereport_gradeconfigwizard_external::get_course_grades($this->student1->id);
        $studentgrades = \external_api::clean_returnvalue(gradereport_gradeconfigwizard_external::get_course_grades_returns(), $studentgrades);
        $this->assertCount(0, $studentgrades['warnings']);
        $this->assertCount(2, $studentgrades['grades']);
        foreach ($studentgrades['grades'] as $grade) {
            if ($grade['courseid'] == $this->course1->id) {
                $this->assertEquals(80.00, $grade['grade']);
                $this->assertEquals(80.0000, $grade['rawgrade']);
            } else {
                $this->assertEquals(40.00, $grade['grade']);
                $this->assertEquals(40.0000, $grade['rawgrade']);
            }
        }

        $studentgrades = gradereport_gradeconfigwizard_external::get_course_grades($this->student2->id);
        $studentgrades = \external_api::clean_returnvalue(gradereport_gradeconfigwizard_external::get_course_grades_returns(), $studentgrades);
        $this->assertCount(0, $studentgrades['warnings']);
        $this->assertCount(2, $studentgrades['grades']);

        // Admins don't see grades.
        $studentgrades = gradereport_gradeconfigwizard_external::get_course_grades();
        $studentgrades = \external_api::clean_returnvalue(gradereport_gradeconfigwizard_external::get_course_grades_returns(), $studentgrades);
        $this->assertCount(0, $studentgrades['warnings']);
        $this->assertCount(0, $studentgrades['grades']);
    }

    /**
     * Test get_course_grades function case teacher
     */
    public function test_get_course_grades_teacher() {
        // Teachers don't see grades.
        $this->setUser($this->teacher);

        $studentgrades = gradereport_gradeconfigwizard_external::get_course_grades();
        $studentgrades = \external_api::clean_returnvalue(gradereport_gradeconfigwizard_external::get_course_grades_returns(), $studentgrades);
        $this->assertCount(0, $studentgrades['warnings']);
        $this->assertCount(0, $studentgrades['grades']);
    }

    /**
     * Test get_course_grades function case incorrect permissions
     */
    public function test_get_course_grades_permissions() {
        // Student can't see other student grades.
        $this->setUser($this->student2);

        $this->expectException('required_capability_exception');
        $studentgrade = gradereport_gradeconfigwizard_external::get_course_grades($this->student1->id);
    }

    /**
     * Test view_grade_report function
     */
    public function test_view_grade_report() {
        global $USER;

        // Redirect events to the sink, so we can recover them later.
        $sink = $this->redirectEvents();

        $this->setUser($this->student1);
        $result = gradereport_gradeconfigwizard_external::view_grade_report($this->course1->id);
        $result = \external_api::clean_returnvalue(gradereport_gradeconfigwizard_external::view_grade_report_returns(), $result);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        // Check the event details are correct.
        $this->assertInstanceOf('\gradereport_gradeconfigwizard\event\grade_report_viewed', $event);
        $this->assertEquals(\context_course::instance($this->course1->id), $event->get_context());
        $this->assertEquals($USER->id, $event->get_data()['relateduserid']);

        $this->setUser($this->teacher);
        $result = gradereport_gradeconfigwizard_external::view_grade_report($this->course1->id, $this->student1->id);
        $result = \external_api::clean_returnvalue(gradereport_gradeconfigwizard_external::view_grade_report_returns(), $result);
        $events = $sink->get_events();
        $event = reset($events);
        $sink->close();

        // Check the event details are correct.
        $this->assertInstanceOf('\gradereport_gradeconfigwizard\event\grade_report_viewed', $event);
        $this->assertEquals(\context_course::instance($this->course1->id), $event->get_context());
        $this->assertEquals($this->student1->id, $event->get_data()['relateduserid']);
    }

    /**
     * Test view_grade_report_permissions function
     */
    public function test_view_grade_report_permissions() {
        $this->setUser($this->student2);

        $this->expectException('moodle_exception');
        $studentgrade = gradereport_gradeconfigwizard_external::view_grade_report($this->course1->id, $this->student1->id);
    }
}
