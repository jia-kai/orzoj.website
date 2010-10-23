<?php
/* 
 * $File: post.php
 * $Date: Sat Oct 23 14:58:08 2010 +0800
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


$POST_VAL_SET = array('id', 'time', 'uid', 'pid', 'prob_id', 'rid', 'subject', 'content'); 
/**
 * @ignore
 */
function _post_get_root_id($pid)
{
	global $db, $DBOP;
	$ret = $db->select_from('posts', array('rid'), array($DBOP['='], 'id', $pid));
	return $ret[0]['rid'];
}

/**
 * @ignore
 */
function _post_update_root_post($id, $uid)
{
	global $db, $DBOP;
	$db->update_data('posts', 
		array('last_reply_time' => time(), 'last_reply_user' => $uid), 
		array($DBOP['='], 'id', $id));
}

/**
 * add a post, using the data posted by the form
 * @param int $prob_id related problem id
 * @param int $pid parent post id
 * @return int the new post id 
 * @exception Exc_runtime
 */
function post_add($prob_id, $pid = 0)
{
	global $db, $DBOP, $user;
	if (!user_check_login())
		throw new Exc_runtime(__('Please login first'));
	if (!isset($_POST['subject']))
		throw new Exc_runtime(__('No post subject'));
	if (strlen($_POST['subject']) > POST_SUBJECT_LEN_MAX)
		throw new Exc_runtime(__('Subject is too long'));
	$content = tf_form_get_rich_text_editor_data('content');

	$val = array('subject' => htmlencode($_POST['subject']),
		'content' => $content, 'time' => time(), 'uid' => $user->id,
		'pid' => $pid, 'prob_id' => $prob_id, 'rid' => 0);

	if ($pid > 0)
	{
		$val['rid'] = _post_get_root_id($pid);
		_post_update_root_post($val['rid'], $user->id);
	}
	$val = filter_apply('before_post_add', $val);
	return $db->insert_into('posts', $val);
}

/**
 * echo form fileds for adding a new post
 * @return void
 */
function post_add_get_form($id)
{
	$str =
		tf_form_get_text_input(__('Subject:'), 'subject') .
		tf_form_get_rich_text_editor(__('Content:'), 'content') .
		tf_form_get_text_input(__('Problem id'), 'prob_id', NULL, "$id");
	echo filter_apply('after_post_add_form', $str);
}

/**
 * get post list in a specific limitation
 * @param bool $concrete TRUE means need content of the post
 * @param bool|NULL $is_top specific whether the post is toped. NULL means no limit.
 * @param int|NULL $offset 
 * @param int|NULL $count how many post do you want, this does not include post replies
 * @param int|NULL $depth depth of the post you want
 * @param int|NULL $uid if this is specified, post published by a certain user will be returned.
 * @param string $post_sort_way 'ASC' or 'DESC', sort by last_reply_time
 * @param string $post_reply_sort_way 'ASC' or 'DESC', sort by time
 * @return array an recursive array of post, in each array, index 0 stores the post in a array, keys see install/tables.php
 *		and from index 1 to end is array, except the first layer. the first layer will be a 
 *		array of array.
 */
function post_get_post_list($concrete = FALSE, $is_top = NULL, $offset = NULL, $count = NULL, $depth = NULL, $uid = NULL, $post_sort_way = 'DESC', $post_reply_sort_way = 'ASC')
{
	global $db, $DBOP, $POST_VAL_SET;
	$where = array($DBOP['='], 'pid', 0);
	if ($is_top !== NULL)
		db_where_add_and($where, array($DBOP['='], 'is_top', ($is_top ? 1 : 0)));
	if ($uid != NULL)
		db_where_add_and($where, array($DBOP['='], 'uid', $uid));
	$ret = array();
	$posts = $db->select_from(
		'posts', 
		array('id'),
		$where,
		array('last_reply_time' => $post_sort_way),
		$offset,
		$count
	);
	foreach ($posts as $post)
		$ret[] = _build_post_list($post['id'], $concrete, $post_reply_sort_way, $depth);
	return filter_apply('after_post_list', $ret);
}

/**
 * @ignore
 */
function _post_top_amount()
{
	global $db, $DBOP;
	return $db->get_number_of_rows('posts', array($DBOP['='], 'is_top', 1));
}
/**
 * get list of post in a specific limitaion, top post included
 * @see post_get_post_list()
 */
