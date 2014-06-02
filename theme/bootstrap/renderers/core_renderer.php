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

defined('MOODLE_INTERNAL') || die();

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_bootstrap
 * @copyright  2012
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_bootstrap_core_renderer extends core_renderer {

    public function notification($message, $classes = 'notifyproblem') {
        $message = clean_text($message);

        if ($classes == 'notifyproblem') {
            return html_writer::div($message, 'alert alert-danger');
        }
        if ($classes == 'notifywarning') {
            return html_writer::div($message, 'alert alert-warning');
        }
        if ($classes == 'notifysuccess') {
            return html_writer::div($message, 'alert alert-success');
        }
        if ($classes == 'notifymessage') {
            return html_writer::div($message, 'alert alert-info');
        }
        if ($classes == 'redirectmessage') {
            return html_writer::div($message, 'alert alert-block alert-info');
        }
        return html_writer::div($message, $classes);
    }

    public function navbar_button_burger() {
        $content = '';

        $burger = html_writer::tag('span', 'Toggle navigation', array('class' => 'sr-only'));
        $line = html_writer::tag('span', '', array('class' => 'icon-bar'));

        $burger .= $line . $line . $line;

        $content .= html_writer::tag('button', $burger, array('class' => 'navbar-toggle', 'data-toggle' => 'collapse',
            'data-target' => '#moodle-navbar'));
        return $content;
    }

    public function navbar_button_login($class = null) {
        if (!isloggedin() || isguestuser()) {
            $loginlink = new moodle_url('/login/index.php');
            $content = html_writer::link($loginlink, get_string('login'),
                array('class' => 'btn btn-success navbar-btn btn-sm btn-login pull-right ' . $class));
            return $content;
        }
        return '';
    }

    public function navbar() {
        $breadcrumbs = '';
        foreach ($this->page->navbar->get_items() as $item) {
            $item->hideicon = true;
            $breadcrumbs .= '<li>'.$this->render($item).'</li>';
        }
        if ($breadcrumbs) {
            return "<ol class=breadcrumb>$breadcrumbs</ol>";
        } else {
            return '';
        }
    }

    public function custom_menu($custommenuitems = '') {
        global $CFG;

        if (!empty($CFG->custommenuitems)) {
            $custommenuitems .= $CFG->custommenuitems;
        }
        //Growdly specific no custom menu items
        $custommenuitems = "";
        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render_custom_menu($custommenu,"top");
    }

    protected function render_custom_menu(custom_menu $menu) {
        global $CFG, $USER;

        // TODO: eliminate this duplicated logic, it belongs in core, not
        // here. See MDL-39565.

        $content = '<ul class="nav navbar-nav">';
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }

        return $content.'</ul>';
    }

    public function user_menu() {
        global $CFG;
        $usermenu = new custom_menu('', current_language());
        return $this->render_user_menu($usermenu);
    }

    protected function render_user_menu(custom_menu $menu) {
        global $CFG, $USER, $DB, $COURSE;

        $addusermenu = true;
        $addlangmenu = true;
        $addcoursemenu = true;
        $addmessagemenu = false;
        $recentactivitymenu = false;

        if (!isloggedin() || isguestuser()) {
            $addmessagemenu = false;
            $addusermenu = false;
        }

        if ($addcoursemenu) {
            if ($COURSE->id > 1) {
                require_once($CFG->dirroot .'/course/lib.php');
                $sectionmenu = $menu->add(get_string('jumpto'),
                new moodle_url('#'), get_string('jumpto'), 12);
                $modinfo = get_fast_modinfo($COURSE->id);
                $sections = $modinfo->get_section_info_all();
                $usessections = course_format_uses_sections($COURSE->format);
                $course = course_get_format($COURSE)->get_course();
                $numsections = $course->numsections;

                if ($usessections && !empty($sections)) {
                    for ($i = 0 ; $i <= $numsections; $i++ ) {
                        if (isset($sections[$i])) {
                            $sectionname = get_section_name($COURSE, $sections[$i]);
                            $sectionname = core_text::substr($sectionname, 0, 15).'...';

                            $sectionmenu->add($sectionname,
                            new moodle_url('/course/view.php', array('id' => $COURSE->id), 'section-' . $i), $sectionname);
                        }
                    }
                }
            }
        }

        if ($addmessagemenu) {
            $messages = $this->get_user_messages();
            $messagecount = count($messages);
            $messagemenu = $menu->add(
                $messagecount . ' ' . get_string('messages', 'message'),
                new moodle_url('/message/index.php', array('viewing' => 'recentconversations')),
                get_string('messages', 'message'),
                9999
            );
            foreach ($messages as $message) {

                if (!$message->from) { // Workaround for issue #103.
                    continue;
                }
                $senderpicture = new user_picture($message->from);
                $senderpicture->link = false;
                $senderpicture = $this->render($senderpicture);

                $messagecontent = $senderpicture;
                $messagecontent .= html_writer::start_span('msg-body');
                $messagecontent .= html_writer::start_span('msg-title');
                $messagecontent .= html_writer::span($message->from->firstname . ': ', 'msg-sender');
                $messagecontent .= $message->text;
                $messagecontent .= html_writer::end_span();
                $messagecontent .= html_writer::start_span('msg-time');
                $messagecontent .= html_writer::tag('i', '', array('class' => 'icon-time'));
                $messagecontent .= html_writer::span($message->date);
                $messagecontent .= html_writer::end_span();

                $messageurl = new moodle_url('/message/index.php', array('user1' => $USER->id, 'user2' => $message->from->id));
                $messagemenu->add($messagecontent, $messageurl, $message->state);
            }
        }

        $langs = get_string_manager()->get_list_of_translations();
        if (count($langs) < 2
        or empty($CFG->langmenu)
        or ($this->page->course != SITEID and !empty($this->page->course->lang))) {
            $addlangmenu = false;
        }

        if ($addlangmenu) {
            $language = $menu->add(get_string('language'), new moodle_url('#'), get_string('language'), 10000);
            foreach ($langs as $langtype => $langname) {
                $language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        if ($addusermenu) {
            if (isloggedin()) {
                $userpicture = new user_picture($USER);
                $userpicture->link = false;
                $picture = $this->render($userpicture);
                $userinfo = html_writer::tag('span', fullname($USER), array('class' => 'username'));
                $usermenu = $menu->add($picture . $userinfo, new moodle_url('#'), fullname($USER), 10001);
                $usermenu->add(
                    '<i class="fa fa-user"></i>' . get_string('viewprofile'),
                    new moodle_url('/user/profile.php', array('id' => $USER->id)),

                    get_string('viewprofile')
                );

                $usermenu->add(
                    '<i class="fa fa-cog"></i>' . get_string('editmyprofile'),
                    new moodle_url('/user/edit.php', array('id' => $USER->id)),

                    get_string('editmyprofile')
                );

                $usermenu->add(
                    '<i class="fa fa-folder"></i>' . get_string('myfiles'),
                    new moodle_url('/user/files.php'),
                    get_string('myfiles')
                );

                // $usermenu->add(
                //     '<i class="fa fa-inbox"></i>' . get_string('messages', 'message'),
                //     new moodle_url('/message/index.php', array('viewing' => 'recentconversations')),

                //     get_string('messages', 'message')
                // );

                // $usermenu->add(
                //     '<i class="fa fa-cog"></i>' . get_string('messageoutputs', 'message'),
                //     new moodle_url('/message/edit.php', array('viewing' => 'recentconversations')),

                //     get_string('messages', 'message')
                // );

                // $usermenu->add(
                //     '<i class="fa fa-calendar"></i>' . get_string('calendar', 'calendar'),
                //     new moodle_url('/calendar/view.php', array('view' => 'month')),
                //     get_string('calendar', 'calendar')
                // );

                $usermenu->add(
                    '<i class="fa fa-lock"></i>' . get_string('logout'),
                    new moodle_url('/login/logout.php', array('sesskey' => sesskey(), 'alt' => 'logout')),
                    get_string('logout')
                );
            } 
        }



        if ($recentactivitymenu) {
        // Get the recent activity menu items.
            $courses = enrol_get_my_courses(NULL, 'fullname ASC', '999');
            $max_dropdown_courses = 15;
            $countcourses = 0;
            $menuitems = array();
            $unread_activity = 0;

            foreach ($courses as $mycourse) {
                $thiscourse = $DB->get_record('course', array('id' => $mycourse->id));
                ob_start();
                $this->bootstrap_print_recent_activity($thiscourse);
                $activity = ob_get_contents();
                ob_end_clean();

                if ($countcourses > $max_dropdown_courses ) {
                    $courseactivity = new stdClass();
                    $courseactivity->content = get_string('morecourses', 'theme_bootstrap');
                    $courseactivity->url = new moodle_url('/my');
                    $courseactivity->name = null;
                    $menuitems[] = $courseactivity;
                    break;
                }

                if (strlen($mycourse->fullname) > 80) {
                    $coursename = substr($mycourse->fullname, 0, 80) . '..';
                } else {
                    $coursename = $mycourse->fullname;
                }

                $courseactivitycontent = $coursename . $activity;
                if ($activity != '') {
                    $courseactivity = new stdClass();
                    $courseactivity->content = $courseactivitycontent;
                    $courseactivity->url = new moodle_url('/course/view.php', array('id'=>$mycourse->id));
                    $courseactivity->name = $mycourse->fullname;
                    $menuitems[] = $courseactivity;
                    //$mycourses->add($courseactivity, new moodle_url('/course/view.php', array('id'=>$mycourse->id)), $mycourse->fullname);
                    $countcourses++;
                    $unread_activity++;
                }
            }
            if ($countcourses) {
                $unread_messages_count = ' ' . html_writer::tag('span', $countcourses, array('class'=>'badge badge-warning'));
                $mycourses = $menu->add(get_string('recentactivity') ,
                new moodle_url('#recent'), get_string('recentactivity') . $unread_messages_count , 11);
                foreach ($menuitems as $menuitem) {
                    $mycourses->add($menuitem->content,$menuitem->url,$menuitem->name);
                }
            }
        }

        $content = '<ul class="nav navbar-nav navbar-right">';
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }

        return $content.'</ul>';
    }

    protected function process_user_messages() {

        $messagelist = array();

        foreach ($usermessages as $message) {
            $cleanmsg = new stdClass();
            $cleanmsg->from = fullname($message);
            $cleanmsg->msguserid = $message->id;

            $userpicture = new user_picture($message);
            $userpicture->link = false;
            $picture = $this->render($userpicture);

            $cleanmsg->text = $picture . ' ' . $cleanmsg->text;

            $messagelist[] = $cleanmsg;
        }

        return $messagelist;
    }

    protected function get_user_messages() {
        global $USER, $DB;
        $messagelist = array();
        $maxmessages = 5;

        $readmessagesql = "SELECT id, smallmessage, useridfrom, useridto, timecreated, fullmessageformat, notification
        				     FROM {message_read}
        			        WHERE useridto = :userid
        			     ORDER BY timecreated DESC
        			        LIMIT $maxmessages";
        $newmessagesql = "SELECT id, smallmessage, useridfrom, useridto, timecreated, fullmessageformat, notification
        					FROM {message}
        			       WHERE useridto = :userid";

        $readmessages = $DB->get_records_sql($readmessagesql, array('userid' => $USER->id));

        $newmessages = $DB->get_records_sql($newmessagesql, array('userid' => $USER->id));

        foreach ($newmessages as $message) {
            $messagelist[] = $this->bootstrap_process_message($message, 'new');
        }

        foreach ($readmessages as $message) {
            $messagelist[] = $this->bootstrap_process_message($message, 'old');
        }
        return $messagelist;

    }

    protected function bootstrap_process_message($message, $state) {
        global $DB;
        $messagecontent = new stdClass();

        if ($message->notification) {
            $messagecontent->text = get_string('unreadnewnotification', 'message');
        } else {
            if ($message->fullmessageformat == FORMAT_HTML) {
                $message->smallmessage = html_to_text($message->smallmessage);
            }
            if (core_text::strlen($message->smallmessage) > 15) {
                $messagecontent->text = core_text::substr($message->smallmessage, 0, 15).'...';
            } else {
                $messagecontent->text = $message->smallmessage;
            }
        }

        if ((time() - $message->timecreated ) <= (3600 * 3)) {
            $messagecontent->date = format_time(time() - $message->timecreated);
        } else {
            $messagecontent->date = userdate($message->timecreated, get_string('strftimetime', 'langconfig'));
        }

        $messagecontent->from = $DB->get_record('user', array('id' => $message->useridfrom));
        $messagecontent->state = $state;
        return $messagecontent;
    }

    protected function render_custom_menu_item(custom_menu_item $menunode, $level = 0 ) {
        static $submenucount = 0;

        if ($menunode->has_children()) {

            if ($level == 1) {
                $dropdowntype = 'dropdown';
            } else {
                $dropdowntype = 'dropdown-submenu';
            }

            $content = html_writer::start_tag('li', array('class' => $dropdowntype));
            // If the child has menus render it as a sub menu.
            $submenucount++;
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }
            $linkattributes = array(
                'href' => $url,
                'class' => 'dropdown-toggle',
                'data-toggle' => 'dropdown',
                'title' => $menunode->get_title(),
            );
            $content .= html_writer::start_tag('a', $linkattributes);
            $content .= $menunode->get_text();
            if ($level == 1) {
                $content .= '<b class="caret"></b>';
            }
            $content .= '</a>';
            $content .= '<ul class="dropdown-menu">';
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode, 0);
            }
            $content .= '</ul>';
        } else {
            $content = '<li>';
            // The node doesn't have children so produce a final menuitem.
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('title' => $menunode->get_title()));
        }
        return $content;
    }

    protected function render_tabtree(tabtree $tabtree) {
        if (empty($tabtree->subtree)) {
            return '';
        }
        $firstrow = $secondrow = '';
        foreach ($tabtree->subtree as $tab) {
            $firstrow .= $this->render($tab);
            if (($tab->selected || $tab->activated) && !empty($tab->subtree) && $tab->subtree !== array()) {
                $secondrow = $this->tabtree($tab->subtree);
            }
        }
        return html_writer::tag('ul', $firstrow, array('class' => 'nav nav-tabs nav-justified')) . $secondrow;
    }

    protected function render_tabobject(tabobject $tab) {
        if ($tab->selected or $tab->activated) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text), array('class' => 'active'));
        } else if ($tab->inactive) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text), array('class' => 'disabled'));
        } else {
            if (!($tab->link instanceof moodle_url)) {
                // Backward compatibility when link was passed as quoted string.
                $link = "<a href=\"$tab->link\" title=\"$tab->title\">$tab->text</a>";
            } else {
                $link = html_writer::link($tab->link, $tab->text, array('title' => $tab->title));
            }
            return html_writer::tag('li', $link);
        }
    }


    // protected function render_pix_icon(pix_icon $icon) {
    //     if ($this->page->theme->settings->fonticons === '1'
    //         && $icon->attributes["alt"] === ''
    //         && $this->replace_moodle_icon($icon->pix) !== false) {
    //         return $this->replace_moodle_icon($icon->pix);
    //     }
    //     return parent::render_pix_icon($icon);
    // }

    protected function replace_moodle_icon($name) {
        $icons = array(
            'add' => 'plus',
            'book' => 'book',
            'chapter' => 'file',
            'docs' => 'question-sign',
            'generate' => 'gift',
            'i/backup' => 'download',
            't/backup' => 'download',
            'i/checkpermissions' => 'user',
            'i/edit' => 'pencil',
            'i/filter' => 'filter',
            'i/grades' => 'grades',
            'i/group' => 'user',
            'i/hide' => 'eye-open',
            'i/import' => 'upload',
            'i/info' => 'info',
            'i/move_2d' => 'move',
            'i/navigationitem' => 'chevron-right',
            'i/publish' => 'globe',
            'i/reload' => 'refresh',
            'i/report' => 'list-alt',
            'i/restore' => 'upload',
            't/restore' => 'upload',
            'i/return' => 'repeat',
            'i/roles' => 'user',
            'i/settings' => 'cog',
            'i/show' => 'eye-close',
            'i/switchrole' => 'user',
            'i/user' => 'user',
            'i/users' => 'user',
            'spacer' => 'spacer',
            't/add' => 'plus',
            't/assignroles' => 'user',
            't/copy' => 'plus-sign',
            't/delete' => 'remove',
            't/down' => 'arrow-down',
            't/edit' => 'edit',
            't/editstring' => 'tag',
            't/hide' => 'eye-open',
            't/left' => 'arrow-left',
            't/move' => 'resize-vertical',
            't/right' => 'arrow-right',
            't/show' => 'eye-close',
            't/switch_minus' => 'minus-sign',
            't/switch_plus' => 'plus-sign',
            't/up' => 'arrow-up',
        );
        if (isset($icons[$name])) {
            return '<span class="glyphicon glyphicon-'.$icons[$name].'"></span> ';
        } else {
            return false;
        }
    }

    function bootstrap_print_recent_activity($course) {
        // $course is an object
        global $CFG, $USER, $SESSION, $DB, $OUTPUT;

        $context = context_course::instance($course->id);

        $viewfullnames = has_capability('moodle/site:viewfullnames', $context);

        $timestart = round(time() - COURSE_MAX_RECENT_PERIOD, -2); // better db caching for guests - 100 seconds

        if (!isguestuser()) {
            if (!empty($USER->lastcourseaccess[$course->id])) {
                if ($USER->lastcourseaccess[$course->id] > $timestart) {
                    $timestart = $USER->lastcourseaccess[$course->id];
                }
            }
        }


        $content = false;

        /// Firstly, have there been any new enrolments?

        $users = get_recent_enrolments($course->id, $timestart);

        //Accessibility: new users now appear in an <OL> list.
        if ($users) {
            echo '<div class="newusers">';
            echo $OUTPUT->heading(get_string("newusers").':', 3);
            $content = true;
            echo "<ol class=\"list\">\n";
            foreach ($users as $user) {
                $fullname = fullname($user, $viewfullnames);
                echo '<li class="name"><a href="'."$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id\">$fullname</a></li>\n";
            }
            echo "</ol>\n</div>\n";
        }

        /// Next, have there been any modifications to the course structure?

        $modinfo = get_fast_modinfo($course);

        $changelist = array();

        $logs = $DB->get_records_select('log', "time > ? AND course = ? AND
                                            module = 'course' AND
                                            (action = 'add mod' OR action = 'update mod' OR action = 'delete mod')",
        array($timestart, $course->id), "id ASC");

        if ($logs) {
            $actions  = array('add mod', 'update mod', 'delete mod');
            $newgones = array(); // added and later deleted items
            foreach ($logs as $key => $log) {
                if (!in_array($log->action, $actions)) {
                    continue;
                }
                $info = explode(' ', $log->info);

                // note: in most cases I replaced hardcoding of label with use of
                // $cm->has_view() but it was not possible to do this here because
                // we don't necessarily have the $cm for it
                if ($info[0] == 'label') {     // Labels are ignored in recent activity
                    continue;
                }

                if (count($info) != 2) {
                    debugging("Incorrect log entry info: id = ".$log->id, DEBUG_DEVELOPER);
                    continue;
                }

                $modname    = $info[0];
                $instanceid = $info[1];

                if ($log->action == 'delete mod') {
                    // unfortunately we do not know if the mod was visible
                    if (!array_key_exists($log->info, $newgones)) {
                        $strdeleted = get_string('deletedactivity', 'moodle', get_string('modulename', $modname));
                        $changelist[$log->info] = array ('operation' => 'delete', 'text' => $strdeleted);
                    }
                } else {
                    if (!isset($modinfo->instances[$modname][$instanceid])) {
                        if ($log->action == 'add mod') {
                            // do not display added and later deleted activities
                            $newgones[$log->info] = true;
                        }
                        continue;
                    }
                    $cm = $modinfo->instances[$modname][$instanceid];
                    if (!$cm->uservisible) {
                        continue;
                    }

                    if ($log->action == 'add mod') {
                        $stradded = get_string('added', 'moodle', get_string('modulename', $modname));
                        $changelist[$log->info] = array('operation' => 'add', 'text' => "$stradded:<br /><a href=\"$CFG->wwwroot/mod/$cm->modname/view.php?id={$cm->id}\">".format_string($cm->name, true)."</a>");

                    } else if ($log->action == 'update mod' and empty($changelist[$log->info])) {
                        $strupdated = get_string('updated', 'moodle', get_string('modulename', $modname));
                        $changelist[$log->info] = array('operation' => 'update', 'text' => "$strupdated:<br /><a href=\"$CFG->wwwroot/mod/$cm->modname/view.php?id={$cm->id}\">".format_string($cm->name, true)."</a>");
                    }
                }
            }
        }

        if (!empty($changelist)) {
            $content = true;
            foreach ($changelist as $changeinfo => $change) {
                echo '<p class="activity">'.$change['text'].'</p>';
            }
        }

        /// Now display new things from each module

        $usedmodules = array();
        foreach($modinfo->cms as $cm) {
            if (isset($usedmodules[$cm->modname])) {
                continue;
            }
            if (!$cm->uservisible) {
                continue;
            }
            $usedmodules[$cm->modname] = $cm->modname;
        }

        foreach ($usedmodules as $modname) {      // Each module gets it's own logs and prints them
            if (file_exists($CFG->dirroot.'/mod/'.$modname.'/lib.php')) {
                include_once($CFG->dirroot.'/mod/'.$modname.'/lib.php');
                $print_recent_activity = $modname.'_print_recent_activity';
                if (function_exists($print_recent_activity)) {
                    // NOTE: original $isteacher (second parameter below) was replaced with $viewfullnames!
                    $content = $print_recent_activity($course, $viewfullnames, $timestart) || $content;
                }
            } else {
                debugging("Missing lib.php in lib/{$modname} - please reinstall files or uninstall the module");
            }
        }
    }

    public function bootstrap_footer_widget($i) {

        $title = 'footertitle' . $i;
        $text = 'footertext' . $i;

        $content = html_writer::start_tag('div',  array('class' => 'footerwidget'));
        if (isset($this->page->theme->settings->$title)) {
            $headingtitle = html_writer::tag('span', $this->page->theme->settings->$title);
            $content .= html_writer::tag('h4', $headingtitle, array('class' => 'footerheading'));
        }
        if (isset($this->page->theme->settings->$text)) {
            $content .= html_writer::start_tag('div',  array('class' => 'footertext'));
            $content .= $this->page->theme->settings->$text;
            if ($i == 3) {
                $content .= '<br>'. $this->page_doc_link();
                $content .= $this->course_footer();
                $content .= $this->standard_footer_html();
            }
            $content .= html_writer::end_tag('div');

        }
        $content .= html_writer::end_tag('div');

        return $content;
    }

    public function bootstrap_calltoaction($container) {
        if (isset($this->page->theme->settings->calltoactiontext) &&
            isset($this->page->theme->settings->calltoactionlinktext) &&
            isset($this->page->theme->settings->calltoactionlink)) {
            $calltoactiontext = $this->page->theme->settings->calltoactiontext;
            $calltoactionlinktext = $this->page->theme->settings->calltoactionlinktext;
            $calltoactionlink = $this->page->theme->settings->calltoactionlink;
        } else {
            return '';
        }
        $content = '';
        $content .= html_writer::start_tag('div',  array('class' => $container));
        $content .= html_writer::start_tag('div',  array('class' => 'row calltoaction'));
        $content .= html_writer::start_tag('div',  array('class' => 'contenttext col-md-8 col-md-push-1'));
        $content .= html_writer::tag('span', $calltoactiontext, array('class' => 'calltoactiontext'));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('div',  array('class' => 'contentlink col-md-2 col-md-push-1'));
        $content .= html_writer::link($calltoactionlink, $calltoactionlinktext, array('class' => 'calltoactionlink'));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        return $content;
    }

    public function bootstrap_quote () {
        if (isset($this->page->theme->settings->quotes)) {
            $quotes = $this->page->theme->settings->quotes;
        } else {
            return '';
        }
        $quote_lines = preg_split('/\r\n|\r|\n/', $quotes);
        $quote_count = count($quote_lines);
        return $quote_lines[rand(0,$quote_count -1)];
    }

    public function bootstrap_nocourses() {
        global $DB, $USER;
        $content = '';
        if (!isloggedin()) {
            return '';
        }
        if (is_siteadmin()) {
            return '';
        }
        if (isset($this->page->theme->settings->nocourses)) {
            $nocourses = $this->page->theme->settings->nocourses;
        } else {
            return '';
        }
        $enrolled = enrol_get_my_courses();
        if (!$enrolled) {
            if (!$cm = get_coursemodule_from_id('page', $nocourses)) {
                print_error('invalidcoursemodule');
            }
            if ($page = $DB->get_record('page', array('id'=>$cm->instance), '*', MUST_EXIST)) {
                $content .= html_writer::tag('h2', $page->name);
                $content .= html_writer::tag('div',$page->content, array('class' => "box generalbox center clearfix"));
                return $content;
            }
        }
    }
}
