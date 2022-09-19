<?php
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/formslib.php');
require_once('css/style.css');
global $DB, $USER;
class user_resetform extends moodleform {
    // Define the form
    function definition() {
		global $CFG,$DB,$USER, $TEXTAREA_OPTIONS;		
		$mform =& $this->_form;
		$token= optional_param('token','', PARAM_RAW);
		$gettoken = $DB->get_record_sql("SELECT * FROM {onboarding_token} WHERE status = 1 AND token='".$token."'");
		$mform->addElement('text', 'fname', 'First Name', 'maxlength="254" size="50" disabled=disabled'); 
		$mform->addElement('text', 'lname', 'Last Name', 'maxlength="254" size="50" disabled=disabled'); 
		$mform->addElement('text', 'email', 'Email', 'maxlength="254" size="50" disabled=disabled'); 
		$mform->addElement('password', 'password', 'Password', 'maxlength="254" size="50" ');
		$mform->addRule('password', 'Enter your password', 'required', 'client');
		$mform->setType('password', PARAM_MULTILANG);	
		$mform->addElement('password', 'confirmpassword', 'Confirm password', 'maxlength="254" size="50" ');
		$mform->addRule('confirmpassword', 'Enter your confirmpassword', 'required', 'client');
		$mform->setType('password', PARAM_MULTILANG);
		$mform->addElement('hidden', 'token'); 
		$mform->addElement('hidden', 'userid'); 
		$mform->addElement('hidden', 'tokenid'); 
		$mform->setType('region', PARAM_MULTILANG); 
		$this->add_action_buttons(false, "SAVE");   
    } 
	
	function validation($data, $files) {
		global $USER, $CFG, $DB, $SESSION, $OUTPUT;
        $error =  array();			
		if($data['password'] != $data['confirmpassword']){
			$error['confirmpassword']="confirmpassword not match";
		}		
        return $error;
    }

}