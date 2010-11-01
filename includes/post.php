<?php
/* 
 * $File: post.php
 * $Date: Mon Nov 01 19:49:39 2010 +0800
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

/* post topic constants */
$POST_TOPIC_FIELDS_SET = array('id', 'time', 'uid', 'prob_id',
	'reply_amount', 'viewed_amount', 'floor_amount',
	'priority', 'is_top', 'type',
   	'last_reply_time', 'last_reply_user', 'subject', 'content',
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
$POST_TYPE_TO_NUM = array();
$tmp = 0;
foreach ($POST_TYPE_SET as $val)
	$POST_TYPE_TO_NUM[$val] = $tmp ++;
unset($tmp);

$POST_TOPIC_ATTRIB_SET = array('is_top', 'is_locked', 'is_boutique');
$POST_TOPIC_STATISTIC_SET = array('reply_amount', 'viewed_amount', 'floor_amount');

$POST_PRIORITY = array('is_top' => 5, 'normal' => 0);

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
 * @return void
 */
function post_add_topic_get_form($prob_id = 0)
{
	$str = 
		tf_form_get_post_type_selector()
		. tf_form_get_text_input(__('Subject:'), 'subject')
		. tf_form_get_rich_text_editor(__('Content:'), 'content')
		. tf_form_get_text_input(__('Problem id:'), 'prob_id', NULL, "$prob_id");
	echo filter_apply('after_post_add_form', $str);
}

/**
 *
 */
function post_add_topic($prob_id = 0)
{
	global $db, $DBOP, $user;
	if (!user_check_login())
		throw new Exc_runtime(__('Please login first'));
	if (!array_key_exists('subject', $_POST) || (strlen($_POST['subject']) == 0))
		throw new Exc_runtime(__('No post subject'));
	if (strlen($_POST['subject']) > POST_SUBJECT_LEN_MAX)
		throw new Exc_runtime(__('Subject is too long'));
	$content = tf_form_get_rich_text_editor_data('content');

	$time = time();
	$val = array('subject' => htmlencode($_POST['subject']),
		'time' => $time, 'uid' => $user->id,
		'prob_id' => $prob_id,
		'last_reply_time' => $time, 'last_reply_user' => $user->id
	);
	$val = filter_apply('before_post_topic_add', $val);
	$topic_id = $db->insert_into('post_topics', $val);
	$db->insert_into('posts', array(
		'time' => $time, 'uid' => $user->id,
		'tid' => $topic_id,
		'content' => $content
		));
	return $topic_id;
}

/**
 * @ignore
 */
function _post_get_topic_list_build_where($type, $uid, $subject, $author, $attrib)
{
	global $DBOP, $POST_TYPE_SET, $POST_TYPE_TO_NUM, $AUTHOR_TYPE_SET, $POST_TOPIC_ATTRIB_SET;
	$where = NULL;
	if (is_string($type))
		db_where_add_and($where, array($DBOP['='], 'type', $POST_TYPE_TO_NUM[$type]));

	if (is_int($uid))
	{
		if (!user_exists($uid))
			throw new Exc_runtime(__('No such user whose id is %d!', $uid));
		db_where_add_and($where, array($DBOP['='], 'uid', $uid));
	} else if (is_string($author) && strlen($author))
	{
		$author_id = user_get_id_by_username($author);
		if ($author_id === NULL)
			throw new Exc_runtime(__('No such user whose %s is %s!', $tmp[0], $author));
		db_where_add_add($where, array($DBOP['='], 'uid', $author_id));
	}

	if (is_string($subject) && strlen($subject))
		db_where_add_and($where, array($DBOP['like'], 'subject', $subject));

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
function post_get_topic_list($fields = NULL, $type = NULL, $offset = NULL, $count = NULL, $uid = NULL, $subject = NULL, $author = NULL, $attrib = NULL)
{
	global $db, $DBOP, $POST_TOPIC_FIELDS_SET, $POST_TOPIC_USER_ID_SET, $POST_USER_NAME_SET;
	$fields = array_intersect($fields, $POST_TOPIC_FIELDS_SET);
	if (array_search('id', $fields) === FALSE)
		$fields[] = 'id';

	// additional fields
	$additional_fields = _deal_addtional_fields_start($fields, $POST_TOPIC_USER_ID_SET);

	$where = _post_get_topic_list_build_where($type, $uid, $subject, $author, $attrib);

	$order_by = array('priority' => 'DESC', 'last_reply_time' => 'DESC');

	$list = $db->select_from('post_topics', $fields, $where, $order_by, $offset, $count);

	_deal_addtional_fields_end($fields, $POST_TOPIC_USER_ID_SET, $additional_fields, $list);

	return filter_apply('after_post_topic_list', $list);
}

/**
 * get amount of topic in a specific limitation
 * @see post_get_topic_list
 */
function post_get_topic_amount($type = NULL, $uid = NULL, $subject = NULL, $author = NULL, $attrib = NULL)
{
	global $db;
	$where = _post_get_topic_list_build_where($type, $uid, $subject, $author, $attrib);
	return $db->get_number_of_rows('post_topics', $where);
}

/**
 *
 */
function post_get_topic($id)
{
	global $db, $DBOP;
	$ret = $db->select_from('post_topics', NULL, array($DBOP['='], 'id', $id));
	if (count($ret) != 1)
		return NULL;
	return $ret[0];
}

/**
 *
 */
function post_topic_exists($id)
{
}

/**
 *
 */
function post_modify_topic_priority($id, $priority)
{
}

/**
 * 
 */
function post_del_topic($id)
{
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
	global $db, $DBOP, $POST_TOPIC_ATTRIB_SET;
	if (array_search($attrib, $POST_TOPIC_ATTRIB_SET) === FALSE)
		return FALSE;
	if (!is_bool($status))
		return FALSE;
	$db->update_data('post_topics', array($attrib => $status),
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
 */
function post_get_post_list($tid, $fields = NULL, $offset = NULL, $count = NULL, $order = 'ASC')
{
	global $POSTS_FIELD_SET, $db, $DBOP, $POSTS_USER_ID_SET;

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
function post_get_post_amount($tid)
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

	$tid = $_POST['post_reply_tid'];
	$uid = $_POST['post_reply_uid'];

	$content = tf_form_get_rich_text_editor_data('post_reply_content');

	$where = array($DBOP['='], 'id', $tid);
	$topic = $db->select_from('post_topics', array('floor_amount', 'reply_amount'), $where);
	$topic = $topic[0];

	$basic_floor = $topic['floor_amount'];

	$time = time();
	for($len = strlen($content); $len > 0; $len -= POST_CONTENT_LEN_MAX)
	{
		$db->insert_into('posts', 
			array('time' => $time,
				'uid' => $uid,
				'tid' => $tid,
				'content' => substr($content, 0, POST_CONTENT_LEN_MAX),
				'floor' => ++ $basic_floor
			)
		);
		if ($len > POST_CONTENT_LEN_MAX)
			$content = substr($content, POST_CONTENT_LEN_MAX);
	}

	$nrep = $topic['reply_amount'];
	$db->update_data('post_topics',
		array('reply_amount' => $nrep + 1,
			'last_reply_time' => $time,
			'last_reply_user' => $uid,
			'floor_amount' => $basic_floor
		), $where
	);
}

