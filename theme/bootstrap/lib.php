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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/iomad/lib/user.php');
require_once($CFG->dirroot.'/local/iomad/lib/iomad.php');

function bootstrap_grid($hassidepre, $hassidepost) {

    if ($hassidepre && $hassidepost) {
        $regions = array('content' => 'col-sm-6 col-sm-push-3 col-lg-8 col-lg-push-2');
        $regions['pre'] = 'col-sm-3 col-sm-pull-6 col-lg-2 col-lg-pull-8';
        $regions['post'] = 'col-sm-3 col-lg-2';
    } else if ($hassidepre && !$hassidepost) {
        $regions = array('content' => 'col-sm-9 col-sm-push-3 col-lg-10 col-lg-push-2');
        $regions['pre'] = 'col-sm-3 col-sm-pull-9 col-lg-2 col-lg-pull-10';
        $regions['post'] = 'emtpy';
    } else if (!$hassidepre && $hassidepost) {
        $regions = array('content' => 'col-sm-9 col-lg-10');
        $regions['pre'] = 'empty';
        $regions['post'] = 'col-sm-3 col-lg-2';
    } else if (!$hassidepre && !$hassidepost) {
        $regions = array('content' => 'col-md-12');
        $regions['pre'] = 'empty';
        $regions['post'] = 'empty';
    }
    return $regions;
}


function theme_bootstrap_select($setting, $default='0', $a = null, $choices) {
    list($name, $title, $description) = theme_bootstrap_setting_details($setting, $a);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    return $setting;
}

function theme_bootstrap_checkbox($setting, $default='0', $a = null) {
    list($name, $title, $description) = theme_bootstrap_setting_details($setting, $a);
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    return $setting;
}

function theme_bootstrap_textarea($setting, $default='', $a = null) {
    list($name, $title, $description) = theme_bootstrap_setting_details($setting, $a);
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    return $setting;
}

function theme_bootstrap_text($setting, $default='', $a = null) {
    list($name, $title, $description) = theme_bootstrap_setting_details($setting, $a);
    return new admin_setting_configtext($name, $title, $description, $default);
}

function theme_bootstrap_image($setting, $default='', $a = null) {
    list($name, $title, $description) = theme_bootstrap_setting_details($setting, $a);
    return new admin_setting_configstoredfile($name, $title, $description, $setting.$a);
}

function theme_bootstrap_setting_details($setting, $a = null) {
    $theme = "theme_bootstrap";
    $name = "$theme/$setting$a";
    $title = get_string($setting, $theme, $a);
    $description = get_string($setting.'desc', $theme, $a);
    return array($name, $title, $description);
}

function theme_bootstrap_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        $theme = theme_config::load('bootstrap');
        if (preg_match('/slideimage\d+/', $filearea)) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if (preg_match('/backimages\d+/', $filearea)) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if (preg_match('/logo/', $filearea)) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else {
            send_file_not_found();
        }
    } else {
        send_file_not_found();
    }
}

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string $css The CSS
 * @param theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */
function theme_bootstrap_process_css($css, $theme) {

    $defaultsettings = array(
        'customcss' => '',
    );

    $settings = theme_bootstrap_get_user_settings($defaultsettings, $theme);

    return theme_bootstrap_replace_settings($settings, $css);
}

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param array $settings containing setting names and default values
 * @param theme_config $theme The theme config object.
 * @return array The setting with defaults replaced with user settings (if any)
 */
function theme_bootstrap_get_user_settings($settings, $theme) {
    foreach (array_keys($settings) as $setting) {
        if (!empty($theme->settings->$setting)) {
            $settings[$setting] = $theme->settings->$setting;
        }
    }
    return $settings;
}

/**
 * For each setting called e.g. "customcss" this looks for the string
 * "[[setting:customcss]]" in the CSS and replaces it with
 * the value held in the $settings array for the key
 * "customcss".
 *
 * @param array $settings containing setting names and values
 * @param string $css The CSS
 * @return string The CSS with replacements made
 */
function theme_bootstrap_replace_settings($settings, $css) {
    $settingnames = array_keys($settings);

    $wrapsettings = function($name) {
        return "[[setting:$name]]";
    };

    $find = array_map($wrapsettings, $settingnames);
    $replace = array_values($settings);

    return str_replace($find, $replace, $css);
}

function theme_bootstrap_page_background($PAGE) {

    $hasbackgroundimages = (!empty($PAGE->theme->settings->backnumber));
    if ($hasbackgroundimages) {
        $backgroundimages = $PAGE->theme->settings->backnumber;
    } else {
        return 'NO';
    }

    if ($backgroundimages == 1) {
        $image = $PAGE->theme->setting_file_url('backimages1' , 'backimages1');
        return '<script>
                $.backstretch("'.$image.'");
                </script>';
    } else {
        $content = '<script> $.backstretch([';
        for ($i = 1 ; $i <= $backgroundimages; $i++) {
            if ($image = $PAGE->theme->setting_file_url('backimages' . $i , 'backimages' . $i)) {
                $content .= '"' . $image . '"'. ",\n"; 
            }
        }
        $content .= '] ,{duration: 10000, fade: 750}); </script>';
        return $content;
    }
}

