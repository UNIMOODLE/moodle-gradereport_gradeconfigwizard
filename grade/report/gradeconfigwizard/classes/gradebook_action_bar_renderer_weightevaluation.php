<?php

use core_grades\output\general_action_bar;
use core_grades\output\gradebook_setup_action_bar;

class gradebook_action_bar_renderer_weightevaluation extends gradebook_setup_action_bar {

    /**
     * Returns the template for the action bar.
     *
     * @return string
     */
    public function get_template(): string {
        return 'core_grades/gradebook_setup_action_bar';
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        global $CFG;

        if ($this->context->contextlevel !== CONTEXT_COURSE) {
            return [];
        }
        $courseid = $this->context->instanceid;
        // Get the data used to output the general navigation selector.
        $generalnavselector = new general_action_bar($this->context,
            new moodle_url('grade/report/gradeconfigwizard/index.php', ['id' => $courseid]), 'report', 'gradereport_gradeconfigwizard');
        $data = $generalnavselector->export_for_template($output);
        $data['selectedoption'] = get_string('gradereportweighteval', 'gradereport_gradeconfigwizard');
        return $data;
    }

}