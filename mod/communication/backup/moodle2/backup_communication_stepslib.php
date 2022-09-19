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
 * @package    mod_communication
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define all the backup steps that will be used by the backup_communication_activity_task
 */

/**
 * Define the complete communication structure for backup, with file and id annotations
 */
class backup_communication_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        // Define each element separated.
        $communication = new backup_nested_element('communication', array('id'), array(
                'name', 'intro', 'introformat', 'timemodified', 'template',
                'schedulingduration', 'schedulingdirection', 'schedulingsubjectitem',
                'schedulingsubjectid', 'schedulingsubjectinfo', 'schedulingthreshold',
                'completiontriggermessage'));

        // Build the tree
        // (none).

        // Define sources.
        $communication->set_source_table('communication', array('id' => backup::VAR_ACTIVITYID));

        // Define id annotations
        // (none).

        // Define file annotations.
        $communication->annotate_files('mod_communication', 'intro', null); // This file areas haven't itemid.

        // Return the root element (communication), wrapped into standard activity structure.
        return $this->prepare_activity_structure($communication);
    }
}