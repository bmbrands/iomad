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

require_once("../../config.php");

$id = optional_param('id',0,PARAM_INT);    // Course Module ID, or
$l = optional_param('l',0,PARAM_INT);     // webvideo ID

if ($id) {
    $PAGE->set_url('/mod/webvideo/index.php', array('id'=>$id));
    if (! $cm = get_coursemodule_from_id('webvideo', $id)) {
        print_error('invalidcoursemodule');
    }

    if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
        print_error('coursemisconf');
    }

    if (! $webvideo = $DB->get_record("webvideo", array("id"=>$cm->instance))) {
        print_error('invalidcoursemodule');
    }

} else {
    $PAGE->set_url('/mod/webvideo/index.php', array('l'=>$l));
    if (! $webvideo = $DB->get_record("webvideo", array("id"=>$l))) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id"=>$webvideo->course)) ){
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance("webvideo", $webvideo->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_login($course, true, $cm);

redirect("$CFG->wwwroot/course/view.php?id=$course->id");