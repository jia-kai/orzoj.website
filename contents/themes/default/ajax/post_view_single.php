<?php
/*
 * $File: post_view_single.php
 * $Date: Mon Nov 01 11:27:04 2010 +0800
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
 *
 * POST:
 *		content: string
 *			reply content
 *		start_page: int
 */

require_once $theme_path . 'post_func.php';
require_once $includes_path . 'avatar.php';

$POSTS_PER_PAGE = 20;

$start_page = 1;
$tid = NULL;
$post_list_args = array('start_page', 'type', 'uid', 'subject', 'author', 'action');
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
		case 'action':
			$action = $expr[1];
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

if ($action == 'submit')
{
	try
	{
		post_reply();
		echo '1';
	}
	catch (Exc_orzoj $e)
	{
		die('0' . $e->msg());
	}
}

if (isset($_POST['start_page']))
{
	$start_page = intval($_POST['start_page']);
}

?>

<div id="post-view-single-navigator-top">
<?php
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

$total_post = post_get_post_amount($tid);
$total_page = ceil($total_post / $POSTS_PER_PAGE);

if ($start_page < 1) $start_page = 1;
if ($start_page > $total_page) $start_page = $total_page;

$posts = post_get_post_list($tid, $fields, ($start_page - 1) * $POSTS_PER_PAGE, $POSTS_PER_PAGE);

$topic = post_get_topic($tid);

?>
<div id="post-view-single-content" style="clear: both;">

<div id="post-view-single-statistic">
<?php echo __('Total posts: <span>%d</span>', $total_post); ?>
</div>
<?php
/**
 * @ignore
 */
function _make_page_link($text, $page)
{
	global $tid,  $post_list_start_page, $post_list_type;
	global $post_list_uid, $post_list_subject, $post_list_author;
	return sprintf('<a href="%s" onclick="%s">%s</a>',
		post_view_single_get_a_href($tid, $page, $post_list_start_page, $post_list_type, $post_list_uid, $post_list_subject, $post_list_author),
		post_view_single_get_a_onclick($tid, $page, $post_list_start_page, $post_list_type, $post_list_uid, $post_list_subject, $post_list_author),
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
		$ret .= ($start_page > 1 ? ' | ' : '') . _make_page_link(__('Next') . '&gt;', $start_page + 1);
	return $ret;
}

/**
 * @ignore
 */
function _make_goto_form()
{
	global $start_page;
	$id = get_random_id();
	$GoToPage = __('Go to page');
?>
<form action="#" id="posts-goto-form" method="post" onsubmit="posts_goto(); return false;">
<label for="<?php echo $id;?>" style="float: left"><?php echo $GoToPage; ?></label>
<input value="<?php echo $start_page; ?>" name="goto_page" id="<?php echo $id; ?>" style="float: left; width: 30px" type="text" />
</form>
<?php
}
?>
<div class="posts-nav">
<?php 
echo _make_page_nav(); 
_make_goto_form();
?>
</div>
<div style="clear: both;">
<div id="post-subject"><?php echo $topic['subject']; ?></div>
<table class="posts-table">
<tr>
	<th width="160px"><?php echo __('Author'); ?></th>
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
	$Floor = __('#%d', $cur_floor ++);
	$content = $post['content'];
	$Reply_href = '';
	if (user_check_login())
		$Reply_href = '<div class="posts-reply-a"><a href="#rep" onclick="append_reply(' . $floor. '); return false;">' . __('Reply') . '</div>';
	echo <<<EOF
<tr>
	<td valign="top">
		<div class="posts-author">
			<div class="posts-avatar"><img src="$avatar_url" alt="$avatar_alt" /></div>
			<div class="posts-nickname"><a href="$nickname_url">$nickname_uid</a></div>
		</div>
	</td>
	<td>
		<div class="posts-content">
			<div class="floor">$Floor</div>
			<div class="posts-content">$content</div>
			$Reply_href
		</div>
	</td>
</tr>
EOF;
}

?>
</table>
<?php if (user_check_login()) { ?>
<span style="float: left;"><?php echo __('Reply'); ?></span>
<form style="clear: both;" method="post" id="post-reply-form" action="#">
<table id="post-reply-table">
	<?php post_reply_get_form($tid)?>
	<a name="rep"></a>
</table>
<input id="post-reply-submit-button" type="submit" value="<?php echo __('Submit')?>" />
<?php // TODO checkcode? ?>
</form>
<?php }
?>

</div>
</div><!-- id: post-view-single-content -->
<script type="text/javascript">
$("button").button();
$("div.posts-nickname a").colorbox();
<?php if (user_check_login()) {?> 
$("#post-reply-submit-button").button();
<?php $ReplyTo = __('Reply to'); ?>
function append_reply(floor)
{
	CKEDITOR.instances.post_reply_content.insertHtml(
		"<?php echo $ReplyTo; ?> #" + floor + ": "
	);
}
<?php
$url = t_get_link('ajax-post-view-single', 
	post_view_single_pack_arg($tid, 1000000, $post_list_start_page, $post_list_type, $post_list_uid, $post_list_subject, $post_list_author, 'submit'),
   	FALSE, TRUE);
?>
$("#post-reply-form").bind("submit", function(){
	var content = CKEDITOR.instances.post_reply_content.getData();
	//$("#post-reply-form").serializeArray(),

	var t = $("#posts-view");
	t.animate({"opacity" : 0.5}, 1);
	$.colorbox({"html" : "<?php echo __('Submitting...'); ?>"});
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php echo $url; ?>",
		"data": ({"post_reply_tid" : "<?php echo $tid; ?>",
			"post_reply_uid" : "<?php echo $user->id; ?>",
			"post_reply_content" : content
		}),
		"success" : function(data) {
			t.animate({"opacity" : 1}, 1);
			if (data.charAt(0) == '0')
			{
				$.colorbox({"html" : data.substr(1)});
			}
			else
			{
				t.html(data.substr(1));
				setTimeout("$.colorbox.close();", 1000);
			}
		}
	});
	return false;
});
<?php }?>
$(".posts-table tr:odd").addClass("posts-table-color-odd");
$(".posts-table tr:even").addClass("posts-table-color-even");
function post_list_goto()
{
	var t = $("#posts-view");
	t.animate({"opacity" : 0.5}, 1);
	page = $("#posts-goto-form input").val();
	$.ajax({
		"url" : "<?php t_get_link('ajax-post-view-single', post_view_single_pack_arg($tid, $start_page, $post_list_start_page, $post_list_type, $post_list_uid, $post_list_subject, $post_list_author), FALSE, FALSE); ?>",
		"type" : "post",
		"cache" : false,
		"data" : ({"start_page" : page}),
		"success" : function(data) {
			t.animate({"opacity" : 1}, 1);
			t.html(data);
		}
	});
	return false;
}
</script>

