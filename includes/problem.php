<?php
/*
 * $File: problem.php
 * $Date: Wed Sep 22 16:34:26 2010 +0800
 * $Author: Fan Qijiang <fqj1994@gmail.com>
 */
/**
 * @package orzoj-phpwebsite
 * @copyright (c) Fan Qijiang
 * @version phpweb-1.0.0alpha1
 * @author Fan Qijiang <fqj1994@gmail.com>
 * @license http://gnu.org/licenses/ GNU GPLv3
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

require_once "l10n.php";
require_once "error.php";


/**
 * Add Problem
 * @param string $title Title of the problem
 * @param string $slug URL Friendly name of the problem
 * @param string $unique_code unique code to match problem and test-data. 
 * @param string $description Description of the problem
 * @param int $difficulty the difficulty $difficulty / 100
 * @param array(int) $contestid id of the contest
 * @param array(int) $type type of the problem
 * @param array(int) $problemgroup group of the problem
 * @param bool $usefile use file I/O or not
 * @param string $inputfile if file I/O,it's the input file's name
 * @param string $outputfile If file I/O,it't the output file's name
 * @return bool on success,TRUE or new problem's id is returned.Otherwise,false is returned and $errormsg is set.
 */
function problem_add($title,$slug,$unique_code,$description,$difficulty,$contestid,
	$type,$problemgroup,
	$usefile,$inputfile,$outputfile,$timelimit,$memorylimit,$publishtime)
{
	global $db,$tablepre;
	$db->transaction_begin();
	$data = array(
		'title' => $title,
		'slug' => $slug,
		'code' => $unique_code,
		'description' => $description,
		'difficulty' => $difficulty,
		'usefile' => $usefile,
		'inputfile' => $inputfile,
		'outputfile' => $outputfile,
		'timelimit' => $timelimit,
		'memorylimit' => $memorylimit,
		'publishtime' => ($publishtime > 0 ? $publishtime : time())
		);
	if (($insert_id = $db->insert_into($tablepre.'problems',$data)) !== FALSE)
	{
		if ($insert_id == 0)
		{
			error_set_message(sprintf(__('Can\'t fetch the ID of new problem.')));
			$db->transaction_rollback();
			return FALSE;
		}
		else
		{
			
			$success = apply_filters('after_add_problem',true,$insert_id);
			if ($success)
			{
				$db->transaction_commit();
				return TRUE;
			}
			else
			{
				$db->transaction_rollback();
				return FALSE;
			}
		}
	}
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		$db->transaction_rollback();
		return FALSE;
	}
	$db->transaction_commit();
}

function problem_edit($id,$title,$slug,$description,$difficulty,$usefile,$inputfile,$outputfile,$timelimit,$memorylimit,$publishtime)
{

	global $db,$tablepre;
	$db->transaction_begin();
	$data = array(
		'title' => $title,
		'slug' => $slug,
		'description' => $description,
		'difficulty' => $difficulty,
		'usefile' => $usefile,
		'inputfile' => $inputfile,
		'outputfile' => $outputfile,
		'timelimit' => $timelimit,
		'memorylimit' => $memorylimit,
		'publishtime' => ($publishtime > 0 ? $publishtime : time())
	);
	$wclause = array('param1' => 'id','op1' => 'int_eq','param2' => $id);
	$insert_id = $id;
	if ($db->update_data($tablepre.'problems',$data,$wclause) !== FALSE)
	{
		$success = apply_filters('after_add_problem',true,$insert_id);
		if ($success)
		{
			$db->transaction_commit();
			return TRUE;
		}
		else
		{
			$db->transaction_rollback();
			return FALSE;
		}
	}
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		$db->transaction_rollback();
		return FALSE;
	}
	$db->transaction_commit();
}

