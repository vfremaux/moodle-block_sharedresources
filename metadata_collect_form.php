<?php

require_once $CFG->libdir.'/formslib.php';

class metadata_collect_form extends moodleform{
	
	function definition(){
		global $USER;

		$itemid = $this->_customdata['entries'];
		$mform = $this->_form;

		$mform->addElement('hidden', 'course');
		$mform->setType('course', PARAM_INT);

		$mform->addElement('hidden', 'step', 2);
		$mform->setType('step', PARAM_INT);
		
		$mform->addElement('hidden', 'entries'); // draft area item id
		$mform->setType('entries', PARAM_INT);

		$fs = get_file_storage();
		$usercontext = context_user::instance($USER->id);
		$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $itemid, 'filepath, filename', $includedirs = false);

		$contextopts[1] = get_string('systemcontext', 'sharedresource');
		sharedresource_add_accessible_contexts($contextopts);

		foreach($files as $f){
			
			$fileid = $f->get_id();
			$mform->addElement('header', 'head'.$fileid, $f->get_filename());
			
			// essential metadata
			$mform->addElement('hidden', 'id'.$fileid, $fileid);
			$mform->setType('id', PARAM_INT);

			$mform->addElement('text', 'title'.$fileid, get_string('title', 'block_sharedresources'), array('size' => 60));
			$mform->setType('title'.$fileid, PARAM_TEXT);
			$mform->addRule('title'.$fileid, get_string('error'), 'required', '', 'client', false, false);

			$mform->addElement('textarea', 'description'.$fileid, get_string('description'), array('cols' => 60, 'rows' => 4));
			$mform->setType('description'.$fileid, PARAM_CLEANHTML);
			$mform->addRule('description'.$fileid, get_string('error'), 'required', '', 'client', false, false);

			$mform->addElement('text', 'keywords'.$fileid, get_string('keywords', 'block_sharedresources'), array('size' => 30));
			$mform->setType('keywords'.$fileid, PARAM_TEXT);

			// sharing contexts
	        $mform->addElement('select', 'context'.$fileid, get_string('sharingcontext', 'sharedresource'), $contextopts);
	        $mform->setType('context'.$fileid, PARAM_INT);
	        $mform->addHelpButton('context'.$fileid, 'sharingcontext', 'sharedresource');

			$mform->addElement('checkbox', 'coursepublish'.$fileid, get_string('publishincourse', 'block_sharedresources'));
			$mform->setDefault('coursepublish'.$fileid, 1);

			$mform->addElement('checkbox', 'overwritedata'.$fileid, get_string('overwritemetadata', 'block_sharedresources'));
			$mform->setDefault('overwritedata'.$fileid, 0);
			
			// todo : get additional widgets that are enabled in search engine.
		}
		
		$this->add_action_buttons(true);		
	}

	function validation($data, $files = null){
	} 	
}