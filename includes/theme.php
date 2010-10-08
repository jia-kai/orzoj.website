<?php
/* 
 * $File: theme.php
 * $Date: Fri Oct 08 15:43:48 2010 +0800
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
 * get contents to be put in the html head tag
 * @return string
 */
function t_get_html_head()
{
	global $website_name;
	$str = <<<EOF
	<meta name="description" content="$website_name" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="oj, orz, oi, acm, online judge, ioi, noi, noip, apio, ctsc, poi, boi, ceoi" />
	<meta name="robots" content="all" />
	<meta name="MSSmartTagsPreventParsing" content="true" />
	<meta http-equiv="imagetoolbar" content="false" />
EOF
	;
	return filter_apply('after_html_head', $str);
}

/**
 * get footer
 * @return string
 */
function t_get_footer()
{
	global $db, $PAGE_START_TIME, $ORZOJ_VERSION;
	$str = "<br />" .
		__('%d database queries | Page execution time: %d milliseconds |' .
	   ' Powerd by <a href="http://code.google.com/p/orzoj/">Orz Online Judge</a> %s<br />', $db->get_query_amount(),
			(microtime(TRUE) - $PAGE_START_TIME) * 1000, $ORZOJ_VERSION);
	return filter_apply('after_footer', $str);
}

/**
 * get logo address
 */