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
 * Handles triggering communication templates.
 *
 * @package   mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_communication\task;
defined('MOODLE_INTERNAL') || die();

/**
 * Task for triggering messages for the communication module.
 *
 * @package   mod_communication
 * @copyright 2017 onwards Strategenics <contact@strategenics.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class trigger_messages extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('tasktriggermessages', 'communication');
    }

    /**
     * Performs the triggering of messages.
     *
     * @return bool|void
     */
    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/communication/lib.php');

        $now = time();

        $beforeenrol = $DB->get_recordset_sql("SELECT ue.userid,
                                                      c.id AS communicationid,
                                                      c.course AS courseid,
                                                      c.template AS templateid
                                                 FROM {communication} c
                                                 JOIN {enrol} e ON c.course = e.courseid
                                                 JOIN {user_enrolments} ue ON ue.enrolid = e.id
                                                WHERE c.schedulingsubjectitem = :enrol
                                                      AND c.schedulingdirection = :before
                                                      AND ue.timeend - c.schedulingduration < :now
                                                      AND ue.timeend - c.schedulingduration + c.schedulingthreshold > :stillnow
                                                      AND NOT EXISTS (
                                                        SELECT *
                                                        
                                                          FROM {communication_trigger} t
                                                          
                                                         WHERE t.userid = ue.userid
                                                               AND t.communicationid = c.id
                                                               AND t.timecreated >= IF(ue.timestart = 0,ue.timecreated,ue.timestart)
                                                     )",
                array(
                    'enrol'    => COMMUNICATION_SCHEDULING_ENROLMENT,
                    'before'   => COMMUNICATION_SCHEDULING_BEFORE,
                    'now'      => $now,
                    'stillnow' => $now
                ));
        communication_process_recordset($beforeenrol);
        $beforeenrol->close();

        $afterenrol = $DB->get_recordset_sql("SELECT ue.userid,
                                                     c.id AS communicationid,
                                                     c.course AS courseid,
                                                     c.template AS templateid
                                                FROM {communication} c
                                                JOIN {enrol} e ON c.course = e.courseid
                                                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                                               WHERE c.schedulingsubjectitem = :enrol
                                                     AND c.schedulingdirection = :after
                                                     AND
                                                     (
                                                        (
                                                              ue.timestart <> 0
                                                          AND ue.timestart + c.schedulingduration < :now
                                                          AND ue.timestart + c.schedulingduration
                                                              + c.schedulingthreshold > :stillnow
                                                        )
                                                        OR
                                                        (
                                                              ue.timestart = 0
                                                          AND ue.timecreated + c.schedulingduration < :yetstillnow
                                                          AND ue.timecreated + c.schedulingduration
                                                              + c.schedulingthreshold > :andyetstillnow
                                                        )
                                                     )
                                                     AND NOT EXISTS (
                                                        SELECT *
                                                        
                                                          FROM {communication_trigger} t
                                                          
                                                         WHERE t.userid = ue.userid
                                                               AND t.communicationid = c.id
                                                               AND t.timecreated >= IF(ue.timestart = 0,ue.timecreated,ue.timestart)
                                                     )",
                array(
                    'enrol'          => COMMUNICATION_SCHEDULING_ENROLMENT,
                    'after'          => COMMUNICATION_SCHEDULING_AFTER,
                    'now'            => $now,
                    'stillnow'       => $now,
                    'yetstillnow'    => $now,
                    'andyetstillnow' => $now
                ));
        communication_process_recordset($afterenrol);
        $afterenrol->close();

        $aftercompletion = $DB->get_recordset_sql("SELECT cmc.userid,
                                                          c.id AS communicationid,
                                                          c.course AS courseid,
                                                          c.template AS templateid
                                                     FROM {communication} c
                                                     JOIN {course_modules_completion} cmc
                                                          ON c.schedulingsubjectid = cmc.coursemoduleid
                                                    WHERE c.schedulingsubjectitem = :completion
                                                          AND c.schedulingdirection = :after
                                                          AND cmc.timemodified + c.schedulingduration < :now
                                                          AND cmc.timemodified + c.schedulingduration
                                                              + c.schedulingthreshold > :stillnow
                                                          AND NOT EXISTS (
                                                                SELECT *
                                                                
                                                                  FROM {communication_trigger} t
                                                                  
                                                                 WHERE t.userid = cmc.userid
                                                                       AND t.communicationid = c.id
                                                                       AND t.timecreated >= ifnull((
                                                                            SELECT MIN(IF(ue.timestart = 0,ue.timecreated,ue.timestart))
                                                                            
                                                                              FROM {enrol} e
                                                                              JOIN {user_enrolments} ue ON ue.enrolid = e.id
                                                                                   
                                                                             WHERE e.courseid = c.course
                                                                                   AND ue.userid = t.userid
                                                                          ),0)
                                                             )",
                array(
                    'completion' => COMMUNICATION_SCHEDULING_COMPLETION,
                    'after'      => COMMUNICATION_SCHEDULING_AFTER,
                    'now'        => $now,
                    'stillnow'   => $now
                ));
        communication_process_recordset($aftercompletion);
        $aftercompletion->close();

        $beforeslot = $DB->get_recordset_sql("SELECT sa.studentid AS userid,
                                                     c.id AS communicationid,
                                                     c.course AS courseid,
                                                     c.template AS templateid,
                                                     s.id AS slotid,
                                                     s.starttime AS slottime
                                                FROM {communication} c
                                                JOIN {scheduler_slots} s ON c.schedulingsubjectid = s.schedulerid
                                                JOIN {scheduler_appointment} sa ON sa.slotid = s.id
                                               WHERE c.schedulingsubjectitem = :slot
                                                     AND sa.grade = c.schedulingsubjectinfo
                                                     AND c.schedulingdirection = :before
                                                     AND s.starttime - c.schedulingduration < :now
                                                     AND s.starttime - c.schedulingduration + c.schedulingthreshold > :stillnow
                                                     AND NOT EXISTS (
                                                        SELECT *
                                                        
                                                          FROM {communication_trigger} t
                                                          
                                                         WHERE t.userid = sa.studentid
                                                               AND t.communicationid = c.id
                                                               AND (c.resendonslotchange = 0 OR ifnull(t.slottime,s.starttime) = s.starttime)
                                                               AND t.timecreated >= ifnull((
                                                                    SELECT MIN(IF(ue.timestart = 0,ue.timecreated,ue.timestart))
                                                                    
                                                                      FROM {enrol} e
                                                                      JOIN {user_enrolments} ue ON ue.enrolid = e.id
                                                                           
                                                                     WHERE e.courseid = c.course
                                                                           AND ue.userid = t.userid
                                                                  ),0)
                                                     )",
                array(
                    'slot'     => COMMUNICATION_SCHEDULING_SLOT,
                    'before'   => COMMUNICATION_SCHEDULING_BEFORE,
                    'now'      => $now,
                    'stillnow' => $now
                ));
        communication_process_recordset($beforeslot);
        $beforeslot->close();

        $afterslot = $DB->get_recordset_sql("SELECT sa.studentid AS userid,
                                                    c.id AS communicationid,
                                                    c.course AS courseid,
                                                    c.template AS templateid,
                                                    s.id AS slotid,
                                                    s.starttime AS slottime
                                               FROM {communication} c
                                               JOIN {scheduler_slots} s ON c.schedulingsubjectid = s.schedulerid
                                               JOIN {scheduler_appointment} sa ON sa.slotid = s.id
                                              WHERE c.schedulingsubjectitem = :slot
                                                    AND sa.grade = c.schedulingsubjectinfo
                                                    AND c.schedulingdirection = :after
                                                    AND s.starttime + (s.duration * 60) + c.schedulingduration < :now
                                                    AND s.starttime + (s.duration * 60) + c.schedulingduration + c.schedulingthreshold > :stillnow
                                                    AND NOT EXISTS (
                                                        SELECT *
                                                        
                                                          FROM {communication_trigger} t
                                                          
                                                         WHERE t.userid = sa.studentid
                                                               AND t.communicationid = c.id
                                                               AND (c.resendonslotchange = 0 OR ifnull(t.slottime,s.starttime) = s.starttime)
                                                               AND t.timecreated >= ifnull((
                                                                    SELECT MIN(IF(ue.timestart = 0,ue.timecreated,ue.timestart))
                                                                    
                                                                      FROM {enrol} e
                                                                      JOIN {user_enrolments} ue ON ue.enrolid = e.id
                                                                           
                                                                     WHERE e.courseid = c.course
                                                                           AND ue.userid = t.userid
                                                                  ),0)
                                                     )",
                array(
                    'slot'     => COMMUNICATION_SCHEDULING_SLOT,
                    'after'    => COMMUNICATION_SCHEDULING_AFTER,
                    'now'      => $now,
                    'stillnow' => $now
                ));
        communication_process_recordset($afterslot);
        $afterslot->close();

       // custom code lds   
        $inactiveuserslot=$DB->get_records_sql("SELECT * from {communication} where `schedulingsubjectitem`=? and `schedulingdirection`=?",array(3,1)); 
          if(!empty($inactiveuserslot)){
             inactivedataslot($inactiveuserslot);
          } 



    }
}