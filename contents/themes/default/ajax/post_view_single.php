<?php
/*
 * $File: post_view_single.php
 * $Date: Sun Oct 31 18:30:14 2010 +0800
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

/*
 * page argument:<tid=int>|<start_page=int>| ...(see below)
 *		tid: int
 *			the id of topic
 *		start_page: int
 *			the start page of a single post
 *		post_list_start_page: int
 *		post_list_type: array or string
 *		post_list_uid: int
 *		post_list_subject: string
 *		post_list_author: string
 */

require_once $theme_path . 'post_func.php';
require_once $includes_path . 'avatar.php';

$POSTS_PER_PAGE = 20;

$start_page = 1;
$tid = NULL;
$post_list_args = array('start_page', 'type', 'uid', 'subject', 'author');
foreach ($post_list_args as $arg)
{
	$name = 'post_list_' . $arg;
	$$name = NULL;
}

if (isset($page_arg))
{
	$options = explode('|', $page_arg);
	foreach ($options as $option)
	{
		$expr = explode('=', $option);
		if (count($expr) != 2)
			die(__('Invalid page argument.'));
		switch ($expr[0])
		{
		case 'tid':
			$tid = intval($expr[1]);
			break;
		case 'start_page':
			$start_page = intval($expr[1]);
			break;
		default:
			$name = $expr[0];
			$val = $expr[1];
			foreach ($post_list_args as $arg)
				if ($name == 'post_list_' . $arg)
				{
					$$name = $val;
					break;
				}
			break;
		}
	}
}

if (is_string($post_list_start_page))
	$post_list_start_page = intval($post_list_start_page);
if (is_string($post_list_uid))
	$post_list_uid = intval($post_list_uid);
if (is_string($post_list_type))
	if (array_search($post_list_type, $POST_TYPE_SET) === FALSE)
		$post_list_type = NULL;

echo '<div id="post-view-single-navigator-top">'; 

// Reply

// Back to list
echo '<a href="' . post_list_get_a_href($post_list_start_page, $post_list_type, $post_list_uid, $post_list_subject, $post_list_author) . '"'
	. ' onclick="' . post_list_get_a_onclick($post_list_start_page, $post_list_type, $post_list_uid, $post_list_subject, $post_list_author) . '">'
	. '<button type="button">' . __('Back to list') . '</button></a>';

echo '</div><!-- id: post-view-single-navigator-top -->';

$fields = array('time', 'nickname_uid', 'tid', 
	'subject', 'content',
	'nickname_last_modify_user'
);

$posts = post_get_post_list($tid, $fields, ($start_page - 1) * $POSTS_PER_PAGE, $POSTS_PER_PAGE);

$total_post = post_get_post_amount($tid);
$total_page = ceil($total_post / $POSTS_PER_PAGE);
$topic = post_get_topic($tid);
?>
<div id="post-view-single-content" style="clear: both;">

<div id="post-view-single-stastic">
<?php echo __('Total posts: <span>%d</span>', $total_post); ?>
</div>
<div style="clear: both;">
<?php echo $topic['subject']; ?>
<table>
<tr>
<th><?php echo __('Author'); ?></th>
<th><?php echo __('Content'); ?></th>
</tr>
<?php
$cur_floor = ($start_page - 1) * $POSTS_PER_PAGE + 1;
foreach ($posts as $post)
{
	$avatar_url = avatar_get_url_by_user_id($post['uid']);
	$avatar_alt = __('Avatar');
	$nickname_uid = $post['nickname_uid'];
	$nickname_url = t_get_link('ajax-user-info', $post['uid'], TRUE, TRUE);
	$floor = $cur_floor;
	$Floor = __('Floor %d', $cur_floor ++);
	$content = $post['content'];
	$Reply = __('Reply');
	echo <<<EOF
<tr>
	<td>
		<div class="posts-author">
			<div class="posts-avatar"><img src="$avatar_url" alt="$avatar_alt" /></div>
			<div class="posts-nickname"><a href="$nickname_url">$nickname_uid</a></div>
		</div>
	</td>
	<td>
		<div class="posts-content">
			<div style="text-align: right;">$Floor</div>
			<div class="posts-content">$content</div>
			<div class="posts-reply-a"><a href="#rep" onclick="append_reply($floor);">$Reply</div>
		</div>
	</td>
</tr>
EOF;
}
?>
</table>
<a name="rep"></a>
</div>
</div><!-- id: post-view-single-content -->
<script type="text/javascript">
$("button").button();
$("div.posts-nickname a").colorbox();
function append_reply(floor)
{
}
</script>

