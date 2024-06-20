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

use core\session\exception;

/**
 * Class formulamanager
 *
 * Provides methods for managing formulas in Moodle.
 */
class formulamanager {

    /**
     * Generates a Moodle formula based on the provided items.
     *
     * This method generates a formula string for Moodle based on the provided items.
     *
     * @param array $items An array of items to include in the formula.
     * @return string The generated Moodle formula string.
     * @throws Exception If the total weight is invalid.
     */
    public static function generate_formula_moodle(array $items): string {
        $formula = "=" . self::check_operation($items[0]['operation']);
        $first = array_shift($items);
        $totalweight = (int)$first['weight'];
        if ($first['operation'] === "WEIGHTEDMEANGRADES") {
            $formula .= "([[" . $first['idnumber']  . "]]" . "*" . (int)$first['weight'];
        } else {
            $formula .= "([[" . $first['idnumber'] . "]]";
        }
        foreach ($items as $item) {
            if ($item['operation'] === "WEIGHTEDMEANGRADES") {
                $formula .= ",[[" . $item['idnumber'] . "]]" . "*" . (int)$item['weight'];
                $totalweight += (int) $item['weight'];
            } else {
                $formula .= ",[[" . $item['idnumber'] . "]]";
            }
        }
        $formula .= ")";
        if ($first['operation'] === "WEIGHTEDMEANGRADES") {
            if ($totalweight >= 1) {
                $formula .= "/" . $totalweight;
            } else {
                throw new Exception("Invalid total weight. The sum of weights should be greater than zero");
            }
        }
        return $formula;
    }

    /**
     * Obtains the ID number for a grade item.
     *
     * This method obtains the ID number for the given grade item ID. If the ID number
     * is already available in the provided grade items, it returns it directly. Otherwise,
     * it generates a new ID number and adds it to the grade item before returning it.
     *
     * @param int $gradeitemid The ID of the grade item.
     * @param array $gradeitems An array of grade items.
     * @return string The ID number of the grade item.
     */
    public static function obtain_idnumber(int $gradeitemid, array $gradeitems): string {
        $item = self::select_grade_item($gradeitems, $gradeitemid);
        if (!empty($item->idnumber)) {
            return $item->idnumber;
        }
        $result = self::generate_id_number($gradeitemid);
        self::afegir_idnumber($item, $result);
        return $result;
    }

    /**
     * Generates a unique ID number for a grade item.
     *
     * This method generates a unique ID number for the given grade item ID. It fetches
     * the grade item using the provided ID, then normalizes its name and appends the ID
     * to it to ensure uniqueness. If the generated ID number is not unique, it appends
     * "_1", "_2", and so on until a unique ID number is found.
     *
     * @param int $gradeitemid The ID of the grade item.
     * @return string The generated unique ID number for the grade item.
     */
    public static function generate_id_number(int $gradeitemid): string {
        $gradeitem = \grade_item::fetch(['id' => $gradeitemid]);
        $normalizedname = self::normalize_string($gradeitem->get_name($gradeitemid) . "_" . ($gradeitemid));
        // Check if the name is unique.
        $isunique = false;
        while (!$isunique) {
            $uniqueitem = \grade_item::fetch(['idnumber' => $normalizedname]);
            if ($uniqueitem == false) {
                $isunique = true;
            } else {
                $normalizedname = $normalizedname . "_1";
            }
        }
        return $normalizedname;
    }

    /**
     * Removes a grade item from the list of available grade items.
     *
     * This method removes the grade item with the specified ID from the array of
     * available grade items. It iterates through the array and removes the item if
     * its ID matches the target ID and it's not a category.
     *
     * @param array $availablegradeitems An array of available grade items.
     * @param int $gradeitemidtarget The ID of the grade item to be removed.
     * @return array The updated array of available grade items after removal.
     */
    public static function remove_gradeitem(array $availablegradeitems, int $gradeitemidtarget): array {
        $size = count($availablegradeitems) - 1;
        for ($i = 0; $i < $size; ++$i) {
            if (!$availablegradeitems[$i]['iscategory'] && $availablegradeitems[$i]['id'] == $gradeitemidtarget) {
                array_splice($availablegradeitems, $i, 1);
            }
        }
        return $availablegradeitems;
    }

    /**
     * Preprocesses the XML data for formula generation.
     *
     * This method preprocesses the XML data representing a formula, extracting relevant
     * information such as item IDs, names, weights, and operations. It returns an array
     * of structured data representing each item in the formula.
     *
     * @param string $formulaxml The XML data representing the formula.
     * @param array $allgradeitem An array of all available grade items.
     * @return array An array of structured data representing each item in the formula.
     */
    public static function preprocess_formula_xml(string $formulaxml, array $allgradeitem): array {
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

    /**
     * Selects a grade item from the list by its ID.
     *
     * This method iterates through the list of grade items and returns the item
     * with the specified ID. If no item is found with the given ID, it returns null.
     *
     * @param array $gradeitems An array of grade items.
     * @param int $gradeitemid The ID of the grade item to select.
     * @return mixed|null The grade item object if found, otherwise null.
     */
    private static function select_grade_item(array $gradeitems, int $gradeitemid) {
        foreach ($gradeitems as $item) {
            if ($item->id == $gradeitemid) {
                return $item;
            }
        }
        return null;
    }


    /**
     * Normalizes a string by removing accents, spaces, and converting to lowercase.
     *
     * This method takes a string as input and performs the following operations:
     * 1. Removes accents from characters.
     * 2. Replaces multiple spaces with a single underscore.
     * 3. Converts the string to lowercase.
     * It then returns the normalized string.
     *
     * @param string $string The string to be normalized.
     * @return string The normalized string.
     */
    private static function normalize_string(string $string): string {
        // Clean accents.
        $cleanstrig = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        // Replace multiple spaces with single underscore.
        $cleanstrig = preg_replace('/\s+/', '_', $cleanstrig);
        // Make the string lowercase.
        return strtolower($cleanstrig);
    }

    /**
     * Checks and returns the corresponding operation for a given operation code.
     *
     * This method takes an operation code as input and returns the corresponding
     * operation name based on the code. If the code matches one of the predefined
     * operations ("HIGHEST", "LOWEST", "SUM", "WEIGHTEDMEANGRADES"), it returns
     * the corresponding operation name ("max", "min", "sum", "average"). Otherwise,
     * it returns "average" as the default operation.
     *
     * @param string $operation The operation code to check.
     * @return string The corresponding operation name.
     */
    public static function check_operation(string $operation): string {
        if ($operation === "HIGHEST") {
            return "max";
        } else if ($operation === "LOWEST") {
            return "min";
        } else if ($operation === "SUM" || $operation === "WEIGHTEDMEANGRADES") {
            return "sum";
        } else {
            return "average";
        }
    }

    /**
     * Adds an ID number to a grade item.
     *
     * This method adds the provided ID number to the given grade item. If the item
     * is not null, it calls the `add_idnumber` method on the item and passes the ID
     * number as a parameter.
     *
     * @param mixed $item The grade item object to which the ID number will be added.
     * @param string $result The ID number to add to the grade item.
     */
    private static function afegir_idnumber($item, $result): void {
        if (isset($item)) {
            $item->add_idnumber($result);
        }
    }
}
