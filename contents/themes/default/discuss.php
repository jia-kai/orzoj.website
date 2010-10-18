<?php
/* 
 * $File: discuss.php
 * $Date: Mon Oct 18 14:58:15 2010 +0800
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

require_once $includes_path . 'post.php';

$POSTS_PER_PAGE = 20;

// parse arg
if (isset($page_arg))
{
	if (sscanf($page_arg, '%c', $char) != 1)
		die(__('Can not parse arg at discuss.php. '));
	if ($char == 's') // single
	{
		if (sscanf($page_arg, 's%d', $id) != 1)
			die(__('Can not parse arg while trying to view single.'));
		require_once $theme_path . 'ajax/post_view_single.php';
	}
	else if ($char == 'l') // list
	{
		if (sscanf($page_arg, 'l%d', $start_page) != 1)
			die(__('Can not parse arg while trying to view list.'));
		require_once $theme_path . 'ajax/post_list.php';
	}
	else
}
else
	require_once $theme_path . 'ajax/post_list.php';
?>
