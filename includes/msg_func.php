<?php
/* 
 * $File: msg_func.php
 * $Date: Mon Sep 27 23:35:14 2010 +0800
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

if (!defined('IN_ORZOJ')) exit;

require_once $includes_path . 'judges.php';
/**
 * msg_write massage to sever
 * @param int $status MSG_STATUS_OK or MSG_STATUS_ERROR
 * @param data string|array $data string if MSG_STATUS_ERROR, array if MSG_STATUS_OK
 * @return void
 */
function msg_write($status, $data)
{
	global $thread_id, $req_id, $static_password, $dynamic_password;
	if ($status == MSG_STATUS_OK)
		$data = json_encode($data);
	echo json_encode(array(
		'status' => $status,
		'data' => $data,
		'checksum' => sha1($thread_id . $req_id . sha1($dynamic_password . $static_password) . $status . $data)
		));
}
/**
 * report error to website
 * @global $func_param parameters in a array, including 'task' and 'msg'
 * @global $db 
 * @return void
 */
function report_error()
{
	global $func_param, $db;
	$task_id = $func_param->task;
	$msg = $func_param->msg;
	$value_array = array(
		'status' => RECORD_STATUS_ERROR,
		'detail' => $msg
	);
	$where_clause = array(
		$DBOP['='], 'id', $task_id
		);
	if ($db->update_data('records', $value_array, $where_clause) === FALSE)
		msg_write(MSG_STATUS_ERROR, $db->error());
	else
		msg_write(MSG_STATUS_OK, NULL);
}

/**
 * FIXME
 */
function get_query_list()
{
	msg_write(MSG_STATUS_OK, array('cpu', 'time'));
}

/**
 * register a new judge
 * @global $func_param parameters in a array, including 'judge' and 'query_ans'
 * @global $db 
 * @return void
 */
function register_new_judge()
{
	global $func_param, $db;
	$judge_name = $func_param->judge;
	$lang_sup = $func_param->lang_supported;
	$query_ans = json_decode($func_param->query_ans, TRUE);
	if ($ar = judge_search_by_name($judge_name))
		judge_update($ar[0]['id'], $judge_name, $lang_supported, $query_ans);
	else
	{
		$ret = judge_add($judge_name, $lang_supported, $query_ans);
		if ($ret === FALSE)
			msg_write(STATUS_ERROR, __("register new judge error.")); // xxx
		else
		{
			judge_online();
			msg_write(STATUS_OK, array('id_num' => $ret));
		}
	}

}

/**
 * remove a judge
 * @global $func_param parameters in a array, including 'judge'
 * @global $db 
 * @return void
 */
function remove_judge()
{
	global $func_param, $db;
	$judge_id = $func_param->judge;
	$where_clause = array(
		$DBOP['='], 'id', $id
	);
	if ($db->delete_item('judges', $where_clause) === FALSE)
		msg_write(MSG_STATUS_ERROR, __("remove judge error."));
	else
		msg_write(MSG_STATUS_OK, NULL);
}

/**
 * fetch a task which is to be executed
 * @global $db 
 */
// FIXME: should data request support?
function fetch_task()
{
	global $db;
	$where_clause = array($DBOP['<='], 'sched_time', time());
}

