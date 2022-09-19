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
 * @package local_riskassessment
 * @author  Daniel Mitchell <daniel.mitchell@strategenics.com.au>
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function local_useremail_logs_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {

if(has_capability('local/useremail_logs:displayuseremail_logssectiononprofile',context_system::instance())){
     $category = new core_user\output\myprofile\category('useremail_logs',get_string('userlogs', 'local_useremail_logs'),null);
    $tree->add_category($category);

    $url = new moodle_url('/local/useremail_logs/index.php', array('userid' => $user->id));
            $node = new core_user\output\myprofile\node('useremail_logs', 'alluserlogs',get_string('userlogsdata', 'local_useremail_logs'), null, $url);
    $tree->add_node($node);
}
 
}



