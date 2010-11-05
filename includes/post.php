<?php
/* 
 * $File: post.php
 * $Date: Fri Nov 05 10:00:12 2010 +0800
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

$POST_USER_NAME_SET = array('nickname', 'username', 'realname');
require_once $includes_path . 'problem.php';

/* post topic constants */
$POST_TOPIC_FIELDS_SET = array('id', 'time', 'uid', 'prob_id',
	'reply_amount', 'viewed_amount', 'floor_amount',
	'priority', 'is_top', 'is_locked', 'is_boutique', 'type',
	'last_reply_time', 'last_reply_user',
   	'subject', 'content',
	'nickname_uid', 'nickname_last_reply_user',
	'username_uid', 'username_last_reply_user',
	'realname_uid', 'realname_last_reply_user'
);

$POST_TOPIC_USER_ID_SET = array('uid', 'last_reply_user');

$POST_TYPE_SET = array('all', 'normal', 'question', 'solution', 'vote');

$POST_TYPE_DISP = array(
	'all' => __('All'),
	'normal' => __('Normal'),
	'question' => __('Question'),
	'solution' => __('Solution'),
	'vote' => __('Vote')
);

filter_apply('post_type_set', $POST_TYPE_SET, $POST_TYPE_DISP);

$POST_TYPE_TO_NUM = array();
foreach ($POST_TYPE_SET as $key => $val)
	$POST_TYPE_TO_NUM[$val] = $key;

$POST_TOPIC_ATTRIB_SET = array('is_top', 'is_locked', 'is_boutique');
$POST_TOPIC_STATISTIC_SET = array('reply_amount', 'viewed_amount', 'floor_amount');

$POST_TOPIC_PRIORITY = array('is_top' => 5, 'normal' => 0);

$AUTHOR_TYPE_SET = array('username');


/* posts constants */
$POSTS_FIELD_SET = array('id', 'time', 'uid', 'tid', 
	'content', 'floor',
   	'last_modify_time',	'last_modify_user',
	'nickname_uid', 'nickname_last_modify_user',
	'username_uid', 'username_last_modify_user',
	'realname_uid', 'realname_last_modify_user',
);

$POSTS_USER_ID_SET = array('uid', 'last_modify_user');

/**
 * get post add topic form
 * @param int $prob_id the problem id
 * @return void
 */
function post_add_topic_get_form($prob_id = 0)
{
	if ($prob_id == 0)
		$prob_code = '';
	else $prob_code = prob_get_code_by_id($prob_id);
	$str = 
		tf_form_get_text_input(__('Problem code:'), 'prob_code', NULL, "$prob_code", 'post-add-topic-prob-code')
		. tf_form_get_post_type_select(__('Post type:'), 'type', NULL, 'post-add-topic-post-type')
		. tf_form_get_text_input(__('Subject:'), 'subject', NULL, NULL, 'post-add-topic-subject')
		. tf_form_get_rich_text_editor(__('Content:'), 'content', NULL, 'post-add-topic-content');
	echo filter_apply('after_post_add_form', $str);
}

/**
 *
 */
function post_add_topic()
{
	global $db, $DBOP, $user, $POST_TYPE_SET, $POST_TYPE_TO_NUM;
	if (!user_check_login())
		throw new Exc_runtime(__('Please login first'));
	if (!isset($_POST['prob_code']) || !isset($_POST['type']))
		throw new Exc_runtime(__('Incomplete POST'));
	if (!array_key_exists('subject', $_POST) || empty($_POST['subject']))
		throw new Exc_runtime(__('No post subject'));
	if (strlen($_POST['subject']) > POST_SUBJECT_LEN_MAX)
		throw new Exc_runtime(__('Subject is too long'));

	$type = $_POST['type'];
	if (array_search($type, $POST_TYPE_SET) === FALSE)
		throw new Exc_runtime(__('Invalid post type'));

	$type = $POST_TYPE_TO_NUM[$type];
	$subject = htmlencode($_POST['subject']);

	global $content;
	$content = tf_form_get_rich_text_editor_data('content');
	_validate_content();

	$prob_code = $_POST['prob_code'];
	$prob_code = trim($prob_code);
	if (!empty($prob_code))
	{
		$prob_id = prob_get_id_by_code($prob_code);
		if (is_null($prob_id))
			throw new Exc_runtime(__('No such problem whose code is \'%s\'', $prob_code));
	}
	else $prob_id = 0;
	$time = time();
	$uid = $last_reply_user =  $user->id;
	$last_reply_time = $time;
	$val = array();
	foreach (array('subject', 'content', 'time', 'uid', 'prob_id', 'last_reply_time', 'last_reply_user', 'type') as $item)
		$val[$item] = $$item;

	$val = filter_apply('before_post_topic_add', $val);

	$tid = $db->insert_into('post_topics', $val);

	$floor = 0;

	_post_reply_add_content($content, $time, $uid, $tid, $floor);

	return $tid;
}

