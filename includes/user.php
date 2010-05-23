<?php
/*
 * $File: user.php
 * $Date: Sun May 16 11:21:23 2010 +0800
 * $Author: Fan Qijiang <fqj1994@gmail.com>
 */
/**
 * @package orzoj-phpwebsite
 * @copyright (c) Fan Qijiang
 * @version phpweb-1.0.0alpha1
 * @author Fan Qijiang <fqj1994@gmail.com>
 * @license http://gnu.org/licenses/ GNU GPLv3
 */
/*
	Orz Online Judge is a cross-platform programming online judge.
	Copyright (C) <2009,2010>  (Fan Qijiang) <fqj1994@gmail.com>

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

if (!defined('IN_ORZOJ')) exit;

require_once "l10n.php";
require_once "error.php";
require_once "common.php";


/**
 * Check password using orzoj standard
 * @param string $db_passwd password read from database
 * @param string $user_passwd password which user input
 * @param bool if right,TRUE,otherwise FALSE
 */
function user_pwd_chk($db_passwd,$user_passwd)
{
	$pos = strpos($db_passwd,'::');
	if ($pos == false) return false;
	else
	{
		$pwdtype = substr($db_passwd,0,$pos);
		$pwdcontent = substr($db_passwd,$pos+2);
		switch ($pwdtype)
		{
		case '01':
			if ($pwdcontent == sha1($user_passwd)) return true;
			else return false;
			break;
		default:
			return false;
		}
	}	
}

/**
 * Generate a password using orzoj standard
 * @param string $user_passwd plain text of password
 * @return string hashed or encrypted password
 */
function user_pwd_gen($user_passwd)
{
	return '01::'.sha1($user_passwd);
}

/**
 * Generate random text
 * @param int $length length of random  text
 * @return string a random text
 */
function user_salt_gen($length = 10)
{
	$chars = array();
	for ($i=ord('a');$i<=ord('z');$i++)
	{
		array_push($chars,chr($i));
		array_push($chars,strtoupper(chr($i)));
	}
	shuffle($chars);
	mt_srand((double)microtime()*10000000*getmypid());
	$salt = "";
	$size= count($chars);
	for ($i=1;$i<=$length;$i++)
		$salt.=$chars[mt_rand(0,$size-1)];
	return $salt;
}

/**
 * Create a user
 * @param string $username username
 * @param string $password plain text password
 * @param string $email real email address
 * @param string $question question to get new password
 * @param string $answer answer to get new password
 * @param int programminglanguage ususally used programming language.
 * @return bool|int on success,true or new user's id will be returned.Otherwise,false will be returned,and $errormsg is set.
 */
function user_add($username,$password,$email,$question,$answer,$programminglanguage,$otherinfo)
{
	global $db,$tablepre;
	$insert_data = array(
		'username' => $username,
		'password' =>  user_pwd_gen($password),
		'email' => $email,
		'question' => $question,
		'answer' => user_pwd_gen($answer),
		'programminglanguage' => (int)($programminglanguage),
		'regtime' => time(),
		'checksum' => user_salt_gen(64),
		'regip' => $_SERVER['REMOTE_ADDR'],
		'otherinfo' => serialize($otherinfo)
	);
	$tablename = $tablepre.'users';
	$exsits_check = array('param1' => 'username','op1' => 'text_eq','param2' => $username);
	$result = $db->get_number_of_rows($tablename,$exsits_check);
	if ($result > 0)
	{
		error_set_message('Username has already existed.');
		return false;
	}
	else
	{
		if (($insert_id = $db->insert_into($tablename,$insert_data)) !== FALSE)
		{
			if ($insert_id > 0) return $insert_id;
			else
				return TRUE;
		}
		else
		{
			error_set_message(sprintf(__('Failed to create user.Error message : %s.'),htmlencode($db->error())));
			return false;
		}
	}
}

/**
 * Change user personal info
 * @param int $uid user id
 * @param string $email new email address
 * @param int $programminglanguage new programming language
 * @return bool on success,true will be returned.Otherwise,false will be returned,and $errormsg is set.
 */
function user_change_personal_info($uid,$email,$programminglanguage)
{
	global $db,$tablepre;
	$newdata = array(
		'email' => $email,
		'programminglanguage' => (int)($programminglanguage)
	);
	if (($affected_rows = $db->update_data($tablepre.'users',$newdata,array('param1' => 'uid','op1' => 'int_eq','param2' => (int)($uid)))) !== FALSE)
		return true;
	else
	{
		error_set_message(sprintf(__('SQL Error : %s.'),htmlencode($db->error())));
		return false;
	}
}

/**
 * Change user password
 * @param int $uid user id
 * @param string $old_pass old_password
 * @param  string $new_pass new_password
 * @return bool on success,true will be returned.Otherwise,false will be returned,and $errormsg is set.
 */
