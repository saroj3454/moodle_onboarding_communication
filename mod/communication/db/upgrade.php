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
 * Upgrade code for install
 *
 * @package   mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the communication schemas (types of messages being sent)
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_communication_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2017070500) {

        // Define field course to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');

        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field name to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'course');

        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field intro to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('intro', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'name');

        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field intro to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'intro');

        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field timemodified to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'introformat');

        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define key course (foreign) to be added to communication.
        $table = new xmldb_table('communication');
        $key = new xmldb_key('course', XMLDB_KEY_FOREIGN, array('course'), 'course', array('id'));

        // Launch add key contextid.
        $dbman->add_key($table, $key);

        // Communication savepoint reached.
        upgrade_mod_savepoint(true, 2017070500, 'communication');
    }

    if ($oldversion < 2017070700) {

        // Define field template to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('template', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'timemodified');

        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field schedulingduration to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('schedulingduration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'template');

        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field schedulingdirection to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('schedulingdirection', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null,
                'schedulingduration');

        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field schedulingsubjectitem to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('schedulingsubjectitem', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null,
                'schedulingdirection');

        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field schedulingsubjectid to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('schedulingsubjectid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null,
                'schedulingsubjectitem');

        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define key template (foreign) to be added to communication.
        $table = new xmldb_table('communication');
        $key = new xmldb_key('template', XMLDB_KEY_FOREIGN, array('template'), 'communication_templates', array('id'));

        // Launch add key contextid.
        $dbman->add_key($table, $key);

        // Communication savepoint reached.
        upgrade_mod_savepoint(true, 2017070700, 'communication');
    }

    if ($oldversion < 2017071000) {

        // Define field completiontriggermessage to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('completiontriggermessage', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0,
                'schedulingsubjectid');

        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Communication savepoint reached.
        upgrade_mod_savepoint(true, 2017071000, 'communication');
    }

    if ($oldversion < 2017071001) {

        // Define table communication_trigger to be created.
        $table = new xmldb_table('communication_trigger');

        // Adding fields to table communication_trigger.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('communicationid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table communication_trigger.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $table->add_key('communicationid', XMLDB_KEY_FOREIGN, array('communicationid'), 'communication', array('id'));

        // Conditionally launch create table for communication_trigger.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Communication savepoint reached.
        upgrade_mod_savepoint(true, 2017071001, 'communication');
    }

    if ($oldversion < 2017071002) {

        // Define field schedulingsubjectinfo to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('schedulingsubjectinfo', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'schedulingsubjectid');

        // Conditionally launch add field schedulingsubjectinfo.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Communication savepoint reached.
        upgrade_mod_savepoint(true, 2017071002, 'communication');
    }

    if ($oldversion < 2017071300) {

        // Define field subjectline to be added to communication_templates.
        $table = new xmldb_table('communication_templates');
        $field = new xmldb_field('subjectline', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'sortorder');

        // Conditionally launch add field subjectline.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field schedulingthreshold to be added to communication.
        $table = new xmldb_table('communication');
        $field = new xmldb_field('schedulingthreshold', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
                'schedulingsubjectinfo');

        // Conditionally launch add field schedulingthreshold.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Communication savepoint reached.
        upgrade_mod_savepoint(true, 2017071300, 'communication');
    }

    if ($oldversion < 2018011800) {

        $table = new xmldb_table('communication_trigger');
        $field = new xmldb_field('slottime', XMLDB_TYPE_INTEGER, '10', null, false, null, null, 'communicationid');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2018011800, 'communication');
    }

    if ($oldversion < 2018012300) {

        $table = new xmldb_table('communication');
        $field = new xmldb_field('resendonslotchange', XMLDB_TYPE_INTEGER, '1', null, true, null, '0', 'completiontriggermessage');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2018012300, 'communication');
    }

    if ($oldversion < 2020102200) {

        $table = new xmldb_table('communication_trigger');
        $field = new xmldb_field('messageid', XMLDB_TYPE_INTEGER, '10', null, false, null, null, 'communicationid');
        $index = new xmldb_index('messageid', XMLDB_INDEX_UNIQUE, array('messageid'));

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_index($table, $index);
        }

        upgrade_mod_savepoint(true, 2020102200, 'communication');
    }

    return true;
}