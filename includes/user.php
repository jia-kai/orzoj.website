<?php
/* 
 * $File: user.php
 * $Date: Thu Oct 14 14:11:22 2010 +0800
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
require_once $includes_path . 'avatar.php';

/**
 * user structure
 */
class User
{
	var $id, $username, $realname, $nickname,
		$avatar, // avatar URL
		$email, $self_desc, $tid, $plang, $wlang,
		$view_gid, // array of gid who can view the user's source
		$theme_id,
		$reg_time, $reg_ip, $last_login_time, $last_login_ip,
		$cnt_submit, $cnt_ac, $cnt_unac, $cnt_ce,
		$groups, // array of ids of groups that the user blongs to
		$admin_groups; // array of ids of groups where the user is an administrator

	/**
	 * @ignore
	 */
	private static function bsearch($a, $key)
	{
		$left = 0;
		$right = count($a) - 1;
		while ($left != $right)
		{
			$mid = ($left + $right) >> 1;
			if ($a[$mid] == $key)
				return TRUE;
			if ($a[$mid] < $key)
				$left = $mid + 1;
			else $right = $mid;
		}
		return $a[$left] == $key;
	}
	
	/**
	 * test whether the user belongs to a specific group
	 * @param int $gid group id
	 * @return bool
	 */
	function is_grp_member($gid)
	{
		return User::bsearch($this->groups, $gid);
	}

	/**
	 * test whether the user is a group administrator of the given group
	 * @param int $gid group id
	 * @return bool
	 */
	function has_admin_perm($gid)
	{
		return User::bsearch($this->admin_groups, $gid);
	}

