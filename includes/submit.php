<?php
/* 
 * $File: submit.php
 * $Date: Fri Jan 06 21:13:23 2012 +0800
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
require_once $includes_path . 'judge.php';


/**
 * @ignore
 */
function _get_latest_src_by_user_and_prob($uid, $pid)
{
	global $db, $DBOP;

	$where = array($DBOP['='], 'uid', $uid);
	db_where_add_and($where, array($DBOP['='], 'pid', $pid));

	$order = array('id' => 'DESC');
	$row = $db->select_from('records', array('id', 'lid'), $where, $order, 0, 1);
	if (empty($row))
		return NULL;
	$row = $row[0];
	$rid = $row['id'];

	// XXX retrieve from orzoj-server
	$src = $db->select_from('sources', 'src', array($DBOP['='], 'rid', $rid));
	if (empty($src))
		return NULL;
	$src = $src[0]['src'];
	return array($row['lid'], $src);
}


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
		throw new Exc_runtime(__('You must be logined to sumbit source.'));
	$plang = array();
	foreach ($db->select_from('plang') as $row)
		$plang[$row['name']] = $row['id'];
	if (!is_int($pid))
		$pid = '';
	$str = '';
	if (!judge_get_amount(TRUE))
		$str .= tf_form_get_raw(NULL, '<span style="color:red;font-weight:bold">' .
			__('Warning: No judge is currently online, so your submission may not be judged immediately.') . '</span>');
	$lid = $user->plang;
	$src = '';
	if ($last_submit = _get_latest_src_by_user_and_prob($user->id, $pid))
		list($lid, $src) = $last_submit;
	$str .= 
		tf_form_get_text_input(__('Problem code:'), 'code', NULL, prob_get_code_by_id($pid)) .
		tf_form_get_select(__('Programming language:'), 'plang', $plang, $lid) .
		tf_form_get_source_editor(__('Source code:'), 'src', $src);
	echo filter_apply('after_submit_src_form', $str, $pid);
}

/**
 * parse posted data and submit the source
 * @exception Exc_runtime
 * @return bool whether the submission will be judged immediately
 */
function submit_src()
{
	global $db, $DBOP, $user, $PROB_SUBMIT_PINFO;
	if (!user_check_login())
		throw new Exc_runtime(__('not logged in'));
	if (!isset($_POST['code']) || !isset($_POST['plang']))
		throw new Exc_runtime(__('incomplete POST'));

	$pid = prob_get_id_by_code($_POST['code']);
	if ($pid == NULL)
		throw new Exc_runtime(__('No such problem'));
	filter_apply_no_iter('before_submit_src', $pid);
	$plang = intval($_POST['plang']);
	$row = $db->select_from('problems', $PROB_SUBMIT_PINFO,
		array($DBOP['='], 'id', $pid));
	if (count($row) != 1)
		throw new Exc_runtime(__('no such problem'));
	$row = $row[0];

	$src = tf_form_get_source_editor_data('src');
	$max_src_length = intval(option_get('max_src_length'));
	if (strlen($src) > $max_src_length)
		throw new Exc_runtime(__('source can not be longer than %d bytes', $max_src_length));

	if (!empty($row['io']))
		$row['io'] = unserialize($row['io']);
	else $row['io'] = NULL;

	$ct = ctal_get_class_by_pid($pid);
	if ($ct)
		return $ct->user_submit($row, $plang, $src);
	if (!$user->is_grp_member(GID_SUPER_SUBMITTER) && !prob_check_perm($user->get_groups(), $row['perm']))
		throw new Exc_runtime(__('permission denied for this problem'));
	$io = $row['io'];
	submit_add_record($pid, $plang, $src, $io);
	return TRUE;
}

/**
 * insert the record into the database
 * @param int $pid problem id
 * @param int $lid programming language id
 * @param string $src the source
 * @param array $io array(<input filename>, <output filename>) or NULL for stdio
 * @param int $status the initial status for this record
 * @param int $cid contest id
 * @return int record id
 */
function submit_add_record($pid, $lid, $src, $io,
	$status = RECORD_STATUS_WAITING_TO_BE_FETCHED, $cid = 0)
{
	if (!user_check_login())
		throw new Exc_inner(__('Not logged in'));
	if (is_null($io))
		$io = array('', '');	// see server-judge/orzoj/structures.py
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

