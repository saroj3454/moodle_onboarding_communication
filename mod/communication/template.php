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
 * Edit and save a new communication template
 *
 * @package   mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$templateid = optional_param('id', 0, PARAM_INT);   // Communication template id.

$PAGE->set_url(new moodle_url('/mod/communication/template.php'), array('id' => $templateid));
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
if ($templateid) {
    $strtitle = get_string('edittemplate', 'communication');
} else {
    $strtitle = get_string('newtemplate', 'communication');
}
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('administrationsite'), new moodle_url('/admin/search.php'));
$PAGE->navbar->add(get_string('plugins', 'admin'), new moodle_url('/admin/category.php', array('category' => 'modules')));
$PAGE->navbar->add(get_string('activitymodules'), new moodle_url('/admin/category.php', array('category' => 'modsettings')));
$PAGE->navbar->add(get_string('pluginname', 'communication'),
        new moodle_url('/admin/settings.php', array('section' => 'modsettingcommunication'), 'communication_templates_view'));
$PAGE->navbar->add(get_string('reusableblocks', 'communication'));
$PAGE->navbar->add(get_string($templateid ? 'edit' : 'new'));

if(!has_capability('moodle/site:config', $systemcontext) && !(get_capability_info('local/communicationsettings:editsettings') && has_capability('local/communicationsettings:editsettings', $systemcontext))){
    throw new required_capability_exception($systemcontext, 'moodle/site:config', 'nopermissions', '');
}

$settingurl = new moodle_url('/admin/settings.php', array('section' => 'modsettingcommunication'), 'communication_templates_view');

$templateform = new \mod_communication\form\template_form();

if ($templateid) {
    $template = $DB->get_record('communication_templates', array('id' => $templateid), '*', MUST_EXIST);
    $PAGE->navbar->includesettingsbase = true;

    if (!empty($template->disabled)) {
        print_error('templateisdisabled', 'communication', $settingurl->out(false));
    }

    $template->fullmessage = array(
        'text' => $template->fullmessage,
        'format' => $template->fullmessageformat,
    );
    $templateform->set_data($template);
}

if ($newtemplate = $templateform->get_data()) {
    $newtemplate->fullmessageformat = $newtemplate->fullmessage['format'];
    $newtemplate->fullmessage = $newtemplate->fullmessage['text'];
    if ($templateid) {
        $DB->update_record('communication_templates', $newtemplate);
    } else {
        $DB->insert_record('communication_templates', $newtemplate);
    }

    redirect($settingurl);
} else if ($templateform->is_cancelled()) {
    redirect($settingurl);
}

echo $OUTPUT->header();

$templateform->display();

echo $OUTPUT->footer();