<?php
/* 
 * $File: l10n.php
 * $Date: Tue Oct 26 22:01:10 2010 +0800
 */
/**
 * @package orzoj-website
 * @subpackage l10n
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

$translators = array();

$translations = array();

$_l10n_init_done = FALSE;

/**
 * Get translation
 * @param string $fmt format, like that of printf in C
 * @param mixed ...
 * @return string translation
 */
function _gettext($fmt)
{
	global $translators, $translations, $_l10n_init_done;
	if (!$_l10n_init_done)
		l10n_init_wlang();
	static $called = false;
	$args = func_get_args();
	if (!$called)
	{
		foreach ($translators as $translator)
		{
			$content = json_decode(file_get_contents($translator),TRUE);
			//$content = eval('return '.file_get_contents($translator).';');
			//$content = unserialize(file_get_contents($translator));
			$translations = array_merge($translations,$content);
		}
		$called = true;
	}
	if (isset($translations[$fmt]))
		$args[0] = $translations[$fmt];
	return call_user_func_array('sprintf', $args);
}



/**
 * Get translation
 * @param string $fmt format 
 * @param mixed ...
 * @return string translation
 * @see _gettext
 */
function __($fmt)
{
	$args = func_get_args();
	return call_user_func_array('_gettext', $args);
}

/**
 * Add php file for translation
 * @param string $file filename
 */
function l10n_add_file($filename)
{
	global $translators;
	if (is_readable($filename))
		$translators[] = $filename;

}

/**
 * Clean translation files list
 * @return void
 */
function l10n_clean_files()
{
	global $translations, $translators;
	$translators = array();
	$translations = array();
}

/**
 * Add an directory for translation
 * @param string $dir directory
 */
function l10n_add_directory($dir)
{
	$dr = @opendir($dir);
	if ($dr)
	{
		while (($file = readdir($dr)) !== false)
		{
			switch (strrchr(strtolower($file), '.'))
			{
				case '.php':
					l10n_add_file($dir . $file);
					break;
			}
		}
	}
}


/**
 * initialize website language before conecting database
 * @return void
 */
function l10n_init_wlang_before_db()
{
	global $root_path, $_l10n_init_done;
	if ($_l10n_init_done)
		return;
	$_l10n_init_done = TRUE;
	if (isset($_SERVER['HTTP_USER_AGENT']))
		$ua = $_SERVER['HTTP_USER_AGENT'];
	else
		$ua = '';
	if (preg_match('/([a-z][a-z]-[A-Z][A-Z])/',$ua,$matches))
	{
		$locale = str_replace('-','_',$matches[0]);
	}
	else
		$locale = 'en_US';
	l10n_clean_files();
	l10n_add_directory($root_path . 'contents/lang/' . $locale . '/');
}

/**
 * initialize website language
 * @return void
 */
function l10n_init_wlang()
{
	global $db, $user, $DBOP, $root_path, $_l10n_init_done;
	if (!isset($db) || !function_exists('user_check_login'))
	{
		l10n_init_wlang_before_db();
		return;
	}
	if ($_l10n_init_done)
		return;
	$_l10n_init_done = TRUE;
	if (user_check_login())
		$id = $user->wlang;
	else
	{
		$ua = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match('/([a-z][a-z]-[A-Z][A-Z])/',$ua,$matches))
		{
			$locale = str_replace('-','_',$matches[0]);
		}
	}
	if (!isset($locale) && !isset($id))
	{
		$id = DEFAULT_WLANG_ID;
	}
	if (isset($id))
	{
		$result = $db->select_from('wlang', array('file'), array($DBOP['='], 'id', $id));
		$locale = $result[0]['file'];
	}
	l10n_clean_files();
	l10n_add_directory($root_path . 'contents/lang/' . $locale . '/');
}

