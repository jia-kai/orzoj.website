<?php
/*
 * $File: problem_edit.php
 * $Date: Wed Nov 17 15:26:08 2010 +0800
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
 * This page should only be required_once by problem.php 
 */

/**
 * GET arguments:
 *		[edit]: int, the id of problem to be edited, or 0 for adding a new problem
 *		[do]:  indicate the submission of the form (ajax_mode)
 *			return:
 *				the first character will be 0 to refresh only page-info div,
 *				or 1 to refresh the whole page (new page address followed)
 *		[delete]: 
 *			delete this problem (already confirmed), id must be sent via $_POST['pid']
 *			and verification code must be sent via $_POST['delete_verify']
 *		[success_info]:
 *			if set, print success information at the beginning
 *
 * POST arguments:
 *		those in the form
 *		['delete_verify']: verification code for deleting a problem
 *		['edit_verify']: verification code for adding/editing a problem
 *
 * SESSION variables:
 *		delete_verify, edit_verify
 */

session_add_prefix('edit');

require_once $includes_path . 'contest/ctal.php';

if (isset($_GET['delete']) && !empty($_POST['pid']) &&
	!empty($_POST['delete_verify']) && $_POST['delete_verify'] == session_get('delete_verify'))
{
	session_set('delete_verify', NULL);
	prob_delete(intval($_POST['pid']));
}

$fields = array(
	// <column name> => <show function>
	// <column name> => array(<edit function>, <value retrieving function>)
	'id' => 'show_id',
	'title' => array('edit_title', 'get_title'),
	'code' => array('edit_code', 'get_code'),
	'desc' => array('edit_desc', 'get_desc'),
	'io' => array('edit_io', 'get_io'),
	'perm' => array('edit_perm', 'get_perm')
);

if (isset($_GET['do']) && !empty($_POST['edit_verify']) && $_POST['edit_verify'] == session_get('edit_verify'))
{
	try
	{
		$pinfo = array('time' => time());
		foreach ($fields as $f)
			if (is_array($f))
				$f[1]();
		if (empty($_GET['edit']))
		{
			if (prob_get_id_by_code($pinfo['code']))
				throw new Exc_runtime(__('problem code %s already exists', $pinfo['code']));
			$pid = $db->insert_into('problems', $pinfo);
			$pinfo['id'] = $pid;
		}
		else
		{
			$db->update_data('problems', $pinfo, array($DBOP['='], 'id', $pinfo['id'] = $_GET['edit']));
			$pid = $_GET['edit'];
		}
		get_grp();
		get_contest();
		session_set('edit_verify', NULL);
		echo "1index.php?page=$cur_page&edit=$pid&success_info=1";
		return;
	}
	catch (Exc_orzoj $e)
	{
		echo '0';
		get_info_div('error', __('Failed to add/updated problem: %s', htmlencode($e->msg())));
		return;
	}
}
if (!empty($_GET['edit']))
{
	$pinfo = $db->select_from('problems', array_keys($fields), array(
		$DBOP['='], 'id', $_GET['edit']));
	if (empty($pinfo))
		die('no such problem');
	else
		$pinfo = $pinfo[0];
	if (empty($pinfo['desc']))
		get_info_div('warning', __('This problem has been marked as being deleted'));
}

echo '<div id="ajax-page">';
if (isset($_GET['success_info']))
	get_info_div('info', __('Problem successfully added/updated (at %s)', time2str(time())));
echo '</div>';

echo "<form action='$cur_page_link&amp;do=1' method='post' id='edit-prob-form'>";
foreach ($fields as $f)
{
	if (is_array($f))
		$f = $f[0];
	$f();
}
edit_grp();
edit_contest();
$edit_verify = get_random_id();
session_set('edit_verify', $edit_verify);
form_get_hidden('edit_verify', $edit_verify);
echo '<input style="clear:both; float: left" type="submit" value="' . __('submit') . '" />';
echo '</form>';
if (isset($pinfo['id']))
{
	echo '<div style="clear: both; float: left; margin-top: 50px;">';
	echo "<form action='$cur_page_link&amp;delete=1' method='post' id='delete-prob-form'>";
	$delete_verify = get_random_id();
	form_get_hidden('pid', $pinfo['id']);
	form_get_hidden('delete_verify', $delete_verify);
	session_set('delete_verify', $delete_verify);
	echo '<button type="button" onclick="delete_prob()">' . __('Delete problem') . '</button>';
	echo '</form>';
	echo '</div>';
}

