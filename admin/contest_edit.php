<?php
/*
 * $File: contest_edit.php
 * $Date: Sat Jan 07 16:13:48 2012 +0800
 */
/**
 * @package orzoj-website
 * @license http://gnu.org/licenses GNU GPLv3
 */
/*
	This file is part of orzoj

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('IN_ORZOJ'))
	exit;

/*
 * This page should only be required_once by contest.php 
 */

/**
 * GET arguments:
 *		[edit]:int the id of contest to be edited, or 0 for adding a new contest
 *		[delete]: (ajax_mode)
 *			delete this contest (already confirmed), id must be sent via $_POST['cid']
 *			and verification code must be sent via $_POST['delete_verify']
 *			the contest must not have started
 *			return 0 for success, 1 for error with error message followed
 *		[do]: indicate the submission of the form (ajax_mode)
 *			return:
 *				the first character will be 0 to refresh only page-info div,
 *				or 1 to refresh the whole page (new page address followed)
 *		[success_info]:
 *			if set, print success information at the beginning
 * POST arguments:
 *		those in the form
 *		['delete_verify']: verification code for deleting a contest
 *		['edit_verify']: verification code for adding/editing a contest
 *		[type]:int the contest type for adding a new contest (valid when edit=0)
 * SESSION variables:
 *		delete_verify, edit_verify
 */
session_add_prefix('edit');

require_once $includes_path . 'problem.php';

if (empty($_GET['edit']))
{
	if (!isset($_POST['type']))
	{
		echo "<form action='$cur_page_link' method='post'>";
		form_get_select(__('Contest type:'), 'type', array_flip(ctal_get_typename_all()));
		echo '<br /><input type="submit" value="' . __('Next step') . '" />';
		echo '</form>';
		return;
	}
	if (!isset($CONTEST_TYPE2CLASS[$_POST['type']]))
		die('no such contest type');
	$type = $CONTEST_TYPE2CLASS[$_POST['type']];
	require_once $includes_path . "contest/$type.php";
	$type = "Ctal_$type";
	$ct = new $type(array('type' => $_POST['type']));
}
else
{
	try
	{
		$ct = ctal_get_class_by_cid($_GET['edit']);
		if ($ct->data['time_end'] <= time())
			throw new Exc_runtime(__('Sorry, you can not edit a contest that has ended at %s',
				time2str($ct->data['time_end'])));
	} catch (Exc_orzoj $e)
	{
		die(htmlencode($e->msg()));
	}
}

if (isset($_GET['delete']) && !empty($_POST['delete_verify']) &&
	$_POST['delete_verify'] == session_get('delete_verify') && isset($_POST['cid']))
{
	try
	{
		$ct = ctal_get_class_by_cid($_POST['cid']);
		$ct->delete_contest();
		session_set('delete_verify', NULL);
		echo '0';
		return;
	}
	catch (Exc_orzoj $e)
	{
		echo '1';
		echo __('Failed to delete contest: %s', htmlencode($e->msg()));
		return;
	}
}

$fields = array(
	// <show function>
	// array(<edit function>, <value retrieving function>)
	'show_id',
	'show_type',
	array('edit_name', 'get_name'),
	array('edit_desc', 'get_desc'),
	array('edit_time_start', 'get_time_start'),
	array('edit_time_end', 'get_time_end'),
	array('edit_perm', 'get_perm'),
	array('edit_prob', 'get_prob')
);
$add_ct_after_got_id_func = array('get_prob');

if (isset($_GET['do']) && !empty($_POST['edit_verify']) && $_POST['edit_verify'] == session_get('edit_verify'))
{
	try
	{
		$db->transaction_begin();
		foreach ($fields as $f)
			if (is_array($f))
				$f[1]();
		if (empty($_GET['edit']))
		{
			$cid = $ct->add_contest();
			$_GET['edit'] = $cid;
			foreach ($add_ct_after_got_id_func as $f)
				$f();
			$cur_page_link = "index.php?page=$cur_page&amp;edit=$cid";
		}
		else
		{
			$ct->update_contest();
			$cid = $ct->data['id'];
		}
		session_set('edit_verify', NULL);
		echo "1index.php?page=$cur_page&edit=$cid&success_info=1";
		$db->transaction_commit();
		return;
	}
	catch(Exc_orzoj $e)
	{
		$db->transaction_rollback();
		echo '0';
		get_info_div('error', __('Failed to add/update contest: %s', htmlencode($e->msg())));
		return;
	}
}
echo '<div id="ajax-page">';
if (isset($_GET['success_info']))
	get_info_div('info', __('Contest successfully added/updated (at %s )', time2str(time())));
