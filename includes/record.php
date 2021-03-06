<?php
/* 
 * $File: record.php
 * $Date: Wed Dec 21 22:37:02 2011 +0800
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

$cnt = 0;

define('RECORD_STATUS_WAITING_TO_BE_FETCHED', $cnt ++);
define('RECORD_STATUS_WAITING_FOR_CONTEST', $cnt ++);
define('RECORD_STATUS_WAITING_ON_SERVER', $cnt ++);

define('RECORD_STATUS_JUDGE_BUSY', $cnt ++);

define('RECORD_STATUS_SYNC_DATA', $cnt ++);
define('RECORD_STATUS_COMPILING', $cnt ++);
define('RECORD_STATUS_COMPILE_SUCCESS', $cnt ++);
define('RECORD_STATUS_RUNNING', $cnt ++);

define('RECORD_STATUS_COMPILE_FAILURE', $cnt ++);

define('RECORD_STATUS_ACCEPTED',$cnt ++ );
define('RECORD_STATUS_WRONG_ANSWER', $cnt ++);
define('RECORD_STATUS_TIME_LIMIT_EXCEED', $cnt ++);
define('RECORD_STATUS_MEMORY_LIMIT_EXCEED', $cnt ++);
define('RECORD_STATUS_RUNTIME_ERROR', $cnt ++);
define('RECORD_STATUS_SYSTEM_ERROR', $cnt ++);
define('RECORD_STATUS_DATA_NOT_FOUND', $cnt ++);
define('RECORD_STATUS_ERROR', $cnt ++);
unset($cnt);

/**
 * test whether the source is executed
 * @param int $status
 * @return bool
 */
function record_status_executed($status)
{
	return $status >= RECORD_STATUS_ACCEPTED && $status <= RECORD_STATUS_SYSTEM_ERROR;
}

/**
 * test whether the judge process is finished
 * @param int $status
 * return bool
 */
function record_status_finished($status)
{
	return intval($status) > RECORD_STATUS_RUNNING;
}

/**
 * get the required where clause in queries for selecting records (must be anded with other clauses)
 * @return array
 */
function record_make_where()
{
	global $DBOP, $user;
	if (!user_check_login())
		return array($DBOP['!='], 'status', RECORD_STATUS_WAITING_FOR_CONTEST);
	if (!$user->is_grp_member(GID_SUPER_RECORD_VIEWER))
		return array($DBOP['||'],
		$DBOP['!='], 'status', RECORD_STATUS_WAITING_FOR_CONTEST,
		$DBOP['='], 'uid', $user->id);
}

/**
 * filter the records which are not allowed to be accessed
 * @param &array $rows the rows selected from database, which must contain columns 'cid', 'pid' and 'uid'
 * disallowed records will be set to NULL
 * @return void
 */
function record_filter_rows(&$rows)
{
	global $user;
	if (user_check_login() && $user->is_grp_member(GID_SUPER_RECORD_VIEWER))
		return;
	foreach ($rows as $key => $row)
	{
		$cid = intval($row['cid']);
		$fid = prob_future_contest($row['pid']);
		if ($fid)
		{
			if ($fid == $cid)
				ctal_filter_record($cid, $rows[$key]);
			else $rows[$key] = NULL;

		} else if ($cid)
			ctal_filter_record($cid, $rows[$key]);
	}
}

/**
 * get all record status in an array(<status number> => <description>)
 * @return string
 */
function &record_status_get_all()
{
	static $TEXT = NULL;
	if (is_null($TEXT))
	{
		$TEXT = array(
			RECORD_STATUS_WAITING_TO_BE_FETCHED => __('Waiting to be fetched'),
			RECORD_STATUS_WAITING_FOR_CONTEST => __('Waiting for contest'),
			RECORD_STATUS_WAITING_ON_SERVER => __('Waiting on orzoj-server'),

			RECORD_STATUS_SYNC_DATA => __('Synchronizing data'),
			RECORD_STATUS_COMPILING => __('Compiling'),
			RECORD_STATUS_COMPILE_SUCCESS => __('Succesfully compiled'),
			RECORD_STATUS_COMPILE_FAILURE => __('Compilation error'),
			RECORD_STATUS_RUNNING => __('Running'),
			RECORD_STATUS_ACCEPTED => __('Accepted'),
			RECORD_STATUS_WRONG_ANSWER => __('Wrong answer'),
			RECORD_STATUS_TIME_LIMIT_EXCEED => __('Time limit exceeded'),
			RECORD_STATUS_MEMORY_LIMIT_EXCEED => __('Memory limit exceeded'),
			RECORD_STATUS_RUNTIME_ERROR => __('Runtime error'),
			RECORD_STATUS_SYSTEM_ERROR => __('System error'),
			RECORD_STATUS_DATA_NOT_FOUND => __('Data not found'),
			RECORD_STATUS_JUDGE_BUSY => __('Judge is busy'),
			RECORD_STATUS_ERROR => __('Error')
		);
	}
	return $TEXT;
}

/**
 * convert record status to human readable text
 * @param int $status record status
 * @return string
 */
function record_status_get_str($status)
{
	$tmp = &record_status_get_all();
	return $tmp[intval($status)];
}

/**
 * whether viewing the source of a record is allowed
 * @param int $uid uid of the record
 * @param int $cid cid of the record
 * @return bool
 */
function record_allow_view_src($uid, $cid)
{
	global $user;
	if (user_check_login() && ($user->id == $uid || $user->is_grp_member(GID_SUPER_RECORD_VIEWER)))
		return TRUE;
	if ($cid)
	{
		$ct = ctal_get_class_by_cid($cid);
		return $ct->allow_view_src($uid);
	}
	return user_check_view_src_perm($uid);
}

/**
 * get the source by record id
 * NOTE: no permission check performed
 * @param int $rid  record id
 */
function record_get_src_by_rid($rid)
{
	global $db, $DBOP;
	$row = $db->select_from('sources', 'src', array(
		$DBOP['='], 'rid', $rid));
	if (count($row) == 1)
		return $row[0]['src'];
	throw new Exc_runtime(__('source not found (rid=%d)', $rid));
	//TODO: retrieve source from orzoj-server
}

