<?php
/* 
 * $File: user.php
 * $Date: Mon Nov 08 23:32:27 2010 +0800
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

// definitions for default user groups
$cnt = 1;
define('GID_NONE', $cnt ++); // nobody should be in this group
define('GID_GUEST', $cnt ++); // unregistered users should be in this group
define('GID_ALL', $cnt ++); // every registered user should be in this group
define('GID_LOCK', $cnt ++); // locked group id
define('GID_ADMIN_USER', $cnt ++); // manage users (lock a user, change password, etc)
define('GID_ADMIN_GROUP', $cnt ++);  // manage user groups (add, remove groups and assign group administrators)
define('GID_ADMIN_TEAM', $cnt ++); // manage user teams
define('GID_ADMIN_PROB', $cnt ++); // manage problems and problem groups
define('GID_ADMIN_CONTEST', $cnt ++);  // manage contests
define('GID_ADMIN_POST', $cnt ++); // manage posts
define('GID_ADMIN_ANNOUNCEMENT', $cnt ++); // manage announcements
define('GID_ADMIN_PLUGIN', $cnt ++);
define('GID_SUPER_SUBMITTER', $cnt ++);
// view and submit regardless of which contest the problem belongs to
// or other limits on viewing or submitting problem
define('GID_SUPER_RECORD_VIEWER', $cnt ++);
// view all records and sources
define('GID_UINFO_VIEWER', $cnt ++); // view register IP, submission IP, user real name etc.


/**
 * user structure
 */
class User
{
	var
		// normal information (assign on construction)
		$id, $username, $realname, $nickname,
		$avatar_id, // avatar id
		$avatar, // avatar URL
		$tid, $plang, $wlang,
		$view_gid, // array of gid who can view the user's source

		// detailed information (assign by set_val_detail())
		$email, $self_desc, 
		$reg_time, $reg_ip, $last_login_time, $last_login_ip;

	private
		$groups = NULL, // array of ids of groups that the user blongs to
		$admin_groups = NULL, // array of ids of groups where the user is an administrator
		$detail_val_done = FALSE;

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
	 * @exception Exc_runtime if user id does not exist
	 */
	public function __construct($uid)
	{
		$this->id = $uid;
		global $db, $DBOP;
		$row = $db->select_from('users', NULL,
			array($DBOP['='], 'id', $uid));
		if (count($row) != 1)
			throw new Exc_runtime(__('user id %d does not exist', $uid));
		$row = $row[0];

		$VAL_SET = array('id', 'username', 'realname', 'nickname',
			'email', 'self_desc', 'tid', 'plang', 'wlang');

		foreach ($VAL_SET as $val)
			$this->$val = $row[$val];

		$this->avatar_id = $row['aid'];
		$this->avatar = avatar_get_url($row['aid']);

		$this->view_gid = json_decode($row['view_gid']);

	}

	/**
	 * get groups that the user belongs to
	 * @return array array of ids of groups
	 */
	public function get_groups()
	{
		if ($this->groups)
			return $this->groups;
		global $db, $DBOP;
		$uid = $this->id;
		$rows = $db->select_from('map_user_grp', array('gid', 'admin'),
			array($DBOP['&&'], $DBOP['='], 'uid', $uid, $DBOP['='], 'pending', 0));

		$groups = array(GID_ALL);
		$this->admin_groups = array();

		$grp_set = array(GID_ALL => 1, GID_NONE => 1, GID_GUEST => 1);

		foreach ($rows as $row)
		{
			$gid = intval($row['gid']);
			if (!isset($grp_set[$gid]))
			{
				$groups[] = $gid;
				if ($row['admin'] == 1)
					$this->admin_groups[] = $gid;

				$grp_set[$gid] = 1;
			}
		}

		// TODO: a sqrt(n) division
		
		$where = NULL;
		foreach ($groups as $id)
			db_where_add_or($where, array($DBOP['='], 'id', $id));
		$rows = $db->select_from('user_grps', 'pgid', $where);
		foreach($rows as $row)
		{
			$pgid = intval($row['pgid']);
			if (!isset($grp_set[$pgid]))
			{
				$groups[] = $pgid;
				$grp_set[$pgid] = 1;
			}
		}
		/*
		for ($i = 0; $i < count($groups); $i ++)
		{
			$rows = $db->select_from('user_grps', 'pgid',
				array($DBOP['='], 'id', $groups[$i]));
			if (empty($rows))
				continue;
			$pgid = intval($rows[0]['pgid']);
			if (!isset($grp_set[$pgid]))
			{
				$groups[] = $pgid;
				$grp_set[$pgid] = 1;
			}
		}
		 */
		$this->groups = $groups;

		sort($this->groups, SORT_NUMERIC);
		sort($this->admin_groups, SORT_NUMERIC);
		return $this->groups;
	}

