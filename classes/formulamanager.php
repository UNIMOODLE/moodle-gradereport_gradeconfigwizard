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

namespace gradereport_gradeconfigwizard;

class formulamanager {
    public static function generate_formula_moodle($items) {
        $formula = "=" . self::check_operation($items[0]['operation']);
        $first = array_shift($items);
        if ($first['operation'] === "WEIGHTEDMEANGRADES") {
            $formula .= "([[" . $first['idnumber']  . "]]" . "*" . $first['weight'];
        } else {
            $formula .= "([[" . $first['idnumber'] . "]]";
        }
        foreach ($items as $item) {
            if ($item['operation'] === "WEIGHTEDMEANGRADES") {
                $formula .= ",[[" . $item['idnumber'] . "]]" . "*" . $item['weight'];
            } else {
                $formula .= ",[[" . $item['idnumber'] . "]]";
            }
        }
        $formula .= ")";
        return $formula;
    }

    public static function obtain_idnumber($gradeitemid, $gradeitems) {
        $item = self::select_grade_item($gradeitems, $gradeitemid);
        if (!empty($item->idnumber)) {
            return $item->idnumber;
        }
        $result = self::generate_id_number($gradeitemid);
        self::afegir_idnumber($item, $result);
        return $result;
    }

    public static function generate_id_number($gradeitemid) {
        $gradeitem = \grade_item::fetch(array('id' => $gradeitemid));
        $normalizedname = self::normalize_string($gradeitem->get_name($gradeitemid) . "_" . ($gradeitemid));
        // Check if the name is unique.
        $isunique = false;
        while (!$isunique) {
            $uniqueitem = \grade_item::fetch(array('idnumber' => $normalizedname));
            if ($uniqueitem == false) {
                $isunique = true;
            } else {
                $normalizedname = $normalizedname . "_1";
            }
        }
        return $normalizedname;
    }

    public static function remove_gradeitem($availablegradeitems, $gradeitemidtarget) {
        $size = count($availablegradeitems) - 1;
        for ($i = 0; $i < $size; ++$i) {
            if (!$availablegradeitems[$i]['iscategory'] && $availablegradeitems[$i]['id'] == $gradeitemidtarget) {
                array_splice($availablegradeitems, $i, 1);
            }
        }
        return $availablegradeitems;
    }

    public static function preprocess_formula_xml($formulaxml, $allgradeitem) {
        $itemgrade = [
            'id' => '',
            'idnumber' => '',
            'displayname' => '',
            'weight' => '',
            'operation' => '',
        ];
        $array = xmlize($formulaxml);
        // Operation name.
        $op = key($array);
        $elements = $array[$op]['#'];
        $gradeitems = [];

        for ($i = 0; $i < count($elements['ITEM']); $i++) {
            $items = $elements['ITEM'][$i];
            foreach ($items as $item) {
                $itemgrade['operation'] = $op;
                if ($op === "WEIGHTEDMEANGRADES") {
                    $itemgrade['weight'] = $item['WEIGHT'][0]['#'];
                }
                $itemgrade['id'] = $item['GRADEITEMID'][0]['#'];
                $itemgrade['idnumber'] = self::obtain_idnumber($item['GRADEITEMID'][0]['#'], $allgradeitem);
                $itemgrade['displayname'] = $item['DISPLAYNAME'][0]['#'];
                $gradeitems[] = $itemgrade;
            }
        }
        return $gradeitems;
    }




    private static function select_grade_item($gradeitems, $gradeitemid) {
        foreach ($gradeitems as $item) {
            if ($item->id == $gradeitemid) {
                return $item;
            }
        }
        return null;
    }
    private static function normalize_string($string) {
        // Clean accents.
        $cleanstrig = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        // Replace multiple spaces with single underscore.
        $cleanstrig = preg_replace('/\s+/', '_', $cleanstrig);
        // Make the string lowercase.
        return strtolower($cleanstrig);
    }

    private static function check_operation($operation) {
        if ($operation === "HIGHEST") {
            return "max";
        } else if ($operation === "LOWEST") {
            return "min";
        } else if ($operation === "SUM") {
            return "sum";
        } else {
            return "average";
        }
    }

    private static function afegir_idnumber($item, $result) {
        if (isset($item)) {
            $item->add_idnumber($result);
        }
    }
}
