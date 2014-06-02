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
 * Theme version info
 *
 * @package    theme_bootstrapbase
 * @copyright  2014 Bas Brands, www.basbrands.nl
 * @authors    Bas Brands, David Scotson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
global $PAGE;

require_once(__DIR__ . "/lib.php");
require_once(__DIR__ . "/simple_theme_settings.class.php");

if ($ADMIN->fulltree) {
    $settings->add(theme_bootstrap_checkbox('fluidwidth'));

    $settings->add(theme_bootstrap_textarea('customcss'));

    $settings->add(theme_bootstrap_image('logo'));

    $settings->add(theme_bootstrap_text('nocourses'));
    for ($i = 1; $i <= 3; $i++) {
        $settings->add(new admin_setting_heading('footerboxes' . $i, 'Footer Box ' . $i, ''));
        $settings->add(theme_bootstrap_text('footertitle', '', $i));
        $settings->add(theme_bootstrap_textarea('footertext', '', $i));
    }

    
    $hasslidenumber = (!empty($PAGE->theme->settings->slidenumber));
    if ($hasslidenumber) {
        $slidenumber = $PAGE->theme->settings->slidenumber;
    } else {
        $slidenumber = '5';
    }

    $settings->add(new admin_setting_heading('Nnoslides', 'Number of slides ' . $slidenumber, ''));
    $choices = array(
        '0'=>'0',
        '1'=>'1',
        '2'=>'2',
        '3'=>'3',
        '4'=>'4',
        '5'=>'5',
        '6'=>'6',
        '7'=>'7',
        '8'=>'8',
        '9'=>'9',
        '10'=>'10');
    $settings->add(theme_bootstrap_select('slidenumber', '', null, $choices));

    $settings->add(theme_bootstrap_checkbox('sliderenabled', ''));

    for ($i =1; $i <= $slidenumber; $i++) {
        $settings->add(new admin_setting_heading('Slide' . $i, 'Slide number  ' . $i, ''));
        $settings->add(theme_bootstrap_checkbox('slideenabled', '', $i));
        $settings->add(theme_bootstrap_text('slidetitle', '', $i));
        $settings->add(theme_bootstrap_textarea('slidetext', '', $i));
        $settings->add(theme_bootstrap_image('slideimage', '', $i));
    }
    $settings->add(new admin_setting_heading('Calltoaction', 'Call to action settings', ''));
    $settings->add(theme_bootstrap_text('calltoactiontext'));
    $settings->add(theme_bootstrap_text('calltoactionlinktext'));
    $settings->add(theme_bootstrap_text('calltoactionlink'));

    $settings->add(theme_bootstrap_select('backnumber', '', null, $choices));

    $hasbacknumber = (!empty($PAGE->theme->settings->backnumber));
    if ($hasbacknumber) {
        $backnumber = $PAGE->theme->settings->backnumber;
    } else {
        $backnumber = '1';
    }
    for ($i =1; $i <= $backnumber; $i++) {
        $settings->add(new admin_setting_heading('loginbackground' . $i, 'Background Image number  ' . $i, ''));
        $settings->add(theme_bootstrap_image('backimages', '', $i));
    }
}

