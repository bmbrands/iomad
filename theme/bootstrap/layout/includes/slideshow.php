<?php
// This file is part of The Bootstrap 3 Moodle theme
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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_bootstrap
 * @copyright  2014 Bas Brands, www.basbrands.nl
 * @authors    Bas Brands, David Scotson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$checkvars = array('slideenabled', 'slidetitle', 'slidetext', 'slidelink', 'slidelinktitle', 'slideimage');


if (!empty($PAGE->theme->settings->slidenumber)) {
    $slides = $PAGE->theme->settings->slidenumber;

    $slidescontent = array();

    for ($x = 1 ; $x <= $slides; $x++) {
        $show = true;
        $slide = new stdClass();
        $slide->id = $x;
        foreach ($checkvars as $var) {
            $setting = $var . $x;
            if (empty($PAGE->theme->settings->$setting)) {
                $show = false;
            } else {
                $slide->$var = $PAGE->theme->settings->$setting;
            }
        }
        if ($show ) {
            $slidescontent[] = bootstrap_slide($slide);
        }
    }
    if ($PAGE->theme->settings->sliderenabled) {
        echo bootstrap_slide_container_start(count($slidescontent));
        foreach ($slidescontent as $item) {
            echo $item;
        }
        echo bootstrap_slide_container_stop(count($slidescontent));

        $interval = '{ interval: 5000 }';
        $script = "$('.carousel').carousel($interval);";
        echo html_writer::tag('script', $script);
    }
}


function bootstrap_slide_container_start($slides) {
    $content = '';
    $content .= html_writer::start_tag('div', array('class' => 'carousel carousel-fade slide growdly', 'data-ride' => 'carousel', 'id' => 'bscarousel'));
    $content .= html_writer::start_tag('div', array('class' => 'row'));
    $content .= html_writer::start_tag('ol', array('class' => 'carousel-indicators col-xs-10 col-xs-push-1 col-sm-8 col-sm-push-2 col-md-4 col-md-push-7'));

    if ($slides > 1) {
        for ($x = 0 ; $x < $slides; $x++) {
            $content .= html_writer::tag('li','', array('class' => '', 'data-slide-to' => $x, 'data-target' => '#bscarousel'));
        }
    }

    $content .= html_writer::end_tag('ol');
    $content .= html_writer::end_tag('div');
    $content .= html_writer::start_tag('div', array('class' => 'carousel-inner'));
    return $content;

}

function bootstrap_slide_container_stop($slides) {
    $content = '';

    $content .= html_writer::end_tag('div');

    if ($slides > 1) {
        $left = html_writer::tag('span','', array('class' => 'glyphicon glyphicon-chevron-left'));
        $right = html_writer::tag('span','', array('class' => 'glyphicon glyphicon-chevron-right'));

        $content .= html_writer::link('#bscarousel', $left, array('class' => 'left carousel-control', 'data-slide' => 'prev'));
        $content .= html_writer::link('#bscarousel', $right, array('class' => 'right carousel-control', 'data-slide' => 'next'));
    }
    $content .= html_writer::end_tag('div');
    return $content;
}

function bootstrap_slide($slide) {
    global $PAGE;

    $content = '';

    $extraclass = '';
    if ($slide->id == 1) {
        $extraclass = 'active';
    }

    $imagevar = 'slideimage' . $slide->id;
    $slide->backgroundimage = $PAGE->theme->setting_file_url($imagevar , $imagevar);
    $slide->style = 'background-image: url('.$slide->backgroundimage.');';

    $content .= html_writer::start_tag('div', array('class' => 'item '. $extraclass, 'style' => $slide->style));
    $content .= html_writer::start_tag('div', array('class' => 'row'));

    $content .= html_writer::start_tag('div', array('class' => 'col-xs-8 col-xs-push-2 col-sm-6 col-sm-push-3 col-md-4 col-md-push-6'));

    $content .= html_writer::start_tag('div', array('class' => 'growdlybanner'));
    $content .= html_writer::start_tag('div', array('class' => 'heading'));
    $content .= html_writer::tag('h3', $slide->slidetitle);
    $content .= html_writer::end_tag('div');
    $content .= html_writer::tag('p', $slide->slidetext);
    $right = html_writer::tag('span','', array('class' => 'glyphicon glyphicon-chevron-right'));
    // $content .= html_writer::start_tag('div', array('class' => 'skew-wrap'));
    // $content .= html_writer::start_tag('div', array('class' => 'btn-skew'));
    // $content .= html_writer::link($slide->slidelink, $slide->slidelinktitle . ' ' . $right, array('class' => 'skew-link'));
    // $content .= html_writer::end_tag('div');
    // $content .= html_writer::end_tag('div');
    $content .= html_writer::end_tag('div');

    $content .= html_writer::end_tag('div');

    $content .= html_writer::end_tag('div');
    $content .= html_writer::end_tag('div');

    return $content;
}
