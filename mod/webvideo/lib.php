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
 * Library of interface functions and constants for module webvideo
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the webvideo specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod
 * @subpackage webvideo
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function webvideo_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:                return false;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_NO_VIEW_LINK:            return true;

        default: return null;
    }
}

/**
 * Saves a new instance of the webvideo into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $webvideo An object from the form in mod_form.php
 * @param mod_webvideo_mod_form $mform
 * @return int The id of the newly inserted webvideo record
 */
function webvideo_add_instance(stdClass $webvideo, mod_webvideo_mod_form $mform = null) {
    global $DB;
    $urlbits = explode('#', $webvideo->externalurl);
    $webvideo->externalurl = $urlbits[0];
    $webvideo->timecreated = time();
    return $DB->insert_record('webvideo', $webvideo);
}

/**
 * This functions takes care of showing the content in the course page
 * It needs some Javascript to take care of the responsive bits,
 * for now this is loaded inline. It needs to be improved to load in the
 * page header or footer
 *
 * @param  $coursemodule
 * @return the video HTML and JavaScript
 */

function webvideo_get_coursemodule_info($coursemodule)  {
    global $DB, $CFG;

    if ($video = $DB->get_record('webvideo', array('id'=>$coursemodule->instance), 'id, name, externalurl, embed, intro, introformat')) {
        if (empty($video->name)) {
            $video->name = "unknown";
            $DB->set_field('webvideo', 'name', $video->name, array('id'=>$video->id));
        }

        $info = new cached_cm_info();

        if ($video->embed == 1) {
            $webvideo = webvideo_get_video($video->externalurl);
            $vidinfo = format_module_intro('webvideo', $video, $coursemodule->id, false);
            $info->content .= webvideo_get_modal($webvideo, $video->name, $video->id, $vidinfo, true);
            $info->content .= $vidinfo;
            $info->content .= webvideo_get_video($video->externalurl);
        } else {
            $webvideo = webvideo_get_video($video->externalurl);
            $vidinfo = format_module_intro('webvideo', $video, $coursemodule->id, false);
            $info->content = webvideo_get_modal($webvideo, $video->name, $video->id, $vidinfo, true);
        }

        $info->name  = $video->name;

        return $info;

    } else {
        return null;
    }
}


function webvideo_get_modal($content, $title, $id, $vidinfo, $icon = null) {
    global $CFG;
    if ($icon) {
         $icon = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/mod/webvideo/pix/icon.svg',
                  'class' => 'iconlarge activityicon', 'alt' => 'Webvideo'));
    }

    $modal = '
    <!-- Link trigger modal -->
    <a href="#" role="button" data-toggle="modal" data-target="#webvideo'.$id.'">'.$icon.'<span class="instancename">'.$title.'</span></a>
    <script>
        $(document).ready(function() {
            $("#webvideo'.$id.'").on("show.bs.modal", function() {
                $("#webvideo'.$id.' .modal-body").html(\''.$content.'\');
            })
        });
    </script>
    <!-- Modal -->
    <div class="modal fade" id="webvideo'.$id.'" tabindex="-1" role="dialog" aria-labelledby="webvideo" aria-hidden="true">
          <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">'.$title.'</h4>
                  </div>
                  <div class="modal-body">
                  </div>
                  <div class="modal-footer">
                        <div class="description">'.$vidinfo.'</div>
                       <button type="button" class="btn btn-default" data-dismiss="modal">'.get_string('close','form').'</button>
                  </div>
                </div>
          </div>
    </div>
    ';
    return $modal;
}



function webvideo_get_video ($url) {
    global $CFG;

    $urlbits = parse_url($url);
    $sourceurl = '';

    if ($urlbits['host'] == 'www.youtube.com' || $urlbits['host'] == 'youtube.com') {
        $sourceurl = 'http://www.youtube.com/embed/';
        if (isset($urlbits['query'])) {
            parse_str($urlbits['query'],$vidparams);
            $sourceurl .= $vidparams['v'];
            $sourceurl .= '?feature=player_detailpage';
        } elseif (isset($urlbits['path'])) {
            $path = $urlbits['path'];
            $pathbits = explode('/',$path);
            $sourceurl .= $pathbits[2];
            $sourceurl .= '?feature=player_detailpage';

        }
    } elseif ($urlbits['host'] == 'vimeo.com') {

        $oembed_endpoint = 'http://vimeo.com/api/oembed';
        $xml_url = $oembed_endpoint . '.xml?url=' . rawurlencode($url) . '&width=640';

        $oembed = simplexml_load_string(webvideo_curl_get($xml_url));

        $content = html_writer::start_tag('div', array('class'=>'moodlevideo fluid-width-video-wrapper'));
        $content .= $oembed->html;
        $content .= html_writer::end_tag('div');

        return $content;

    } else {
        //return html_writer::tag('div', print_r($urlbits,true));
    }

    if ($sourceurl) {
        $content = html_writer::start_tag('div', array('class'=>'moodlevideo fluid-width-video-wrapper'));
        $iframeoptions = array('width'=>'640', 'height'=>'360', 'frameborder'=>'0', 'allowfullscreen'=>'true', 'src'=>$sourceurl);
        $content .= html_writer::tag('iframe','',$iframeoptions);
        $content .= html_writer::end_tag('div');
    }

    return $content;
}

function webvideo_curl_get($url) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    $return = curl_exec($curl);
    curl_close($curl);
    return $return;
}

/**
 * Updates an instance of the webvideo in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $webvideo An object from the form in mod_form.php
 * @param mod_webvideo_mod_form $mform
 * @return boolean Success/Fail
 */
function webvideo_update_instance(stdClass $webvideo, mod_webvideo_mod_form $mform = null) {
    global $DB;

    if (!isset($webvideo->embed)) {
        $webvideo->embed = 0;
    }
    $urlbits = explode('#', $webvideo->externalurl);
    $webvideo->externalurl = $urlbits[0];

    $webvideo->timemodified = time();
    $webvideo->id = $webvideo->instance;
    return $DB->update_record('webvideo', $webvideo);
}

/**
 * Removes an instance of the webvideo from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function webvideo_delete_instance($id) {
    global $DB;

    if (! $webvideo = $DB->get_record('webvideo', array('id' => $id))) {
        return false;
    }
    $DB->delete_records('webvideo', array('id' => $webvideo->id));

    return true;
}


/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function webvideo_get_extra_capabilities() {
    return array();
}
