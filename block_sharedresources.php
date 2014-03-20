<?php //$Id: block_sharedresources.php,v 1.1 2013-02-13 21:58:19 wa Exp $

class block_sharedresources extends block_base {
    function init() {
        $this->title = get_string('blockname', 'block_sharedresources');
        $this->version = 2010043000;
    }
    
    function has_config() {
        return false;
    }

    function get_content(){
        global $CFG, $COURSE;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        
        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        if (!has_capability('moodle/course:manageactivities', $context)){
            $this->content->text = '';
            $this->content->footer = '';
            return $this->content;
        }

        
        $convertallstr = get_string('convertall', 'block_sharedresources');
        $this->content->text = "<a href=\"{$CFG->wwwroot}/mod/sharedresource/admin_convertall.php?course={$COURSE->id}\">$convertallstr</a><br/><br/>";

        $convertbackstr = get_string('convertback', 'block_sharedresources');
        $this->content->text .= "<a href=\"{$CFG->wwwroot}/mod/sharedresource/admin_convertback.php?course=$COURSE->id\" title=\"{$convertbackstr}\">$convertbackstr</a><br/><br/>";

        $importstr = get_string('importfromfiles', 'block_sharedresources');
        $this->content->text .= "<a href=\"{$CFG->wwwroot}/blocks/sharedresources/importresourcesfromfiles.php?course=$COURSE->id\" title=\"{$importstr}\">$importstr</a><br/><br/>";
		
		$viewlibrarystr = get_string('viewlibrary', 'block_sharedresources');
        $this->content->text .= "<a href=\"{$CFG->wwwroot}/local/sharedresources/index.php?course={$COURSE->id}\">$viewlibrarystr</a>";

        $this->content->footer = '';

        return $this->content;
    }
}

?>