echo '</div>';

echo "<form action='$cur_page_link&amp;do=1' method='post' id='edit-ct-form'>";
foreach ($fields as $f)
{
	if (is_array($f))
		$f = $f[0];
	$f();
}
$ct->get_form_fields();
$edit_verify = get_random_id();
session_set('edit_verify', $edit_verify);
form_get_hidden('edit_verify', $edit_verify);
form_get_hidden('type', $ct->data['type']);
echo '<input style="clear:both; float: left" type="submit" value="' . __('submit') . '" />';
echo '</form>';

if (get_array_val($ct->data, 'time_start') > time())
{
	echo '<div style="clear: both; float: left; margin-top: 50px;">';
	echo "<form action='$cur_page_link&amp;delete=1' method='post' id='delete-ct-form'>";
	$delete_verify = get_random_id();
	session_set('delete_verify', $delete_verify);
	form_get_hidden('delete_verify', $delete_verify);
	form_get_hidden('cid', $ct->data['id']);
	form_get_hidden('ajax_mode', 1);
	echo '<button type="button" onclick="delete_contest()">' . __('Delete this contest') . '</button>';
	echo '</form>';
	echo '</div>';
}

function show_id()
{
	global $ct;
	if ($id = get_array_val($ct->data, 'id'))
		echo '<div class="form-field"><label>' . __('Contest id:') . "</label>$id</div>";
}

function show_type()
{
	global $ct;
	echo '<div class="form-field"><label>' . __('Contest type:') . '</label>' .
		ctal_get_typename_by_type($ct->data['type']) . '</div>';
}

function edit_name()
{
	global $ct;
	form_get_input(__('Contest name:'), 'name', get_array_val($ct->data, 'name'));
}

function get_name()
{
	$name = get_post('name');
	if (empty($name))
		throw new Exc_runtime(__('please tell me the contest name'));
	global $ct;
	$ct->data['name'] = htmlencode($name);
}

function edit_desc()
{
	global $ct;
	form_get_ckeditor(__('Contest description:'), 'desc', get_array_val($ct->data, 'desc'));
}

function get_desc()
{
	xhtml_validate($desc = get_post('desc'));
	global $ct;
	$ct->data['desc'] = $desc;

}

function edit_time_start()
{
	global $ct;
	$default = get_array_val($ct->data, 'time_start');
	if (!empty($default))
		$default = time2str($default, FALSE);
	form_get_input(__('Start time:'), 'time_start', $default);
}

function get_time_start()
{
	$time = strtotime(get_post('time_start'));
	if ($time === FALSE)
		throw new Exc_runtime(__('failed to convert start time. Is the format correct?'));
	global $ct;
	$orig_time_start = get_array_val($ct->data, 'time_start');
	if ($orig_time_start && $orig_time_start < time() && $time != $orig_time_start)
		throw new Exc_runtime(__('can not change the start time of a contest that has aleady started'));
	$ct->data['time_start'] = $time;
}

function edit_time_end()
{
	global $ct;
	$default = get_array_val($ct->data, 'time_end');
	if (!empty($default))
		$default = time2str($default, FALSE);
	form_get_input(__('End time:'), 'time_end', $default);
	echo '<div class="form-help-msg" style="position: relative">';
	$url = 'http://www.php.net/manual/en/datetime.formats.php';
	echo __('Please refer to %s for time formats',
		"<a href='$url' target='_blank'>$url</a>") . '<br />';
	echo __('Examples:') . '<br />';
	$example = array('now', '10 September 2000',
		'+1 day', '+1 week', '+1 week 2 days 4 hours 2 seconds',
		'next Thursday', 'last Monday', '2012-12-21 23:00:00 +0800');
	echo '<ul>';
	foreach ($example as $eg)
	{
		echo '<li>';
		echo "$eg<span style='position:absolute; left: 350px'>=&gt;&nbsp;&nbsp;";
		echo time2str(strtotime($eg));
		echo '</span></li>';
	}
	echo '</ul>';
	echo '</div>';
}

