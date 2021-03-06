<?php
/* 
 * $File: functions.php
 * $Date: Sat Feb 12 16:30:29 2011 +0800
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
		return NULL;
}

/**
 * @ignore
 */
function _session_start()
{
	static $done = FALSE;
	if (!$done)
	{
		$done = TRUE;
		session_start();
	}
}

/**
 * @ignore
 */
$_session_prefix = $table_prefix;

/**
 * add the string to the current session prefix
 * @param string $str
 * @return void
 */
function session_add_prefix($str)
{
	global $_session_prefix;
	$_session_prefix .= $str . '$';
}

/**
 * set session with a given name
 * @param string $name name of sesssion
 * @param string $value value of sesssion
 */
function session_set($name, $value)
{
	global $_session_prefix;
	_session_start();
	$_SESSION[$_session_prefix . $name] = $value;
}

/**
 * get session value
 * @param string $name name of session
 * @return string|NULL the session value or NULL if no such session
 */
function session_get($name)
{
	global $_session_prefix;
	_session_start();
	$name = $_session_prefix . $name;
	if (isset($_SESSION[$name]))
		return $_SESSION[$name];
	return NULL;
}

/**
 * delete all sessions with given prefix
 * @param string|NULL $prefix the session prefix, or NULL indicating all sessions
 * @return void
 */
function session_clear($prefix = NULL)
{
	global $table_prefix;
	_session_start();
	if (is_null($prefix))
		session_destroy();
	else
	{
		$prefix = $table_prefix . $prefix . '$';
		foreach ($_SESSION as $name => $val)
			if (substr($name, 0, strlen($prefix)) == $prefix)
				unset($_SESSION[$name]);
	}
}

/**
 * translate HTML special chars and then change \n to &lt;br /&gt;
 * @param string $text plain text
 * @param bool $replace_space whether to replace space to &nbsp;
 * @return string translated text
 */
function htmlencode($text, $replace_space = FALSE)
{
	if (!is_string($text))
		return $text;
	$text = htmlspecialchars($text);
	if ($replace_space)
		$text = str_replace(' ', '&nbsp;', $text);
	return nl2br($text);
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
	if (defined('ORZOJ_DEBUG_MODE'))
		$db->record_query = TRUE;
	$db->connect($db_host, $db_port, $db_user, $db_password, $db_dbname);
	if (!defined('ORZOJ_DEBUG_MODE'))
		$db_password = '';
	$db->set_prefix($table_prefix);
}

$_option_cache = array();
/**
 * get option value
 * @param string $key option key
 * @param mixed $default default value to return if no such option
 * @return string|NULL option value on success, $default if no such option
 */
function option_get($key, $default = NULL)
{
	global $db, $DBOP, $_option_cache;
	static $is_cached = false;
	if (!$is_cached)
	{
		$is_cached = true;
		$data = $db->select_from('options');
		foreach ($data as $option)
			$_option_cache[$option['key']] = $option['value'];
	}
	if (array_key_exists($key, $_option_cache))
		return $_option_cache[$key];
	return $default;
}

/**
 * delete option
 * @param string $key option key
 * @return void
 */
function option_delete($key)
{
	global $db, $DBOP, $_option_cache;
	$db->delete_item('options', array($DBOP['=s'], 'key', $key));
	$_option_cache[$key] = NULL;
}

/**
 * set option value
 * @param string $key option key
 * @param string $value option value
 * @return void
 * @exception Exc_inner if $key too long
 */
function option_set($key, $value)
{
	if (strlen($key) > OPTION_KEY_LEN_MAX)
		throw new Exc_inner('option key too long');
	global $db, $DBOP, $_option_cache;
	$val  = array('value' => $value);
	$where = array($DBOP['=s'], 'key', $key);
	if ($db->get_number_of_rows('options', $where))
		$db->update_data('options', $val, $where);
	else
	{
		$val['key'] = $key;
		$db->insert_into('options', $val);
	}
	$_option_cache[$key] = $value;
}

/**
 * get remote address
 * @return string 
 */
function get_remote_addr()
{
	if (!isset($_SERVER['REMOTE_ADDR']))
		return 'mars';
	return $_SERVER['REMOTE_ADDR'];
}

/**
 * get the URL pointing to $file
 * @param string $file file path
 * @return string|NULL the URL, or NULL if can not get file path
 */
function get_page_url($file)
{
	global $website_root, $root_path;
	$file = realpath($file);
	if ($file === FALSE)
		return NULL;
	return $website_root . substr($file, strlen($root_path));
}

/**
 * @ignore
 */
