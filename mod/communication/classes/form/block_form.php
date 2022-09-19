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
 * This file contains the forms to create and edit a reusable block
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
 * Reusable block form
 *
 * @package    mod_communication
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_form extends \moodleform {
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

        $mform->addElement('text', 'name', get_string('blockname', 'communication'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'placeholder', get_string('placeholder', 'communication'));
        $mform->setType('placeholder', PARAM_TEXT);
        $mform->addRule('placeholder', get_string('required'), 'required', null, 'client');

        $mform->addElement('header', 'templatehdr', get_string('template', 'communication'));

        $editoroptions = array('maxfiles' => 0, 'maxbytes' => 0, 'trusttext' => false, 'forcehttps' => false);
        $mform->addElement('editor', 'template', '', null, $editoroptions);
        $mform->addRule('template', get_string('required'), 'required', null, 'client');

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

        $reservednames = array('PFNAME', 'PLNAME', 'FFNAME', 'FLNAME', 'FPHONE', 'PNAME', 'SDATEW',
            'SDATED', 'SDATEM', 'SDATEMN', 'SDATEYL', 'SDATEYS', 'STIME12', 'STIME24', 'SLOCATION',
            'SLOCATION+');
        if (in_array($data['name'], $reservednames)) {
            $error['name'] = get_string('reservedname', 'communication');
        } else if ($DB->record_exists_select('communication_blocks', 'name = :name AND id <> :id',
                array('name' => $data['name'], 'id' => $data['id']))) {
            $error['name'] = get_string('duplicateblock', 'communication');
        }

        if ($DB->record_exists_select('communication_blocks', 'placeholder = :placeholder AND id <> :id',
                array('placeholder' => $data['placeholder'], 'id' => $data['id']))) {
            $error['placeholder'] = get_string('duplicateplaceholder', 'communication');
        }

        return $error;
    }
}