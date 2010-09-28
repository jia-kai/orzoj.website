<?php
/* 
 * $File: msg_func.php
 * $Date: Tue Sep 28 16:47:14 2010 +0800
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

require_once $includes_path . 'judges.php';
require_once $includes_path . 'sched.php';
require_once $includes_path . 'const.inc.php';

/**
 * write a massage to sever
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
 * call functions in this page with exceptions dealing.
 * exceptions are mostly throwd from dabase.
 * @param string $name function to call
 * @return void
 */
function call_func($name)
{
	try
	{
		call_user_func($name);
	} catch (Exc_orzoj $e)
	{
		msg_write(MSG_STATUS_ERROR, $e->msg());
	}
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
	$db->update_data('records', $value_array, $where_clause);
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
		judge_online();
		msg_write(STATUS_OK, array('id_num' => $ret));
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
	$where_clause = array($DBOP['='], 'id', $id);
	if ($db->delete_item('judges', $where_clause) === FALSE)
		msg_write(MSG_STATUS_ERROR, __("remove judge error."));
	else
		msg_write(MSG_STATUS_OK, NULL);
}

/**
 * @ignore
 * throw out this exception means a task is fetched.
 */
class Exc_msg extends Exception
{
}

/**
 * fetch a judge request
 * @return void throw a Exception Exc_msg if succeed
 */
function fetch_judge_request()
{
	/* judge request */
	$where_clause = array($DBOP['='], 'status', RECORD_STATUS_WAITING_TO_BE_FETCHED);
	$orderby = array('stime' => 'ASC');
	$record = $db->select_from('records', NULL, $where_clause, $orderby, NULL, 1);
	if (count($record) != 0)
	{
		$record = $record[0];

		// get problem code
		$where_clause = array($DBOP['='], 'id', $record['pid']);
		$pcode = $db->select_from('problems', NULL, $where_clause);
		if (count($pcode) == 0)
			throw new Exc_orzoj(__("MSG Error: Can not find problem %d in database.", $record['pid']));
		$pcode = $pcode[0]['code'];

		// get src
		$where_clause = array($DBOP['='], 'id', $record['id']);
		$src = $db->select_from('sources', NULL, $where_clause);
		if (count($src) == 0)
			throw new Exc_orzoj(__("MSG Error: Can not find source %d in database", $record['sid']));
		$src = $src[0]['src'];

		// get language string
		$where_clause = array($DBOP['='], 'id', $record['lid']);
		$lang = $db->select_from('plang', NULL, $where_clause);
		if (count($lang) == 0)
			throw new Exc_orzoj(__("MSG Error: Can not find plang %d in database", $record['lid']));
		$lang = $lang[0]['name'];

		msg_write(MSG_STATUS_OK, 
			array(
				'type' => 'judge',
				'id' => $ret['id'],
				'pcode' => $pcode,
				'lang' => $lang,
				'src' => $src,
				// FIXME: where can I find use file or not, in contest or record?
				'input' => '', // XXX
				'output' => '', // XXX
			)
		);
		// set source sent 
		$value = array('sent' => 1);
		$where_clause = array($DBOP['='], 'id', $record['sid']);
		$db->update_data('sources', $value, $where_clause);

		throw new Exc_msg();
	}

}

/**
 * fetch a get_src request
 * @global $db
 * @return void throw a Exception Exc_msg
 */
function fetch_get_src_request()
{
	global $db;
	$src_req = $db->select_from('src_req', NULL, NULL, NULL, NULL, 1);
	if (count($src_req) != 0)
	{
		$src_req = $src_req[0];
		msg_write(MSG_STATUS_OK, 
			array(
				'type' => 'src',
				'sid' => $src_req['sid'],
			)
		);
		throw new Exc_msg();
	}
}

/**
 * XXX
 * fetch a get_data_list request
 * @global $db
 * @return void throw a Exception Exc_msg
 */
function fetch_get_data_list_request()
{
	global $db;
}

/**
 * XXX
 * fetch a get_data request
 * @global $db
 * @return void throw a Exception Exc_msg
 */
function fetch_get_data_request()
{
	global $db;
}

/**
 * no task
 * @return void throw a Exception Exc_msg
 */
function no_task()
{
	throw new Exc_msg();
}

/**
 * fetch a task which is to be executed (web request)
 * there are four request now:
 * "judge" : id, prob, lang, src, input, output
 * "get_src" : id
 * "get_data" : prob
 * "none" 
 * @return void
 */
function fetch_task()
{
	try
	{
		sched_work();
		fetch_get_src_request();
		fetch_judge_request();
		fetch_get_data_list_request();
		fetch_get_data_request();
		no_task();
	}
	catch (Exc_msg $e);
}

/**
 *  report to orzoj-website that no judges are available in
 *  a specific language for a record
 *  @global $db
 *  @return void
 */
function report_no_judge()
{
	$db->s
}

function judge_add($name,$lang_sup,$query_ans)
{
	global $db;
	$content = array(
		'name' => $name,
		'lang_sup' => serialize($lang_sup),
		'detail' => serialize($query_ans)
	);
	$db->transaction_begin();
	$insert_id = $db->insert_into('judges',$content);
	apply_filters('after_add_judge',true,$insert_id);
	$db->transaction_commit();
	return $insert_id;
}


function judge_update($id, $name, $lang_sup, $query_ans)
{
	global $db;
	$condition = array($DBOP['='], 'id', $id);
	$content = array(
		'name' => $name,
		'lang_sup' => serialize($lang_sup),
		'detail' => serialize($query_ans)
	);
	$db->transaction_begin();
	$db->update_data('judges', $content, $condition);
	apply_filters('after_add_judge', true, $id);
	$db->transaction_commit();
	return $id;
}


function judge_set_status($id, $status, $success_filter)
{	
	global $db;
	$condition = array($DBOP['='], 'id', $id);
	$content = array('status' => $status);
	$db->update_data('judges', $content, $condition);
	apply_filters($success_filter, TRUE, $id);
}

function judge_set_online($id)
{
	judge_set_status($id, JUDGE_STATUS_ONLINE, 'after_judge_online');
}


function judge_set_offline($id)
{
	judge_set_status($id, JUDGE_STATUS_OFFLINE, 'after_judge_offline');
}

function judge_set_running($id)
{
	judge_set_status($id, JUDGE_STATUS_RUNNING, 'after_judge_running');
}

