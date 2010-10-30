<?php
/* 
 * $File: post.php
 * $Date: Sat Oct 30 12:03:24 2010 +0800
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


$POST_VAL_SET = array('id', 'time', 'uid', 'pid', 'prob_id', 'rid', 'reply_amount', 'viewed_amount', 'priority', 'is_tio', 'type', 'last_reply_time', 'last_reply_user', 'subject', 'content', 'last_modify_time', 'last_modify_user', 'nickname_uid', 'nickname_last_reply_user', 'nickname_last_modify_user'); 
$POST_TYPE_SET = array('all', 'normal', 'question', 'solution', 'vote');
$POST_TYPE_DISP = array(
	'all' => __('All'),
	'normal' => __('Normal'),
	'question' => __('Question'),
	'solution' => __('Solution'),
	'vote' => __('Vote')
);
$POST_TYPE_TO_NUM = array();
$tmp = 0;
foreach ($POST_TYPE_SET as $val)
	$POST_TYPE_TO_NUM[$val] = $tmp ++;
unset($tmp);

$POST_ATTRIB_SET = array('is_top');
$POST_PRIORITY = array('is_top' => 5, 'normal' => 0);

/**
 * @param int $id the post id
 * return int root post id of a post
 */
function post_get_root_id($id)
{
	global $db, $DBOP;
	if ($id == 0) return 0;
	$ret = $db->select_from('posts', array('rid'), array($DBOP['='], 'id', $id));
	if (count($ret) == 0)
		return 0;
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

	$time = time();
	$val = array('subject' => htmlencode($_POST['subject']),
		'content' => $content, 'time' => $time, 'uid' => $user->id,
		'pid' => $pid, 'prob_id' => $prob_id, 
		'last_reply_time' => $time, 'last_reply_user' => $user->id
	);
	if ($pid > 0)
	{
		$val['rid'] = _post_get_root_id($pid);
		_post_update_root_post($val['rid'], $user->id);
	}
	$val = filter_apply('before_post_add', $val);
	$ret = $db->insert_into('posts', $val);
	$rid = ($pid ? post_get_root_id($pid) : $ret);
	$db->update_data('posts', array('rid' => ($pid ? $rid : $ret)));
	$db->update_data('posts', array('last_reply_time' => time()), array($DBOP['='], 'rid', $rid));
	return $ret;
}

/**
 * echo form fields for adding a new post
 * @return void
 */
function post_add_get_form($id)
{
	$str =
	//	tf_form_getf_post_type_selector() .
		tf_form_get_text_input(__('Subject:'), 'subject') .
		tf_form_get_rich_text_editor(__('Content:'), 'content') .
		tf_form_get_text_input(__('Problem id'), 'prob_id', NULL, "$id");
	echo filter_apply('after_post_add_form', $str);
}

/**
 * @ignore
 */
function _build_post_tree(&$ret, $id)
{
	global $_map_post, $_posts;
	$ret[] = $_posts[$id];
	if (isset($_map_post[$id]))
		foreach ($_map_post[$id] as $ch)
		{
			$ret[] = array();
			_build_post_tree($ret[count($ret) - 1], $ch);
		}
}


/**
 * get post list in a specific limitation, if deepened, the reply will sort by time in ascending order 
 * @param array $fields the fields you want to get, see @install/tables.php
 * @param bool $deepened FALSE the root post or FALSE the whole post tree
 * @param array|string|NULL $type NULL all the post, or string or array of string, @see $POST_TYPE_SET
 * @param int|NULL $offset offset will be converted to include a whole root post
 * @param int|NULL $count how many post do you want, this does not include post replies
 * @param int|NULL $uid if this is specified, post published by a certain user will be returned.
 * @param string|NULL $subject specified subject pattern, a pattern tranformed by includes/functions.php : transform_pattern
 * @param string|NULL $author specified the author, nickname and username included
 *						if both $author and $uid is set, it will both work
 * @param string $post_sort_way 'ASC' or 'DESC', sort by last_reply_time
 * @return array an recursive array of post, in each array, index 0 stores the post in a array, keys see install/tables.php
 *		and from index 1 to end is array, except the first layer. the first layer will be a 
 *		array of array.
 * @exception Exc_runtime if user does not exists
 */
