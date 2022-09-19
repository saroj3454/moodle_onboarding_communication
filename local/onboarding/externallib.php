<?php 
global $CFG;
require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/local/onboarding/lib.php");

class local_onboarding_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */

    public static function course_enroll_parameters() {
        return new external_function_parameters(
            array(

                'contactid' => new external_value(PARAM_RAW, 'contact id'),                       
                'email' => new external_value(PARAM_RAW, 'user email'),                       
                'firstname' => new external_value(PARAM_RAW, 'first name'),                       
                'lastname' => new external_value(PARAM_RAW, 'last name'),                       
                'courseid' => new external_value(PARAM_INT, 'course id'),                       
            )
        );
    }
    public static function course_enroll_returns() {
        return new external_single_structure(
            array(                    
                'status' => new external_value(PARAM_INT, 'status of API', VALUE_DEFAULT, 0),                  
                'message' => new external_value(PARAM_RAW, 'online of course', VALUE_DEFAULT, ""),
                'provisingstate' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
                'data' => new external_single_structure(
                    array(                    
                        'passwordtoken' => new external_value(PARAM_RAW, 'level of course', VALUE_DEFAULT, ''), 
                        'passwordreseturl' => new external_value(PARAM_RAW, 'level of course', VALUE_DEFAULT, ''), 
                        'moodleuserid' => new external_value(PARAM_RAW, 'level of course', VALUE_DEFAULT, 0), 
                    )
                )
            )
        );
    }
    public static function course_enroll($contactid,$email,$firstname,$lastname,$courseid) {
        global $DB,$CFG,$USER;
        $usertoken='';
        $token = openssl_random_pseudo_bytes(16);
        $token = bin2hex($token);
        $status=0;
        $provisingstate=0;
        $passwordreseturl=$CFG->wwwroot."/local/onboarding/forgotpassword.php?token=";
        $moodleuserid=0;
        $message='';
/*        try {
        	$reqdata = new \stdClass();
        	$reqdata->apicalled = "Onboarding";
        	$reqdata->requesttime = time();
        	$reqdata->requestdata = array(
        		"contactid"=>$contactid,
        		"email"=>$email,
        		"firstname"=>$firstname,
        		"lastname"=>$lastname,
        		"courseid"=>$courseid,
        	);
        	$reqdata->requestdata = json_encode($reqdata->requestdata);
        	$DB->insert_record("api_requests", $reqdata);
        } catch (Exception $e) {
        }
*/        $response=array('status'=>0,'message'=>'','provisingstate'=>0,'data'=>array('passwordtoken'=>'', 'moodleuserid'=>0));
        $coursedata=$DB->get_record_sql("SELECT * FROM {course} WHERE id=? ",array($courseid));
        if(!empty($coursedata)){
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $userdata=$DB->get_record_sql("SELECT * FROM {user} WHERE email=? AND confirmed=?",array($email,1));
                if(!empty($userdata)){
                	if($userdata->suspended == 1){
						$DB->set_field("user", "suspended", 0, array("id"=>$userdata->id));
						$DB->set_field("user", "emailstop", 0, array("id"=>$userdata->id));	
					}            
                    //on boarding 
					/* $newuserid = $userdata->id;
					$userstatus = "Referred";					
					local_onboarding_usestatus($newuserid, $userstatus,$courseid);  */
						
                   $checkfirttimeuser=$DB->get_record_sql("SELECT * FROM {onboarding_token} where `userid`=? and status=?",array($userdata->id,0));
                    if(!empty($checkfirttimeuser)){
                    $moodleuserid=$userdata->id;
                    $userenrollment=$DB->get_record_sql("SELECT u.* FROM {user} u JOIN {user_enrolments} ue ON u.id=ue.userid JOIN {enrol} e ON e.id=ue.enrolid JOIN {course} c ON c.id=e.courseid WHERE u.email=? AND c.id=?",array($email,$courseid));
						if(!empty($userenrollment)){
							$status=1;
							$message="User already Enrolled in this course";
							$provisingstate=1;
									//$usertoken=$token;
									//self::addToUserToken($userenrollment->id,$token);

						}else{
							$userenrollment=$DB->get_record_sql("SELECT u.*,e.courseid FROM {user} u JOIN {user_enrolments} ue ON u.id=ue.userid JOIN {enrol} e ON e.id=ue.enrolid JOIN {course} c ON c.id=e.courseid WHERE u.email=?",array($email,$courseid));
							if(!empty($userenrollment)){
								$getcoursecomp = "SELECT * FROM {course_completions} WHERE userid=".$userenrollment->id ." AND course = ".$userenrollment->courseid ." AND timecompleted IS NOT NULL";
								$encourse = $DB->get_record_sql($getcoursecomp);
								if(empty($encourse)){
									$status=1;
									$message="User Already Enrolled into other course";
									$provisingstate=1;
											//$usertoken=$token;
											//self::addToUserToken($userenrollment->id,$token);                             
								}else{

									$userfieldid = $DB->get_records("user_info_field");
									foreach($userfieldid as $field){
										$arrfields[$field->id] = $field->shortname;
									}
									$arrdata[array_search('v2ConsumerWithdrawalReason',$arrfields)] = "";
									$arrdata[array_search('UserType',$arrfields)] = "Participant";
									$arrdata[array_search('ConsumerWithdrawalDate',$arrfields)] = 0;
									foreach($arrdata as $key => $data){  
									  update_user_field($userdata->id,$key,$data);
									}
									$userstatus = "Referred";						
									local_onboarding_usestatus($userdata->id, $userstatus,$courseid);




									$user_enroll_response=self::enrolCourse($courseid,$userenrollment->id);
									if($user_enroll_response['status']){
										$status=1;
										$message=$user_enroll_response['message'];
										$provisingstate=1;
												//$usertoken=$token;
											   // self::addToUserToken($userenrollment->id,$token);
									}else{
										$status=0;
										$message=$user_enroll_response['message'];
										$provisingstate=1;
												//$usertoken='';
									}
								}
							}else{					
								$userfieldid = $DB->get_records("user_info_field");
									foreach($userfieldid as $field){
										$arrfields[$field->id] = $field->shortname;
									}
									$arrdata[array_search('v2ConsumerWithdrawalReason',$arrfields)] = "";
									$arrdata[array_search('UserType',$arrfields)] = "Participant";
									$arrdata[array_search('ConsumerWithdrawalDate',$arrfields)] = 0;
									foreach($arrdata as $key => $data){  
									  update_user_field($userdata->id,$key,$data);
									}
									$userstatus = "Referred";						
									local_onboarding_usestatus($userdata->id, $userstatus,$courseid);
					
								$user_enroll_response=self::enrolCourse($courseid,$userdata->id);
								if($user_enroll_response['status']){
									$status=1;
									$message=$user_enroll_response['message'];
									$provisingstate=1;
											//$usertoken=$token;
										   // self::addToUserToken($userdata->id,$token);
										   
								}else{
									$status=0;
									$message=$user_enroll_response['message'];
									$provisingstate=1;
											//$usertoken='';
								}
							}
						}

					}else{
						$checkalltimeuser=$DB->get_record_sql("SELECT * FROM {onboarding_token} where `userid`=? and status=?",array($userdata->id,1));
						$message="First time Reset Password Not updated";
						$status=0;
						$moodleuserid=$userdata->id;
						$usertoken=$checkalltimeuser->token;
						if(!empty($usertoken)){
							//$passwordreseturl .= $usertoken;
						} else {
							$passwordreseturl = "";
						}


					}

				}else{				
						//on boarding 
					$std=new stdClass();
					$std->firstname=$firstname;
					$std->lastname=$lastname;
					$std->confirmed=1;
					$std->mnethostid=1;
					$std->password="cccb63518fe5c5a50354c81f2cccfcdb";
					$std->email=$email;
					$std->username=$email;
					$std->idnumber=$contactid;
					$std->timecreated=time();
					$userid=$DB->insert_record("user",$std);
					$moodleuserid = $userid;
					if(!empty($moodleuserid)){					
						$newuserid = $moodleuserid;
						$userstatus = "Referred";						
						local_onboarding_usestatus($newuserid, $userstatus,$courseid);					
					}
					$status=1;
					$message="User Create Sucessfully";
					$provisingstate=2;
					$usertoken=$token;
					self::addToUserToken($userid,$token);
					self::usertracking($userid,$courseid);

				}
			}else{
				$status=0;
				$message="Invalid Email";
				$provisingstate=0;
				$usertoken='';
			}
		}else{
		  $status=0;
		  $message="Invalid Course";
		  $provisingstate=0;
		  $usertoken='';
		}
		if(!empty($usertoken)){
			$passwordreseturl .= $usertoken;
		}else{
			$passwordreseturl = "";
		}
		$response=array('status'=>$status,'message'=>$message,'provisingstate'=>$provisingstate,'data'=>array('passwordtoken'=>$usertoken,'moodleuserid'=>$moodleuserid,'passwordreseturl'=>$passwordreseturl));
		return $response; 
	}

	private function usertracking($userid,$courseid){
	 global $DB, $CFG; 
	 $userdata=$DB->get_record_sql("select * from {onboarding_track} where `userid`='".$userid."' and `courseid`='".$courseid."'");
		if(empty($userdata)){
			$std=new stdClass();
			$std->userid=$userid;
			$std->courseid=$courseid;
			$std->createddate=time();
			$DB->insert_record("onboarding_track",$std);
		}

	}


	private function addToUserToken($userid,$token){
		global $DB,$USER;
		$std=new stdClass();
		$std->userid=$userid;
		$std->token=$token;
		$std->status=1;
		$std->createddate=time();
		$std->createdby=$USER->id;
		$DB->insert_record("onboarding_token",$std);
	}

	private function enrolCourse($courseid, $userid, $duration = 0) {
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
						add_to_log($courseid, 'course', 'enrol', '../enrol/users.php?id='.$courseid, $courseid, $userid); //there should be userid somewhere!
					   // mailtemplate($userid,$courseid);
						$result = array(
							'success' => true,
							'message' => 'enrolled successfully',
						);
			}else {
				$result = array(
					'success' => true,
					'message' => 'Already enrolled',
				);
			}
		}else{
			$result = array(
				'success' => false,
				'message' => 'manual enrolement not available',
			);
		}

		return $result;
	}
	
	
}
// end code