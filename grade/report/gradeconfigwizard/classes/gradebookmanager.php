<?php

namespace gradereport_gradeconfigwizard;

class gradebookmanager{

    public static function move_after(int $draggedgradeitemid, int $targetidgradeitemid, int $courseid, bool $first = false) {
        $draggedgradeitem = \grade_item::fetch(['id' => $draggedgradeitemid]);
        $targetgradeitem = \grade_item::fetch(['id' => $targetidgradeitemid]);

        $eid       = self::generate_eid_dragndrop($draggedgradeitem);
        $moveafter = self::generate_eid_dragndrop($targetgradeitem);

        self::_move_after($eid, $moveafter, $courseid, $first);
    }

    // Ref: moodle/grade/edit/tree/index.php
    private static function _move_after($eid, $moveafter, $courseid, $first = false) {

        $gtree = new \grade_tree($courseid, false, false);

        if (!$element = $gtree->locate_element($eid)) {
            throw new \moodle_exception('invalidelementid', '');
        }
        $object = $element['object'];

        if(!$after_el = $gtree->locate_element($moveafter)) {
            throw new \moodle_exception('invalidelementid', '');
        }

        $after = $after_el['object'];
        $sortorder = $after->get_sortorder();

        if (!$first) {
            $parent = $after->get_parent_category();
            $object->set_parent($parent->id);
        } else {
            $object->set_parent($after->id);
        }

        $object->move_after_sortorder($sortorder);
    }

    private static function generate_eid_dragndrop($gradeitem) {
        if ($gradeitem->itemtype == 'manual' || $gradeitem->itemtype == 'mod'){
            return 'ig'.$gradeitem->id;
        }
        if ($gradeitem->itemtype == 'course'){
            return 'cg'.$gradeitem->iteminstance;
        }
        if ($gradeitem->itemtype == 'category'){
            return 'cg'.$gradeitem->iteminstance;
        }
    }

    public static function get_disabledcategory($courseid, $usecache = false) : \grade_item | bool {
        static $disabledgradecategoriescache = [];
        if ($usecache && isset($disabledgradecategoriescache[$courseid])) {
            return $disabledgradecategoriescache[$courseid];
        }
        $disabledgradecategory = \grade_category::fetch(['courseid' => $courseid, 'fullname' => '[[DISABLED]]']);
        $disabledgradeitem = \grade_item::fetch(['iteminstance' => $disabledgradecategory->id, 'itemtype' => 'category', 'courseid' => $courseid]);
        if($disabledgradeitem !== false){
            $disabledgradecategoriescache[$courseid] = $disabledgradeitem;
        }
        return $disabledgradeitem;
    }

    public static function create_disabledcategory($courseid) : \grade_item {
        $gc = new \grade_category(['courseid' => $courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => '[[DISABLED]]',
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($gc, $properties);
        $id = $gc->insert();

        $gradeitem = $gc->load_grade_item();
        \grade_item::set_properties($gradeitem, ['weightoverride' => 1, 'aggregationcoef' => 0]);
        $gradeitem->update();
        return $gradeitem;
    }



}
