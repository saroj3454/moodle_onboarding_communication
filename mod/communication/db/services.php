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
 * Communication external functions and service definitions.
 *
 * @package    mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'mod_communication_generate_preview' => array(
        'classname'    => 'mod_communication\external',
        'methodname'   => 'generate_preview',
        'classpath'    => '',
        'description'  => 'Generate communication template preview',
        'type'         => 'read',
        'capabilities' => 'moodle/site:config',
        'ajax'         => true,
    ),
    'mod_communication_get_blocks' => array(
        'classname'    => 'mod_communication\external',
        'methodname'   => 'get_blocks',
        'classpath'    => '',
        'description'  => 'Load the list communication blocks',
        'type'         => 'read',
        'capabilities' => 'moodle/site:config',
        'ajax'         => true,
    ),
    'mod_communication_get_templates' => array(
        'classname'    => 'mod_communication\external',
        'methodname'   => 'get_templates',
        'classpath'    => '',
        'description'  => 'Load the list communication templates',
        'type'         => 'read',
        'capabilities' => 'moodle/site:config',
        'ajax'         => true,
    ),
    'mod_communication_get_messagetypes' => array(
        'classname'    => 'mod_communication\external',
        'methodname'   => 'get_types',
        'classpath'    => '',
        'description'  => 'Load the list communication message types',
        'type'         => 'read',
        'capabilities' => 'moodle/site:config',
        'ajax'         => true,
    ),
);