function show_id()
{
	global $pinfo;
	$id = get_array_val($pinfo, 'id');
	if (!empty($id))
		echo '<div class="form-field"><label>' . __('Problem id:') . '</label>' . $pinfo['id'] . '</div>';
}

function edit_code()
{
	global $pinfo;
	form_get_input(__('Problem code:'), 'code', get_array_val($pinfo, 'code'));
}

function get_code()
{
	$val = get_post('code');
	prob_validate_code($val);
	global $pinfo;
	$pinfo['code'] = $val;
}

function edit_title()
{
	global $pinfo;
	form_get_input(__('Problem title:'), 'title', get_array_val($pinfo, 'title'));
}

function get_title()
{
	global $pinfo;
	$pinfo['title'] = htmlencode(get_post('title'));
}

function edit_desc()
{
	global $pinfo, $root_path, $PROB_DESC_FIELDS_ALLOW_XHTML;
	if (!is_null($pinfo) && !empty($pinfo['desc']))
		$desc = unserialize($pinfo['desc']);
	else
		$desc = array();
	form_get_input(__('Time limit:'), 'desc[time]', get_array_val($desc, 'time', __('1 second')));
	form_get_input(__('Memory limit:'), 'desc[memory]', get_array_val($desc, 'memory', __('256 MB')));
	$fields = array(
		'desc' => __('Description'),
		'input_fmt' => __('Input Format'),
		'output_fmt' => __('Output Format'));
	foreach ($fields as $key => $val)
		form_get_ckeditor($val, "desc[$key]", htmlspecialchars(get_array_val($desc, $key, '')));

	$fields = array(
		'input_samp' => __('Sample Input'),
		'output_samp' => __('Sample Output'),
		'range' => __('Range'),
		'source' => __('Source'),
		'hint' => __('Hint')
	);

	$small = 0;
	foreach ($fields as $key => $text)
	{
		$small ++;
		$val = get_array_val($desc, $key);
		if (in_array($key, $PROB_DESC_FIELDS_ALLOW_XHTML))
			$val = htmlspecialchars($val);
		form_get_textarea($text, "desc[$key]", $val, $small > 2);
	}
}

function get_desc()
{
	global $PROB_DESC_FIELDS_ALLOW_XHTML;
	$desc_post = get_post('desc');
	if (!is_array($desc_post))
		throw new Exc_runtime('invalid desc type');
	$fields = array(
		'time' => __('time limit'),
		'memory' => __('memory limit'),
		'desc' => __('problem description'),
		'input_fmt' => __('input format'),
		'output_fmt' => __('output format'),
		'input_samp' => __('sample input'),
		'output_samp' => __('sample output'),
		'range' => __('range'),
		'source' => __('problem source'),
		'hint' => __('hint')
	);
	$fields_required = array(
		'time', 'memory', 'desc', 'input_fmt', 'output_fmt',
		'input_samp', 'output_samp', 'range');
	$desc = array();
	foreach ($fields as $f => $name)
	{
		if (!isset($desc_post[$f]))
			throw new Exc_runtime(__('incomplete post'));
		if (in_array($f, $fields_required) && !strlen($desc_post[$f]))
			throw new Exc_runtime(__('%s can not be empty', $name));
		$val = $desc_post[$f];
		if (in_array($f, $PROB_DESC_FIELDS_ALLOW_XHTML))
		{
			try
			{
				xhtml_validate($val);
			}
			catch (Exc_xhtml $e)
			{
				throw new Exc_runtime(__('XHTML validation error for field %s: %s', $name, $e->msg()));
			}
		}
		else
			$val = htmlspecialchars($val);
		$desc[$f] = $val;
	}
	global $pinfo;
	$pinfo['desc'] = serialize($desc);
}

function edit_io()
{
	global $pinfo;
	if (empty($pinfo['io']))
	{
		$io = array('', '');
		$checked = array('checked="checked"', '');
	}
	else
	{
		$io = unserialize($pinfo['io']);
		$checked = array('', 'checked="checked"');
	}
	echo '<div class="form-field">';
	echo '<label>' . __('Problem I/O:') . '</label>';
	echo '<div class="form-field-prob-io">';
	echo '<div>';
	echo '<span>' . __('I/O method:') . '</span>';

	$id = get_unique_id();
	echo "<input type='radio' onchange='$(\"#prob-io\").slideUp()' name='io_method' value='0' id='$id' $checked[0] />";
	echo "<label for='$id'>" . __('Standard I/O') . '</label>';
	$id = get_unique_id();
	echo "<input type='radio' onchange='$(\"#prob-io\").slideDown()' name='io_method' value='1' id='$id' $checked[1] />";
	echo "<label for='$id'>" . __('File I/O') . '</label>';

	echo '</div>';
	echo '<div id="prob-io">';
	form_get_input(__('Input filename:'), 'io0', $io[0], FALSE);
	echo '<br />';
	form_get_input(__('Output filename:'), 'io1', $io[1], FALSE);
	echo '</div></div></div>';
}

