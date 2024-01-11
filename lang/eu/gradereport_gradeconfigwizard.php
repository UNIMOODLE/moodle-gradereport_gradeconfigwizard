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

$string['eventgradereportviewed'] = 'Kalifikazioa konfiguratzeko morroia Kalifikazio-txostena ikusi da';
$string['gradesgradeconfigwizard'] = 'Kalifikazioak konfiguratzeko laguntzailearen kalifikazioak';
$string['pluginname'] = 'Konfiguratu kalifikazioen morroia';
$string['privacy:metadata'] = 'Grade Setup Wizard kalifikazio-txostenak beste kokapen batzuetan gordetako datuak soilik bistaratzen ditu.';
$string['gradeconfigwizard:view'] = 'Ikusi Konfiguratu kalifikazioen morroia txostena';

// Asistente para configurar calificaciones.
$string['gradereportheading'] = 'Konfiguratu kalifikazioen morroia';
$string['gradereportmultipleeval'] = 'Sar zaitez haztatutako ebaluazio-editorera';
$string['gradereportweighteval'] = 'Sar zaitez ebaluazio-ibilbidearen editorea';
$string['checkbox'] = 'Berrikusi kalifikazio-liburua';
$string['savebutton'] = 'Aldaketak gorde';
$string['multevalbutton'] = 'Sar zaitez haztatutako ebaluazio-editorera';
$string['weightevalbutton'] = 'Sar zaitez ebaluazio-ibilbidearen editorea';
$string['tablename'] = 'Izena';
$string['tableweight'] = 'Ponderazioa';
$string['tableactions'] = 'Ekintzak';
$string['editbutton'] = 'Editatu';
$string['cataddcategory'] = 'Gehitu kategoria bat';
$string['cataddgradeitem'] = 'Gehitu balorazio-elementu bat';
$string['catdisable'] = 'Desaktibatu';
$string['courseaddcategory'] = 'Gehitu kategoria bat';
$string['coursedisable'] = 'Gehitu balorazio-elementu bat';
$string['totaleditcalucation'] = 'Editatu kalkulua';
$string['manualeditcalucation'] = 'Editatu kalkulua';
$string['manualdisable'] = 'Desaktibatu';
$string['moddisable'] = 'Desaktibatu';

// Creador de fórmulas.
$string['heading'] = 'Formula editorea hobetua';
$string['selectitems'] = 'Aukera daitezkeen elementuak';
$string['aggregation'] = 'Agregazioa';
$string['selectaggremethod'] = 'Hautatu agregazio-metodo bat';
$string['meangrades'] = 'Notaren batez bestekoa';
$string['weightmeangrades'] = 'Puntuazioen batez besteko haztatua';
$string['sumgrades'] = 'Noten batura';
$string['highestgrade'] = 'Balorazio altuena';
$string['lowestgrade'] = 'Balorazio baxuena';
$string['defcalculation'] = 'Definitu kalkulua';
$string['colitem'] = 'Elementua';
$string['colweight'] = 'Pisatzea';
$string['generateformula'] = 'Sortu formula';
$string['formula'] = 'Formula';
$string['saveformula'] = 'Gorde formula';
$string['cancelbutton3'] = 'Utzi';

// Evaluación múltiple.
$string['catcreation'] = 'KATEGORIAK SORTZEA';
$string['createbutton'] = 'Sortu';
$string['multitablecategory'] = 'KATEGORIA';
$string['multitableweight1'] = 'PISATZEA';
$string['multitablecutoffgrade1'] = 'EBATZEKO MARKA';
$string['multitablerecovery1'] = 'BERRESKURATZEA';
$string['multitableelements'] = 'ELEMENTUAK';
$string['multitableweight2'] = 'PISATZEA';
$string['multitablecutoffgrade2'] = 'EBATZEKO MARKA';
$string['multitablerecovery2'] = 'BERRESKURATZEA';
$string['chooseelements'] = 'Aukeratu elementuak';
$string['addbutton'] = 'Gehitu';
$string['cancelbutton'] = 'Utzi';
$string['savebutton'] = 'Gorde eta irten';
$string['addelement1'] = 'Gehitu elementu bat';
$string['addelement2'] = 'Gehitu elementu bat';
$string['addelement3'] = 'Gehitu elementu bat';
$string['addelement4'] = 'Gehitu elementu bat';
$string['addelement5'] = 'Gehitu elementu bat';
$string['cancelbutton5'] = 'Utzi';

// Evaluación múltiple ponderada.
$string['wcatcreation'] = 'IBILBIDEAK SORTZEA';
$string['wcreatebutton'] = 'Sortu';
$string['wmultitableitineraries'] = 'IBILBIDEAK';
$string['wmultitablecategories'] = 'KATEGORIAK';
$string['wmultitableweight1'] = 'PISATZEA';
$string['wmultitableelements'] = 'ELEMENTUAK';
$string['wmultitableweight2'] = 'PISATZEA';
$string['savebutton2'] = 'Gorde eta irten';
$string['chooseelements2'] = 'Aukeratu elementuak';
$string['addbutton2'] = 'Gehitu';
$string['cancelbutton2'] = 'Utzi';
$string['additinerary1'] = 'Gehitu';
$string['additinerary2'] = 'Gehitu';
$string['cancelbutton4'] = 'Utzi';
$string['maincontent'] = 'Eduki nagusia';

// Elementos de evaluación disponibles.
$string['cattotal'] = 'Guztira';
