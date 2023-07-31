<?php

namespace gradereport_gradeconfigwizard;

use moodle_url;

require_once(__DIR__ . '/../../../../config.php');

class gradebookcreations{
    private $courseid;                  // Current course id
    private $relativepaths;             // Relative paths of the categories to create
    private $randomnames_dictionary;    // Dictionary used to relate the random names with the category id and name
    private $newgradeitems;             // Grade items info to create
    private $gradeitemparent;           // Grade item parents

    public function __construct($courseid, $relativepaths, $randomnames_dictionary, $newgradeitems, $gradeitemparent){
        $this->courseid = $courseid;
        $this->relativepaths = $relativepaths;
        $this->randomnames_dictionary = $randomnames_dictionary;
        $this->newgradeitems = $newgradeitems;
        $this->gradeitemparent = $gradeitemparent;
    }

    /**
     * Main function in charge of the gradebook creation process (categories and grade items)
     *s
     * @return void
     */
    public function process(){
        // In case there are new categories to create
        if($this->relativepaths != null) $this->create_categories();
        // In case there are new grade items to create
        if($this->newgradeitems != null) $this->create_grade_items();
        // Notify the user that the gradebook has been configured correctly
        $redirecturl = new moodle_url('/grade/report/gradeconfigwizard/index.php', array('id' => $this->courseid));
        redirect($redirecturl, 'Gradebook configurado correctamente', null, \core\output\notification::NOTIFY_SUCCESS);
    }


    /**
     * Function in charge of the categories creation process
     *
     * @return void
     */
    private function create_categories(){
        // Init dictionary names
        $new_dictionary_names = [];
        foreach ($this->randomnames_dictionary as $key => $value) {
            $new_dictionary_names[$key]['categoryid'] = -1;
            $new_dictionary_names[$key]['name'] = $value;
        }
        $this->randomnames_dictionary = $new_dictionary_names;

        // Make the categories creation process by depth
        for($i = 1; $i <= $this->get_max_depth(); $i++){
            foreach ($this->relativepaths as $items) {
                $parts = explode('/', $items);
                $depth = count($parts) - 1;
                // Parts:
                // 0: id of the already created root category
                // (n-1): name of th enew category ...

                if($depth != $i) continue; // In case the category is not in the current depth order

                if(count($parts) == 2){
                    $new_category_id = $this->create_subcategory(
                        $parts[0],
                        $this->randomnames_dictionary[$parts[count($parts)-1]]['name']);

                    $this->randomnames_dictionary[$parts[count($parts)-1]]['categoryid'] = $new_category_id;
                }else{
                    // Case when the category parent is a subcategory
                    $new_category_id = $this->create_subcategory(
                        $this->randomnames_dictionary[$parts[count($parts)-2]]['categoryid'],
                        $this->randomnames_dictionary[$parts[count($parts)-1]]['name']);
                    $this->randomnames_dictionary[$parts[count($parts)-1]]['categoryid'] = $new_category_id;
                }

            }
        }
    }


    /**
     * Function in charge of the grade items creation process
     *
     * @return void
     */
    private function create_grade_items(){

        foreach($this->gradeitemparent as $key => $value){
            // Check if the category is already created
            $parts = explode('/', $value);

            if(count($parts) == 2){
                // Case when the category parent is already created
                $this->create_grade_item($parts[1], $this->newgradeitems[$key]);
            }else{
                // Otherwise
                $this->create_grade_item($this->randomnames_dictionary[$parts[0]]['categoryid'], $this->newgradeitems[$key]);
            }
        }
    }

    /**
     * Function in charge of the grade item creation process
     *
     * @param  mixed $categoryid
     * @param  mixed $name
     * @return void
     */
    private function create_grade_item($categoryid, $name){
        $grade_item = new \grade_item(array(
            'courseid' => $this->courseid,
            'categoryid' => $categoryid,
        ), false);

        $grade_item->itemtype = 'manual';
        $grade_item->itemname = $name;
        $grade_item->insert();              // Add the grade item to the gradebook
    }


    /**
     * Function in charge of the subcategory creation process
     *
     * @param  mixed $parentid
     * @param  mixed $name
     * @return void
     */
    private function create_subcategory($parentid, $name) {
        $gc = new \grade_category(['courseid' => $this->courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => $name,
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($gc, $properties);
        $id = $gc->insert();
        $gc->set_parent($parentid);

        $gradeitem = $gc->load_grade_item();
        \grade_item::set_properties($gradeitem, ['weightoverride' => 1]);
        $gradeitem->update();
        return $id;
    }


    /**
     * Function in charge of getting the max depth of the categories to create
     *
     * @return void
     */
    private function get_max_depth(){
        $max_depth = 0;
        foreach ($this->relativepaths as $items) {
            $parts = explode('/', $items);
            if(count($parts) - 1 > $max_depth){
                $max_depth = count($parts) - 1;
            }
        }
        return $max_depth;
    }
}
