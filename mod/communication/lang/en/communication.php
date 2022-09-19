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
 * Strings for component 'communication', language 'en'.
 *
 * @package   mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['activitycompletion'] = 'Activity completion';
$string['after'] = 'After';
$string['before'] = 'Before';
$string['blockdeleted'] = 'The block "{$a}" has been deleted.';
$string['blockdisabled'] = 'The block "{$a}" has been disabled.';
$string['blockenabled'] = 'The block "{$a}" has been enabled.';
$string['blockisdisabled'] = 'This block is disabled. You cannot edit a disabled block.';
$string['blockname'] = 'Block Name';
$string['chooseactivity'] = 'Select activity...';
$string['choosescheduler'] = 'Select scheduler...';
$string['chooseslot'] = 'Select slot...';
$string['communication:addinstance'] = 'Add a new communication activity';
$string['communicationname'] = 'Communication Name';
$string['completiontrigger'] = 'Require scheduled message being triggered';
$string['deleteblockconfirm'] = 'Delete this block?';
$string['deletetemplateconfirm'] = 'Delete this template?';
$string['deletetypeconfirm'] = 'Delete this message type?';
$string['description'] = 'Description';
$string['duplicateblock'] = 'Another block with this name exists. Please choose a different name for this block.';
$string['duplicateplaceholder'] = 'Another block with this variable name exists. Please choose a different variable for this block.';
$string['duplicatetemplate'] = 'Another template with this name exists. Please choose a different name for this template.';
$string['duplicatetype'] = 'Another message type with this name exists. Please choose a different name for this message type.';
$string['durationempty'] = 'Please specify a time here.';
$string['editblock'] = 'Edit reusable block';
$string['edittemplate'] = 'Edit communication template';
$string['edittype'] = 'Edit message type';
$string['enrolmentdate'] = 'Enrolment date';
$string['eventmessagetriggered'] = 'A communication message is triggered.';
$string['filter'] = 'Filter by Message Type';
$string['generalsettings'] = 'General settings';
$string['giveupthreshold'] = 'Give up threshold';
$string['messagelong'] = 'Long Message';
$string['messagelong_help'] = 'The following variables are available:

