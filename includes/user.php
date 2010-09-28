<?php
/* 
 * $File: user.php
 * $Date: Tue Sep 28 20:34:24 2010 +0800
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
		$avatar, // avatar file name, NULL if unavailable
		$email, $self_desc, $tid,
		$view_gid, // array of gid who can view the user's source
		$reg_time, $reg_ip, $plang, $wlang,
		$groups, // array of id of groups that the user blongs to
		$admin_groups, // array of id of groups where the user is an administrator
		$is_admin; // whether the user is an administrator
}

$user = NULL;
/**
 * check user login and initialize $user structure
 * @global $user
 * @param int $cookie_time see cookie_set() in functions.php
 * @return bool whether login successfully
 */
function user_check_login($cookie_time = NULL)
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
		if (count($row) != 1)
			return FALSE;

		$row = $row[0];
		$pwd_chk = user_check_passwd($_POST['password'], $row['passwd']);
		if ($pwd_chk === FALSE)
			return FALSE;
		if (is_string($pwd_chk))
			$db->update_data('users', array('passwd' => $pwd_chk),
				array($DBOP['='], 'id', $row['id']));

		$salt = user_make_salt();

		$db->update_data('users', array('salt' => $salt),
			array($DBOP['='], 'id', $row['id']));

		cookie_set('uid', $row['id'], $cookie_time);
		cookie_set('password', user_make_passwd($salt . $pwd_chk),
			$cookie_time);
	}
	else
	{
		$uid = intval(cookie_get('uid'));
		$password = cookie_get('password');
		$row = $db->select_from('users', NULL, array($DBOP['='], 'id', $uid));
		if (count($row) != 1)
			return FALSE;
		$row = $row[0];

		if ($password != user_make_passwd($row['salt'] . $row['passwd']))
			return FALSE;
	}

	$VAL_SET = array('id', 'username', 'realname', 'email', 'self_desc', 'tid',
		'reg_time', 'reg_ip', 'plang', 'wlang');
	foreach ($VAL_SET as $val)
		$user->$val = $row[$val];

	$tmp = $db->select_from('user_avatars', 'file', array($DBAL['='], 'id', $row['aid']));
	if (count($tmp) != 1)
		$user->avatar = NULL;
	else
		$user->avatar = $tmp[0]['file'];

	$user->view_gid = unserialize($row['view_gid']);

	$tmp = $db->select_from('map_user_group', array('gid', 'admin'),
		array($DBAL['&&'], $DBAL['='], 'uid', $uid, $DBAL['='], 'pending', 0));
	$groups = array();
	$user->admin_groups = array();

	$user->groups = array();
	$user->is_admin = FALSE;

	for ($tmp as $val)
	{
		$groups[] = $val['gid'];
		if ($val['admin'])
			$user->admin_groups[] = $val['gid'];
		$user->groups[$val] = NULL;

		if ($val == GID_ADMIN)
			$user->is_admin = TRUE;
	}

	for ($i = 0; $i < count($groups); $i ++)
	{
		$tmp = $db->select_from('user_groups', 'pgid',
			array($DBOP['='], 'id', $groups[$i]));
		if (count($tmp) != 1)
			continue;
		$tmp = $tmp[0]['pgid'];
		if (!isset($user->groups[$tmp]))
		{
			array_push($groups, $tmp);
			$user->groups[$tmp] = NULL;
		}
	}

	return TRUE;
}

/**
 * clean cookies
 * @return void
 */
function user_logout()
{
	cookie_set('uid', NULL, -1);
	cookie_set('password', NULL, -1);
}

define('PASSWD_ENCRYPTION_VERSION', '01');

/**
 * check user password
 * @param string $passwd plain password
 * @param string $passwd_encr encrypted password read from database
 * @return bool|string return the new password as string if password in the database needs updating
 */
function user_check_passwd($passwd, $passwd_encr)
{
	$pos = strpos($passwd_encr, ':');
	$version = substr($passwd_encr, 0, $pos);
	$func = 'user_make_passwd_v' . $version;
	$ret = $func($passwd);
	if ($ret != substr($passwd_encr, $pos + 1))
		return FALSE;
	if ($version != PASSWD_ENCRYPTION_VERSION)
		return user_make_passwd($passwd);
	return TRUE;
}

/**
 * check whether the user name is a valid one
 * @param string $name user name
 * @return bool
 */
function user_check_name($name)
{
	if (strlen($name) > USERNAME_LEN_MAX)
		return FALSE;
}

/**
 * @ignore
 */
function user_make_passwd_v1($passwd)
{
	return sha1(md5($passwd) . $passwd . sha1($passwd));
}

/**
 * encrypt the password
 * @param string $passwd original password
 * @return string encrypted password
 */
function user_make_passwd($passwd)
{
	$func = 'user_make_passwd_v' . PASSWD_ENCRYPTION_VERSION;
	return PASSWD_ENCRYPTION_VERSION . ':' . $func($passwd);
}

/**
 * return a highly random string
 * @return string
 */
function user_make_salt()
{
	return uniqid(mt_rand(), TRUE);
}

