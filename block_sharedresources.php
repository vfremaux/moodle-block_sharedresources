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
 * @subpackage blocks
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

class block_sharedresources extends block_base {

    public function init() {
        $this->title = get_string('blockname', 'block_sharedresources');
    }

    public function has_config() {
        return false;
    }

    public function get_content() {
        global $COURSE;

        if($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        $context = context_course::instance($COURSE->id);
        if (!has_capability('moodle/course:manageactivities', $context)) {
            $this->content->text = '';
            $this->content->footer = '';
            return $this->content;
        }

        $convertallstr = get_string('convertall', 'block_sharedresources');
        $converturl = new moodle_url('/mod/sharedresource/admin_convertall.php', array('course' => $COURSE->id));
        $this->content->text = '<a href="'.$converturl.'">'.$convertallstr.'</a><br/><br/>';

        $convertbackstr = get_string('convertback', 'block_sharedresources');
        $converturl = new moodle_url('/mod/sharedresource/admin_convertback.php', array('course' => $COURSE->id));
        $this->content->text .= '<a href="'.$converturl.'" title="'.$convertbackstr.'">'.$convertbackstr.'</a><br/><br/>';

        $importstr = get_string('importfromfiles', 'block_sharedresources');
        $converturl = new moodle_url('/blocks/sharedresources/importresourcesfromfiles.php', array('course' => $COURSE->id));
        $this->content->text .= '<a href="'.$converturl.'" title="'.$importstr.'">'.$importstr.'</a><br/><br/>';

        $viewlibrarystr = get_string('viewlibrary', 'block_sharedresources');
        $libraryurl = new moodle_url('/local/sharedresources/index.php', array('course' => $COURSE->id));
        $this->content->text .= '<a href="'.$libraryurl.'">'.$viewlibrarystr.'</a>';

        $this->content->footer = '';

        return $this->content;
    }
}

