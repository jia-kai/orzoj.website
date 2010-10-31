<?php
/*
 * $File: problem_edit.php
 * $Date: Sun Oct 31 11:55:15 2010 +0800
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
 */

if (isset($_GET['delete']) && isset($_POST['pid']) && $_POST['delete_verify'] == session_get('delete_verify') &&
	!empty(session_get('delete_verify')))
	prob_delete(intval($_POST['pid']));

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

$pinfo = NULL;
if (!empty($_GET['edit']))
{
	$pinfo = $db->select_from('problems', array_keys($fields), array(
		$DBOP['='], 'id', $_GET['edit']));
	if (empty($pinfo))
		die('no such problem');
	else
		$pinfo = $pinfo[0];
}

echo "<form action='$cur_page_link&amp;do' method='post'>";
foreach ($fields as $f)
	$f[0]();
echo '</form>';

