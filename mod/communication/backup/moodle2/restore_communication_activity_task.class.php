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
 * This file contains the communication module restore class
 *
 * @package    mod_communication
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/communication/backup/moodle2/restore_communication_stepslib.php');

/**
 * communication restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_communication_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Label only has one structure step.
        $this->add_step(new restore_communication_activity_structure_step('communication_structure', 'communication.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('communication', array('intro'), 'communication');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('COMMUNICATIONVIEWBYID', '/mod/communication/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('COMMUNICATIONINDEX', '/mod/communication/index.php?id=$1', 'course');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * communication logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('communication', 'add', 'view.php?id={course_module}', '{communication}');
        $rules[] = new restore_log_rule('communication', 'update', 'view.php?id={course_module}', '{communication}');
        $rules[] = new restore_log_rule('communication', 'view', 'view.php?id={course_module}', '{communication}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('communication', 'view all', 'index.php?id={course}', null);

        return $rules;
    }

    /**
     * Getter for communication plugins.
     *
     * @return int
     */
    public function get_old_moduleid() {
        return $this->oldmoduleid;
    }

    /**
     * This function, executed after all the tasks in the plan
     * have been executed, will perform the recode of the
     * target activity for the communication activity. This must be done here
     * and not in normal execution steps because the target activity
     * may be restored after the communication activity.
     */
    public function after_restore() {
        global $DB;

        // Get the communicationid
        $communicationid = $this->get_activityid();

        if ($communication = $DB->get_record('communication', array('id' => $communicationid))) {
            $changed = false;
            switch ($communication->schedulingsubjectitem) {
                case COMMUNICATION_SCHEDULING_COMPLETION:
                    if (!empty($communication->schedulingsubjectid)) {
                        if ($cmmap = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'course_module',
                                $communication->schedulingsubjectid)) {
                            $communication->schedulingsubjectid = $cmmap->newitemid;
                        } else {
                            $communication->schedulingsubjectid = 0;
                        }
                        $changed = true;
                    }
                    break;
                case COMMUNICATION_SCHEDULING_ENROLMENT:
                    // Do nothing.
                    break;
                case COMMUNICATION_SCHEDULING_SLOT:
                    if (!empty($communication->schedulingsubjectid)) {
                        if ($schedulermap = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'scheduler',
                                $communication->schedulingsubjectid)) {
                            $communication->schedulingsubjectid = $schedulermap->newitemid;
                        } else {
                            $communication->schedulingsubjectid = 0;
                        }
                        $changed = true;
                    }
                    break;
            }
            if ($changed) {
                $DB->update_record('communication', $communication);
            }
        }
    }
}
