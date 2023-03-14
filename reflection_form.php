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


defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/formslib.php');

class reflection_form extends moodleform {

    /**
     * Definition of the form
     */
    public function definition () {
        global $USER, $CFG, $COURSE;
        $coursecontext = context_course::instance($COURSE->id);

        $mform =& $this->_form;

        $mform->addElement('editor', 'description_editor', "Reflection");
        $mform->setType('description_editor', PARAM_RAW);
        $mform->addRule('description_editor', get_string('required'), 'required', null, 'client');
        $mform->addElement('text', 'userid', 'USER ID');
        $mform->setType('userid', PARAM_RAW);
        $mform->setDefault('userid', $USER->id);
        $mform->addElement('text', 'itemid', 'ITEM ID');
        $mform->setType('itemid', PARAM_RAW);

        $this->add_action_buttons(false, get_string('saveref', 'assignsubmission_reflection'));

        // $PAGE->requires->js_call_amd('assignsubmission_reflection/reflectionFormControl', 'init');
    }

    /**
     * Extend the form definition after the data has been parsed.
     */
    public function definition_after_data() {
        // global $COURSE, $DB, $USER;

        // $mform = $this->_form;
        // $groupid = $mform->getElementValue('id');
        // $coursecontext = context_course::instance($COURSE->id);

        // if ($group = $DB->get_record('groups', array('id' => $groupid))) {
        // If can create group conversation then get if a conversation area exists and it is enabled.
        // if (\core_message\api::can_create_group_conversation($USER->id, $coursecontext)) {
        // if (\core_message\api::is_conversation_area_enabled('core_group', 'groups', $groupid, $coursecontext->id)) {
        // $mform->getElement('enablemessaging')->setSelected(1);
        // }
        // }
        // Print picture.
        // if (!($pic = print_group_picture($group, $COURSE->id, true, true, false))) {
        // $pic = get_string('none');
        // if ($mform->elementExists('deletepicture')) {
        // $mform->removeElement('deletepicture');
        // }
        // }
        // $imageelement = $mform->getElement('currentpicture');
        // $imageelement->setValue($pic);
        // } else {
        // if ($mform->elementExists('currentpicture')) {
        // $mform->removeElement('currentpicture');
        // }
        // if ($mform->elementExists('deletepicture')) {
        // $mform->removeElement('deletepicture');
        // }
        // }

    }

    /**
     * Form validation
     *
     * @param array $data
     * @param array $files
     * @return array $errors An array of errors
     */
    public function validation($data, $files) {
        // global $COURSE, $DB, $CFG;

        // $errors = parent::validation($data, $files);

        // $name = trim($data['name']);
        // if (isset($data['idnumber'])) {
        // $idnumber = trim($data['idnumber']);
        // } else {
        // $idnumber = '';
        // }
        // if ($data['id'] and $group = $DB->get_record('groups', array('id'=>$data['id']))) {
        // if (core_text::strtolower($group->name) != core_text::strtolower($name)) {
        // if (groups_get_group_by_name($COURSE->id,  $name)) {
        // $errors['name'] = get_string('groupnameexists', 'group', $name);
        // }
        // }
        // if (!empty($idnumber) && $group->idnumber != $idnumber) {
        // if (groups_get_group_by_idnumber($COURSE->id, $idnumber)) {
        // $errors['idnumber']= get_string('idnumbertaken');
        // }
        // }

        // if ($data['enrolmentkey'] != '') {
        // $errmsg = '';
        // if (!empty($CFG->groupenrolmentkeypolicy) && $group->enrolmentkey !== $data['enrolmentkey']
        // && !check_password_policy($data['enrolmentkey'], $errmsg)) {
        // Enforce password policy when the password is changed.
        // $errors['enrolmentkey'] = $errmsg;
        // } else {
        // Prevent twice the same enrolment key in course groups.
        // $sql = "SELECT id FROM {groups} WHERE id <> :groupid AND courseid = :courseid AND enrolmentkey = :key";
        // $params = array('groupid' => $data['id'], 'courseid' => $COURSE->id, 'key' => $data['enrolmentkey']);
        // if ($DB->record_exists_sql($sql, $params)) {
        // $errors['enrolmentkey'] = get_string('enrolmentkeyalreadyinuse', 'group');
        // }
        // }
        // }

        // } else if (groups_get_group_by_name($COURSE->id, $name)) {
        // $errors['name'] = get_string('groupnameexists', 'group', $name);
        // } else if (!empty($idnumber) && groups_get_group_by_idnumber($COURSE->id, $idnumber)) {
        // $errors['idnumber']= get_string('idnumbertaken');
        // } else if ($data['enrolmentkey'] != '') {
        // $errmsg = '';
        // if (!empty($CFG->groupenrolmentkeypolicy) && !check_password_policy($data['enrolmentkey'], $errmsg)) {
        // Enforce password policy.
        // $errors['enrolmentkey'] = $errmsg;
        // } else if ($DB->record_exists('groups', array('courseid' => $COURSE->id, 'enrolmentkey' => $data['enrolmentkey']))) {
        // Prevent the same enrolment key from being used multiple times in course groups.
        // $errors['enrolmentkey'] = get_string('enrolmentkeyalreadyinuse', 'group');
        // }
        // }

        // return $errors;
    }

    // /**
    // * Get editor options for this form
    // *
    // * @return array An array of options
    // */
    // function get_editor_options() {
    // return $this->_customdata['editoroptions'];
    // }
}
