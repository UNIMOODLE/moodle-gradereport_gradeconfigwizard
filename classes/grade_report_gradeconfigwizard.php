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

defined('MOODLE_INTERNAL') || die();

use context_course;
use context_user;
use flexible_table;
use grade_grade;
use grade_item;
use grade_report;
use html_table;
use html_writer;
use moodle_url;

require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->libdir . '/tablelib.php');

/**
 * Class providing an API for the gradeconfigwizard report building and displaying.
 * @uses grade_report
 * @package gradereport_gradeconfigwizard
 */
class grade_report_gradeconfigwizard extends grade_report {

    /**
     * The user.
     * @var object $user
     */
    public $user;

    /**
     * The user's courses
     * @var array $courses
     */
    public $courses;

    /**
     * A flexitable to hold the data.
     * @var object $table
     */
    public $table;

    /**
     * Show student ranks within each course.
     * @var array $showrank
     */
    public $showrank;

    /**
     * show course/category totals if they contain hidden items
     * @var array $showtotalsifcontainhidden
     */
    public $showtotalsifcontainhidden;

    /**
     * An array of course ids that the user is a student in.
     * @var array $studentcourseids
     */
    public $studentcourseids;

    /**
     * An array of courses that the user is a teacher in.
     * @var array $teachercourses
     */
    public $teachercourses;

    /**
     * Constructor. Sets local copies of user preferences and initialises grade_tree.
     * @param int $userid
     * @param object $gpr grade plugin return tracking object
     * @param string $context
     */
    public function __construct($userid, $gpr, $context) {
        global $CFG, $COURSE, $DB;
        parent::__construct($COURSE->id, $gpr, $context);

        // Get the user (for full name).
        $this->user = $DB->get_record('user', ['id' => $userid]);

        // Load the user's courses.
        $this->courses = enrol_get_users_courses($this->user->id, false, 'id, shortname, showgrades');

        $this->showrank = [];
        $this->showrank['any'] = false;

        $this->showtotalsifcontainhidden = [];

        $this->studentcourseids = [];
        $this->teachercourses = [];
        $roleids = explode(',', get_config('moodle', 'gradebookroles'));

        if ($this->courses) {
            foreach ($this->courses as $course) {
                $this->showrank[$course->id] = grade_get_setting(
                    $course->id,
                    'report_gradeconfigwizard_showrank',
                    !empty($CFG->grade_report_gradeconfigwizard_showrank)
                );
                if ($this->showrank[$course->id]) {
                    $this->showrank['any'] = true;
                }

                $this->showtotalsifcontainhidden[$course->id] = grade_get_setting(
                    $course->id,
                    'report_gradeconfigwizard_showtotalsifcontainhidden',
                    $CFG->grade_report_gradeconfigwizard_showtotalsifcontainhidden
                );

                $coursecontext = context_course::instance($course->id);

                foreach ($roleids as $roleid) {
                    if (user_has_role_assignment($userid, $roleid, $coursecontext->id)) {
                        $this->studentcourseids[$course->id] = $course->id;
                        // We only need to check if one of the roleids has been assigned.
                        break;
                    }
                }

                if (has_capability('moodle/grade:viewall', $coursecontext, $userid)) {
                    $this->teachercourses[$course->id] = $course;
                }
            }
        }

        // Base url for sorting by first/last name.
        $this->baseurl = $CFG->wwwroot . '/grade/gradeconfigwizard/index.phppp?id=' . $userid;
        $this->pbarurl = $this->baseurl;
        // Set up link to preferences page.
        $courseid = SITEID;
        $this->preferences_page = $CFG->wwwroot . '/grade/report/grader/preferencesss.php?id=' . $courseid;

        $this->setup_table();
    }

    /**
     * Prepares the headers and attributes of the flexitable.
     */
    public function setup_table() {
        /*
         * Table has 3 columns
         *| course  | final grade | rank (optional) |
         */

        // Setting up table headers.
        if ($this->showrank['any']) {
            $tablecolumns = ['coursename', 'grade', 'rank'];
            $tableheaders = [
                $this->get_lang_string('coursename', 'grades'),
                $this->get_lang_string('gradenoun'),
                $this->get_lang_string('rank', 'grades'),
            ];
        } else {
            $tablecolumns = ['coursename', 'grade'];
            $tableheaders = [
                $this->get_lang_string('coursename', 'grades'),
                $this->get_lang_string('gradenoun'),
            ];
        }
        $this->table = new flexible_table('grade-report-gradeconfigwizard-' . $this->user->id);

        $this->table->define_columns($tablecolumns);
        $this->table->define_headers($tableheaders);
        $this->table->define_baseurl($this->baseurl);

        $this->table->set_attribute('cellspacing', '0');
        $this->table->set_attribute('id', 'gradeconfigwizard-grade');
        $this->table->set_attribute('class', 'boxaligncenter generaltable');

        $this->table->setup();
    }

