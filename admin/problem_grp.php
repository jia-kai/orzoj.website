<?php
/*
 * $File: problem_grp.php
 * $Date: Thu Nov 04 20:02:56 2010 +0800
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

require_once $admin_path . 'group.php';
require_once $includes_path . 'problem.php';

class Prob_grp_admin extends Grp_admin
{
	function gid_selector_type()
	{
		return 1;
	}

	function form_get_gid_select($prompt, $post_name, $default = NULL)
	{
		$id = get_unique_id();
		echo "<label for='$id'>$prompt</label> ";
		echo "<select id='$id' name='$post_name'>";
		if (is_null($default))
			$default = -1;
		foreach (make_pgid_select_opt(__('None')) as $opt)
		{
			echo "<option value='$opt[1]'";
			if ($opt[1] == $default)
				echo ' selected="selected" ';
			echo ">$opt[0]</option>";
		}
		echo '</select>';
	}

	function get_info($gid)
	{
		global $db, $DBOP;
		$row = $db->select_from('prob_grps', NULL, array($DBOP['='], 'id', $gid));
		if (empty($row))
			throw new Exc_runtime(__('no such problem group #%d', $gid));
		return $row[0];
	}

	function get_all_children($gid)
	{
		global $db, $DBOP;
		$rows = $db->select_from('cache_pgrp_child', 'chid', array($DBOP['='], 'gid', $gid));
		$ret = array();
		foreach ($rows as $row)
			$ret[] = intval($row['chid']);
		return $ret;
	}

	function validate_info(&$info)
	{
		prob_validate_grp_name($info['name']);
		$info['desc'] = htmlencode($info['desc']);
	}

	function update_cache_add($gid)
	{
		prob_update_grp_cache_add($gid);
	}

	function update_cache_delete($gid)
	{
		prob_update_grp_cache_delete($gid);
	}

	function update_db($gid, $info)
	{
		global $db, $DBOP;
		$db->update_data('prob_grps', $info, array(
			$DBOP['='], 'id', $gid));
	}

	function new_grp($info)
	{
		if (prob_grp_get_id_by_name($info['name']))
			throw new Exc_runtime(__('problem group name %s already exists', $info['name']));
		global $db;
		$db->transaction_begin();
		$gid = $db->insert_into('prob_grps', $info);
		prob_update_grp_cache_add($gid);
		$db->transaction_commit();
	}

	function del_grp($gid)
	{
		global $db, $DBOP;
		$db->transaction_begin();
		$db->delete_item('prob_grps', array($DBOP['='], 'id', $gid));
		prob_update_grp_cache_delete($gid);
		$db->transaction_commit();
	}
}

$c = new Prob_grp_admin();
$c->work();

