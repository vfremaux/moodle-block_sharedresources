<?php

require_once $CFG->libdir.'/formslib.php';

class import_collect_form extends moodleform{
	
	function definition(){
		
		$mform = $this->_form;

		$mform->addElement('hidden', 'course');
		$mform->setType('course', PARAM_INT);

		$mform->addElement('filemanager', 'entries', get_string('filestoadd', 'block_sharedresources'));
		
		$this->add_action_buttons(true);
		
	}

	function validation($data, $files = null){
	} 	
}