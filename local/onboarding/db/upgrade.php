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



defined('MOODLE_INTERNAL') || die();

function xmldb_local_onboarding_upgrade($oldversion) {
      global $CFG, $DB;
    $dbman = $DB->get_manager();

    $onboardingemailsettingtable = new xmldb_table('onboarding_email_setting');
    $onboardingtokentable = new xmldb_table('onboarding_token');
    $onboardingtracktable = new xmldb_table('onboarding_track');

    $emailcustomcrontable = new xmldb_table('email_custom_cron');
    $transferuserdetailstable = new xmldb_table('transfer_user_details');
    $coursetransfermappingtable = new xmldb_table('course_transfer_mapping');
    $userepisodehistorytable = new xmldb_table('user_episode_history_table');
    $userepisodelstrecordtable = new xmldb_table('user_episode_last_records');
    if($oldversion<2022090901){


		if(!$dbman->table_exists($onboardingemailsettingtable)){
			$onboardingemailsettingtable->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
			$onboardingemailsettingtable->add_field('reminder_email', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'id');
			$onboardingemailsettingtable->add_field('completesurveyscorm', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'reminder_email');
			$onboardingemailsettingtable->add_field('incompletesurveyscorm', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'completesurveyscorm');
			$onboardingemailsettingtable->add_field('inactive2week', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'incompletesurveyscorm');
			$onboardingemailsettingtable->add_field('inactive5week', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'inactive2week');		
			$onboardingemailsettingtable->add_field('created_date',XMLDB_TYPE_INTEGER,'18',null,null, null,null,'inactive5week');
			$onboardingemailsettingtable->add_field('updated_date',XMLDB_TYPE_INTEGER,'18',null,null, null,null,'created_date');
			$onboardingemailsettingtable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
	        $dbman->create_table($onboardingemailsettingtable);
		}


		if(!$dbman->table_exists($emailcustomcrontable)){
			$emailcustomcrontable->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
			$emailcustomcrontable->add_field('name', XMLDB_TYPE_CHAR,'255',null,null, null,null,'id');
			$emailcustomcrontable->add_field('templateid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'name');
			$emailcustomcrontable->add_field('userid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'templateid');
			$emailcustomcrontable->add_field('courseid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'userid');		
			$emailcustomcrontable->add_field('email_senddate', XMLDB_TYPE_CHAR,'255',null,null, null,null,'courseid');
			$emailcustomcrontable->add_field('updated_date', XMLDB_TYPE_CHAR,'255',null,null, null,null,'email_senddate');
			$emailcustomcrontable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
	        $dbman->create_table($emailcustomcrontable);
		}

		if(!$dbman->table_exists($transferuserdetailstable)){
			$transferuserdetailstable->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
			$transferuserdetailstable->add_field('userid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'id');
			$transferuserdetailstable->add_field('courseid', XMLDB_TYPE_INTEGER,'255',null,null, null,null,'userid');
			$transferuserdetailstable->add_field('previous_stage', XMLDB_TYPE_CHAR,'25',null,null, null,null,'courseid');
			$transferuserdetailstable->add_field('current_stage', XMLDB_TYPE_CHAR,'25',null,null, null,null,'previous_stage');	
			$transferuserdetailstable->add_field('created_date',XMLDB_TYPE_CHAR,'255',null,null, null,null,'current_stage');
			$transferuserdetailstable->add_field('updated_date',XMLDB_TYPE_CHAR,'255',null,null, null,null,'created_date');
			$transferuserdetailstable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
	        $dbman->create_table($transferuserdetailstable);

		if(!$dbman->table_exists($onboardingtracktable)){
			$onboardingtracktable->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
			$onboardingtracktable->add_field('userid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'id');
			$onboardingtracktable->add_field('courseid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'userid');
			$onboardingtracktable->add_field('createddate',XMLDB_TYPE_CHAR,'255',null,null, null,null,'courseid');
			$onboardingtracktable->add_field('modifieddate',XMLDB_TYPE_CHAR,'255',null,null, null,null,'createddate');
			$onboardingtracktable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
			$dbman->create_table($onboardingtracktable);
		}


		if(!$dbman->table_exists($onboardingtokentable)){
			$onboardingtokentable->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
			$onboardingtokentable->add_field('userid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'id');
			$onboardingtokentable->add_field('token', XMLDB_TYPE_CHAR,'255',null,null, null,null,'userid');
			$onboardingtokentable->add_field('status', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'token');
			$onboardingtokentable->add_field('createdby', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'status');		
			$onboardingtokentable->add_field('reminder',XMLDB_TYPE_INTEGER,'18',null,null, null,null,'createdby');
			$onboardingtokentable->add_field('modifiedby',XMLDB_TYPE_INTEGER,'18',null,null, null,null,'reminder');
			$onboardingtokentable->add_field('createddate',XMLDB_TYPE_CHAR,'255',null,null, null,null,'modifiedby');
			$onboardingtokentable->add_field('modifieddate',XMLDB_TYPE_CHAR,'255',null,null, null,null,'createddate');
			$onboardingtokentable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
			$dbman->create_table($onboardingtokentable);
		}
		
		}if(!$dbman->table_exists($onboardingemailsettingtable)){
			$onboardingemailsettingtable->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
			$onboardingemailsettingtable->add_field('reminder_email', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'id');
			$onboardingemailsettingtable->add_field('completesurveyscorm', XMLDB_TYPE_INTEGER,'255',null,null, null,null,'reminder_email');
			$onboardingemailsettingtable->add_field('incompletesurveyscorm', XMLDB_TYPE_INTEGER,'25',null,null, null,null,'completesurveyscorm');
			$onboardingemailsettingtable->add_field('inactive2week', XMLDB_TYPE_INTEGER,'25',null,null, null,null,'incompletesurveyscorm');	
			$onboardingemailsettingtable->add_field('inactive5week', XMLDB_TYPE_INTEGER,'25',null,null, null,null,'inactive2week');	
			$onboardingemailsettingtable->add_field('created_date',XMLDB_TYPE_CHAR,'255',null,null, null,null,'inactive5week');
			$onboardingemailsettingtable->add_field('updated_date',XMLDB_TYPE_CHAR,'255',null,null, null,null,'created_date');
			$onboardingemailsettingtable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
	        $dbman->create_table($onboardingemailsettingtable);
		}

		if(!$dbman->table_exists($coursetransfermappingtable)){
			$coursetransfermappingtable->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
			$coursetransfermappingtable->add_field('from_courseid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'id');
			$coursetransfermappingtable->add_field('graduates', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'from_courseid');
			$coursetransfermappingtable->add_field('completers', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'graduates');
			$coursetransfermappingtable->add_field('days', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'completers');
			$coursetransfermappingtable->add_field('created_date',XMLDB_TYPE_CHAR,'255',null,null, null,null,'days');
			$coursetransfermappingtable->add_field('updated_date',XMLDB_TYPE_CHAR,'255',null,null, null,null,'created_date');
			$coursetransfermappingtable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
			$dbman->create_table($coursetransfermappingtable);
		}

		if(!$dbman->table_exists($userepisodehistorytable)){
			$userepisodehistorytable->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
			$userepisodehistorytable->add_field('userid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'id');
			$userepisodehistorytable->add_field('usertype', XMLDB_TYPE_CHAR,'55',null,null, null,null,'userid');
			$userepisodehistorytable->add_field('withdrawalreason', XMLDB_TYPE_CHAR,'55',null,null, null,null,'usertype');
			$userepisodehistorytable->add_field('courseid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'withdrawalreason');
			$userepisodehistorytable->add_field('enrolmentid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'courseid');
			$userepisodehistorytable->add_field('fromstatus', XMLDB_TYPE_CHAR,'50',null,null, null,null,'enrolmentid');
			$userepisodehistorytable->add_field('status', XMLDB_TYPE_CHAR,'50',null,null, null,null,'fromstatus');
			$userepisodehistorytable->add_field('timecreated',XMLDB_TYPE_CHAR,'255',null,null, null,null,'status');
			$userepisodehistorytable->add_field('timeupdate',XMLDB_TYPE_CHAR,'255',null,null, null,null,'timecreated');
			$userepisodehistorytable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
			$dbman->create_table($userepisodehistorytable);
		}

if(!$dbman->table_exists($userepisodelstrecordtable)){
			$userepisodelstrecordtable->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
			$userepisodelstrecordtable->add_field('userid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'id');
			$userepisodelstrecordtable->add_field('enrolmentid', XMLDB_TYPE_INTEGER,'18',null,null, null,null,'userid');
			$userepisodelstrecordtable->add_field('status', XMLDB_TYPE_CHAR,'50',null,null, null,null,'enrolmentid');
			$userepisodelstrecordtable->add_field('timeupdated',XMLDB_TYPE_CHAR,'255',null,null, null,null,'status');
			$userepisodelstrecordtable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
			$dbman->create_table($userepisodelstrecordtable);
		}

		upgrade_plugin_savepoint(true, 2022090901, 'local', 'onboarding');
	}

	 return true;
}