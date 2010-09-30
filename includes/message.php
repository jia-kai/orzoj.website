<?php
/* 
 * $File: message.php
 * $Date: Thu Sep 30 21:55:10 2010 +0800
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
	var $id, $time, $uid_snd, $uid_rcv, $title, $content, $is_read;

	/**
	 * set attributes in this class
	 * $id must be valid
	 * @param array|NULL $fields the fileds needed to be set, or NULL if all
	 * @return void
	 * @exception Exc_inner if $id is not valid
	 */
	function set_val($fields)
	{
		global $db, $DBOP;
		$row = $db->select_from('messages', $fields,
			array($DBOP['='], 'id', $this->id));
		if (count($row) != 1)
			throw new Exc_inner(__('attempt to read non-existent message'));

		$row = $row[0];

		foreach ($fields as $f)
			$this->$f = $row[$f];
	}
}

/**
 * Send a message from user to user
 * @param int $uid_snd
 * @param int $uid_rcv
 * @param string $title
 * @param string $content
 * @return void
 */
function message_send($uid_snd, $uid_rcv, $title, $content)
{
	global $db;
	$value = array(
		'time' => time(),
		'uid_snd' => $uid_snd,
		'uid_rcv' => $uid_rcv,
		'title' => $title,
		'content' => $content
	);
	$value = filter_apply('before_send_message', $value);
	$db->insert_into('messages', $value);
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
	filter_apply_no_iter('after_read_message', $id);
}

/**
 * @ignore
 */
function _where_and_eql(&$where, $col, $val)
{
	if (is_array($where))
		$where = array_merge(array($DBOP['&&'], $DBOP['='], $col, $val), $where);
	else $where = array($DBOP['='], $col, $val);
}

/**
 * get messages of a specific user satisfying some requirements
 * @param NULL|int $uid_snd the user id of sender, or NULL if unsecific
 * @param NULL|int $uid_rcv the user id of receiver, or NULL if unsecific
 * @param NULL|bool $read_flag if NULL, read and unread messages are returned; otherwise return as required
 * @param NULL|array $fields fields needing to be set, must be a subset of attributes of class Message. NULL means all
 * @param NULL|int $offset
 * @param NULL|int $cnt
 * @param string $sort_way the way to sort message by time ('DESC'|'ASC')
 * @return array array of class Message
 */
function message_get($uid_snd, $uid_rcv = NULL, $read_flag = NULL,
	$fields = array('id'), $offset = NULL, $cnt = NULL, $sort_way = 'DESC')
{
	global $db, $DBOP;
	$where = NULL;
	if (is_int($uid_snd))
	{
		_where_and_eql($where, 'uid_snd', $uid_snd);
		_where_and_eql($where, 'rm_snd', 0);
	}
	if (is_int($uid_rcv))
	{
		_where_and_eql($where, 'uid_rcv', $uid_rcv);
		_where_and_eql($where, 'rm_rcv', 0);
	}
	if (is_bool($read_flag))
		_where_and_eql($where, 'is_read', $read_flag ? 1 : 0);

	$ret = $db->select_from('messages', $fields, $where, array('time' => $sort_way), $offset, $cnt);
	$result = array();
	foreach ($ret as $row)
	{
		$msg = new Message();
		foreach ($fields as $field)
			$msg->$field = $row[$field];
		$result[] = $msg;
	}
	return $result;
}

/**
 * get the number of messages satisfying some requirements
 * @see message_get
 * @return int
 */
function message_get_amount($uid_snd, $uid_rcv = NULL, $read_flag = NULL)
{
	global $db, $DBOP;
	$where = NULL;
	if (is_int($uid_snd))
	{
		_where_and_eql($where, 'uid_snd', $uid_snd);
		_where_and_eql($where, 'rm_snd', 0);
	}
	if (is_int($uid_rcv))
	{
		_where_and_eql($where, 'uid_rcv', $uid_rcv);
		_where_and_eql($where, 'rm_rcv', 0);
	}
	if (is_bool($read_flag))
		_where_and_eql($where, 'is_read', $read_flag ? 1 : 0);

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
		filter_apply_no_iter('after_delete_message', $id);
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
		filter_apply_no_iter('after_delete_message', $id);
	}
	else $db->update_data('messages', array('rm_rcv' => 1), $where);
}

