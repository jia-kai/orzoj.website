<?php
/* 
 * $File: functions.php
 * $Date: Tue Sep 28 16:11:17 2010 +0800
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

require_once $includes_path . 'db/' . $db_type . '.php';

/**
 * Set Cookie with $table_prefix at the beginning of cookie name
 * @param string $name name of cookie
 * @param string $value value of cookie
 * @param int $lasttime how long will the cookie exists. NULL means broswer session
 */
function cookie_set($name, $value, $lasttime = NULL)
{
	global $table_prefix;
	$name = $table_prefix . $name;
	if ($lasttime > 0)
		setcookie($name, $value, time() + $lasttime);
	else
		setcookie($name, $value);
}

/**
 * Get Cookie value with $table_prefix at the beginning of cookie name
 * @param string $name name of cookie
 * @return bool|string If cookie exists,a string is returned. Otherwise,False is returned.
 */
function cookie_get($name)
{
	global $table_prefix;
	$name = $table_prefix . $name;
	if (isset($_COOKIE[$name]))
		return $_COOKIE[$name];
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

$db = NULL;
/**
 * connect to the database and set global variable $db
 * @global $db
 * @return void
 */
function db_init()
{
	global $db, $db_type, $db_host, $db_port, $db_user, $db_password, $db_dbname,
		$table_prefix;
	if ($db)
		return;
	$db_class = 'Dbal_' . $db_type;
	$db = new $db_class;
	$db->connect($db_host, $db_port, $db_user, $db_password, $db_dbname);
	$db_password = '';
	$db->set_prefix($table_prefix);
}

/**
 * get option value
 * @param string $key option key
 * @return string|bool option value on success, FALSE on failure 
 */
function option_get($key)
{
	global $db, $DBOP;
	$data = $db->select_from('options', NULL,
		array($DBOP['=s'], 'key', $key));
	if ($data && count($data))
		return $data[0]['value'];
	else
		return false;
}

/**
 * delete option
 * @param string $key option key
 * @return bool whether succeed
 */
function option_delete($key)
{
	global $db, $DBOP;
	if ($db->delete_item('options', array($DBOP['=s'], 'key', $key)) !== FALSE)
		return true;
	else
		return false;
}

/**
 * set option value
 * @param string $key option key
 * @param string $value option value
 * @return bool whether succeed
 */
function option_set($key, $value)
{
	global $db, $DBOP;
	$ndt  = array('key' => $key,
		'value' => $value
		);
	$wclause = array('param1' => 'option_name','op1' => 'text_eq','param2' => $option_name);
	if (option_get($key) !== FALSE)
	{
		if ($db->update_data('options', $ndt, array($DBOP['=s'], 'key', $key)) !== FALSE)
			return true;
		else
			return false;
	}
	else
	{
		if ($db->insert_into('options', $ndt) !== FALSE)
			return true;
		else
			return false;
	}
}

/**
 * get remote address
 * @return string 
 */
function get_remote_addr()
{
	return $_SERVER['REMOTE_ADDR'];
}


