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
 * This is the external API for this profile field.
 *
 * @package    mod_communication
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_communication;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * This is the external API for this profile field.
 *
 * @copyright  2017 onwards Strategenics <contact@strategenics.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends \external_api {

    public static function generate_preview_parameters() {
        $text = new \external_value(PARAM_RAW, 'template input text');
        $style = new \external_value(PARAM_BOOL, 'Using Moodle stylesheets or not', VALUE_DEFAULT, 0);
        $plain = new \external_value(PARAM_BOOL, 'Plain text format?', VALUE_DEFAULT, 0);
        $params = array(
            'text' => $text,
            'style' => $style,
            'plain' => $plain
        );
        return new \external_function_parameters($params);
    }

    public static function generate_preview($text, $style, $plain) {
        global $CFG, $PAGE, $DB, $USER;

        $params = self::validate_parameters(self::generate_preview_parameters(),
            array(
                'text' => $text,
                'style' => $style,
                'plain' => $plain
            )
        );
        $context = \context_system::instance();
        self::validate_context($context);

        require_once($CFG->dirroot.'/user/profile/lib.php');
        profile_load_data($USER);

        $blocks = $DB->get_records('communication_blocks', null, 'sortorder DESC');
        foreach ($blocks as $block) {
            if ($block->disabled) {
                $text = str_replace('{' . $block->placeholder . '}', '', $text);
            } else {
                $text = str_replace('{' . $block->placeholder . '}', $block->template, $text);
            }
        }

        if ($preferrednamefield = get_config('communication', 'preferrednamefield')) {
            if ($preferrednamefield = $DB->get_record('user_info_field', array('id' => $preferrednamefield))) {
                $preferrednamefield = 'profile_field_' . $preferrednamefield->shortname;
            }

        }
        if ($preferredphonefield = get_config('communication', 'preferredphonefield')) {
            if ($preferredphonefield = $DB->get_record('user_info_field', array('id' => $preferredphonefield))) {
                $preferredphonefield = 'profile_field_' . $preferredphonefield->shortname;
            }
        }

        $variables = array(
            'PFNAME' => '',
            'PLNAME' => $USER->lastname,
            'FNAME'  => 'Frank Facilitator',
            'FFNAME' => 'Frank',
            'FLNAME' => 'Facilitator',
            'FPHONE' => '',
            'PNAME'  => 'Some Provider',

            'SDATEW'                => userdate(time(), '%A'),
            'SDATED'                => userdate(time(), '%e'),
            'SDATEM'                => userdate(time(), '%m'),
            'SDATEMN'               => userdate(time(), '%B'),
            'SDATEYL'               => userdate(time(), '%Y'),
            'SDATEYS'               => userdate(time(), '%y'),
            'SDATES'                => userdate(time(), '%e/%m/%y'),
            'STIME12'               => '2:00 PM',
            'STIME24'               => '14:00',
            'SVENUENAME'            => 'Strategenics Office',
            'SVENUEROOM'            => 'Meeting Room 2',
            'SVENUEBUILDING'        => 'Port Office Hotel',
            'SVENUEADDRESS1'        => '40 Edward Street',
            'SVENUEADDRESS2'        => '',
            'SVENUEPOSTCODE'        => '4000',
            'SVENUESUBURB'          => 'BRISBANE CBD',
            'SVENUESTATE'           => 'QLD',
            'SVENUEINSTRUCTIONS'    => 'Take elevator to 2nd floor. Street parking only.',
            'SVENUEACCESSIBLE'      => 'Yes',
            'SLOCATION'             => 'Meeting Room 2<br>Port Office Hotel<br>40 Edward Street<br>4000 BRISBANE CBD',
            'SLOCATION+'            => 'Meeting Room 2<br>Port Office Hotel<br>40 Edward Street<br>4000 BRISBANE CBD<br><br>Additional Instructions:<br>Take elevator to 2nd floor. Street parking only.',

            'PROFILEFIELD_ConsumerGender' => 'Female',
            'PROFILEFIELD_ConsumerATSI' => 'Aboriginal',
            'PROFILEFIELD_ConsumerResidentialAddress1' => '123 Main Street',
            'PROFILEFIELD_ConsumerResidentialAddress2' => '',
            'PROFILEFIELD_ConsumerSuburb' => 'Brisbane',
            'PROFILEFIELD_ConsumerPostcode' => '4000',
            'PROFILEFIELD_ConsumerState' => 'QLD',
            'PROFILEFIELD_ConsumerReferralHealthConcerns' => 'High blood pressure'
        );


        if ($preferrednamefield && !empty($USER->{$preferrednamefield})) {
            $variables['PFNAME'] = $USER->{$preferrednamefield};
        } else {
            $variables['PFNAME'] = $USER->firstname;
        }
        if ($preferredphonefield && !empty($USER->{$preferredphonefield})) {
            $variables['FPHONE'] = $USER->{$preferredphonefield};
        }

        // any number of session slots can be included so create as many variables as necessary
        $matches = [];
        preg_match_all('/\{S[A-Z]+_(\d+)}/', $text, $matches, PREG_PATTERN_ORDER );
        $sessions = ($matches && $matches[1]) ? intval(max($matches[1])) : 0;

        for ($i=1; $i<=$sessions; $i++) {
            $time = time() + $i*7*24*60*60;
            $variables["SDATEW_$i"] = userdate($time, '%A');
            $variables["SDATED_$i"] = userdate($time, '%e');
            $variables["SDATEM_$i"] = userdate($time, '%m');
            $variables["SDATEMN_$i"] = userdate($time, '%B');
            $variables["SDATEYL_$i"] = userdate($time, '%Y');
            $variables["SDATEYS_$i"] = userdate($time, '%y');
            $variables["SDATES_$i"] = userdate($time, '%e/%m/%y');
            $variables["STIME12_$i"] = $variables["STIME12"];
            $variables["STIME24_$i"] = $variables["STIME24"];
            $variables["SVENUENAME_$i"] = $variables["SVENUENAME"];
            $variables["SVENUEROOM_$i"] = $variables["SVENUEROOM"];
            $variables["SVENUEBUILDING_$i"] = $variables["SVENUEBUILDING"];
            $variables["SVENUEADDRESS1_$i"] = $variables["SVENUEADDRESS1"];
            $variables["SVENUEADDRESS2_$i"] = $variables["SVENUEADDRESS2"];
            $variables["SVENUEPOSTCODE_$i"] = $variables["SVENUEPOSTCODE"];
            $variables["SVENUESUBURB_$i"] = $variables["SVENUESUBURB"];
            $variables["SVENUESTATE_$i"] = $variables["SVENUESTATE"];
            $variables["SVENUEINSTRUCTIONS_$i"] = $variables["SVENUEINSTRUCTIONS"];
            $variables["SVENUEACCESSIBLE_$i"] = $variables["SVENUEACCESSIBLE"];
            $variables["SLOCATION_$i"] = $variables["SLOCATION"];
            $variables["SLOCATION+_$i"] = $variables["SLOCATION+"];
        }

        foreach ($variables as $key => $value) {
            $text = str_ireplace('{' . $key . '}', $value, $text);
        }

        // populate the preivew
        $preview = '';
        $preview .= '<html><head>';
        if ($style) {
            $urls = $PAGE->theme->css_urls($PAGE);
            foreach ($urls as $url) {
                $preview .= '<link rel="stylesheet" type="text/css" href="' . $url->out(false) . '" />';
            }
        }
        $preview .= '</head>';
        $preview .= "<body>$text</body>";
        $preview .= '</html>';

        return $preview;
    }

    public static function generate_preview_returns() {
        return new \external_value(PARAM_RAW, 'Output text');
    }

    public static function get_blocks_parameters() {
        $limitfrom = new \external_value(PARAM_INT, 'limit from', VALUE_REQUIRED);
        $limitnum = new \external_value(PARAM_INT, 'limit num', VALUE_REQUIRED);
        $params = array(
            'limitfrom'   => $limitfrom,
            'limitnum'    => $limitnum,
        );
        return new \external_function_parameters($params);
    }

    public static function get_blocks($limitfrom, $limitnum) {
        global $CFG, $DB;
        $params = self::validate_parameters(self::get_blocks_parameters(),
                array(
                    'limitfrom' => $limitfrom,
                    'limitnum'  => $limitnum
                )
        );
        $context = \context_system::instance();
        self::validate_context($context);

        $blocks = $DB->get_records('communication_blocks', null, 'sortorder', '*', $limitfrom, $limitnum);

        $sesskey = sesskey();

        foreach (array_keys($blocks) as $id) {
            $blocks[$id]->editurl = "{$CFG->wwwroot}/mod/communication/block.php?id={$id}";
            $blocks[$id]->deleteurl = "{$CFG->wwwroot}/mod/communication/deleteblock.php?id={$id}&sesskey=$sesskey";
            $blocks[$id]->toggleurl = "{$CFG->wwwroot}/mod/communication/toggleblock.php?id={$id}&sesskey=$sesskey";
        }
        return $blocks;
    }

    public static function get_blocks_returns() {
        return new \external_multiple_structure(
            new \external_single_structure(array(
                'id' => new \external_value(PARAM_INT, 'Block ID'),
                'name' => new \external_value(PARAM_RAW, 'Block name'),
                'placeholder' => new \external_value(PARAM_RAW, 'Block placeholder'),
                'disabled' => new \external_value(PARAM_INT, 'Is block disabled?'),
                'sortorder' => new \external_value(PARAM_INT, 'Sort order'),
                'editurl' => new \external_value(PARAM_URL, 'edit url'),
                'deleteurl' => new \external_value(PARAM_URL, 'delete url'),
                'toggleurl' => new \external_value(PARAM_URL, 'toggle url'),
            ))
        );
    }

    /**
     * Returns description of get_other_fields() parameters.
     *
     * @return \external_function_parameters
     */
    public static function get_templates_parameters() {
        $limitfrom = new \external_value(PARAM_INT, 'limit from', VALUE_REQUIRED);
        $limitnum = new \external_value(PARAM_INT, 'limit num', VALUE_REQUIRED);
        $typeid = new \external_value(PARAM_INT, 'message type id', VALUE_DEFAULT, 0);
        $params = array(
            'limitfrom'   => $limitfrom,
            'limitnum'    => $limitnum,
            'messagetype' => $typeid,
        );
        return new \external_function_parameters($params);
    }

    public static function get_templates($limitfrom, $limitnum, $messagetype) {
        global $CFG, $DB;
        $params = self::validate_parameters(self::get_templates_parameters(),
            array(
                'limitfrom'   => $limitfrom,
                'limitnum'    => $limitnum,
                'messagetype' => $messagetype
            )
        );
        $context = \context_system::instance();
        self::validate_context($context);

        $where = $messagetype ? "WHERE mt.id = $messagetype" : '';

        $templates = $DB->get_records_sql("SELECT t.*,
                                                  mt.name AS typename
                                             FROM {communication_templates} t
                                             JOIN {communication_messagetypes} mt ON t.type = mt.id
                                            $where
                                         ORDER BY mt.name, t.name", null, $limitfrom, $limitnum);

        $sesskey = sesskey();

        foreach (array_keys($templates) as $id) {
            $templates[$id]->editurl = "{$CFG->wwwroot}/mod/communication/template.php?id={$id}";
            $templates[$id]->deleteurl = "{$CFG->wwwroot}/mod/communication/deletetemplate.php?id={$id}&sesskey=$sesskey";
            $templates[$id]->toggleurl = "{$CFG->wwwroot}/mod/communication/toggletemplate.php?id={$id}&sesskey=$sesskey";
        }
        return $templates;
    }

    /**
     * Returns description of get_other_fields() result value.
     *
     * @return \external_description
     */
    public static function get_templates_returns() {
        return new \external_multiple_structure(
            new \external_single_structure(array(
                'id' => new \external_value(PARAM_INT, 'Template ID'),
                'name' => new \external_value(PARAM_RAW, 'Template name'),
                'type' => new \external_value(PARAM_RAW, 'Template type'),
                'typename' => new \external_value(PARAM_RAW, 'Template type name'),
                'disabled' => new \external_value(PARAM_INT, 'Is template disabled?'),
                'sortorder' => new \external_value(PARAM_INT, 'Sort order'),
                'editurl' => new \external_value(PARAM_URL, 'edit url'),
                'deleteurl' => new \external_value(PARAM_URL, 'delete url'),
                'toggleurl' => new \external_value(PARAM_URL, 'toggle url'),
            ))
        );
    }

    public static function get_types_parameters() {
        $limitfrom = new \external_value(PARAM_INT, 'limit from', VALUE_REQUIRED);
        $limitnum = new \external_value(PARAM_INT, 'limit num', VALUE_REQUIRED);
        $params = array(
            'limitfrom' => $limitfrom,
            'limitnum'  => $limitnum,
        );
        return new \external_function_parameters($params);
    }

    public static function get_types($limitfrom, $limitnum) {
        global $CFG, $DB;
        $params = self::validate_parameters(self::get_types_parameters(),
            array(
                'limitfrom' => $limitfrom,
                'limitnum'  => $limitnum
            )
        );
        $context = \context_system::instance();
        self::validate_context($context);

        $types = $DB->get_records('communication_messagetypes', null, 'sortorder', '*', $limitfrom, $limitnum);

        $sesskey = sesskey();

        foreach (array_keys($types) as $id) {
            $types[$id]->editurl = "{$CFG->wwwroot}/mod/communication/type.php?id={$id}";
            $types[$id]->deleteurl = "{$CFG->wwwroot}/mod/communication/deletetype.php?id={$id}&sesskey=$sesskey";
            $types[$id]->toggleurl = "{$CFG->wwwroot}/mod/communication/toggletype.php?id={$id}&sesskey=$sesskey";
        }
        return $types;
    }

    public static function get_types_returns() {
        return new \external_multiple_structure(
            new \external_single_structure(array(
                'id' => new \external_value(PARAM_INT, 'Block ID'),
                'name' => new \external_value(PARAM_RAW, 'Block name'),
                'disabled' => new \external_value(PARAM_INT, 'Is block disabled?'),
                'sortorder' => new \external_value(PARAM_INT, 'Sort order'),
                'editurl' => new \external_value(PARAM_URL, 'edit url'),
                'deleteurl' => new \external_value(PARAM_URL, 'delete url'),
                'toggleurl' => new \external_value(PARAM_URL, 'toggle url'),
            ))
        );
    }

}