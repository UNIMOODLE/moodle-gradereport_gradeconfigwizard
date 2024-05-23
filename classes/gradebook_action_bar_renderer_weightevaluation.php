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

use core_grades\output\general_action_bar;
use core_grades\output\gradebook_setup_action_bar;

/**
 * Class gradebook_action_bar_renderer_weightevaluation
 *
 * This class extends the gradebook_setup_action_bar class and provides methods for rendering
 * the action bar and exporting data for the mustache template used in the Weighted Evaluation
 * tool within the gradebook setup.
 */
class gradebook_action_bar_renderer_weightevaluation extends gradebook_setup_action_bar {

    /**
     * Returns the template for the action bar.
     *
     * @return string The template path.
     */
    public function get_template(): string {
        return 'core_grades/gradebook_setup_action_bar';
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output renderer to be used to render the action bar elements.
     * @return array The data array for the mustache template.
     */
    public function export_for_template(\renderer_base $output): array {
        global $CFG;

        if ($this->context->contextlevel !== CONTEXT_COURSE) {
            return [];
        }
        $courseid = $this->context->instanceid;
        // Get the data used to output the general navigation selector.
        $generalnavselector = new general_action_bar(
            $this->context,
            new \moodle_url(
                'grade/report/gradeconfigwizard/index.php',
                ['id' => $courseid]
            ),
            'report',
            'gradereport_gradeconfigwizard'
        );
        $data = $generalnavselector->export_for_template($output);
        $data['selectedoption'] = get_string('gradereportweighteval', 'gradereport_gradeconfigwizard');
        return $data;
    }
}
