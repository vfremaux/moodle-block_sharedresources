<?php

/**
 * Moodle - Modular Object-Oriented Dynamic Learning Environment
 *          http://moodle.org
 * Copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    blocks
 * @subpackage block_sharedreosurce
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This page provides facility to push resources from the accessible repositories
 * as sharedresources
 * Handled repositories are : 
 * - user attached files
 * - active repositories
 */

    include '../../config.php';
    require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
    require_once($CFG->dirroot.'/blocks/sharedresources/import_collect_form.php');
    require_once($CFG->dirroot.'/blocks/sharedresources/metadata_collect_form.php');
    require_once($CFG->dirroot.'/blocks/sharedresources/lib.php');

    global $CFG;
    
    $courseid = optional_param('course', SITEID, PARAM_INT);
    $step = optional_param('step', 1, PARAM_INT);
    
    if (!$course = $DB->get_record('course', array( 'id' => $courseid))){
        print_error('coursemisconf');
    }

/// Security
    
    $context = get_context_instance(CONTEXT_COURSE, $course->id);    
    require_capability('moodle/course:manageactivities', $context);

   // prepare the page.
    
    $PAGE->set_context($context);
    $PAGE->requires->js('/blocks/sharedresources/js/js.js');    
    $PAGE->set_title(get_string('sharedresources_library', 'local_sharedresources'));
    $PAGE->set_heading(get_string('sharedresources_library', 'local_sharedresources'));
    $PAGE->navbar->add($course->fullname, '/course/view.php?id='.$courseid);
    $PAGE->navbar->add(get_string('import', 'block_sharedresources'));

    $url = $CFG->wwwroot.'/blocks/sharedresources/importresourcesfromfiles.php';
    $params = array('course' => $courseid);

    $PAGE->set_url($url, $params);
    
/// Print header

	if ($step == 1){

	    $form = new import_collect_form();
	    
	    if ($form->is_cancelled()){
			redirect($CFG->wwwroot.'/blocks/sharedresources/importresourcesfromfiles.php?course='.$courseid);
	    }
	    
	    if ($data = $form->get_data()){

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

	} elseif ($step == 2){

    	// we can process step 2
    	$form2 = new metadata_collect_form($url, array('entries' => $_POST['entries'])); // we need pass this value to build metdata form elements for collected items.

	    if ($form2->is_cancelled()){
			redirect($CFG->wwwroot.'/blocks/sharedresources/importresourcesfromfiles.php?course='.$courseid);
	    }
	    
    	if ($data2 = $form2->get_data()){

		    echo $OUTPUT->header();       
		    echo $OUTPUT->heading(get_string('importresourcesfromfilestitle', 'block_sharedresources'));

			sharedresources_process_entries($data2, $course);

		    if ($course->format == 'page'){
		        echo $OUTPUT->continue_button($CFG->wwwroot."/course/view.php?id={$courseid}&amp;action=activities");
		    } else {
		        echo $OUTPUT->continue_button($CFG->wwwroot."/course/view.php?id={$courseid}");
		    }

		    echo $OUTPUT->footer();
		    exit;
    	}
	}

?>