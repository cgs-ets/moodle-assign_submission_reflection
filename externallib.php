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
 * External reflection API
 *
 * @package    assigsumission_reflection
 * @category   external
 * @copyright  2023 Veronica Bermegui
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once("reflection_form.php");
require_once("lib.php");

class assignsubmission_reflection_external extends external_api {
    /**
     * Describes the parameters for submit_reflection_form webservice.
     * @return external_function_parameters
     */
    public static function submit_reflection_form_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the course'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the reflection form, encoded as a json array'),
                'canedit' => new external_value(PARAM_INT, 'Is the student allowed to edit the reflection'),
            )
        );
    }

    /**
     * Submit the create reflection form.
     *
     * @param int $contextid The context id for the course.
     * @param string $jsonformdata The data from the form, encoded as a json array.
     */
    public static function submit_reflection_form($contextid, $jsonformdata, $canedit) {
        global $CFG, $USER, $DB, $COURSE;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_reflection_form_parameters(),
                                            ['contextid' => $contextid, 'jsonformdata' => $jsonformdata, 'canedit' => $canedit]);

        $context = context::instance_by_id($params['contextid']);
        // We always must call validate_context in a webservice.
        self::validate_context($context);

        list($ignored, $course) = get_context_info_array($context->id);

        $serialiseddata = json_decode($params['jsonformdata']);
        $serialiseddata . '&status=""';

        $data = array();

        parse_str($serialiseddata, $data);

        $reflection = new stdClass();
        $reflection->courseid = $course->id;
        $reflection->userid = $USER->id;
        $reflection->context = $context;
        $id = '';
        $response = new stdClass();

        if (strlen(($data['reflectiontxt_editor'])['text']) == 0) {
            $serialiseddata . '&status=EMPTY';
            $response->result = 'EMPTY';
            return json_encode($response);
        }

        $formdata = new stdClass();
        $formdata->assignment = $data['assignment'];
        $formdata->submission = $data['submission'];
        $customdata = [ 'context' => $context,
                        'id' => $id,
                        'current' => $reflection
                     ];
        $mform = new reflection_form(null, $customdata, get_editor_options($context), 'post', '', '', true, $data);
        $mform->set_data($formdata);

        if ($record = $DB->get_record('assignsubmission_reflection', ['assignment' => $data['assignment'], 'submission' => $data['submission']], '*')) {

            $record->reflectiontxt = ($data['reflectiontxt_editor'])['text'];
            $DB->update_record('assignsubmission_reflection', $record);
            $id = $record->id;

        } else {

            $ins = (object)[
                    'assignment' => $formdata->assignment,
                    'submission' => $formdata->submission,
                    'reflectiontxt' => '', // Update later.
                    'reflectiontxtformat' => FORMAT_HTML
                ];

            $id = $DB->insert_record('assignsubmission_reflection', $ins, true);
        }

        $serialiseddata .= "&id=$id";
        $data['id'] = $id;
        $data = file_postupdate_standard_editor((object)$data,
                                                    'reflectiontxt',
                                                    get_editor_options($contextid),
                                                    $context,
                                                    ASSIGNSUBMISSION_REFLECTION_COMPONENT,
                                                    ASSIGNSUBMISSION_REFLECTION_FILEAREA,
                                                    $id
                                                    );

        $DB->update_record('assignsubmission_reflection', $data);

        if (!$canedit) {
            $txt = ($data->reflectiontxt_editor)['text'];
            $text = rewrite_assignsubmission_reflection_urls($txt, $data->id, $context->id);
            unset($serialiseddata);
            $response->result = 'SUCCESS_NON_EDITABLE';
            $response->reflection = $text;
            return json_encode($response);
        }

        $response->result = 'SUCCESS_EDITABLE';
        $response->serialiseddata = $serialiseddata;

        return  json_encode($response);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function submit_reflection_form_returns() {
        return new external_value(PARAM_RAW, 'reflection text submitted');
    }


    public static function get_reflection_parameters() {
        return new external_function_parameters(
            array(
                'submission' => new external_value(PARAM_INT, 'Submission id (AKA itemid)'),
                'assignment' => new external_value(PARAM_INT, 'Assignment id'),
                'context' => new external_value(PARAM_INT, 'The context id for the course')
            )
        );
    }

    /**
     * Get the reflection text
     *
     * @param int $submission The submissin id.
     * @param int $assignment The assignment id.
     * @return int contextid  The context id for the course.
     */
    public static function get_reflection($submission, $assignment, $contextid) {
        global $CFG, $USER, $DB, $COURSE;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::get_reflection_parameters(),
                                            ['submission' => $submission, 'assignment' => $assignment, 'context' => $contextid]);

        $context = context::instance_by_id($params['context']);
        // We always must call validate_context in a webservice.
        self::validate_context($context);
        $data = $DB->get_record('assignsubmission_reflection', ['assignment' => $assignment, 'submission' => $submission], '*');

        $text = file_rewrite_pluginfile_urls(
            $data->reflectiontxt,
            'pluginfile.php',
            $context->id,
            ASSIGNSUBMISSION_REFLECTION_COMPONENT,
            ASSIGNSUBMISSION_REFLECTION_FILEAREA,
            $data->id
        );

        if (strlen($data->reflectiontxt) > 0) {
            $text .= html_writer::start_tag('a', ['href' => '#',
                                                   'id' => 'more',
                                                   'title' => get_string('showmore', 'assignsubmission_reflection')])
                                                   . '<i class="icon fa fa-plus fa-fw assignsubmission_reflection-plus"></i>' . html_writer::end_tag('a');
        }

        return  $text;

    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function get_reflection_returns() {
        return new external_value(PARAM_RAW, 'reflection text submitted');
    }


}