/**
 * @ignore
 */
function _post_get_topic_list_build_where($type, $uid, $subject, $author, $prob_id, $attrib)
{
	global $DBOP, $POST_TYPE_SET, $POST_TYPE_TO_NUM, $AUTHOR_TYPE_SET, $POST_TOPIC_ATTRIB_SET;
	$where = NULL;
	if (is_string($type))
		db_where_add_and($where, array($DBOP['='], 'type', $POST_TYPE_TO_NUM[$type]));

	if (is_int($uid))
	{
		if (is_null(user_get_username_by_id($uid)))
			throw new Exc_runtime(__('No such user whose id is %d!', $uid));
		db_where_add_and($where, array($DBOP['='], 'uid', $uid));
	} else if (is_string($author) && strlen($author))
	{
		$author_id = user_get_id_by_username($author);
		if ($author_id === NULL)
			throw new Exc_runtime(__('No such user whose username is %s!', $author));
		db_where_add_and($where, array($DBOP['='], 'uid', $author_id));
	}

	if (is_string($subject) && strlen($subject))
		db_where_add_and($where, array($DBOP['like'], 'subject', $subject));

	if (!empty($prob_id))
	{
		$prob_id = intval($prob_id);
		if (is_null(prob_get_code_by_id($prob_id)))
			throw new Exc_runtime(__('No such problem whose id is %d!', $prob_id));
		db_where_add_and($where, array($DBOP['='], 'prob_id', $prob_id));
	}
	if (is_array($attrib))
		foreach ($POST_TOPIC_ATTRIB_SET as $at)
			if (array_key_exists($at, $attrib) && is_bool($attrib[$at]))
				db_where_add_and($where, array($DBOP['='], $at, $attrib[$at]));

	return $where;
}

/**
 * @ignore
 */
function _deal_addtional_fields_start(&$fields, $ID_SET)
{
	global $POST_USER_NAME_SET;
	if (!is_array($fields))
		return NULL;
	$additional_fields = array();
	foreach ($POST_USER_NAME_SET as $prefix)
		foreach ($ID_SET as $item)
			if (($key = array_search($prefix . '_' . $item, $fields)) !== FALSE)
			{
				unset($fields[$key]);
				if (!isset($additional_fields[$prefix]))
					$additional_fields[$prefix] = array($item);
				else $additional_fields[$prefix][] = $item;
				if (array_search($item, $fields) === FALSE)
					$fields[] = $item;
			}
	return $additional_fields;
}

/**
 * @ignore
 */
function _deal_addtional_fields_end(&$fields, $ID_SET, &$additional_fields, &$list)
{
	global $POST_USER_NAME_SET, $db, $DBOP;
	if (is_null($fields) || is_null($additional_fields))
		return;
	$cnt = count($list);
	$block_size = sqrt($cnt);
	$_users = array();
	$val_set = $POST_USER_NAME_SET;
	$val_set[] = 'id';
	for ($i = 0; $i < $cnt; $i += $block_size)
	{
		$where = NULL;
		for ($j = $i; $j < $cnt && $j < $i + $block_size; $j ++)
			foreach ($ID_SET as $item)
				db_where_add_or($where, array($DBOP['='], 'id', $list[$j][$item]));
		//die(var_dump($val_set));
		$ret = $db->select_from('users', $val_set, $where);

		foreach ($ret as $us)
			$_user[$us['id']] = $us;

		for ($j = $i; $j < $cnt && $j < $i + $block_size; $j ++)
			foreach ($additional_fields as $prefix => $items)
				foreach ($items as $item)
				{
					$t = $list[$j][$item];
					if ($t)
						$list[$j][$prefix . '_' . $item] 
							= $_user[$t][$prefix];
					else
						$list[$j][$prefix . '_' . $item] 
							= '';
				}
	}
}


