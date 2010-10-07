<?php
/* 
 * $File: theme.php
 * $Date: Thu Oct 07 13:15:49 2010 +0800
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

$ORZOJ_PAGE_VARS = array(
	'title' => $web_site_name,
	'html_head' => <<<EOF
	<meta name="description" content="$web_site_name" />
	<meta name="keywords" content="oj, orz, oi, acm, online judge, ioi, noi, noip, apio, ctsc, poi, boi, ceoi" />
EOF
	,
	
	'footer' => "Powerd by <a href='http://code.google.com/p/orzoj/'>Orz Online Judge $ORZOJ_VERSION</a>"
);

