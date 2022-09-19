<?php
require('../../config.php');
require_once($CFG->dirroot.'/lib/enrollib.php');
require_once('onboard_resetform.php');
require_login();
$id  = optional_param('id', 0, PARAM_INT);
$PAGE->set_context(context_system::instance());
global $DB, $USER;
$pageurl = new moodle_url('/local/onboarding/onboard_reset.php');

$pagetitle = 'Onboard Reset';
$PAGE->set_title($pagetitle);
$PAGE->set_pagelayout('standard');
$PAGE->set_url($pageurl);
$PAGE->navigation->extend_for_user($user);
$PAGE->navbar->add("Setting form", $pageurl);
echo $OUTPUT->header();
$OUTPUT->heading($pagetitle);
$userId = $USER->id;
?>
<link rel="stylesheet" href="css/custom.css">
<div class="C_ours_e">
    <div class="container">
        <div class="dash-heading">
            <h3  class="text-center">ONBOARDING</h3> 

        </div>
    </div>
</div>
<?php
$st_reminder_email =336;
$st_completesurveyscorm =1008;
$st_incompletesurveyscorm =1344;
$st_inactive2week =336;
$st_inactive5week =840;


$addformaaa = new onboard_mappingform();
if ($addform = $addformaaa->is_cancelled()) {
    //echo 1111;
    //die;
	$emailsetting =  $DB->get_record_sql("SELECT * FROM {onboarding_email_setting}");
    if(empty($emailsetting)){
        $updaterecord = new stdClass();
        $updaterecord->reminder_email           = 336;
        $updaterecord->completesurveyscorm      = 1008;
        $updaterecord->incompletesurveyscorm    = 1344;
        $updaterecord->inactive2week            = 336;
        $updaterecord->inactive5week            = 840;
        $updaterecord->updated_date             = time();
       
        $updateRecords=$DB->insert_record('onboarding_email_setting', $updaterecord);
        if($updateRecords){
            $completefinalsurveyscorm = set_config('completefinalsurveyscorm', $updaterecord->completesurveyscorm, 'local_onboarding');
            $incompletesurveyscorm = set_config('incompletefinalsurveyscorm', $updaterecord->incompletesurveyscorm, 'local_onboarding');
            $setupreminder = set_config('setupreminder', $updaterecord->reminder_email, 'local_onboarding');

            $templateinactive2week = $DB->get_record_sql("SELECT ct.* FROM {communication_templates} as ct INNER JOIN {communication_messagetypes} as cm ON cm.id = ct.type WHERE ct.name LIKE ? AND ct.disabled=?", array("%Inactive for 2 weeks%",0));
            $getinactive2week = $DB->get_record('communication_templates',array('id'=>$templateinactive2week->id));
            $inactive2weekid = $getinactive2week->id;
            if(!empty($inactive2weekid)){
                $getinactive2weekrecs = $DB->get_records_sql("SELECT * FROM {communication} WHERE template = ?", array($inactive2weekid));
                foreach ($getinactive2weekrecs as $getinactive2weekrec) {
                    $updatinactive2week = new stdClass();
                    $updatinactive2week->id                       = $getinactive2weekrec->id;
                    $updatinactive2week->schedulingduration       = $updaterecord->inactive2week*60*60;
                    $updatinactive2weekRecords=$DB->update_record('communication', $updatinactive2week);
                }

            }
            $templateinactive5week = $DB->get_record_sql("SELECT ct.* FROM {communication_templates} as ct INNER JOIN {communication_messagetypes} as cm ON cm.id = ct.type WHERE ct.name LIKE ? AND ct.disabled=?", array("%Inactive for 5 weeks%",0));
            $getinactive5week = $DB->get_record('communication_templates',array('id'=>$templateinactive5week->id));
            $inactive5weekid = $getinactive5week->id;
            if(!empty($inactive5weekid)){
                $getinactive5weekrecs = $DB->get_records_sql("SELECT * FROM {communication} WHERE template = ?", array($inactive5weekid));
                foreach ($getinactive5weekrecs as $getinactive5weekrec) {
                    $updatinactive5week = new stdClass();
                    $updatinactive5week->id                       = $getinactive5weekrec->id;
                    $updatinactive5week->schedulingduration       = $updaterecord->inactive5week*60*60;
                    $updatinactive5weekRecords=$DB->update_record('communication', $updatinactive5week);
                }

            }

        }

    }else{
        $updaterecord = new stdClass();
        $updaterecord->id                       = $emailsetting->id;
        $updaterecord->reminder_email           = 336;
        $updaterecord->completesurveyscorm      = 1008;
        $updaterecord->incompletesurveyscorm    = 1344;
        $updaterecord->inactive2week            = 336;
        $updaterecord->inactive5week            = 840;
        $updaterecord->updated_date             = time();
        $updateRecords=$DB->update_record('onboarding_email_setting', $updaterecord);
        if($updateRecords){
            $completefinalsurveyscorm = set_config('completefinalsurveyscorm', $updaterecord->completesurveyscorm, 'local_onboarding');
            $incompletesurveyscorm = set_config('incompletefinalsurveyscorm', $updaterecord->incompletesurveyscorm, 'local_onboarding');
            $setupreminder = set_config('setupreminder', $updaterecord->reminder_email, 'local_onboarding');

            $templateinactive2week = $DB->get_record_sql("SELECT ct.* FROM {communication_templates} as ct INNER JOIN {communication_messagetypes} as cm ON cm.id = ct.type WHERE ct.name LIKE ? AND ct.disabled=?", array("%Inactive for 2 weeks%",0));
            $getinactive2week = $DB->get_record('communication_templates',array('id'=>$templateinactive2week->id));
            $inactive2weekid = $getinactive2week->id;
            if(!empty($inactive2weekid)){
                $getinactive2weekrecs = $DB->get_records_sql("SELECT * FROM {communication} WHERE template = ?", array($inactive2weekid));
                foreach ($getinactive2weekrecs as $getinactive2weekrec) {
                    $updatinactive2week = new stdClass();
                    $updatinactive2week->id                       = $getinactive2weekrec->id;
                    $updatinactive2week->schedulingduration       = $updaterecord->inactive2week*60*60;
                    $updatinactive2weekRecords=$DB->update_record('communication', $updatinactive2week);
                }

            }
            $templateinactive5week = $DB->get_record_sql("SELECT ct.* FROM {communication_templates} as ct INNER JOIN {communication_messagetypes} as cm ON cm.id = ct.type WHERE ct.name LIKE ? AND ct.disabled=?", array("%Inactive for 5 weeks%",0));
            $getinactive5week = $DB->get_record('communication_templates',array('id'=>$templateinactive5week->id));
            $inactive5weekid = $getinactive5week->id;
            if(!empty($inactive5weekid)){
                $getinactive5weekrecs = $DB->get_records_sql("SELECT * FROM {communication} WHERE template = ?", array($inactive5weekid));
                foreach ($getinactive5weekrecs as $getinactive5weekrec) {
                    $updatinactive5week = new stdClass();
                    $updatinactive5week->id                       = $getinactive5weekrec->id;
                    $updatinactive5week->schedulingduration       = $updaterecord->inactive5week*60*60;
                    $updatinactive5weekRecords=$DB->update_record('communication', $updatinactive5week);
                }

            }

        }
    }

	
}
elseif($addform = $addformaaa->get_data())
{
   $emailsetting =  $DB->get_record_sql("SELECT * FROM {onboarding_email_setting}");
    if(!empty($emailsetting)){
        $updaterecord = new stdClass();
        $updaterecord->id 						= $emailsetting->id;
        $updaterecord->reminder_email 			= $addform->reminder_email ;
        $updaterecord->completesurveyscorm 		= $addform->completesurveyscorm;
        $updaterecord->incompletesurveyscorm 	= $addform->incompletesurveyscorm;
        $updaterecord->inactive2week 			= $addform->inactive2week;
        $updaterecord->inactive5week 			= $addform->inactive5week;
        $updaterecord->updated_date 			= time();

        $templateinactive2week = $DB->get_record_sql("SELECT ct.* FROM {communication_templates} as ct INNER JOIN {communication_messagetypes} as cm ON cm.id = ct.type WHERE ct.name LIKE ? AND ct.disabled=?", array("%Inactive for 2 weeks%",0));
        $getinactive2week = $DB->get_record('communication_templates',array('id'=>$templateinactive2week->id));
        $inactive2weekid = $getinactive2week->id;
        if(!empty($inactive2weekid)){
            $getinactive2weekrecs = $DB->get_records_sql("SELECT * FROM {communication} WHERE template = ?", array($inactive2weekid));
            foreach ($getinactive2weekrecs as $getinactive2weekrec) {
                $updatinactive2week = new stdClass();
                $updatinactive2week->id                       = $getinactive2weekrec->id;
                $updatinactive2week->schedulingduration       = $addform->inactive2week*60*60;
                $updatinactive2weekRecords=$DB->update_record('communication', $updatinactive2week);
            }

        }
        $templateinactive5week = $DB->get_record_sql("SELECT ct.* FROM {communication_templates} as ct INNER JOIN {communication_messagetypes} as cm ON cm.id = ct.type WHERE ct.name LIKE ? AND ct.disabled=?", array("%Inactive for 5 weeks%",0));
        $getinactive5week = $DB->get_record('communication_templates',array('id'=>$templateinactive5week->id));
        $inactive5weekid = $getinactive5week->id;
        if(!empty($inactive5weekid)){
            $getinactive5weekrecs = $DB->get_records_sql("SELECT * FROM {communication} WHERE template = ?", array($inactive5weekid));
            foreach ($getinactive5weekrecs as $getinactive5weekrec) {
                $updatinactive5week = new stdClass();
                $updatinactive5week->id                       = $getinactive5weekrec->id;
                $updatinactive5week->schedulingduration       = $addform->inactive5week*60*60;
                $updatinactive5weekRecords=$DB->update_record('communication', $updatinactive5week);
            }

        }

        $updateRecords=$DB->update_record('onboarding_email_setting', $updaterecord);
        if($updateRecords){
            $completefinalsurveyscorm = set_config('completefinalsurveyscorm', $updaterecord->completesurveyscorm, 'local_onboarding');
            $incompletesurveyscorm = set_config('incompletefinalsurveyscorm', $updaterecord->incompletesurveyscorm, 'local_onboarding');
            $setupreminder = set_config('setupreminder', $updaterecord->reminder_email, 'local_onboarding');

            redirect(new moodle_url($CFG->wwwroot.'/local/onboarding/onboard_reset.php'),'Updated Records successfully.', null, \core\output\notification::NOTIFY_SUCCESS);
        }
    }else{
        $insertrecord = new stdClass();
        $insertrecord->reminder_email 			= $addform->reminder_email ;
        $insertrecord->completesurveyscorm 		= $addform->completesurveyscorm;
        $insertrecord->incompletesurveyscorm 	= $addform->incompletesurveyscorm;
        $insertrecord->inactive2week 			= $addform->inactive2week;
        $insertrecord->inactive5week 			= $addform->inactive5week;
        $insertrecord->created_date 			= time();
        $insertdata = $DB->insert_record('onboarding_email_setting', $insertrecord);
        if( $insertdata){
            $completefinalsurveyscorm = set_config('completefinalsurveyscorm', $insertrecord->completesurveyscorm, 'local_onboarding');
            $incompletesurveyscorm = set_config('incompletefinalsurveyscorm', $insertrecord->incompletesurveyscorm, 'local_onboarding');
            $setupreminder = set_config('setupreminder', $insertrecord->reminder_email, 'local_onboarding');
         redirect(new moodle_url($CFG->wwwroot.'/local/onboarding/onboard_reset.php'),'Insert Records successfully.', null, \core\output\notification::NOTIFY_SUCCESS);
     }
 }
}

 $data =  $DB->get_record_sql("SELECT * FROM {onboarding_email_setting}"); 
if(empty($data)){
    $data  = new stdClass();
    $data->id = $id;
}

$addformaaa->set_data($data);
$addformaaa->display();
echo $OUTPUT->footer();
?>