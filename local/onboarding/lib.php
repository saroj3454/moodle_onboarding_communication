<?php
function local_onboarding_extend_navigation(global_navigation $navigation) {
 global $USER, $CFG, $DB;
    $settings = new stdClass;
    $settings->enabled = 1;
    $settings->flatenabled = 1;
    if(is_siteadmin()){
		$allmenu = array();
		/*$settings->menuitems = get_string('menu_one', 'local_onboarding').' | '.new moodle_url('/course/view.php?id=8')."
		".get_string('menu_two', 'local_onboarding').' | '.new moodle_url('/course/view.php?id=9')."
		".get_string('menu_three', 'local_onboarding').' | '.new moodle_url('/cohort/assign.php?id=1'); */
		$cohortdata = $DB->get_record('cohort', array('idnumber' => 'GRADUATES'));
        $cohortdata1 = $DB->get_record('cohort', array('idnumber' => 'Non-Completers'));
		$settings->menuitems = get_string('menu_three', 'local_onboarding').' | '.new moodle_url('/cohort/assign.php?id='.$cohortdata->id.'') ."
". get_string('menu_forth', 'local_onboarding').' | '.new moodle_url('/cohort/assign.php?id='.$cohortdata1->id.'');

$settings->pix = "i/user";
	}
	//print_r($settings->menuitems);
    if (!empty($settings->menuitems) && $settings->enabled) {
        $menu = new custom_menu($settings->menuitems,$settings->pix, current_language());
        if ($menu->has_children()) {
            foreach ($menu->get_children() as $item) {
				//print_r($item);
                onboarding_custom_menu_item($item, 0, "i/user", $settings->flatenabled);
            }
        }
    }
} 

/**
 * ADD custom menu in navigation recursive childs node
 * Is like render custom menu items
 *
 * @param custom_menu_item $menunode {@link custom_menu_item}
 * @param int $parent is have a parent and it's parent itself
 * @param object $pmasternode parent node
 * @param int $flatenabled show master node in boost navigation
 * @return void
 */
function onboarding_custom_menu_item(custom_menu_item $menunode, $parent, $pmasternode, $flatenabled) {
    global $PAGE, $CFG;

    static $submenucount = 0;

    if ($menunode->has_children()) {
        $submenucount++;
        $url = $CFG->wwwroot;
        if ($menunode->get_url() !== null) {
            $url = new moodle_url($menunode->get_url());
        } else {
            $url = null;
        }
        if ($parent > 0) {
            $masternode = $pmasternode->add(local_onboarding_get_string($menunode->get_text()),
                                            $url, navigation_node::TYPE_CONTAINER);
            $masternode->title($menunode->get_title());
        } else {
            $masternode = $PAGE->navigation->add(local_onboarding_get_string($menunode->get_text()),
                                            $url, navigation_node::TYPE_CONTAINER);
            $masternode->title($menunode->get_title());
            if ($flatenabled) {
                $masternode->isexpandable = true;
                $masternode->showinflatnavigation = true;
            }
        }
        foreach ($menunode->get_children() as $menunode) {
            navigation_custom_menu_item($menunode, $submenucount, $masternode, $flatenabled);
        }
    } else {
        $url = $CFG->wwwroot;
        if ($menunode->get_url() !== null) {
            $url = new moodle_url($menunode->get_url());
        } else {
            $url = null;
        }
        if ($parent) {
            $childnode = $pmasternode->add(local_onboarding_get_string($menunode->get_text()),
                                        $url, navigation_node::TYPE_CUSTOM);
            $childnode->title($menunode->get_title());
        } else {
            $masternode = $PAGE->navigation->add(local_onboarding_get_string($menunode->get_text()),
                                        $url, navigation_node::TYPE_CONTAINER);
            $masternode->title($menunode->get_title());
            if ($flatenabled) {
                $masternode->isexpandable = true;
                $masternode->showinflatnavigation = true;
            }
        }
    }

    return true;
}

/**
 * Translate Custom Navigation Nodes
 *
 * This function is based in a short peace of Moodle code
 * in  Name processing on user_convert_text_to_menu_items.
 *
 * @param string $string text to translate.
 * @return string
 */
function local_onboarding_get_string($string) {
    $title = $string;
    $text = explode(',', $string, 2);
    if (count($text) == 2) {
        // Check the validity of the identifier part of the string.
        if (clean_param($text[0], PARAM_STRINGID) !== '') {
            // Treat this as atext language string.
            $title = get_string($text[0], $text[1]);
        }
    }
    return $title;
}

