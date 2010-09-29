<?php
/* 
 * $File: user.php
 * $Date: Wed Sep 29 14:16:36 2010 +0800
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
 * user structure
 */
class User
{
	var $id, $username, $realname,
		$avatar, // avatar file name, NULL if unavailable
		$email, $self_desc, $tid, $plang, $wlang,
		$view_gid, // array of gid who can view the user's source
		$reg_time, $reg_ip, $last_login_time, $last_login_ip,
		$cnt_submit, $cnt_ac, $cnt_unac, $cnt_ce,
		$groups, // array of id of groups that the user blongs to
		$admin_groups, // array of id of groups where the user is an administrator
		$is_admin, // whether the user is an administrator
		$is_locked; // whether this user is in the lock group

	/**
	 * set attributes in this class
	 * @param int $uid user id
	 * @return void
	 * @exception Exc_inner if user id does not exist
	 */
	function set_val($uid)
	{
		global $db, $DBOP;
		$row = $db->select_from('users', NULL,
			array($DBOP['='], 'id', $uid));
		if (count($row) != 1)
			throw new Exc_inner(__('user id %d does not exist', $uid));
		$row = $row[0];

		$VAL_SET = array('id', 'username', 'realname',
			'email', 'self_desc', 'tid', 'plang', 'wlang',
			'reg_time', 'reg_ip', 'last_login_time', 'last_login_ip',
			'cnt_submit', 'cnt_ac', 'cnt_unac', 'cnt_ce');

		foreach ($VAL_SET as $val)
			$this->$val = $row[$val];

		$tmp = $db->select_from('user_avatars', 'file', array($DBOP['='], 'id', $row['aid']));
		if (count($tmp) != 1)
			$this->avatar = NULL;
		else
			$this->avatar = $tmp[0]['file'];

		$this->view_gid = unserialize($row['view_gid']);

		$tmp = $db->select_from('map_user_group', array('gid', 'admin'),
			array($DBOP['&&'], $DBOP['='], 'uid', $uid, $DBOP['='], 'pending', 0));

		$groups = array(GID_ALL);
		$this->admin_groups = array();

		$this->is_admin = FALSE;
		$this->is_locked = FALSE;

		$grp_set = array();

		foreach ($tmp as $val)
		{
			$groups[] = $val['gid'];
			if ($val['admin'])
				$this->admin_groups[] = $val['gid'];

			$grp_set[$val['gid']] = 1;

			if ($val['gid'] == GID_ADMIN)
				$this->is_admin = TRUE;

			if ($val['gid' == GID_LOCK])
				$this->is_locked = TRUE;
		}

		for ($i = 0; $i < count($groups); $i ++)
		{
			$tmp = $db->select_from('user_groups', 'pgid',
				array($DBOP['='], 'id', $groups[$i]));
			if (count($tmp) != 1)
				continue;
			$tmp = $tmp[0]['pgid'];
			if (!isset($grp_set[$tmp]))
			{
				array_push($groups, $tmp);
				$grp_set[$tmp] = 1;

				if ($tmp == GID_LOCK)
					$this->is_locked = TRUE;
			}
		}

		$this->groups = $groups;
	}
}

$user = NULL;
/**
 * check user login and initialize $user structure
 * @global User $user
 * @param int $cookie_time see cookie_set() in functions.php
 * @return bool whether login successfully
 */
function user_check_login($cookie_time = NULL)
{
	global $user, $db, $DBOP, $action;
	if ($user)
		return TRUE;
	if (isset($action) && $action == 'login')
	{
		if (!isset($_POST['username']) || !isset($_POST['password']))
			return FALSE;
		$_POST['password'] .= $_POST['username'];
		if (!user_check_name($_POST['username']))
			return FALSE;

		$row = $db->select_from('users', array('id', 'passwd'),
			array($DBOP['=s'], 'username', $_POST['username']));
		if (count($row) != 1)
			return FALSE;

		$row = $row[0];
		$pwd_chk = user_check_passwd($_POST['password'], $row['passwd']);
		if (!$pwd_chk)
			return FALSE;
		if ($pwd_chk != $row['passwd'])
			$db->update_data('users', array('passwd' => $pwd_chk),
				array($DBOP['='], 'id', $row['id']));

		$salt = user_make_salt();

		$db->update_data('users', array('salt' => $salt),
			array($DBOP['='], 'id', $row['id']));

		cookie_set('uid', $row['id'], $cookie_time);
		cookie_set('password', user_make_passwd($salt . $pwd_chk),
			$cookie_time);

		$uid = $row['id'];
	}
	else
	{
		$uid = intval(cookie_get('uid'));
		$password = cookie_get('password');
		if ($uid === FALSE || $password === FALSE)
			return FALSE;
		$row = $db->select_from('users', array('passwd', 'salt'),
			array($DBOP['='], 'id', $uid));
		if (count($row) != 1)
			return FALSE;
		$row = $row[0];

		if ($password != user_make_passwd($row['salt'] . $row['passwd']))
			return FALSE;

	}
	$db->update_data('users', array('last_login_time' => time(), 'last_login_ip' => get_remote_addr()),
		array($DBOP['='], 'id', $uid));

	$user = new User();
	$user->set_val($uid);
	return TRUE;
}

/**
 * clean cookies about user login
 * @return void
 */
