<?php
/*
 * $File: problem_edit.php
 * $Date: Mon Nov 01 21:52:14 2010 +0800
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
 *		[do]:  indicate the submission of the form
 *		[delete]: 
 *			delete this problem (already confirmed), id must be sent via $_POST['pid']
 *			and verification code must be sent via $_POST['delete_verify']
 * POST arguments:
 *		those in the form
 *		['delete_verify']: verification code for deleting a problem
 *		['edit_verify']: verification code for adding/editing a problem
 *
 * SESSION prefix:
 *		prob_edit
 * 
 * SESSION variables:
 *		delete_verify, edit_verify
 */

session_add_prefix('prob_edit');

if (isset($_GET['delete']) && isset($_POST['pid']) &&
	!empty($_POST['delete_verify']) && $_POST['delete_verify'] == session_get('delete_verify'))
{
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
		$pinfo = array();
		foreach ($fields as $f)
			if (is_array($f))
				$f[1]();
		if (empty($_GET['edit']))
		{
			$pid = $db->insert_into('problems', $pinfo);
			$_GET['edit'] = $pid;
			$cur_page_link  = "index.php?page=$cur_page&amp;edit=$pid";
		}
		else
			if (!$db->update_data('problems', $pinfo, array($DBOP['='], 'id', $_GET['edit'])))
				throw new Exc_runtime(__('no such problem #%d', $_GET['edit']));
		echo '<div class="notice">' . __('Problem successfully added/updated') . '</div>';
	}
	catch (Exc_orzoj $e)
	{
		echo '<div class="error">' . __('Failed to add/updated problem: %s', htmlencode($e->msg())) . '</div>';
	}
}
else
{
	$pinfo = NULL;
	if (!empty($_GET['edit']))
	{
		$pinfo = $db->select_from('problems', array_keys($fields), array(
			$DBOP['='], 'id', $_GET['edit']));
		if (empty($pinfo))
			die('no such problem');
		else
			$pinfo = $pinfo[0];
		if (empty($pinfo['desc']))
			echo '<div class="warning">' . __('This problem has been marked as being deleted') . '</div>';
	}
}

echo "<form action='$cur_page_link&amp;do=1' method='post'>";
foreach ($fields as $f)
{
	if (is_array($f))
		$f = $f[0];
	$f();
}
$edit_verify = get_random_id();
session_set('edit_verify', $edit_verify);
echo '<input type="hidden" name="edit_verify" value="' . $edit_verify . '" />';
echo '<input style="clear:both; float: left" type="submit" value="' . __('submit') . '" />';
echo '</form>';

function _get_val(&$array, $key, $default = NULL)
{
	return is_array($array) && isset($array[$key]) ? $array[$key] : $default;
}

function _make_form_input($prompt, $post_name, $default = NULL)
{
	$id = get_unique_id();
	if (is_null($default))
		$default = '';
	echo "<div class='form-field'>
		<label for='$id'>$prompt</label><input type='text' name='$post_name' value='$default' id='$id' />
		</div>";
}

function _make_form_textarea($prompt, $post_name, $default = NULL, $small = FALSE)
{
	$id = get_unique_id();
	if (is_null($default))
		$default = '';
	$class = $small ? 'small' : 'big';
	echo "<div class='form-field'><label for='$id'>$prompt</label>
		<textarea class='$class' id='$id' name='$post_name'>$default</textarea></div>";
}

function get_post($name)
{
	if (!isset($_POST[$name]))
		throw new Exc_runtime(__('incomplete post'));
	return $_POST[$name];
}

function show_id()
{
	global $pinfo;
	if (is_array($pinfo))
		echo '<label>' . __('Problem id:') . '</label>' . $pinfo['id'];
}

function edit_code()
{
	global $pinfo;
	_make_form_input(__('Problem code:'), 'code', _get_val($pinfo, 'code'));
}

function get_code()
{
	$val = get_post('code');
	prob_validate_code($val);
	global $pinfo;
	$pinfo['code'] = $$val;
}

function edit_title()
{
	global $pinfo;
	_make_form_input(__('Problem title:'), 'title', _get_val($pinfo, 'title'));
}

function get_title()
{
	global $pinfo;
	$pinfo['title'] = htmlencode(get_post('title'));
}

function edit_desc()
{
	global $pinfo, $root_path;
	if (!is_null($pinfo) && !empty($pinfo['desc']))
		$desc = unserialize($pinfo['desc']);
	else
		$desc = array();
	_make_form_input(__('Time limit:'), 'desc[time]', _get_val($desc, 'time', __('1 second')));
	_make_form_input(__('Memory limit:'), 'desc[memory]', _get_val($desc, 'memory', __('256 MB')));
	$fields = array(
		'desc' => __('Description'),
		'input_fmt' => __('Input Format'),
		'output_fmt' => __('Output Format'));
	foreach ($fields as $key => $val)
	{
		$id = get_unique_id();
		echo "<div class='form-field'><label for='$id'>$val</label><br />";
		echo "<textarea id='$id' name='desc[$key]'>";
		echo htmlencode(_get_val($desc, $key));
		echo '</textarea><script type="text/javascript">
			CKEDITOR.replace("' . $id . '");
		</script></div>';
	}

	$fields = array(
		'input_samp' => __('Sample Input'),
		'output_samp' => __('Sample Output'),
		'range' => __('Range'),
		'source' => __('Source'),
		'hint' => __('Hint')
	);

	$small = 0;
	foreach ($fields as $key => $val)
	{
		$small ++;
		_make_form_textarea($val, "desc[$key]", _get_val($desc, $key), $small > 2);
	}
}

function get_desc()
{
	$desc_post = get_post('desc');
	if (!is_array($desc_post))
		throw new Exc_runtime('invalid desc type');
	$fields = array(
		'time', 'memory', 'desc', 'input_fmt', 'output_fmt',
		'input_samp', 'output_samp', 'range', 'source', 'hint'
	);
	$fields_no_html_encode = array(
		'desc', 'input_fmt', 'output_fmt', 'source', 'hint'
	);
	$desc = array();
	foreach ($fields as $f)
	{
		if (!isset($desc_post[$f]))
			throw new Exc_runtime(__('incomplete post'));
		$val = $desc_post[$f];
		if (in_array($f, $fields_no_html_encode))
		{
			try
			{
				xhtml_validate($val);
			}
			catch (Exc_xhtml $e)
			{
				throw new Exc_runtime(__('XHTML validation error for field %s: %s', $f, $e->msg()));
			}
		}
		else
			$val = htmlencode($val);
		$desc[$f] = $val;
	}
	global $pinfo;
	$pinfo['desc'] = $desc;
}

function edit_io()
{
	global $pinfo;
	if (empty($pinfo['io']))
		$io = array('', '');
	else
		$io = unserialize($pinfo['io']);
	_make_form_input(__('Input(empty for stdin):'), 'io0', $io[0]);
	_make_form_input(__('Output(empty for stdout):'), 'io1', $io[1]);
}

function get_io()
{
	global $pinfo;
	$pinfo['io'] = serialize(array(get_post('io0'), get_post('io1')));
}

function edit_perm()
{
	global $pinfo;
	echo '<div class="form-field">';
	echo '<label style="float: left">' . __('Set problem permission') .
		'</label><div style="float: left">';
	form_get_perm_editor('perm', _get_val($pinfo, 'perm'));
	echo '</div></div>';
}

function get_perm()
{
	global $pinfo;
	$pinfo['perm'] = form_get_perm_editor_val('perm');
}

