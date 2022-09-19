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
 * Toggle visibility of a reusable block
 *
 * @package   mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$blockid = required_param('id', PARAM_INT); // Reusable block id.

$PAGE->set_url('/mod/communication/deleteblock.php', array('id' => $blockid));

if (!$block = $DB->get_record('communication_blocks', array('id' => $blockid), '*', MUST_EXIST)) {
    print_error('invalidid');
}

require_login();

$context = context_system::instance();

if(!has_capability('moodle/site:config', $context) && (get_capability_info('local/communicationsettings:editsettings') && !has_capability('local/communicationsettings:editsettings', $context))){
    throw new required_capability_exception($context, 'moodle/site:config', 'nopermissions', '');
}

$settingurl = new moodle_url('/admin/settings.php', array('section' => 'modsettingcommunication'),
        'communication_reusableblocks_view');

if (confirm_sesskey()) {
    // TODO: check and only disable if used.
    $DB->set_field('communication_blocks', 'disabled', $block->disabled ? 0 : 1, array('id' => $blockid));

    $message = get_string($block->disabled ? 'blockenabled' : 'blockdisabled', 'communication', $block->name);
    redirect($settingurl, $message);
} else {
    print_error('confirmsesskeybad', 'error', $settingurl);
}