<strong>{PFNAME}:</strong> Participant First Name<br />
<strong>{PLNAME}:</strong> Participant Last Name<br />
<br />
<strong>{FNAME}:</strong> Facilitator\'s Full Name<br />
<strong>{FFNAME}:</strong> Facilitator First Name<br />
<strong>{FLNAME}:</strong> Facilitator Last Name<br />
<strong>{FPHONE}:</strong> Facilitator Phone<br />
<strong>{PNAME}:</strong> Provider Name<br />
<br />
<strong>{SDATEW}:</strong> Day of Week (e.g. Monday)<br />
<strong>{SDATED}:</strong> Day (e.g. 30)<br />
<strong>{SDATEM}:</strong> Month (e.g. 5)<br />
<strong>{SDATEMN}:</strong> Month Name (e.g. March)<br />
<strong>{SDATEYL}:</strong> Year Long (e.g. 2019)<br />
<strong>{SDATEYS}:</strong>  Year Short (e.g. 19)<br />
<strong>{SDATES}:</strong>  Short Date (e.g. 30/05/19)<br />
<strong>{STIME12}:</strong> 12 Hour (e.g. 1:00 PM)<br />
<strong>{STIME24}:</strong> 24 Hour (e.g. 13:00)<br />
<strong>{SLOCATION}:</strong> Session Location (formatted across multiple lines)<br />
<strong>{SLOCATION+}:</strong> Session Location with Additional Instructions (formatted across multiple lines)<br />
<strong>{SVENUENAME}:</strong> Venue Name<br />
<strong>{SVENUEROOM}:</strong> Venue Room<br />
<strong>{SVENUEBUILDING}:</strong> Venue Building<br />
<strong>{SVENUEADDRESS1}:</strong> Venue Address 1<br />
<strong>{SVENUEADDRESS2}:</strong> Venue Address 2<br />
<strong>{SVENUEPOSTCODE}:</strong> Venue Postcode<br />
<strong>{SVENUESUBURB}:</strong> Venue Suburb<br />
<strong>{SVENUESTATE}:</strong> Venue State<br />
<strong>{SVENUEINSTRUCTIONS}:</strong> Venue Additional Instructions <br />
<strong>{SVENUEACCESSIBLE}:</strong> Venue Universally Accessible (Yes or No)<br />
<br />
<strong>{PROFILEFIELD_######}:</strong> Profile field value, where ### is replaced by the unique field shortname, for example:<br />
<strong>{PROFILEFIELD_ConsumerGender}:</strong> Gender<br />
<strong>{PROFILEFIELD_ConsumerDOB}:</strong> Date of Birth';
$string['messagescheduling'] = 'Send the message';
$string['messageshort'] = 'Short Message';
$string['messageshort_help'] = $string['messagelong_help'];
$string['messagesubject'] = 'Message subject';
$string['messagetype'] = 'Message Type';
$string['messagetypes'] = 'Message Types';
$string['modulename'] = 'Communication';
$string['modulenameplural'] = 'Communications';
$string['name'] = 'Name';
$string['newblock'] = 'Add new block';
$string['newtemplate'] = 'Add new template';
$string['newtype'] = 'Add new type';
$string['noblocks'] = 'No blocks have been created yet';
$string['notapplicable'] = 'N/A';
$string['notemplates'] = 'No templates have been created yet';
$string['notypes'] = 'No communication types have been created yet';
$string['placeholder'] = 'Variable';
$string['pluginadministration'] = 'Communication administration';
$string['pluginname'] = 'Communication';
$string['preview'] = 'Preview';
$string['preferrednamefield'] = 'Preferred name field';
$string['preferrednamefield_help'] = 'Preferred name field';
$string['preferrednamefield_help'] = 'The custom profile field that its value is going to override user\'s name in communications';
$string['preferredphonefield'] = 'Preferred phone field';
$string['preferredphonefield_help'] = 'The custom profile field that its value is going to override user\'s phone number in communications';
$string['profilefielddisplay'] = '{$a->name} ({$a->shortname})';
$string['resendonslotchange'] = 'Resend if scheduler slot changes';
$string['reservedname'] = 'This is a reserved name. Please use another name.';
$string['reusableblocks'] = 'Reusable Blocks';
$string['schedulerslot'] = 'Scheduler slot';
$string['scheduling'] = 'Scheduling';
$string['tasktriggermessages'] = 'Trigger communication templates';
$string['template'] = 'Template';
$string['template1'] = 'Template 1';
$string['template2'] = 'Template 2';
$string['templatedeleted'] = 'The template "{$a}" has been deleted.';
$string['templatedisabled'] = 'The template "{$a}" has been disabled.';
$string['templateenabled'] = 'The template "{$a}" has been enabled.';
$string['templateisdisabled'] = 'This template is disabled. You cannot edit a disabled template.';
$string['templatename'] = 'Template Name';
$string['templates'] = 'Communication templates';
$string['themecss'] = 'Include css from themes';
$string['type'] = 'Type';
$string['typedeleted'] = 'The message type "{$a}" has been deleted.';
$string['typedisabled'] = 'The message type "{$a}" has been disabled.';
$string['typeenabled'] = 'The message type "{$a}" has been enabled.';
$string['typeisdisabled'] = 'This message type is disabled. You cannot edit a disabled message type.';
$string['typename'] = 'Message Type Name';
$string['venueaddress1html'] = '{$a}';
$string['venueaddress1text'] = '{$a}';
$string['venueaddress2html'] = '{$a}';
$string['venueaddress2text'] = '{$a}';
$string['venuebuildinghtml'] = '{$a}';
$string['venuebuildingtext'] = '{$a}';
$string['venueinstructionshtml'] = '{$a}';
$string['venueinstructionstext'] = '{$a}';
$string['venuelocationformathtml'] = '{$a->room}<br>{$a->building}<br>{$a->address1}<br>{$a->address2}<br>{$a->postcode} {$a->suburb}';
$string['venuelocationformattext'] = '{$a->room}
{$a->building}
{$a->address1}
{$a->address2}
{$a->postcode} {$a->suburb}';
$string['venuelocationinfoformathtml'] = '{$a->address}<br><br><strong>Additional Instructions:</strong><br>{$a->instructions}</p>';
$string['venuelocationinfoformattext'] = '{$a->address}

Additional Instructions:
{$a->instructions}';
$string['venuepostcodehtml'] = '{$a}';
$string['venuepostcodetext'] = '{$a}';
$string['venueroomhtml'] = '{$a}';
$string['venueroomtext'] = '{$a}';
$string['venuesuburbhtml'] = '{$a}';
$string['venuesuburbtext'] = '{$a}';
$string['inactiveuser'] = 'Inactive User';




global $DB;
if ($DB->get_manager()->table_exists('communication_messagetypes')) {
    $types = $DB->get_records('communication_messagetypes', null, 'sortorder');

    foreach ($types as $type) {
        $string["messageprovider:type_{$type->id}"] = $type->name;
    }
}