/**
 * @param array|NULL $fields the fields you want to get, NULL means ALL, see $POST_TOPIC_FIELDS_SET. if some extra fields which doen't exists in databse, the base fields will be added in.
 * @param string|NULL $type NULL ALL type of post, or string a specific type, see $POST_TYPE_SET
 * @param int|NULL $offset
 * @param int|NULL $count amount of topics you want to get
 * @param int|NULL $uid NULL ALL of the users, or int a specific user
 * @param string|NULL $subject the pattern the subject of topics is to be matched. the pattern should be a database-recognizable pattern, or a human-readable pattern transformed by includes/functions.php : transform_pattern
 * @param string|NULL $author the author of topic. if $uid is set, this option will not work
 * @param array|NULL $attrib valid attributes : array('is_top' => BOOL, 'is_locked' => BOOL), if set more than one, they will all to be matched
 * @exception Exc_runtime if user does not exists
 */
function post_get_topic_list($fields = NULL, $type = NULL, $offset = NULL, $count = NULL, $uid = NULL, $subject = NULL, $author = NULL, $prob_id, $attrib = NULL)
{
	global $db, $DBOP, $POST_TOPIC_FIELDS_SET, $POST_TOPIC_USER_ID_SET, $POST_USER_NAME_SET;
	if (is_array($fields))
	{
		$fields = array_intersect($fields, $POST_TOPIC_FIELDS_SET);
		if (array_search('id', $fields) === FALSE)
			$fields[] = 'id';
	}

	// additional fields
	$additional_fields = _deal_addtional_fields_start($fields, $POST_TOPIC_USER_ID_SET);

	$where = _post_get_topic_list_build_where($type, $uid, $subject, $author, $prob_id, $attrib);

	$order_by = array('priority' => 'DESC', 'last_reply_time' => 'DESC');

	$list = $db->select_from('post_topics', $fields, $where, $order_by, $offset, $count);

	_deal_addtional_fields_end($fields, $POST_TOPIC_USER_ID_SET, $additional_fields, $list);

	return filter_apply('after_post_topic_list', $list);
}

/**
 * get amount of topic in a specific limitation
 * @see post_get_topic_list
 */
function post_get_topic_amount($type = NULL, $uid = NULL, $subject = NULL, $author = NULL, $prob_id = NULL, $attrib = NULL)
{
	global $db;
	$where = _post_get_topic_list_build_where($type, $uid, $subject, $author, $prob_id, $attrib);
	return $db->get_number_of_rows('post_topics', $where);
}

/**
 * get content of a topic
 * @param int $id
 * @param string|array|NULL $fields
 * @return array
 */
function post_get_topic($id, $fields = NULL)
{
	global $db, $DBOP, $POST_TOPIC_FIELDS_SET;
	if (!is_int($id))
		throw new Exc_runtime(__('type of parameter `%s` wrong', 'id'));
	if (is_string($fields))
		$fields = array($fields);
	if (is_array($fields))
		$fields = array_intersect($fields, $POST_TOPIC_FIELDS_SET);
	else if (!is_null($fields))
		throw new Exc_runtime(__('type of parameter `%s` wrong', 'fields'));
	$ret = $db->select_from('post_topics', $fields, array($DBOP['='], 'id', $id));
	if (count($ret) != 1)
		return NULL;
	return $ret[0];
}

/**
 * judge if a topic exists
 * @param int $id
 * @return BOOL
 */
function post_topic_exists($id)
{
	global $db, $DBOP;
	static $cache = array();
	if (array_key_exists($id, $cache))
		return $cache[$id];
	return $cache[$id] = ($db->get_number_of_rows('post_topics', array($DBOP['='], 'id', $id)) == 1) ? TRUE : FALSE;
}

/**
 *
 */
function post_topic_modify_priority($id, $priority)
{
}

/**
 * delete a post topic
 * @param int $id
 * @return void
 * @exception Exc_runtime
 */
function post_topic_delete($id)
{
	global $db, $DBOP, $user;
	if (!user_check_login())
		throw new Exc_runtime(__('You must login first'));
	if (!$user->is_grp_member(GID_ADMIN_POST))
		throw new Exc_runtime(__('You are not permitted to do this operation'));
	if (!post_topic_exists($id))
		throw new Exc_runtime(__('No such post topic whose id is %d', $id));
	$db->delete_item('post_topics', array($DBOP['='], 'id', $id));
	$db->delete_item('posts', array($DBOP['='], 'tid', $id));
}

/**
 * set topic attributes, see $POST_TOPIC_ATTRIB_SET
 * @param int $id post topic id
 * @param string $attrib attributes, @see $POST_TOPIC_ATTRIB_SET
 * @param bool $status TRUE or FALSE
 * @return bool TRUE if succeed, or FALSE if failed.
 */
