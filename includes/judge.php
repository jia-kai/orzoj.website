<?php
/* 
 * $File: judge.php
 * $Date: Wed Dec 21 09:02:51 2011 +0800
 */
/**
 * @package orzoj-website
 * @license http://gnu.org/licenses GNU GPLv3
 */
/*
	This file is part of orzoj.

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

/**
 * judge info structure
 */
class Judge
{
	var $id, // int
		$name, // string
		$status, // see /includes/const.php, consts with JUDGE_STATUS_ prefix
		$lang_sup, // array of name of supported languages
		$detail; // array of info like 'cpu', 'mem', defined in table options:'judge_info_list'
}

/**
 * @ignore
 */
function _judge_make_where($online)
{
	global $db, $DBOP;
	if (time() - option_get('prev_orzoj_server_response', 0) > option_get('orzoj_server_max_rint', 30))
		$db->update_data('judges', array('status' => JUDGE_STATUS_OFFLINE));
	if (is_bool($online))
		return array($DBOP['='], 'status', $online ? JUDGE_STATUS_ONLINE : JUDGE_STATUS_OFFLINE);
	return NULL;
}

/**
 * get judges satisfying given conditions
 * @param NULL|int $id if not NULL, specifies the id of the wanted judge
 * @param NULL|bool $online only return online/offline judges if it is bool
 * @return array array of Judge
 */
function judge_get_list($id = NULL, $online = NULL)
{
	global $db, $DBOP;
	$where = _judge_make_where($online);
	if (is_int($id))
		db_where_add_and($where,array($DBOP['='],'id',$id));
	$judge_list = $db->select_from('judges', NULL, $where);

	$ret = array();
	$detail_req = unserialize(option_get('judge_info_list'));
	foreach ($judge_list as $row)
	{
		$row['lang_sup'] = unserialize($row['lang_sup']);
		$detail = unserialize($row['detail']);
		$row['detail'] = array();
		foreach ($detail_req as $r)
			$row['detail'][$r] = $detail[$r];
		$judge = new Judge();
		foreach(get_class_vars(get_class($judge)) as $key => $val)
		{
			$judge->$key = $row[$key];
		}
		$ret[] = $judge;
	}
	return $ret;
}

/**
 * get the number of judges
 * @param NULL|bool $online only return online/offline judges if it is bool
 * @return int
 */
function judge_get_amount($online)
{
	global $db;
	return $db->get_number_of_rows('judges', _judge_make_where($online));
}

/**
 * remove a judge
 * @param int $id judge id
 * @return void
 */
function judge_remove($id)
{
	global $db, $DBOP;
	$db->delete_item('judges', array($DBOP['='], 'id', $id));
}

/**
 * get judge name by id
 * @param int $jid judge id
 * @return string|NULL judge name or NULL if no such judge
 */
function judge_get_name_by_id($jid)
{
	if (!$jid)
		return NULL;
	static $cache = array();
	if (array_key_exists($jid, $cache))
		return $cache[$jid];
	global $db, $DBOP;
	$row = $db->select_from('judges', 'name', array(
		$DBOP['='], 'id', $jid));
	if (count($row) != 1)
		return $cache[$jid] = NULL;
	return $cache[$jid] = $row[0]['name'];
}

/**
 * get judge id by name
 * @param string $name judge name
 * @return int|NULL judge id, or NULL if no such judge
 */
function judge_get_id_by_name($name)
{
	global $db, $DBOP;
	$row = $db->select_from('judges', 'id', array($DBOP['=s'], 'name', $name));
	if (count($row) != 1)
		return NULL;
	return $row[0]['id'];
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
function _judge_set_status($id, $status, $success_filter)
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
	_judge_set_status($id, JUDGE_STATUS_ONLINE, 'after_set_judge_online');
}

/**
 * set judge status to offline
 * @param int $id
 * @return void
 */
function judge_set_offline($id)
{
	_judge_set_status($id, JUDGE_STATUS_OFFLINE, 'after_set_judge_offline');
}

/**
 * set all judge status to be offline
 * @return void
 */
function judge_set_offline_all()
{
	global $db;
	$db->update_data('judges', array('status' => JUDGE_STATUS_OFFLINE));
}

