<?php

namespace gradereport_gradeconfigwizard;

class weightedgradebook {

    private int $courseid;

    public function __construct(int $courseid) {
        $this->courseid = $courseid;
    }

    public function process(array $categories): bool {
        $this->require_grade_libs();
        $this->set_course_aggregation();

        foreach ($categories as $category) {
            $categoryid = $this->create_category($category);
            if (isset($category['subcategories'])) {
                foreach ($category['subcategories'] as $subcategory) {
                    $subcategoryid = $this->create_subcategory($subcategory, $categoryid);
                    if (isset($subcategory['items'])) {
                        foreach ($subcategory['items'] as $item) {
                            $this->move_item($item, $subcategoryid);
                        }
                    }
                }
            }
        }

        return true;
    }

    private function set_course_aggregation(): void {
        $coursegradecat = new \grade_category(['depth' => 1, 'courseid' => $this->courseid, 'fullname' => '?'], true);
        $properties = [
            'aggregation' => GRADE_AGGREGATE_MAX,
        ];
        \grade_category::set_properties($coursegradecat, $properties);
        $coursegradecat->update();
    }

    private function create_category(array $category): int {
        $gc = new \grade_category(['courseid' => $this->courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => $category['name'],
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($gc, $properties);
        return $gc->insert();
    }

    private function create_subcategory(array $subcategory, int $parentid): int {
        $gc = new \grade_category(['courseid' => $this->courseid], false);
        $gc->apply_default_settings();
        $gc->apply_forced_settings();
        $properties = [
            'fullname' => $subcategory['name'],
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
        ];
        \grade_category::set_properties($gc, $properties);
        $id = $gc->insert();
        $gc->set_parent($parentid);

        $gradeitem = $gc->load_grade_item();
        \grade_item::set_properties($gradeitem, ['weightoverride' => 1, 'aggregationcoef' => $subcategory['weight']]);
        $gradeitem->update();

        return $id;
    }

    private function move_item(array $item, int $parentid): void {
        $gi = new \grade_item(['id' => $item['id']], true);
        $gi->set_parent($parentid);
        $parameters = ['aggregationcoef' => $item['weight']];
        \grade_item::set_properties($gi, $parameters);
        $gi->update();
    }

    private function require_grade_libs() {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');
        require_once($CFG->libdir . '/grade/grade_category.php');
        require_once($CFG->libdir . '/grade/grade_item.php');
        require_once($CFG->libdir . '/grade/constants.php');
    }

}