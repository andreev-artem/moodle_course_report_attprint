<?php

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/attforblock/locallib.php');
require_once($CFG->libdir.'/pdflib.php');


$courseid       = required_param('id', PARAM_INT);
$groupid        = optional_param('group', 0, PARAM_INT);

if (! $course = get_record("course", "id", $courseid)) {
    error("Course ID is incorrect");
}

require_course_login($course);

add_to_log($course->id, "report print attendance", "print");

$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('mod/attforblock:export', $context);

$users = groups_get_members($groupid);

require './PHPRtfLite/PHPRtfLite.php';
PHPRtfLite::registerAutoloader();

$rtf = new PHPRtfLite();
$rtf->setMargins(1, 1, 1, 1);
$times12 = new PHPRtfLite_Font(12, 'Times new Roman');
$border = PHPRtfLite_Border::create(1, '#000000');
$par = new PHPRtfLite_ParFormat();
$par->setIndentLeft(0.1);
$par->setIndentLeft(0.1);
$sect = $rtf->addSection();

$statuses = get_statuses($course->id);

foreach ($users as $user)
{
    $sect->writeText('<b>'.$user->firstname.' '.$user->lastname.'</b><br />', $times12);

    $where = "ats.courseid={$course->id} AND al.studentid = {$user->id}";
    $stqry = "SELECT ats.id,ats.sessdate,ats.description,al.statusid,al.remarks
                FROM {$CFG->prefix}attendance_log al
                JOIN {$CFG->prefix}attendance_sessions ats
                  ON al.sessionid = ats.id";
    $stqry .= " WHERE " . $where;
    $stqry .= " ORDER BY ats.sessdate asc";

    $i = 1;
    if ($sessions = get_records_sql($stqry)) {
        $table = $sect->addTable();
        $table->addColumnsList(array(0.7, 3, 1.5, 10.5, 3.3));
        $table->addRow();
        $table->getCell(1, 1)->writeText('#', $times12, $par);
        $table->getCell(1, 2)->writeText(get_string('date'), $times12, $par);
        $table->getCell(1, 3)->writeText(get_string('time'), $times12, $par);
        $table->getCell(1, 4)->writeText(get_string('description','attforblock'), $times12, $par);
        $table->getCell(1, 5)->writeText(get_string('status','attforblock'), $times12, $par);

        foreach($sessions as $key=>$sessdata)
        {
            $i++;
            $table->addRow();
            $table->getCell($i, 1)->writeText($i-1, $times12, $par);
            $table->getCell($i, 2)->writeText(ltrim(str_replace('&nbsp;', ' ', userdate($sessdata->sessdate, get_string('strftimedmyw', 'attforblock')))), $times12, $par);
            $table->getCell($i, 3)->writeText(userdate($sessdata->sessdate, get_string('strftimehm', 'attforblock')), $times12, $par);
            $table->getCell($i, 4)->writeText(empty($sessdata->description) ? get_string('nodescription', 'attforblock') : $sessdata->description, $times12, $par);
            $table->getCell($i, 5)->writeText($statuses[$sessdata->statusid]->description, $times12, $par);
        }
    }

    $table->setBorderForCellRange($border, 1, 1, $i>0 ? $i : null, 5);
    $sect = $rtf->addSection();
}
$rtf->sendRtf('att-report.rtf');

?>
