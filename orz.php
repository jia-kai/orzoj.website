<?php
/*
 * $File: orz.php
 * $Date: Thu Sep 30 22:48:54 2010 +0800
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
require_once $includes_path . 'user.php';
require_once $includes_path . 'sched.php';
require_once $includes_path . 'judge.php';
require_once $includes_path . 'exe_status.inc.php';
require_once $includes_path . 'plugin.php';
require_once $includes_path . 'record.inc.php';

define('MSG_VERSION', 1);

/* MSG_STATUS */
define('MSG_STATUS_OK', 0);
define('MSG_STATUS_ERROR', 1);

$static_password = option_get('static_password');
if ($static_password === FALSE)
	die(__('static password is not set'));

if (isset($_REQUEST['action'])) // login
{
	if ($_REQUEST['action'] == 'login1')
	{
		if (!isset($_REQUEST['version']))
			exit('0');
		if (MSG_VERSION != $_REQUEST['version'])
			exit('0');
		$dynamic_password_array = option_get('dynamic_password_with_time');
		$dp = unserialize($dynamic_password_array);
		if (is_array($dp) && time() - $dp['time']  < DYNAMIC_PASSWORD_LIFETIME)
		{
			exit($dp['password']);
		}
		else
		{
			mt_srand(time());
			$password = (uniqid(mt_rand(), true));
			$password_array = array('time' => time(), 'password' => $password);
			option_set('dynamic_password_with_time', serialize($password_array));
			exit($password);
		}
	}
	else if ($_REQUEST['action'] == 'login2')
	{
		if (!isset($_REQUEST['checksum']))
			exit('0');
		$dynamic_password_array = option_get('dynamic_password_with_time');

		$dp = unserialize($dynamic_password_array);
		if (!is_array($dp))
			exit('0');

		$stdchecksum = sha1(sha1($dp['password'] . $static_password));
		$verify = sha1(sha1($dp['password']) . $static_password);
		if ($_REQUEST['checksum'] == $stdchecksum)
		{
			option_set('dynamic_password', $dp['password']);
			option_set('thread_req_id', serialize(array()));
			exit($verify);
		}
		else exit('0');
	} else exit('hello, world!');
}

// authentication and data decoding
if (isset($_REQUEST['data']))
{
	$data = json_decode($_REQUEST['data']);
	if (isset($data->thread_id) && isset($data->req_id) && isset($data->data) && isset($data->checksum))
	{
		$thread_id = $data->thread_id;
		$req_id = $data->req_id;
		$dynamic_password = option_get('dynamic_password');

		$db_rid = unserialize(option_get('thread_req_id'));
		if (!is_array($db_rid))
			exit('1');


		if (!isset($db_rid[$thread_id]))
			$db_rid[$thread_id] = 0;

		$stdchecksum = sha1($thread_id . $req_id . sha1($dynamic_password . $static_password) . $data->data);
		if ($stdchecksum != $data->checksum)
			exit('relogin');

		if ($db_rid[$thread_id] != $req_id)
			exit('relogin');

		$db_rid[$thread_id] ++;
		option_set('thread_req_id', serialize($db_rid));
	}
	else
		exit('4');

	$func_param = json_decode($data->data);

	// use $func_param as a global variable
	call_func($func_param->action);
}
else
	exit('what\'s going on?');


/* ----------------------------------------- */
/* | All called functions are listed below  |*/
/* ----------------------------------------- */

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
	$query_list = unserialize(option_get('judge_info_list'));
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
	if ($ret = judge_get_id_by_name($judge_name))
		judge_update($ret, $judge_name, $lang_sup, $query_ans);
	else
		$ret = judge_add($judge_name, $lang_sup, $query_ans);
	judge_set_online($ret);
	msg_write(MSG_STATUS_OK, array('id_num' => $ret));
}

/**
 * search a judge by name
 * @param string $name judge name
 * @return int judge id, or 0 if no such judge
 */
function judge_get_id_by_name($name)
{
	global $db, $DBOP;
	$row = $db->select_from('judges', 'id', array($DBOP['=s'], 'name', $name));
	if (count($row) != 1)
		return 0;
	return $row[0]['id'];
}

/**
 * remove a judge
 * @return void
 */