function user_change_password($uid,$old_pass,$new_pass)
{
	global $db,$tablepre;
	$newdata = array('password' => user_pwd_gen($new_pass));
	$wclause = array('param1' => 'uid','op1' => 'int_eq','param2' => (int)($uid));
	$content = $db->select_from($tablepre.'users',NULL,$wclause);
	if (!is_array($content))
	{
		error_set_message(sprintf(__('SQL Error : %s.'),htmlencode($db->error())));
		return false;
	}
	if (count($content)<1)
	{
		error_set_message(__('Invalid user.'));
		return false;
	}
	if (!user_pwd_chk($content[0]['password'],$old_pass))
	{
		error_set_message(__('Wrong old password.'));
		return false;
	}
	if ($db->update_data($tablepre.'users',$newdata,$wclause) !== FALSE)
		return true;
	else
	{
		error_set_message(sprintf(__('SQL Error : %s.'),htmlencode($db->error())));
		return false;
	}
}


/**
 * Change user's question and answer
 * @param int $uid user id
 * @param string $old_answer old answer
 * @param string $new_question new question for get new password
 * @param string $new_answer new answer to get password
 * @return bool on success,TRUE is returned.Otherwise false is returned
 */
function user_change_question_ans_answer($uid,$old_answer,$new_question,$new_an)
{
	global $db,$tablepre;
	$wclause = array('param1' => 'uid','op1' => 'int_eq','param2' => (int)($uid));
	$content = $db->select_from($tablepre.'users',NULL,$wclause);
	if (!is_array($content))
	{
		error_set_message(sprintf(__('SQL Error : %s.'),htmlencode($db->error())));
		return false;
	}
	if (count($content)<1)
	{
		error_set_message(__('Invalid user.'));
		return false;
	}
	if (!user_pwd_chk($content[0]['answer'],$old_answer))
	{
		error_set_message(__('Wrong old answer'));
		return false;
	}
	if ($db->update_data($tablepre.'users',$newdata,$wclause) !== FALSE)
		return true;
	else
	{
		error_set_message(sprintf(__('SQL Error : %s.'),htmlencode($db->error())));
		return false;
	}
}

/**
 * Delete a user by uid
 * @param int $uid user id
 * @return bool if success,TRUE is returned.Otherwise,FALSE is returned.
 */
function user_delete_by_uid($uid)
{
	global $db,$tablepre;
	$whereclause = array('param1' => 'uid','op1' => 'int_eq','param2' => (int)($uid));
	if ($db->delete_item($tablepre.'users',$whereclause) !== FALSE)
		return true;
	else
	{
		error_set_message(sprintf(__('SQL Error : %s.'),htmlencode($db->error())));
		return false;
	}
}

/**
 * Search a user by uid
 * @param int $uid user id 
 * @return array|bool if success,an array with user info in it is returned.Otherwise,FALSE is returned.
 */
function user_search_by_uid($uid)
{
	global $db,$tablepre;
	$whereclause = array('param1' => 'uid','op1' => 'int_eq','param2' => (int)($uid));
	$content = $db->select_from($tablepre.'users',NULL,$whereclause);
	if ($content && count($content))
		return $content[0];
	else if (is_array($content))
	{
		error_set_message(__('User not found.'));
		return false;
	}
	else
	{
		error_set_message(sprintf(__('SQL Error : %s.'),htmlencode($db->error())));
		return false;
	}
}

/**
 * Search a user by username
 * @param string $username username
 * @return array|bool if success,an array with user info in it is returned.Otherwise,FALSE is returned.
 */
function user_search_by_username($username)
{
	global $db,$tablepre;
	$whereclause = array('param1' => 'username','op1' => 'text_eq','param2' => $username);
	$content = $db->select_from($tablepre.'users',NULL,$whereclause);
	if ($content && count($content))
		return $content[0];
	else if (is_array($content))
	{
		error_set_message(__('User not found.'));
		return false;
	}
	else
	{
		error_set_message(sprintf(__('SQL Error : %s.'),htmlencode($db->error())));
		return false;
	}
}

/**
 * Login by uid
 * @param int $uid user id
 * @return bool if success,TRUE.Otherwise false.
 */
function user_login($uid,$expires = NULL)
{
	global $db,$tablepre;
	$uid = (int)($uid);
	$info = user_search_by_uid($uid);
	if ($info)
	{
		cookie_set('user_uid',$uid,$expires);
		cookie_set('user_checksum',$info['checksum'],$expires);
		$newinfo =  array('lastlogintime' => time(),'lastloginip' => $_SERVER['REMOTE_ADDR']);
		if ($db->update_data($tablepre.'users',$newinfo,array('param1' => 'uid','op1' => 'int_eq','param2' => (int)($uid))))
			return true;
		else
		{
			error_set_message(sprintf(__('SQL Error : %s.'),htmlencode($db->error())));
			user_logout();
			return false;
		}
	}
	else
		return false;
}

/**
 * Logout
 */
function user_logout()
{
	cookie_set('user_uid',0);
	cookie_set('user_checksum',0);
}

