<?php
/* 
 * $File: user.php
 * $Date: Sun Oct 03 19:09:22 2010 +0800
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

require_once $includes_path . 'message.php';

/**
 * user structure
 */
class User
{
	var $id, $username, $realname, $nickname,
		$avatar, // avatar file name, NULL if unavailable
		$email, $self_desc, $tid, $plang, $wlang,
		$view_gid, // array of gid who can view the user's source
		$theme_id,
		$reg_time, $reg_ip, $last_login_time, $last_login_ip,
		$cnt_submit, $cnt_ac, $cnt_unac, $cnt_ce,
		$groups, // array of id of groups that the user blongs to
		$admin_groups, // array of id of groups where the user is an administrator
		$is_admin, // whether the user is an administrator
		$is_locked; // whether this user is in the lock group

	/**
	 * set attributes in this class
	 * @param int $uid user id
	 * @param bool $set_grp_info whether to set $this->groups, $this->admin_groups, $this->is_admin, $this->is_locked
	 *		(because these operations may take some time)
	 * @return void
	 * @exception Exc_inner if user id does not exist
	 */
	function set_val($uid, $set_grp_info = TRUE)
	{
		global $db, $DBOP;
		$row = $db->select_from('users', NULL,
			array($DBOP['='], 'id', $uid));
		if (count($row) != 1)
			throw new Exc_inner(__('user id %d does not exist', $uid));
		$row = $row[0];

		$VAL_SET = array('id', 'username', 'realname', 'nickname',
			'email', 'self_desc', 'tid', 'plang', 'wlang', 'theme_id',
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

		if ($set_grp_info)
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
 * @param int $cookie_time see cookie_set() in functions.php
 * @return bool whether login successfully
 */
function user_check_login($cookie_time = NULL)
{
	global $user, $db, $DBOP, $action;
	static $result = NULL;
	if (is_bool($result))
		return $result;
	if (isset($_POST['username']) && isset($_POST['password']))
	{
		filter_apply_no_iter('before_user_login');
		$username = $_POST['username'];
		$password = $_POST['password'];
		if (!user_check_name($username))
			return $result = FALSE;
		$username = strtolower($username);
		$password .= $username;

		$row = $db->select_from('users', array('id', 'passwd'),
			array($DBOP['=s'], 'username', $username));
		if (count($row) != 1)
			return $result = FALSE;

		$row = $row[0];
		$pwd_chk = _user_check_passwd($password, $row['passwd']);
		if (!$pwd_chk)
			return $result = FALSE;
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
			return $result = FALSE;
		$row = $db->select_from('users', array('passwd', 'salt'),
			array($DBOP['='], 'id', $uid));
		if (count($row) != 1)
			return $result = FALSE;
		$row = $row[0];

		if ($password != _user_make_passwd($row['salt'] . $row['passwd']))
			return $result = FALSE;

	}

	filter_apply_no_iter('after_user_login', $uid);

	$db->update_data('users', array('last_login_time' => time(), 'last_login_ip' => get_remote_addr()),
		array($DBOP['='], 'id', $uid));

	$user = new User();
	$user->set_val($uid);
	return $result = TRUE;
}

/**
 * get user login form
 * @return string HTML login form
 */	
function user_check_login_get_form()
{
	$str = 
		tf_form_get_text_input(__('Username:'), 'username') .
		tf_form_get_passwd(__('Password:'), 'password');
	return filter_apply('after_user_login_form', $str);
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
 * check whether the username is a valid one
 * @param string $name username
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
 * get user id by username
 * @param string $name username
 * @return bool|int FALSE if such user does not exist, user id otherwise
 */
function user_get_id_by_name($name)
{
	$name = strtolower($name);
	global $db, $DBOP;
	$row = $db->select_from('users', 'id',
		array($DBOP['=s'], 'username', $name));
	if (count($row) == 1)
		return $row[0]['id'];
	return FALSE;
}

$_user_checker_id = tf_form_register_checker('user_check_name_string_output');

/**
 * check whether the username is a valid one
 * @param string $name username
 * @return string a human readable string describing the result
 */
function user_check_name_string_output($name)
{
	if (strlen($name) < USERNAME_LEN_MIN)
		return __('username should not be shorter than %d characters', USERNAME_LEN_MIN);
	if (strlen($name) > USERNAME_LEN_MIN)
		return __('username should not be longer than %d characters', USERNAME_LEN_MAX);
	if (count(preg_grep('#^[a-zA-Z][a-zA-Z0-9_.]*$#', array($name))) != 1)
		return __('username should begin with a letter and only contain letters, digits, dots(.) or underlines(_)');
	if (user_get_id_by_name($name))
		return __('username %s already exists', $name);
	return __('OK');
}


/**
 * @ignore
 */
function _user_init_plang_wlang()
{
	foreach (array('plang', 'wlang') as $lang)
	{
		$tmp = $db->select_from($lang, array('id', 'name'));
		$var = '_user_' . $lang;
		global $$var = array();
		foreach ($tmp as $row)
			$$var[$row['name']] = $row['id'];
	}
}

/**
 * get user register form 
 * @return string register form fields in HTML
 * @see user_register
 */
function user_register_get_form()
{
	_user_init_plang_wlang();
	global $db, $_user_plang, $_user_wlang;
	$str = 
		tf_form_get_text_input(__('Username (for loggin in):'), 'username', $_user_checker_id) . 
		tf_form_get_passwd(__('Password:'), 'password', __('Confirm password:')) .
		tf_form_get_text_input(__('Real name (only seen by the administrator):'), 'realname') .
		tf_form_get_text_input(__('Nickname (display name):'), 'nickname') .
		tf_form_get_text_input(__('E-mail:'), 'email') .
		tf_form_get_avatar_browser(__('Avatar:'), 'avatar') .
		tf_form_get_select(__('Preferred programming language:'), 'plang', $_user_plang) .
		tf_form_get_select(__('Preferred website language:'), 'wlang', $_user_wlang) .
		tf_form_get_long_text_input(__('Self description:'), 'self_desc');
	return filter_apply('after_user_register_form', $str);
}

/**
 * register a user, using the data posted by the user register form
 * @return int user id
 * @see user_register_get_form_fields
 * @exception Exc_runtime if failed
 */
function user_register()
{
	$VAL_SET = array('username', 'password', 'realname', 'nickname', 'email',
		'avatar', 'plang', 'wlang', 'self_desc');
	$val = array();
	foreach ($VAL_SET as $v)
	{
		if (!isset($_POST[$v]))
			throw new Exc_runtime('incomplete post');
		$val[$v] = $_POST[$v];
	}
	if (!user_check_name($_POST['username']))
		throw new Exc_runtime(__('invalid username'));
	if (user_get_id_by_name($_POST['username']))
		throw new Exc_runtime(__('username already exists'));

	$val['username'] = strtolower($val['username']);
	$val['password'] .= $val['username'];
	$val['passwd'] = _user_make_passwd($val['password']);
	unset($val['password']);
	$val['self_desc'] = htmlencode($val['self_desc']);
	$val['view_gid'] = serialize(array());
	$val['reg_time'] = time();
	$val['reg_ip'] = get_remote_addr();

	$val = filter_apply('before_user_register', $val);
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
	$db->delete_item('posts', $where);

	message_del_by_sender($uid);
	message_del_by_receiver($uid);
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
 * get a form for updating user information
 * @return string
 */
function user_update_info_get_form()
{
	if (!user_check_login())
		throw new Exc_runtime(__('Not logged in'));
	_user_init_plang_wlang();
	global $user, $_user_plang, $_user_wlang;
	$str = 
		tf_form_get_text_input(__('Real name'), 'realname', $user->realname) .
		tf_form_get_text_input(__('Nickname'), 'nickname', $user->nickname) .
		tf_form_get_text_input(__('E-mail:'), 'email', $user->email) .
		tf_form_get_avatar_browser(__('Avatar:'), 'avatar') .
		tf_form_get_select(__('Preferred programming language:'), 'plang', $_user_plang, $user->plang) .
		tf_form_get_select(__('Preferred website language:'), 'wlang', $_user_wlang, $user->wlang) .
		tf_form_get_theme_browser(__('Preferred website theme:'), 'theme_id', $user->theme_id) .
		tf_form_get_gid_selector(__('User groups who can view your source:'), 'view_gid', $user->view_gid) .
		tf_form_get_team_selector(__('Your team:'), 'tid', $user->tid) .
		tf_form_get_long_text_input(__('Self description:'), 'self_desc');

	return filter_apply('after_user_update_info_form', $str);
}

/**
 * @return void
 */
function user_update_info()
{
	if (!user_check_login())
		throw new Exc_runtime(__('Not logged in'));
	global $db, $DBOP, $user;
	$VAL_SET = array('realname', 'nickname', 'email', 'avatar', 'plang', 'wlang', 'theme_id',
		'', );

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
		throw new Exc_inner(__('user_update_statistics: uid %d does not exist', $uid));

	$val = $val[0];
	foreach ($val as $k => $v)
		$val[$k] += $delta;

	$db->update_data('users', $val, $where);
}


