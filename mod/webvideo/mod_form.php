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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_webvideo_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('webvideoname', 'webvideo'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }


        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'webvideoname', 'webvideo');

        $this->add_intro_editor(false, get_string('videointro','mod_webvideo'));

        $mform->addElement('header', 'content', get_string('contentheader', 'url'));
        $mform->addElement('url', 'externalurl', get_string('externalurl', 'url'), array('size'=>'60'), array('usefilepicker'=>true));
        $mform->setType('externalurl', PARAM_TEXT);
        $mform->addRule('externalurl', get_string('required'), 'required');

        $mform->addElement('checkbox', 'embed', get_string('embed', 'mod_webvideo'));

        $mform->setExpanded('content');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors= array();

        if (empty($data['externalurl'])) {
            $errors['externalurl']= get_string('missing url', 'mod_webvideo');
        }

        return $errors;
    }
}