function post_get_list($concrete = FALSE, $offset = NULL, $count = NULL, $depth = NULL, $uid = NULL, $post_sort_way = 'DESC', $post_reply_sort_way = 'ASC')
{
	if (user_check_login())
	{
		// XXX
		// view permission check
	}
	else
	{
		// XXX
	}
	$top_post_amount = _post_top_amount();
	if ($offset + 1 <= $top_post_amount) // some top posts are included
	{
		if ($offset + $count <= $top_post_amount) // all top posts
		{
			return post_get_post_list($concrete, TRUE, $offset, $count, $depth, $uid, $post_sort_way, $post_reply_sort_way);
		}
		else // some are top posts
		{
			$top_post_amount = $top_post_amount - $offset;
			$ret = post_get_post_list($concrete, TRUE, $offset, $top_post_amount, $depth, $uid, $post_sort_way, $post_reply_sort_way);
			$remain_amount = $count - $top_post_amount;
			$ret[] = post_get_post_list($concrete, FALSE, 0, $remain_amount, $depth, $uid, $post_sort_way, $post_reply_sort_way);
			return $ret;
		}
	}
	else // no top posts are included
	{
		return post_get_post_list($concrete, FALSE, $offset, $count, $depth, $uid, $post_sort_way, $post_reply_sort_way);
	}
}
/**
 * @ignore
 */
function _build_post_list($id, $concrete = FALSE, $sort_way = 'ASC', $depth = NULL)
{
	global $db, $DBOP, $POST_VAL_SET;
	$ret = array();
	$value = $POST_VAL_SET;
	if (!$concrete)
		unset($value['content']);
	$posts = $db->select_from('posts', $value, array($DBOP['='], 'id', $id));
	$ret[] = $posts[0];

	$posts = $db->select_from('posts', array('id'), array($DBOP['='], 'pid', $id), array('time' => $sort_way));
	if (is_null($depth) || ((!is_null($depth) && $depth > 1)))
		foreach ($posts as $post)
			$ret[] = _build_post_list($post['id'], $concrete, $sort_way, is_null($depth) ? NULL : $depth - 1);
	return $ret;
}

/**
 * delete post recursively
 * @param int $id post id
 * @return void
 * @exception Exc_runtime
 */
function post_del_posts($id)
{
	global $db, $DBOP, $user;
	if ($id <= 0)
		throw new Exc_runtime(__('Invalid post id: id <= 0'));
	if (!user_check_login())
		throw new Exc_runtime(__("Please login first"));
	$post = $db->select_from('posts', array('uid'), array($DBOP['='], 'id', $id));
	if (!($user->id == $post[0]['uid'] || $user->is_grp_member(GID_ADMIN_POST)))
		throw new Exc_runtime(__('You are not permitted to delete this post'));
	_post_del_posts($id);
}

/**
 * @ignore
 */
function _post_del_posts($id)
{
	global $db, $DBOP;
	$posts = $db->select_from('posts', array('id'), array($DBOP['='], 'pid', $id));
	foreach ($posts as $post)
		_post_del_posts($post['id']);
	filter_apply_no_iter('before_post_delete', $id);
	$db->delete_item('posts', array($DBOP['='], 'id', $id));
}

/**
 * modify a post
 * @return int|BOOL affected rows or TRUE
 */
function post_modify_post()
{
	if (!user_check_login())
		throw new Exc_runtime(__('Please login first'));
	$id = $_POST['post_id'];
	$tmp = $db->select_from('posts', array('uid'), array($DBOP['='], 'id', $id));
	if (!($user->id == $tmp[0]['uid'] || $user->is_grp_member(GID_ADMIN_POST)))
		throw new Exc_runtime(__('You are not permitted to modify this post.'));

	if (!isset($_POST['subject']))
		throw new Exc_runtime(__('No post subject'));
	if (strlen($_POST['subject']) > POST_SUBJECT_LEN_MAX)
		throw new Exc_runtime(__('Subject is too long'));
	if (!isset($_POST['post_id']))
		throw new Exc_runtime(__('No post is specified.'));
	$content = tf_form_get_rich_text_editor_data('content');

	$val = array('subject' => htmlencode($_POST['rubject']),
		'content' => $content, 
		'last_modify_time' => time(), 'last_modify_user' => $user->id
	);
	$val = filter_apply('before_post_modify', $val);
	return $db->update_data('posts', $val, array($DBOP['='], 'id', $id));
}

/**
 *  echo form fields for modifying a post
 *  @param int $id post id
 *  @return void
 */
function post_modify_post_get_form($id)
{
	$str =
		tf_form_get_text_input(__('Subject:'), 'subject') .
		tf_form_get_rich_text_editor(__('Content:'), 'content') .
		tf_form_get_hidden('id', $id);
	echo filter_apply('after_post_modify_form', $str);
}

/**
 * set a post top status
 * @param int $id post id
 * @param BOOL $status TRUE top, and FALSE the opposite
 */
function post_set_top_status($id, $status = TRUE)
{
	global $db, $DBOP;
	$db->update_data('posts', array('is_top' => $state),
		array($DBOP['='], 'id', $status === TRUE ? 1 : 0));
}