function _xhtml_error_handler($errno, $msg)
{
	throw new Exc_xhtml($msg);
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
	try
	{
		global $_xhtml_error, $root_path;

		$text = '<' . _XHTML_ROOT . '>' . $text . '</' . _XHTML_ROOT . '>';
		$text = sprintf('<?xml version="1.0" standalone="no" ?>
			<!DOCTYPE %s SYSTEM "%s">
			', _XHTML_ROOT, 
			$root_path . 'contents/' . _XHTML_ROOT . '.dtd') . $text;
		$_xhtml_error = NULL;
		set_error_handler('_xhtml_error_handler');

		$doc = new DOMDocument;
		$doc->strictErrorChecking = FALSE;
		// setting $new->strictErrorChecking to TRUE seems not to work on my system

		$doc->encoding = "utf-8";
		$doc->validateOnParse = TRUE;
		if (!$doc->loadXML($text, LIBXML_DTDVALID))
			throw new Exc_xhtml(__('invalid document'));

		$list = $doc->getElementsByTagName(_XHTML_ROOT);
		if ($list->length > 1)
			throw new Exc_xhtml(__('disallowed tag: %s', _XHTML_ROOT));

		$list = $doc->getElementsByTagName('a');
		$str_js = 'javascript:';
		for ($i = 0; $i < $list->length; $i ++)
		{
			$attr = $list->item($i)->attributes;
			if (!is_null($attr))
			{
				$val = $attr->getNamedItem('href');
				if (!is_null($val))
				{
					$val = html_entity_decode($val->nodeValue);
					$val = strtolower($val);
					$tp = 0;
					for ($j = 0; $j < strlen($val) && $tp < strlen($str_js); $j ++)
					{
						$ch = $val[$j];
						if (ctype_alpha($ch) || $ch == ':')
						{
							if ($ch != $str_js[$tp])
								break;
							$tp ++;
						}
					}
					if ($tp == strlen($str_js))
						throw new Exc_xhtml(__('javascript is not allowed'));
				}
			}
		}
	}
	catch (Exc_xhtml $e)
	{
		restore_error_handler();
		throw $e;
	}
	restore_error_handler();
}

/**
 * get programming language name by id
 * @param int $lid language id
 * @return string|NULL language name or NULL if no such language
 */
function plang_get_name_by_id($lid)
{
	static $cache = array();
	if (array_key_exists($lid, $cache))
		return $cache[$lid];
	global $db, $DBOP;
	$row = $db->select_from('plang', 'name',
		array($DBOP['='], 'id', $lid));
	if (count($row) != 1)
		return $cache[$lid] = NULL;
	return $cache[$lid] = $row[0]['name'];
}

/**
 * get programming language type by id
 * @param int $lid language id
 * @return string|NULL the name or NULL if no such language
 */
function plang_get_type_by_id($lid)
{
	global $db, $DBOP;
	$row = $db->select_from('plang', 'type',
		array($DBOP['='], 'id', $lid));
	if (count($row) != 1)
		return NULL;
	return $row[0]['type'];
}

/**
 * validate an email address.
 * from http://www.linuxjournal.com/article/9585
 * @param string $email email address
 * @return void
 * @exception Exc_runtime if email address is invalid
 */
function email_validate($email)
{
	$atIndex = strrpos($email, "@");
	if (is_bool($atIndex) && !$atIndex)
		throw new Exc_runtime(__('invalid email address: no at symbol (@) found'));
	$domain = substr($email, $atIndex+1);
	$local = substr($email, 0, $atIndex);
	$localLen = strlen($local);
	$domainLen = strlen($domain);
	if ($localLen < 1 || $localLen > 64)
		throw new Exc_runtime(__('invalid email address: local part length exceeded'));
	if ($domainLen < 1 || $domainLen > 255)
		throw new Exc_runtime(__('invalid email address: domain part length exceeded'));
	if ($local[0] == '.' || $local[$localLen-1] == '.')
		throw new Exc_runtime(__('invalid email address: local part starts or ends with dot(.)'));
	if (preg_match('/\\.\\./', $local))
		throw new Exc_runtime(__('invalid email address: local part has two consecutive dots'));
	if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		throw new Exc_runtime(__('invalid email address: character not valid in domain part'));
	if (preg_match('/\\.\\./', $domain))
		throw new Exc_runtime(__('invalid email address: domain part has two consecutive dots'));
	if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
		str_replace("\\\\","",$local)))
	{
		if (!preg_match('/^"(\\\\"|[^"])+"$/',
			str_replace("\\\\","",$local)))
			throw new Exc_runtime(__('invalid email address: character not valid in local part unless local part is quoted'));
	}
	if (!option_get('email_validate_no_dns_check'))
	{
		if (!checkdnsrr($domain, 'MX'))
			// || !checkdnsrr($domain,"A")))
			throw new Exc_runtime(__('invalid email address: MX record not found in DNS'));
	}
}

/**
 * get a human readable string representing the Unix time stamp
 * @param int $time the Unix time stamp
 * @param bool $l10n whether to localize the output
 * @return string
 */
function time2str($time, $l10n = TRUE)
{
	if (!$l10n)
		return strftime('%a %b %d %H:%M:%S %Y %z', $time);
	return strftime('%a %b %d %H:%M:%S %Y %Z', $time);
}

