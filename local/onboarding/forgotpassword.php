<?php
require_once("../../config.php");
global $USER, $CFG, $DB,$PAGE,$OUTPUT;
 $logo = $OUTPUT->get_logo_url(null, 100);

$token=required_param('token',PARAM_RAW);
$tokendata=$DB->get_record_sql("select * from {onboarding_token} where `token`=? and `status`=?",array($token,1));
if(!empty($tokendata)){
    $userid=$tokendata->userid;
}else{
    redirect($CFG->wwwroot);   
}
if(!empty($_POST['changepassword'])){
    if($_POST['password']==$_POST['cpassword']){
        $updatedetoken = new stdClass();
        $updatedetoken->id=$_POST['userid'];
        $updatedetoken->password=md5($_POST['password']);
        if($DB->update_record('user', $updatedetoken)){
            $tokendata->status=0;
            $tokendata->modifieddate=time();
            $DB->update_record('onboarding_token', $tokendata);
            $userdata = $DB->get_record("user", array("id"=>$userid));
            $userondata=$DB->get_record_sql("select * from {onboarding_track} where `userid`='".$userid."'");
            if(!empty($userondata)){
                enrolCourse($userondata->courseid, $userid); 
            }
            complete_user_login($userdata);
            $userdata->lastlogin=time();
            $DB->update_record("user",$userdata);
            \core\session\manager::apply_concurrent_login_limit($userdata->id, session_id());
            redirect($CFG->wwwroot, "Password Updated Sucessfully", null, \core\output\notification::NOTIFY_SUCCESS);
        }else{
            redirect($CFG->wwwroot , "Some thing Wrong Please try again", null, \core\output\notification::NOTIFY_ERROR);
        }
    }else{
        redirect($CFG->wwwroot . "/local/onboarding/forgotpassword.php?token=".$token, "Password and Confirm Password Not Match", null, \core\output\notification::NOTIFY_ERROR);
    }
}


require_once($CFG->libdir . '/filelib.php');
//require_once('forgotpassword_form.php');
$pagetitle = 'forgot Password';
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('login');
$PAGE->set_title($pagetitle);
$token= optional_param('token','', PARAM_RAW);
$gettoken = $DB->get_record_sql("SELECT * FROM {onboarding_token} WHERE status = 1 AND token='".$token."'");
$tokengenratedate = $gettoken->createddate;
$lat12week =  strtotime("-84 day 00:00:01");
if($tokengenratedate < $lat12week){
	redirect($CFG->wwwroot, "Welcome back! Your unique link to access the On Track option is only valid for 12 weeks and has expired. To ensure you are offered the most suitable program option available to you after this time, you will need to redo the online health check.[Click here] Prefer to talk to someone? Call 137475 for assistance.", null, \core\output\notification::NOTIFY_ERROR);
}
echo $OUTPUT->header();
$loginuser = $USER->id;
if(empty($loginuser)){
	$loginuser = $gettoken->userid;
}
if(empty($gettoken)){
	redirect($CFG->wwwroot);
} 
?>
 
<link rel="stylesheet" href="css/style.css"></link>
<div class='login-bg'>
    <div class='login-img1'>
            <div class='row login-form-wrap'>
                <div class='col-12 col-lg-8 form-bg px-5 py-4'>
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="logo">
                            <img src="<?php echo $logo ?>" alt="" width="100%" height="100%" />
                        </div>   
                    </div>
                   <!--  <div class='text-center'>
                    </div> -->
                    <div class='row justify-content-center'>
                        <div class='col-12 col-lg-5'>
                            <form  class='mt-5' method="post">

                                <input type="hidden" value="<?php echo $userid; ?>" name='userid'>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control p-4" id="inputPassword" placeholder="Password" required="true">
                                </div>
                                <div class="form-group">
                                    <input type="password" name="cpassword" class="form-control p-4" id="InputPassword1" placeholder="Confirm Password" required="true">
                                </div>
                                <p class="reminder">Reminder: When you log in next time, you will need to login with your email address and this password.</p>
                                <button type="submit" class="btn btn-primary w-100 p-4" value="changepassword" name='changepassword'>Log In</button>

                         

                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
<?php

if(!empty($token)){
	$data = $DB->get_record("onboarding_token", array("token"=>$token));
	$getuser = $DB->get_record("user", array("id"=>$data->userid));
    $data->fname = $getuser->firstname;
    $data->lname = $getuser->lastname;
    $data->email = $getuser->email;
    $data->token = $token;
    $data->userid = $data->userid;
    $data->tokenid = $data->id;

}

if(!empty($token)){
    $data = $DB->get_record("onboarding_token", array("token"=>$token));
    $getdata = $DB->get_record("user", array("id"=>$data->userid));
    // $data->fname = $getuser->firstname
}

// else(!empty($token)){
//     $data = $DB->get_record("into", array("id"=>$data->userid));
//     $getuser = $DB->getuser->firstname;
//     $getuser =
// } 

echo $OUTPUT->footer();

function enrolCourse($courseid, $userid, $duration = 0) {

    global $DB, $CFG;   
    // $query = .$courseid;
    $enrollmentID = $DB->get_record_sql('SELECT * FROM {enrol} WHERE enrol = "manual" AND courseid = ?', array($courseid));
    if(!empty($enrollmentID->id)) {
        if (!$DB->record_exists('user_enrolments', array('enrolid'=>$enrollmentID->id, 'userid'=>$userid))) {
            $userenrol = new stdClass();
            $userenrol->status = 0;
            $userenrol->userid = $userid;
            $userenrol->enrolid = $enrollmentID->id; 
            $userenrol->timestart  = time(); 
            $userenrol->timeend = 0; 
            $userenrol->modifierid  = 2; 
            $userenrol->timecreated  = time();
            $userenrol->timemodified  = time(); 
            $enrol_manual = enrol_get_plugin('manual');
            $enrol_manual->enrol_user($enrollmentID, $userid, 5, $userenrol->timestart, $userenrol->timeend);
            add_to_log($courseid, 'course', 'enrol', '../enrol/users.php?id='.$courseid, $courseid, $userid); 
        } 
    } 
}

?>
