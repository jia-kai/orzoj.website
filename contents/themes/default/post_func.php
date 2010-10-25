<?php
/*
 * $File: post_func.php
 * $Date: Mon Oct 25 12:06:17 2010 +0800
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

function post_view_list_pack_arg($start_page)
{
	return $start_page;
}

function post_view_list_parse_arg()
{
	global $start_page, $page_arg;
	if (sscanf($page_arg, '%d', $start_page) != 1)
		die('Can not parse argument.');
}

function post_view_list_get_a_href($start_page)
{
	return t_get_link('show-ajax-post-list', "$start_page", TRUE, TRUE);
}


function post_view_list_get_a_onclick($start_page)
{
	$arg = post_view_list_pack_arg($start_page);
	return 'post_view_set_content(\'' . 
		t_get_link('ajax-post-list', post_view_list_pack_arg($start_page), FALSE, TRUE)
		. '\'); return false;';
}



function post_view_single_pack_arg($id, $start_page)
{
	if ($start_page == NULL)
		$start_page = 1;
	return "$id|$start_page";
}

function post_view_single_parse_arg()
{
	global $id, $start_page, $page_arg;
	if (sscanf($page_arg, '%d|%d', $id, $start_page) != 2)
		die('Can not parse argument.');
}

function post_view_single_get_a_href($id)
{
	return t_get_link('posts', $id, TRUE, TRUE);
}

function post_view_single_get_a_onclick($id, $start_page = NULL)
{
	$arg = post_view_single_pack_arg($id, $start_page);
	return 'post_view_set_content(\'' . 
		t_get_link('ajax-post-view-single', $arg, FALSE, TRUE)
		. '\'); return false;';
}

