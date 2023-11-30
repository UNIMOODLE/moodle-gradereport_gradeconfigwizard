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

class multiplegradebook {

    private $courseid;

    public function __construct(int $courseid) {
        $this->courseid = $courseid;
    }

    public function process(array $categories): bool {
        $this->require_grade_libs();
        $this->set_course_aggregation();

        foreach ($categories as $category) {
            $categoryid = $this->create_category($category);

            if (isset($category['recitem'])) {
                $provisionalcategoryid = $this->create_provisional_category($categoryid);
                $this->move_grade_item_to_category($category['recitem']['recitemid'], $categoryid);
                $gc = new \grade_category(['id' => $provisionalcategoryid], true);
                $provgradeitem = $gc->get_grade_item();
                $this->set_cutoff_mark_calculation($categoryid, $provgradeitem->id, $category['recitem']);
                $categoryid = $provisionalcategoryid;
            }

            if (isset($category['items'])) {
                foreach ($category['items'] as $item) {
                    if (isset($item['recitem'])) {
                        $gicategoryid = $this->create_item_category($item['id'], $item['weight'], $categoryid);
                        $this->move_grade_item_to_category($item['id'], $gicategoryid);
                        $this->move_grade_item_to_category($item['recitem']['recitemid'], $gicategoryid);
                        $this->set_cutoff_mark_calculation($gicategoryid, $item['id'], $item['recitem']);
                    } else {
                        $this->move_grade_item_to_category($item['id'], $categoryid);
                        $this->change_item_weight($item['id'], $item['weight']);
                    }
                }
            }
        }

        return true;
    }

    private function set_course_aggregation(): void {
        $coursegradecat = new \grade_category(['depth' => 1, 'courseid' => $this->courseid, 'fullname' => '?'], true);
        $properties = [
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($coursegradecat, $properties);
        $coursegradecat->update();
    }

    private function create_category(array $category): int {
        $gc = new \grade_category(['courseid' => $this->courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => clean_param($category['name'], PARAM_TEXT),
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($gc, $properties);
        $id = $gc->insert();

        $gradeitem = $gc->load_grade_item();
        \grade_item::set_properties($gradeitem, ['weightoverride' => 1, 'aggregationcoef' => $category['weight']]);
        $gradeitem->update();

        return $id;
    }

    private function create_provisional_category(int $parentid): int {
        $gc = new \grade_category(['courseid' => $this->courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => clean_param(get_string('maincontent', 'gradereport_gradeconfigwizard'), PARAM_TEXT),
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($gc, $properties);
        $id = $gc->insert();

        $gc->set_parent($parentid);
        $gc->update();

        return $id;
    }

    private function set_cutoff_mark_calculation(int $categoryid, int $itemid, array $recitem): void {
        $gc = new \grade_category(['id' => $categoryid], true);
        $gi = $gc->load_grade_item();

        $gradeitem = new \grade_item(['id' => $itemid], true);
        if (empty($gradeitem->idnumber)) {
            $gradeitem->add_idnumber(\gradereport_gradeconfigwizard\formulamanager::generate_id_number($itemid));
            $gradeitem->update();
        }

        $recgi = new \grade_item(['id' => $recitem['recitemid']], true);
        if (empty($recgi->idnumber)) {
            $recgi->add_idnumber(\gradereport_gradeconfigwizard\formulamanager::generate_id_number($recitem['recitemid']));
            $recgi->update();
        }

        $gi->set_calculation(
            "=IF([[" . $gradeitem->idnumber . "]]>=" . $recitem['recitemgrade'] .
            ",[[" . $gradeitem->idnumber . "]],[[" . $recgi->idnumber . "]])"
        );
        $gi->update();
    }

    private function move_grade_item_to_category(int $gradeitemid, int $categoryid): void {
        $gi = new \grade_item(['id' => $gradeitemid], true);
        $gi->set_parent($categoryid);
        $gi->update();
    }

    private function change_item_weight(int $itemid, int $weight): void {
        $gi = new \grade_item(['id' => $itemid], true);
        \grade_item::set_properties($gi, ['weightoverride' => 1, 'aggregationcoef' => $weight]);
        $gi->update();
    }

    private function create_item_category(int $gradeitemid, int $weight, int $parentid): int {
        $gi = new \grade_item(['id' => $gradeitemid], true);
        $gc = new \grade_category(['courseid' => $this->courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => clean_param($gi->get_name(), PARAM_TEXT),
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
            'parent' => clean_param($parentid, PARAM_INT),
        ];
        \grade_category::set_properties($gc, $properties);
        $id = $gc->insert();

        $gradeitem = $gc->load_grade_item();
        \grade_item::set_properties($gradeitem, ['weightoverride' => 1, 'aggregationcoef' => $weight]);
        $gradeitem->update();

        return $id;
    }

    private function require_grade_libs() {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');
        require_once($CFG->libdir . '/grade/grade_category.php');
        require_once($CFG->libdir . '/grade/grade_item.php');
        require_once($CFG->libdir . '/grade/constants.php');
    }

}
