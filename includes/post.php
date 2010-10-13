<?php
/* 
 * $File: post.php
 * $Date: Wed Oct 13 10:02:47 2010 +0800
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

class Post
{
	var $id, $time, $uid, 
		$pid,  // related problem or 0 means no problem is related
		$subject, $content,
		$last_reply_time, $last_reply_user,
		$last_modify_time, $last_modify_user;
}

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
 * @param int $pid problem id or parent post id, less than means this pos is a root post, @see install/tables.php
 * @return int the new post id 
 * @exception Exc_runtime
 */
function post_add($pid)
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
		'pid' => $pid, 'rid' => 0);
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
function post_add_get_form()
{
	$str =
		tf_form_get_text_input(__('Subject:'), 'subject') .
		tf_form_get_rich_text_editor(__('Content:'), 'content');
	echo filter_apply('after_post_add_form', $str);
}

/**
 * get post list wiht  a specific limitation
 * @param int|NULL $uid int a user's id or NULL means all
 * @param bool $concrete TRUE means need concrete information, FALSE means the opposite.
 * @param int $offset 
 * @param int $count how many post do you want, this does not include post replies
 * @param string $post_sort_way 'ASC' or 'DESC', sort by last_reply_time
 * @param string $post_reply_sort_way 'ASC' or 'DESC', sort by time
 * @param int $uid if this is specified, post published by a certain user will be returned.
 * @return array an recursive array of post, in each array, index 0 stores the post, 
 *		and from index 1 to end is array, except the first layer. the first layer will be a 
 *		array of array.
 */
function post_get_post_list($concrete = FALSE, $offset = NULL, $count = NULL, $post_sort_way = 'DESC', $post_reply_sort_way = 'ASC', $uid = NULL)
{
	global $db, $DBOP;
	$ret = array();
	if ($concrete)
		array_push($value, 'content');
	$posts = $db->select_from(
		'posts', 
		array('id'),
		array($DBOP['<='], 'pid', 0),
		array('last_reply_time' => $post_sort_way),
		$offset,
		$count
	);
	foreach ($posts as $post)
		$ret[] = _build_post_list($post['id'], $concrete, $post_reply_sort_way);
	return filter_apply('after_post_list', $ret);
}

/**
 * @ignore
 */
function _build_post_list($id, $concrete = FALSE, $sort_way = 'ASC')
{
	global $db, $DBOP;
	$ret = array();
	$value = array('id', 'time', 'uid', 'pid', 'rid', 'subject');
	if ($concrete)
		array_push($value, 'content');
	$posts = $db->select_from('posts', $value, array($DBOP['='], 'id', $id));
	$post = new Post();
	foreach ($posts[0] as $key => $val)
		$post->$key == $val;
	$ret[] = $post;

	$posts = $db->select_from('posts', array('id'), array($DBOP['='], 'pid', $id), array('time' => $sort_way));
	foreach ($posts as $post)
		$ret[] = _build_post_list($post['id'], $concrete, $sort_way);
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
		throw new Exc_runtime(__("Please login first."));
	$post = $db->select_from('posts', array('uid'), array($DBOP['='], 'id', $id));
	if (!($user->id == $post[0]['uid'] || $user->is_grp_member(GID_ADMIN_POST)))
		throw new Exc_runtime(__('You are not permitted to delete this post.'));
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
 * @return int|BOOl affected rows or TRUE
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

	$val = array('subject' => htmlencode($_POST['subject']),
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
		tf_form_get_hidden('post_id', $id);
	echo filter_apply('after_post_modify_form', $str);
}

