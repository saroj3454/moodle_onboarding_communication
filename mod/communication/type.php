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
 * Edit and save a new message type
 *
 * @package   mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$typeid = optional_param('id', 0, PARAM_INT);   // Message type id.

$PAGE->set_url(new moodle_url('/mod/communication/type.php'), array('id' => $typeid));
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
if ($typeid) {
    $strtitle = get_string('edittype', 'communication');
} else {
    $strtitle = get_string('newtype', 'communication');
}
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('administrationsite'), new moodle_url('/admin/search.php'));
$PAGE->navbar->add(get_string('plugins', 'admin'), new moodle_url('/admin/category.php', array('category' => 'modules')));
$PAGE->navbar->add(get_string('activitymodules'), new moodle_url('/admin/category.php', array('category' => 'modsettings')));
$PAGE->navbar->add(get_string('pluginname', 'communication'),
        new moodle_url('/admin/settings.php', array('section' => 'modsettingcommunication'), 'communication_messagetypes_view'));
$PAGE->navbar->add(get_string('messagetypes', 'communication'));
$PAGE->navbar->add(get_string($typeid ? 'edit' : 'new'));

if(!has_capability('moodle/site:config', $systemcontext) && !(get_capability_info('local/communicationsettings:editsettings') && has_capability('local/communicationsettings:editsettings', $systemcontext))){
    throw new required_capability_exception($systemcontext, 'moodle/site:config', 'nopermissions', '');
}

$settingurl = new moodle_url('/admin/settings.php', array('section' => 'modsettingcommunication'),
        'communication_messagetypes_view');

$typeform = new \mod_communication\form\type_form();

if ($typeid) {
    $type = $DB->get_record('communication_messagetypes', array('id' => $typeid), '*', MUST_EXIST);
    $PAGE->navbar->includesettingsbase = true;

    if (!empty($type->disabled)) {
        print_error('typeisdisabled', 'communication', $settingurl->out(false));
    }

    $typeform->set_data($type);
}

if ($newtype = $typeform->get_data()) {
    if ($typeid) {
        $DB->update_record('communication_messagetypes', $newtype);
    } else {
        $newtype->id = $DB->insert_record('communication_messagetypes', $newtype);
    }

    message_update_providers('mod_communication');

    $sm = get_string_manager();
    $sm->reset_caches();

    redirect($settingurl);
} else if ($typeform->is_cancelled()) {
    redirect($settingurl);
}

echo $OUTPUT->header();

$typeform->display();

echo $OUTPUT->footer();