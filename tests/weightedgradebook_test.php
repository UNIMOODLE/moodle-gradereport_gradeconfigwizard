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
 * Class weightedgradebook_test
 *
 * Test cases for the Weighted Gradebook in the Grade Config Wizard.
 */
final class weightedgradebook_test extends \advanced_testcase {

    /**
     * The object course1
     * @var object $course1
     */
    public $course1;

    /**
     * Set up for every test
     */
    public function setUp(): void {
        global $DB;
        $this->resetAfterTest(true);
        $this->course1 = $this->getDataGenerator()->create_course();

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
        $assignment3 = $this->getDataGenerator()->create_module(
            'assign',
            [
                'name' => "Test assign 3",
                'course' => $this->course1->id,
            ]
        );
        $modcontext1 = get_coursemodule_from_instance('assign', $assignment1->id, $this->course1->id);
        $modcontext2 = get_coursemodule_from_instance('assign', $assignment2->id, $this->course1->id);
        $modcontext3 = get_coursemodule_from_instance('assign', $assignment3->id, $this->course1->id);
        $assignment1->cmidnumber = $modcontext1->id;
        $assignment2->cmidnumber = $modcontext2->id;
        $assignment3->cmidnumber = $modcontext3->id;
    }

    /**
     * Test process.
     * @covers \gradereport_gradeconfigwizard\weightedgradebook::set_course_aggregation
     * @covers \gradereport_gradeconfigwizard\weightedgradebook::create_category
     * @covers \gradereport_gradeconfigwizard\weightedgradebook::create_subcategory
     * @covers \gradereport_gradeconfigwizard\weightedgradebook::move_item
     */
    public function test_process(): void {
        $assigment1 = grade_item::fetch(['itemname' => 'Test assign 1']);
        $assigment2 = grade_item::fetch(['itemname' => 'Test assign 2']);
        $assigment3 = grade_item::fetch(['itemname' => 'Test assign 3']);
        $categories = [
            '0' => [
                'name' => 'pathway1',
                'subcategories' => [
                    '1' => [
                        'name' => 'cat1',
                        'weight' => '3',
                        'items' => [
                            $assigment1->id => [
                                'id' => $assigment1->id,
                                'weight' => '1',
                            ],
                            $assigment2->id => [
                                'id' => $assigment2->id,
                                'weight' => '2',
                            ],
                        ],
                    ],
                    '2' => [
                        'name' => 'cat2',
                        'weight' => '1',
                        'items' => [
                            $assigment3->id => [
                                'id' => $assigment3->id,
                                'weight' => '3',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $multiplevaluation = new weightedgradebook($this->course1->id);
        $data = $multiplevaluation->process($categories);
        $this->assertEquals(1, $data);
    }
}
