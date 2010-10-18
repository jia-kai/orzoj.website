<?php
/* 
 * $File: index.php
 * $Date: Mon Oct 18 19:37:51 2010 +0800
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
require_once $includes_path . 'user.php';

/*
 * Detect web server.
 * Copied from wordpress.
 */
if (isset($_SERVER['SERVER_SOFTWARE']))
{
	$is_Apache = (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false);
	$is_IIS = (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'ExpressionDevServer') !== false);
	$is_IIS7 = $is_iis7 = (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS/7.') !== false);

	if ($is_Apache) $webserver = WEBSERVER_APACHE;
	else if ($is_IIS7) $webserver = WEBSERVER_IIS7;
	else if ($is_IIS) $webserver = WEBSERVER_IIS;
	else $webserver = WEBSERVER_OTHERS;

	unset($is_Apache,$is_IIS,$is_IIS7);
}

/*
 * Detect UA Browser.
 * Copied from wordpress.
 */
$userbrowser = USER_BROWSER_OTHERS;

if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx') !== false ) {
		$userbrowser = USER_BROWSER_LYNX;
	} elseif ( stripos($_SERVER['HTTP_USER_AGENT'], 'chrome') !== false ) {
		$userbrowser = USER_BROWSER_CHROME;
	} elseif ( stripos($_SERVER['HTTP_USER_AGENT'], 'safari') !== false ) {
		$userbrowser = USER_BROWSER_SAFARI;
	} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') !== false ) {
		$userbrowser = USER_BROWSER_GECKO;
	} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false ) {
		$userbrowser = USER_BROWSER_MSIE;
	} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false ) {
		$userbrowser = USER_BROWSER_OPERA;
	} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Nav') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/4.') !== false ) {
		$userbrowser = USER_BROWSER_NETSCAPE;
	}
}

if ( $userbrowser == USER_BROWSER_SAFARI && stripos($_SERVER['HTTP_USER_AGENT'], 'mobile') !== false )
	$is_iphone = USER_BROWSER_IPHONE;

/*
 * Detect UA Browser finished.
 */

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


// XXX: for debug only
$db->record_query = TRUE;

require_once  $theme_path . 'index.php';