	/**
	 * get groups that the user is an administrator of
	 * @return array array of ids of groups
	 */
	public function get_admin_groups()
	{
		if (!$this->admin_groups)
			$this->get_groups();
		return $this->admin_groups;
	}
	
	/**
	 * test whether the user belongs to a specific group
	 * @param int $gid group id
	 * @return bool
	 */
	function is_grp_member($gid)
	{
		return User::bsearch($this->get_groups(), $gid);
	}

	/**
	 * test whether the user is a group administrator of the given group
	 * @param int $gid group id
	 * @return bool
	 */
	function has_admin_perm($gid)
	{
		return User::bsearch($this->get_admin_groups(), $gid);
	}

	/**
	 * set attributes for detailed information in this class
	 * @return void
	 * @exception Exc_runtime if user id does not exist
	 */
	function set_val_detail()
	{
		if ($this->detail_val_done)
			return;
		$this->detail_val_done = TRUE;
		global $db, $DBOP;
		$VAL_SET = array('email', 'self_desc',
			'reg_time', 'reg_ip', 'last_login_time', 'last_login_ip');
		$row = $db->select_from('users', $VAL_SET,
			array($DBOP['='], 'id', $this->id));
		if (count($row) != 1)
			throw new Exc_runtime(__('user id %d does not exist', $uid));

		$row = $row[0];
		foreach ($VAL_SET as $v)
			$this->$v = $row[$v];
	}

