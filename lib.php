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


 // File component for submission reflection.
define('ASSIGNSUBMISSION_REFLECTION_COMPONENT', 'assignsubmission_reflection');

// File area for submission reflection.
define('ASSIGNSUBMISSION_REFLECTION_FILEAREA', 'submission_reflection');
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

    // When the student is allowed to edit their reflections. The serialization from PHP is different from JS.
    // the & is amp:, we need to remove it, to load the text in the textarea.
    $formdataaux = [];
    foreach ($formdata as $i => $param) {
        $formdataaux[trim(str_replace('amp;', '', $i))] = $param;
    }

    $formdata = $formdataaux;
    list($ignored, $course) = get_context_info_array($context->id);

    $reflection = new stdClass();
    $reflection->courseid = $course->id;
    $reflection->id = isset($formdata['id']) ? $formdata['id'] : 0;
    $reflection->itemid = isset($formdata['id']) ? $formdata['id'] : 0;
    $reflection->reflectiontxt = isset($formdata['reflectiontxt_editor']['text'])
                                 ? ($formdata['reflectiontxt_editor']['text'])
                                 : '';
    $reflection->reflectiontxtformat = FORMAT_HTML;
    $reflection->context = $context;
    $reflection = file_prepare_standard_editor($reflection,
                                                'reflectiontxt',
                                                get_editor_options($context),
                                                $context,
                                                ASSIGNSUBMISSION_REFLECTION_COMPONENT,
                                                ASSIGNSUBMISSION_REFLECTION_FILEAREA,
                                                $reflection->id
                                            );

    $customdata = [ 'context' => $context, 'id' => $reflection->id];

    // Only update the text if the url has the format @@PLUGINFILE@@. This means it has been saved.
    if (strpos($formdata['reflectiontxt_editor']['text'], '@@PLUGINFILE@@') != false) {

        $formdata['reflectiontxt_editor']['text'] = $reflection->reflectiontxt_editor['text'];
    }

    $mform = new reflection_form(null, $customdata, 'post', '', get_editor_options($context), true, $formdata);
    // Used to set the courseid.
    $mform->set_data($reflection);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    return $mform->render();

}

/**
 * Serve the files from the MYPLUGIN file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function assignsubmission_reflection_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== ASSIGNSUBMISSION_REFLECTION_FILEAREA) {
        return false;
    }

    // Make sure the user is logged in and has access to the module.
    // (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, false, $cm);

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/assignsubmission_reflection/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();
    if (!($file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        return false;
    }

    // Download MUST be forced - security!
    send_stored_file($file, 0, 0, true);
}

/**
 * File format options.
 *
 * @return array
 */
function get_editor_options($context) {
    global $COURSE;
    return [
        'subdirs' => false,
        'maxbytes' => $COURSE->maxbytes,
        'maxfiles' => EDITOR_UNLIMITED_FILES,
        'changeformat' => false,
        'context' => $context,
        'noclean' => false,
        'trusttext' => false,
        'enable_filemanagement' => false
    ];

}

/**
 * Convert encoded URLs in $text from the @@PLUGINFILE@@/... form to an actual URL.
 * @param string $text the Text to check
 * @param int $gradeid The grade ID which refers to the id in the gradebook
 * assignsubmission_reflection_rewrite_urls
 */
function assignsubmission_reflection_rewrite_urls($text, $id, $contextid) {

    return file_rewrite_pluginfile_urls(
        $text,
        'pluginfile.php',
        $contextid,
        ASSIGNSUBMISSION_REFLECTION_COMPONENT,
        ASSIGNSUBMISSION_REFLECTION_FILEAREA,
        $id
    );
}

/**
 * Format reflection text to display files properly.
 */
function assignsubmission_reflection_format_submitted_reflection($text, $id, $contextid) {

    if ($text) {
        $text = assignsubmission_reflection_rewrite_urls($text->reflectiontxt, $id, $contextid);
        $text = format_text(
            $text,
            $text->reflectiontxt,
            [
                'context' => FORMAT_HTML
            ]
        );

        return $text;
    }
    return '';
}

/**
 * Check if the user that is going to see the activity is
 * a mentor.
 */
function assignsubmission_reflection_is_mentor($userid) {
    global $DB, $USER;
    // Parents are allowed to view block in their mentee profiles.
    $mentorrole = $DB->get_record('role', array('shortname' => 'parent'));
    $mentor = null;

    if ($mentorrole) {

        $sql = "SELECT ra.*, r.name, r.shortname
            FROM {role_assignments} ra
            INNER JOIN {role} r ON ra.roleid = r.id
            INNER JOIN {user} u ON ra.userid = u.id
            WHERE ra.userid = ?
            AND ra.roleid = ?
            AND ra.contextid IN (SELECT c.id
                FROM {context} c
                WHERE c.contextlevel = ?
                AND c.instanceid = ?)";
        $params = array(
            $USER->id,
            $mentorrole->id,
            CONTEXT_USER,
            $userid,
        );

        $mentor = $DB->get_records_sql($sql, $params);
    }

    return $mentor;
}
