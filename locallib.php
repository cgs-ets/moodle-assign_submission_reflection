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

/**
 * This file contains the definition for the library class for declaration submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package assignsubmission_reflection
 * @copyright 2023 Veronica Bermegui
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class assign_submission_reflection extends assign_submission_plugin {

    public function get_name() {
        return get_string('reflection', 'assignsubmission_reflection');
    }

     /**
      *  Get the settings for reflection submission plugin.
      *
      * @param MoodleQuickForm $mform The form to add elements to
      * @return void
      */
    public function get_settings(MoodleQuickForm $mform) {

        $enablereflectiongrp = array();
        $enablereflectiongrp[] = $mform->createElement('checkbox', 'assignsubmission_reflection_before_grading_enabled',
                '', '');
        $mform->addGroup($enablereflectiongrp, 'assignsubmission_reflection_before_grading_group', get_string('reflectionbeforegrading', 'assignsubmission_reflection'), ' ', false);

        $mform->addHelpButton('assignsubmission_reflection_before_grading_group',
                              'reflectionbeforegrading',
                              'assignsubmission_reflection');

        $mform->hideIf('assignsubmission_reflection_before_grading_group',
        'assignsubmission_reflection_enabled',
        'notchecked');

        $mform->hideIf('assignsubmission_reflection_before_grading_enabled',
                       'assignsubmission_reflection_enabled',
                       'notchecked');

    }

    /**
     * The submission reflection plugin has no submission component so should not be counted
     * when determining whether to show the edit submission link.
     * @return boolean
     */
    public function allow_submissions() {
        return false;
    }



     /**
      * Save the settings for declaration submission plugin
      *
      * @param stdClass $data
      * @return bool
      */
    public function save_settings(stdClass $data) {
        global $DB;
        error_log(print_r($data, true));
        $dataobject = new stdClass();
        $dataobject->enabledbgrading = isset($data->assignsubmission_reflection_before_grading_enabled) ? $data->assignsubmission_reflection_before_grading_enabled : 0;
        $dataobject->assignment = $this->assignment->get_instance()->id;

        $DB->insert_record('assignsubmission_ref_setting', $dataobject);

        return true;
    }

      /**
       * @param stdClass $submission
       * @param bool $showviewlink - If the summary has been truncated set this to true
       * @return string
       */
    public function view_summary(stdClass $submission, & $showviewlink) {
        global $OUTPUT, $USER;
        $data = new stdClass();
        $data->contextid = context_module::instance($this->assignment->get_course_module()->id)->id;
        $data->userid = $USER->id; // Student.
        $data->itemid = $submission->id;
        $data->assignment  = $this->assignment->get_instance()->id;
        $reflection = $this->assignsubmission_reflection_get_reflection($data->itemid, $data->assignment);

        $o = get_string( 'availability_message', 'assignsubmission_reflection');

        if (isset($reflection->reflectiontxt)) { // The student submitted the reflection.
            $o = $reflection->reflectiontxt;
        } else if ($this->assignsubmission_reflection_is_graded($data->userid, $data->assignment)
        || $this->assignsubmission_reflection_is_enabled()) {
            $o = $this->assignment->get_renderer()->container($OUTPUT->render_from_template('assignsubmission_reflection/assignsubmission_reflection', $data), 'reflectioncontainer');
        }
        return $o;
    }
      /**
       * The assignment has been deleted - cleanup
       *
       * @return bool
       */
    public function delete_instance() {
        global $DB;

        $DB->delete_records('assignsubmission_reflection', array('assignment' => $this->assignment->get_instance()->id));
        $DB->delete_records('assignsubmission_ref_setting', array('assignment' => $this->assignment->get_instance()->id));

        return true;
    }

    /**
     * Remove a submission.
     *
     * @param stdClass $submission The submission
     * @return boolean
     */
    public function remove($submission) {
        global $DB;
        $submissionid = $submission ? $submission->id : 0;
        if ($submissionid) {
            $DB->delete_records('assignsubmission_reflection', array('submission' => $submissionid));
        }
        return true;

    }
    /**
       * Return the plugin configs for external functions.
       *
       * @return array the list of settings
       * @since Moodle 3.2
    */
    public function get_config_for_external() {
        return (array) $this->get_config();
    }

    /**
     * Get the reflection saved
     */
    public function assignsubmission_reflection_get_reflection($submission, $assignment) {
        global $DB;

        $r = $DB->get_record('assignsubmission_reflection', ['assignment' => $assignment, 'submission' => $submission], 'reflectiontxt');
        return $r;

    }

    /**
     * Only display the reflection text editor if the assignmenthas been graded
     */
    public function assignsubmission_reflection_is_graded($userid, $assignment) {
        global $DB;

        $grade = $DB->get_record('assign_grades', ['assignment' => $assignment, 'userid' => $userid], 'grade', IGNORE_MISSING);

        if (isset($grade->grade)) {
            return $grade->grade > -1.00000;
        }

        return false;

    }

    public function assignsubmission_reflection_is_enabled() {
        global $DB;
        $setting = $DB->get_record('assignsubmission_ref_setting', ['assignment' =>  $this->assignment->get_instance()->id], 'enabledbgrading');

        return $setting->enabledbgrading;
    }

}
