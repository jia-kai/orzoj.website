<?php
/* 
 * $File: submit.php
 * $Date: Sun Oct 17 10:32:55 2010 +0800
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

	if (!prob_check_perm($user->groups, $row['perm']))
		throw new Exc_runtime(__('permission denied for this problem'));

	$src = tf_form_get_source_editor_data('src');
	$max_src_length = intval(option_get('max_src_length'));
	if (strlen($src) > $max_src_length)
		throw new Exc_runtime(__('source length exceeds the limit (%d bytes)', $max_src_length));

	$ct = ctal_get_class($pid);
	if ($ct)
		$ct->user_submit($row, $plang, $src);
	else
	{
		if (is_string($row['io']) && strlen($row['io']))
			$io = unserialize($row['io']);
		else $io = array('', '');
		$rid = submit_add_record($pid, $plang, $src);
		submit_add_judge_req($rid, $io[0], $io[1]);
	}
}

/**
 * insert the record into the database
 * @param int $pid problem id
 * @param int $lid programming language id
 * @param string $src the source
 * @param int $status the initial status for this record
 * @return int record id
 */
function submit_add_record($pid, $lid, $src,
	$status = RECORD_STATUS_WAITING_TO_BE_FETCHED)
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
			'src_len' => strlen($src),
			'status' => $status,
			'stime' => time(),
			'ip' => get_remote_addr()
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

/**
 * add a judge request so that the source will be judged soon
 * @param int $rid record id
 * @param string $input input file name, or empty string to use stdin
 * @param string $output output file name, or empty string to use stdout
 * @exception Exc_runtime
 * @return void
 */
function submit_add_judge_req($rid, $input, $output)
{
	global $db, $DBOP;
	$err_msg = NULL;
	$row = $db->select_from('records', array('pid', 'lid'),
		array($DBOP['='], 'id', $rid));
	if (count($row) != 1)
		throw new Exc_inner(__('No such record #%d', $rid));
	$row = $row[0];
	$pcode = $db->select_from('problems', 'code',
		array($DBOP['='], 'id', $row['pid']));
	if (count($pcode) != 1)
	{
		$db->update_data('records',
			array(
				'status' => RECORD_STATUS_ERROR,
				'detail' => __('No such problem #%d', $row['pid'])
			), array($DBOP['='], 'id', $rid));
		return;
	}
	$pcode = $pcode[0]['code'];

	$lang = $db->select_from('plang', 'name',
		array($DBOP['='], 'id', $row['lid']));
	if (count($lang) != 1)
	{
		$db->update_data('records',
			array(
				'status' => RECORD_STATUS_ERROR,
				'detail' => __('No such programming language #%d', $row['lid'])
			), array($DBOP['='], 'id', $rid));
		return;
	}
	$lang = $lang[0]['name'];

	$db->insert_into('orz_req', array(
		'data' => serialize(array(
			'type' => 'src',
			'id' => $rid,
			'prob' => $pcode,
			'lang' => $lang,
			'input' => $input,
			'output' => $output
		))));
}

