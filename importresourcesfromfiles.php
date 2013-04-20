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
 * This page provides facility to construct massively resources from 
 * files stored within the course file directory. It is provided as
 * turnaround of the importresourcesfromfiles action in the page format
 * for course format that do not support pages.
 *
 */
    
    include '../../config.php';
    include $CFG->dirroot.'/mod/sharedresource/locallib.php';

    global $CFG;
    
    $courseid = required_param('course', PARAM_INT);
    $path = optional_param('path', '', PARAM_TEXT);
    $collecttitles = optional_param('collecttitles', null, PARAM_TEXT); // result of title collection form
    $asshared = optional_param('asshared', false, PARAM_BOOL);
    
    if (!$course = get_record('course', 'id', $courseid)){
        error("Bad course ID");
    }

/// Security
    
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
    
    require_capability('moodle/course:manageactivities', $context);
    require_js($CFG->wwwroot.'/blocks/sharedresources/js/js.js');

/// Print header

    $navlinks = array(array('name' => get_string('import', 'block_sharedresources'), 'url' => '' , 'type' => 'title'));

    print_header_simple(get_string('importresourcesfromfiles', 'block_sharedresources'), get_string('importresourcesfromfiles', 'block_sharedresources'), build_navigation($navlinks));
    
    print_heading(get_string('importresourcesfromfilestitle', 'block_sharedresources'));

