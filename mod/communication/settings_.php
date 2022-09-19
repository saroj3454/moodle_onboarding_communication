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
 * This file contains the actual settings so that it can be included in settings.php and local_communicationsettings\settings.php
 *
 * @package   mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$renderable = new \mod_communication\output\main();
$renderer = $PAGE->get_renderer('mod_communication');

$template = $renderer->render($renderable);

$fields = $DB->get_records('user_info_field');
$profilefields = array('' => get_string('choose'));
foreach ($fields as $field) {
    $profilefields[$field->id] = get_string('profilefielddisplay', 'communication', $field);
}

$settings->add(new admin_setting_configselect('communication/preferrednamefield',
    get_string('preferrednamefield', 'communication'),
    get_string('preferrednamefield_help', 'communication'), null, $profilefields));

$settings->add(new admin_setting_configselect('communication/preferredphonefield',
    get_string('preferredphonefield', 'communication'),
    get_string('preferredphonefield_help', 'communication'), null, $profilefields));

$settings->add(new admin_setting_heading('communication_templates', new lang_string('templates', 'communication'), $template));