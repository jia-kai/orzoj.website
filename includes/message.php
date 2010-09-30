<?php
/* 
 * $File: message.php
 * $Date: Thu Sep 30 19:59:41 2010 +0800
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

/**
 *  message structure
 */
class Message
{
	var $id, $time, $uid_snc, $uid_rcv, $title, $content, $is_read;
}

/**
 * Transform array returned by database queries
 * @param array $db_array array returned by database queries
 * @return array array of class Message
 */
function message_transform_result($db_array)
{
	$result = array();
	foreach ($db_array as $tmp => $arr)
	{
		$message = new Message();
		foreach (get_class_vars(get_class($message)) as $key => $value)
			$message->$key = $arr[$key];
		$result[] = $message;
	}
	return $result;
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
		'content' => $content,
		'is_read' => FALSE
	);
	$db->insert_into('messages', $value);
}

/**
 * Set message to read
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
}

/**
 * Get amount of message a user hasn't read
 * @param int $uid
 * @return int amount of messages not read
 */
function message_get_unread_amount($uid)
{
	global $db, $DBOP;
	$ret = $db->select_from('messages', array('id'),
		array($DBOP['='], 'uid_rcv', $uid_rcv)
	);
	return count($ret);
}

/**
 * Get message details
 * @param int $id
 * @return bool|Message return FALSE if no such message, otherwise details stored in class Message will be returned.
 */
function message_get_message_detail($id)
{
	global $db, $DBOP;
	$ret = $db->select_from('messages', NULL, 
		array($DBOP['='], 'id', $id));
	if (count($ret) == 0)
		return FALSE;
	if (count($ret) != 1)
		throw new Exc_orzoj(__('Message Error: more than one message has the same id %d', $id));
	$result = message_transform_result($ret);
	return $result[0];
}

/**
 * Get all messages of a user, sorted by descending time defaultly
 * @param int $uid
 * @param string $sort_way 'ASC' or 'DESC' 
 * @return Message
 */
function message_get_all_messages($uid, $sort_way = 'DESC')
{
	global $db, $DBOP;
	$ret = $db->select_from('messages', NULL,
		array($DBOP['='], 'uid_rcv', $uid),
		array('time' => $sort_way)
	);
	return message_transform_array($ret);
}

/**
 * Get unread messages a user has, sorted by descending time defaultly
 * @param int $uid
 * @param string $sort_by 'ASC' or 'DESC'
 * @return array an array of class Message
 */
function message_get_all_unread_messages($uid, $sort_by = 'DESC')
{
	global $db, $DBOP;
	$ret = $db->select_from('messages', NULL,
		array($DBOP['&&'], $DBOP['='], 'uid_rcv', $uid, $DBOP['='], 'is_read', FALSE),
		array('time' => $sort_by)
	);
	return message_transform_result($ret);
}

/**
 * Get a array of unread message id, sorted by descending time defaultly
 * @param int $uid
 * @param string $sort_by 'ASC' or 'DESC'
 * @return array array of int
 */
function message_get_unread_message_list($uid, $sort_by = 'DESC')
{
	global $db, $DBOP;
	$ret = $db->select_from('messages', array('id'),
		array($DBOP['='], 'uid_rcv', $uid),
		array('time' => $sort_by)
	);
	$result = array();
	foreach ($ret as $key => $value)
		$result[$key] = $value['id'];
	return $result;
}

/**
 * Get messages with a specific sender and receiver, sorted by descending time defaultly
 * @param int $uid_snd
 * @param int $uid_rcv
 * @param string $sort_by 'ASC' or 'DESC'
 * @return array an array of class Message
 */
function message_get_by_sender_and_receiver($uid_snd, $uid_rcv, $sort_by = 'DESC')
{
	global $db, $DBOP;
	$ret = $db->select_from('messages', NULL,
		array($DBOP['&&'], $DBOP['='], 'uid_snd', $uid_snd, $DBOP['='], 'uid_rcv', $uid_rcv),
		array('time' => $sort_by)
	);
	return message_transform_result($ret);
}

/**
 * Filter unread messages from some messages
 * @param array $messages array of Message
 * @return array array of Message
 */
function message_filter_unread_messages($messages)
{
	$result = array();
	foreach ($messages as $key => $message)
		if ($message->is_read == FALSE)
			$result[] = $message;
	return $result;
}

