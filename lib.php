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
 * @package    blocks
 * @subpackage block_sharedresources
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
require_once($CFG->dirroot.'/mod/sharedresource/sharedresource_plugin_base.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');

/**
* processes a set of file entries to convert them as file ressources
* 1. create a Moodle resource that uses the file
* 2. create a Moodle course_module that attaches the resource to the course 
* 3. create a page format page_item that puts the resource in the page
*/
function sharedresources_process_entries(&$data, &$course) {
    global $USER;

    $usercontext = context_user::instance($USER->id);

    $fs = get_file_storage();

    $filestoprocess = $fs->get_area_files($usercontext->id, 'user', 'draft', $data->entries, "itemid, filepath, filename", false);

    foreach($filestoprocess as $file) {

        $fileid = $file->get_id();
        $metadata = new StdClass;
        $metadata->title = $data->{'title'.$fileid};
        $metadata->description = $data->{'description'.$fileid};
        $metadata->keywords = $data->{'keywords'.$fileid};
        $metadata->context = $data->{'context'.$fileid};
        $metadata->publish = $data->{'coursepublish'.$fileid};
        $metadata->overwrite = @$data->{'overwritedata'.$fileid};

        sharedresources_process_single_entry($file, $metadata, $course);
    }

    // full cleans out file area after processing
    $fs->delete_area_files($usercontext->id, 'user', 'draft', $data->entries);
}

function sharedresources_process_single_entry(stored_file $file, $metadata = array(), &$course) {
    global $DB, $CFG;

    // create moodle resource

    $module = $DB->get_record('modules', array('name'=> 'sharedresource'));

    /// check for sharedresourceentry and add if not here

    $identifier = $file->get_contenthash();

    if (!$sharedentry = $DB->get_record('sharedresource_entry', array('identifier' => $identifier))) {

        $shentry = new StdClass;
        $shentry->title = $metadata->title;
        $shentry->type = 'file';
        $shentry->mimetype = $file->get_mimetype();
        $shentry->identifier = $identifier;
        $shentry->remoteid = '';
        $shentry->file = $file->get_id();
        $shentry->url = $CFG->wwwroot.'/mod/sharedresource/view.php?identifier='.$identifier;
        $shentry->lang = ''; // not more used
        $shentry->description = @$metadata->description;
        $shentry->keywords = @$metadata->keywords;
        $shentry->timemodified = time();
        $shentry->provider = 'local';
        $shentry->isvalid = 1;
        $shentry->context = $metadata->context;
        $shentry->scoreview = 0;
        $shentry->scorelike = 0;
        try {
            $sharedresourceentryid = $DB->insert_record('sharedresource_entry', $shentry);

            /// give some traces
            print_string('builtentry', 'block_sharedresources', $shentry->identifier);

            // dispatch metadata into entry
            $mtdstandard = sharedresource_plugin_base::load_mtdstandard($CFG->pluginchoice);
            $mtdstandard->setTextElementValue($mtdstandard->getDescriptionElement()->name, '', $shentry->description);
            $mtdstandard->setTextElementValue($mtdstandard->getTitleElement()->name, '', $shentry->title);
            if (!empty($shentry->keywords)) {
                $mtdstandard->setKeywords($shentry->keywords);
            }

            // store now the draft file in sharedresource filearea
    
            $systemcontext = context_system::instance();
    
            $fs = get_file_storage();
            $filerecord = new StdClass;
            $filerecord->contextid = $systemcontext->id;
            $filerecord->component = 'mod_sharedresource';
            $filerecord->filearea = 'sharedresource';
            $filerecord->itemid = $sharedresourceentryid;
            $filerecord->path = '/';
            $newfile = $fs->create_file_from_storedfile($filerecord, $file);

            // remap sharedresource with new file record
            $DB->set_field('sharedresource_entry', 'file', $newfile->get_id(), array('identifier' => $identifier));
        } catch(Exception $e) {
        }

    } else {
        if ($metadata->overwrite) {

            /// give some traces
            print_string('existsupdating', 'block_sharedresources', $sharedentry->title);

            $sharedentry->context = $metadata->context;
            $sharedentry->description = @$metadata->description;
            $sharedentry->keywords = @$metadata->keywords;
            $DB->update_record('sharedresource_entry', $sharedentry);

            // dispatch metadata into entry
            $mtdstandard = sharedresource_plugin_base::load_mtdstandard($CFG->pluginchoice);
            $mtdstandard->setTextElementValue($mtdstandard->getDescriptionElement()->name, '', $sharedentry->description);
            $mtdstandard->setTextElementValue($mtdstandard->getTitleElement()->name, '', $sharedentry->title);
            if (!empty($shentry->keywords)) {
                $mtdstandard->setKeywords($shentry->keywords);
            }
        } else {
            /// give some traces
            print_string('existsignorechanges', 'block_sharedresources', $sharedentry->title);
        }
    }

    if ($metadata->publish) {
        if ($course->id != SITEID) {
            /// complete a sharedresource record
            $sharedresource = new StdClass;
            $sharedresource->course = $course->id;
            $sharedresource->type = 'file';
            $sharedresource->name = $metadata->title;
            $sharedresource->summary = @$metadata->description;
            $sharedresource->identifier = $identifier;
            $sharedresource->alltext = '';
            $sharedresource->popup = 0;
            $sharedresource->options = '';
            $sharedresource->timemodified = time();

            $sharedresource->id = $DB->insert_record('sharedresource', $sharedresource);

            $cm = new StdClass;
            $cm->course = $course->id;
            $cm->module = $module->id;
            $cm->instance = $sharedresource->id;
            $cm->section = 1;
            $cm->visible = 1;

            $cm->coursemodule = add_course_module($cm);
            course_add_cm_to_section($course, $cm->coursemodule, sharedresource_get_course_section_to_add($course));

            // finish with a pageitem if we are in a page format

            if ($course->format == 'page') {
                include_once($CFG->dirroot.'/course/format/page/page.class.php');
                $page = course_page::get_current_page($course->id);
                $pageitem = new StdClass;
                $pageitem->pageid = $page->id;
                $pageitem->cmid = $cm->coursemodule;
                $pageitem->blockinstance = 0;
                $pageitem->position = 'c';
                $pageitem->sortorder = get_field_select('format_page_items', 'MAX(sortorder)', " pageid = $pageitem->pageid AND position = 'c' ") + 1;
                $pageitem->visible = 1;

                $DB->insert_record('format_page_items', $pageitem);
            }
            /// give some traces
            print_string('constructed', 'block_sharedresources', $sharedresource->id);
        }
    }
}