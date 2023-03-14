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

 /**
  * Serve the reflection form as a fragment.
  *
  * @param array $args List of named arguments for the fragment loader.
  * @return string
  */
function assignsubmission_reflection_output_fragment_reflectionpanel($args) {
    global $CFG;

    require_once('reflection_form.php');
    $args = (object) $args;
    $context = $args->context;

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    list($ignored, $course) = get_context_info_array($context->id);

    $reflection = new stdClass();
    $reflection->courseid = $course->id;

    $editoroptions = [
        // 'maxfiles' => EDITOR_UNLIMITED_FILES,
        'maxbytes' => $course->maxbytes,
        'trust' => false,
        'context' => $context,
        'noclean' => true,
        'subdirs' => false
    ];

    $reflection = file_prepare_standard_editor($reflection, 'reflection', $editoroptions, $context, 'assign_submission_reflection', 'submission_reflection', null);

    $mform = new reflection_form($formdata);
    // Used to set the courseid.
    $mform->set_data($reflection);


    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    return $mform->render();

}
