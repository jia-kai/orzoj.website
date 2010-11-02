<?php
/*
 * $File: post_func.php
 * $Date: Tue Nov 02 17:21:59 2010 +0800
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
function post_list_pack_arg($start_page, $type, $uid, $subject, $author, $prob_id = NULL, $action = NULL)
{
	global $POST_TYPE_SET;
	$s = '';
	if (is_string($start_page))
		$start_page = intval($start_page);
	if (is_int($start_page))
		$s .= "start_page=$start_page";
	else $s .= "start_page=1";

	if (!is_string($type) || 
		(is_string($type) && (array_search($type, $POST_TYPE_SET) === FALSE)))
		$type = NULL;
	else $s .= "|type=$type";

	if (is_int($uid))
		$s .= "|uid=$uid";

	if (is_string($subject) && strlen($subject))
		$s .= "|subject=$subject";

	if (is_string($author) && strlen($author))
		$s .= "|author=$author";

	if (!is_null($prob_id))
		$s .= "|prob_id=$prob_id";

	if (is_string($action))
		$s .= "|action=$action";

	return $s;
}

/**
 * @ignore
 */
function post_list_get_a_href($start_page, $type, $uid, $subject, $author, $prob_id = NULL, $action = NULL)
{
	$arg = post_list_pack_arg($start_page, $type, $uid, $subject, $author, $prob_id);
	return t_get_link('show-ajax-post-list', $arg, TRUE, TRUE);
}

/**
 * @ignore
 */
function post_list_get_a_onclick($start_page, $type, $uid, $subject, $author, $prob_id = NULL, $action = NULL)
{
	$arg = post_list_pack_arg($start_page, $type, $uid, $subject, $author, $prob_id);
	return 'posts_view_set_content(\'' . t_get_link('ajax-post-list', $arg, FALSE, TRUE) . '\'); return false;';
}
/**
 * @ignore
 */
function post_view_single_pack_arg($tid, $start_page, $post_list_start_page, $type, $uid, $subject, $author, $prob_id = NULL, $action = NULL)
{
	global $POST_TYPE_SET;
	$s = '';
	$tid = intval($tid);
	$s .= "tid=$tid";

	if (is_string($start_page))
		$start_page = intval($start_page);
	$s .= "|start_page=$start_page";

	if (is_int($post_list_start_page))
		$s .= "|post_list_start_page=$post_list_start_page";
	else $s .= "|post_list_start_page=1";

	if (!is_string($type) || 
		(is_string($type) && (array_search($type, $POST_TYPE_SET) === FALSE)))
		$type = NULL;
	else $s .= "|post_list_type=$type";

	if (is_int($uid))
		$s .= "|post_list_uid=$uid";

	if (is_string($subject) && strlen($subject))
		$s .= "|post_list_subject=$subject";

	if (is_string($author) && strlen($author))
		$s .= "|post_list_author=$author";

	if (is_int($prob_id))
		$s .= "post_list_prob_id=$prob_id";

	if (is_string($action) && strlen($action))
		$s .= "|action=$action";

	return $s;
}

/**
 * @ignore
 */
function post_view_single_get_a_href($id, $start_page, $post_list_start_page, $type, $uid, $subject, $author, $prob_id, $action = NULL)
{
	$arg = post_view_single_pack_arg($id, $start_page, $post_list_start_page, $type, $uid, $subject, $author, $prob_id, $action);
	return t_get_link('show-ajax-post-view-single', $arg, TRUE, TRUE);
}

/**
 * @ignore
 */
function post_view_single_get_a_onclick($id, $start_page, $post_list_start_page, $type, $uid, $subject, $author, $prob_id, $action = NULL)
{
	$arg = post_view_single_pack_arg($id, $start_page, $post_list_start_page, $type, $uid, $subject, $author, $prob_id, $action);

	return 'posts_view_set_content(\'' . t_get_link('ajax-post-view-single', $arg, FALSE, TRUE) . '\'); return false;';
}

