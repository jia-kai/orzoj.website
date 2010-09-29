<?php
/* TODO update user info!!!~
 * $File: msg.php
 * $Date: Wed Sep 29 12:05:36 2010 +0800
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

require_once 'pre_include.php';

define('MSG_VERSION', 1);

/* MSG_STATUS */
define('MSG_STATUS_OK', 0);
define('MSG_STATUS_ERROR', 1);


if (isset($_REQUEST['action'])) // login
{
	if (!isset($_REQUEST['version'])) exit('0');
	if (MSG_VERSION != $_REQUEST['version']) exit('0');
	if ($_REQUEST['action'] == 'login1')
	{
		$tmp_dynamic_password = option_get('tmp_dynamic_password');
		$dp = unserialize($tmp_dynamic_password);
		if (is_array($dp) && time() - $dp['time']  < 10)
		{
			exit($dp['password']);
		}
		else
		{
			mt_srand(time());
			$password = (uniqid(mt_rand(),true));
			$newpassword = array('time' => time(),'password' => $password);
			option_set('tmp_dynamic_password',serialize($newpassword));
			exit($password);
		}
	}
	else if ($_REQUEST['action'] == 'login2')
	{
		$tmp_dynamic_password = option_get('tmp_dynamic_password');
		$dp = unserialize($tmp_dynamic_password);
		$stdchecksum = sha1(sha1($dp['password'] . $static_password));
		if (!isset($_REQUEST['checksum'])) exit('0');
		else
		{
			$verify = sha1(sha1($dp['password']) . $static_password);
			if ($_REQUEST['checksum'] == $stdchecksum)
			{
				option_set('dynamic_password',$dp['password']);
				exit($verify);
			}
			else exit('0');
		}
	}
}


// xxx --test--
$func_param->task = 1;
$func_param->msg = "error";
$func_param->judge = 1;

call_func('report_error');

/*
if (isset($_REQUEST['data'])) // decode data from $_REQUEST
{
	$data = json_decode($_REQUEST['data']);
	if (isset($data->thread_id) && isset($data->req_id) && isset($data->data) && isset($data->checksum))
	{
		$thread_id = $data->thread_id;
		$req_id = $data->req_id;
		$dynamic_password = option_get('dynamic_password');
		// FIXME: check $req_id increment
		$stdchecksum = sha1($data->thread_id . $data-> req_id . sha1($dynamic_password . $static_password) . $data->data);
		if ($stdchecksum != $data->checksum)
			exit('0');
	}
	else
		exit('0');

	$func_param = json_decode($data->data);

	// use $func_param as a global variable
	// calling functions are in msg_function.php
	call_user_func($func_param->action);
}
else
	exit('1');
 */


/* -------------------------------------- */
/* | All msg functions are listed below | */
/* -------------------------------------- */

require_once $includes_path . 'judges.php';
require_once $includes_path . 'sched.php';
require_once $includes_path . 'const.inc.php';
require_once $includes_path . 'exe_status.inc.php';

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
 * @return void
 */
