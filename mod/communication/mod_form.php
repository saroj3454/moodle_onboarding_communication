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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package   mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Communication settings form.
 *
 * @package   mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_communication_mod_form extends moodleform_mod {

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {
        global $CFG, $COURSE, $DB, $PAGE;
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('communicationname', 'communication'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $templates = array('' => get_string('choosedots'));
        $templates += $DB->get_records_sql_menu("SELECT t.id, t.name
                                                 FROM {communication_templates} t
                                                 JOIN {communication_messagetypes} mt ON t.type = mt.id
                                             ORDER BY mt.name, t.name");
        $dictionary = $DB->get_records_menu('communication_templates', null, 'sortorder', 'id, description');
        $mform->addElement('select', 'template', get_string('template', 'communication'), $templates);
        $mform->addRule('template', null, 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('description', 'communication'),
                array('rows' => 4, 'cols' => 60, 'disabled' => 'disabled'));
        $PAGE->requires->js_call_amd('mod_communication/description_loader', 'init',
                array('#id_template', '#id_description', $dictionary));

        $mform->addElement('header', 'schedulinghdr', get_string('scheduling', 'communication'));

        $scheduling = array();
        $scheduling[] = $mform->createElement('duration', 'schedulingduration', '',
                array('defaultunit' => '86400', 'optional' => false));
        $direction = array(
            COMMUNICATION_SCHEDULING_BEFORE => get_string('before', 'communication'),
            COMMUNICATION_SCHEDULING_AFTER => get_string('after', 'communication')
        );
        $scheduling[] = $mform->createElement('select', 'schedulingdirection', '', $direction);
        $activities = array(get_string('chooseactivity', 'communication'));
        $schedulers = array(get_string('choosescheduler', 'communication'));
        $schedulerslots = array();
        $modinfo = get_fast_modinfo($COURSE);
        foreach ($modinfo->cms as $id => $othercm) {
            $activities[$id] = format_string($othercm->name, true, array('context' => context_course::instance($COURSE->id)));
            if ($othercm->modname == 'scheduler') {
                $scheduler = $DB->get_record('scheduler', array('id' => $othercm->instance));
                if ($scheduler->scale > 0) {
                    $schedulerslots[$othercm->instance] = $scheduler->scale;
                    $schedulers[$othercm->instance] = format_string($othercm->name, true,
                            array('context' => context_course::instance($COURSE->id)));
                }
            }
        }

        $select1[COMMUNICATION_SCHEDULING_SLOT] = get_string('schedulerslot', 'communication');
        $select1[COMMUNICATION_SCHEDULING_COMPLETION] = get_string('activitycompletion', 'communication');
        $select1[COMMUNICATION_SCHEDULING_ENROLMENT] = get_string('enrolmentdate', 'communication');
        $select1[COMMUNICATION_SCHEDULING_INACTIVE] = get_string('inactiveuser', 'communication');

        $select2[COMMUNICATION_SCHEDULING_SLOT] = $schedulers;
        $select2[COMMUNICATION_SCHEDULING_COMPLETION] = $activities;
        $select2[COMMUNICATION_SCHEDULING_ENROLMENT][0] = get_string('notapplicable', 'communication');

        foreach (array_keys($schedulers) as $id) {
            $select3[COMMUNICATION_SCHEDULING_SLOT][$id][0] = get_string('chooseslot', 'communication');
            if ($id) {
                for ($i = 1; $i <= $schedulerslots[$id]; $i++) {
                    $select3[COMMUNICATION_SCHEDULING_SLOT][$id][$i] = "$i";
                }
            }
        }

        foreach (array_keys($activities) as $cmid) {
                $select3[COMMUNICATION_SCHEDULING_COMPLETION][$cmid][0] = get_string('notapplicable', 'communication');
        }

        $select3[COMMUNICATION_SCHEDULING_ENROLMENT][0][0] = get_string('notapplicable', 'communication');

        $hiersel = $mform->createElement('hierselect', 'schedulingsubject', '', array('class' => 'custom-select'), ' ');
        $hiersel->setOptions(array($select1, $select2, $select3));
        $scheduling[] = $hiersel;

        $mform->addGroup($scheduling, 'scheduling', get_string('messagescheduling', 'communication'), array(' '), false);
        $PAGE->requires->js_call_amd('mod_communication/hierselect_hiding', 'init', array('schedulingsubject', 3));

        $mform->addElement('duration', 'schedulingthreshold', get_string('giveupthreshold', 'communication'));
        $mform->setDefault('schedulingthreshold', 86400);

        $mform->addElement('selectyesno', 'resendonslotchange', get_string('resendonslotchange', 'communication'));
        $mform->disabledIf('resendonslotchange','schedulingsubject[0]','neq',0);

        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();

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
        $error = parent::validation($data, $files);

        if (empty($data['schedulingduration'])) {
            $error['scheduling'] = get_string('durationempty', 'communication');
        } else if (!in_array($data['schedulingdirection'],
                array(
                    COMMUNICATION_SCHEDULING_BEFORE,
                    COMMUNICATION_SCHEDULING_AFTER
                ))) {
            $error['scheduling'] = 'invalid';
        } else if ($data['schedulingsubject'][1]) {
            //$error['scheduling'] = 'invalid';
        }

        return $error;
    }

    /**
     * Need to translate the "schedulingsubjectitem" and "schedulingsubjectid" field.
     *
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        if (!isset($defaultvalues['schedulingsubject'])) {
            $defaultvalues['schedulingsubject'] = array();
        }
        if (isset($defaultvalues['schedulingsubjectitem'])) {
            $defaultvalues['schedulingsubject'][0] = $defaultvalues['schedulingsubjectitem'];
        }
        if (isset($defaultvalues['schedulingsubjectid'])) {
            $defaultvalues['schedulingsubject'][1] = $defaultvalues['schedulingsubjectid'];
        }
        if (isset($defaultvalues['schedulingsubjectinfo'])) {
            $defaultvalues['schedulingsubject'][2] = $defaultvalues['schedulingsubjectinfo'];
        }
    }

    /**
     * Adds completion elements to the form and return the list of IDs.
     *
     * @return array
     */
    public function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement('checkbox', 'completiontriggermessage', '', get_string('completiontrigger', 'communication'));
        $mform->setDefault('completion', COMPLETION_TRACKING_AUTOMATIC);
        $mform->setDefault('completiontriggermessage', true);

        return array('completiontriggermessage');
    }

    /**
     * Called during validation. Indicates, based on the data, whether a custom completion rule is enabled (selected).
     *
     * @param array $data
     * @return bool
     */
    public function completion_rule_enabled($data) {
        return (!empty($data['completiontriggermessage']));
    }

    /**
     * Return submitted data if properly submitted or returns NULL if validation fails or
     * if there is no submitted data.
     *
     * @return bool|object
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        if (!$data) {
            return false;
        }
        // Turn off completion settings if the checkboxes aren't ticked.
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (!$autocompletion) {
                $data->completiontriggermessage = 0;
            }
        }

        $data->schedulingsubjectitem = $data->schedulingsubject[0];
        $data->schedulingsubjectid   = $data->schedulingsubject[1];
        $data->schedulingsubjectinfo = $data->schedulingsubject[2];
        unset($data->schedulingsubject);
         //added lds 
        if($data->schedulingsubjectitem=='3'){
            $data->schedulingsubjectid=0;
        }   

    }
}