    /**
     * Set up the courses grades data for the report.
     *
     * @param bool $studentcoursesonly Only show courses that the user is a student of.
     * @return array of course grades information
     */
    public function setup_courses_data($studentcoursesonly) {
        global $USER, $DB;

        $coursesdata = [];
        $numusers = $this->get_numusers(false);

        foreach ($this->courses as $course) {
            if (!$course->showgrades) {
                continue;
            }

            // If we are only showing student courses and this course isn't part of the group, then move on.
            if ($studentcoursesonly && !isset($this->studentcourseids[$course->id])) {
                continue;
            }

            $coursecontext = context_course::instance($course->id);

            if (!$course->visible && !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                // The course is hidden and the user isn't allowed to see it.
                continue;
            }

            if (
                !has_capability('moodle/user:viewuseractivitiesreport', context_user::instance($this->user->id)) &&
                ((!has_capability('moodle/grade:view', $coursecontext) || $this->user->id != $USER->id) &&
                    !has_capability('moodle/grade:viewall', $coursecontext))
            ) {
                continue;
            }

            $coursesdata[$course->id]['course'] = $course;
            $coursesdata[$course->id]['context'] = $coursecontext;

            $canviewhidden = has_capability('moodle/grade:viewhidden', $coursecontext);

            // Get course grade_item.
            $courseitem = grade_item::fetch_course_item($course->id);

            // Get the stored grade.
            $coursegrade = new grade_grade(['itemid' => $courseitem->id, 'userid' => $this->user->id]);
            $coursegrade->grade_item = &$courseitem;
            $finalgrade = $coursegrade->finalgrade;

            if (!$canviewhidden && !is_null($finalgrade)) {
                if ($coursegrade->is_hidden()) {
                    $finalgrade = null;
                } else {
                    $adjustedgrade = $this->blank_hidden_total_and_adjust_bounds(
                        $course->id,
                        $courseitem,
                        $finalgrade
                    );

                    // We temporarily adjust the view of this grade item - because the min and
                    // max are affected by the hidden values in the aggregation.
                    $finalgrade = $adjustedgrade['grade'];
                    $courseitem->grademax = $adjustedgrade['grademax'];
                    $courseitem->grademin = $adjustedgrade['grademin'];
                }
            } else {
                // We must use the specific max/min because it can be different for
                // each grade_grade when items are excluded from sum of grades.
                if (!is_null($finalgrade)) {
                    $courseitem->grademin = $coursegrade->get_grade_min();
                    $courseitem->grademax = $coursegrade->get_grade_max();
                }
            }

            $coursesdata[$course->id]['finalgrade'] = $finalgrade;
            $coursesdata[$course->id]['courseitem'] = $courseitem;

            if ($this->showrank['any'] && $this->showrank[$course->id] && !is_null($finalgrade)) {
                // Find the number of users with a higher grade.
                // Please note this can not work if hidden grades involved :-( to be fixed in 2.0.
                $params = [$finalgrade, $courseitem->id];
                $sql = "SELECT COUNT(DISTINCT(userid))
                          FROM {grade_grades}
                         WHERE finalgrade IS NOT NULL AND finalgrade > ?
                               AND itemid = ?";
                $rank = $DB->count_records_sql($sql, $params) + 1;

                $coursesdata[$course->id]['rank'] = $rank;
                $coursesdata[$course->id]['numusers'] = $numusers;
            }
        }
        return $coursesdata;
    }

    /**
     * Interface needed by grade_report.
     *
     * @param mixed $data Data to process.
     * @return void
     */
    public function process_data($data) {
        return;
    }


    /**
     * Interface needed by grade_report.
     *
     * @param mixed $target The target.
     * @param mixed $action The action to perform.
     * @return void
     */
    public function process_action($target, $action) {
        return;
    }

    /**
     * Check if the user can access the report.
     * @return bool true if the user can access the report
     * @since  Moodle 3.2
     */
    public static function check_access($systemcontext, $context, $personalcontext, $course, $userid) {
        global $USER;

        $access = false;
        if (has_capability('moodle/grade:viewall', $systemcontext)) {
            // Ok - can view all course grades.
            $access = true;
        } else if (has_capability('moodle/grade:viewall', $context)) {
            // Ok - can view any grades in context.
            $access = true;
        } else if ($userid == $USER->id && ((has_capability('moodle/grade:view', $context) && $course->showgrades)
            || $course->id == SITEID)) {
            // Ok - can view own course grades.
            $access = true;
        } else if (has_capability('moodle/grade:viewall', $personalcontext) && $course->showgrades) {
            // Ok - can view grades of this user - parent most probably.
            $access = true;
        } else if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext) && $course->showgrades) {
            // Ok - can view grades of this user - parent most probably.
            $access = true;
        }
        return $access;
    }

    /**
     * Trigger the grade_report_viewed event
     *
     * @param  int $courseid      course id
     * @param  int $userid        user id
     * @since Moodle 3.2
     */
    public static function viewed($context, $courseid, $userid) {
        $event = \gradereport_gradeconfigwizard\event\grade_report_viewed::create(
            [
                'context' => $context,
                'courseid' => $courseid,
                'relateduserid' => $userid,
            ]
        );
        $event->trigger();
    }
}
