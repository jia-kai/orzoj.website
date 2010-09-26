<?php
/* 
 * $File: functions.php
 * $Date: Sun Sep 26 22:28:30 2010 +0800
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
if (!defined('IN_ORZOJ')) exit;

require_once $includes_path . 'db/' . $db_type . '.php'

/**
 * Set Cookie with $tablepre at the beginning of cookie name
 * @param string $cookiename name of cookie
 * @param string $cookievalue value of cookie
 * @param int $lasttime how long will the cookie exists.NULL means broswer session
 */
function cookie_set($cookiename,$cookievalue,$lasttime = NULL)
{
	global $tablepre;
	if ($lasttime > 0)
	{
		setcookie($tablepre.$cookiename,$cookievalue,time() + $lasttime);
	}
	else
	{
		setcookie($tablepre.$cookiename,$cookievalue);
	}
}

/**
 * Get Cookie Value with $tablepre at the beginning of cookie name
 * @param string $cookiename name of cookie
 * @return bool|string If cookie exists,a string is returned.Otherwise,False is returned.
 */
function cookie_get($cookiename)
{
	global $tablepre;
	if (isset($_COOKIE[$tablepre.$cookiename]))
		return $_COOKIE[$tablepre.$cookiename];
	else
		return false;
}

/**
 * Translate HTML special chars and then change \n to <br>
 * @param string $text plain text
 * @return string translated text
 */
function htmlencode($text)
{
	return nl2br(htmlspecialchars($text));
}

$_db_instance = NULL;
/**
 * connect to the database and return an instance of dbal
 * @return dbal|bool on success, return an instance; return FALSE on failure
 */
function get_db_instance()
{
	global $_db_instance, $db_type, $db_host, $db_port, $db_user, $db_password, $db_dbname;
	if ($_db_instance)
		return $_db_instance;
	$_db_instance = new 'dbal_' . $db_type;
	if ($_db_instance->connect($db_host, $db_port, $db_user, $db_password, $db_dbname))
	{
		$db_password = '';
		return $_db_instance;
	}
	else
	{
		$_db_instance = NULL;
		return FALSE;
	}
}

function option_get($option_name)
{
	global $db,$tablepre;
	$wclause = array('param1' => 'option_name','op1' => 'text_eq','param2' => $option_name);
	$data = $db->select_from($tablepre.'options',NULL,$wclause);
	if ($data &&count($data)) return $data[0]['option_value'];
	else
		return false;
}

function option_delete($option_name)
{
	global $db,$tablepre;
	$wclause = array('param1' => 'option_name','op1' => 'text_eq','param2' => $option_name);
	if ($db->delete_item($tablepre.'options',$wclause) !== FALSE) return true;
	else
		return false;
}

function option_set($option_name,$new_value)
{
	global $db,$tablepre;
	$ndt  = array('option_name' => $option_name,
		'option_value' => $new_value
		);
	$wclause = array('param1' => 'option_name','op1' => 'text_eq','param2' => $option_name);
	if (option_get($option_name) !== FALSE)
	{
		if ($db->update_data($tablepre.'options',$ndt,$wclause) !== FALSE)
			return true;
		else
			return false;
	}
	else
	{
		if ($db->insert_into($tablepre.'options',$ndt) !== FALSE)
			return true;
		else
			return false;
	}
}

/**
 * Get Real User IP
 */
function get_real_ip()
{
	return $_SERVER['REMOTE_ADDR'];
}