function post_get_post_list($fields = NULL, $deepened = FALSE, $type = NULL, $offset = NULL, $count = NULL, $uid = NULL, $subject = NULL, $author = NULL, $post_sort_way = 'DESC')
{
	global $db, $DBOP, $POST_VAL_SET, $POST_TYPE_SET, $POST_TYPE_TO_NUM;
	$fields = array_intersect($fields, $POST_VAL_SET);
	$appended_fields = array();
	if (!array_search('rid', $fields))
	{
		$appended_fields[] = 'rid';
		$fields[] = 'rid';
	}
	if (!array_search('id', $fields))
	{
		$appended_fields[] = 'id';
		$fields[] = 'id';
	}
	if (!array_search('pid', $fields))
	{
		$appended_fields[] = 'pid';
		$fields[] = 'pid';
	}
	$nickname_items = array();
	foreach (array('uid', 'last_reply_user', 'last_modify_user') as $item)
	{
		$name = 'nickname_' . $item;
		if ($key = array_search($name, $fields))
		{
			unset($fields[$key]);
			$nickname_items[] = $item;
			if (!array_search($item, $fields))
			{
				$appended_fields[] = $item;
				$fields[] = $item;
			}
		}
	}
	if (is_string($type))
		$type = array($type);
	if (is_array($type))
		$type = array_intersect($type, $POST_TYPE_SET);
	else $type = NULL;

	$where = NULL;

	// deal user filter
	$user_where = NULL;
	if (is_int($uid))
	{
		if (!user_exists($uid))
			throw new Exc_runtime(__('No such user whose id is %d!', $uid));
		db_where_add_or($user_where, array($DBOP['='], 'uid', $uid));
	}
	if (is_string($author) && strlen($author))
	{
		$flag = false;
		if ($id = user_get_id_by_username($author))
		{
			db_where_add_or($user_where, array($DBOP['='], 'uid', $id));
			$flag = true;
		}
		if ($id = user_get_id_by_nickname($author))
		{
			db_where_add_or($user_where, array($DBOP['='], 'uid', $id));
			$flag = true;
		}
		if (!$flag)
			throw new Exc_runtime(__('No such user whose nickname or username is %s!', $author));
	}
	if (is_array($user_where))
		db_where_add_and($where, $user_where);

	if (is_string($subject))
		db_where_add_and($where, array($DBOP['like'], 'subject', $subject));

	if (is_array($type))
	{
		$tmp = NULL;
		foreach ($type as $t)
			db_where_add_or($tmp, array($DBOP['='], 'type', $POST_TYPE_TO_NUM[$t]));
		db_where_add_and($where, $tmp);
	}
	if ($deepened == FALSE)
		db_where_add_and($where, array($DBOP['='], 'pid', 0));
	$order_by = array('priority' => 'DESC', 'last_reply_time' => $post_sort_way, 'time' => 'ASC');
	$posts = $db->select_from('posts', $fields, $where, $order_by, $offset, $count);
	if (count($posts) == 0) 
		return filter_apply('after_post_list', array());
	if ($deepened)
	{
		$cnt_post = count($posts);
		$last_post_rid = $posts[$cnt_post - 1]['rid'];
		$new_posts = array();
		foreach ($posts as $post)
			if ($post['rid'] != $last_post_rid)
				$new_posts[] = $post;
		$posts = $db->select_from('posts', $fields, array($DBOP['='], 'rid', $last_post_rid));
		foreach ($new_posts as $t)
			$posts[] = $t;
	}
	global $_map_post, $_posts;
	$_map_post = array();
	$_posts = array();
	foreach ($posts as $post)
	{
		if (!isset($_map_post[$post['pid']]))
			$_map_post[$post['pid']] = array();
		$_map_post[$post['pid']][] = $post['id'];
		foreach ($appended_fields as $f)
			unset($post[array_search($f, $post)]);
		$id = $post['id'];
		$_posts[$id] = $post;
		$tmp = $db->select_from('posts', $nickname_items, array($DBOP['='], 'id', $id));
		foreach ($tmp[0] as $key => $val)
			$_posts[$id]['nickname_' . $key] = user_get_nickname_by_id($val);
	}

	$ret = array();
	//	foreach ($appended_fields as $f)
	//		unset($fields[array_search($f, $fields)]);

	foreach ($_map_post[0] as $id)
	{
		$ret[] = array();
		_build_post_tree($ret[count($ret) - 1], $id);
	}
	return filter_apply('after_post_list', $ret);
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
	// FIXME: this is wrong
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
 * set a post to a toped post, root post needed.
 * @param int $id post id
 * @param BOOL $status TRUE top, and FALSE the opposite
 * @exception Exc_runtime error message is the post is not a root post
 * @return void
 */
function post_set_top_status($id, $status = TRUE)
{
	global $db, $DBOP;
	$status = ($status == TRUE ? 1 : 0);
	$post = $db->select_from('posts', array('rid'), array($DBOP['='], 'id', $id));
	if ($post[0]['rid'] != $id)
		throw new Exc_runtime(__('This post is not a root post.'));
	$db->update_data('posts', array('is_top' => $state),
		array($DBOP['='], 'id', $id));
	$db->update_data('posts', array('priority' => ($status == 1 ? $POST_PRIORITY['is_top'] : $POST_PRIORITY['normal'])),
		array($DBOP['='], 'rid', $id));
}

/**
 * @param bool $deepened FALSE the root post or FALSE the whole post tree
 * @param array|string|NULL $type NULL all the post, or string or array of string, @see $POST_TYPE_SET
 * @param int|NULL $uid if this is specified, post published by a certain user will be returned.
 * @return int number of posts in a specific condition
 */
function post_get_post_amount($deepened = FALSE, $type = NULL, $uid = NULL, $subject = NULL, $author = NULL)
{
	global $db, $DBOP, $POST_TYPE_SET, $POST_TYPE_TO_NUM;
	$where = NULL;
	if ($deepened == FALSE)
		db_where_add_and($where, array($DBOP['='], 'pid', 0));
	if (is_string($type))
		$type = array($type);
	if (is_array($type))
		$type = array_intersect($type, $POST_TYPE_SET);
	else $type = NULL;

	$user_where = NULL;
	if (is_int($uid))
		db_where_add_or($user_where, array($DBOP['='], 'uid', $uid));
	if (is_string($author))
	{
		if ($id = user_get_id_by_username($author))
			db_where_add_or($user_where, array($DBOP['='], 'uid', $id));
		if ($id = user_get_id_by_username($author))
			db_where_add_or($user_where, array($DBOP['='], 'uid', $id));
	}
	if (is_array($user_where))
		db_where_add_and($where, $user_where);

	if (is_string($subject))
		db_where_add_and($where, array($DBOP['like'], 'subject', $subject));

	if (is_array($type))
	{
		$tmp = NULL;
		foreach ($type as $t)
			db_where_add_or($tmp, array($DBOP['='], 'type', $POST_TYPE_TO_NUM[$t]));
		db_where_add_and($where, $tmp);
	}
	return $db->get_number_of_rows('posts', $where);
}

/**
 * to judge if a post exists
 * @return BOOL the result
 */
function post_exists($id)
{
	global $db, $DBOP;
	return ($db->get_number_of_rows('posts', array($DBOP['='], 'id', $id)) == 1) ? TRUE : FALSE;
}

