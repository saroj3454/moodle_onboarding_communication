<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_onboarding', new lang_string('pluginname','local_onboarding'));
    $ADMIN->add('localplugins', $settings);

    //cohorts list used for generalwaitlist
    $cohorts = $DB->get_records_menu('cohort',array('visible'=>1),'name','id,name');
    $cohorts = array(0 => new lang_string('none')) + $cohorts;

    $name = new lang_string('accountsetupreminderemail', 'local_onboarding');
    $description = new lang_string('accountsetupreminderemail_help', 'local_onboarding');
    $settings->add(new admin_setting_configtext('local_onboarding/setupreminder',$name,$description,Hours,PARAM_INT));
   
    $name = new lang_string('completedscorm', 'local_onboarding');
    $description = new lang_string('completedscorm_help', 'local_onboarding');
    $settings->add(new admin_setting_configtext('local_onboarding/completefinalsurveyscorm',$name,$description,Hours,PARAM_INT));

    $name = new lang_string('incompletedscorm', 'local_onboarding');
    $description = new lang_string('incompletedscorm_help', 'local_onboarding');
    $settings->add(new admin_setting_configtext('local_onboarding/incompletefinalsurveyscorm',$name,$description,Hours,PARAM_INT));

    
    

    $name = new lang_string('sendmailccuser', 'local_onboarding');
    $description = new lang_string('sendmailccuser_help', 'local_onboarding');
    $settings->add(new admin_setting_configtextarea('local_onboarding/ccemaillist',$name,$description,null,PARAM_TEXT));

    $name = new lang_string('suspendedusermessaging', 'local_onboarding');
    $description = new lang_string('suspendedusermessaging_help', 'local_onboarding');
    $settings->add(new admin_setting_configtextarea('local_onboarding/useruspendedmessage',$name,$description,null,PARAM_TEXT));



    $externalpage = new admin_externalpage('local_onboarding_reset',
        new lang_string('pluginname','local_onboarding') . ' ' . new lang_string('onboard_reset','local_onboarding'),
        new moodle_url('/local/onboarding/onboard_reset.php'));
    $ADMIN->add('localplugins', $externalpage);

}

