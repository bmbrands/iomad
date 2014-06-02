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


$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);

$knownregionpre = $PAGE->blocks->is_known_region('side-pre');
$knownregionpost = $PAGE->blocks->is_known_region('side-post');
$knownregionpost = $PAGE->blocks->is_known_region('side-post');
$knownregiontop = $PAGE->blocks->is_known_region('page-top');
$hasbanner = (!empty($PAGE->layout_options['banner']));
$nocoursesmsg = (!empty($PAGE->layout_options['nocoursesmsg']));
$nomoodleheader = (!isset($PAGE->layout_options['moodleheader']));
$oauth = (!empty($PAGE->layout_options['oauth']));
$backimage = (!empty($PAGE->layout_options['backimage']));
$nofixedbackground = (!empty($PAGE->layout_options['nofixedbackground']));
$nofixedbackground = true;
$showhome = optional_param('showhome', '', PARAM_TEXT);

$regions = bootstrap_grid($hassidepre, $hassidepost);
$PAGE->set_popup_notification_allowed(false);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('bootstrap', 'theme_bootstrap');

$html = theme_bootstrap_get_html_for_settings($OUTPUT, $PAGE);

if ($backimage) {
    $PAGE->requires->jquery_plugin('backstrech', 'theme_bootstrap');
}

$contentstate = 'showcontent';
if ($showhome != "yes" && (!isloggedin() || isguestuser()) ) {
    $contentstate = 'hidecontent';
}

$homelink = get_string('mycourses');
if (!isloggedin() || isguestuser()) {
    //$knownregionpre = false;
    //$knownregionpost = false;
    $homelink = get_string('home');
}


$fluid = (!empty($PAGE->layout_options['fluid']));
$container = 'container';
if (isset($PAGE->theme->settings->fluidwidth) && ($PAGE->theme->settings->fluidwidth == true)) {
    $container = 'container-fluid';
}
if ($fluid) {
    $container = 'container-fluid';
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <style><?php echo $html->companycss ?></style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body <?php echo $OUTPUT->body_attributes(array('class' => $contentstate)); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>
<?php if (!$backimage ) { 
    if (!$nofixedbackground) { ?>
<div class="fixedbackground">
    <img src="<?php echo $OUTPUT->pix_url('background', 'theme');?>">
</div>
<?php } }?>

<div class="wrapperfixedfooter">
    <div class="pagewrap">
        <div class="growdlyheader">
            <div class="<?php echo $container; ?>">
                <div class="growdlybrand">
                    <a id="moodlehome" href="<?php echo $CFG->wwwroot; ?>">
                        <img src="<?php echo $html->heading;?>">
                    </a>
                </div>
            </div>
        </div>
        <nav role="navigation" class="navbar navbar-inverse">
            <div class="<?php echo $container; ?>">
                <div class="navbar-header">
                    <?php echo $OUTPUT->navbar_button_login('visible-xs'); ?>
                    <?php echo $OUTPUT->navbar_button_burger(); ?>
                    <div class="brandwrapper">
                        <a id="moodlehome" class="navbar-brand" href="<?php echo $CFG->wwwroot; ?>">
                            <?php echo $homelink; ?>
                        </a>
                    </div>
                </div>
                <?php echo $OUTPUT->navbar_button_login('hidden-xs'); ?>
                <div id="moodle-navbar" class="navbar-collapse collapse">
                    <?php echo $OUTPUT->custom_menu(); ?>
                    <?php echo $OUTPUT->user_menu(); ?>
                    <ul class="nav pull-right">
                        <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
                    </ul>
                </div>
            </div>
        </nav>
         <div id="gwslider" class="<?php echo $container; ?>">
            <?php if ($hasbanner) {
                if ($showhome != "yes" && (!isloggedin() || isguestuser()) ) {
                    include($CFG->dirroot . '/theme/bootstrap/layout/includes/slideshow.php'); 
                    echo $OUTPUT->bootstrap_calltoaction($container);
                }
            }
            ?>
        </div>

        <?php if (!$hasbanner) { 
            if (!$nomoodleheader) {?>
            <header class="moodleheader">
                <div class="<?php echo $container; ?>">
                    <?php echo $OUTPUT->page_heading(); ?>
                </div>
            </header>
        <?php } 
        } ?>

        <div id="page" class="<?php echo $container; ?>">
            <header id="page-header" class="clearfix">
                <div id="page-navbar" class="clearfix">
                    <nav class="breadcrumb-nav" role="navigation" aria-label="breadcrumb"><?php echo $OUTPUT->navbar(); ?></nav>
                    <div class="breadcrumb-button"><?php echo $OUTPUT->page_heading_button(); ?></div>
                </div>

                <div id="course-header">
                    <?php echo $OUTPUT->course_header(); ?>
                </div>
            </header>

            <div id="page-content" class="row">
                <div id="region-main" class="<?php echo $regions['content']; ?>">
                    <?php
                    if ($knownregiontop) {
                        echo $OUTPUT->blocks('page-top', 'page-top');
                    }
                    echo $OUTPUT->course_content_header();
                    if ($nocoursesmsg) {
                        echo $OUTPUT->bootstrap_nocourses();
                    }
                    echo $OUTPUT->main_content();
                    echo $OUTPUT->course_content_footer();
                    if ($oauth) {
                        require_once($CFG->dirroot . '/auth/googleoauth2/lib.php');
                        ?>
                        <div class="hidden">
                        <?php auth_googleoauth2_display_buttons();?>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <?php
                if ($knownregionpre) {
                    echo $OUTPUT->blocks('side-pre', $regions['pre']);
                }?>
                <?php
                if ($knownregionpost) {
                    echo $OUTPUT->blocks('side-post', $regions['post']);
                }?>
            </div>
            <?php echo $OUTPUT->standard_end_of_body_html() ?>
        </div>
    </div>
</div>
<footer id="page-footer" class="bootstrapfooter">
    <div class="<?php echo $container; ?> innerfooter">
       <div class="row">
          <div class="footinner">
               <div class="col-sm-4">
                   <?php echo $OUTPUT->bootstrap_footer_widget(1); ?>
               </div>
               <div class="col-sm-4">
                   <?php echo $OUTPUT->bootstrap_footer_widget(2);?>
               </div>
               <div class="col-sm-4">
                   <?php echo $OUTPUT->bootstrap_footer_widget(3);?>
               </div>
               <div class="clearfix">
               </div>

            </div>
        </div>
    </div>
</footer>

<?php if ($backimage) {
    if (core_useragent::get_device_type() != 'mobile') {
        echo theme_bootstrap_page_background($PAGE);
    }
}?>

</body>
</html>
