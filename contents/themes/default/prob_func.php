<?php
/*
 * $File: prob_func.php
 * $Date: Tue Oct 19 01:06:17 2010 +0800
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

function prob_view_by_group_get_a_onclick($gid, $start_page, $sort_col = 'id', $sort_way = 'ASC', $in_HTML = TRUE)
{
	$arg = sprintf('%d|%d|%s|%s', $gid, $start_page, $sort_col, $sort_way);
	return 'prob_view_set_content(\'' . t_get_link('ajax-prob-view-by-group', $arg, $in_HTML, TRUE) . '\'); return false;';
}

function _get_int(&$pos, $str, $len)
{
	$ret = 0; $flag = false;
	if ($pos >= $len)
		throw new Exc_orzoj();
	if ($str[$pos] == '|')
		$pos ++;
	while ($pos < $len && ($str[$pos] >= '0' && $str[$pos] <= '9'))
	{
		$ret = $ret * 10 + ($str[$pos ++]) - '0';
		$flag = true;
	}
	if (!$flag)
		throw new Exc_orzoj();
	return $ret;
}

function _get_string(&$pos, $str, $len)
{
	$ret = ''; $flag = false;
	if ($pos >= $len)
		throw new Exc_orzoj();
	if ($str[$pos] == '|')
		$pos ++;
	while ($pos < $len && $str[$pos] != '|')
	{
		$ret .= $str[$pos ++];
		$flag = true;
	}
	if (!$flag)
		throw new Exc_orzoj();
	return $ret;
}

function prob_view_by_group_parse_arg()
{
	global $gid, $start_page, $page_arg, $sort_col, $sort_way;
	if (is_null($page_arg))
	{
		$gid = NULL;
		$start_page = 1;
		$sort_col = 'id';
		$sort_way = 'ASC';
		return;
	}
	try
	{
		$len = strlen($page_arg);
		$pos = 0;
		$gid = _get_int($pos, $page_arg, $len);
		$start_page = _get_int($pos, $page_arg, $len);
		$sort_col = _get_string($pos, $page_arg, $len);
		$sort_way = _get_string($pos, $page_arg, $len);
	}
   	catch (Exc_orzoj $e)
	{
		die('Hello, argument is wrong.');
	}
	if ($gid == 0)
		$gid = NULL;
	if ($start_page < 1)
		$start_page = 1;
}

function prob_view_single_pack_arg($pid, $gid, $start_page, $sort_col, $sort_way)
{
	if (is_null($gid))
		$gid = 0;
	return "$pid|$gid|$start_page|$sort_col|$sort_way";
}

function prob_view_single_get_a_href($pid, $gid, $start_page)
{
	$arg = prob_view_single_pack_arg($pid, $gid, $start_page, 'id', 'ASC');
	return t_get_link('show-ajax-prob-view-single', $arg, TRUE, TRUE);
}

function prob_view_single_get_a_onclick($pid, $gid, $start_page, $sort_col, $sort_way, $in_HTML = TRUE)
{
	$arg = prob_view_single_pack_arg($pid, $gid, $start_page, $sort_col, $sort_way);
	return 'prob_view_set_content(\'' . t_get_link('ajax-prob-view-single', $arg, $in_HTML, TRUE) . '\'); return false;';
}

function prob_view_single_parse_arg()
{
	global $pid, $gid, $start_page, $page_arg, $sort_col, $sort_way;
	if (!isset($pid))
	{
		try
		{
			$len = strlen($page_arg);
			$pos = 0;
			$pid = _get_int($pos, $page_arg, $len);
			$gid= _get_int($pos, $page_arg, $len);
			$start_page  = _get_int($pos, $page_arg, $len);
			$sort_col = _get_string($pos, $page_arg, $len);
			$sort_way = _get_string($pos, $page_arg, $len);
		} catch (Exc_orzoj $e)
			{
				die('What ?');
		}
	}
}

