<?php
/* 
 * $File: l10n.php
 * $Date: Mon Oct 18 23:01:42 2010 +0800
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
if (!defined('IN_ORZOJ')) exit;

require_once $includes_path . 'pomo/mo.php';
require_once $includes_path . 'pomo/po.php';

$translators = array();

$translations = array();

/**
 * Get translation
 * @param string $fmt format, like that of printf in C
 * @param mixed ...
 * @return string translation
 */
function _gettext($fmt)
{
	global $translators,$translations;
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
 * Add an directory for translation
 * @param string $dir directory
 */
function l10n_add_directory($dir)
{
	$dr = opendir($dir);
	if ($dr)
	{
		while (($file = readdir($dr)) !== false)
		{
			switch (strstr(strtolower($file),'.'))
			{
			case '.php':
				l10n_add_file($dir . $file);
				break;
			}
		}
	}
}

