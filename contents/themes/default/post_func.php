<?php
/*
 * $File: post_func.php
 * $Date: Sat Oct 30 12:01:42 2010 +0800
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
/**
 * @ignore
 */
function post_list_pack_arg($start_page, $post_type, $post_uid, $subject, $author)
{
	global $POST_TYPE_SET;
	$s = '';
	if (is_string($start_page))
		$start_page = intval($start_page);
	if (is_int($start_page))
		$s .= "start_page=$start_page";
	else $s .= "start_page=1";

	if (is_string($post_type))
		$post_type = array($post_type);
	if (is_array($post_type))
		$post_type = array_intersect($post_type, $POST_TYPE_SET);
	else $post_type = NULL;
	if (is_array($post_type))
		foreach ($post_type as $type)
			$s .= "|type=$type";

	if (is_int($post_uid))
		$s .= "|uid=$post_uid";

	if (is_string($subject) && strlen($subject))
		$s .= "|subject=$subject";

	if (is_string($author) && strlen($author))
		$s .= "|author=$author";

	return $s;
}

/**
 * @ignore
 */
function post_list_get_a_href($start_page, $post_type, $post_uid, $subject, $author)
{
	$arg = post_list_pack_arg($start_page, $post_type, $post_uid, $subject, $author);
	return t_get_link('show-ajax-post-list', $arg, TRUE, TRUE);
}

/**
 * @ignore
 */
function post_list_get_a_onclick($start_page, $post_type, $post_uid, $subject, $author)
{
	$arg = post_list_pack_arg($start_page, $post_type, $post_uid, $subject, $author);
	return 'posts_view_set_content(\'' . t_get_link('ajax-post-list', $arg, FALSE, TRUE) . '\'); return false;';
}
/**
 * @ignore
 */
function post_view_single_from_list_pack_arg($id, $post_list_start_page, $post_type, $post_uid, $subject, $author)
{
	global $POST_TYPE_SET;
	$s = '';
	if (is_string($id))
		$id = intval($id);
	if (is_int($id))
		$s .= "id=$id";
	else throw new Exc_runtime(__('Invalid id...'));

	if (is_int($post_list_start_page))
		$s .= "|post_list_start_page=$post_list_start_page";
	else $s .= "|post_list_start_page=1";

	if (is_string($post_type))
		$post_type = array($post_type);
	if (is_array($post_type))
		$post_type = array_intersect($post_type, $POST_TYPE_SET);
	else $post_type = NULL;
	if (is_array($post_type))
		foreach ($post_type as $type)
			$s .= "|type=$type";

	if (is_int($post_uid))
		$s .= "|uid=$post_uid";

	if (is_string($subject) && strlen($subject))
		$s .= "|subject=$subject";

	if (is_string($author) && strlen($author))
		$s .= "|author=$author";

	return $s;
}

/**
 * @ignore
 */
function post_view_single_from_list_get_a_href($id, $post_list_start_page, $post_type, $post_uid, $subject, $author)
{
	$arg = post_view_single_from_list_pack_arg($id, $post_list_start_page, $post_type, $post_uid, $subject, $author);
	return t_get_link('show-ajax-post-view-single', $arg, TRUE, TRUE);
}

/**
 * @ignore
 */
function post_view_single_from_list_get_a_onclick($id, $post_list_start_page, $post_type, $post_uid, $subject, $author)
{
	$arg = post_view_single_from_list_pack_arg($id, $post_list_start_page, $post_type, $post_uid, $subject, $author);
	return 'posts_view_set_content(\'' . t_get_link('ajax-post-view-single', $arg, FALSE, TRUE) . '\'); return false;';
}

