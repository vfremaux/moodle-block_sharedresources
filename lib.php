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
 * @category   blocks
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_plugin_base.class.php');
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

    foreach ($filestoprocess as $file) {

        $fileid = $file->get_id();
        $metadata = new StdClass;
        $metadata->title = $data->{'title'.$fileid};
        $metadata->description = $data->{'description'.$fileid};
        $metadata->keywords = $data->{'keywords'.$fileid};
        $metadata->context = $data->{'context'.$fileid};
        $metadata->publish = @$data->{'coursepublish'.$fileid};
        $metadata->overwrite = @$data->{'overwritedata'.$fileid};

        sharedresources_process_single_entry($file, $metadata, $course);
    }

    // Full cleans out file area after processing.
    $fs->delete_area_files($usercontext->id, 'user', 'draft', $data->entries);
}

function sharedresources_process_single_entry(stored_file $file, $metadata = array(), &$course) {
    global $DB, $CFG, $PAGE;
    static $pbm = null;

    $config = get_config('sharedresource');

    if (is_null($pbm) && $course->format == 'page') {
        $pbm = new page_enabled_block_manager($PAGE);
    }

    // Create moodle resource.

    $module = $DB->get_record('modules', array('name'=> 'sharedresource'));

    // Check for sharedresourceentry and add if not here.

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

            // Give some traces.
            print_string('builtentry', 'block_sharedresources', $shentry->identifier);

            // Dispatch metadata into entry.
            $mtdstandard = \mod_sharedresource\plugin_base::load_mtdstandard($config->schema);
            $mtdstandard->setEntry($sharedresourceentryid);
            $mtdstandard->setTextElementValue($mtdstandard->getDescriptionElement()->node, '', $shentry->description);
            $mtdstandard->setTextElementValue($mtdstandard->getTitleElement()->node, '', $shentry->title);
            if (!empty($shentry->keywords)) {
                $mtdstandard->setKeywords($shentry->keywords);
            }

            // Store now the draft file in sharedresource filearea.

            $systemcontext = context_system::instance();
            $fs = get_file_storage();
            $filerecord = new StdClass;
            $filerecord->contextid = $systemcontext->id;
            $filerecord->component = 'mod_sharedresource';
            $filerecord->filearea = 'sharedresource';
            $filerecord->itemid = $sharedresourceentryid;
            $filerecord->path = '/';
            $newfile = $fs->create_file_from_storedfile($filerecord, $file);

            // Remap sharedresource with new file record.
            $DB->set_field('sharedresource_entry', 'file', $newfile->get_id(), array('identifier' => $identifier));
        } catch (Exception $e) {
            mtrace('Error while saving file');
            print_object($e);
        }

    } else {
        if ($metadata->overwrite) {

            print_string('exists', 'block_sharedresources', $sharedentry);

            // Give some traces.

            $sharedentry->context = $metadata->context;
            $sharedentry->description = @$metadata->description;
            $sharedentry->keywords = @$metadata->keywords;
            $DB->update_record('sharedresource_entry', $sharedentry);

            // Dispatch metadata into entry.
            $mtdstandard = \mod_sharedresource\plugin_base::load_mtdstandard($config->schema);
            $mtdstandard->setEntry($sharedentry->id);
            $mtdstandard->setTextElementValue($mtdstandard->getDescriptionElement()->node, '', $sharedentry->description);
            $mtdstandard->setTextElementValue($mtdstandard->getTitleElement()->node, '', $sharedentry->title);
            if (!empty($shentry->keywords)) {
                $mtdstandard->setKeywords($shentry->keywords);
            }
        } else {
            // Give some traces.
            print_string('existsignorechanges', 'block_sharedresources', $sharedentry);
        }
    }

    if ($metadata->publish) {
        if ($course->id != SITEID) {
            // Complete a sharedresource record.
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

            // Finish with a pageitem if we are in a page format.

            if ($course->format == 'page') {
                // Add a page_item.
                include_once($CFG->dirroot.'/course/format/page/page.class.php');
                $page = course_page::get_current_page($course->id);
                $pageitem = new StdClass;
                $pageitem->pageid = $page->id;
                $pageitem->cmid = $cm->coursemodule;
                $pageitem->blockinstance = 0;
                $pageitem->position = 'c';
                $select = " pageid = ? AND position = 'c' ";
                $params = array($pageitem->pageid);
                $pageitem->sortorder = $DB->get_field_select('format_page_items', 'MAX(sortorder)', $select, $params) + 1;
                $pageitem->visible = 1;
                $pageitem->id = $DB->insert_record('format_page_items', $pageitem);

                if ($instance = $pbm->add_block_at_end_of_page_region('page_module', $page->id)) {
                    $pageitem = $DB->get_record('format_page_items', array('blockinstance' => $instance->id));
                    $DB->set_field('format_page_items', 'cmid', $cm->coursemodule, array('id' => $pageitem->id));
                    $DB->set_field('format_page_items', 'blockinstance', $instance->id, array('id' => $pageitem->id));
                }

                // Now add cminstance id to configuration.
                $block = block_instance('page_module', $instance);
                $block->config->cmid = $cm->coursemodule;
                $block->instance_config_save($block->config);

            }
            // Give some traces.
            print_string('constructed', 'block_sharedresources', $sharedresource->id);
        }
    }
}