<?php
/* 
 * $File: post.php
 * $Date: Sun Oct 03 21:37:09 2010 +0800
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
	var $id, $time, $uid, $pid, $subject, $content;
}

/**
 * add a post, using the data posted by the form
 * @param int $pid problem id or parent post id, @see install/tables.php
 * @return int|string the new post id, or a human readable string describing the failure reason
 */
function post_add($pid)
{
	if (!user_check_login())
		return __('Please log in first');
	if (!isset($_POST['subject']))
		return __('No post subject');
	if (strlen($_POST['subject']) > POST_SUBJECT_LEN_MAX)
		return __('Subject is too long');
	$content = tf_form_get_rich_text_editor_data('content');
	if ($content === NULL)
		return __('No post content');

	global $user;
	$val = array('subject' => htmlencode($_POST['subject']),
		'content' => $content, 'time' => time(), 'uid' => $user->id,
		'pid' => $pid);
	return $db->insert_into('posts', filter_apply('before_post_add', $val));
}

/**
 * get the new post form
 * @return string HTML code
 */
function post_add_get_form()
{
	$str =
		tf_form_get_text_input(__('Subject:'), 'subject') .
		tf_form_get_rich_text_editor(__('Message:'), 'content');
	return filter_apply('after_post_add_form', $str);
}

/**
 * get post list
 *
 */

del
tree
form

