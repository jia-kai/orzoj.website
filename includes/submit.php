<?php
/* 
 * $File: submit.php
 * $Date: Thu Oct 21 16:51:13 2010 +0800
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
require_once $includes_path . 'contest/ctal.php';
require_once $includes_path . 'record.php';
/**
 * echo fileds in the form for submitting source code
 * @param int $pid default problem id
 * @exception Exc_runtime
 * @return void
 */
function submit_src_get_form($pid)
{
	global $db, $user;
	if (!user_check_login())
	$plang = array();
	foreach ($db->select_from('plang') as $row)
		$plang[$row['name']] = $row['id'];
	if (!is_int($pid))
		$pid = '';
	$str = 
		tf_form_get_text_input(__('Problem code:'), 'code', NULL, prob_get_code_by_id($pid)) .
		tf_form_get_select(__('Programming language:'), 'plang', $plang, $user->plang) .
		tf_form_get_source_editor(__('Source code:'), 'src');
	echo filter_apply('after_submit_src_form', $str);
}

/**
 * parse posted data and submit the source
 * @exception Exc_runtime
 * @return void
 */
function submit_src()
{
	global $db, $DBOP, $user, $PROB_SUBMIT_PINFO;
	if (!user_check_login())
		throw new Exc_runtime(__('not logged in'));
	filter_apply_no_iter('before_submit_src');
	if (!isset($_POST['code']) || !isset($_POST['plang']))
		throw new Exc_runtime(__('incomplete POST'));

	$pid = prob_get_id_by_code($_POST['code']);
	$plang = intval($_POST['plang']);
	$row = $db->select_from('problems', $PROB_SUBMIT_PINFO,
		array($DBOP['='], 'id', $pid));
	if (count($row) != 1)
		throw new Exc_runtime(__('no such problem'));
	$row = $row[0];

	if (!prob_check_perm($user->get_groups(), $row['perm']))
		throw new Exc_runtime(__('permission denied for this problem'));

	$src = tf_form_get_source_editor_data('src');
	$max_src_length = intval(option_get('max_src_length'));
	if (strlen($src) > $max_src_length)
		throw new Exc_runtime(__('source length exceeds the limit (%d bytes)', $max_src_length));

	$ct = ctal_get_class_by_pid($pid);
	if ($ct)
		$ct->user_submit($row, $plang, $src);
	else
	{
		if (is_string($row['io']) && strlen($row['io']))
			$io = unserialize($row['io']);
		else $io = array('', '');
		submit_add_record($pid, $plang, $src, $io);
	}
}

/**
 * insert the record into the database
 * @param int $pid problem id
 * @param int $lid programming language id
 * @param string $src the source
 * @param array $io array(<input filename>, <output filename>), empty string for stdio
 * @param int $status the initial status for this record
 * @param int $cid contest id
 * @return int record id
 */
function submit_add_record($pid, $lid, $src, $io,
	$status = RECORD_STATUS_WAITING_TO_BE_FETCHED, $cid = 0)
{
	if (!user_check_login())
		throw new Exc_inner(__('Not logged in'));
	global $db, $DBOP, $user;
	$db->transaction_begin();
	$rid = $db->insert_into('records',
		array(
			'uid' => $user->id,
			'pid' => $pid,
			'lid' => $lid,
			'cid' => $cid,
			'src_len' => strlen($src),
			'status' => $status,
			'stime' => time(),
			'ip' => get_remote_addr(),
			'detail' => serialize($io)
		));
	$db->insert_into('sources',
		array(
			'rid' => $rid,
			'src' => $src,
			'time' => time()
		));
	$db->transaction_commit();
	return $rid;
}

