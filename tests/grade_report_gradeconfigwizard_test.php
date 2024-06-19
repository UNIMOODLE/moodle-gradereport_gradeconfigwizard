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

use context_course;
use context_system;
use context_user;
use core_reportbuilder\local\aggregation\count;
use grade_grade;
use grade_item;
use grade_plugin_return;


/**
 * Class grade_report_gradeconfigwizard_test
 *
 * Test cases for the Grade Config Wizard grade report in the gradebook.
 */
final class grade_report_gradeconfigwizard_test extends \advanced_testcase {

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

        $this->course1 = $this->getDataGenerator()->create_course();

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->student1 = $this->getDataGenerator()->create_user();
        $this->teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course1->id, $teacherrole->id);
        $this->getDataGenerator()->enrol_user($this->student1->id, $this->course1->id, $studentrole->id);

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
        $studentgrades = [$this->student1->id => $this->student1grade1];
        assign_grade_item_update($assignment1, $studentgrades);
        $studentgrades = [$this->student1->id => $this->student1grade2];
        assign_grade_item_update($assignment2, $studentgrades);

        grade_get_setting($this->course1->id, 'report_gradeconfigwizard_showrank', 1);
    }

    /**
     * Test grade_report_configwizard.
     *
     * @covers \gradereport_gradeconfigwizard\grade_report_gradeconfigwizard::setup_table
     */
    public function test_grade_report_configwizard(): void {
        $context = context_course::instance($this->course1->id);
        $gpr = new grade_plugin_return([
            'type' => 'report',
            'plugin' => 'gradeconfigwizard',
            'courseid' => $this->course1->id,
            'userid' => $this->teacher->id,
        ]);
        $report = new grade_report_gradeconfigwizard($this->teacher->id, $gpr, $context);
        $this->assertNotEmpty($report, 'created');
    }

    /**
     * Test setup_courses_data.
     *
     * @covers \gradereport_gradeconfigwizard\grade_report_gradeconfigwizard::setup_courses_data
     */
    public function test_setup_courses_data(): void {
        $context = context_course::instance($this->course1->id);
        $gpr = new grade_plugin_return(
            [
                'type' => 'report',
                'plugin' => 'gradeconfigwizard',
                'courseid' => $this->course1->id,
                'userid' => $this->student1->id,
            ]
        );
        $report = new grade_report_gradeconfigwizard($this->student1->id, $gpr, $context);
        $report->setup_courses_data(true);
        $this->assertNotEmpty($report, 'modified');
    }
}
