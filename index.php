<?php  // $Id: index.php,v 1.0 2009/02/14 argentum@cdp.tsure.ru Exp $

require_once('../../../config.php');

$id        = required_param('id', PARAM_INT);                 // course id.
$start     = optional_param('start', false, PARAM_BOOL);
$expr      = optional_param('expr', 0, PARAM_INT);

if (!$course = get_record('course', 'id', $id)) {
    print_error('invalidcourse');
}

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('mod/attforblock:export', $context);

$strattprint = get_string('title', 'report_attprint');
$strreports    = get_string('reports');

$langdir = $CFG->dirroot.'/course/report/attprint/lang/';
$pluginname = 'report_attprint';

require_once('HTML/AJAX/JSON.php');

require_js('yui_yahoo');
require_js('yui_dom');
require_js('yui_utilities');
require_js('yui_connection');
require_js($CFG->wwwroot.'/course/report/attprint/clientlib.js');

$courseid = required_param('id', PARAM_INT);
$groupid  = optional_param('group', false, PARAM_INT);
$userid   = optional_param('user', false, PARAM_INT);
$action   = groups_param_action();

$returnurl = $CFG->wwwroot.'/course/report.php?id='.$courseid;

switch ($action) {
    case false: //OK, display form.
        break;

    case 'ajax_getmembersingroup':
        $roles = array();
        if ($groupmemberroles = groups_get_members_by_role($groupid,$courseid,'u.id,u.firstname,u.lastname')) {
            foreach($groupmemberroles as $roleid=>$roledata) {
                $shortroledata=new StdClass;
                $shortroledata->name=$roledata->name;
                $shortroledata->users=array();
                foreach($roledata->users as $member) {
                    $shortmember=new StdClass;
                    $shortmember->id=$member->id;
                    $shortmember->name=fullname($member, true);
                    $shortroledata->users[]=$shortmember;
                }
                $roles[]=$shortroledata;
            }
        }
        echo json_encode($roles);
        die;  // Client side JavaScript takes it from here.

    default: //ERROR.
        if (debugging()) {
            error('Error, unknown button/action. Probably a user-interface bug!', $returnurl);
        break;
    }
}

$navlinks = array(array('name'=>$strreports, 'link'=>$CFG->wwwroot.'/course/report.php?id='.$courseid, 'type'=>'misc'),
                  array('name'=>$strattprint, 'link'=>'', 'type'=>'misc'));
$navigation = build_navigation($navlinks);

/// Print header
print_header_simple($strattprint, ': ', $navigation, '', '', true, '', navmenu($course));

print_heading(format_string($course->shortname) .': '.$strattprint, 'center', 3);
echo '<form id="groupselectform" action="pdf.php" method="get">'."\n";
echo '<div>'."\n";
echo '<input type="hidden" name="id" value="' . $courseid . '" />'."\n";

echo '<table cellpadding="6" class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">'."\n";
echo '<tr>'."\n";


echo "<td>\n";
// NO GROUPINGS YET!
echo '<p><label for="groups"><span id="groupslabel">'.get_string('groups').':</span><span id="thegrouping">&nbsp;</span></label></p>'."\n";

if (ajaxenabled()) {
    $onchange = 'membersCombo.refreshMembers();';
} else {
    $onchange = '';
}


echo '<select name="group" id="groups" size="15" class="select" onchange="'.$onchange.'"'."\n";
echo ' onclick="window.status=this.selectedIndex==-1 ? \'\' : this.options[this.selectedIndex].title;" onmouseout="window.status=\'\';">'."\n";

$groups = groups_get_all_groups($courseid);
$selectedname = '&nbsp;';

if ($groups) {
    // Print out the HTML
    foreach ($groups as $group) {
        $select = '';
        $usercount = (int)count_records('groups_members', 'groupid', $group->id);
        $groupname = format_string($group->name).' ('.$usercount.')';
        if ($group->id == $groupid) {
            $select = ' selected="selected"';
            $selectedname = $groupname;
        }

        echo "<option value=\"{$group->id}\"$select title=\"$groupname\">$groupname</option>\n";
    }
} else {
    // Print an empty option to avoid the XHTML error of having an empty select element
    echo '<option>&nbsp;</option>';
}

echo '</select>'."\n";
echo '</td>'."\n";
echo '<td>'."\n";

echo '<p><label for="members"><span id="memberslabel">'.
    get_string('membersofselectedgroup', 'group').
    ' </span><span id="thegroup">'.$selectedname.'</span></label></p>'."\n";
//NOTE: the SELECT was, multiple="multiple" name="user[]" - not used and breaks onclick.
echo '<select name="user" id="members" size="15" class="select"'."\n";
echo ' onclick="window.status=this.options[this.selectedIndex].title;" onmouseout="window.status=\'\';">'."\n";

$member_names = array();

$atleastonemember = false;
if ($groupmemberroles = groups_get_members_by_role($groupid,$courseid,'u.id,u.firstname,u.lastname')) {
    foreach($groupmemberroles as $roleid=>$roledata) {
        echo '<optgroup label="'.htmlspecialchars($roledata->name).'">';
        foreach($roledata->users as $member) {
            echo '<option value="'.$member->id.'">'.fullname($member, true).'</option>';
            $atleastonemember = true;
        }
        echo '</optgroup>';
    }
}

if (!$atleastonemember) {
    // Print an empty option to avoid the XHTML error of having an empty select element
    echo '<option>&nbsp;</option>';
}

echo '</select>'."\n";

echo '</td>'."\n";
echo '</tr>'."\n";
echo '</table>'."\n";

echo '<div align="center">';
echo '<input type="submit" value="' . get_string('downloadreport', 'report_attprint') . '" />'."\n";
echo '</div>'."\n";

echo '</div>'."\n";
echo '</form>'."\n";

if (ajaxenabled()) {
    echo '<script type="text/javascript">'."\n";
    echo '//<![CDATA['."\n";
    echo 'var membersCombo = new UpdatableMembersCombo("'.$CFG->wwwroot.'", '.$course->id.');'."\n";
    echo '//]]>'."\n";
    echo '</script>'."\n";
}

print_footer($course);


/**
 * Returns the first button action with the given prefix, taken from
 * POST or GET, otherwise returns false.
 * See /lib/moodlelib.php function optional_param.
 * @param $prefix 'act_' as in 'action'.
 * @return string The action without the prefix, or false if no action found.
 */
function groups_param_action($prefix = 'act_') {
    $action = false;

    if ($_POST) {
        $form_vars = $_POST;
    }
    elseif ($_GET) {
        $form_vars = $_GET;
    }
    if ($form_vars) {
        foreach ($form_vars as $key => $value) {
            if (preg_match("/$prefix(.+)/", $key, $matches)) {
                $action = $matches[1];
                break;
            }
        }
    }
    if ($action && !preg_match('/^\w+$/', $action)) {
        $action = false;
        error('Action had wrong type.');
    }
    return $action;
}

?>