// user status episode
function local_onboarding_usestatus($userid, $status, $courseid=0, $statuscheck=""){
    global $DB;
    $finaledata = new stdCLass();
    $finaledata->usertype = "";
    $finaledata->withdrawalreason = "";
    $finaledata->fromstatus = "";
    $finaledata->userid = $userid;
    $finaledata->status = $status;
    $finaledata->courseid = $courseid; 
    $finaledata->timecreated = time(); 
    $finaledata->enrolmentid = 0;
    $ConsumerDigitalStatus = array("Referred","Enrolled","Incomplete","Complete","Withdrawn");

    //echo "SELECT f.shortname, d.data FROM {user_info_field} f LEFT JOIN {user_info_data} d on d.fieldid = f.id WHERE d.userid=? and f.shortname in ('ConsumerUserType', 'withdrawalreason')", array($userid);

    $profiledata = $DB->get_records_sql("SELECT f.shortname, d.data FROM {user_info_field} f LEFT JOIN {user_info_data} d on d.fieldid = f.id WHERE d.userid=? and f.shortname in ('UserType', 'ConsumerWithdrawalReason')", array($userid));

    if(isset($profiledata['UserType'])){
        $finaledata->usertype = $profiledata['UserType']->data;
    }else{
        $getdefultdata = $DB->get_record_sql("SELECT * FROM {user_info_field} WHERE shortname =?",array("UserType"));
        $finaledata->usertype = $getdefultdata->defaultdata;
    }
    if(isset($profiledata['ConsumerWithdrawalReason'])){
        $finaledata->withdrawalreason = $profiledata['ConsumerWithdrawalReason']->data;
    }else{
        $getdefultdata = $DB->get_record_sql("SELECT * FROM {user_info_field} WHERE shortname =?",array("ConsumerWithdrawalReason"));
        $finaledata->withdrawalreason = $getdefultdata->defaultdata;
    }   

    $enrolmentdata = $DB->get_record_sql("SELECT ue.id,e.courseid FROM {user_enrolments} ue JOIN {enrol} e on e.id=ue.enrolid WHERE ue.userid = ?",array($userid));

    if($enrolmentdata){
        $finaledata->courseid = $enrolmentdata->courseid;
        $finaledata->enrolmentid = $enrolmentdata->id;
    }
    $olddata = $DB->get_record_sql("SELECT * from {user_episode_history_table} WHERE userid=? and courseid=? and (enrolmentid=? or enrolmentid=0) order by id desc LIMiT 0,1", array($userid, $finaledata->courseid, $finaledata->enrolmentid));
    if($olddata){
        $finaledata->fromstatus = $olddata->status;
    }
        $getepisodelastrecords = $DB->get_record_sql("SELECT * FROM {user_episode_last_records} WHERE userid = ? and courseid = ? and enrolmentid = ?", array($userid, $finaledata->courseid, $finaledata->enrolmentid));

    if(!empty($statuscheck)){
        if($finaledata->fromstatus!=$statuscheck){
            return false;
        }
    }

    if(empty($getepisodelastrecords)){

        $lastrecors = new stdCLass();
        $lastrecors->userid = $userid;
        $lastrecors->courseid = $finaledata->courseid; 
        $lastrecors->enrolmentid = $finaledata->enrolmentid;
        $lastrecors->status = $finaledata->status;
        $lastrecors->timeupdated = time();
        $DB->insert_record("user_episode_last_records", $lastrecors);
    }else{    
        $updatelastrecors = new stdCLass();
        $updatelastrecors->id = $getepisodelastrecords->id;
        $updatelastrecors->status = $finaledata->status;
        $updatelastrecors->timeupdated = time();
        $DB->update_record("user_episode_last_records",$updatelastrecors);

    }
    if(in_array($finaledata->status, $ConsumerDigitalStatus)){
        $get_consumerdigitalstatus =  $DB->get_record_sql("SELECT * FROM {user_info_field} WHERE shortname='ConsumerDigitalStatus'");
        $consumerdigitalstatus_id = $get_consumerdigitalstatus->id;
        update_user_field($userid,$consumerdigitalstatus_id,$finaledata->status); 

    }
    return $DB->insert_record("user_episode_history_table", $finaledata);

}


function update_user_field($userid,$key,$data){ 
    global $DB; 
    if($DB->record_exists('user_info_data',array('userid'=>$userid,'fieldid'=>$key))){
             $sql = "UPDATE mdl_user_info_data SET `data`= '".$data."' WHERE userid=$userid AND fieldid=$key";
           $res =$DB->execute($sql);
     }else{
        $stdobj = new stdClass();
        $stdobj->userid=$userid;
        $stdobj->fieldid = $key;
        $stdobj->data = $data;
       // print_r($stdobj);
        $DB->insert_record('user_info_data',$stdobj);
     } 
  }