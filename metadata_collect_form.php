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

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @package    block_sharedresource
 * @category   blocks
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require_once($CFG->libdir.'/formslib.php');

class metadata_collect_form extends moodleform {

    public function definition() {
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

        foreach ($files as $f) {

            $fileid = $f->get_id();
            $mform->addElement('header', 'head'.$fileid, $f->get_filename());

            // Essential metadata.
            $mform->addElement('hidden', 'id'.$fileid, $fileid);
            $mform->setType('id'.$fileid, PARAM_INT);

            $mform->addElement('text', 'title'.$fileid, get_string('title', 'block_sharedresources'), array('size' => 60));
            $mform->addRule('title'.$fileid, get_string('error'), 'required', '', 'client', false, false);
            $mform->setType('title'.$fileid, PARAM_CLEANHTML);

            $mform->addElement('textarea', 'description'.$fileid, get_string('description'), array('cols' => 60, 'rows' => 4));
            $mform->setType('description'.$fileid, PARAM_CLEANHTML);
            $mform->addRule('description'.$fileid, get_string('error'), 'required', '', 'client', false, false);

            $mform->addElement('text', 'keywords'.$fileid, get_string('keywords', 'block_sharedresources'), array('size' => 30));
            $mform->setType('keywords'.$fileid, PARAM_TEXT);

            // Sharing contexts.
            $mform->addElement('select', 'context'.$fileid, get_string('sharingcontext', 'sharedresource'), $contextopts);
            $mform->setType('context'.$fileid, PARAM_INT);
            $mform->addHelpButton('context'.$fileid, 'sharingcontext', 'sharedresource');

            $mform->addElement('checkbox', 'coursepublish'.$fileid, get_string('publishincourse', 'block_sharedresources'));
            $mform->setDefault('coursepublish'.$fileid, 1);

            $mform->addElement('checkbox', 'overwritedata'.$fileid, get_string('overwritemetadata', 'block_sharedresources'));
            $mform->setDefault('overwritedata'.$fileid, 0);

            // TODO : Get additional widgets that are enabled in search engine.
        }

        $this->add_action_buttons(true);
    }

    public function validation($data, $files = null) {
    }
}