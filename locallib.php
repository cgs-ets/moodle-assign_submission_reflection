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

require_once($CFG->dirroot . '/mod/assign/submission/reflection/lib.php');

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

        $reflectionbeforegrading = $this->assignsubmission_reflection_get_setting();

        $mform->setDefault('assignsubmission_reflection_before_grading_enabled', $reflectionbeforegrading);

        // Enable editing setting.

        $enableeditreflectiongrp = array();
        $enableeditreflectiongrp[] = $mform->createElement('checkbox', 'assignsubmission_reflection_editing_grading_enabled',
                '', '');
        $mform->addGroup($enableeditreflectiongrp, 'assignsubmission_reflection_editing_grading_group', get_string('reflectioneditinggrading', 'assignsubmission_reflection'), ' ', false);

        $mform->addHelpButton('assignsubmission_reflection_editing_grading_group',
                              'reflectionbeforegrading',
                              'assignsubmission_reflection');

        $mform->hideIf('assignsubmission_reflection_editing_grading_group',
        'assignsubmission_reflection_enabled',
        'notchecked');

        $mform->hideIf('assignsubmission_reflection_before_grading_enabled',
                       'assignsubmission_reflection_enabled',
                       'notchecked');

        $data = $this->assignsubmission_reflection_get_setting();
        $mform->setDefault('assignsubmission_reflection_before_grading_enabled', $data->enabledbgrading);
        $mform->setDefault('assignsubmission_reflection_editing_grading_enabled', $data->enableediting);

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

        $dataobject = new stdClass();
        $dataobject->enabledbgrading = isset($data->assignsubmission_reflection_before_grading_enabled) ? $data->assignsubmission_reflection_before_grading_enabled : 1;
        $dataobject->enableediting = isset($data->assignsubmission_reflection_editing_grading_enabled) ? $data->assignsubmission_reflection_editing_grading_enabled : 0;
        $dataobject->assignment = $this->assignment->get_instance()->id;
        $table = 'assignsubmission_ref_setting';

        if ($r = $DB->get_record($table, ['assignment' => $this->assignment->get_instance()->id])) {
            $dataobject->id = $r->id;
            $DB->update_record($table, $dataobject);
            $id = $r->id;
        } else {
            $id = $DB->insert_record($table, $dataobject);
        }

        $this->set_config('reflectionbeforegrading', $id);
        $this->set_config('enableediting', $dataobject->enableediting);
        $this->set_config('reflectionenabled', 1);

        return true;
    }

      /**
       * @param stdClass $submission
       * @param bool $showviewlink - If the summary has been truncated set this to true
       * @return string
       */
    public function view_summary(stdClass $submission, & $showviewlink) {
        global $OUTPUT, $USER, $COURSE;

        $data = new stdClass();
        $data->contextid = context_module::instance($this->assignment->get_course_module()->id)->id;
        $data->itemid = $submission->id;
        $data->assignment  = $this->assignment->get_instance()->id;
        $data->courseid = $COURSE->id;

        $reflection = $this->assignsubmission_reflection_get_reflection($data->itemid, $data->assignment);

        $o = get_string( 'availability_message', 'assignsubmission_reflection');

        $settings = $this->assignsubmission_reflection_get_setting();

        $data->canedit = $settings->enableediting;

        $data->reflectiontxt = json_encode(['text' => isset($reflection->reflectiontxt)
                                                            ? $reflection->reflectiontxt
                                                            : '',
                                            'format' => 1]);
        $data->userid = $submission->userid; // Student.
        $data->itemid = $submission->id;
        $data->assignment  = $this->assignment->get_instance()->id;

        $data->jsondata = http_build_query(['sesskey' => $USER->sesskey,
                            '_qf__reflection_form' => 1,
                            'reflectiontxt_editor' => ['text' => isset($reflection->reflectiontxt) ? $reflection->reflectiontxt : '',
                                                'format' => FORMAT_HTML],
                            'userid' => $data->userid,
                            'submission' => $submission->id,
                            'assignment' => $this->assignment->get_instance()->id,
                            'context' => $data->contextid,
                            'id' => isset($reflection->id) ? $reflection->id : null
                            ]);

        if ($this->assignsubmission_reflection_is_graded($data->userid, $data->assignment)
            ||( $settings->enabledbgrading && $settings->enableediting)
            || ($settings->enabledbgrading && !isset($reflection->reflectiontxt))) {
            $o = $this->assignment->get_renderer()->container($OUTPUT->render_from_template('assignsubmission_reflection/assignsubmission_reflection', $data), 'reflectioncontainer');
        } else {
            $reflection->reflectiontxt = assignsubmission_reflection_format_submitted_reflection($reflection, $reflection->id, $this->assignment->get_context()->id);
            $d = new stdClass();
            $d->reflectiontxt .= $reflection->reflectiontxt;
            $d->userid = $submission->userid;
            $d->itemid = $submission->id;
            $d->contextid = $data->contextid;
            $d->assignment  = $this->assignment->get_instance()->id;

            $o = $this->assignment->get_renderer()->container($OUTPUT->render_from_template('assignsubmission_reflection/assignsubmission_reflection_non_edit', $d));
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
     *
     */
    public function get_config_for_external() {
        return (array) $this->get_config();
    }

    /**
     * Display the saved text content from the editor in the view table
     *
     * @param stdClass $submission
     * @return string
    */
    public function view(stdClass $submission) {
        $reflection = $this->assignsubmission_reflection_get_reflection($submission->id, $submission->assignment, $submission->userid);

        if ($reflection) {

            $contextid = context_module::instance($this->assignment->get_course_module()->id)->id;
            $reflection = rewrite_assignsubmission_reflection_urls($reflection->reflectiontxt, $reflection->id, $contextid);
            $o = '';
            $o .= html_writer::start_div('view-reflection');
            $o .= $reflection;
            $o .= html_writer::end_div();

            return $o;
        }

        return '';
    }

    /**
     * Get the reflection saved
     */
    private function assignsubmission_reflection_get_reflection($submission, $assignment, $userid = 0) {
        global $DB;
        if ($userid != 0) {
            $r = $DB->get_record('assignsubmission_reflection', ['assignment' => $assignment, 'submission' => $submission, 'userid' => $userid], '*');
        } else {
            $r = $DB->get_record('assignsubmission_reflection', ['assignment' => $assignment, 'submission' => $submission], '*');
        }
        return $r;
    }


    private function assignsubmission_reflection_is_graded($userid, $assignment) {
        global $DB;

        $grade = $DB->get_record('assign_grades', ['assignment' => $assignment, 'userid' => $userid], 'grade', IGNORE_MISSING);

        if (isset($grade->grade)) {
            return $grade->grade > -1.00000;
        }

        return false;

    }

    private function assignsubmission_reflection_get_setting() {
        global $DB;

        $reflectionbeforegrading = $this->get_config('reflectionbeforegrading');
        $data = new stdClass();
        $data->enabledbgrading = 1;
        $data->enableediting = 0;
        $reflectionbeforegradingison = 0;

        if ($reflectionbeforegrading != 0) {
            $reflectionbeforegradingison = $DB->get_record('assignsubmission_ref_setting', ['id' => $reflectionbeforegrading ], 'enabledbgrading');
            $data = $DB->get_record('assignsubmission_ref_setting', ['id' => $reflectionbeforegrading ], '*');
            $reflectionbeforegradingison = $reflectionbeforegradingison->enabledbgrading;
        }

        return $data;
    }

    /**
     * Return a description of external params suitable for uploading an submission reflection from a webservice.
     *
     * @return external_description|null
     */
    public function assignsubmission_reflection_get_external_parameters() {
        $editorparams = array('text' => new external_value(PARAM_RAW, 'The text for this reflection.'),
                              'format' => new external_value(PARAM_INT, 'The format for this reflection'));
        $editorstructure = new external_single_structure($editorparams, 'Editor structure', VALUE_OPTIONAL);
        return array('reflectiontxt' => $editorstructure);
    }

}
