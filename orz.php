<?php
/*
 * $File: orz.php
 * $Date: Thu Oct 21 18:34:48 2010 +0800
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

if (isset($_GET['sched_work']))
{
	require_once $includes_path . 'sched.php';
	global $db, $DBOP, $root_path;
	$where_clause = array(
		$DBOP['<='], 'time', time());
	$db->transaction_begin();
	$rows = $db->select_from('scheds', NULL, $where_clause);
	$db->delete_item('scheds', $where_clause);
	$db->transaction_commit();
	$ok = TRUE;
	foreach ($rows as $row)
	{
		require_once $root_path . $row['file'];
		try
		{
			call_user_func_array($row['func'], unserialize($row['args']));
		}
		catch (Exc_orzoj $e)
		{
			$ok = FALSE;
			echo $e->msg();
		}
	}
	if ($ok)
		echo '0';
	die();
}

require_once $includes_path . 'judge.php';
require_once $includes_path . 'exe_status.php';
require_once $includes_path . 'plugin.php';
require_once $includes_path . 'record.php';
require_once $includes_path . 'contest/ctal.php';

define('MSG_VERSION', 1);

/* MSG_STATUS */
define('MSG_STATUS_OK', 0);
define('MSG_STATUS_ERROR', 1);

$static_password = option_get('static_password');
if ($static_password === FALSE)
	die('static password is not set');

