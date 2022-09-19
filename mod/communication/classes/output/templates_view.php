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
 * Class containing data for templates view in the communication activity module.
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
 * Class containing data for templates view in the communication activity module.
 *
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class templates_view implements renderable, templatable {
    /** Quantity of courses per page. */
    const TEMPLATES_PER_PAGE = 6;

    /** @var array $templates List of templates. */
    protected $templates = [];
    /** @var array $types List of types. */
    protected $types = [];

    /**
     * The courses_view constructor.
     *
     * @param array $templates list of templates.
     * @param array $types list of types.
     */
    public function __construct($templates, $types) {
        $this->templates = $templates;
        $this->types = $types;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        $today = time();

        // Build courses view data structure.
        $templatesview = [
            'hastemplates' => !empty($this->templates),
            'hastypes' => !empty($this->types)
        ];

        // How many courses we have per status?
        $templatescount = 0;
        foreach ($this->templates as $template) {
            $courseid = $template->id;
            $exporter = $template;
            $exportedcourse = $template;

            // Courses still in progress. Either their end date is not set, or the end date is not yet past the current date.
            $inprogresspages = floor($templatescount / $this::TEMPLATES_PER_PAGE);

            $templatesview['pages'][$inprogresspages]['templates'][] = $exportedcourse;
            $templatesview['pages'][$inprogresspages]['active'] = ($inprogresspages == 0 ? true : false);
            $templatesview['pages'][$inprogresspages]['page'] = $inprogresspages + 1;
            $templatesview['haspages'] = true;
            $templatescount++;
        }

        // Build courses view paging bar structure.
        $quantpages = ceil($templatescount / $this::TEMPLATES_PER_PAGE);

        if ($quantpages) {
            $templatesview['pagingbar']['disabled'] = ($quantpages <= 1);
            $templatesview['pagingbar']['pagecount'] = $quantpages;
            $templatesview['pagingbar']['first'] = ['page' => '&laquo;', 'url' => '#'];
            $templatesview['pagingbar']['last'] = ['page' => '&raquo;', 'url' => '#'];
            for ($page = 0; $page < $quantpages; $page++) {
                $templatesview['pagingbar']['pages'][$page] = [
                    'number' => $page + 1,
                    'page' => $page + 1,
                    'url' => '#',
                    'active' => ($page == 0 ? true : false)
                ];
            }
        }

        return $templatesview;
    }
}
