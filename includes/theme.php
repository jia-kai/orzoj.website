<?php
/* 
 * $File: theme.php
 * $Date: Thu Oct 14 19:58:40 2010 +0800
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


/**
 * echo contents to be put in the html head tag
 * @return void
 */
function t_get_html_head()
{
	global $website_name;
	$str = <<<EOF
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="description" content="$website_name" />
	<meta name="keywords" content="oj, orz, oi, acm, online judge, ioi, noi, noip, apio, ctsc, poi, boi, ceoi" />
	<meta name="robots" content="all" />
	<meta name="MSSmartTagsPreventParsing" content="true" />
	<meta http-equiv="imagetoolbar" content="false" />
EOF
	;
	echo filter_apply('after_html_head', $str);
}

/**
 * echo footer
 * @return void
 */
function t_get_footer()
{
	global $db, $PAGE_START_TIME, $ORZOJ_VERSION;
	$str = "<br />" .
		__('%d database queries | Page execution time: %d milliseconds |' .
		' Powerd by <a href="http://code.google.com/p/orzoj/" target="_blank">Orz Online Judge</a> %s<br />',
		$db->get_query_amount(),
			(microtime(TRUE) - $PAGE_START_TIME) * 1000, $ORZOJ_VERSION);
	echo filter_apply('after_footer', $str);
}

/**
 * image directory of default theme
 */
$T_DEFAULT_IMG_PATH = get_page_url($root_path . 'contents/theme/default/images/');

/**
 * echo or return a URL to visit the page
 *
 * before including index.php in a theme, global variable $cur_page and $page_arg
 * will be set according to the link
 * @param string $page
 * @param string|NULL $arg
 * @param bool $in_HTML whether this link address is used in HTML
 *		(because in HTML, '&' needed to be encoded as '&amp;') (only meaningful when $arg != NULL)
 * @param bool $return_string whether to return the address or echo it
 * @return void|string
 */
function t_get_link($page, $arg = NULL, $in_HTML = TRUE, $return_string = FALSE)
{
	// TODO: Make rewrite better and extendable.
	global $root_path, $webserver, $website_root;
	if ($webserver != WEBSERVER_APACHE || defined('DISABLE_URI_REWRITE'))
	{
		$str = get_page_url($root_path . 'index.php') . '?page=' . urlencode($page);
		if (!is_null($arg))
		{
			$str .= '&';
			if ($in_HTML)
				$str .= 'amp;';
			$str .= 'arg=' . urlencode($arg);
		}
	}
	else
	{
		$str = $website_root;
		$str .= urlencode($page) . '/';
		if (is_string($arg))
			$str .= urlencode($arg);
	}
	if ($return_string)
		return $str;
	echo $str;
}


