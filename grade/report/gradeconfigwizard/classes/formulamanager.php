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

namespace gradereport_gradeconfigwizard;

class formulamanager {
    public static function generate_formula_moodle($items){
        $formula = "=" . self::check_operation($items[0]['operation']);
        $first = array_shift($items);
        if ($first['operation'] === "WEIGHTEDMEANGRADES"){
            $formula .= "([[" . $first['idnumber']  . "]]" . "*" . $first['weight'];
        }
        else {
            $formula .= "([[" . $first['idnumber'] . "]]";
        }
        foreach ($items as $item){
            if ($item['operation'] === "WEIGHTEDMEANGRADES"){
                $formula .= ",[[" . $item['idnumber'] . "]]" . "*" . $item['weight'];
            }
            else{
                $formula .= ",[[" . $item['idnumber'] . "]]";
            }
        }
        $formula .= ")";
        return $formula;
    }

    public static function obtain_idnumber($grade_item_id, $grade_items){
        $item = self::select_grade_item($grade_items, $grade_item_id);
        if (!empty($item->idnumber)) return $item->idnumber;
        $result = self::generate_id_number($grade_item_id);
        self::afegir_idnumber($item, $result);
        return $result;
    }

    public static function generate_id_number($grade_item_id){
        $grade_item = \grade_item::fetch(array('id'=>$grade_item_id));
        $normalized_name = self::normalize_string($grade_item->get_name($grade_item_id) . "_" . ($grade_item_id));
        // Check if the name is unique
        $is_unique = false;
        while(!$is_unique){
            $unique_item = \grade_item::fetch(array('idnumber'=>$normalized_name));
            if($unique_item == false){
                $is_unique = true;
            }else{
                $normalized_name = $normalized_name . "_1";
            }
        }
        return $normalized_name;
    }

    public static function remove_gradeitem($availablegradeitems, $gradeitemidtarget){
        $size = sizeof($availablegradeitems)-1;
        for ($i = 0; $i < $size; ++$i){
            if(!$availablegradeitems[$i]['iscategory'] and $availablegradeitems[$i]['id'] == $gradeitemidtarget){
                array_splice($availablegradeitems, $i, 1);
            }
        }
        return $availablegradeitems;
    }

    public static function preprocess_formula_xml($formulaxml, $all_grade_item){
        $item_nota = [
            'id' => '',
            'idnumber' => '',
            'displayname' => '',
            'weight' => '',
            'operation' => '',
        ];
        $array = xmlize($formulaxml);
        // operation name
        $op = key($array);
        $elements= $array[$op]['#'];
        $grade_items = [];

        for ($i = 0; $i < sizeof($elements['ITEM']); $i++){
            $items= $elements['ITEM'][$i];
            foreach ($items as $item){
                $item_nota['operation'] = $op;
                if($op === "WEIGHTEDMEANGRADES") $item_nota['weight'] = $item['WEIGHT'][0]['#'];
                $item_nota['id'] = $item['GRADEITEMID'][0]['#'];
                $item_nota['idnumber'] = \gradereport_gradeconfigwizard\formulamanager::obtain_idnumber($item['GRADEITEMID'][0]['#'],$all_grade_item);
                $item_nota['displayname'] = $item['DISPLAYNAME'][0]['#'];
                $grade_items[] = $item_nota;
            }
        }
        return $grade_items;
    }




    private static function select_grade_item($grade_items, $grade_item_id){
        foreach($grade_items as $item){
            if ($item->id == $grade_item_id) return $item;
        }
        return null;
    }
    private static function normalize_string($string){
        // Clean accents
        $clean_strig = iconv('UTF-8','ASCII//TRANSLIT',$string);
        // Replace multiple spaces with single underscore
        $clean_strig = preg_replace('/\s+/', '_', $clean_strig);
        // Make the string lowercase
        return strtolower($clean_strig);
    }

    private static function check_operation($operation){
        if ($operation === "HIGHEST") return "max";
        elseif ($operation === "LOWEST") return "min";
        elseif ($operation === "SUM") return "sum";
        else return "average";
    }

    private static function afegir_idnumber($item, $result){
        if (isset($item)) $item->add_idnumber($result);
    }
}