if (isset($_GET['action'])) // login
{
	if ($_GET['action'] == 'login1')
	{
		if (!isset($_GET['version']))
			exit('0');
		if (MSG_VERSION != $_GET['version'])
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
	else if ($_GET['action'] == 'login2')
	{
		if (!isset($_GET['checksum']))
			exit('0');
		$dynamic_password_array = option_get('dynamic_password_with_time');

		$dp = unserialize($dynamic_password_array);
		if (!is_array($dp))
			exit('0');

		$stdchecksum = sha1(sha1($dp['password'] . $static_password));
		$verify = sha1(sha1($dp['password']) . $static_password);
		if ($_GET['checksum'] == $stdchecksum)
		{
			option_set('dynamic_password', $dp['password']);
			option_set('thread_req_id', serialize(array()));
			if (isset($_GET['refetch']))
				$db->update_data('records', array('status' => RECORD_STATUS_WAITING_TO_BE_FETCHED),
					array($DBOP['='], 'status', RECORD_STATUS_WAITING_ON_SERVER));
			exit($verify);
		}
		else
			exit('0');
	}
	else
		exit('hello, world!');
}

// authentication and data decoding
if (isset($_POST['data']))
{
	$data = json_decode($_POST['data']);
	if (is_null($data))
		die('invalid json encoded data');
	if (isset($data->thread_id) && isset($data->data) && isset($data->checksum))
	{
		$thread_id = $data->thread_id;
		$dynamic_password = option_get('dynamic_password');

		$db_rid = unserialize(option_get('thread_req_id'));
		if (!is_array($db_rid))
			exit('1');


		if (!isset($db_rid[$thread_id]))
			$db_rid[$thread_id] = 0;
		$req_id = $db_rid[$thread_id];

		$stdchecksum = sha1($thread_id . $req_id . sha1($dynamic_password . $static_password) . $data->data);
		if ($stdchecksum != $data->checksum)
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
	exit('Please DO NOT orz me... I am too weak...<br /> Tim orz!!!');


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
	$ret = judge_get_id_by_name($judge_name);
	if (is_null($ret))
		$ret = judge_add($judge_name, $lang_sup, $query_ans);
	else
		judge_update($ret, $judge_name, $lang_sup, $query_ans);
	judge_set_online($ret);
	msg_write(MSG_STATUS_OK, array('id_num' => $ret));
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
 * get a request from table 'orz_req'
 */
function get_request()
{
	return FALSE;
	// XXX: not implemented now
}

/**
 * get a submission that has not been judged
 */
function get_unjudged_submission()
{
	global $db, $DBOP;
	$row = $db->select_from('records', array('id', 'pid', 'lid', 'detail'),
		array($DBOP['='], 'status', RECORD_STATUS_WAITING_TO_BE_FETCHED),
		array('id' => 'ASC'), NULL, 1);
	if (count($row) != 1)
		return FALSE;
	$row = $row[0];
	$req = array('type' => 'src', 'id' => $row['id']);

	if (is_null($req['prob'] = prob_get_code_by_id($row['pid'])))
		throw Exc_inner(sprintf('no such problem #%d', $row['pid']));

	if (is_null($req['lang'] = plang_get_name_by_id($row['lid'])))
		throw Exc_inner(sprintf('no such programming language #%d', $row['lid']));

	$src = $db->select_from('sources', 'src',
		array($DBOP['='], 'rid', $row['id']));
	if (count($src) != 1)
		throw new Exc_inner(sprintf('source for record #%d not found', $row['id']));
	$req['src'] = $src[0]['src'];

	$tmp = unserialize($row['detail']);
	$req['input'] = $tmp[0];
	$req['output'] = $tmp[1];

	$db->update_data('records', array('status' => RECORD_STATUS_WAITING_ON_SERVER),
		array($DBOP['='], 'id', $row['id']));
	msg_write(MSG_STATUS_OK, $req);
	return TRUE;
}

/**
 * no task
 */
function no_task()
{
	msg_write(MSG_STATUS_OK, array('type' => 'none'));
	return TRUE;
}

/**
 * fetch a task which is to be executed (web request)
 * @return void
 */
function fetch_task()
{
	get_request() || get_unjudged_submission() || no_task();
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
 *  report to orzoj-website that the judge is waiting (perhaps system busy)
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
 * update statistics value in the database
 * @param int $rid record id
 * @param string $type must be one of 'ac', 'unac', 'ce'
 * @return void
 */
function update_statistics($rid, $type)
{
	global $db, $DBOP;
	$row = $db->select_from('records', array('uid', 'pid', 'cid'),
		array($DBOP['='], 'id', $rid));
	if (count($row) != 1)
		throw new Exc_inner('No such record #%d', $rid);
	$row = $row[0];
	$cid = intval($row['cid']);

	$db->transaction_begin();

	$where = array($DBOP['&&'],
		$DBOP['='], 'uid', $row['uid'],
		$DBOP['='], 'pid', $row['pid']);
	$old_sts = $db->select_from('sts_prob_user', 'status', $where);

	$update_col_user = array("cnt_$type");
	$update_col_prob = array("cnt_$type");
	if (!count($old_sts))
	{
		$old_sts = NULL;
		$update_col_user[] = 'cnt_submit_prob';
		$update_col_user[] = 'cnt_ac_submit_sum';
		$update_col_prob[] = 'cnt_submit_user';
		if ($type == 'ac')
		{
			$update_col_user[] = 'cnt_ac_prob';
			$update_col_user[] = 'cnt_ac_prob_blink';
			$update_col_prob[] = 'cnt_ac_user';
			$new_sts = STS_PROB_USER_AC_BLINK;
		}
		else
			$new_sts = STS_PROB_USER_UNAC;
	}
	else
	{
		$old_sts = $old_sts[0]['status'];
		if ($old_sts == STS_PROB_USER_UNAC)
		{
			$update_col_user[] = 'cnt_ac_submit_sum';
			if ($type == 'ac')
			{
				$update_col_user[] = 'cnt_ac_prob';
				$update_col_prob[] = 'cnt_ac_user';
				$new_sts = STS_PROB_USER_AC;
			}
		}
	}

	if (isset($new_sts))
	{
		if (!is_null($old_sts))
			$db->update_data('sts_prob_user',
				array('status' => $new_sts), $where);
		else
			$db->insert_into('sts_prob_user',
				array('status' => $new_sts, 'uid' => $row['uid'], 'pid' => $row['pid']));
	}

	// update 'users' table
	$where = array($DBOP['='], 'id', $row['uid']);
	$cols = $update_col_user;
	$update_sts = array('cnt_ac_prob', 'cnt_ac_submit_sum');
	if (count(array_intersect($cols, $update_sts)))
		$cols = array_unique(array_merge($cols, $update_sts));
	else $update_sts = NULL;

	$tmp = $db->select_from('users', $cols, $where);
	if (count($tmp) != 1)
		throw Exc_inner(__('No corresponding user #%d for record #%d', $row['uid'], $rid));
	$tmp = $tmp[0];
	foreach ($update_col_user as $c)
		$tmp[$c] ++;
	if (!is_null($update_sts))
		$tmp['ac_ratio'] = floor($tmp['cnt_ac_prob'] * DB_REAL_PRECISION / $tmp['cnt_ac_submit_sum'] + 0.5);
	$db->update_data('users', $tmp, $where);


	// update 'problems' table
	$where[2] = $row['pid'];
	$cols = $update_col_prob;
	$update_sts = array('cnt_ac_user', 'cnt_submit_user');
	if (count(array_intersect($cols, $update_sts)))
		$cols = array_unique(array_merge($cols, $update_sts));
	else $update_sts = NULL;

	$tmp = $db->select_from('problems', $cols, $where);
	if (count($tmp) != 1)
		throw Exc_inner(__('No corresponding problem #%d for record #%d', $row['pid'], $rid));
	$tmp = $tmp[0];
	foreach ($update_col_prob as $c)
		$tmp[$c] ++;
	if (!is_null($update_sts))
	{
		$a = intval($tmp['cnt_ac_user']);
		$s = intval($tmp['cnt_submit_user']);
		$tmp['difficulty'] = floor(($s - $a) * DB_REAL_PRECISION / $s + 0.5);
	}
	$db->update_data('problems', $tmp, $where);

	$db->update_data('sources', array('sent' => 1),
		array($DBOP['='], 'rid', $rid));

	$db->transaction_commit();

	if ($cid)
	{
		$ct = ctal_get_class_by_cid($cid);
		$ct->judge_done($rid);
	}
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
	$value = array(
		'status' => RECORD_STATUS_COMPILE_SUCCESS,
		'mem' => $func_param->ncase
	);
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
	update_statistics($rid, 'ce');
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 *  report to orzoj-website a single case result
 *  @return void
 */
function report_judge_progress()
{
	global $db, $func_param, $DBOP;
	$db->update_data('records',
		array(
			'time' => $func_param->now,
			'status' => RECORD_STATUS_RUNNING
		),
		array($DBOP['='], 'id', $func_param->task));
	msg_write(MSG_STATUS_OK, NULL);
}

/**
 * determin the record status by execution details
 * @param array $details array of Case_result
 * @return int the status
 */
function determin_record_status($details)
{
	$cnt = array();
	foreach ($details as $d)
		if ($d->exe_status != EXESTS_RIGHT)
		{
			$s = $d->exe_status;
			if ($s == EXESTS_PARTIALLY_RIGHT)
				$s = EXESTS_WRONG_ANSWER;
			if (!isset($cnt[$s]))
				$cnt[$s] = 1;
			else $cnt[$s] ++;
		}
	asort($cnt);
	$s = key($cnt);

	if ($s == EXESTS_WRONG_ANSWER)
		return RECORD_STATUS_WRONG_ANSWER;

	if ($s == EXESTS_TLE)
		return RECORD_STATUS_TIME_LIMIT_EXCEED;

	if ($s == EXESTS_SIGSEGV)
		return RECORD_STATUS_MEMORY_LIMIT_EXCEED;

	if ($s == EXESTS_SYSTEM_ERROR)
		return RECORD_STATUS_ERROR;

	return RECORD_STATUS_RUNTIME_ERROR;
}

/**
 *  report to orzoj-website a prob result
 *  @return void
 */
function report_prob_result()
{
	global $db, $func_param, $DBOP;

	$status = RECORD_STATUS_ACCEPTED;
	$result = array();
	$tot_score = 0;
	$tot_time = 0;
	$max_mem = 0;
	foreach (get_class_vars('Case_result') as $var => $val)
		foreach ($func_param->$var as $idx => $val)
		{
			if (!isset($result[$idx]))
				$result[$idx] = new Case_result();
			$result[$idx]->$var = $val;
		}

	foreach ($result as $cres)
	{
		if ($cres->exe_status > 0)
		{
			$cres->exe_status += 2;
			$status = -1;
		}
		else
		{
			if ($cres->score == 0)
			{
				$cres->exe_status = EXESTS_WRONG_ANSWER;
				$status = -1;
			}
			else
			{
				$tot_score += $cres->score;
				$tot_time += $cres->time;
				if ($cres->memory > $max_mem)
					$max_mem = $cres->memory;

				if ($cres->score == $cres->full_score)
					$cres->exe_status = EXESTS_RIGHT;
				else
				{
					$cres->exe_status = EXESTS_PARTIALLY_RIGHT;
					$status = -1;
				}
			}
		}
	}

	if ($status == -1)
		$status = determin_record_status($result);

	$rid = $func_param->task;
	update_statistics($rid, $status == RECORD_STATUS_ACCEPTED ? 'ac' : 'unac');

	$db->update_data('records',
		array(
			'status' => $status,
			'score' => $tot_score,
			'time' => $tot_time,
			'mem' => $max_mem,
			'detail' => case_result_array_encode($result)
		),
		array($DBOP['='], 'id', $rid));

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
 * @param int $status see const.php
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

