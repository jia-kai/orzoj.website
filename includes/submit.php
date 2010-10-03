<?php
/* 
 * $File: submit.php
 * $Date: Sun Oct 03 22:05:15 2010 +0800
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

require_once $includes_path . 'problem.php';

/**
 * get a form for submitting source code
 * @param int $pid default problem id
 * @return string
 */
function submit_get_form($pid)
{
	if (!user_check_login())
		throw new Exc_runtime(__('Not logged in'));
	global $db, $user;
	$plang = array();
	foreach ($db->select_from('plang') as $row)
		$plang[$row['name']] => $row['id'];
	if (!is_int($pid))
		$pid = '';
	$str = 
		tf_form_get_text_input(__('Problem id:'), 'pid', $pid) .
		tf_form_get_select(__('Programming language:'), 'plang', $plang, $user->plang) .
		tf_form_get_source_editor(__('Source:'), 'src');
	return filter_apply('after_submit_form', $str);
}

/**
 * parse posted data and submit the source
 * @return void
 */
function submit()
{
	if (!user_check_login())
		throw new Exc_runtime(__('Not logged in'));
	filter_apply_no_iter('before_submit');
	if (!isset($_POST['pid']) || !isset($_POST['plang']))
		throw new Exc_runtime(__('incomplete post'));
	global $db, $DBOP, $user;
	$pid = intval($_POST['pid']);
	$row = $db->select_from('problems', array('grp_deny', 'grp_allow'),
		array($DBOP['='], 'id', $pid));
	if (count($row) != 1)
		throw new Exc_runtime(__('No such problem'));
	$row = $row[0];

	if (!prob_check_perm($user, $row['grp_deny'], $row['grp_allow'])
		throw new Exc_runtime(__('Permission denied for this problem'));

	$now = time();
	$row = $db->select_from('map_prob_ct', 'cid',
		array($DBOP['&&'], $DBOP['&&'], $DBOP['&&'],
		$DBOP['='], 'pid', $pid,
		$DBOP['<='], 'time_start', $now
		$DBOP['>='], 'time_end', $now));
	if (count($row))
	{
		$row = $row[0];
	}
}

