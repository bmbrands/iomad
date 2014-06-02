<?php

// This file is part of the bootstrap webvideo plugin
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
 * @package    mod
 * @subpackage webvideo
 * @copyright  2013 Bas Brands, bas@sonsbeekmedia.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete webvideo structure for backup, with file and id annotations
 */
class backup_webvideo_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $webvideo = new backup_nested_element('webvideo', array('id'), array(
            'name', 'timecreated', 'timemodified', 'externalurl', 'embed', 'intro', 'introformat'));

        // Define sources
        $webvideo->set_source_table('webvideo', array('id' => backup::VAR_ACTIVITYID));

        // Define file annotations
        $webvideo->annotate_files('mod_webvideo', 'intro', null); // This file area hasn't itemid

        // Return the root element (webvideo), wrapped into standard activity structure
        return $this->prepare_activity_structure($webvideo);
    }
}
