<?php

/**
 * This page lists all the instances of newmodule in a particular course
 *
 * @author  David Smith <moodle@davosmith.co.uk>
 * @package mod/checklist
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

global $DB;

$id = required_param('id', PARAM_INT);   // course

if (! $course = $DB->get_record('course', array('id' => $id) )) {
    error('Course ID is incorrect');
}

$PAGE->set_url('/mod/checklist/index.php',array('id'=>$course->id));
require_course_login($course);
$PAGE->set_pagelayout('incourse');

add_to_log($course->id, 'checklist', 'view all', "index.php?id=$course->id", '');

/// Get all required stringsnewmodule

$strchecklists = get_string('modulenameplural', 'checklist');
$strchecklist  = get_string('modulename', 'checklist');


/// Print the header

$PAGE->navbar->add($strchecklists);
$PAGE->set_title($strchecklists);
echo $OUTPUT->header();

/// Get all the appropriate data

if (! $checklists = get_all_instances_in_course('checklist', $course)) {
    notice('There are no instances of checklist', "../../course/view.php?id=$course->id");
    die;
}

/// Print the list of instances (your module will probably extend this)

$timenow  = time();
$strname  = get_string('name');
$strweek  = get_string('week');
$strtopic = get_string('topic');
$strprogress = get_string('progress','checklist');

$table = new html_table();

if ($course->format == 'weeks') {
    $table->head  = array ($strweek, $strname);
    $table->align = array ('center', 'left', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array ($strtopic, $strname);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strname);
    $table->align = array ('left', 'left');
}

$context = get_context_instance(CONTEXT_COURSE, $course->id);
$canupdateown = has_capability('mod/checklist:updateown', $context);
if ($canupdateown) {
    $table->head[] = $strprogress;
}

foreach ($checklists as $checklist) {
    if (!$checklist->visible) {
        //Show dimmed if the mod is hidden
        $link = '<a class="dimmed" href="view.php?id='.$checklist->coursemodule.'">'.format_string($checklist->name).'</a>';
    } else {
        //Show normal if the mod is visible
        $link = '<a href="view.php?id='.$checklist->coursemodule.'">'.format_string($checklist->name).'</a>';
    }


    if ($course->format == 'weeks' or $course->format == 'topics') {
        $row = array ($checklist->section, $link);
    } else {
        $row = array ($link);
    }

    if ($canupdateown) {
        $row[] = checklist_class::print_user_progressbar($checklist->id, $USER->id, '300px', true, true);
    }

    $table->data[] = $row;
}

echo $OUTPUT->heading($strchecklists);
echo html_writer::table($table);

/// Finish the page

echo $OUTPUT->footer();
