<?php
/* 
 * $File: judge.php
 * $Date: Tue Sep 28 14:15:22 2010 +0800
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

if (!defined('IN_ORZOJ')) exit;

define('JUDGE_STATUS_ONLINE',1);
define('JUDGE_STATUS_OFFLINE',0);
define('JUDGE_STATUS_RUNNING',2);


function judge_search_by_name($name)
{
	global $db;
	$condition = array($DBOP['=s'], 'name', $name);
	$rt = $db->select_from('judges',NULL,$condition);
	if (is_array($rt) && count($rt) > 0)
		return $rt;
	else
		throw new Exc_orzoj('judge_search_by_name failure.');
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
	try
	{
		apply_filters('after_add_judge',true,$insert_id);
	}
	catch (Exc_orzoj $e)
	{
		$db->transaction_rollback();
		return $e;
	}
	$db->transaction_commit();
	return $insert_id;
}


function judge_update($id,$name,$lang_sup,$query_ans)
{
	global $db;
	$condition = array($DBOP['='], 'id', $id);
	$content = array(
		'name' => $name,
		'lang_sup' => serialize($lang_sup),
		'detail' => serialize($query_ans)
	);
	$db->transaction_begin();
	try
	{
		$db->update_data('judges',$content,$condition);
		apply_filters('after_add_judge',true,$id);
		$db->transaction_commit();
		return $id;
	}
	catch (Exc_orzoj $e)
	{
		$db->transaction_rollback();
		throw $e;
	}

}


function judge_set_status($id,$status,$success_filter)
{	
	global $db;
	$condition = array($DBOP['='], 'id', $id);
	$content = array('status' => $status);
	$db->update_data('judges',$content,$condition);
	apply_filters($success_filter, TRUE, $id);
}

function judge_online($id)
{
	judge_set_status($id,JUDGE_STATUS_ONLINE,'after_judge_online');
}


function judge_offline($id)
{
	judge_set_status($id,JUDGE_STATUS_OFFLINE,'after_judge_offline');
}

function judge_running($id)
{
	judge_set_status($id,JUDGE_STATUS_RUNNING,'after_judge_running');
}

