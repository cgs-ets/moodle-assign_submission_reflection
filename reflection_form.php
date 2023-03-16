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

        $mform->addElement('editor', 'reflectiontxt', "Reflection");
        $mform->setType('reflectiontxt', PARAM_RAW);
        $mform->addRule('reflectiontxt', get_string('required'), 'required', null, 'client');
        $mform->addElement('text', 'userid', 'USER ID');
        $mform->setType('userid', PARAM_RAW);
        $mform->setDefault('userid', $USER->id);
        $mform->addElement('text', 'submission', 'ITEM ID');
        $mform->setType('submission', PARAM_RAW);
        $mform->addElement('text', 'assignment', 'ASSIGNMENT ID');
        $mform->setType('assignment', PARAM_RAW);

        $this->add_action_buttons(false, get_string('saveref', 'assignsubmission_reflection'));

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

}
