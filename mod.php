<?php

    if (!defined('MOODLE_INTERNAL')) {
        die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
    }

    if (has_capability('mod/attforblock:export', $context)) {
        echo '<p>';
        echo "<a href=\"{$CFG->wwwroot}/course/report/attprint/index.php?id={$course->id}\">";
        echo get_string('title', 'report_attprint')."</a>\n";
        echo '</p>';
    }
?>
