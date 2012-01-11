<?php
/*
 * $File: post_manipulate.php
 * $Date: Thu Nov 04 19:55:42 2010 +0800
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

require_once $includes_path . 'user.php';
require_once $includes_path . 'post.php';

try
{
	if (!user_check_login() || (user_check_login() && !$user->is_grp_member(GID_ADMIN_POST)) || !isset($page_arg) || empty($page_arg))
		die(__('Hi buddy, what\'s up?'));

	$POST_MAN_OPTION = array('tid', 'action', 'pid', 'start_page');
	$POST_ACTION_SET = array(
		'stick', 'unstick',
		'set_elaborate', 'unset_elaborate',
		'lock', 'unlock', 
		'delete', 'do_delete',
		'delete_post', 'do_delete_post'
	);
	foreach ($POST_MAN_OPTION as $item) 
		$$item = NULL;

	$options = explode('|', $page_arg);
	foreach ($options as $option)
	{
		$expr = explode('=', $option);
		if (count($expr) != 2)
			die(__('Unknown argument'));
		if (array_search($expr[0], $POST_MAN_OPTION) === FALSE)
			die(__('Unknown option'));
		switch ($expr[0])
		{
		case 'tid':
			$tid = intval($expr[1]);
			break;
		case 'pid':
			$pid = intval($expr[1]);
			break;
		case 'start_page':
			$start_page = intval($expr[1]);
			break;
		case 'action':
			if (array_search($expr[1], $POST_ACTION_SET) === FALSE )
				die(__('Unknown action'));
			$action = $expr[1];
		}
	}
	if (empty($tid))
		die(__('What do you wanna do?'));

	if (!empty($tid) && !post_topic_exists($tid))
		die(__('Post topic id: %d does not exists', $tid));

	if (!empty($pid) && !post_reply_exists($pid))
		die(__('Post reply id: %d does not exists', $pid));

	echo '<div class="post-manipulate">';
	function _report_success($item)
	{
		global $tid;
		echo __('%s succeed.', $item);
?>
		<script type="text/javascript">
			setTimeout("$.colorbox.close()", 800);
			posts_view_set_content("<?php t_get_link('ajax-post-view-single', 'tid=' . $tid, FALSE, FALSE);?>");
		</script>
<?php
	}
	switch ($action)
	{
	case 'stick':
		post_topic_set_attrib($tid, 'is_top', TRUE);
		_report_success(__('Stick'));
		break;
	case 'unstick':
		post_topic_set_attrib($tid, 'is_top', FALSE);
		_report_success(__('Unstick'));
		break;
	case 'set_elaborate':
		post_topic_set_attrib($tid, 'is_elaborate', TRUE);
		_report_success(__('Set elaborate'));
		break;
	case 'unset_elaborate':
		post_topic_set_attrib($tid, 'is_elaborate', FALSE);
		_report_success(__('Unset elaborate'));
		break;
	case 'lock':
		post_topic_set_attrib($tid, 'is_locked', TRUE);
		_report_success(__('Lock'));
		break;
	case 'unlock':
		post_topic_set_attrib($tid, 'is_locked', FALSE);
		_report_success(__('Unlock'));
		break;
	case 'delete':
		// TODO check code
		$subject = post_get_topic($tid, 'subject');
		$subject = $subject['subject'];
		$href_yes = t_get_link('ajax-post-manipulate', 'tid=' . $tid . '|action=do_delete', TRUE, TRUE);
		$href_no = t_get_link('discuss', 'tid=' . $tid, TRUE, TRUE);
		$onclick_yes = '';
		$onclick_no = '$.colorbox.close(); return false;';
		echo '<a class="delete-post-yes" href="' . $href_yes . '" onclick="' . $onclick_yes . '"><button>' . __('Yes') . '</button></a>';
		echo __('Are you sure to delete <b>%s</b> ?', $subject);
		echo '<a class="delete-post-no" href="' . $href_no . '" onclick="' . $onclick_no . '"><button>' . __('No') . '</button></a>';
		echo '<script type="text/javascript">$("a.delete-post-yes").colorbox();</script>';
		break;
	case 'do_delete':
		post_topic_delete($tid);
		echo __('Topic has been successfully deleted');
?>
		<script type="text/javascript">
			setTimeout("$.colorbox.close()", 1500);
			posts_view_set_content("<?php t_get_link('ajax-post-list');?>");
		</script>
<?php
		break;

	case 'delete_post':
		$href_yes = t_get_link('ajax-post-manipulate', 'tid=' . $tid . '|start_page=' . $start_page . '|pid=' . $pid. '|action=do_delete_post', TRUE, TRUE);
		$href_no = t_get_link('discuss', 'tid=' . $tid, TRUE, TRUE);
		$onclick_yes = '';
		$onclick_no = '$.colorbox.close(); return false;';
		echo '<a class="delete-post-yes" href="' . $href_yes . '" onclick="' . $onclick_yes . '"><button>' . __('Yes') . '</button></a>';
		echo __('Are you sure to delete?');
		echo '<a class="delete-post-no" href="' . $href_no . '" onclick="' . $onclick_no . '"><button>' . __('No') . '</button></a>';
		echo '<script type="text/javascript">$("a.delete-post-yes").colorbox();</script>';

		break;
	case 'do_delete_post':
		post_reply_delete($pid);
		echo __('Reply has been successfully deleted');
?>
		<script type="text/javascript">
			setTimeout("$.colorbox.close()", 1500);
			posts_view_set_content("<?php t_get_link('ajax-post-view-single', 'tid=' . $tid . '|start_page=' . $start_page, FALSE, FALSE);?>");
		</script>
<?php

		break;
	}
	echo '</div>';
	if ($action != 'delete' && $action != 'delete_post')
		echo '<script type="text/javascript">$(".post-manipulate a").colorbox();</script>';
} catch (Exc_orzoj $e)
	{
		die($e->msg());
	}
?>
