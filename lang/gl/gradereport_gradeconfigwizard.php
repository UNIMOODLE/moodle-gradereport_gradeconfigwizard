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

$string['eventgradereportviewed'] = 'Visto o informe de cualificación do asistente de configuración de cualificación';
$string['gradesgradeconfigwizard'] = 'Cualificacións do asistente de configuración de cualificacións';
$string['pluginname'] = 'Asistente de configuración de notas';
$string['privacy:metadata'] = 'O informe de cualificación do Asistente de configuración de cualificación só mostra os datos almacenados noutras localizacións.';
$string['gradeconfigwizard:view'] = 'Consulta o informe do asistente de configuración de cualificacións';

// Asistente para configurar calificaciones.
$string['gradereportheading'] = 'Asistente de configuración de notas';
$string['gradereportmultipleeval'] = 'Acceda ao editor de avaliación múltiple';
$string['gradereportweighteval'] = 'Acceda ao editor de itinerarios de avaliación';
$string['checkbox'] = 'Revisa o libro de cualificacións';
$string['savebutton'] = 'Gardar cambios';
$string['multevalbutton'] = 'Acceda ao editor de avaliación múltiple';
$string['weightevalbutton'] = 'Acceda ao editor de itinerarios de avaliación';
$string['tablename'] = 'Nome';
$string['tableweight'] = 'Ponderacións';
$string['tableactions'] = 'Accións';
$string['editbutton'] = 'Editar';
$string['cataddcategory'] = 'Engade unha categoría';
$string['cataddgradeitem'] = 'Engade un elemento de valoración';
$string['catdisable'] = 'Desactivar';
$string['courseaddcategory'] = 'Engade unha categoría';
$string['coursedisable'] = 'Engade un elemento de valoración';
$string['totaleditcalucation'] = 'Editar cálculo';
$string['manualeditcalucation'] = 'Editar cálculo';
$string['manualdisable'] = 'Desactivar';
$string['moddisable'] = 'Desactivar';

// Creador de fórmulas.
$string['heading'] = 'Editor de fórmulas mellorado';
$string['selectitems'] = 'Elementos seleccionables';
$string['aggregation'] = 'Agregación';
$string['selectaggremethod'] = 'Seleccione un método de agregación';
$string['meangrades'] = 'Media das puntuacións';
$string['weightmeangrades'] = 'Media ponderada das puntuacións';
$string['sumgrades'] = 'Suma das puntuacións';
$string['highestgrade'] = 'Valoración máis alta';
$string['lowestgrade'] = 'Valoración máis baixa';
$string['defcalculation'] = 'Definir o cálculo';
$string['colitem'] = 'Elemento';
$string['colweight'] = 'Pesaxe';
$string['generateformula'] = 'Xera a fórmula';
$string['formula'] = 'Fórmula';
$string['saveformula'] = 'Garda a fórmula';
$string['cancelbutton3'] = 'Cancelar';

// Evaluación múltiple.
$string['catcreation'] = 'CREACIÓN DE CATEGORÍAS';
$string['createbutton'] = 'Crear';
$string['multitablecategory'] = 'CATEGORÍA';
$string['multitableweight1'] = 'PESAXE';
$string['multitablecutoffgrade1'] = 'MARCA DE CORTE';
$string['multitablerecovery1'] = 'RECUPERACIÓN';
$string['multitableelements'] = 'ELEMENTOS';
$string['multitableweight2'] = 'PESAXE';
$string['multitablecutoffgrade2'] = 'MARCA DE CORTE';
$string['multitablerecovery2'] = 'RECUPERACIÓN';
$string['chooseelements'] = 'Escolle elementos';
$string['addbutton'] = 'Añadir';
$string['cancelbutton'] = 'Engadir';
$string['savebutton'] = 'Garda e sae';
$string['addelement1'] = 'Engade un elemento';
$string['addelement2'] = 'Engade un elemento';
$string['addelement3'] = 'Engade un elemento';
$string['addelement4'] = 'Engade un elemento';
$string['addelement5'] = 'Engade un elemento';
$string['cancelbutton5'] = 'Cancelar';

// Evaluación múltiple ponderada.
$string['wcatcreation'] = 'CREACIÓN DE ITINERARIOS';
$string['wcreatebutton'] = 'Crear';
$string['wmultitableitineraries'] = 'ITINERARIOS';
$string['wmultitablecategories'] = 'CATEGORÍAS';
$string['wmultitableweight1'] = 'PESAXE';
$string['wmultitableelements'] = 'ELEMENTOS';
$string['wmultitableweight2'] = 'PESAXE';
$string['savebutton2'] = 'Garda e sae';
$string['chooseelements2'] = 'Escolle elementos';
$string['addbutton2'] = 'Engadir';
$string['cancelbutton2'] = 'Cancelar';
$string['additinerary1'] = 'Engadir';
$string['additinerary2'] = 'Engadir';
$string['cancelbutton4'] = 'Cancelar';
$string['maincontent'] = 'Contido principal';

// Elementos de evaluación disponibles.
$string['cattotal'] = 'Total';