function problem_delete($id)
{
	global $db,$tablepre;
	$db->transaction_begin();
	$wclause = array('param1' => 'id','op1' => 'int_eq','param2' => $id);
	if ($db->delete_item($tablepre.'problems',$wclause) !== FALSE)
	{
		$w2 = array('param1' => 'problemid','op1' => 'int_eq','param2' => $id);
		if ($db->delete_item($tablepre.'problem_contest_relationships',$w2) !== FALSE)
		{
			if ($db->delete_item($tablepre.'problem_problemgroup_relationships',$w2) !== FALSE)
			{
				if ($db->delete_item($tablepre.'problem_problemtype_relationships',$w2) !== FALSE)
				{
					$successful = apply_filters('after_deleting_problem',true,$id);
					if ($successful)
					{
						$db->transaction_commit();
						return true;
					}
					else
					{
						$db->transaction_rollback();
						return false;
					}
				}
				else
					error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
			}
			else
				error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
			$db->transaction_rollback();

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

function problem_search_by_id($id)
{
	global $db,$tablepre;
	$wclause = array('param1' => 'id','op1' => 'int_eq','param2' => $id);
	$content = $db->select_from($tablepre.'problems',NULL,$wclause);
	if (count($content) > 0)
	{
		if (isset($content[0]['otherinfo']))
		{
			$content[0]['otherinfo'] = unserialize($content[0]['otherinfo']);
		}
		return $content[0];
	}
	else
		return false;
}


function problem_search_by_slug($slug)
{
	global $db,$tablepre;
	$wclause = array('param1' => 'slug','op1' => 'int_eq','param2' => $slug);
	$content = $db->select_from($tablepre.'problems',NULL,$wclause);
	if (count($content) > 0)
	{
		if (isset($content[0]['otherinfo']))
		{
			$content[0]['otherinfo'] = unserialize($content[0]['otherinfo']);
		}
		return $content[0];
	}
	else
		return false;

}


function problem_search_by_problemgroup($groupid)
{
	global $db,$tablepre;
	$db->directquery =false;
	$sub_query = $db->select_from($tablepre.'problem_problemgroup_relationships','problemid',array('param1' => 'problemgroupid','op1' => 'int_eq','param2' => $groupid));
	$db->directquery = true;
	return $db->select_from($tablepre.'problems',NULL,array('param1' => 'problemid','op1' => 'subquery_in','param2' => $sub_query[0]));
}

function problem_search_by_contest($contestid)
{
	global $db,$tablepre;
	$db->directquery =false;
	$sub_query = $db->select_from($tablepre.'problem_contest_relationships','problemid',array('param1' => 'contestid','op1' => 'int_eq','param2' => $groupid));
	$db->directquery = true;
	return $db->select_from($tablepre.'problems',NULL,array('param1' => 'problemid','op1' => 'subquery_in','param2' => $sub_query[0]));

}

function problem_search_by_problemtype($typeid)
{
	global $db,$tablepre;
	$db->directquery =false;
	$sub_query = $db->select_from($tablepre.'problem_problemtype_relationships','problemid',array('param1' => 'problemtypeid','op1' => 'int_eq','param2' => $groupid));
	$db->directquery = true;
	return $db->select_from($tablepre.'problems',NULL,array('param1' => 'problemid','op1' => 'subquery_in','param2' => $sub_query[0]));
}

/**
 * Add problem group
 * @param string $groupname name of problem group
 * @param int $parent id of parent group
 * @return int|bool on success,new group's id or true is returned.Otherwise,false is returned.
 */
function problem_group_add($groupname,$parent = 0)
{
	$insert_data = array(
		'groupname' => $groupname,
		'parent' => $parent
	);
	global $db,$tablepre;
	if (($insert_id = $db->insert_into($tablepre.'problemgroups',$insert_data)) !== FALSE)
	{
		if ($insert_id !== TRUE)
			return $insert_id;
		else
			return true;
	}
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		return false;
	}
}


function problem_group_edit($groupid,$groupname,$parent = 0)
{
	$insert_data = array(
		'groupname' => $groupname,
		'parent' => $parent
	);
	global $db,$tablepre;
	if (($affected_rows = $db->update_data($tablepre.'problemgroups',$insert_data,array('param1' => 'id','op1' => 'int_eq','param2' => (int)($groupid)))) !== FALSE)
		return true;
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		return false;
	}
}


function problem_group_delete($groupid)
{
	global $db,$tablepre;
	$db->transaction_begin();
	$wclause = array('param1' => 'id','op1' => 'int_eq','param2' => (int)($groupid));
	if ($db->delete_item($tablepre.'problemgroups',$wclause) === FALSE)
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		$db->transaction_rollback();
		return false;
	}
	else
	{
		$w2 = array('param1' => 'problemgroupid','op1' => 'int_eq','param2' => (int)($groupid));
		if ($db->delete_item($tablepre.'problem_problemgroup_relationships',$w2) === false)
		{
			error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
			$db->transaction_rollback();
			return false;
		}
		$db->transaction_commit();
		return true;
	}
}


function problem_type_add($typename)
{
	global $db,$tablepre;
	$ndt = array(
		'typename' => $typename
	);
	if (($insert_id = $db->insert_into($db.'problemtypes',$ndt)) !== FALSE)
	{
		if ($insert_id !== TRUE)
			return $insert_id;
		else
			return TRUE;
	}
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		return FALSE;
	}
}

function problem_type_edit($typeid,$typename)
{
	global $db,$tablepre;
	$ndt = array(
		'typename' => $typename
	);
	$wclause = array('param1' =>'id','op1'=> 'int_eq','param2' => (int)($typeid));
	if ($db->update_data($tablepre.'problemtypes',$ndt,$wclause) !== FALSE)
		return true;
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		return false;
	}
}


function problem_type_delete($typeid)
{
	global $db,$tablepre;
	$db->transaction_begin();
	$wclause = array('param1' =>'id','op1'=> 'int_eq','param2' => (int)($typeid));
	if ($db->delete_item($tablepre.'problemtypes',$wclause) !== FALSE)
	{
		$w2 = array('param1' => 'problemtypeid','op1' => 'int_eq','param2' => (int)($typeid));
		if ($db->delete_item($tablepre.'problem_problemtype_relationships',$w2) === false)
		{
			error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
			$db->transaction_rollback();
			return false;
		}
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

function problem_gen_html($id,$plain,$cache = FALSE)
{
	return $plain;
}

