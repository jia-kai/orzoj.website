<?php
/* 
 * $File: contest.php
 * $Date: Wed Jun 23 23:08:26 2010 +0800
 * $Author: Fan Qijiang <fqj1994@gmail.com>
 */
/**
 * @package orzoj-phpwebsite
 * @license http://gnu.org/licenses GNU GPLv3
 * @version phpweb-1.0.0alpha1
 * @copyright (C) Fan Qijiang
 * @author Fan Qijiang <fqj1994@gmail.com>
 */
/*
	Orz Online Judge is a cross-platform programming online judge.
	Copyright (C) <2010>  (Fan Qijiang) <fqj1994@gmail.com>

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

/**
 * Create a contest
 * XXX
 * @param string $name contest's name
 * @param int $rule rule id
 * @param int $start_time beginning time
 * @param int $end_time end  time
 * @param string $description description of contest
 * @param array $judge_server available judge server for this content
 * @param int $public Whether the contest and its problems are public.If $public = 1,it's completely public.If $public=2,it's not public.If $public = 3,it will switch to public after the contest. 
 * @param array $auth if $public != 1 ,auth info is requried.
 */
function contest_create($name,$rule,$start_time,$end_time,$description,$judge_server,$public,$auth)
{
	$newdata = array(
		'name' => $name,
		'rule' => $rule,
		'starttime' => $start_time,
		'endtime' => $end_time,
		'description' => $description,
		'judgeserver' => addslashes($judge_server),
		'public' =>  (int)($public),
		'auth' => addslashes($auth)
		);
}

/**
 * Delete a contest
 * @param int id of the contest that you want to delete
 * @return bool on success,TRUE is returned.Otherwise,False is returned.
 */
function contest_delete($id)
{
	global $db,$tablepre;
	$db->transaction_begin();
	$wclause = array('param1' => 'id','op1' => 'int_eq','param2' => $id);
	if ($db->delete_item($tablepre.'contests',$wclause) !== FALSE)
	{
		$w2 = array('param1' => 'contestid','op1' => 'int_eq','param2' => $id);
		if ($db->delete_item($tablepre.'problem_contest_relationships',$wclause) !== FALSE)
		{
			$db->transaction_commit();
			return true;
		}
		else
		{
			error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
			$db->transaction_rollback();
			return false;
		}
	}
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		$db->transaction_rollback();
		return false;
	}
}


/**
 * Create a rule
 * @param string $rulename New name of the rule
 * @param int $whentojudge 1=after the competition;2=after submitting
 * @param array $sortby
 * @return bool|int on success,TRUE or insert id.Otherwise,false is returned.
 */
function rule_create($rulename,$whentojudge = 1,$sortby)
{
	global $db,$tablepre;
	$newdata = array(
		'whentojudge' => (int)($whentojudge),
		'sortby' => $sortby
		);
	if (($insert_id = $db->insert_into($tablepre.'rules',
		array('rulename' => $rulename,'ruledetail' => addslashes($newdata))
	)) !== false)
	{
		if ($insert_id > 0)
			return $insert_id;
		else
			return true;
	}
	else
		return false;
}



/**
 * Edit a rule
 * @param id $ruleid ID of the rule you want to edit
 * @param string $rulename new name of the rule
 * @param int $whentojudge new judge time @see rule_create
 * @param array $sortby Sort order
 * @return bool on success,TRUE.Othersie,false is returned.
 */
function rule_edit($ruleid,$rulename,$whentojudge,$sortby)
{
	global $db,$tablepre;
	$newdata = array(
		'whentojudge' => (int)($whentojudge),
		'sortby' => $sortby
	);
	$wclause = array('param1' => 'id','op1' => 'int_eq','param2' => $ruleid);
	if ($db->update_data($tablepre.'rules',array('rulename' => $rulename,'ruledetail' => addslashes($newdata))) !== false)
		return true;
	else
		return false;
}



function rule_delete($ruleid)
{
	global $db,$tablepre;
	$db->transaction_begin();
	$w1 = array('param1' => 'ruleid','op1' => 'int_eq','param2' => $ruleid);
	$wclause = array('param1' => 'id','op1' => 'int_eq','param2' => $ruleid);
	if ($db->delete_item($tablepre.'rules',$wclause) !== false)
		return true;
	else
		return false;
}


