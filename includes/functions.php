<?php
/* 
 * $File: functions.php
 * $Date: Wed Oct 13 18:59:25 2010 +0800
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

require_once $includes_path . 'db/' . $db_type . '.php';

/**
 * set cookie
 * @param string $name name of cookie,$table_prefix will be added at the beginning automatically
 * @param string $name name of cookie
 * @param string $value value of cookie
 * @param int $lasttime how long will the cookie exists. NULL means broswer session, set it to a non-positive value will delete the cookie
 */
function cookie_set($name, $value, $lasttime = NULL)
{
	global $table_prefix;
	$name = $table_prefix . $name;
	if (is_int($lasttime))
	{
		if ($lasttime > 0)
			setcookie($name, $value, time() + $lasttime);
		else setcookie($name, '', 123);
	}
	else
		setcookie($name, $value);
}

/**
 * get cookie value
 * @param string $name name of cookie,$table_prefix will be added at the beginning automatically
 * @param string $name name of cookie
 * @return NULL|string return the cookie value or NULL if it does not exist
 */
function cookie_get($name)
{
	global $table_prefix;
	$name = $table_prefix . $name;
	if (isset($_COOKIE[$name]))
		return $_COOKIE[$name];
	else
		return FALSE;
}

/**
 * set session with $table_prefix at the beginning of session name
 * @param string $name name of sesssion
 * @param string $value value of sesssion
 */
function session_set($name, $value)
{
	global $table_prefix;
	static $session_started = FALSE;
	if (!$session_started)
	{
		session_start();
		$session_started = TRUE;
	}
	$_SESSION[$table_prefix.$name] = $value;
}

/**
 * get session value
 * @param string $name name of session,$table_prefix will be added at the beginning automatically
 * @return bool|string If cookie exists,content is returned.Otherwise,False is returned.
 */
function session_get($name)
{
	global $table_prefix;
	if (isset($_SESSION[$name]))
	{
		return $_SESSION[$name];
	}
	else
		return FALSE;
}


/**
 * translate HTML special chars and then change \n to &lt;br /&gt;
 * @param string $text plain text
 * @param bool $replace_space whether to replace space to &nbsp;
 * @return string translated text
 */
function htmlencode($text, $replace_space = FALSE)
{
	if ($replace_space)
		return nl2br(str_replace(' ', '&nbsp;',
			htmlspecialchars($text)));
	return nl2br(htmlspecialchars($text));
}

$db = NULL;
/**
 * connect to the database and set global variable $db
 * @global Dbal $db
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
	$data = $db->select_from('options', 'value',
		array($DBOP['=s'], 'key', $key));
	if ($data && count($data))
		return $data[0]['value'];
	else
		return FALSE;
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
		return TRUE;
	else
		return FALSE;
}

/**
 * set option value
 * @param string $key option key
 * @param string $value option value
 * @return bool whether succeed
 * @exception Exc_inner if $key too long
 */
function option_set($key, $value)
{
	if (strlen($key) > OPTION_KEY_LEN_MAX)
		throw new Exc_inner('option key too long');
	global $db, $DBOP;
	$ndt  = array('key' => $key,
		'value' => $value
		);
	if (option_get($key) !== FALSE)
	{
		if ($db->update_data('options', $ndt, array($DBOP['=s'], 'key', $key)) !== FALSE)
			return TRUE;
		else
			return FALSE;
	}
	else
	{
		if ($db->insert_into('options', $ndt) !== FALSE)
			return TRUE;
		else
			return FALSE;
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

/**
 * get the URL pointing to $file
 * @param string $file file path
 * @return string the URL
 */
function get_page_url($file)
{
	global $website_root, $root_path;
	$file = realpath($file);
	return $website_root . substr($file, strlen($root_path));
}

/**
 * update table numeric value
 * @param string $table table name
 * @param array $where where clause
 * @param string|array $fileds the fileds need to be increased
 * @param int $delta
 * @return void
 * @exception Exc_inner if the row id does not exist
 */
function table_update_numeric_value($table, $where, $fields, $delta = 1)
{
	global $db, $DBOP;

	if (is_string($fields))
		$fields = array($fields);

	$val = $db->select_from($table, $fields, $where);

	if (!count($val))
		throw new Exc_inner(__('no such row'));

	$val = $val[0];
	foreach ($val as $k => $v)
		$val[$k] += $delta;

	$db->update_data($table, $val, $where);
}

/**
 * @ignore
 */
$_xhtml_error = NULL;

/**
 * @ignore
 */
function _xhtml_error_handler($errno, $msg)
{
	global $_xhtml_error;
	$_xhtml_error = $msg;
}

define('_XHTML_ROOT', 'orzoj-xhtml');

/**
 * check user posted XHTML data
 * @param string $text
 * @return void
 * @exception Exc_xhtml on error
 */
function xhtml_validate($text)
{
	$text = '<' . _XHTML_ROOT . '>' . $text . '</' . _XHTML_ROOT . '>';
	global $_xhtml_error, $root_path;
	$_xhtml_error = NULL;
	set_error_handler('_xhtml_error_handler');

	$old = new DOMDocument;
	$old->loadXML($text);

	if (is_null($_xhtml_error))
	{
		$creator = new DOMImplementation;
		$doctype = $creator->createDocumentType(_XHTML_ROOT, NULL,
		   $root_path . 'contents/' . _XHTML_ROOT . '.dtd');
		$new = $creator->createDocument(NULL, NULL, $doctype);
		$new->encoding = "utf-8";

		$new->strictErrorChecking = FALSE;
		// setting $new->strictErrorChecking to TRUE seems not to work on my system

		$new->appendChild(
			$new->importNode(
				$old->getElementsByTagName(_XHTML_ROOT)->item(0), TRUE));

		$new->validate();
	}

	restore_error_handler();

	if (!is_null($_xhtml_error))
		throw new Exc_xhtml($_xhtml_error);
}