function theme_bootstrap_get_html_for_settings(renderer_base $output, moodle_page $page) {
    global $CFG, $USER, $DB;
    $return = new stdClass;

    $hascompanyid = optional_param('cpid', 0, PARAM_INT);
    $context = context_system::instance();
    // get logos
    $theme = $page->theme;
    $logo = $theme->setting_file_url('logo', 'logo');
    if (empty($logo)) {
        $logo = $output->pix_url('logo', 'theme');
    }
    $return->heading = $logo;
    $companycss = '';

    $bgcolor_header = false;
    $bgcolor_content = false;


    if ($hascompanyid) {
        if ($company = $DB->get_record('company', array('id' => $hascompanyid))) {
            $bgcolor_header = $company->bgcolor_header;
            $bgcolor_content = $company->bgcolor_content;
        };
        if ($files = $DB->get_records('files', array('contextid' => $context->id, 'component' => 'theme_iomad', 'filearea' => 'companylogo', 'itemid' => $hascompanyid))) {
            foreach ($files as $file) {
                if ($file->filename != '.') {
                    $clientlogo = $CFG->wwwroot . "/pluginfile.php/{$context->id}/theme_iomad/companylogo/$hascompanyid/{$file->filename}";
                    $return->heading =  $clientlogo;
                }
            }
        }
    }

    if ($companyid = iomad::is_company_user()) {
        if ($files = $DB->get_records('files', array('contextid' => $context->id, 'component' => 'theme_iomad', 'filearea' => 'companylogo', 'itemid' => $companyid))) {
            foreach ($files as $file) {
                if ($file->filename != '.') {
                    $clientlogo = $CFG->wwwroot . "/pluginfile.php/{$context->id}/theme_iomad/companylogo/$companyid/{$file->filename}";
                    $return->heading =  $clientlogo;
                }
            }
        }
        company_user::load_company();
        $bgcolor_header = $USER->company->bgcolor_header;
        $bgcolor_content = $USER->company->bgcolor_content;
    }


    if (isset($bgcolor_header)) {
        $companycss .= '.growdlyheader { background-color: '.$bgcolor_header.' ; }';
        $companycss .= 'a, a:hover, a:focus { color: '.$bgcolor_header.' ; }';
        $companycss .= '.block_settings .block_tree .collapsed .tree_item.branch a:before, .block_navigation .block_tree .collapsed .tree_item.branch a:before, .block_settings .block_tree .collapsed .tree_item.branch span:before, .block_navigation .block_tree .collapsed .tree_item.branch span:before, .block_settings .block_tree .collapsed .tree_item.emptybranch a:before, .block_navigation .block_tree .collapsed .tree_item.emptybranch a:before, .block_settings .block_tree .collapsed .tree_item.emptybranch span:before, .block_navigation .block_tree .collapsed .tree_item.emptybranch span:before, .block_settings .block_tree .contains_branch .tree_item.emptybranch a:before, .block_navigation .block_tree .contains_branch .tree_item.emptybranch a:before, .block_settings .block_tree .contains_branch .tree_item.emptybranch span:before, .block_navigation .block_tree .contains_branch .tree_item.emptybranch span:before, .block_settings .block_tree .contains_branch .tree_item.branch a:before, .block_navigation .block_tree .contains_branch .tree_item.branch a:before, .block_settings .block_tree .contains_branch .tree_item.branch span:before, .block_navigation .block_tree .contains_branch .tree_item.branch span:before {
            color: '.$bgcolor_header.' ;
        }';
        $companycss .= '.block_settings .block_tree li p.active_tree_node, .block_navigation .block_tree li p.active_tree_node, .block_settings .block_tree li p.active_tree_node:hover, .block_navigation .block_tree li p.active_tree_node:hover {
            background-color: '.$bgcolor_header.' ;
        }';
        $companycss .= '.block_settings .block_tree li p:hover, .block_navigation .block_tree li p:hover, .block_settings .block_tree li p:active, .block_navigation .block_tree li p:active, .block_settings .block_tree li p:focus, .block_navigation .block_tree li p:focus {
            background-color: '.$bgcolor_header.' ;
        }';
        $companycss .= '.block .minicalendar td.weekend {
            color: '.$bgcolor_header.' ;
        }';
        $companycss .= '.iomadlink_container .iomadlink .iomadicon .fa-action {
            background-color: '.$bgcolor_header.' ;
        }';
        $companycss .= '.mform fieldset.collapsible legend a.fheader:hover, .mform legend.ftoggler:hover, .mform fieldset.collapsible legend a.fheader:active, .mform legend.ftoggler:active {
            color: '.$bgcolor_header.' ;
        }';
        $companycss .= 'input.form-submit, input#id_submitbutton, input#id_submitbutton2, .path-admin .buttons input[type="submit"], td.submit input, input.form-submit:hover, input#id_submitbutton:hover, input#id_submitbutton2:hover, .path-admin .buttons input[type="submit"]:hover, td.submit input:hover, input.form-submit:focus, input#id_submitbutton:focus, input#id_submitbutton2:focus, .path-admin .buttons input[type="submit"]:focus, td.submit input:focus, input.form-submit:active, input#id_submitbutton:active, input#id_submitbutton2:active, .path-admin .buttons input[type="submit"]:active, td.submit input:active, input.form-submit.active, input#id_submitbutton.active, input#id_submitbutton2.active, .path-admin .buttons input[type="submit"].active, td.submit input.active, .open .dropdown-toggleinput.form-submit, .open .dropdown-toggleinput#id_submitbutton, .open .dropdown-toggleinput#id_submitbutton2, .open .dropdown-toggle.path-admin .buttons input[type="submit"], .open .dropdown-toggletd.submit input {
            background-color: '.$bgcolor_header.' ;
            border-color: '.$bgcolor_header.' ;
        }';
    }
    if (isset($bgcolor_content)) {
            $companycss .= 'body { background-color: '.$bgcolor_content.' ; }';
    }

    $return->footnote = '';
    if (!empty($page->theme->settings->footnote)) {
        $return->footnote = '<div class="footnote text-center">'.$page->theme->settings->footnote.'</div>';
    }

    $return->companycss = $companycss;

    return $return;
}