function post_topic_set_attrib($id, $attrib, $status = TRUE)
{
	global $db, $DBOP, $POST_TOPIC_ATTRIB_SET, $POST_TOPIC_PRIORITY;
	if (array_search($attrib, $POST_TOPIC_ATTRIB_SET) === FALSE)
		return FALSE;
	if (!is_bool($status))
		return FALSE;
	$val = array($attrib => $status);
	if ($attrib == 'is_top')
		$val['priority'] = ($status == TRUE ? $POST_TOPIC_PRIORITY['is_top'] : $POST_TOPIC_PRIORITY['normal']);
	$db->update_data('post_topics', $val,
		array($DBOP['='], 'id', $id));
	return TRUE;
}

/**
 * increase statistic of a post topic 
 * @param int $id post topic id
 * @param array|string $fields fields in $POST_TOPIC_STATISTIC_SET
 * @param int|array $delta if $field is an array and $delta is a int, 
 *			all fields in $field will be increased $delta; if array
 *			if specified, the corresponding element in $field will
 *			increased by number in $delta. fields with no corresponding
 *			delta will be increased by one.
 * @return void
 */
function post_topic_increase_statistic($id, $fields, $delta = 1)
{
	global $POST_TOPIC_STATISTIC_SET, $db, $DBOP;
	if (is_string($fields))
		$fields = array($fields);
	else if (!is_array($fields))
		return;
	$fields = array_intersect($POST_TOPIC_STATISTIC_SET, $fields);
	if (count($fields) == 0)
		return;
	$where = array($DBOP['='], 'id', $id);
	$valset = $db->select_from('post_topics', $fields, $where);
	$valset = $valset[0];
	foreach ($fields as $item)
	{
		if (is_int($delta))
			$valset[$item] += $delta;
		else if (is_array($delta))
			$valset[$item] += (isset($delta[$key]) ? $delta[$key] : 1);
		else
			$valset[$item] += 1;
	}
	$db->update_data('post_topics', $valset, $where);
}

/**
 * get post list
 * @param int $tid post topic id
 * @param array|string|NULL $fields @see $POSTS_FIELD_SET
 * @param int|NULL $offset
 * @param int|NULL $count
 * @param string $order 'ASC' or 'DESC', the way to sort posts by time
 * @return array the post list
 * @exception Exc_runtime
 */
function post_get_post_reply_list($tid, $fields = NULL, $offset = NULL, $count = NULL, $order = 'ASC')
{
	global $POSTS_FIELD_SET, $db, $DBOP, $POSTS_USER_ID_SET;

	if (!post_topic_exists($tid))
		throw new Exc_runtime(__('Post topic whose id is %d doesn\'t exists', $tid));
	if (is_string($fields))
		$fields = array($fields);
	if (is_array($fields))
		$fields = array_intersect($fields, $POSTS_FIELD_SET);
	else $fields = array('id');
	if (array_search('id', $fields) === FALSE)
		$fields[] = 'id';

	$additional_fields = _deal_addtional_fields_start($fields, $POSTS_USER_ID_SET);

	$tid = intval($tid);

	$where = array($DBOP['='], 'tid', $tid);

	$order = ($order == 'DESC' ? 'DESC' : 'ASC');
	$order_by = array('time' => $order, 'id' => $order);


	$list = $db->select_from('posts', $fields, $where, $order_by, $offset, $count);

	_deal_addtional_fields_end($fields, $POSTS_USER_ID_SET, $additional_fields, $list);

	return filter_apply('after_post_list', $list);
}


/**
 * get the amount of post of a topic
 */
function post_get_post_reply_amount($tid)
{
	global $db, $DBOP;
	return $db->get_number_of_rows('posts', array($DBOP['='], 'tid', $tid));
}

/**
 *
 */
function post_modify_post_get_form()
{
}

/**
 *
 */
function post_modify_post()
{
}

/**
 * @ignore
 */
function _post_is_topic_post($id)
{
}

/**
 * get post reply form, the user must be logined
 * @param int $tid topic id
 * @exception Exc_runtime throw when user is not logined.
 * @return void
 */
function post_reply_get_form($tid)
{
	global $user;
	$s = tf_form_get_hidden('post_reply_tid', $tid);
	if (!user_check_login())
		throw new Exc_runtime(__('You must be logined to reply.'));
	$s .= tf_form_get_hidden('post_reply_uid', $user->id);
	$s .= tf_form_get_rich_text_editor(__('Content:'), 'post_reply_content');

	echo filter_apply('after_post_reply_form', $s);
}

/**
 * @ignore
 */
