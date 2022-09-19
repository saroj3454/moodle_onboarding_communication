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
 * This file adds the settings pages to the navigation menu
 *
 * @package   local_communicationsettings
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$context = context_system::instance();

if(!has_capability('moodle/site:config',$context) && has_capability('local/communicationsettings:editsettings',$context)){
    $settings = new admin_settingpage('modsettingcommunication', new lang_string('pluginname','local_communicationsettings'),'local/communicationsettings:editsettings');
    $ADMIN->add('appearance', $settings);

    include_once($CFG->dirroot.'/mod/communication/settings_.php');
}