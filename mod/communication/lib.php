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
 * This file contains the moodle hooks for the communication module.
 *
 * @package   mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('COMMUNICATION_SCHEDULING_SLOT', 0);
define('COMMUNICATION_SCHEDULING_COMPLETION', 1);
define('COMMUNICATION_SCHEDULING_ENROLMENT', 2);
define('COMMUNICATION_SCHEDULING_INACTIVE', 3);

define('COMMUNICATION_SCHEDULING_AFTER', 1);
define('COMMUNICATION_SCHEDULING_BEFORE', -1);

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $communication add forum instance
 * @param mod_communication_mod_form $mform
 * @return int intance id
 */
function communication_add_instance($communication, $mform = null) {
    global $CFG, $DB;

    $communication->timemodified = time();

    $communication->intro = '';
    $communication->introformat = 0;


//     echo "<pre>";
//     print_r($communication);
// die();
    $communication->id = $DB->insert_record('communication', $communication);

    return $communication->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $communication communication instance
 * @param object $mform
 * @return bool success
 */
function communication_update_instance($communication, $mform) {
    global $DB, $OUTPUT, $USER;

    $communication->timemodified = time();
    $communication->id           = $communication->instance;

    $communication->intro = '';
    $communication->introformat = 0;

    $DB->update_record('communication', $communication);

    return true;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id communication instance id
 * @return bool success
 */
function communication_delete_instance($id) {
    global $DB;

    if (!$communication = $DB->get_record('communication', array('id' => $id))) {
        return false;
    }
    if (!$cm = get_coursemodule_from_instance('communication', $communication->id)) {
        return false;
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        return false;
    }

    if (!$DB->delete_records('communication', array('id' => $communication->id))) {
        return false;
    }

    return true;
}

/**
 * Returns the information if the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function mod_communication_supports($feature) {
    switch($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return false;
        case FEATURE_PLAGIARISM:
            return false;
        default:
            return null;
    }
}

/**
 * Obtains the automatic completion state for this communication based on any conditions
 * in communication settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not. (If no conditions, then return
 *   value depends on comparison type)
 * @throws Exception
 */
function communication_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;

    // Get communication details.
    if (!($communication = $DB->get_record('communication', array('id' => $cm->instance)))) {
        throw new Exception("Can't find communication {$cm->instance}");
    }

    // If completion option is enabled, evaluate it and return true/false.
    if ($communication->completiontriggermessage) {
        return $DB->record_exists('communication_trigger', array('communicationid' => $communication->id, 'userid' => $userid));
    } else {
        // Completion option is not enabled so just return $type.
        return $type;
    }
}

/**
 * Processed provided data and triggers messages.
 *
 * @param array $rs An array of objects (records or recordset) that contains userid, communicationid, courseid and templateid.
 *                  If the object is related to a scheduler activity, it should contain slotid as well.
 */
function communication_process_recordset($rs) {
    global $CFG, $DB, $PAGE;
    static $blocks              = null,
           $templates           = null,
           $types               = null,
           $users               = array(),
           $courses             = array(),
           $coursecats          = array(),
           $courseteachers      = array(),
           $coursestudents      = array(),
           $courseslots         = array(),
           $communications      = array(),
           $slots               = array(),
           $preferrednamefield  = null,
           $preferredphonefield = null;

    require_once($CFG->dirroot . '/user/profile/lib.php');
    require_once($CFG->dirroot . '/lib/completionlib.php');

    if ($blocks === null) {
        $blocks = $DB->get_records('communication_blocks', null, 'sortorder DESC');
    }

    if ($templates === null) {
        $templates = $DB->get_records('communication_templates');
    }

    if ($types === null) {
        $types = $DB->get_records('communication_messagetypes');
    }

    if ($preferrednamefield === null) {
        if ($preferrednamefield = get_config('communication', 'preferrednamefield')) {
            if ($preferrednamefield = $DB->get_record('user_info_field', array('id' => $preferrednamefield))) {
                $preferrednamefield = 'profile_field_' . $preferrednamefield->shortname;
            }

        }
    }

    if ($preferredphonefield === null) {
        if ($preferredphonefield = get_config('communication', 'preferredphonefield')) {
            if ($preferredphonefield = $DB->get_record('user_info_field', array('id' => $preferredphonefield))) {
                $preferredphonefield = 'profile_field_' . $preferredphonefield->shortname;
            }
        }
    }

    foreach ($rs as $record) {
        $cm = get_coursemodule_from_instance('communication', $record->communicationid, $record->courseid);

        if (!\core_availability\info_module::is_user_visible($cm, $record->userid, false)) {
            continue;
        }

        if (!array_key_exists($record->templateid, $templates)) {
            continue;
        } else {
            $template = clone $templates[$record->templateid];
        }

        if (!array_key_exists($template->type, $types)) {
            continue;
        } else {
            $type = $types[$template->type];
        }

        foreach ($blocks as $block) {
            if ($block->disabled) {
                $template->fullmessage = str_replace('{' . $block->placeholder . '}', '', $template->fullmessage);
                $template->shortmessage = str_replace('{' . $block->placeholder . '}', '', $template->shortmessage);
            } else {
                $template->fullmessage = str_replace('{' . $block->placeholder . '}', $block->template, $template->fullmessage);
                $template->shortmessage = str_replace('{' . $block->placeholder . '}', $block->template, $template->shortmessage);
            }
        }

        if (empty($users[$record->userid])) {
            $users[$record->userid] = $DB->get_record('user', array('id' => $record->userid));
            profile_load_data($users[$record->userid]);
        }
        if (empty($courses[$record->courseid])) {
            $courses[$record->courseid] = $DB->get_record('course', array('id' => $record->courseid));
        }
        if (empty($communications[$record->communicationid])) {
            $communications[$record->communicationid] = $DB->get_record('communication', array('id' => $record->communicationid));
        }
        if (empty($coursecats[$record->courseid])) {
            $coursecats[$record->courseid] = $DB->get_record_sql("SELECT cc.*
                                                                    FROM {course_categories} cc
                                                                    JOIN {course} c ON c.category = cc.id
                                                                   WHERE c.id = ?", array($record->courseid));
        }
        if (empty($coursestudents[$record->courseid])) {
            $studentroles = $DB->get_fieldset_select('role','id','archetype = "student"');
            $coursestudents[$record->courseid] = get_role_users($studentroles,context_course::instance($record->courseid),true,'u.id',null,false);
        }

        if(!array_key_exists($record->userid,$coursestudents[$record->courseid])){
            $trigger = new stdClass();
            $trigger->userid = $record->userid;
            $trigger->communicationid = $record->communicationid;
            $trigger->timecreated = time();
            $trigger->slottime = null;

            $DB->insert_record('communication_trigger', $trigger);
            continue;
        }

        $variables = array(
            'PFNAME'     => '',
            'PLNAME'     => $users[$record->userid]->lastname,
            'FNAME'      => '',
            'FFNAME'     => '',
            'FLNAME'     => '',
            'FPHONE'     => '',
            'PNAME'      => $coursecats[$record->courseid]->name
        );

        if ($preferrednamefield && !empty($users[$record->userid]->{$preferrednamefield})) {
            $variables['PFNAME'] = $users[$record->userid]->{$preferrednamefield};
        } else {
            $variables['PFNAME'] = $users[$record->userid]->firstname;
        }

        $slocationtext = $slocationinfotext = $slocationhtml = $slocationinfohtml = '';

        if (!empty($record->slotid)) {
            if (empty($slots[$record->slotid])) {
                $slots[$record->slotid] = $DB->get_record('scheduler_slots', array('id' => $record->slotid));
            }
            $slot = $slots[$record->slotid];
            if (empty($users[$slot->teacherid])) {
                $users[$slot->teacherid] = $DB->get_record('user', array('id' => $slot->teacherid));
                profile_load_data($users[$slot->teacherid]);
            }

            $variables['FFNAME'] = $users[$slot->teacherid]->firstname;
            $variables['FLNAME'] = $users[$slot->teacherid]->lastname;
            $variables['FNAME'] = $users[$slot->teacherid]->firstname . ' ' . $users[$slot->teacherid]->lastname;

            if ($preferredphonefield && !empty($users[$slot->teacherid]->{$preferredphonefield})) {
                $variables['FPHONE'] = $users[$slot->teacherid]->{$preferredphonefield};
            }

            $variables['SDATEW'] = userdate($slot->starttime, '%A', $users[$record->userid]->timezone);
            $variables['SDATED'] = userdate($slot->starttime, '%d', $users[$record->userid]->timezone);
            $variables['SDATEM'] = ltrim(str_replace(array(' 0', ' '), '', userdate($slot->starttime, ' %m', $users[$record->userid]->timezone)));
            $variables['SDATEMN'] = userdate($slot->starttime, '%B', $users[$record->userid]->timezone);
            $variables['SDATEYL'] = userdate($slot->starttime, '%Y', $users[$record->userid]->timezone);
            $variables['SDATEYS'] = userdate($slot->starttime, '%y', $users[$record->userid]->timezone);
            $variables['SDATES'] = userdate($slot->starttime, '%d/%m/%y', $users[$record->userid]->timezone);
            $variables['STIME12'] = userdate($slot->starttime, '%I:%M %p', $users[$record->userid]->timezone);
            $variables['STIME24'] = userdate($slot->starttime, '%H:%M', $users[$record->userid]->timezone);
            $variables['SVENUENAME'] = $slot->venuename;
            $variables['SVENUEROOM'] = $slot->venueroom;
            $variables['SVENUEBUILDING'] = $slot->venuebuilding;
            $variables['SVENUEADDRESS1'] = $slot->venueaddress1;
            $variables['SVENUEADDRESS2'] = $slot->venueaddress2;
            $variables['SVENUEPOSTCODE'] = $slot->venuepostcode;
            $variables['SVENUESUBURB'] = $slot->venuesuburb;
            $variables['SVENUESTATE'] = $slot->venuestate;
            $variables['SVENUEINSTRUCTIONS'] = $slot->venueinstructions;
            $variables['SVENUEACCESSIBLE'] = $slot->venueaccessible == 1 ? 'Yes' : 'No';

            list ($slocationtext, $slocationinfotext, $slocationhtml, $slocationinfohtml) = communication_make_venue_texts($slot);

            $template->fullmessage = str_replace('{SLOCATION}', $slocationhtml, $template->fullmessage);
            $template->fullmessage = str_replace('{SLOCATION+}', $slocationinfohtml, $template->fullmessage);
            $template->shortmessage = str_replace('{SLOCATION}', $slocationtext, $template->shortmessage);
            $template->shortmessage = str_replace('{SLOCATION+}', $slocationinfotext, $template->shortmessage);
        } else {
            if(empty($courseteachers[$record->courseid])){
                $teacherroles = $DB->get_fieldset_select('role','id','archetype = "editingteacher"');

                //TODO: Allow this to work for THC courses where the teacher/coach is restricted to the group
                $courseteachers[$record->courseid] = get_role_users($teacherroles,context_course::instance($record->courseid),true,'u.id,u.firstname,u.lastname',null,false);
            }

            $i = 0;
            $teachercount = count($courseteachers[$record->courseid]);

            foreach($courseteachers[$record->courseid] as $teacher){
               if($i) {
                   $separator = $teachercount === $i+1 ? ' or ' : ', ';

                   $variables['FNAME'] .= $separator;
                   $variables['FFNAME'] .= $separator;
                   $variables['FLNAME'] .= $separator;
               }

               $variables['FFNAME'] .= $teacher->firstname;
               $variables['FLNAME'] .= $teacher->lastname;
               $variables['FNAME'] .= $teacher->firstname . ' ' . $teacher->lastname;

               $i++;
            }


            if(empty($courseslots[$record->courseid])){
                $schedulerid = $DB->get_field('scheduler','id',array('course' => $record->courseid),IGNORE_MULTIPLE);

                if($schedulerid){
                    $courseslots[$record->courseid] = $DB->get_records_select('scheduler_slots','schedulerid = :schedulerid',array('schedulerid' => $schedulerid));
                    var_dump($courseslots[$record->courseid]);
                } else {
                    $courseslots[$record->courseid] = array();
                }
            }

            foreach($courseslots[$record->courseid] as $slot){
                $variables['SDATEW_'.$slot->sessiondefault] = userdate($slot->starttime, '%A', $users[$record->userid]->timezone);
                $variables['SDATED_'.$slot->sessiondefault] = userdate($slot->starttime, '%d', $users[$record->userid]->timezone);
                $variables['SDATEM_'.$slot->sessiondefault] = ltrim(str_replace(array(' 0', ' '), '', userdate($slot->starttime, ' %m', $users[$record->userid]->timezone)));
                $variables['SDATEMN_'.$slot->sessiondefault] = userdate($slot->starttime, '%B', $users[$record->userid]->timezone);
                $variables['SDATEYL_'.$slot->sessiondefault] = userdate($slot->starttime, '%Y', $users[$record->userid]->timezone);
                $variables['SDATEYS_'.$slot->sessiondefault] = userdate($slot->starttime, '%y', $users[$record->userid]->timezone);
                $variables['SDATES_'.$slot->sessiondefault] = userdate($slot->starttime, '%d/%m/%y', $users[$record->userid]->timezone);
                $variables['STIME12_'.$slot->sessiondefault] = userdate($slot->starttime, '%I:%M %p', $users[$record->userid]->timezone);
                $variables['STIME24_'.$slot->sessiondefault] = userdate($slot->starttime, '%H:%M', $users[$record->userid]->timezone);
                $variables['SVENUENAME_'.$slot->sessiondefault] = $slot->venuename;
                $variables['SVENUEROOM_'.$slot->sessiondefault] = $slot->venueroom;
                $variables['SVENUEBUILDING_'.$slot->sessiondefault] = $slot->venuebuilding;
                $variables['SVENUEADDRESS1_'.$slot->sessiondefault] = $slot->venueaddress1;
                $variables['SVENUEADDRESS2_'.$slot->sessiondefault] = $slot->venueaddress2;
                $variables['SVENUEPOSTCODE_'.$slot->sessiondefault] = $slot->venuepostcode;
                $variables['SVENUESUBURB_'.$slot->sessiondefault] = $slot->venuesuburb;
                $variables['SVENUESTATE_'.$slot->sessiondefault] = $slot->venuestate ? $slot->venuestate : 'QLD';
                $variables['SVENUEINSTRUCTIONS_'.$slot->sessiondefault] = $slot->venueinstructions;
                $variables['SVENUEACCESSIBLE_'.$slot->sessiondefault] = $slot->venueaccessible == 1 ? 'Yes' : 'No';

                $slocationtext = $slocationinfotext = $slocationhtml = $slocationinfohtml = '';
                list ($slocationtext, $slocationinfotext, $slocationhtml, $slocationinfohtml) = communication_make_venue_texts($slot);

                $template->fullmessage = str_replace('{SLOCATION_'.$slot->sessiondefault.'}', $slocationhtml, $template->fullmessage);
                $template->fullmessage = str_replace('{SLOCATION+_'.$slot->sessiondefault.'}', $slocationinfohtml, $template->fullmessage);
                $template->shortmessage = str_replace('{SLOCATION_'.$slot->sessiondefault.'}', $slocationtext, $template->shortmessage);
                $template->shortmessage = str_replace('{SLOCATION+_'.$slot->sessiondefault.'}', $slocationinfotext, $template->shortmessage);
            }
        }

        /*
        * MC-19: Retrieved all user_info_fields
        * Matched with the variables array keys for profilefields with case-insensitive and mapped the values from the user profile and replaced them in templates
        */


        $fieldrecords = $DB->get_records('user_info_field');

        $user_info_fields = $DB->get_records_select_menu('user_info_field', 'datatype not in ("dqrules","addresslookup")', null, 'sortorder', 'shortname,id');

        $profile_variable_prefix= 'PROFILEFIELD_';

        $profile_variable_regex = '/{'.$profile_variable_prefix.'[\w+}]*}/i';
        preg_match_all($profile_variable_regex, $template->fullmessage, $fullmessage_matches);
        preg_match_all($profile_variable_regex, $template->shortmessage, $shortmessage_matches);


        $profile_variables = preg_replace(array('/{/','/}/'),'',array_unique(array_merge($fullmessage_matches[0],$shortmessage_matches[0])));

        foreach ($user_info_fields as $shortname => $fieldid) {

            $indexVal = array_search(strtolower($profile_variable_prefix.$shortname), array_map('strtolower', $profile_variables));

            if($indexVal !== false){
                require_once($CFG->dirroot . '/user/profile/field/' . $fieldrecords[$fieldid]->datatype . '/field.class.php');
                $fieldclass= 'profile_field_' . $fieldrecords[$fieldid]->datatype;
                $fieldobject = new $fieldclass($fieldid, $record->userid);
                $variables[$profile_variables[$indexVal]] = trim(($fieldrecords[$fieldid]->datatype == 'checkbox') ? ((strpos($fieldobject->display_data(), 'checked')) ? 'Yes' : 'No') : ($fieldobject->display_data()));

            }
        }

        foreach ($variables as $key => $value) {
            $template->fullmessage = str_ireplace('{' . $key . '}', $value, $template->fullmessage);
            $template->shortmessage = str_ireplace('{' . $key . '}', $value, $template->shortmessage);
        }


        /*
        * MC-19: Finished mapping of all user_info_fields to variables with prefix and replaced the keys in template with values
        */


        if ($template->includecss) {
            //TODO: This css should be inlined if it is to be supported in email
            $urls = $PAGE->theme->css_urls($PAGE);
            $htmlhead = '<head>';
            foreach ($urls as $url) {
                $htmlhead .= '<link rel="stylesheet" type="text/css" href="' . $url->out(false) . '" />';
            }
            $htmlhead .= '</head>';
        }
	//var_dump($variables);

// -- ------------------------------------------------
//debug_log("communication_process_recordset :: variables : ", $variables, true, "communication_variables");
// -- -----------------------------------------------

        $message = new \core\message\message();
        $message->courseid = $record->courseid;
        $message->component = 'mod_communication';
        $message->name = "type_{$type->id}";
        $message->userfrom = core_user::get_noreply_user();
        $message->userto = $users[$record->userid];
        $message->subject = $template->subjectline;

        $message->fullmessage = $template->fullmessage;
        $message->fullmessageformat = $template->fullmessageformat;
        $message->fullmessagehtml = format_text($template->fullmessage, $template->fullmessageformat, array('trusted' => true,
                                                                                                            'noclean' => true));

        if ($template->includecss) {
            $message->fullmessagehtml = '<html>' . $htmlhead . '<body>' . $message->fullmessagehtml . '</body></html>';
        }

        $message->smallmessage = $template->shortmessage;
        $message->notification = 1;

// -- ------------------------------------------------
//debug_log("communication_process_recordset :: message_send :: message: ", $message, true, "communication_message_send");
// -- -----------------------------------------------

	$messageid = message_send($message);

        if ($messageid) {
            //send mail cc user 
            $ccemaillist = get_config('local_onboarding', 'ccemaillist');
            $ccemail = explode(",",$ccemaillist);
            //moodle email 
            foreach ($ccemail as $ccemails) {                           
                if(empty($ccemails)){continue;}
                $emailuser = (object)array();
                $emailuser->email = trim($ccemails," ");
                $emailuser->firstname = $variables->firstname;
                $emailuser->lastname= $variables->lastname;
                $emailuser->maildisplay = true;
                $emailuser->mailformat = 1; // 0 (zero) text-only emails, 1 (one) for HTML/Text emails.
                $emailuser->id = 1;
                $emailuser->firstnamephonetic = false;
                $emailuser->lastnamephonetic = false;
                $emailuser->middlename = false;
                $emailuser->username = false;
                $emailuser->alternatename = false;
                $mail = email_to_user($emailuser,$message->userfrom, $message->subject, $message->fullmessagehtml, $message->fullmessagehtml);
            }
            //send mail cc user


            $trigger = new stdClass();
            $trigger->userid = $record->userid;
            $trigger->communicationid = $record->communicationid;
            $trigger->messageid = $messageid;
            $trigger->timecreated = time();
            $trigger->slottime = property_exists($record,'slottime') ? $record->slottime : null;

            $trigger->id = $DB->insert_record('communication_trigger', $trigger);

            $data = array(
                'objectid' => $trigger->id,
                'relateduserid' => $trigger->userid,
                'context' => \context_course::instance($record->courseid),
                'other' => array(
                    'shortmessage' => $template->shortmessage,
                    'fullmessage' => $template->fullmessage
                ),
            );

            // Trigger event.
            mod_communication\event\message_triggered::create($data)->trigger();

            // Update completion state.
            $completion = new completion_info($courses[$record->courseid]);
            if ($completion->is_enabled($cm) && $communications[$record->communicationid]->completiontriggermessage) {
                $completion->update_state($cm, COMPLETION_COMPLETE, $record->userid);
            }
        }
    }
}

function communication_make_venue_texts($slot) {
    $venuetext = new stdClass();
    $venuetext->room = '';
    $venuetext->building = '';
    $venuetext->address1 = '';
    $venuetext->address2 = '';
    $venuetext->postcode = '';
    $venuetext->suburb = '';
    $venuetext->instructions = '';

    $venuehtml = new stdClass();
    $venuehtml->room = '';
    $venuehtml->building = '';
    $venuehtml->address1 = '';
    $venuehtml->address2 = '';
    $venuehtml->postcode = '';
    $venuehtml->suburb = '';
    $venuehtml->instructions = '';

    if (!empty($slot->venueroom)) {
        $venuetext->room = get_string('venueroomtext', 'communication', $slot->venueroom);
        $venuehtml->room = get_string('venueroomhtml', 'communication', $slot->venueroom);
    }
    if (!empty($slot->venuebuilding)) {
        $venuetext->building = get_string('venuebuildingtext', 'communication', $slot->venuebuilding);
        $venuehtml->building = get_string('venuebuildinghtml', 'communication', $slot->venuebuilding);
    }
    if (!empty($slot->venueaddress1)) {
        $venuetext->address1 = get_string('venueaddress1text', 'communication', $slot->venueaddress1);
        $venuehtml->address1 = get_string('venueaddress1html', 'communication', $slot->venueaddress1);
    }
    if (!empty($slot->venueaddress2)) {
        $venuetext->address2 = get_string('venueaddress2text', 'communication', $slot->venueaddress2);
        $venuehtml->address2 = get_string('venueaddress2html', 'communication', $slot->venueaddress2);
    }
    if (!empty($slot->venuepostcode)) {
        $venuetext->postcode = get_string('venuepostcodetext', 'communication', $slot->venuepostcode);
        $venuehtml->postcode = get_string('venuepostcodehtml', 'communication', $slot->venuepostcode);
    }
    if (!empty($slot->venuesuburb)) {
        $venuetext->suburb = get_string('venuesuburbtext', 'communication', $slot->venuesuburb);
        $venuehtml->suburb = get_string('venuesuburbhtml', 'communication', $slot->venuesuburb);
    }

    $slocationtext = get_string('venuelocationformattext', 'communication', $venuetext);
    $slocationhtml = get_string('venuelocationformathtml', 'communication', $venuehtml);

    while (core_text::strpos($slocationtext, "\n\n") !== false) {
        $slocationtext = str_replace("\n\n", "\n", $slocationtext);
    }

    $slocationhtml = str_replace(array('<br/>', '<br />'), '<br>', $slocationhtml);
    while (core_text::strpos($slocationhtml, '<br><br>') !== false) {
        $slocationhtml = str_replace('<br><br>', '<br>', $slocationhtml);
    }

    if (!empty($slot->venueinstructions)) {
        $venuetext->address = $slocationtext;
        $venuetext->instructions = get_string('venueinstructionstext', 'communication', $slot->venueinstructions);
        $slocationinfotext = get_string('venuelocationinfoformattext', 'communication', $venuetext);

        $venuehtml->address = $slocationhtml;
        $venuehtml->instructions = get_string('venueinstructionshtml', 'communication', $slot->venueinstructions);
        $slocationinfohtml = get_string('venuelocationinfoformathtml', 'communication', $venuehtml);
    } else {
        $slocationinfotext = $slocationtext;
        $slocationinfohtml = $slocationhtml;
    }

    return array($slocationtext, $slocationinfotext, $slocationhtml, $slocationinfohtml);
}
function inactivedataslot($data){
 foreach ($data as $keydata) {
    inactiveenrolleduser($keydata);
 }
}
function inactiveenrolleduser($data){
     global $USER, $CFG, $DB,$PAGE;
     static $blocks              = null,
           $templates           = null,
           $types               = null,
           $users               = array(),
           $courses             = array(),
           $coursecats          = array(),
           $courseteachers      = array(),
           $coursestudents      = array(),
           $courseslots         = array(),
           $communications      = array(),
           $slots               = array(),
           $preferrednamefield  = null,
           $preferredphonefield = null;
			require_once($CFG->dirroot . '/user/profile/lib.php');
            require_once("$CFG->dirroot/local/onboarding/lib.php");
			$pasttime=strtotime("-".$data->schedulingduration." sec");
			$userrecord=$DB->get_records_sql("SELECT u.id,u.username FROM {user} as u INNER JOIN {role_assignments} as rs on rs.userid=u.id INNER JOIN {context} as ct on rs.contextid=ct.id WHERE ct.instanceid='".$data->course."' AND rs.roleid='5' AND u.emailstop!='1' AND u.confirmed='1' AND u.suspended='0' AND u.deleted='0' AND u.mnethostid='1' AND u.lastlogin < '".$pasttime."' AND u.lastaccess < '".$pasttime."' AND u.lastlogin !='0'");
            // echo  "SELECT u.id,u.username FROM {user} as u INNER JOIN {role_assignments} as rs on rs.userid=u.id INNER JOIN {context} as ct on rs.contextid=ct.id WHERE ct.instanceid='".$data->course."' and rs.roleid='5' and u.emailstop!='1' and u.confirmed='1' and u.suspended='0' and u.deleted='0' and u.mnethostid='1' and  u.lastlogin < '".$pasttime."' and u.timecreated < '".$pasttime."'";

			$template = $DB->get_record('communication_templates',array('id'=>$data->template));
			if ($template->includecss) {
			    //TODO: This css should be inlined if it is to be supported in email
			    $urls = $PAGE->theme->css_urls($PAGE);
			    $htmlhead = '<head>';
			    foreach ($urls as $url) {
			        $htmlhead .= '<link rel="stylesheet" type="text/css" href="' . $url->out(false) . '" />';
			    }
			    $htmlhead .= '</head>';
			} 
     foreach($userrecord as $userdata){

    	$notificationavl=$DB->get_record_sql("SELECT * FROM {communication_trigger} where userid=? and communicationid=?",array($userdata->id,$data->id));
  		if(empty($notificationavl)){
			if (empty($users[$userdata->id])) {
			    $users[$userdata->id] = $DB->get_record('user', array('id' => $userdata->id));
			    profile_load_data($users[$userdata->id]);
			}
			if ($preferrednamefield === null) {
				if ($preferrednamefield = get_config('communication', 'preferrednamefield')) {
					if ($preferrednamefield = $DB->get_record('user_info_field', array('id' => $preferrednamefield))) {
				    $preferrednamefield = 'profile_field_' . $preferrednamefield->shortname;
				    }
			    }
			}     
			$variables = array(
			    'PFNAME'     => $users[$userdata->id]->firstname,
			    'PLNAME'     => $users[$userdata->id]->lastname,
			    'FNAME'      => $users[$userdata->id]->firstname.' '.$users[$userdata->id]->lastname,
			    'FFNAME'     => '',
			    'FLNAME'     => '',
			    'FPHONE'     => '',
			    'PNAME'      => $coursecats[$data->course]->name
			);

			foreach ($variables as $key => $value) {
			    $template->fullmessage = str_ireplace('{' . $key . '}', $value, $template->fullmessage);
			    $template->shortmessage = str_ireplace('{' . $key . '}', $value, $template->shortmessage);
			}
			$blocks = $DB->get_records('communication_blocks', null, 'sortorder DESC');
			foreach ($blocks as $block) {
			    if ($block->disabled) {
			        $template->fullmessage = str_replace('{' . $block->placeholder . '}', '', $template->fullmessage);
			    } else {
			        $template->fullmessage = str_replace('{' . $block->placeholder . '}', $block->template, $template->fullmessage);
			    }
			}
			$message = new \core\message\message();
			$message->courseid = $data->course;
			$message->component = 'mod_communication';
			$message->name = "type_6";
			$message->userfrom = core_user::get_noreply_user();
			$message->userto = $userdata->id;
			$message->subject = $template->subjectline;
			$message->fullmessage = $template->fullmessage;
			$message->fullmessageformat = $template->fullmessageformat;
			$message->fullmessagehtml = format_text($template->fullmessage, $template->fullmessageformat, array('trusted' => true,
			                                                                                                'noclean' => true));
			if ($template->includecss) {
			$message->fullmessagehtml = '<html>' . $htmlhead . '<body>' . $message->fullmessagehtml . '</body></html>';
			}
			$message->smallmessage = $template->shortmessage;
			$message->notification = 1;
            echo "name" . $users[$userdata->id]->firstname;
            echo "message id";
			echo $messageid = message_send($message);
            $twoweekepisode = $DB->get_record_sql("SELECT * FROM {communication_templates} WHERE id = ? AND name LIKE ?", array($data->template, "%2 weeks"));
            echo "template ". $data->template;
            if($twoweekepisode){
                $coursecompstatus = $DB->get_record_sql("SELECT * FROM {course_completions}  WHERE userid = ? AND course = ? AND timecompleted IS NOT NULL", array($userdata->id,$data->course));
                if(empty($coursecompstatus)){
                    echo $userstatus = "Incomplete and 2+ weeks inactive";
                    local_onboarding_usestatus($userdata->id, $userstatus,$data->course);

                }
            }

            $fiveweekepisode = $DB->get_record_sql("SELECT * FROM {communication_templates} WHERE id = ? AND name LIKE ?", array($data->template, "%5 weeks"));
            if($fiveweekepisode){
                $coursecompstatus = $DB->get_record_sql("SELECT * FROM {course_completions}  WHERE userid = ? AND course = ? AND timecompleted IS NOT NULL", array($userdata->id,$data->course));
                if(empty($coursecompstatus)){ 
                    echo $userstatus = "Incomplete and 5+ weeks inactive";
                    local_onboarding_usestatus($userdata->id, $userstatus,$data->course);

                }

            } 
			if ($messageid) { 
                //send mail cc user 
                $ccemaillist = get_config('local_onboarding', 'ccemaillist');
                $ccemail = explode(",",$ccemaillist);
                //moodle email 
                foreach ($ccemail as $ccemails) {                           
                    if(empty($ccemails)){continue;}
                    $emailuser = (object)array();
                    $emailuser->email = trim($ccemails);
                    $emailuser->firstname = $users[$userdata->id]->firstname;
                    $emailuser->lastname= $users[$userdata->id]->lastname;
                    $emailuser->maildisplay = true;
                    $emailuser->mailformat = 1; // 0 (zero) text-only emails, 1 (one) for HTML/Text emails.
                    $emailuser->id = 1;
                    $emailuser->firstnamephonetic = false;
                    $emailuser->lastnamephonetic = false;
                    $emailuser->middlename = false;
                    $emailuser->username = false;
                    $emailuser->alternatename = false;
                    $emailuser->email;
                    $mail = email_to_user($emailuser,$message->userfrom, $message->subject, $message->fullmessagehtml, $message->fullmessagehtml);
                    if($mail){
                        echo "mail send" .$emailuser->email; 
                    }else{
                        echo "email failed";
                    }
                    //die;
                }
                //send mail cc user 
        
			    $trigger = new stdClass();
			    $trigger->userid = $userdata->id;
			    $trigger->communicationid = $data->id;
			    $trigger->messageid = $messageid;
			    $trigger->timecreated = time();
			    $trigger->slottime = property_exists($record,'slottime') ? $data->slottime : null;

			    $trigger->id = $DB->insert_record('communication_trigger', $trigger);

			    $fulldata = array(
			        'objectid' => $trigger->id,
			        'relateduserid' => $trigger->userid,
			        'context' => \context_course::instance($data->course),
			        'other' => array(
			            'shortmessage' => $template->shortmessage,
			            'fullmessage' => $template->fullmessage
			        ),
			    );
			    // Trigger event.
			    mod_communication\event\message_triggered::create($fulldata)->trigger();
			    echo "send message";
			}
		}
    }   
}
