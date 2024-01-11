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

$string['eventgradereportviewed'] = 'S\'ha visualitzat l\'informe de qualificació de l\'Assistent per configurar qualificacions';
$string['gradesgradeconfigwizard'] = 'Qualificacions de l\'Assistent per configurar qualificacions';
$string['pluginname'] = 'Assistent per configurar qualificacions';
$string['privacy:metadata'] = 'L\'informe de qualificació de l\'Assistent per configurar qualificacions només mostra dades emmagatzemades en altres ubicacions.';
$string['gradeconfigwizard:view'] = 'Visualitza l\'informe de l\'Assistent per configurar qualificacions';

// Assistent per configurar qualificacions.
$string['gradereportheading'] = 'Assistent per configurar qualificacions';
$string['gradereportmultipleeval'] = 'Accedeix a l\'editor d\'avaluacions ponderades';
$string['gradereportweighteval'] = 'Accedeix a l\'editor d\'itineraris d\'avaluació';
$string['checkbox'] = 'Revisa el llibre de qualificacions';
$string['savebutton'] = 'Desa els canvis';
$string['multevalbutton'] = 'Accedeix a l\'editor d\'avaluacions ponderades';
$string['weightevalbutton'] = 'Accedeix a l\'editor d\'itineraris d\'avaluació';
$string['tablename'] = 'Nom';
$string['tableweight'] = 'Ponderacions';
$string['tableactions'] = 'Accions';
$string['editbutton'] = 'Edita';
$string['cataddcategory'] = 'Afegeix una categoria';
$string['cataddgradeitem'] = 'Afegeix un element de qualificació';
$string['catdisable'] = 'Desactiva';
$string['courseaddcategory'] = 'Afegeix una categoria';
$string['coursedisable'] = 'Afegeix un element de qualificació';
$string['totaleditcalucation'] = 'Edita el càlcul';
$string['manualeditcalucation'] = 'Edita el càlcul';
$string['manualdisable'] = 'Desactiva';
$string['moddisable'] = 'Desactiva';

// Creador de fórmules.
$string['heading'] = 'Editor de fórmules millorat';
$string['selectitems'] = 'Elements seleccionables';
$string['aggregation'] = 'Agregació';
$string['selectaggremethod'] = 'Selecciona un mètode d\'agregació';
$string['meangrades'] = 'Mitjana de les qualificacions';
$string['weightmeangrades'] = 'Mitjana ponderada de les qualificacions';
$string['sumgrades'] = 'Suma de les qualificacions';
$string['highestgrade'] = 'Qualificació més alta';
$string['lowestgrade'] = 'Qualificació més baixa';
$string['defcalculation'] = 'Defineix el càlcul';
$string['colitem'] = 'Element';
$string['colweight'] = 'Ponderació';
$string['generateformula'] = 'Genera la fórmula';
$string['formula'] = 'Fórmula';
$string['saveformula'] = 'Desa la fórmula';
$string['cancelbutton3'] = 'Cancel·la';

// Avaluació múltiple.
$string['catcreation'] = 'CREACIÓ DE CATEGORIES';
$string['createbutton'] = 'Crea';
$string['multitablecategory'] = 'CATEGORIA';
$string['multitableweight1'] = 'PONDERACIÓ';
$string['multitablecutoffgrade1'] = 'NOTA DE TALL';
$string['multitablerecovery1'] = 'RECUPERACIÓ';
$string['multitableelements'] = 'ELEMENTS';
$string['multitableweight2'] = 'PONDERACIÓ';
$string['multitablecutoffgrade2'] = 'NOTA DE TALL';
$string['multitablerecovery2'] = 'RECUPERACIÓ';
$string['chooseelements'] = 'Tria elements';
$string['addbutton'] = 'Afegeix';
$string['cancelbutton'] = 'Cancel·la';
$string['savebutton'] = 'Desa i surt';
$string['addelement1'] = 'Afegeix un element';
$string['addelement2'] = 'Afegeix un element';
$string['addelement3'] = 'Afegeix un element';
$string['addelement4'] = 'Afegeix un element';
$string['addelement5'] = 'Afegeix un element';
$string['cancelbutton5'] = 'Cancel·la';

// Avaluació múltiple ponderada.
$string['wcatcreation'] = 'CREACIÓ D\'ITINERARIS';
$string['wcreatebutton'] = 'Crea';
$string['wmultitableitineraries'] = 'ITINERARIS';
$string['wmultitablecategories'] = 'CATEGORIES';
$string['wmultitableweight1'] = 'PONDERACIÓ';
$string['wmultitableelements'] = 'ELEMENTS';
$string['wmultitableweight2'] = 'PONDERACIÓ';
$string['savebutton2'] = 'Desa i surt';
$string['chooseelements2'] = 'Tria elements';
$string['addbutton2'] = 'Afegeix';
$string['cancelbutton2'] = 'Cancel·la';
$string['additinerary1'] = 'Afegeix';
$string['additinerary2'] = 'Afegeix';
$string['cancelbutton4'] = 'Cancel·la';
$string['maincontent'] = 'Contingut principal';

// Elements d'avaluació disponibles.
$string['cattotal'] = 'Total';

