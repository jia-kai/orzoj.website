<?php
/*
 * $File: problem.php
 * $Date: Fri May 28 22:16:51 2010 +0800
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
 * @param string $description Description of the problem
 * @param string $inputformat Input Format of the problem
 * @param string $outputformat Output Format of the problem
 * @param string $sampleinput The Sample Input
 * @param string $sampleoutput The Sample Output
 * @param string $source The source of this problem
 * @param string $hint Hints
 * @param int $difficulty the difficulty $difficulty / 100
 * @param int $contestid id of the contest
 * @param int $dataid id of the data
 * @param int $type type of the problem
 * @param int $problemgroup group of the problem
 * @param bool $usefile use file I/O or not
 * @param string $inputfile if file I/O,it's the input file's name
 * @param string $outputfile If file I/O,it't the output file's name
 * @return bool on success,TRUE or new problem's id is returned.Otherwise,false is returned and $errormsg is set.
 */
function problem_add($title,$description,$inputformat,$outputformat,$sampleinput,$sampleoutput,$source,$hint,
	$difficulty,$contestid,$dataid,$type,$problemgroup,$usefile,$inputfile,$outputfile,$timelimit,$memorylimit,$otherinfo
	)
{
	$insert_data = array(
		'title' => $title,
		'description' => $description,
		'inputformat' => $inputformat,
		'outputformat' => $outputformat,
		'sampleinput' => $sampleinput,
		'sampleoutput' => $sampleoutput,
		'source' => $source,
		'hint' => $hint,
		'difficulty' => (int)($difficulty),
		'contestid' => (int)($contestid),
		'dataid' => (int)($dataid),
		'typeid' => array($type),
		'problemgroupid' => array($problemgroup),
		'usefile' => (((int)$usefile > 0)?true:false),
		'inputfile' => $inputfile,
		'outputfile' => $outputfile,
		'timelimit' => $timelimit,
		'memorylimit' => $memorylimit,
		'otherinfo' => serialize($otherinfo)
		);
	global $db,$tablepre;
	if (($insert_id = $db->insert_into($tablepre.'problems',$insert_data)) !== FALSE)
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

function problem_edit($id,$title,$description,$inputformat,$outputformat,$sampleinput,$sampleoutput,$source,$hint,
	$difficulty,$contestid,$dataid,$type,$problemgroup,$usefile,$inputfile,$outputfile,$timelimit,$memorylimit
)
{
	$new_data= array(
		'title' => $title,
		'description' => $description,
		'inputformat' => $inputformat,
		'outputformat' => $outputformat,
		'sampleinput' => $sampleinput,
		'sampleoutput' => $sampleoutput,
		'source' => $source,
		'hint' => $hint,
		'difficulty' => (int)($difficulty),
		'contestid' => (int)($contestid),
		'dataid' => (int)($dataid),
		'typeid' => array($type),
		'problemgroupid' => array($problemgroup),
		'usefile' => (((int)$usefile > 0)?true:false),
		'inputfile' => $inputfile,
		'outputfile' => $outputfile,
		'timelimit' => $timelimit,
		'memorylimit' => $memorylimit,
		'otherinfo' => serialize($otherinfo)
	);
	global $db,$tablepre;
	$wclause = array('param1' => 'id','op1' => 'int_eq','param2' => $id);
	if ($db->update_data($tablepre.'problems',$new_data,$wclause) !== FALSE)
		return true;
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		return false;
	}
}

function problem_delete($id)
{
	global $db,$tablepre;
	$wclause = array('param1' => 'id','op1' => 'int_eq','param2' => $id);
	if ($db->delete_item($tablepre.'problems',$wclause) !== FALSE)
		return true;
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		return false;
	}
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
	$wclause = array('param1' => 'id','op1' => 'int_eq','param2' => (int)($groupid));
	if ($db->delete_item($tablepre.'problemgroups',$wclause) === FALSE)
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		return false;
	}
	else
		return true;
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
	$wclause = array('param1' =>'id','op1'=> 'int_eq','param2' => (int)($typeid));
	if ($db->delete_item($tablepre.'problemtypes',$wclause) !== FALSE)
		return true;
	else
	{
		error_set_message(sprintf(__('SQL Error : %s'),$db->error()));
		return false;
	}
}

