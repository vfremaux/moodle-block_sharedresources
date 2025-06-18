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
 * @package    block_sharedresources
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This page provides facility to push resources from the accessible repositories
 * as sharedresources
 * Handled repositories are :
 * - user attached files
 * - active repositories
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/blocks/sharedresources/forms/import_collect_form.php');
require_once($CFG->dirroot.'/blocks/sharedresources/forms/metadata_collect_form.php');
require_once($CFG->dirroot.'/blocks/sharedresources/lib.php');

$courseid = optional_param('course', SITEID, PARAM_INT);
$step = optional_param('step', 1, PARAM_INT);

if (!$course = $DB->get_record('course', array( 'id' => $courseid))) {
    print_error('coursemisconf');
}

// Security.

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:manageactivities', $context);

// Prepare the page.

$PAGE->set_context($context);
$PAGE->requires->js('/blocks/sharedresources/js/js.js');
$PAGE->set_title(get_string('sharedresources_library', 'local_sharedresources'));
$PAGE->set_heading(get_string('sharedresources_library', 'local_sharedresources'));
$PAGE->navbar->add($course->fullname, '/course/view.php?id='.$courseid);
$PAGE->navbar->add(get_string('import', 'block_sharedresources'));

$url = new moodle_url('/blocks/sharedresources/importresourcesfromfiles.php');
$params = array('course' => $courseid);

$PAGE->set_url($url, $params);

// Print header.

if ($step == 1) {

    $form = new import_collect_form();

    if ($form->is_cancelled()) {
        redirect(new moodle_url('/blocks/sharedresources/importresourcesfromfiles.php', array('course' => $courseid)));
    }

    if ($data = $form->get_data()) {

        $form2 = new metadata_collect_form($url, array('entries' => $data->entries));

        $formdata = new StdClass;
        $formdata->course = $courseid;
        $formdata->entries = $data->entries; // draft file area item id
        $form2->set_data($formdata);

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('importresourcesfromfilestitle', 'block_sharedresources'));

        $form2->display();

        echo $OUTPUT->footer();
        exit;
    }

    $formdata = new StdClass;
    $formdata->course = $courseid;
    $form->set_data($formdata);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('importresourcesfromfilestitle', 'block_sharedresources'));

    $form->display();

    print($OUTPUT->footer($course));

} else if ($step == 2) {

    // We can process step 2.
    // We need pass this value to build metdata form elements for collected items.
    $form2 = new metadata_collect_form($url, array('entries' => $_POST['entries']));

    if ($form2->is_cancelled()) {
        redirect(new moodle_url('/blocks/sharedresources/importresourcesfromfiles.php', array('course' => $courseid)));
    }

    if ($data2 = $form2->get_data()) {

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('importresourcesfromfilestitle', 'block_sharedresources'));

        sharedresources_process_entries($data2, $course);


        if ($course->format == 'page') {
            $page = \format_page\course_page::get_current_page($course->id);
            $buttonurl = new moodle_url('/course/view.php', array('id' => $courseid, 'page' => $page->id));
            echo $OUTPUT->continue_button($buttonurl);
        } else {
            echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid)));
        }

        echo $OUTPUT->footer();
        exit;
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('importresourcesfromfilestitle', 'block_sharedresources'));
    $form2->display();
    echo $OUTPUT->footer();

}

