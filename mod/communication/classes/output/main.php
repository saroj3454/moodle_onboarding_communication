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
 * Class containing data for communication activity module.
 *
 * @package    mod_communication
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_communication\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for my overview block.
 *
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $USER, $DB;

        $templates = $DB->get_records('communication_templates');
        $types = $DB->get_records('communication_messagetypes', null, 'sortorder');

        $messagetypes = array();
        $messagetypes[] = array('id' => '', 'name' => get_string('all'));
        foreach ($types as $type) {
            $messagetypes[] = array('id' => $type->id, 'name' => $type->name);
        }

        $templatesview = new templates_view($templates, $types);
        $nocoursesurl = $output->image_url('courses', 'mod_communication')->out();
        $noeventsurl = $output->image_url('activities', 'mod_communication')->out();

        return [
            'midnight' => usergetmidnight(time()),
            'templatesview' => $templatesview->export_for_template($output),
            'messagetypes' => $messagetypes,
            'urls' => [
                'nocourses' => $nocoursesurl,
                'noevents' => $noeventsurl
            ]
        ];
    }
}
