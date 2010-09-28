<?php
/* 
 * $File: user.php
 * $Date: Tue Sep 28 17:42:02 2010 +0800
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

require_once $includs_path . 'const.inc.php';

/**
 * @ignore
 */
class _User
{
	var $id, $username, $realname,
		$avatar, // avatar file name
		$email, $self_desc, $tid,
		$view_gid, // array of gid who can view the user's source
		$reg_time, $reg_ip, $plang, $wlang,
		$groups, // array of id of groups that the user blongs to
		$is_admin; // whether the user is an administrator
}

$user = NULL;
/**
 * check user login and initialize $user structure
 * @global $user
 * @return bool whether login successfully
 */
function user_check_login()
{
	global $user, $db, $DBOP;
	if ($user)
		return TRUE;
	$user = new _User();
	if (isset($_GET['login']))
	{
		if (!isset($_POST['username']) || !isset($_POST['password']))
			return FALSE;
		strip_magic_quotes('username', 'password');
		$user->username = $_POST['username'];
		if (!user_check_name($user->username))
			return FALSE;

		$row = $db->select_from('users', NULL,
			array($DBOP['=s'], 'username', $user->username));
	}
}

/**
 * check whether the user name is a valid one
 * @param string $name user name
 * @return bool
 */
function user_check_name($name)
{
	if (strlen($name) > USERNAME_LEN_MAX)
		return false;
}

/**
 * encrypt the password
 * @param string $passwd original password
 * @return string encrypted password
 */
function user_make_passwd($passwd)
{
	return sha1(md5($passwd) . $passwd . sha1($passwd));
}