function _post_reply_add_content($content, $time, $uid, $tid, &$basic_floor)
{
	global $db;
	for($len = strlen($content); $len > 0; $len -= POST_CONTENT_FLOOR_LEN_MAX)
	{
		$db->insert_into('posts', 
			array('time' => $time,
			'uid' => $uid,
			'tid' => $tid,
			'content' => substr($content, 0, POST_CONTENT_FLOOR_LEN_MAX),
			'floor' => ++ $basic_floor
		)
	);
		if ($len > POST_CONTENT_LEN_MAX)
			$content = substr($content, POST_CONTENT_LEN_MAX);
	}
}

/**
 * @ignore
 */

function _validate_content()
{
	global $content;
	$content = trim($content);
	if (empty($content))
		throw new Exc_runtime(__('Hi buddy, Something to say?'));
	if ($content > POST_CONTENT_LEN_MAX)
		throw new Exc_runtime(__('Content is too long'));

}

/**
 * parse posted data and reply the topic
 * @exception Exc_runtime throw when something is wrong
 * @return void
 */
function post_reply()
{
	global $db, $DBOP;
	if (!user_check_login())
		throw new Exc_runtime(__('You must be logined to reply.'));
	filter_apply_no_iter('before_post_reply');
	if (!isset($_POST['post_reply_tid']) || !isset($_POST['post_reply_uid']))
		throw new Exc_runtime(__('Incomplete POST.'));

	$tid = intval($_POST['post_reply_tid']);
	$uid = intval($_POST['post_reply_uid']);

	$topic = post_get_topic($tid, 'is_locked');
	if ($topic['is_locked'] && !$user->is_grp_member(GID_ADMIN_POST))
		throw new Exc_runtime(__('This topic is locked and you are not permitted to reply'));

	global $content;
	$content = tf_form_get_rich_text_editor_data('post_reply_content');
	_validate_content();

	$where = array($DBOP['='], 'id', $tid);
	$topic = $db->select_from('post_topics', array('floor_amount', 'reply_amount'), $where);
	$topic = $topic[0];

	$basic_floor = $topic['floor_amount'];
	$time = time();

	_post_reply_add_content($content, $time, $uid, $tid, $basic_floor);

	$nrep = $topic['reply_amount'];
	$db->update_data('post_topics',
		array('reply_amount' => $nrep + 1,
		'last_reply_time' => $time,
		'last_reply_user' => $uid,
		'floor_amount' => $basic_floor
		), $where
	);
}

/**
 * @ignore
 */
function _get_post_reply($id)
{
	global $db, $DBOP;
	static $cache = array();
	if (array_key_exists($id, $cache))
		return $cache[$id];
	$ret = $db->select_from('posts', NULL, array($DBOP['='], 'id', $id));
	if (count($ret) != 1)
		return $cache[$id] = NULL;
	return $cache[$id] = $ret[0];
}
/**
 * judge if a post reply exists
 * @param int $id
 * @return BOOL
 */
function post_reply_exists($id)
{
	global $db, $DBOP;
	static $cache = array();
	if (array_key_exists($id, $cache))
		return $cache[$id];
	$post = _get_post_reply($id);
	return $cache[$id] = ($post !== NULL);
}


/**
 * @ignore
 */
function _post_reply_is_first_floor($id)
{
	global $db, $DBOP;
	static $cache = array();
	if (array_key_exists($id, $cache))
		return $cache[$id];
	$post = _get_post_reply($id);
	$where = array($DBOP['='], 'tid', $id);
	db_where_add_and($where, array($DBOP['<='], 'time', $post['time']));
	db_where_add_and($where, array($DBOP['<'], 'id', $id));
	return $cache[$id] = ($db->get_number_of_rows('posts', $where) == 0);
}


/**
 * delete a post reply
 * @param int $id
 * @return void
 * @exception Exc_runtime
 */
function post_reply_delete($id)
{
	global $db, $DBOP, $user;
	if (!user_check_login())
		throw new Exc_runtime(__('You must login first'));
	if (!$user->is_grp_member(GID_ADMIN_POST))
		throw new Exc_runtime(__('You are not permitted to do this operation'));
	if (!post_reply_exists($id))
		throw new Exc_runtime(__('No such post reply whose id is %d', $id));
	$post = _get_post_reply($id);
	if (_post_reply_is_first_floor($id))
		$db->update_data('post_topics', array('content' => ''), array($DBOP['='], 'id', $post['tid']));
	$db->delete_item('posts', array($DBOP['='], 'id', $id));
}


