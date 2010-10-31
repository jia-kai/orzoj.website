<?php
/* 
 * $File: avatar.php
 * $Date: Sun Oct 31 18:31:55 2010 +0800
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

/**
 * get the URL of an avatar, or the default avatar if the id is invalid
 * @param int $id avatar id
 * @return string
 */
function avatar_get_url($id)
{
	global $db, $DBOP;
	static $avatar_cache = array();
	if (isset($avatar_cache[$id]))
		return $avatar_cache[$id];
	$row = $db->select_from('user_avatars', 'file',
		array($DBOP['='], 'id', $id));
	if (count($row) != 1)
		$row = 'default.gif';
	else $row = $row[0]['file'];
	return $avatar_cache[$id] = avatar_get_url_by_file($row);
}

/**
 * get the URL of an avatar
 * @param string $file file name
 * @return string
 */
function avatar_get_url_by_file($file)
{
	global $website_root;
	return $website_root . 'contents/uploads/user_avatars/' . $file;
}

/**
 * get the amount of avatars
 * @return int
 */
function avatar_get_amount()
{
	global $db;
	return $db->get_number_of_rows('user_avatars');
}

/**
 * get avatar list
 * @param int|NULL $offset
 * @param int|NULL $cnt
 * @return array array of array('id' => <avatar id>, 'file' => <file name>)
 */
function avatar_list($offset = NULL, $cnt = NULL)
{
	global $db;
	return $db->select_from('user_avatars', NULL, NULL, array('id' => 'ASC'),
		$offset, $cnt);
}

/**
 * get avatar url by user id
 * @param int $uid user id
 * @return string the url
 */
function avatar_get_url_by_user_id($uid)
{
	global $db, $DBOP;
	static $cache;
	if (isset($cache[$uid]))
		return $cache[$uid];
	$aid = $db->select_from('users', 'aid', array($DBOP['='], 'id', $uid));
	if (count($aid) != 1)
		return avatar_get_url_by_file('default.gif');
	$aid = $aid[0]['aid'];
	return $cache[$uid] = avatar_get_url($aid);
}

