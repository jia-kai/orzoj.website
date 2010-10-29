<?php
/*
 * $File: post_list.php
 * $Date: Fri Oct 29 13:30:51 2010 +0800
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
 * page argument: <start_page=int>|<type=string>|<uid=int>|<author=string>|<subject=string>
 *		type string see includes/post.php : $POST_TYPE_SET
 *		type option can appear more than once
 *		uid: user id
 *		subject: string
 *		author: string
 */
require_once $includes_path . 'post.php';
require_once $theme_path . 'post_func.php';

$POSTS_PER_PAGE = 20;

$start_page = 1;
$post_type = NULL;
$post_uid = NULL;
$subject = NULL;
$author = NULL;

if (isset($page_arg))
{
	$options = explode('|', $page_arg);
	foreach ($options as $option)
	{
		$expr = explode('=', $option);
		if (count($expr) != 2)
			die(__('Unknown page argument.'));
		switch ($expr[0])
		{
		case 'start_page':
			$start_page = intval($expr[1]);
			break;
		case 'type':
			if (!array_search($expr[1], $POST_TYPE_SET))
				die(__('Unknown page argument.'));
			if ($post_type == NULL)
				$post_type = array($expr[1]);
			else
				$post_type[] = $expr[1];
			break;
		case 'uid':
			$post_uid = intval($uid);
			break;
		}
	}
}

if (isset($_POST['start_page']))
	$start_page = $_POST['start_page'];
if (isset($_POST['subject']))
	$subject = $_POST['subject'];
if (isset($_POST['author']))
	$author = $_POST['author'];
?>
<table class="page-table">
<?php
// cv : column value

/**
 * @ignore
 */
function _cv_type()
{
	global $post, $theme_path, $POST_TYPE_SET;
	echo '<img src="' . $theme_path . 'images/post-type-' .$post['type'] . '.gif' . '" alt="' . $POST_TYPE_SET[$post['type']] . '"/>';
}

/**
 * @ignore
 */
function _cv_subject()
{
	global $post;
	echo '<a class="post-subject" href="' . t_get_link('show-ajax-post-view-single', $post['id'], TRUE, TRUE) . '">' . $post['subject'] . '</a>';
}

/**
 * @ignore
 */
function _cv_author()
{
	global $post;
	echo '<a class="post-author" href="' . t_get_link('ajax-user-info', $post['uid'], TRUE, TRUE) . '">' . $post['nickname_uid'] . '</a>';
}

/**
 * @ignore
 */
function _cv_rep_viewed()
{
	global $post;
	echo '<span class="post-reply-amount">' . $post['reply_amount']. '</span>';
	echo '/';
	echo '<span class="post-viewed-amount">' . $post['viewed_amount'] . '</span>';
}

/**
 * @ignore
 */
function _cv_last_replay()
{
	global $post;
	echo '<div class="post-last-reply">';
	echo '<div class="post-last-reply-user"><a href="' . t_get_link('ajax-user-info', $post['last_reply_user'], TRUE, TRUE) . '">' . $post['nickname_last_reply_user'] . '</a></div>';
	echo '<div class="post-last-reply-time">' . time2str($post['last_reply_time']) . '</div>';
	echo '</div>';
}

$cols = array(
	// array(<display name>, <display function>)
	array('', '_cv_type'),
	array(__('Subject'), '_cv_subject'),
	array(__('Author'), '_cv_author'),
	array(__('Rep./Viewed'), '_cv_rep_viewed'),
	array(__('Last reply'), '_cv_last_replay')
);
echo '<tr>';
foreach ($cols as $val)
	echo "<th>$val[0]</th>";
echo '</tr>';

$total_page = post_get_post_amount(FALSE, $post_type, $post_uid);
if ($start_page < 1) $start_page = 1;
if ($start_page > $total_page) $start_page = $total_page;

$posts = post_get_post_list(
	array('id', 'time', 'uid', 'prob_id', 'score', 'type', 'last_reply_time', 'last_reply_user', 'subject', 'nickname_last_reply_user', 'nickname_uid', 'reply_amount', 'viewed_amount'), 
	FALSE, 
	$post_type,
	($start_page - 1) * $POSTS_PER_PAGE, 
	$POSTS_PER_PAGE,
	$post_uid
);

foreach ($posts as $post)
{
	$post = $post[0];
	echo '<tr>';
	foreach ($cols as $col)
	{
		echo '<td>';
		$func = $col[1];
		$func();
		echo '</td>';
	}
	echo '</tr>';
}
?>
</table>
<div id="post-list-navigator-bottom">
<?
/**
 * @ignore
 */
function _make_page_link($text, $page)
{
	global $post_type, $post_uid, $subject, $author;
	return sprintf('<a href="%s" onclick="%s">%s</a>',
		post_list_get_a_href($page, $post_type, $post_uid, $subject, $author),
		post_list_get_a_onclick($page, $post_type, $post_uid, $subject, $author),
		$text
		);
}

/**
 * @ignore
 */
function _make_page_nav()
{
	global $start_page, $total_page;
	$ret = '';

	if ($start_page > 1)
		$ret .= '&lt;' . _make_page_link(__('Prev'), $start_page - 1);

	if ($start_page < $total_page)
		$ret . = ($start_page > 1 ? ' | ' : '') . _make_page_link(__('Next'), $start_page + 1) . '&gt;';
	return $ret;
}
echo _make_page_nav();
$id = _tf_get_random_id();
$GoToPage = __('Go to page');
echo <<<EOF
<form action="#" id="post-list-goto-form" method="get" onsubmit="post_list_goto(); return false;">
<label for="$id" style="float: left">$GoToPage</label>
<input value="$start_page" name="goto_page" id="$id" style="float: left; widdth: 30px" type="text" />
/$total_page
</form>
EOF;
?>
</div><!-- id: post-list-navigator-bottom -->
<?php
echo $db->get_query_amount() . ' database queries. ' . count($posts) . ' posts.';
?>

<script type="text/javascript">
table_set_double_bgcolor();
$(".post-last-reply-user a").colorbox();
$("a.post-author").colorbox();
function post_list_goto()
{
	var t = $("#posts");
	t.animate({"opacity" : 0.5}, 1);
	page = $("")
}

</script>