/**
 * convert a time interval to human readable string
 * @param int $len the length of the interval in seconds
 *		if $len == -1, the time units used will be returned
 * @return string|array
 */
function time_interval_to_str($len)
{
	static $UNITS = NULL;
	if (is_null($UNITS))
	{
		$UNITS = array(
			array(60, __('second'), __('seconds')),
			array(60, __('minute'), __('minutes')),
			array(24, __('hour'), __('hours')),
			array(365, __('day'), __('days')),
			array(0, __('year'), __('years'))
		);
	}
	if ($len == -1)
		return $UNITS;
	foreach ($UNITS as $val)
	{
		if (!$len)
			break;
		$cur = $len;
		if ($val[0])
		{
			$cur %= $val[0];
			$len = floor($len / $val[0]);
		}
		$ret[] = $cur . ' ' . $val[1 + intval($cur >= 2)];
	}
	return implode(' ', array_reverse($ret));
}

/**
 * generate a javascript function that takes an integer as argument
 * and returns a string representing the time interval length
 * @param string $func the javascript function name
 * @return string the js function
 */
function time_interval_to_str_gen_js($func)
{
	$ret = "
		function  $func(len)
		{
			if (typeof($func.units) == 'undefined')
				$func.units = [";
			foreach (time_interval_to_str(-1) as $item)
			{
				$ret .= '[';
				foreach ($item as $v)
				{
					if (is_string($v))
						$ret .= '"';
					$ret .= $v;
					if (is_string($v))
						$ret .= '"';
					$ret .= ',';
				}
				$ret[strlen($ret) - 1] = ']';
				$ret .= ',';
			}

			$ret[strlen($ret) - 1] = ']';
			$ret .= ";
			var units = $func.units;";

			$ret .= '
				var ret = new Array();
			for (var i = 0; i < units.length && len; i ++)
			{
				var cur = len;
				if (units[i][0])
				{
					cur %= units[i][0];
					len = Math.floor(len / units[i][0]);
}
if (cur < 2)
	ret = ret.concat([cur + " " + units[i][1]]);
else
	ret = ret.concat([cur + " " + units[i][2]]);
}
ret.reverse();
return ret.join(" ");
}';
return $ret;
}

/**
 * transform a normal pattern to a database-recognizable pattern
 * patterns will follow rules below
 *		1. spaces at head or tail will be ignored
 *		2. a slash '\' in the end of pattern will be treated as a character '\'
 *		3. a slash '\' with '*', '?' or '\' after will be treated as a character '*', '?' or '\'
 *		4. other slash '\' is treated as a charater '\'
 *		5. '*' with no '\' in front is treated as a arbitrary string
 *		6. '?' with no '\' in front is treated as a arbitrary character
 * transforming rules @see includes/db/dbal.php : function select_from
 * @param string $tp the pattern to be transformed
 * @return string the pattern transformed
 */
function transform_pattern($tp)
{
	if (!is_string($tp))
		return NULL;
	$tp = trim($tp);
	$len = strlen($tp);
	$s = '';
	for ($i = 0; $i < $len; $i ++)
		if ($tp[$i] == '\\')
		{
			$i ++;
			if ($i < $len)
			{
				$ch = $tp[$i];
				if ($ch == '*' || $ch == '?' || $ch == '\\')
					$s .= ($ch == '\\' ? '\\\\' : $ch);
				else if ($ch == '_' || $ch == '%')
					$s .= '\\\\\\' . $ch;
				else
					$s .= '\\\\' . $ch;
			}
			else
				$s .= '\\\\';
		}
		else
		{
			$ch = $tp[$i];
			if ($ch == '%' || $ch == '_')
				$s .= '\\' . $ch;
			else if ($ch == '*')
				$s .= '%';
			else if ($ch == '?')
				$s .= '_';
			else
				$s .= $ch;

		}
	$s = '%' . $s . '%';
	return $s;
}

/**
 * get a random id containing only letters and digits, and begins with a letter
 * @return string
 */
function get_random_id()
{
	return 'i' . md5(uniqid(mt_rand(), TRUE));
}

/**
 * get a id which contains only letters and digits, and begins with a letter, and is unique
 * during the execution of the script
 */
function get_unique_id()
{
	static $id = 0;
	return 'ud' . ($id ++);
}

/**
 * get a string containig chars from $low to $high
 * @param string $low
 * @param string $high
 * @return string
 */
function str_range($low, $high)
{
	if (!is_string($low) || !is_string($high))
		return NULL;
	$low = $low[0];
	$high = $high[0];
	$ret = '';
	while ($low <= $high)
		$ret .= $low ++;
	return $ret;
}

/**
 * Set Error Code like 404(Not Found),500(Internal Error)
 */
function set_error($code,$info)
{
	$code = (int)($code);
	header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$info);
}
