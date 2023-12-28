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

use core_grades\output\general_action_bar;

defined('MOODLE_INTERNAL') || die();

require_once('grade/report/gradeconfigwizard/classes/gradebook_action_bar_renderer_multiplevaluation.php');

class gradeconfigwizard_gradebook_action_bar_renderer_multiplevaluation_test extends \advanced_testcase {

    /**
     * Set up for every test
     */
    public function setUp(): void {
        $this->resetAfterTest(true);
        $this->course1 = $this->getDataGenerator()->create_course();
    }

    /**
     * Test get_template.
     */
    public function test_get_template() {
        $context = context_course::instance($this->course1->id);
        $actionbarformula = new \gradebook_action_bar_renderer_multiplevaluation($context);
        $template = $actionbarformula->get_template();
        $result = 'core_grades/gradebook_setup_action_bar';
        $this->assertEquals($result, $template);
    }

    /**
     * Test export_for_template.
     */
    public function test_export_for_template() {
        global $PAGE;
        $output = new renderer_base($PAGE, "target");
        $context = context_course::instance($this->course1->id);
        $generalnavselector = new general_action_bar($context,
            new moodle_url('grade/report/gradeconfigwizard/index.php',
                ['id' => $this->course1->id]), 'report', 'gradereport_gradeconfigwizard');
        $data = $generalnavselector->export_for_template($output);
        $data['selectedoption'] = get_string('gradereportmultipleeval', 'gradereport_gradeconfigwizard');
        $this->assertNotEmpty($data, 'navselector not created');
    }

}
