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
require_once($CFG->libdir.'/tablelib.php');

/**
 * Class providing an API for the gradeconfigwizard report building and displaying.
 * @uses grade_report
 * @package gradereport_gradeconfigwizard
 */
class grade_report_gradeconfigwizard extends grade_report{

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
     */
    var $showtotalsifcontainhidden;

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
        $this->user = $DB->get_record('user', array('id' => $userid));

        // Load the user's courses.
        $this->courses = enrol_get_users_courses($this->user->id, false, 'id, shortname, showgrades');

        $this->showrank = array();
        $this->showrank['any'] = false;

        $this->showtotalsifcontainhidden = array();

        $this->studentcourseids = array();
        $this->teachercourses = array();
        $roleids = explode(',', get_config('moodle', 'gradebookroles'));

        if ($this->courses) {
            foreach ($this->courses as $course) {
                $this->showrank[$course->id] = grade_get_setting($course->id, 'report_gradeconfigwizard_showrank', !empty($CFG->grade_report_gradeconfigwizard_showrank));
                if ($this->showrank[$course->id]) {
                    $this->showrank['any'] = true;
                }

                $this->showtotalsifcontainhidden[$course->id] = grade_get_setting($course->id, 'report_gradeconfigwizard_showtotalsifcontainhidden', $CFG->grade_report_gradeconfigwizard_showtotalsifcontainhidden);

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


        // base url for sorting by first/last name
        $this->baseurl = $CFG->wwwroot.'/grade/gradeconfigwizard/index.phppp?id='.$userid;
        $this->pbarurl = $this->baseurl;
        // Set up link to preferences page
        $courseid = SITEID; //#DEBUG# remove
        $this->preferences_page = $CFG->wwwroot.'/grade/report/grader/preferencesss.php?id='.$courseid; //#DEBUG# remove

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

        // setting up table headers
        if ($this->showrank['any']) {
            $tablecolumns = array('coursename', 'grade', 'rank');
            $tableheaders = array($this->get_lang_string('coursename', 'grades'),
                                  $this->get_lang_string('gradenoun'),
                                  $this->get_lang_string('rank', 'grades'));
        } else {
            $tablecolumns = array('coursename', 'grade');
            $tableheaders = array($this->get_lang_string('coursename', 'grades'),
                                  $this->get_lang_string('gradenoun'));
        }
        $this->table = new flexible_table('grade-report-gradeconfigwizard-'.$this->user->id);

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

        $coursesdata = array();
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

            if (!has_capability('moodle/user:viewuseractivitiesreport', context_user::instance($this->user->id)) &&
                    ((!has_capability('moodle/grade:view', $coursecontext) || $this->user->id != $USER->id) &&
                    !has_capability('moodle/grade:viewall', $coursecontext))) {
                continue;
            }

            $coursesdata[$course->id]['course'] = $course;
            $coursesdata[$course->id]['context'] = $coursecontext;

            $canviewhidden = has_capability('moodle/grade:viewhidden', $coursecontext);

            // Get course grade_item.
            $courseitem = grade_item::fetch_course_item($course->id);

            // Get the stored grade.
            $coursegrade = new grade_grade(array('itemid' => $courseitem->id, 'userid' => $this->user->id));
            $coursegrade->grade_item =& $courseitem;
            $finalgrade = $coursegrade->finalgrade;

            if (!$canviewhidden and !is_null($finalgrade)) {
                if ($coursegrade->is_hidden()) {
                    $finalgrade = null;
                } else {
                    $adjustedgrade = $this->blank_hidden_total_and_adjust_bounds($course->id,
                                                                                 $courseitem,
                                                                                 $finalgrade);

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
                $params = array($finalgrade, $courseitem->id);
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
     * Fill the table for displaying.
     *
     * @param bool $activitylink If this report link to the activity report or the user report.
     * @param bool $studentcoursesonly Only show courses that the user is a student of.
     */
    public function fill_table($activitylink = false, $studentcoursesonly = false) {
        global $CFG, $DB, $OUTPUT, $USER;

        if ($studentcoursesonly && count($this->studentcourseids) == 0) {
            return false;
        }

        // Only show user's courses instead of all courses.
        if ($this->courses) {
            $coursesdata = $this->setup_courses_data($studentcoursesonly);

            // Check whether current user can view all grades of this user - parent most probably.
            $viewasuser = $this->course->showgrades && has_any_capability([
                'moodle/grade:viewall',
                'moodle/user:viewuseractivitiesreport',
            ], context_user::instance($this->user->id));

            foreach ($coursesdata as $coursedata) {

                $course = $coursedata['course'];
                $coursecontext = $coursedata['context'];
                $finalgrade = $coursedata['finalgrade'];
                $courseitem = $coursedata['courseitem'];

                $coursenamelink = format_string(get_course_display_name_for_list($course), true, ['context' => $coursecontext]);

                // Link to the course grade report pages (performing same capability checks as the pages themselves).
                if ($activitylink &&
                        (has_capability('gradereport/' . $CFG->grade_profilereport .':view', $coursecontext) || $viewasuser)) {

                    $coursenamelink = html_writer::link(new moodle_url('/course/user.php', [
                        'mode' => 'grade',
                        'id' => $course->id,
                        'user' => $this->user->id,
                    ]), $coursenamelink);
                } else if (!$activitylink && (has_capability('gradereport/user:view', $coursecontext) || $viewasuser)) {
                    $coursenamelink = html_writer::link(new moodle_url('/grade/report/user/index.php', [
                        'id' => $course->id,
                        'userid' => $this->user->id,
                        'group' => $this->gpr->groupid,
                    ]), $coursenamelink);
                }

                $data = [$coursenamelink, grade_format_gradevalue($finalgrade, $courseitem, true)];

                if ($this->showrank['any']) {
                    if ($this->showrank[$course->id] && !is_null($finalgrade)) {
                        $rank = $coursedata['rank'];
                        $numusers = $coursedata['numusers'];
                        $data[] = "$rank/$numusers";
                    } else {
                        // No grade, no rank.
                        // Or this course wants rank hidden.
                        $data[] = '-';
                    }
                }

                $this->table->add_data($data);
            }

            return true;
        } else {
            echo $OUTPUT->notification(get_string('notenrolled', 'grades'), 'notifymessage');
            return false;
        }
    }

    /**
     * Prints or returns the HTML from the flexitable.
     * @param bool $return Whether or not to return the data instead of printing it directly.
     * @return string
     */
    public function print_table($return=false) {
        ob_start();
        $this->table->print_html();
        $html = ob_get_clean();
        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }

    /**
     * Print a table to show courses that the user is able to grade.
     */
    public function print_teacher_table() {
        $table = new html_table();
        $table->head = array(get_string('coursename', 'grades'));
        $table->data = null;
        foreach ($this->teachercourses as $courseid => $course) {
            $url = new moodle_url('/grade/report/index.php', array('id' => $courseid));
            $table->data[] = array(html_writer::link($url, $course->fullname));
        }
        echo html_writer::table($table);
    }

    // Interface needed by grade_report
    public function process_data($data) {
        return;
    }
    // Interface needed by grade_report
    public function process_action($target, $action) {
        return;
    }

    /**
     * This report supports being set as the 'grades' report.
     */
    public static function supports_mygrades() {
        return true;
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

        } else if ($userid == $USER->id and ((has_capability('moodle/grade:view', $context) and $course->showgrades)
                || $course->id == SITEID)) {
            // Ok - can view own course grades.
            $access = true;

        } else if (has_capability('moodle/grade:viewall', $personalcontext) and $course->showgrades) {
            // Ok - can view grades of this user - parent most probably.
            $access = true;
        } else if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext) and $course->showgrades) {
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
            array(
                'context' => $context,
                'courseid' => $courseid,
                'relateduserid' => $userid,
            )
        );
        $event->trigger();
    }
}
