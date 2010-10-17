<?php
/* 
 * $File: index.php
 * $Date: Sun Oct 17 18:59:51 2010 +0800
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

$theme_name = '';
$theme_path = '';

require_once 'pre_include.php';
require_once $includes_path . 'theme.php';

/**
 * @ignore
 */
function _index_set_theme($name = NULL)
{
	global $theme_path, $root_path;
	if ($name == NULL)
		$name = DEFAULT_THEME;
	$theme_path = $root_path . 'contents/themes/' . $name . '/';
}

// TODO: user custom theme
//_index_set_theme(user_check_login() ? $user->theme_id : NULL);
_index_set_theme();

/*
if (user_check_login())
{
	$result = $db->select_from('wlang', array('file'), array($DBOP['='], 'id', $user->wlang));
	l10n_add_directory($root_path . 'contents/lang/' . $result[0]['file'] . '/');
	unset($result);
}
else
{
	$result = $db->select_from('wlang', array('file'), array($DBOP['='], 'id', DEFAULT_WLANG_ID));
	l10n_add_directory($root_path . 'contents/lang/' . $result[0]['file'] . '/');
	unset($result);
}
 */

require_once $theme_path . 'functions.php';
user_init_form();

/*
 * TODO: Make rewrite more extendable. 
 */
if (isset($_GET['page']))
	$cur_page = $_GET['page'];
else $cur_page = 'index';

if (isset($_GET['arg']))
	$page_arg = $_GET['arg'];
else $page_arg = NULL;

require_once  $theme_path . 'index.php';