function get_time_end()
{
	$time = strtotime(get_post('time_end'));
	if ($time === FALSE)
		throw new Exc_runtime(__('failed to convert end time. Is the format correct?'));
	if ($time < time())
		throw new Exc_runtime(__('can not set the end time to the past'));
	global $ct;
	$orig_time_start = get_array_val($ct->data, 'time_start');
	if ($orig_time_start && $time <= $orig_time_start)
		throw new Exc_runtime(__('this contest ends even before it starts'));
	$ct->data['time_end'] = $time;
}

function edit_perm()
{
	global $ct;
	echo '<div class="form-field">';
	echo '<label style="float: left">' . __('Set contest permission') .
		'</label><div style="float: left">';
	form_get_perm_editor('perm', get_array_val($ct->data, 'perm'));
	echo '</div></div>';
}

function get_perm()
{
	global $ct;
	$ct->data['perm'] = form_get_perm_editor_val('perm');
}

function edit_prob()
{
	global $ct, $db, $DBOP;
	$cid = get_array_val($ct->data, 'id');
	$prob_code = array();
	if ($cid)
	{
		$db_rows = $db->select_from('map_prob_ct',
			'pid', array($DBOP['='], 'cid', $cid), array('order' => 'ASC'));
		foreach ($db_rows as &$row)
			array_push($prob_code, prob_get_code_by_id($row['pid']));
	}

	form_get_input(__('Problems(use problem code, separated by comma)'), 'prob_code',
		implode(',', $prob_code), TRUE, 'input-prob-code');
}

function get_prob()
{
	global $ct, $db, $DBOP;
	$cid = get_array_val($ct->data, 'id');
	if (!$cid)
		return;
	$prob_id = array();
	foreach (explode(',', $_POST['prob_code']) as $pcode)
	{
		$pcode = trim($pcode);
		if (!strlen($pcode))
			continue;
		$id = prob_get_id_by_code($pcode);
		if (is_null($id))
			throw new Exc_runtime(__('unknown problem code: %s', $pcode));
		$other_ct = prob_future_contest($id);
		if (!is_null($other_ct) && $other_ct != $cid)
			throw new Exc_runtime(__('problem %s already belongs to upcoming contest %d',
				$pcode, $other_ct));
		array_push($prob_id, $id);
	}
	if (empty($prob_id))
		throw new Exc_runtime(__('no problem in this contest'));
	$db->delete_item('map_prob_ct', array($DBOP['='], 'cid', $cid));
	for ($i = 0; $i < count($prob_id); $i ++)
		$db->insert_into('map_prob_ct', array('pid' => $prob_id[$i],
			'cid' => $cid, 'order' => $i));
}

?>

<script type="text/javascript">
$(document).ready(function(){
	var f = $("#edit-ct-form");
	f.append('<input type="hidden" name="ajax_mode" value="1" />');
	f.bind('submit', function(){
		var f = $("#edit-ct-form");
		for (instance in CKEDITOR.instances)
			CKEDITOR.instances[instance].updateElement();
		$.ajax({
			'type': 'post',
			'cache': false,
			'url': f.attr('action'),
			'data': f.serializeArray(),
			'success': function(data){
				if (data.charAt(0) == '0')
				{
					$('#ajax-page').html(data.substr(1));
					window.scrollTo(0, 0);
				}
				else
					window.location = data.substr(1);
			}
		});
		return false;
	});
});

function delete_contest()
{
	if (confirm("<?php echo __('Are you sure to delete this contest?')?>"))
	{
		f = $("#delete-ct-form");
		$.ajax({
			'type': 'post',
			'cache': false,
			'url': f.attr('action'),
			'data': f.serializeArray(),
			'success': function(data){
				if (data.charAt(0) == '0')
					window.location="index.php?page=contest";
				else
					alert(data.substr(1));
			}
		});
	}
}

$('#input-prob-code').autocomplete("contest_edit_complete_prob.php", {
	multiple: true,
	multipleSeparator: ",",
	formatItem: function(row) {
		return row[0] + " (<?php echo __('title:')?>" + row[1] + ")";
	},
	formatResult: function (row) {
		return row[0];
	}
});

</script>


