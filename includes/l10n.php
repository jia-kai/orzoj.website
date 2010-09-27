<?php
/* 
 * $File: l10n.php
 * $Date: Mon Sep 27 19:25:22 2010 +0800
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

/**
 * Get translation
 * @param string $fmt format, like that of printf in C
 * @param mixed ...
 * @return string translation
 */
function _gettext($fmt)
{
	global $translators;
	$args = func_get_args();
	$res = $fmt;
	foreach ($translators as $key => $translator)
	{
		$current = $translator['class']->translate($fmt); 
		if ($current != $fmt)
		{
			$res = $current;
			break;
		}
	}
	$args[0] = $res;
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
 * Add a new .mo file as a translation source.
 * @param string $filename path and name of .mo file
 * @param bool $use_cache whether use in memory cache or not
 * @see l10n_add_po_file
 */
function l10n_add_mo_file($filename,$use_cache = true)
{
	global $translators;
	$insert_id = count($translators);
	$newmo = new MOReader;
	$newmo->use_cache = $use_cache;
	$newmo->filename = $filename;
	$translators[$insert_id] = array(
		'type' => 'mo',
		'class' => $newmo
	);
}


/**
 * Add a new .po file as a translation source.
 * @param string $filename path and name of .po file
 * @param bool $use_cache whether use in memory or not
 * @see l10n_add_mo_file
 */
function l10n_add_po_file($filename,$use_cache = true)
{
	global $translators;
	$insert_id = count($translators);
	$newmo = new POReader;
	$newmo->use_cache = $use_cache;
	$newmo->filename = $filename;
	$translators[$insert_id] = array(
		'type' => 'po',
		'class' => $newmo
	);
}


