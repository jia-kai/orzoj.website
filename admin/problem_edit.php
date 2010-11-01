<?php
/*
 * $File: problem_edit.php
 * $Date: Mon Nov 01 11:08:44 2010 +0800
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

$session_prefix = 'prob_edit';

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
echo '</form>';

function _get_val(&$array, $key, $default = NULL)
{
	return isset($array[$key]) ? $array[$key] : $default;
}

function _make_form_input($prompt, $post_name, $default = NULL)
{
	$id = get_random_id();
	if (is_null($default))
		$default = '';
	echo "<div class='form-field'>
		<label for='$id'>$prompt</label><input type='text' name='$post_name' value='$default' id='$id' />
		</div>";
}

function show_id()
{
	global $pinfo;
	if (is_array($pinfo))
		echo __('Problem id:') . $pinfo['id'];
}

function edit_code()
{
	global $pinfo;
	_make_form_input(__('Problem code:'), 'title', is_array($pinfo) ? $pinfo['code'] : NULL);
}

function get_code()
{
}


function edit_title()
{
	global $pinfo;
	_make_form_input(__('Problem title:'), 'title', is_array($pinfo) ? $pinfo['title'] : NULL);
}

function get_title()
{
}

function edit_desc()
{
	global $pinfo;
	if (!is_null($pinfo) && !empty($pinfo['desc']))
		$desc = unserialize($pinfo['desc']);
	else
		$desc = array();
	_make_form_input(__('Time limit:'), 'desc[time]', _get_val($desc, 'time', __('1 second')));
	_make_form_input(__('Memory limit:'), 'desc[memory]', _get_val($desc, 'memory', __('256 MB')));
}

function get_desc()
{
}

function edit_io()
{
}

function get_io()
{
}

function edit_perm()
{
}

function get_perm()
{
}

