<?php
/* 
 * $File: message.php
 * $Date: Wed Oct 06 11:17:05 2010 +0800
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

require_once $includes_path . 'plugin.php';

/**
 *  message structure
 */
class Message
{
	var $id, $time, $uid_snd, $uid_rcv, $subject, $content, $is_read;

	/**
	 * set $this->content
	 * $this->id must be already set
	 * @return void
	 * @exception Exc_inner if $this->id not valid
	 */
	function set_content()
	{
		global $db, $DBOP;
		$row = $db->select_from('messages', 'content',
			array($DBOP['='], 'id', $this->id));
		if (count($row) != 1)
			throw new Exc_inner(__('attempt to read non-existent message'));

		$this->content = $row[0]['content'];
	}
}

/**
 * send a message using data posted
 * @return void
 */
function message_send()
{
	if (!user_check_login())
		throw new Exc_runtime(__('Not logged in'));
	global $db, $user;
	$VAL_SET = array('uid_rcv', 'subject');
	$val = array();
	foreach ($VAL_SET as $v)
	{
		if (!isset($_POST[$v]))
			throw new Exc_runtime(__('incomplete post'));
		$val[$v] = $_POST[$v];
	}
	if (!($val['uid_rcv'] = user_get_id_by_name($val['uid_rcv'])))
		throw new Exc_runtime(__('username does not exsists.'));
	$val['subject'] = htmlencode($val['subject']);
	$val['content'] = tf_form_get_rich_text_editor_data('content');
	$val['uid_snd'] = $user->id;
	$val['time'] = time();
	$val = filter_apply('before_message_send', $val);
	$db->insert_into('messages', $val);
}

/**
 * get a form for sending messages
 * @return string
 */
function message_send_get_form()
{
	$str = 
		tf_form_get_text_input(__('Receiver:'), 'uid_rcv') .
		tf_form_get_text_input(__('Title:'), 'subject') .
		tf_form_get_rich_text_editor(__('Content:'), 'content');
	return filter_apply('after_message_send_form', $str);
}

/**
 * mark the message to be read
 * @param int $id message id
 * @return void
 */
function message_set_read($id)
{
	global $db, $DBOP;
	$db->update_data('messages',
		array('is_read' => TRUE),
		array($DBOP['='], 'id', $id)
	);
	filter_apply_no_iter('after_message_read', $id);
}

/**
 * @ignore
 */
function _msg_where_and_eql(&$where, $col, $val)
{
	if (is_array($where))
		$where = array_merge(array($DBOP['&&'], $DBOP['='], $col, $val), $where);
	else $where = array($DBOP['='], $col, $val);
}

/**
 * get messages of a specific user satisfying some requirements
 * @param NULL|int $uid_rcv the user id of receiver, or NULL if unsecific
 * @param NULL|int $uid_snd the user id of sender, or NULL if unsecific
 * @param NULL|bool $read_flag if NULL, read and unread messages are returned; otherwise return as required
 * @param NULL|int $offset
 * @param NULL|int $cnt
 * @param string $sort_way the way to sort message by time ('DESC'|'ASC')
 * @return array array of class Message, but 'content' is not set
 */
function message_get($uid_rcv, $uid_snd = NULL, $read_flag = NULL,
	$offset = NULL, $cnt = NULL, $sort_way = 'DESC')
{
	global $db, $DBOP;
	$where = NULL;
	if (is_int($uid_snd))
	{
		_msg_where_and_eql($where, 'uid_snd', $uid_snd);
		_msg_where_and_eql($where, 'rm_snd', 0);
	}
	if (is_int($uid_rcv))
	{
		_msg_where_and_eql($where, 'uid_rcv', $uid_rcv);
		_msg_where_and_eql($where, 'rm_rcv', 0);
	}
	if (is_bool($read_flag))
		_msg_where_and_eql($where, 'is_read', $read_flag ? 1 : 0);

	$fields = array('id', 'time', 'uid_snd', 'uid_rcv', 'subject', 'is_read');
	$ret = $db->select_from('messages', $fields, $where, array('time' => $sort_way), $offset, $cnt);
	$result = array();
	foreach ($ret as $row)
	{
		$msg = new Message();
		foreach ($fields as $field)
			$msg->$field = $row[$field];
		unset($msg->content);
		$result[] = $msg;
	}
	return $result;
}

/**
 * get the number of messages satisfying some requirements
 * @see message_get
 * @return int
 */
function message_get_amount($uid_rcv, $uid_snd = NULL, $read_flag = NULL)
{
	global $db, $DBOP;
	$where = NULL;
	if (is_int($uid_snd))
	{
		_msg_where_and_eql($where, 'uid_snd', $uid_snd);
		_msg_where_and_eql($where, 'rm_snd', 0);
	}
	if (is_int($uid_rcv))
	{
		_msg_where_and_eql($where, 'uid_rcv', $uid_rcv);
		_msg_where_and_eql($where, 'rm_rcv', 0);
	}
	if (is_bool($read_flag))
		_msg_where_and_eql($where, 'is_read', $read_flag ? 1 : 0);

	return $db->get_numer_of_rows('messages', $where);
}

/**
 * request message deletion by the sender
 * @param int $id message id
 * @return void
 */
function message_del_by_sender($id)
{
	global $db, $DBOP;
	$where = array($DBOP['='], 'id', $id);
	$row = $db->select_from('messages', 'rm_rcv', $where);
	if (count($row) != 1)
		return;
	if ($row[0]['rm_rcv'] == 1)
	{
		$db->delete_item('messages', $where);
		filter_apply_no_iter('after_message_delete', $id);
	}
	else $db->update_data('messages', array('rm_snd' => 1), $where);
}

/**
 * request message deletion by the receiver
 * @param int $id message id
 * @return void
 */
function message_del_by_receiver($id)
{
	global $db, $DBOP;
	$where = array($DBOP['='], 'id', $id);
	$row = $db->select_from('messages', 'rm_snd', $where);
	if (count($row) != 1)
		return;
	if ($row[0]['rm_snd'] == 1)
	{
		$db->delete_item('messages', $where);
		filter_apply_no_iter('after_message_delete', $id);
	}
	else $db->update_data('messages', array('rm_rcv' => 1), $where);
}