function report_error()
{
	global $func_param, $db, $DBOP;
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
 * send out query list to orzoj-sever
 * query list is stored in 'options' table, which is a serialized array, key is 'judge_info_list',.
 * @return void
 */
function get_query_list()
{
	global $db;
	$where_clause = array('key' => 'judge_info_list');
	$query_list = $db->select_from('options', array('value'), $where_clause);
	$query_list = unserialize($query_list[0]['value']);
	msg_write(MSG_STATUS_OK, $query_list);
}

/**
 * register a new judge
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
 * @return void
 */
function remove_judge()
{
	global $func_param, $db, $DBOP;
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
 * get a request from table 'msg_req'
 */
function get_request()
{
	global $db;
	$req = $db->select_from('msg_req', NULL, NULL, NULL, NULL, 1);
	if (count($req) > 0)
	{
		$req = $req[0];
		msg_write(MSG_STATUS_OK, $req['data']);
		throw new Exc_msg();
	}
}

/**
 * no task
 * @return void throw a Exception Exc_msg
 */
function no_task()
{
	msg_write(MSG_STATUS_OK, array('type' => 'none'));
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
		get_request();
		no_task();
	}
	catch (Exc_msg $e){}
}

/**
 *  report to orzoj-website that no judges are available in
 *  a specific language for a record
 *  @global $db
 *  @global $func_param
 *  @global $DBOP
 *  @return void
 */
function report_no_judge()
{
	global $db, $func_param, $DBOP;
	$rid = $func_param->task;
	$value = array('status' => RECORD_STATUS_LANGUAGE_NOT_SUPPORTED);
	$where_clause = array($DBOP['='], 'id', $rid);
	$db->update_data('records', $value, $where_clause);
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 *  report to orzoj-website that none of the judges 
 *  has the data of this problem
 *  @global $db
 *  @global $func_param
 *  @global $DBOP
 *  @return void
 */
function report_no_data()
{
	global $db, $func_param, $DBOP;
	$rid = $func_param->task;
	$value = array('status' => RECORD_STATUS_LANGUAGE_NOT_SUPPORTED);
	$where_clause = array($DBOP['='], 'id', $rid);
	$db->update_data('records', $value, $where_clause);
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 *  report to orzoj-website that none of the judges 
 *  has the data of this problem
 *  @global $db
 *  @global $func_param
 *  @global $DBOP
 *  @return void
 */
function report_judge_waiting()
{
	global $db, $func_param, $DBOP;
	$rid = $func_param->task;
	$value = array('status' => RECORD_STATUS_JUDGE_BUSY);
	$where_clause = array($DBOP['='], 'id', $rid);
	$db->update_data('records', $value, $where_clause);
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 *  report to orzoj-website that the judge is compiling source
 *  @global $db
 *  @global $func_param
 *  @return void
 */
function report_compiling()
{
	global $db, $func_param, $DBOP;
	$rid = $func_param->task;
	$jid = $func_param->jid;
	$value = array('status' => RECORD_STATUS_COMPILING,
					'jid' => $jid);
	$where_clause = array($DBOP['='], 'id', $rid);
	$db->update_data('records', $value, $where_clause);
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 *  report to orzoj-website that the judge compiled successfully
 *  @global $db
 *  @global $func_param
 *  @return void
 */
function report_compile_success()
{
	global $db, $func_param, $DBOP;
	$rid = $func_param->task;
	$value = array('status' => RECORD_STATUS_COMPILE_SUCCESS);
	$where_clause = array($DBOP['='], 'id', $rid);
	$db->update_data('records', $value, $where_clause);
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 *  report to orzoj-website that the judge has failed to compile
 *  @global $db
 *  @global $func_param
 *  @return void
 */
function report_compile_failure()
{
	global $db, $func_param, $DBOP;
	$rid = $func_param->task;
	$value = array('status' => RECORD_STATUS_COMPILE_FAILURE);
	$where_clause = array($DBOP['='], 'id', $rid);
	$db->update_data('records', $value, $where_clause);
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 *  report to orzoj-website a single case result
 *  @global $db
 *  @global $func_param
 *  @return void
 */
function report_case_result()
{
	global $db, $funca_param, $DBOP;
	$rid = $func_param->task;
	$result = new Case_result();

	foreach(get_class_vars(get_class($result)) as $key => $val)
		$result->$key = $func_param->$key;

	$col = array('detail');
	$where_clause = array($DBOP['='], 'id', $rid);
	$detail = $db->select_from('records', $col, $where_clause);
	$detail = $detail[0]['detail'];
	unserialize($detail);

	$detail[] = $result;

	$value = array('detail' => serialize($detail));
	$db->update_data('records', $value, $where_clause);

	msg_write(MSG_STATUS_OK, NULL);
}


/**
 *  report to orzoj-website a prob result
 *  @global $db
 *  @global $func_param
 *  @return void
 */
function report_prob_result()
{
	global $db, $func_param, $DBOP;
	$rid = $func_param->task;

	$value = array(
		'score' => $func_param->total_score,
		'full_score' => $func_param->full_score,
		'time' => $func_param->total_time,
		'mem' => $func_param->max_mem
	);
	$where_clause = array($DBOP['='], 'id', $rid);
	$db->update_data('records', $value, $where_clause);
	msg_write(MSG_STATUS_OK, NULL);
}


/**
 * add a judge
 * @param string $name
 * @param string $lang_sup serialized array, 
 *			see $root_path . 'install/table.php'
 * @param string $query_ans serialized array,
 *			XXX ?? what is the structure of $query_ans ??
 * @return int new judge id
 */
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

/**
 * update judge info
 * @param int $id
 * @param string $name
 * @param string $lang_sup
 * @param string $query_ans
 * @return int judge id
 * @see judge_add
 */
function judge_update($id, $name, $lang_sup, $query_ans)
{
	global $db, $DBOP;
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

/**
 * set judge status
 * @param int $id
 * @param int $status see const.inc.php
 * @param $success_filter XXX what does this mean? successor?
 * @return void
 */
function judge_set_status($id, $status, $success_filter)
{	
	global $db, $DBOP;
	$condition = array($DBOP['='], 'id', $id);
	$content = array('status' => $status);
	$db->update_data('judges', $content, $condition);
	apply_filters($success_filter, TRUE, $id);
}

/**
 * set judge status to online
 * @param int $id
 * @return void
 */
function judge_set_online($id)
{
	judge_set_status($id, JUDGE_STATUS_ONLINE, 'after_judge_online');
}

/**
 * set judge status to offline
 * @param int $id
 * @return void
 */
function judge_set_offline($id)
{
	judge_set_status($id, JUDGE_STATUS_OFFLINE, 'after_judge_offline');
}

/**
 * set judge status to busy
 * @param int $id
 * @return void
 */
function judge_set_busy($id)
{
	judge_set_status($id, JUDGE_STATUS_BUSY, 'after_judge_running');
}

