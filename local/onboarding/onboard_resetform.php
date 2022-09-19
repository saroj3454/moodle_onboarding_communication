<?php
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/formslib.php');
global $DB, $USER;
class onboard_mappingform extends moodleform {
    // Define the form
    function definition() {
		global $CFG,$DB,$USER, $TEXTAREA_OPTIONS;		
		$mform =& $this->_form;

		$mform->addElement('text', 'reminder_email', get_string('accountsetupreminderemail', 'local_onboarding'));
		$mform->setType('reminder_email', PARAM_MULTILANG); 
		
		$mform->addElement('text', 'completesurveyscorm', get_string('completedscorm', 'local_onboarding'));
		$mform->setType('completesurveyscorm', PARAM_MULTILANG); 
		
		$mform->addElement('text', 'incompletesurveyscorm', get_string('incompletedscorm', 'local_onboarding'));
		$mform->setType('incompletesurveyscorm', PARAM_MULTILANG); 
		
		$mform->addElement('text', 'inactive2week', get_string('inactive2week', 'local_onboarding'));
		$mform->setType('inactive2week', PARAM_MULTILANG); 
		
		$mform->addElement('text', 'inactive5week', get_string('inactive5week', 'local_onboarding'));
		$mform->setType('inactive5week', PARAM_MULTILANG); 
		$mform->addElement('hidden', 'id'); 
		$mform->setType('region', PARAM_MULTILANG);  
		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] =& $mform->createElement('cancel', 'cancel', 'Reset');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);


    }
}