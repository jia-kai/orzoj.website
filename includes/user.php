<?php
/* 
 * $File: user.php
 * $Date: Sat Oct 02 21:53:44 2010 +0800
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
	var $id, $username, $realname, $nickname,
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
	 * @param NULL|aray $fields_not_need the attributes not needed to be set
	 * @return void
	 * @exception Exc_inner if user id does not exist
	 */
	function set_val($uid, $fields_not_need = NULL)
	{
		global $db, $DBOP;
		$row = $db->select_from('users', NULL,
			array($DBOP['='], 'id', $uid));
		if (count($row) != 1)
			throw new Exc_inner(__('user id %d does not exist', $uid));
		$row = $row[0];

		$tmp = $fields_not_need;
		$fields_not_need = array();
		if (is_array($tmp))
			foreach ($tmp as $f)
				$fields_not_need[$f] = NULL;

		$VAL_SET = array('id', 'username', 'realname', 'nickname',
			'email', 'self_desc', 'tid', 'plang', 'wlang',
			'reg_time', 'reg_ip', 'last_login_time', 'last_login_ip',
			'cnt_submit', 'cnt_ac', 'cnt_unac', 'cnt_ce');

		foreach ($VAL_SET as $val)
			$this->$val = $row[$val];

		if (!isset($fields_not_need['avatar']))
		{
			$tmp = $db->select_from('user_avatars', 'file', array($DBOP['='], 'id', $row['aid']));
			if (count($tmp) != 1)
				$this->avatar = NULL;
			else
				$this->avatar = $tmp[0]['file'];
		}

		$this->view_gid = unserialize($row['view_gid']);

		if (!isset($fields_not_need['groups']) ||
			!isset($fields_not_need['admin_groups']) ||
			!isset($fields_not_need['is_admin']) ||
			!isset($fields_not_need['is_locked']))
		{
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
}

$user = NULL;
/**
 * check user login and initialize $user structure
 * @global User $user
 * @param string|NULL $username the user name, or NULL if read from cookie
 * @param string|NULL $password the password in plain text, or NULL if read from cookie
 * @param int $cookie_time see cookie_set() in functions.php
 * @return bool whether login successfully
 */
function user_check_login($username = NULL, $password = NULL, $cookie_time = NULL)
{
	global $user, $db, $DBOP, $action;
	if ($user)
		return TRUE;
	if (is_string($username) && is_string($password))
	{
		if (!user_check_name($username))
			return FALSE;
		$username = strtolower($username);
		$password .= $username;

		$row = $db->select_from('users', array('id', 'passwd'),
			array($DBOP['=s'], 'username', $username));
		if (count($row) != 1)
			return FALSE;

		$row = $row[0];
		$pwd_chk = _user_check_passwd($password, $row['passwd']);
		if (!$pwd_chk)
			return FALSE;
		if ($pwd_chk != $row['passwd'])
			$db->update_data('users', array('passwd' => $pwd_chk),
				array($DBOP['='], 'id', $row['id']));

		$salt = _user_make_salt();

		$db->update_data('users', array('salt' => $salt),
			array($DBOP['='], 'id', $row['id']));

		cookie_set('uid', $row['id'], $cookie_time);
		cookie_set('password', _user_make_passwd($salt . $pwd_chk),
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

		if ($password != _user_make_passwd($row['salt'] . $row['passwd']))
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
	if (user_check_login())
	{
		$db->update_data('users', array('salt' => _user_make_salt()),
			array($DBOP['='], 'id', $user->id));
		$user = NULL;
	}
	cookie_set('uid', NULL, -1);
	cookie_set('password', NULL, -1);
}

define('_USER_PASSWD_ENCRYPTION_VERSION', '01');

/**
 * @ignore
 */
/*
 * check user password
 * @param string $passwd plain password
 * @param string $passwd_encr encrypted password read from database
 * @return NULL|string return the newest password as string if checking successfully, or NULL otherwise
 */
function _user_check_passwd($passwd, $passwd_encr)
{
	$pos = strpos($passwd_encr, ':');
	$version = substr($passwd_encr, 0, $pos);
	$func = '_user_make_passwd_v' . $version;
	$ret = $func($passwd);
	if ($ret != substr($passwd_encr, $pos + 1))
		return NULL;
	return _user_make_passwd($passwd);
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
function _user_make_passwd_v01($passwd)
{
	return sha1(md5($passwd) . $passwd . sha1($passwd));
}

/**
 * @ignore
 */
function _user_make_passwd($passwd)
{
	$func = '_user_make_passwd_v' . _USER_PASSWD_ENCRYPTION_VERSION;
	return _USER_PASSWD_ENCRYPTION_VERSION . ':' . $func($passwd);
}

/**
 * @ignore
 */
function _user_make_salt()
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

$_user_checker_id = tf_register_checker('user_check_name_string_output');

/**
 * check whether the user name is a valid one
 * @param string $name user name
 * @return string a human readable string describing the result
 */
function user_check_name_string_output($name)
{
	if (strlen($name) < USERNAME_LEN_MIN)
		return __('user name should not be shorter than %d characters', USERNAME_LEN_MIN);
	if (strlen($name) > USERNAME_LEN_MIN)
		return __('user name should not be longer than %d characters', USERNAME_LEN_MAX);
	if (count(preg_grep('#^[a-zA-Z][a-zA-Z0-9_.]*$#', array($name))) != 1)
		return __('user name should begin with a letter and only contain letters, digits, dots(.) or underlines(_)');
	if (user_get_id_by_name($name))
		return __('user name %s already exists', $name);
	return __('OK');
}

/**
 * get user register form fields
 * @return string register form fields in HTML
 * @see user_register
 */
function user_register_get_form_fields()
{
	global $db;
	foreach (array('plang', 'wlang') as $lang)
	{
		$tmp = $db->select_from($lang, array('id', 'name'));
		$$lang = array();
		foreach ($tmp as $row)
			$$lang[$row['name']] = $row['id'];
	}
	$str = 
		tf_get_form_text_input(__('User name'), 'username', $_user_checker_id) . 
		tf_get_form_passwd_with_verifier('password') .
		tf_get_form_text_input(__('Real name'), 'realname') .
		tf_get_form_text_input(__('Nick name'), 'nickname') .
		tf_get_form_text_input(__('E-mail', 'email')) .
		tf_get_avatar_browser(__('Avatar', 'avatar')) .
		tf_get_form_select(__('Preferred programming language'), 'plang', $plang) .
		tf_get_form_select(__('Preferred website language'), 'wlang', $wlang) .
		tf_get_form_long_text_input(__('Self description'), 'self_desc');
	return filter_apply('after_user_register_form', $str);
}

/**
 * register a user, using the data posted by the user register form
 * @return int|string user id, or a string describing the reason of failure
 * @see user_register_get_form_fields
 */
function user_register()
{
	$VAL_SET = array('username', 'password', 'realname', 'nickname', 'email',
		'avatar', 'plang', 'wlang', 'self_desc');
	$val = array();
	foreach ($VAL_SET as $v)
	{
		if (!isset($_POST[$v]))
			return 'incomplete post';
		$val[$v] = $_POST[$v];
	}
	if (!user_check_name($_POST['username']))
		return __('invalid user name');
	if (user_get_id_by_name($_POST['username']))
		return __('user name already exists');

	$val['username'] = strtolower($val['username']);
	$val['password'] .= $val['username'];
	$val['passwd'] = _user_make_passwd($val['password']);
	unset($val['password']);
	$val['self_desc'] = htmlencode($val['self_desc']);
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
	$db->delete_item('');
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

	if (!_user_check_passwd($oldpwd, $row['passwd']))
		return FALSE;

	$db->update_data('users', array('passwd' => _user_make_passwd($newpwd)),
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
 * update user statistics value
 * @param int $uid user id
 * @param array $filed the fileds need to be increased, which must be a subset
 *		of array('submit', 'ac', 'unac', 'ce')
 * @param int $delta
 * @return void
 * @exception Exc_inner if user id does not exist
 */
function user_update_statistics($uid, $field, $delta = 1)
{
	global $db, $DBOP;
	$VAL_SET = array('submit', 'ac', 'unac', 'ce');
	$val = array_intersect($field, $VAL_SET);

	if (!count($val))
		return;

	foreach ($val as $k => $v)
		$val[$k] = 'cnt_' . $v;

	$where = array($DBOP['='], 'id' ,$uid);

	$val = $db->select_from('users', $val, $where);

	if (!count($val))
		throw new Exc_inner(__('user_increase_statistics: uid %d does not exist', $uid));

	$val = $val[0];
	foreach ($val as $k => $v)
		$val[$k] += $delta;

	$db->update_data('users', $val, $where);
}


/**
 * get the user regster form
 * @return string HTML register form
 */
function user_get_register_form()
{
}

// TODO: team, avatar
