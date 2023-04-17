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

        $mform =& $this->_form;
        $mform->addElement('editor',
                           'reflectiontxt_editor',
                            get_string('reflection', 'assignsubmission_reflection'),
                            null,
                            get_editor_options($this->_customdata['context']));

        $mform->setType('reflectiontxt_editor', PARAM_RAW);
        $mform->addRule('reflectiontxt_editor', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'userid', 'USER ID');
        $mform->setType('userid', PARAM_RAW);
        $mform->setDefault('userid', $USER->id);

        $mform->addElement('text', 'id', 'ID');
        $mform->setType('id', PARAM_RAW);
        $mform->setDefault('id', $this->_customdata['id']);

        $mform->addElement('text', 'submission', 'ITEM ID');
        $mform->setType('submission', PARAM_RAW);

        $mform->addElement('text', 'assignment', 'ASSIGNMENT ID');
        $mform->setType('assignment', PARAM_RAW);

        $mform->addElement('text', 'context', 'CONTEXT ID');
        $mform->setType('context', PARAM_RAW);
        $mform->setDefault('context', $this->_customdata['context']);

        $this->add_action_buttons(false, get_string('saveref', 'assignsubmission_reflection'));

    }

}