function get_io()
{
	global $pinfo;
	if (get_post('io_method') == '0')
		$pinfo['io'] = NULL;
	else
	{
		prob_validate_io($io0 = get_post('io0'));
		prob_validate_io($io1 = get_post('io1'));
		$pinfo['io'] = serialize(array($io0, $io1));
	}
}

function edit_perm()
{
	global $pinfo;
	echo '<div class="form-field">';
	echo '<label style="float: left">' . __('Set problem permission') .
		'</label><div style="float: left">';
	form_get_perm_editor('perm', get_array_val($pinfo, 'perm'));
	echo '</div></div>';
}

function get_perm()
{
	global $pinfo;
	$pinfo['perm'] = form_get_perm_editor_val('perm');
}

function edit_grp()
{
	global $pinfo, $db, $DBOP;
	if (isset($pinfo['id']))
	{
		$rows = $db->select_from('map_prob_grp', 'gid', array($DBOP['='], 'pid', $pinfo['id']));
		$default = array();
		foreach ($rows as $row)
			$default[] = $row['gid'];
	}
	else 
		$default = NULL;

	echo '<div class="form-field">';
	echo '<label>' . __('Problem groups:') . '</label>';
	form_get_gid_selector('prob_grps', 1, $default);
	echo '</div>';
}

function get_grp()
{
	global $pinfo, $db, $DBOP;
	$pid = $pinfo['id'];
	$old = array();
	$rows = $db->select_from('map_prob_grp', 'gid', array($DBOP['='], 'pid', $pid));
	foreach ($rows as $row)
		$old[intval($row['gid'])] = TRUE;
	$new = array();
	foreach(form_get_gid_selector_val('prob_grps') as $i)
		$new[intval($i)] = TRUE;

	$where = array($DBOP['&&'],
		$DBOP['='], 'gid', -1,
		$DBOP['='], 'pid', $pid);
	foreach ($old as $i => $nouse)
		if (!isset($new[$i]))
		{
			$where[3] =  $i;
			$db->delete_item('map_prob_grp', $where);
		}
	foreach ($new as $i => $nouse)
		if (!isset($old[$i]))
			$db->insert_into('map_prob_grp', array(
				'pid' => $pid, 'gid' => $i
			));
}

function edit_contest()
{
	$list = ctal_get_list(array('id', 'type', 'name'), 0);
	$list = array_merge($list, ctal_get_list(array('id', 'type', 'name'), 1));
	$opt = array(__('None') => 0);
	foreach ($list as $r)
		$opt[$r['id'] . ':' . ctal_get_typename_by_type($r['type']) .
			':' . $r['name']] = $r['id'];
	global $pinfo;
	$default = NULL;
	if (!is_null($pid = get_array_val($pinfo, 'id')))
		$default = prob_future_contest($pid);
	if (is_null($default))
		$default = '0';
	echo '<div class="form-field">';
	form_get_select(__('Related contest:'), 'ctid', $opt, $default);
	echo '</div>';
}

function get_contest()
{
	global $pinfo, $db, $DBOP;
	$cid = intval(get_post('ctid'));
	$cur_cid = intval(prob_future_contest($pid = $pinfo['id']));
	if ($cid == $cur_cid)
		return;
	$where = array($DBOP['&&'],
			$DBOP['='], 'cid', $cur_cid,
			$DBOP['='], 'pid', $pid);
	if (!$cid)
		$db->delete_item('map_prob_ct', $where);
	else if (!$cur_cid)
		$db->insert_into('map_prob_ct',
			array('cid' => $cid, 'pid' => $pid, 'order' => $pid));
	else
	{

		$db->update_data('map_prob_ct', array('cid' => $cid), $where);
	}
}

?>
<script type="text/javascript">
$(document).ready(function(){
	var f = $("#edit-prob-form");
	f.append('<input type="hidden" name="ajax_mode" value="1" />');
	f.bind('submit', function(){
		var f = $("#edit-prob-form");
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
	<?php if (empty($pinfo['io'])) echo '$("#prob-io").slideUp();' ?>
});

function delete_prob()
{
	if (confirm("<?php echo __('Are you sure to delete this problem?')?>"))
	{
		document.getElementById('delete-prob-form').submit();
		window.location="index.php?page=prob";
	}
}

</script>