function remove_judge()
{
	global $func_param;
	judge_set_offline($func_param->judge);
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 * @ignore
 * throwing out this exception means a task is fetched.
 */
class Exc_msg extends Exception
{
}

/**
 * get a request from table 'orz_req'
 */
function get_request()
{
	global $db, $DBOP;
	$req = $db->select_from('orz_req', NULL, NULL, NULL, NULL, 1);
	if (count($req) > 0)
	{
		$req = $req[0];
		$db->delete_item('orz_req', array($DBOP['='], 'id', $req['id']));

		$req = unserialize($req['data']);
		if ($req['type'] == 'src')
		{
			$src = $db->select_from('sources', 'src',
				array($DBOP['='], 'rid', $req['id']));
			if (count($src) != 1)
				throw new Exc_inner(__('source not found'));
			$req['src'] = $src[0]['src'];
			$db->update_data('records', array('status' => RECORD_STATUS_WAITING_ON_SERVER),
				array($DBOP['='], 'id', $req['id']));
			$db->update_data('sources', array('sent' => 1),
				array($DBOP['='], 'rid', $req['id']));
		}

		msg_write(MSG_STATUS_OK, $req);
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
 * @return void
 */
function fetch_task()
{
	sched_work();
	try
	{
		get_request();
		no_task();
	}
	catch (Exc_msg $e){}
}

/**
 *  report to orzoj-website that none of the judges 
 *  has the data of this problem
 *  @return void
 */
function report_no_data()
{
	global $db, $func_param, $DBOP;
	$rid = $func_param->task;
	$value = array('status' => RECORD_STATUS_DATA_NOT_FOUND);
	$where_clause = array($DBOP['='], 'id', $rid);
	$db->update_data('records', $value, $where_clause);
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 *  report to orzoj-website that none of the judges 
 *  has the data of this problem
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
 * get uid by rid
 * @param int $rid
 * @return int uid
 */
function get_uid_by_rid($rid)
{
	global $db, $DBOP;
	$ret = $db->select_from('records', array('uid'), array($DBOP['='], 'id', $rid));
	return $ret[0]['uid'];
}

/**
 *  report to orzoj-website that the judge is compiling source
 *  @return void
 */
function report_compiling()
{
	global $db, $func_param, $DBOP;
	$rid = $func_param->task;
	$jid = $func_param->judge;
	$value = array(
		'status' => RECORD_STATUS_COMPILING,
		'jid' => $jid,
		'jtime' => time());
	$where_clause = array($DBOP['='], 'id', $rid);
	$db->update_data('records', $value, $where_clause);
	user_update_statistics(get_uid_by_rid($rid), array('submit'));
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 *  report to orzoj-website that the judge compiled successfully
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
 *  @return void
 */
function report_compile_failure()
{
	global $db, $func_param, $DBOP;
	$rid = $func_param->task;
	$value = array('status' => RECORD_STATUS_COMPILE_FAILURE,
		'detail' => $func_param->info);
	$where_clause = array($DBOP['='], 'id', $rid);
	$db->update_data('records', $value, $where_clause);
	user_update_statistics(get_uid_by_rid($rid), array('ce'));
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 *  report to orzoj-website a single case result
 *  @return void
 */
function report_case_result()
{
	global $db, $func_param, $DBOP;
	$rid = $func_param->task;
	$result = new Case_result();

	foreach(get_class_vars(get_class($result)) as $key => $val)
		$result->$key = $func_param->$key;

	$col = array('detail');
	$where_clause = array($DBOP['='], 'id', $rid);
	$detail = $db->select_from('records', $col, $where_clause);
	$detail = $detail[0]['detail'];

	$detail = @unserialize($detail);
	if (!is_array($detail))
		$detail = array();

	$detail[] = $result;

	$value = array('detail' => serialize($detail));
	$db->update_data('records', $value, $where_clause);

	msg_write(MSG_STATUS_OK, NULL);
}


/**
 *  report to orzoj-website a prob result
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
	if ($func_param->total_score == $func_param->full_score && $func_param->total_score)
	{
		$result = 'ac';
		$value['status'] = RECORD_STATUS_ACCEPTED;
	}
	else
	{
		$result = 'unac';
		$value['status'] = RECORD_STATUS_WRONG_ANSWER;
	}

	$where_clause = array($DBOP['='], 'id', $rid);
	$db->update_data('records', $value, $where_clause);
	user_update_statistics(get_uid_by_rid($rid), array($result));
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 * add a judge
 * @param string $name
 * @param string $lang_sup serialized array, 
 *			see $root_path . 'install/table.php'
 * @param string $query_ans serialized array,
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
	filter_apply('after_add_judge', true, $insert_id);
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
	filter_apply('after_add_judge', true, $id);
	$db->transaction_commit();
	return $id;
}

/**
 * set judge status
 * @param int $id
 * @param int $status see const.inc.php
 * @param $success_filter 
 * @return void
 */
function judge_set_status($id, $status, $success_filter)
{	
	global $db, $DBOP;
	$condition = array($DBOP['='], 'id', $id);
	$content = array('status' => $status);
	$db->update_data('judges', $content, $condition);
	filter_apply($success_filter, TRUE, $id);
}

/**
 * set judge status to online
 * @param int $id
 * @return void
 */
function judge_set_online($id)
{
	judge_set_status($id, JUDGE_STATUS_ONLINE, 'after_set_judge_online');
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

