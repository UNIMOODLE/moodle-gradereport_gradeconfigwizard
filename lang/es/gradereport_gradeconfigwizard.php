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

$string['eventgradereportviewed'] = 'Informe de calificación del Asistente para configurar calificaciones visto';
$string['gradesgradeconfigwizard'] = 'Calificaciones del Asistente para configurar calificaciones';
$string['pluginname'] = 'Asistente para configurar calificaciones';
$string['privacy:metadata'] = 'El informe de calificación del Asistente para configurar calificaciones sólo muestra datos almacenados en otras ubicaciones.';
$string['gradeconfigwizard:view'] = 'Ver el informe del Asistente para configurar calificaciones';

// Asistente para configurar calificaciones.
$string['gradereportheading'] = 'Asistente para configurar calificaciones';
$string['gradereportmultipleeval'] = 'Acceder al editor de múltiples evaluaciones';
$string['gradereportweighteval'] = 'Acceder al editor de itinerarios de evaluación';
$string['checkbox'] = 'Revisar el libro de calificaciones';
$string['savebutton'] = 'Guardar cambios';
$string['multevalbutton'] = 'Acceder al editor de múltiples evaluaciones';
$string['weightevalbutton'] = 'Acceder al editor de itinerarios de evaluación';
$string['tablename'] = 'Nombre';
$string['tableweight'] = 'Ponderaciones';
$string['tableactions'] = 'Acciones';
$string['editbutton'] = 'Editar';
$string['cataddcategory'] = 'Añadir una categoría';
$string['cataddgradeitem'] = 'Añadir un elemento de calificación';
$string['catdisable'] = 'Desactivar';
$string['courseaddcategory'] = 'Añadir una categoría';
$string['coursedisable'] = 'Añadir un elemento de calificación';
$string['totaleditcalucation'] = 'Editar el cálculo';
$string['manualeditcalucation'] = 'Editar el cálculo';
$string['manualdisable'] = 'Desactivar';
$string['moddisable'] = 'Desactivar';

// Creador de fórmulas.
$string['heading'] = 'Editor de fórmulas mejorado';
$string['selectitems'] = 'Elementos seleccionables';
$string['aggregation'] = 'Agregación';
$string['selectaggremethod'] = 'Seleccionar un método de agregación';
$string['meangrades'] = 'Media de las calificaciones';
$string['weightmeangrades'] = 'Media ponderada de las calificaciones';
$string['sumgrades'] = 'Suma de las calificaciones';
$string['highestgrade'] = 'Calificación más alta';
$string['lowestgrade'] = 'Calificación más baja';
$string['defcalculation'] = 'Definir el cálculo';
$string['colitem'] = 'Elemento';
$string['colweight'] = 'Ponderación';
$string['generateformula'] = 'Generar la fórmula';
$string['formula'] = 'Fórmula';
$string['saveformula'] = 'Guardar la fórmula';
$string['cancelbutton3'] = 'Cancelar';

// Evaluación múltiple.
$string['catcreation'] = 'CREACIÓN DE CATEGORÍAS';
$string['createbutton'] = 'Crear';
$string['multitablecategory'] = 'CATEGORÍA';
$string['multitableweight1'] = 'PONDERACIÓN';
$string['multitablecutoffgrade1'] = 'NOTA DE CORTE';
$string['multitablerecovery1'] = 'RECUPERACIÓN';
$string['multitableelements'] = 'ELEMENTOS';
$string['multitableweight2'] = 'PONDERACIÓN';
$string['multitablecutoffgrade2'] = 'NOTA DE CORTE';
$string['multitablerecovery2'] = 'RECUPERACIÓN';
$string['chooseelements'] = 'Elegir elementos';
$string['addbutton'] = 'Añadir';
$string['cancelbutton'] = 'Cancelar';
$string['savebutton'] = 'Guardar y salir';
$string['addelement1'] = 'Añadir un elemento';
$string['addelement2'] = 'Añadir un elemento';
$string['addelement3'] = 'Añadir un elemento';
$string['addelement4'] = 'Añadir un elemento';
$string['addelement5'] = 'Añadir un elemento';
$string['cancelbutton5'] = 'Cancelar';

// Evaluación múltiple ponderada.
$string['wcatcreation'] = 'CREACIÓN DE ITINERARIOS';
$string['wcreatebutton'] = 'Crear';
$string['wmultitableitineraries'] = 'ITINERARIOS';
$string['wmultitablecategories'] = 'CATEGORÍAS';
$string['wmultitableweight1'] = 'PONDERACIÓN';
$string['wmultitableelements'] = 'ELEMENTOS';
$string['wmultitableweight2'] = 'PONDERACIÓN';
$string['savebutton2'] = 'Guardar y salir';
$string['chooseelements2'] = 'Elegir elementos';
$string['addbutton2'] = 'Añadir';
$string['cancelbutton2'] = 'Cancelar';
$string['additinerary1'] = 'Añadir';
$string['additinerary2'] = 'Añadir';
$string['cancelbutton4'] = 'Cancelar';
$string['maincontent'] = 'Contenido principal';

// Elementos de evaluación disponibles.
$string['cattotal'] = 'Total';

