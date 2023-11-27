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

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'gradereport_gradeconfigwizard_get_course_grades' => array(
        'classname' => 'gradereport_gradeconfigwizard_external',
        'methodname' => 'get_course_grades',
        'description' => 'Get the given user courses final grades',
        'type' => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'gradereport_gradeconfigwizard_view_grade_report' => array(
        'classname' => 'gradereport_gradeconfigwizard_external',
        'methodname' => 'view_grade_report',
        'description' => 'Trigger the report view event',
        'type' => 'write',
        'capabilities' => 'gradereport/gradeconfigwizard:view',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    )
);
