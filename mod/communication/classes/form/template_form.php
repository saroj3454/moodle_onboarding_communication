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
 * This file contains the forms to create and edit a communication template
 *
 * @package   mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_communication\form;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Communication template form
 *
 * @package    mod_communication
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template_form extends \moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition () {
        global $DB, $PAGE;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('templatename', 'communication'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $types = $DB->get_records_menu('communication_messagetypes', null, 'sortorder', 'id, name');
        $types = array('' => get_string('choose').'...') + $types;
        $mform->addElement('select', 'type', get_string('messagetype', 'communication'), $types);
        $mform->addRule('type', get_string('required'), 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('description', 'communication'), array('rows' => 4, 'cols' => 60));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('header', 'template1', get_string('template1', 'communication'));

        $mform->addElement('text', 'subjectline', get_string('messagesubject', 'communication'));
        $mform->setType('subjectline', PARAM_TEXT);
        $mform->addRule('subjectline', get_string('required'), 'required', null, 'client');

        $editoroptions = array('maxfiles' => 0, 'maxbytes' => 0, 'trusttext' => false, 'forcehttps' => false);
        $mform->addElement('editor', 'fullmessage', get_string('messagelong', 'communication'), null, $editoroptions);
        $mform->addRule('fullmessage', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('fullmessage', 'messagelong', 'communication');

        // Disabled as this was never implemented
        //$mform->addElement('advcheckbox', 'includecss', get_string('themecss', 'communication'));
        $mform->addElement('hidden','includecss');
        $mform->setDefault('includecss', 0);

        $mform->addElement('button', 'preview1', get_string('preview', 'communication'));
        $PAGE->requires->js_call_amd('mod_communication/template_preview', 'init',
                array('#id_preview1', '#id_fullmessage', '#id_includecss'));

        $mform->addElement('header', 'template2', get_string('template2', 'communication'));

        $mform->addElement('textarea', 'shortmessage', get_string('messageshort', 'communication'),
                array('rows' => 4, 'cols' => 60));
        // $mform->addRule('shortmessage', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('shortmessage', 'messageshort', 'communication');
        $mform->addElement('button', 'preview2', get_string('preview', 'communication'));
        $PAGE->requires->js_call_amd('mod_communication/template_preview', 'init',
                array('#id_preview2', '#id_shortmessage', ''));

        $this->add_action_buttons();
    }

    /**
     * Form validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;

        $error = parent::validation($data, $files);

        if ($DB->record_exists_select('communication_templates', 'name = :name AND id <> :id',
                array('name' => $data['name'], 'id' => $data['id']))) {
            $error['name'] = get_string('duplicatetemplate', 'communication');
        }

        return $error;
    }
}