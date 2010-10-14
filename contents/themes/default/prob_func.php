<?php
/*
 * $File: prob_func.php
 * $Date: Thu Oct 14 21:07:36 2010 +0800
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

function prob_view_by_group_get_a_href($gid, $start_page)
{
	$arg = sprintf('%d|%d', $gid, $start_page);
	return t_get_link('show-ajax-prob-view-by-group', $arg, TRUE, TRUE);
}

function prob_view_by_group_get_a_onclick($gid, $start_page)
{
	$arg = sprintf('%d|%d', $gid, $start_page);
	return 'prob_view_set_content(\'' . t_get_link('ajax-prob-view-by-group', $arg, FALSE, TRUE) . '\'); return false;';
}

function prob_view_by_group_parse_arg()
{
	global $gid, $start_page, $page_arg;
	if (is_null($page_arg))
	{
		$gid = 0;
		$start_page = 1;
		return;
	}
	if (sscanf($page_arg, '%d|%d', $gid, $start_page) != 2)
		die('Hello, argument is wrong.');
	if ($start_page < 1)
		$start_page = 1;
}

function prob_view_single_pack_arg($pid, $gid, $start_page)
{
	return "$pid|$gid|$start_page";
}

function prob_view_single_get_a_href($pid, $gid, $start_page)
{
	$arg = prob_view_single_pack_arg($pid, $gid, $start_page);
	return t_get_link('show-ajax-prob-view-single', $arg, TRUE, TRUE);
}

function prob_view_single_get_a_onclick($pid, $gid, $start_page)
{
	$arg = prob_view_single_pack_arg($pid, $gid, $start_page);
	return 'prob_view_set_content(\'' . t_get_link('ajax-prob-view-single', $arg, FALSE, TRUE) . '\'); return false;';
}

function prob_view_single_parse_arg()
{
	global $pid, $gid, $start_page, $page_arg;
	if (!isset($pid))
	{
		if (sscanf($page_arg, '%d|%d|%d', $pid, $gid, $start_page) != 3)
			die('what ?');
	}
}

