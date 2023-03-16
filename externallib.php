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

class assignsubmission_reflection_external extends external_api {
    /**
     * Describes the parameters for submit_create_group_form webservice.
     * @return external_function_parameters
     */
    public static function submit_reflection_form_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the course'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the reflection form, encoded as a json array')
            )
        );
    }

    /**
     * Submit the create group form.
     *
     * @param int $contextid The context id for the course.
     * @param string $jsonformdata The data from the form, encoded as a json array.
     * @return int new group id.
     */
    public static function submit_reflection_form($contextid, $jsonformdata) {
        global $CFG, $USER, $DB;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_reflection_form_parameters(),
                                            ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        list($ignored, $course) = get_context_info_array($context->id);
        $serialiseddata = json_decode($params['jsonformdata']);

        $data = array();
        parse_str($serialiseddata, $data);

        $editoroptions = [
            // 'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $course->maxbytes,
            'trust' => false,
            'context' => $context,
            'noclean' => true,
            'subdirs' => false
        ];
        $reflection = new stdClass();
        $reflection->courseid = $course->id;
        $reflection->userid = $USER->id;
        $reflection = file_prepare_standard_editor($reflection, 'reflection', $editoroptions, $context, 'assign_submission_reflection', 'submission_reflection', null);
        $saved = false;
        // The last param is the ajax submitted data.
        $mform = new reflection_form($data);

        $validateddata = $mform->get_data();
        $dataobject = new stdClass();
        $dataobject->assignment = $data['assignment'];
        $dataobject->submission = $data['submission'];
        $dataobject->reflectiontxt = ($data['reflectiontxt'])['text'];
        $dataobject->reflectioformat = ($data['reflectiontxt'])['format'];

        if ($dataobject->reflectiontxt == '') {
            $r = 'EMPTY';
        } else {

            $id = $DB->insert_record('assignsubmission_reflection', $dataobject, true);
            $r = "FAIL";
            if ($id) {
               $r = $dataobject->reflectiontxt;
            }
        }

        return $r;
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
}