function user_logout()
{
	global $user, $db, $DBOP;
	if ($user)
	{
		$db->update_data('users', array('salt' => user_make_salt()),
			array($DBOP['='], 'id', $user->id));
		$user = NULL;
	}
	cookie_set('uid', NULL, -1);
	cookie_set('password', NULL, -1);
}

define('PASSWD_ENCRYPTION_VERSION', '01');

/**
 * check user password
 * @param string $passwd plain password
 * @param string $passwd_encr encrypted password read from database
 * @return NULL|string return the newest password as string if checking successfully, or NULL otherwise
 */
function user_check_passwd($passwd, $passwd_encr)
{
	$pos = strpos($passwd_encr, ':');
	$version = substr($passwd_encr, 0, $pos);
	$func = 'user_make_passwd_v' . $version;
	$ret = $func($passwd);
	if ($ret != substr($passwd_encr, $pos + 1))
		return NULL;
	return user_make_passwd($passwd);
}

/**
 * check whether the user name is a valid one
 * @param string $name user name
 * @return bool
 */
function user_check_name($name)
{
	if (strlen($name) > USERNAME_LEN_MAX || strlen($name) < USERNAME_LEN_MIN)
		return FALSE;
	return count(preg_grep('#^[a-zA-Z][a-zA-Z0-9_.]*$#', array($name))) > 0;
}

/**
 * @ignore
 */
function user_make_passwd_v01($passwd)
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
 * return a highly unpredictable string
 * @return string
 */
function user_make_salt()
{
	return uniqid(mt_rand(), TRUE);
}

/**
 * get user id by user name
 * @param string $name user name
 * @return bool|int FALSE if such user does not exist, user id otherwise
 */
function user_get_id_by_name($name)
{
	global $db, $DBOP;
	$row = $db->select_from('users', 'id',
		array($DBOP['=s'], 'username', $name));
	if (count($row) == 1)
		return $row[0]['id'];
	return FALSE;
}

/**
 * add a user
 * @param array $value see /install/tables.php, the 'users' table
 * @param string $passwd plain password
 * @return int user id, or 0 if user name exists or invalid user name
 */
function user_add($value, $passwd)
{
	if (!user_check_name($value['username']))
		return 0;
	if (user_get_id_by_name($value['username']))
		return 0;
	$passwd .= $value['username'];
	global $db, $DBOP;
	$VAL_SET = array('username', 'realname', 'aid',
		'email', 'self_desc', 'tid', 'plang', 'wlang');
	$val = array();
	foreach ($VAL_SET as $v)
	{
		if (!isset($value[$v]))
			throw new Exc_inner(__('not enough parameters passed to user_add()'));
		$val[$v] = $value[$v];
	}
	$val['passwd'] = user_make_passwd($passwd);
	$val['view_gid'] = serialize(array());
	$val['reg_time'] = time();
	$val['reg_ip'] = get_remote_addr();

	return $db->insert_into('users', $val);
}

/**
 * delete a user and other information related to it. If the user does not exist, nothing happens
 * @param int $uid user id
 * @return void
 */
function user_del($uid)
{
	global $db, $DBOP;
	$where = array($DBOP['='], 'id', $uid);
	$db->delete_item('users', $where);

	$where[1] = 'uid';

	$db->delete_item('map_user_group', $where);
	$db->delete_item('records', $where);

	$db->delete_item('messages',
		array($DBOP['||'],
			$DBOP['='], 'uid_snd', $uid,
			$DBOP['='], 'uid_rcv', $uid));
}

/**
 * change user password
 * @param int $uid user id
 * @param string $oldpwd old password in plain text
 * @param string $newpwd new password in plain text
 * @return bool whether old password is correct
 * @exception Exc_inner if $uid does not exist
 */
function user_chpasswd($uid, $oldpwd, $newpwd)
{
	global $db, $DBOP;
	$where = array($DBOP['='], 'id', $uid);
	$row = $db->select_from('users', array('username', 'passwd'), $where);
	if (count($row) != 1)
		throw new Exc_inner(__('user_chpasswd: uid %d does not exist', $uid));

	$row = $row[0];
	$oldpwd .= $row['username'];
	$newpwd .= $row['username'];

	if (!user_check_passwd($oldpwd, $row['passwd']))
		return FALSE;

	$db->update_data('users', array('passwd' => user_make_passwd($newpwd)),
		$where);
}

/**
 * update user info. If such user does not exist, nothing happens
 * @param int $uid user id
 * @param array $value array of values to be updated, whose valid fields are in
 *			array('realname', 'aid', 'email', 'self_desc', 'tid', 'plang', 'wlang',
 *				'view_gid')
 * @return void
 */
function user_update_info($uid, $value)
{
	global $db, $DBOP;
	$VAL_SET = array('realname', 'aid', 'email', 'self_desc', 'tid', 'plang', 'wlang',
		'view_gid');

	$val = array();
	foreach ($VAL_SET as $v)
		if (isset($value[$v]))
			$val[$v] = $value[$v];

	$db->update_data('users', $val, array($DBOP['='], 'id', $uid));
}

/**
 * increase user statistics value
 * @param int $uid user id
 * @param array $filed the fileds need to be increased, which must be a subset
 *		of array('submit', 'ac', 'unac', 'ce')
 * @param int $delta
 * @return void
 * @exception Exc_inner if user id does not exist
 */
function user_increase_statistics($uid, $field, $delta = 1)
{
}

