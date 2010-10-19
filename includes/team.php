<?php
/* 
 * $File: team.php
 * $Date: Tue Oct 19 17:35:45 2010 +0800
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

$_team_img_dir = $root_path . 'contents/uploads/team_image/';

/**
 * user team structure
 */
class Team
{
	var $id, $name, $desc,
		$img; // image file URL, or NULL if not found

	/**
	 * set $this->desc according to $this->id
	 * @return void
	 * @exception Exc_inner if $this->id is invalid
	 */
	function set_desc()
	{
		if (is_string($this->desc))
			return;
		global $db, $DBOP;
		$row = $db->select_from('user_teams', 'desc',
			array($DBOP['='], 'id', $this->id));
		if (count($row) != 1)
			throw new Exc_inner(__('No such team #%d', $this->id));
		$this->desc = $row[0]['desc'];
	}

	/**
	 * set id, name and desc
	 * @param int $id team id
	 * @return bool whether successful
	 */
	function set_val($id)
	{
		$this->id = $id;
		global $db, $DBOP, $_team_img_dir;
		$row = $db->select_from('user_teams', NULL, array(
			$DBOP['='], 'id', $id));
		if (count($row) != 1)
			return FALSE;
		$row = $row[0];
		$row['img'] = get_page_url($_team_img_dir . $row['img']);
		$this->name = $row['name'];
		$this->desc = $row['desc'];
		return TRUE;
	}
}

/**
 * get team name suggest
 * @param string $begin the beginning of the name
 * @return array array of class Team without 'desc' field set
 */
function team_get_name_suggest($begin)
{
	global $db, $DBOP, $_team_img_dir;
	$rows = $db->select_from('user_teams', array('id', 'name', 'img'),
		array($DBOP['like'], 'name', $begin . '%'));
	$ret = array();
	foreach ($rows as $row)
	{
		$team = new Team();
		$team->id = $row['id'];
		$team->name = $row['name'];
		$team->img = get_page_url($_team_img_dir . $row['img']);
		$ret[] = $team;
	}
	return $ret;
}

/**
 * get the number of all user teams
 */
function team_get_amount()
{
	static $ret = NULL;
	if (!is_null($ret))
		return $ret;
	global $db;
	$ret = $db->get_number_of_rows('user_teams');
	return $ret;
}

/**
 * get team list
 * @param int|NULL $offset
 * @param int|NULL $cnt
 * @param bool $set_desc whether to set the 'desc' field in the result
 * @return array array of class Team
 */
function team_get_list($offset = NULL, $cnt = NULL, $set_desc = FALSE)
{
	global $db, $_team_img_dir;
	$fields = array('id', 'name', 'img');
	if ($set_desc)
		$fields[] = 'desc';
	$rows = $db->select_from('user', $fields, NULL, NULL, $offset, $cnt);
	$ret = array();
	foreach ($rows as $row)
	{
		$t = new Team();
		$row['img'] = get_page_url($_team_img_dir . $row['img']);
		foreach ($fields as $f)
			$t->$f = $row[$f];
		$ret[] = $t;
	}
	return $ret;
}

