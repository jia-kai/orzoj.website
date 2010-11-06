<?php
/*
 * $File: pages.php
 * $Date: Sat Nov 06 17:28:03 2010 +0800
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

$sys_page = pages_get_detail($page_arg);
if ($sys_page)
{
	echo '<h2 align="center">'.$sys_page->title.'</h2>';
	echo $sys_page->content;
}
else
{
	set_error(404,'Not Found');
	require_once("404.php");
}