	public $STATISTICS_FIELDS = array(
		'cnt_ac', 'cnt_unac', 'cnt_ce', 'cnt_ac_prob', 'cnt_ac_prob_blink',
		'cnt_ac_submission_sum', 'cnt_submitted_prob', 'cnt_submit', 'ac_ratio'
		// ac_ratio is a real number between 0 and 1
	);
	/**
	 * get statistics value
	 * @return array an array containing $this->STATISTICS_FIELDS
	 * @exception Exc_runtime if user id does not exist
	 */
	function &get_statistics()
	{
		static $cache = NULL;
		global $db, $DBOP;
		if (!is_null($cache))
			return $cache;
		$f = $this->STATISTICS_FIELDS;
		unset($f[array_search('cnt_submit', $f)]);
		$cache = $db->select_from('users', $f,
			array($DBOP['='], 'id', $this->id));
		if (count($cache) != 1)
			throw new Exc_runtime(__('user id %d does not exist', $this->id));
		$cache = $cache[0];
		$cache['ac_ratio'] = $cache['ac_ratio'] / DB_REAL_PRECISION;
		$cache['cnt_submit'] = $cache['cnt_ac'] + $cache['cnt_unac'] + $cache['cnt_ce'];
		return $cache;
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
		try
		{
			user_validate_username($username);
		} catch (Exc_orzoj $e)
		{
			return $_user_check_login_result = FALSE;
		}
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

	$user = new User($uid);
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
 * get logout verification code
 * @return string|NULL NULL if not logged in
 */
function user_logout_get_code()
{
	if (!user_check_login())
		return NULL;
	global $db, $DBOP, $user;
	$row = $db->select_from('users', 'salt', array(
		$DBOP['='], 'id', $user->id));
	$row = $row[0];
	return md5(_user_make_passwd($user->id, sha1($row['salt'])));
}

/**
 * clean cookies about user login
 * if verification failes, nothing happens
 * @param string $code logout verification code returned by user_logout_get_code()
 * @return void
 */
function user_logout($code)
{
	global $user, $db, $DBOP, $_user_check_login_result;
	if (user_check_login())
	{
		if ($code != user_logout_get_code())
			return;
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
 * @ignore
 */
function _user_make_passwd_v01($username, $passwd)
{
	$passwd .= '$' . strtolower($username);
	return sha1(md5($passwd) . $passwd . sha1($passwd));
}

/**
 * @ignore
 */
function _user_make_passwd_vold($username, $passwd)
{
	return md5(md5($passwd).sha1($passwd).crc32($passwd).md5(base64_encode($passwd)));
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
 * @return int|NULL user id or NULL if no such user
 */
function user_get_id_by_username($name)
{
	$name = strtolower($name);
	global $db, $DBOP;
	$row = $db->select_from('users', 'id',
		array($DBOP['=s'], 'username', $name));
	if (count($row) == 1)
		return $row[0]['id'];
	return NULL;
}

/**
 * initialize user check id
 * @return void
 */
function user_init_form()
{
	global $_checker_id;
	$_checker_id['user'] = tf_form_register_checker('_user_check_name_form');
	$_checker_id['email'] = tf_form_register_checker('_user_check_email');
}

/**
 * @ignore
 */
function _user_check_name_form($name)
{
	try
	{
		user_validate_username($name);
	} catch (Exc_runtime $e)
	{
		return $e->msg();
	}
	if (user_get_id_by_username($name))
		return __('username %s already exists', $name);
	return __('Username avaliable');
}

/**
 * check whether the username is a valid one
 * @param string $name username
 * @exception Exc_runtime on error
 * @return void
 */
function user_validate_username($name)
{
	if (strlen($name) < USERNAME_LEN_MIN)
		throw new Exc_runtime(__('username should not be shorter than %d characters', USERNAME_LEN_MIN));
	if (strlen($name) > USERNAME_LEN_MAX)
		throw new Exc_runtime(__('username should not be longer than %d characters', USERNAME_LEN_MAX));
	if (count(preg_grep('#^[a-zA-Z][a-zA-Z0-9_.]*$#', array($name))) != 1)
		throw new Exc_runtime(
			__('username should begin with a letter and only contain letters, digits, dots(.) or underscores(_)'));
}

/**
 * check whether the nickname is a valid one
 * @param string $name nickname
 * @exception Exc_runtime on error
 * @return void
 */
function user_validate_nickname($name)
{
	if (strlen($name) > NICKNAME_LEN_MAX)
		throw new Exc_runtime(__('nickname should not be longer than %d characters', NICKNAME_LEN_MAX));
}

/**
 * check whether the real name is a valid one
 * @param string $name real name
 * @exception Exc_runtime on error
 * @return void
 */
function user_validate_realname($name)
{
	if (strlen($name) > REALNAME_LEN_MAX)
		throw new Exc_runtime(__('realname should not be longer than %d characters', NICKNAME_LEN_MAX));
}


/**
 * @ignore
 */
function _user_check_email($email)
{
	try
	{
		email_validate($email);
	}
	catch (Exc_runtime $e)
	{
		return $e->msg();
	}
	return '';
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
	global $_checker_id;
	_user_init_plang_wlang();
	global $db, $_user_plang, $_user_wlang;
	$str = 
		tf_form_get_text_input(__('Username:'), 'username', $_checker_id['user']) . 
		tf_form_get_passwd(__('Password:'), 'passwd', __('Confirm password:'), 'passwd_confirm') .
		tf_form_get_text_input(__('Real name:'), 'realname') .
		tf_form_get_text_input(__('Nickname:'), 'nickname') .
		tf_form_get_text_input(__('E-mail:'), 'email', $_checker_id['email']) .
		tf_form_get_avatar_browser(__('Avatar:'), 'aid') .
		tf_form_get_select(__('Preferred programming language:'), 'plang', $_user_plang) .
		tf_form_get_select(__('Preferred website language:'), 'wlang', $_user_wlang) .
		tf_form_get_long_text_input(__('Self description(XHTML):'), 'self_desc');
	echo filter_apply('after_user_register_form', $str);
}

/**
 * register a user, using the data posted by the user register form
 * @param bool $login_after_register whether to set cookies indicating
 * the user has logged in after succesful regesteration
 * @return int user id
 * @see user_register_get_form fields
 * @exception Exc_runtime if failed
 */
function user_register($login_after_register = FALSE)
{
	$VAL_SET = array('username', 'passwd', 'passwd_confirm', 'realname', 'nickname', 'email',
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
	if ($val['passwd'] != $val['passwd_confirm'])
		throw new Exc_runtime(__('Passwords do not match'));
	unset($val['passwd_confirm']);

	user_validate_username($_POST['username']);
	if (user_get_id_by_username($_POST['username']))
		throw new Exc_runtime(__('username already exists'));

	try
	{
		xhtml_validate($val['self_desc']);
	} catch (Exc_orzoj $e)
	{
		throw new Exc_runtime(__('Error while validating self description: %s', $e->msg()));
	}

	user_validate_realname($val['realname']);
	user_validate_nickname($val['nickname']);

	email_validate($val['email']);

	$val['username'] = strtolower($val['username']);
	$val['passwd'] = _user_make_passwd($val['username'], $val['passwd']);
	$val['realname'] = htmlencode($val['realname']);
	$val['nickname'] = htmlencode($val['nickname']);
	$val['email'] = htmlencode($val['email']);
	$val['view_gid'] = json_encode(array(GID_ALL, GID_GUEST));
	$val['reg_time'] = time();
	$val['reg_ip'] = get_remote_addr();

	$val = filter_apply('before_user_register', $val);

	if ($login_after_register)
	{
		$salt = _user_make_salt();
		$val['salt'] = $salt;
	}

	global $db;
	$uid = $db->insert_into('users', $val);

	if ($login_after_register)
	{
		cookie_set('uid', $uid);
		cookie_set('password', _user_make_passwd($uid, $salt . $val['passwd']));
	}

	return $uid;
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
		if ($user->id != $uid)
			throw new Exc_runtime(__('permission denied'));
		if (!_user_check_passwd($row['username'], $oldpwd, $row['passwd']))
			throw new Exc_runtime(__('old password is not correct'));
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
	global $_checker_id;
	if (!user_check_login())
		throw new Exc_runtime(__('Not logged in'));
	_user_init_plang_wlang();
	global $user, $_user_plang, $_user_wlang;
	$str = 
		tf_form_get_text_input(__('Real name'), 'realname', NULL, $user->realname) .
		tf_form_get_text_input(__('Nickname'), 'nickname', NULL, $user->nickname) .
		tf_form_get_text_input(__('E-mail:'), 'email', $_checker_id['email'], $user->email) .
		tf_form_get_avatar_browser(__('Avatar:'), 'aid', $user->avatar_id) .
		tf_form_get_select(__('Preferred programming language:'), 'plang', $_user_plang, $user->plang) .
		tf_form_get_select(__('Preferred website language:'), 'wlang', $_user_wlang, $user->wlang) .
		tf_form_get_gid_selector(__('User groups who can view your source:'), 'view_gid', $user->view_gid) .
		tf_form_get_team_browser(__('Your team:'), 'tid', $user->tid) .
		tf_form_get_long_text_input(__('Self description(XHTML):'), 'self_desc', htmlspecialchars($user->self_desc));

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
	$VAL_SET = array('realname', 'nickname', 'email', 'aid',
		'plang', 'wlang', 'tid', 'self_desc');

	$val = array();
	foreach ($VAL_SET as $v)
	{
		if (!isset($_POST[$v]))
			throw new Exc_runtime(__('incomplete post'));
		$val[$v] = $_POST[$v];
	}

	try
	{
		xhtml_validate($val['self_desc']);
	} catch (Exc_orzoj $e)
	{
		throw new Exc_runtime(__('Error while validating self description: %s', $e->msg()));
	}

	user_validate_realname($val['realname']);
	user_validate_nickname($val['nickname']);


	email_validate($val['email']);

	$val['realname'] = htmlencode($val['realname']);
	$val['nickname'] = htmlencode($val['nickname']);
	$val['email'] = htmlencode($val['email']);
	$val['view_gid'] = json_encode(tf_form_get_gid_selector_value('view_gid'));

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
		$grp = $user->get_groups();
	else $grp = array(GID_GUEST);
	$row = $db->select_from('users', 'view_gid',
		array($DBOP['='], 'id', $uid));
	if (count($row) != 1)
		return TRUE;
	$row = json_decode($row[0]['view_gid']);
	return count(array_intersect($row, $grp)) > 0;
}

/**
 * get user group name by group id
 * @param int $gid group id
 * @return string|NULL group name or NULL if no such group
 */
function user_grp_get_name_by_id($gid)
{
	global $db, $DBOP;
	$row = $db->select_from('user_grps', 'name', array(
		$DBOP['='], 'id', $gid));
	if (count($row) != 1)
		return NULL;
	return $row[0]['name'];
}

/**
 * get user group id by group name
 * @param int $name group name
 * @return string|NULL group id or NULL if no such group
 */
function user_grp_get_id_by_name($name)
{
	global $db, $DBOP;
	$row = $db->select_from('user_grps', 'id', array(
		$DBOP['=s'], 'name', $name));
	if (count($row) != 1)
		return NULL;
	return $row[0]['id'];
}

/**
 * TODO : any limitation?
 * get user amount
 * @return int
 */
function user_get_user_amount()
{
	global $db;
	return $db->get_number_of_rows('users');
}

/**
 * initialize default user groups
 * should only be called on installation
 */
function user_init_default_grp()
{
	$grps = array(
		// <group id> => array(<name>, <description>, <parant group id>)
		GID_NONE => array('Nobody', __('nobody can be in this group'), 0),
		GID_GUEST => array('Guests', __('unregistered visitors'), 0),
		GID_ALL => array('All-Reg.', __('all registered users'), 0),
		GID_LOCK => array('Lock', __('locked users'), GID_ALL),
		GID_ADMIN_USER => array('Admin-User', __('administrate users and user groups'), GID_ALL),
		GID_ADMIN_TEAM => array('Admin-Team', __('administrate user teams'), GID_ALL),
		GID_ADMIN_PROB => array('Admin-Prob', __('administrate problems and problem groups'), GID_ALL),
		GID_ADMIN_CONTEST => array('Admin-Contest', __('administrate contests'), GID_ALL),
		GID_ADMIN_POST => array('Admin-Post', __('administrate posts'), GID_ALL),
		GID_ADMIN_ANNOUNCEMENT => array('Admin-Announcement', __('administrate announcements'), GID_ALL),
		GID_ADMIN_PLUGIN => array('Admin-Plugin',__('administrate plugins'),GID_ALL),
		GID_SUPER_SUBMITTER => array('Super-Submitter', __(
			'view problem and submit regardless of problem permission or contest permission or other limitations'),
			GID_ALL),
		GID_SUPER_RECORD_VIEWER => array('Record-Viewer', __('view all records and sources'), GID_ALL),
		GID_UINFO_VIEWER => array('User-Info-Viewer', __('view register IP, submission IP, real name, etc'), GID_ALL)
	);
	global $db;
	foreach ($grps as $gid => $info)
	{
		$db->insert_into('user_grps', array(
			'id' => $gid,
			'pgid' => $info[2],
			'name' => $info[0],
			'desc' => $info[1]
		));
		user_update_grp_cache_add($gid);
	}
}

/**
 * put the user in all privileged groups
 * @param int $uid the id of user to be operated on
 * @return void
 */
function user_set_super_admin($uid)
{
	global $db;
	for ($i = GID_ADMIN_USER; $i <= GID_UINFO_VIEWER; $i ++)
		$db->insert_into('map_user_grp', array(
			'uid' => $uid,
			'gid' => $i,
			'pending' => 0,
			'admin' => 1
		));
}

/**
 * update cache, must be called exactly once after adding a user group
 * @param int $gid id of newly added user group
 * @return void
 */
function user_update_grp_cache_add($gid)
{
	global $db, $DBOP;
	$pgid = $gid;
	while (TRUE)
	{
		$db->insert_into('cache_ugrp_child',
			array('gid' => $pgid, 'chid' => $gid));
		$pgid = $db->select_from('user_grps', 'pgid',
			array($DBOP['='], 'id', $pgid));
		if (empty($pgid))
			return;
		$pgid = intval($pgid[0]['pgid']);
		if ($pgid == 0)
			return;
	}
}

/**
 * update cache, must be called after deleting a user group
 * @param int $gid id of the deleted user group
 * @return void
 */
function user_update_grp_cache_delete($gid)
{
	global $db, $DBOP;
	$db->delete_item('cache_ugrp_child',
		array($DBOP['||'],
		$DBOP['='], 'gid', $gid,
		$DBOP['='], 'chid', $gid));
}