	/**
	 * set attributes in this class
	 * @param int $uid user id
	 * @param bool $set_grp_info whether to set $this->groups, $this->admin_groups
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

		$this->avatar = avatar_get_url($row['aid']);

		$this->view_gid = unserialize($row['view_gid']);

		if ($set_grp_info)
		{
			$tmp = $db->select_from('map_user_group', array('gid', 'admin'),
				array($DBOP['&&'], $DBOP['='], 'uid', $uid, $DBOP['='], 'pending', 0));

			$groups = array(GID_ALL);
			$this->admin_groups = array();

			$grp_set = array();

			foreach ($tmp as $val)
			{
				$groups[] = $val['gid'];
				if ($val['admin'])
					$this->admin_groups[] = $val['gid'];

				$grp_set[$val['gid']] = 1;
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
				}
			}

			$this->groups = $groups;

			sort($this->groups, SORT_NUMERIC);
			sort($this->admin_groups, SORT_NUMERIC);
		}
		else
		{
			unset($this->groups);
			unset($this->admin_groups);
		}
	}
}

$user = NULL;
$_user_check_login_result = NULL;
/**
 * check user login and initialize $user structure
 * @global User $user
 * @param int $cookie_time see cookie_set() in functions.php
 * @return bool whether login successfully
 */
function user_check_login($cookie_time = NULL)
{
	global $user, $db, $DBOP, $action, $_user_check_login_result;
	if (is_bool($_user_check_login_result))
		return $_user_check_login_result;
	if (isset($_POST['username']) && isset($_POST['password']))
	{
		filter_apply_no_iter('before_user_login');
		$username = $_POST['username'];
		$password = $_POST['password'];
		if (!user_check_name($username))
			return $_user_check_login_result = FALSE;
		$username = strtolower($username);

		$row = $db->select_from('users', array('id', 'passwd'),
			array($DBOP['=s'], 'username', $username));
		if (count($row) != 1)
			return $_user_check_login_result = FALSE;

		$row = $row[0];
		$pwd_chk = _user_check_passwd($username, $password, $row['passwd']);
		if (!$pwd_chk)
			return $_user_check_login_result = FALSE;
		if ($pwd_chk != $row['passwd'])
			$db->update_data('users', array('passwd' => $pwd_chk),
				array($DBOP['='], 'id', $row['id']));

		$salt = _user_make_salt();

		$db->update_data('users', array('salt' => $salt),
			array($DBOP['='], 'id', $row['id']));

		cookie_set('uid', $row['id'], $cookie_time);
		cookie_set('password', _user_make_passwd($row['id'], $salt . $pwd_chk),
			$cookie_time);

		$uid = $row['id'];
	}
	else
	{
		$uid = intval(cookie_get('uid'));
		$password = cookie_get('password');
		if ($uid === FALSE || $password === FALSE)
			return $_user_check_login_result = FALSE;
		$row = $db->select_from('users', array('passwd', 'salt'),
			array($DBOP['='], 'id', $uid));
		if (count($row) != 1)
			return $_user_check_login_result = FALSE;
		$row = $row[0];

		if ($password != _user_make_passwd($uid, $row['salt'] . $row['passwd']))
			return $_user_check_login_result = FALSE;

	}

	filter_apply_no_iter('after_user_login', $uid);

	$db->update_data('users', array('last_login_time' => time(), 'last_login_ip' => get_remote_addr()),
		array($DBOP['='], 'id', $uid));

	$user = new User();
	$user->set_val($uid);
	return $_user_check_login_result = TRUE;
}

/**
 * echo fileds in user login form
 * @return void
 */	
function user_check_login_get_form()
{
	$str = 
		tf_form_get_text_input(__('Username:'), 'username') .
		tf_form_get_passwd(__('Password:'), 'password');
	echo filter_apply('after_user_login_form', $str);
}

/**
 * clean cookies about user login
 * @return void
 */
function user_logout()
{
	global $user, $db, $DBOP, $_user_check_login_result;
	if (user_check_login())
	{
		$db->update_data('users', array('salt' => _user_make_salt()),
			array($DBOP['='], 'id', $user->id));
		$user = NULL;
	}
	cookie_set('uid', NULL, -1);
	cookie_set('password', NULL, -1);
	$_user_check_login_result = FALSE;
}

define('_USER_PASSWD_ENCRYPTION_VERSION', '01');

/**
 * @ignore
 */
/*
 * check user password
 * @param string $username user name
 * @param string $passwd plain password
 * @param string $passwd_encr encrypted password read from database
 * @return NULL|string return the newest password as string if checking successfully, or NULL otherwise
 */
function _user_check_passwd($username, $passwd, $passwd_encr)
{
	$pos = strpos($passwd_encr, ':');
	$version = substr($passwd_encr, 0, $pos);
	$func = '_user_make_passwd_v' . $version;
	$ret = $func($username, $passwd);
	if ($ret != substr($passwd_encr, $pos + 1))
		return NULL;
	return _user_make_passwd($username, $passwd);
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
function _user_make_passwd_v01($username, $passwd)
{
	$passwd .= '$' . $username;
	return sha1(md5($passwd) . $passwd . sha1($passwd));
}

/**
 * @ignore
 */
function _user_make_passwd($username, $passwd)
{
	$func = '_user_make_passwd_v' . _USER_PASSWD_ENCRYPTION_VERSION;
	return _USER_PASSWD_ENCRYPTION_VERSION . ':' . $func($username, $passwd);
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
 * @return int|NULL user id or NULL of no such user
 */
function user_get_id_by_name($name)
{
	$name = strtolower($name);
	global $db, $DBOP;
	$row = $db->select_from('users', 'id',
		array($DBOP['=s'], 'username', $name));
	if (count($row) == 1)
		return $row[0]['id'];
	return NULL;
}

$_user_checker_id = NULL;

/**
 * initialize user check id
 * @return void
 */
function user_init_form()
{
	global $_user_checker_id;
	$_user_checker_id = tf_form_register_checker('user_check_name_string_output');
}

/**
 * check whether the username is a valid one
 * @param string $name username
 * @return string a human readable string describing the result
 */
function user_check_name_string_output($name)
{
	if (strlen($name) < USERNAME_LEN_MIN)
		return __('username should not be shorter than %d characters', USERNAME_LEN_MIN);
	if (strlen($name) > USERNAME_LEN_MAX)
		return __('username should not be longer than %d characters', USERNAME_LEN_MAX);
	if (count(preg_grep('#^[a-zA-Z][a-zA-Z0-9_.]*$#', array($name))) != 1)
		return __('username should begin with a letter and only contain letters, digits, dots(.) or underscores(_)');
	if (user_get_id_by_name($name))
		return __('username %s already exists', $name);
	return __('Username avaliable');
}


/**
 * @ignore
 */
function _user_init_plang_wlang()
{
	global $db;
	foreach (array('plang', 'wlang') as $lang)
	{
		$tmp = $db->select_from($lang, array('id', 'name'));
		$var = '_user_' . $lang;
		global $$var;
		$$var = array();
		$t = &$$var;
		foreach ($tmp as $row)
			$t[$row['name']] = $row['id'];
	}
}

/**
 * echo fields in user register form 
 * @return void
 * @see user_register
 */
function user_register_get_form()
{
	global $_user_checker_id;
	_user_init_plang_wlang();
	global $db, $_user_plang, $_user_wlang;
	$str = 
		tf_form_get_text_input(__('Username:'), 'username', $_user_checker_id) . 
		tf_form_get_passwd(__('Password:'), 'passwd', __('Confirm password:')) .
		tf_form_get_text_input(__('Real name:'), 'realname') .
		tf_form_get_text_input(__('Nickname:'), 'nickname') .
		tf_form_get_text_input(__('E-mail:'), 'email') .
		tf_form_get_avatar_browser(__('Avatar:'), 'aid') .
		tf_form_get_select(__('Preferred programming language:'), 'plang', $_user_plang) .
		tf_form_get_select(__('Preferred website language:'), 'wlang', $_user_wlang) .
		tf_form_get_long_text_input(__('Self description(XHTML):'), 'self_desc');
	echo filter_apply('after_user_register_form', $str);
}

/**
 * register a user, using the data posted by the user register form
 * @return int user id
 * @see user_register_get_form_fields
 * @exception Exc_runtime if failed
 */
function user_register()
{
	$VAL_SET = array('username', 'passwd', 'realname', 'nickname', 'email',
		'aid', 'plang', 'wlang', 'self_desc');
	$val = array();
	foreach ($VAL_SET as $v)
	{
		if (!isset($_POST[$v]))
			throw new Exc_runtime(__('incomplete post: required field "%s" not found', $v));
		if (!strlen($_POST[$v]))
			throw new Exc_runtime(__('Every field in the register form must be filled.'));
		$val[$v] = $_POST[$v];
	}
	if (!user_check_name($_POST['username']))
		throw new Exc_runtime(__('invalid username'));
	if (user_get_id_by_name($_POST['username']))
		throw new Exc_runtime(__('username already exists'));

	try
	{
		xhtml_validate($val['self_desc']);
	} catch (Exc_orzoj $e)
	{
		throw new Exc_runtime(__('Error while validating self description: %s', $e->msg()));
	}

	$val['username'] = strtolower($val['username']);
	$val['passwd'] = _user_make_passwd($val['username'], $val['passwd']);
	$val['realname'] = htmlencode($val['realname']);
	$val['nickname'] = htmlencode($val['nickname']);
	$val['email'] = htmlencode($val['email']);
	$val['view_gid'] = serialize(array());
	$val['reg_time'] = time();
	$val['reg_ip'] = get_remote_addr();

	$val = filter_apply('before_user_register', $val);
	global $db;
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
 * @param string $oldpwd old password in plain text (if logged in as administrator, $oldpwd is ignored)
 * @param string $newpwd new password in plain text
 * @return void
 * @exception Exc_runtime if faield to change password
 */
function user_chpasswd($uid, $oldpwd, $newpwd)
{
	global $db, $DBOP, $user;
	$where = array($DBOP['='], 'id', $uid);
	$row = $db->select_from('users', array('username', 'passwd'), $where);
	if (count($row) != 1)
		throw new Exc_runtime(__('uid %d does not exist', $uid));

	$row = $row[0];

	if (!user_check_login())
		throw new Exc_runtime(__('permission denied'));
	if (!$user->is_grp_member(GID_ADMIN_USER))
	{
		if ($user->id != $uid || !_user_check_passwd($row['username'], $oldpwd, $row['passwd']))
			throw new Exc_runtime(__('permission denied'));
	}

	$db->update_data('users', array('passwd' => _user_make_passwd($row['username'], $newpwd)),
		$where);
}


/**
 * echo fields in the form for updating user information
 * @return void
 * @see user_update_info
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

	echo filter_apply('after_user_update_info_form', $str);
}

/**
 * update user info
 * @return void
 * @see user_update_info_get_form
 */
function user_update_info()
{
	if (!user_check_login())
		throw new Exc_runtime(__('Not logged in'));
	global $db, $DBOP, $user;
	$VAL_SET = array('realname', 'nickname', 'email', 'avatar',
		'plang', 'wlang', 'theme_id', 'self_desc');

	$val = array();
	foreach ($VAL_SET as $v)
	{
		if (!isset($_POST[$v]))
			throw new Exc_runtime(__('incomplete post'));
		$val[$v] = htmlencode($_POST[$v]);
	}
	$val['view_gid'] = tf_form_get_gid_selector_value('view_gid');
	$val['tid'] = tf_form_get_team_selector_value('tid');

	$val = filter_apply('before_user_update_info', $val);

	$db->update_data('users', $val, array($DBOP['='], 'id', $user->id));
}

/**
 * @ignore
 */
function _user_get_name_by_id($uid)
{
	static $cache = array();
	if (array_key_exists($uid, $cache))
		return $cache[$uid];
	global $db, $DBOP;
	$row = $db->select_from('users', array('username', 'realname', 'nickname'),
		array($DBOP['='], 'id', $uid));
	if (count($row) != 1)
		return $cache[$uid] = NULL;
	return $cache[$uid] = $row[0];
}

/**
 * get username by user id
 * @param int $uid user id
 * @return array|NULL the username, or NULL if no such user
 */
function user_get_username_by_id($uid)
{
	$ret = _user_get_name_by_id($uid);
	if (!$ret)
		return NULL;
	return $ret['username'];
}

/**
 * get nickname by user id
 * @param int $uid user id
 * @return array|NULL the nickname, or NULL if no such user
 */
function user_get_nickname_by_id($uid)
{
	$ret = _user_get_name_by_id($uid);
	if (!$ret)
		return NULL;
	return $ret['nickname'];
}

/**
 * get real name by user id
 * @param int $uid user id
 * @return array|NULL the real name, or NULL if no such user
 */
function user_get_realname_by_id($uid)
{
	$ret = _user_get_name_by_id($uid);
	if (!$ret)
		return NULL;
	return $ret['realname'];
}

/**
 * check whether the current user has permission to view the source of a specific user
 * @param int $uid target user id
 * @return bool
 */
function user_check_view_src_perm($uid)
{
	global $db, $DBOP, $user;
	if (user_check_login())
	{
		if ($user->id == $uid || $user->is_grp_member(GID_SUPER_RECORD_VIEWER))
			return TRUE;
		$grp = $user->groups;
	} else $grp = array(GID_GUEST);
	$row = $db->select_from('users', 'view_gid',
		array($DBOP['='], 'id', $uid));
	if (count($row) != 1)
		return TRUE;
	$row = unserialize($row[0]['view_gid']);
	return count(array_intersect($row, $grp)) > 0;
}