/// Print header

    if (!empty($path)){
        if (empty($collecttitles)){
            $basepath = $CFG->dataroot.'/'.$course->id.$path;
            $DIR = opendir($basepath);
            
            $filenamestr = get_string('filename', 'block_sharedresources');
            $resourcenamestr = get_string('resourcename', 'block_sharedresources');
            $descriptionstr = get_string('description');
            $keywordsstr = get_string('keywords', 'block_sharedresources');
            $checkallstr = get_string('checkall', 'block_sharedresources');
            $uncheckallstr = get_string('uncheckall', 'block_sharedresources');
            
            $controls = "<a href=\"javascript:importcheckall()\" class=\smalltext\">$checkallstr</a> / <a href=\"javascript:importuncheckall()\" class=\smalltext\">$uncheckallstr</a>";

            echo "<center>";
            echo "<div style=\"width:90%; minheight:250px\">";
            echo "<form name=\"importfilesasresources\" action=\"{$CFG->wwwroot}/blocks/sharedresources/importresourcesfromfiles.php\" method=\"POST\">";
            echo "<input type=\"hidden\" name=\"course\" value=\"{$course->id}\" />";
            echo "<input type=\"hidden\" name=\"action\" value=\"importresourcesfromfiles\" />";
            echo "<input type=\"hidden\" name=\"path\" value=\"{$path}\" />";
            echo "<fieldset>";
            echo "<table width=\"100%\">";
            echo "<tr><td align=\"center\" width=\"30%\"><b>$filenamestr</b><br/>$controls</td>";
            echo "<td align=\"center\" width=\"70%\"><b>$resourcenamestr</b></td></tr>";
            echo "</table>";
            echo "</fieldset>";
            
            $i = 0;

            while($entry = readdir($DIR)){
                if (is_dir($basepath.'/'.$entry)) continue;
                if (preg_match('/^\./', $entry)) continue;

                print_box_start('commonbox');

                echo "<fieldset>";
                echo "<table width=\"100%\">";
                echo "<tr><td width=\"30%\"><input type=\"checkbox\" name=\"file{$i}\" value=\"{$path}/{$entry}\" checked=\"checked\" /> $entry</td>";
                echo "<td width=\"70%\"><input type=\"text\" name=\"resource{$i}\" size=\"50\" /></td></tr>";
                echo "<tr><td><b>$descriptionstr</b></td>";
                echo "<td><textarea name=\"description{$i}\" cols=\"50\" rows=\"4\"></textarea></td></tr>";
                echo "<tr><td><b>$keywordsstr</b></td>";
                echo "<td><input type=\"text\" name=\"keywords{$i}\"  size=\"50\" /></td></tr>";
                echo "</table>";
                echo "</fieldset>";

                print_box_end();
                $i++;
            }

            echo '<p>';
            print_string('importasshared', 'block_sharedresources');
            echo '<input type="checkbox" name="asshared" value="1" /> ';
            echo "<p><input type=\"submit\" name=\"collecttitles\" value=\"".get_string('submit').'" /> ';
            echo "<input type=\"button\" name=\"cancel_btn\" value=\"".get_string('cancel')."\" onclick=\"window.location.href = '{$CFG->wwwroot}/course/view.php?id={$courseid}'; \" /></p>";
            echo "</form>";
            echo "</div>";
            echo "</center>";

        } else {
            // everything collected. We can perform.
            // 1. create a Moodle resource that uses the file
            // 2. create a Moodle course_module that attaches the resource to the course 
            // 3. create a page format page_item that puts the resource in the page
            
            print_box_start('commonbox');

            echo '<br/>';
            echo '<center>';
            
            $fileparms = preg_grep('/^file/', array_keys($_POST));
            if (!empty($fileparms)){
                foreach($fileparms as $fileparm){
                    preg_match('/file(\d+)/', $fileparm, $matches);
                    $idnum = $matches[1];
                    $filepath = required_param('file'.$idnum, PARAM_TEXT);
                    $resourcename = required_param('resource'.$idnum, PARAM_TEXT);
                    $description = addslashes(required_param('description'.$idnum, PARAM_CLEANHTML));
                    
                    // use file name as default
                    
                    if (empty($resourcename)) $resourcename = basename($filepath);
                    
                    // create moodle resource
    
                    $module = get_record('modules', 'name', 'resource');
                
                    /// first get the course module the sharedresource is attached to
                
                    /// complete a resource record
                    $resource->course = $course->id;
                    $resource->type = 'file';
                    $resource->name = $resourcename;
                    $resource->summary = $description;
                    $resource->reference = preg_replace('/^\//', '', $filepath); // discard first occasional /
                    $resource->alltext = '';
                    $resource->popup = 0;
                    $resource->options = '';
                    $resource->timemodified = time();
                    
                    $resource->id = insert_record('resource', $resource);
    
                    $cm->course = $course->id;
                    $cm->module = $module->id;
                    $cm->instance = $resource->id;
                    $cm->section = 1;
                    $cm->visible = 1;
                    
                    $cm->coursemodule = add_course_module($cm);
                    add_mod_to_section($cm);
                    
                    // finish with a pageitem if we are in a page format
                    
                    if ($course->format == 'page'){
                        $page = page_get_current_page($course->id);
                        $pageitem->pageid = $page->id;
                        $pageitem->cmid = $cm->coursemodule;
                        $pageitem->blockinstance = 0;
                        $pageitem->position = 'c';
                        $pageitem->sortorder = get_field_select('format_page_items', 'MAX(sortorder)', " pageid = $pageitem->pageid AND position = 'c' ") + 1;
                        $pageitem->visible = 1;
        
                        insert_record('format_page_items', $pageitem);
                    }
                            
                    /// give some traces
                    print_string('constructed', 'block_sharedresources', $resource->id);
                    
                    if ($asshared){
                    	// restore unslashed content as conversion slashes
                    	$resource->description = stripslashes(@$resource->description);
                    	$resource->name = stripslashes($resource->name);
                        sharedresource_convertto($resource);
                    }
                }
            }
            echo '</center>';
            
            print_box_end();

            if ($course->format == 'page'){
                print_continue($CFG->wwwroot."/course/view.php?id={$courseid}&amp;action=activities");
            } else {
                print_continue($CFG->wwwroot."/course/view.php?id={$courseid}");
            }
        }
    } else {
        if (empty($path)){
            // get available dirs in course directory and provide a list
            
            // a recursive fucntion to scan dirs
            $paths = array();
            $paths['/'] = '/';

            function importresources_get_paths_rec(&$course, $path, &$paths){
                global $CFG;
                
                $basepath = $CFG->dataroot.'/'.$course->id.$path;

                if ($DIR = opendir(preg_replace('/\/$/', '', $basepath))){
                    while ($entry = readdir($DIR)){
                        if (preg_match('/^\./', $entry)) continue;
                        if (is_dir($basepath.$entry)){
                            $paths[$path.$entry] = $path.$entry;
                            importresources_get_paths_rec($course, $path.$entry.'/', $paths);
                        }
                    }                
                }
            }
            
            importresources_get_paths_rec($course, '/', $paths);

            print_box_start('commonbox');

            echo "<center>";
            echo "<div style=\"width:90%; height:250px\">";
            echo "<form name=\"importfilesasresources\" action=\"{$CFG->wwwroot}/blocks/sharedresources/importresourcesfromfiles.php\" method=\"POST\">";
            echo "<input type=\"hidden\" name=\"course\" value=\"{$course->id}\" />";
            echo "<input type=\"hidden\" name=\"action\" value=\"importresourcesfromfiles\" />";
            print_string('choosepathtoimport', 'block_sharedresources');
            choose_from_menu($paths, 'path');
            $cancelstr = get_string('cancel');
            echo "<p><input type=\"submit\" name=\"go_btn\" value=\"".get_string('submit').'" />';
            echo " <input type=\"button\" name=\"cancel_btn\" value=\"$cancelstr\"  onclick=\"window.location.href = '{$CFG->wwwroot}/course/view.php?id={$courseid}'; \" onclick=\"window.location.href = '{$CFG->wwwroot}/course/view.php?id={$courseid}'; \" /></p>";
            echo "</form>";
            echo "</div>";
            echo "</center>";

            print_box_end();
        }
    }
    
    print_footer($course);

?>