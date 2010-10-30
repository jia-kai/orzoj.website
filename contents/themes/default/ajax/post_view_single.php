<?php
/*
 * $File: post_view_single.php
 * $Date: Sat Oct 30 11:34:31 2010 +0800
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

/*
 * page argument:<id=int>|<start_page=int>
 *		id: int
 *			the id of post
 *		start_page: int
 *			the start page of a single post
 */

$start_page = 1;
$id = NULL;
if (isset($page_arg))
{
	$options = explode('|', $page_arg);
	foreach ($options as $option)
	{
		$expr = explode('=', $option);
		if (count($expr) != 2)
			die(__('Invalid page argument.'));
		switch ($expr[0])
		{
		case 'id':
			$id = intval($expr[1]);
			break;
		case 'start_page':
			$start_page = intval($expr[1]);
			break;
		}
	}
}

