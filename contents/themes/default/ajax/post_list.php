<?php
/*
 * $File: post_list.php
 * $Date: Tue Nov 09 23:58:02 2010 +0800
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
 *		type: string see includes/post.php : $POST_TYPE_SET
 *		uid: int user id
 *		subject: string
 *		author: string both username and nickname are supported
 *		action: string ['goto-page', 'in-colorbox']
 *		prob_id: int
 */

require_once $includes_path . 'post.php';
require_once $theme_path . 'post_func.php';
require_once $theme_path . 'prob_func.php';
$POST_TOPIC_PER_PAGE = 20;

$start_page = 1;
$type = NULL;
$uid = NULL;
$subject = NULL;
$author = NULL;
$prob_id = NULL;
$prob_code = NULL;
$action = NULL;

if (isset($page_arg))
{
	$options = explode('|', $page_arg);
	foreach ($options as $option)
	{
		$expr = explode('=', $option);
		if (count($expr) != 2)
			die(__('Unknown page argument.'));
		$item = $expr[0];
		$$item = $expr[1];
	}
}

foreach (array('start_page', 'uid', 'subject', 'author', 'type', 'prob_id', 'action', 'prob_code') as $item)
	if (isset($_POST[$item]))
	{
		$$item = $_POST[$item];
		if (empty($$item))
			$$item = NULL;
	}

if (is_string($start_page) && !empty($start_page))
	$start_page = intval($start_page);
if (is_string($uid) && !empty($uid))
	$uid = intval($uid);

foreach (array('start_page', 'uid', 'prob_id') as $item)
	if (is_string($$item) && !empty($$item))
		$$item = intval($$item);

if (!empty($prob_code))
	$prob_id = prob_get_id_by_code($prob_code);

if (array_search($type, $POST_TYPE_SET) === FALSE)
	$type = NULL;
if ($type == 'all')
	$type = NULL;

if (is_string($subject))
	$subject_pattern = transform_pattern($subject);
else $subject_pattern = NULL;

?>

<div class="post-topic-list-container"> 
<?php if ($action != 'in-colorbox') {?>
<div id="post-filter-container">

<div class="post-filter" style="margin-right: 10px; float: left;">
<?php echo __('Filter:'); ?>
</div>

<form action="<?php t_get_link('show-ajax-post-list'); ?>" method="post" id="post-filter-form">

<?php
/**
 * @ignore
 */
function _make_input($prompt, $post_name)
{
	global $$post_name;
	if (isset($$post_name))
		$default = $$post_name;
	else $default = '';
	$id = get_random_id();
	echo <<<EOF
<div class="post-filter"><label for="$id">$prompt</label></div>
<div class="post-filter"><input type="text" name="$post_name" id="$id" value="$default" /></div>
EOF;
}
/**
 * @ignore
 */
function _make_select($prompt, $post_name, $options)
{
	global $type;
	if (is_string($type))
		$default = $type;
	else $default = '';
	$id = get_random_id();
	echo <<<EOF
<div class="post-filter"><label for="$id">$prompt</label></div>
<div class="post-filter"><select id="$id" name="$post_name">
EOF;
	asort($options);

	foreach ($options as $disp => $val)
	{
		if ((string)$val == $default) 
			$selected = 'selected="selected"';
		else $selected = '';
		echo <<<EOF
<option value="$val" $selected>$disp</option>
EOF;
	}
	echo '</select>';
}

_make_input(__('Subject'), 'subject');
_make_input(__('Author'), 'author');
_make_input(__('Problem code:'), 'prob_code');

$types = array();
foreach ($POST_TYPE_SET as $ty)
	$types[$POST_TYPE_DISP[$ty]] = $ty;
_make_select(__('Type'), 'type', $types);
echo '</div>';
$Apply = __('Apply');
echo <<<EOF
<div class="post-filter"><input type="submit" id="filter-apply-button" value="$Apply" /></div>
EOF;
?>
</form></div><!-- id: post-filter-container -->

<div style="float:right">
<a title="<?php echo __('Refresh')?>">
	<img src="<?php _url('images/refresh.gif');?>" alt="&lt;refresh&gt;"
	onclick="set_page(1); return false;" 
	style="cursor: pointer;" />
</a>
</div>

<?php }?>

<?php 
$error = FALSE;
$total_page = 1;
try
{
	$total_page = ceil(post_get_topic_amount($type, $uid, $subject_pattern, $author, $prob_id) / $POST_TOPIC_PER_PAGE);

	if ($start_page < 1) $start_page = 1;
	if ($start_page > $total_page) $start_page = $total_page;

	$posts = post_get_topic_list(
		array('id', 'uid', 'prob_id', 'score', 'type',
		'last_reply_time', 'last_reply_user', 
		'nickname_last_reply_user', 'username_last_reply_user', 
		'nickname_uid', 'username_uid', 
		'subject', 'content', 'is_top', 'is_locked', 'is_elaborate',
		'reply_amount', 'viewed_amount'), 
		$type,
		($start_page - 1) * $POST_TOPIC_PER_PAGE, 
		$POST_TOPIC_PER_PAGE,
		$uid,
		$subject_pattern,
		$author,
		$prob_id
	);
} catch (Exc_runtime $e)
{
	echo '<div style="clear: both;">' . $e->msg() . '</div>';
	$error = TRUE;
}



/**
 * @ignore
 */
function _make_page_link($text, $page)
{
	global $type, $uid, $subject, $author, $prob_id, $action;
	$href = ' href="' . post_list_get_a_href($page, $type, $uid, $subject, $author, $prob_id, $action) . '"';
	$onclick = " onclick=\"set_page($page); return false;\"";
	if ($action == 'in-colorbox')
	{
		$arg = post_list_pack_arg($page, $type, $uid, $subject, $author, $prob_id, $action);
		$href = ' href="' . t_get_link('ajax-post-list', $arg, TRUE, TRUE) . '"';
		$onclick = '';
	}
	return sprintf('<a %s %s>%s</a>',
		$href,
		$onclick,
		$text
	);
}

/**
 * @ignore
 */
function _make_page_nav()
{
	global $start_page, $total_page, $action;
	$ret = '';

	if ($start_page > 1)
		$ret .= '&lt;' . _make_page_link(__('Prev'), $start_page - 1);

	if ($start_page < $total_page)
		$ret .= ($start_page > 1 ? ' | ' : '') . _make_page_link(__('Next'), $start_page + 1) . '&gt;';

	if (user_check_login() && $action != 'in-colorbox')
		echo '<div class="post-new-topic-button"><a href="#new-topic">' . __('New topic') . '</a></div>';
	echo '<div class="post-list-navigator">';

	echo $ret;
	$id = get_random_id();
	$GoToPage = __('Go to page');
	static $cnt = 0;
	$cnt ++;
	echo <<<EOF
<form action="#" class="post-list-goto-form" id="post-list-goto-form$cnt" method="post" onsubmit="post_list_goto($cnt); return false;">
<label for="$id" style="float: left">$GoToPage</label>
<input value="$start_page" name="goto_page" id="$id" style="float: left; width: 30px" type="text" />
/$total_page
</form>
EOF;
	echo '</div><!-- class: post-list-navigator -->';
}
_make_page_nav(); 
?>
<?php
// cv : column value

/**
 * @ignore
 */
function _cv_type()
{
	global $post, $theme_path, $POST_TYPE_SET;
	$alt = $POST_TYPE_SET[$post['type']];
	echo '<img src="' . _url('images/post-type-' . $alt. '.png', TRUE) . '" alt="' . $alt . '" title="' . $alt . '"/>';
}

/**
 * @ignore
 */
function _cv_subject()
{
	global $post, $start_page, $type, $uid, $subject, $author, $action, $prob_id;
	$s = '<div class="post-topic-subject">';
	if ($post['is_top'])
		$s .= '<span class="post-subject-sticky">[' . __('Sticky') . ']</span>';
	if ($post['is_locked'])
		$s .= '<span class="post-subject-locked">[' . __('Locked') . ']</span>';
	$s .= '<a class="post-list-topic-subject" style="color: #9999ee" href="' ;
		$arg = post_view_single_pack_arg($post['id'], 1, $start_page, $type, $uid, $subject, $author, $prob_id, 'new_viewer,' . $action);
	if ($action == 'in-colorbox')
		$s .= t_get_link('ajax-post-view-single', $arg, TRUE, TRUE);
	else
		$s .= t_get_link('discuss', $post['id'], TRUE, TRUE);
	$s .= '" onclick="' . post_view_single_get_a_onclick($post['id'], 1, $start_page, $type, $uid, $subject, $author, $prob_id, 'new_viewer,' . $action) . '"';
	$s .= '>' . $post['subject'] . '</a>';
	if ($post['prob_id'])
	{
		$s .= ' ';
		$prob_code = prob_get_code_by_id($post['prob_id']);
		if ($action == 'in-colorbox')
			$s .= '<span style="color: #ccccff" class="post-list-prob-code">[' . $prob_code . ']</span>';
		else
			$s .= '<a style="color: #ccccff" class="post-list-prob-code" target="_blank" href="' . t_get_link('problem', $prob_code, TRUE, TRUE) . '">[' . $prob_code . ']</a>';
	}
	if ($post['is_elaborate'])
		$s .= '<span class="post-subject-elaborate">[' . __('Elaborate') . ']</span>';

	$s .= '</div>';
	echo $s;
}

/**
 * @ignore
 */
function _cv_author()
{
	global $post, $action;
	if ($action != 'in-colorbox')
		$s = '<a class="post-author" '
		. 'title="' . $post['username_uid'] . '" '
		. 'href="' . t_get_link('ajax-user-info', $post['uid'], TRUE, TRUE) . '" '
		. '>' . $post['nickname_uid'] . '</a>';
	else
		$s = '<span class="post-author " '
		. 'title="' . $post['username_uid']. '">'
		. $post['nickname_uid'] . '</span>';
	echo $s;
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

function _get_time_str($time)
{
	$now = time();
	$today = strftime('%d %b %Y', $now);
	$timeday = strftime('%d %b %Y', $time);
	if ($today == $timeday)
		return strftime('%H:%M', $time);
	else
		return strftime('%d %b', $time);
	$time = strftime('%H:%M:%S %d %b %Y', $time);
}

/**
 * @ignore
 */
function _cv_last_reply()
{
	global $post, $action;

	$s = '';
	$time = _get_time_str($post['last_reply_time']);
	$s .= $time . '</td>';

	$s .= '<td>';
	if ($action != 'in-colorbox')
		$s .= '<a class="post-last-reply-user" href="' . t_get_link('ajax-user-info', $post['last_reply_user'], TRUE, TRUE) . '"'
		. ' title="' . $post['username_last_reply_user'] . '">' . $post['nickname_last_reply_user'] . '</a>';
	else
		$s .= '<span title="' . $post['username_last_reply_user'] . '">' . $post['nickname_last_reply_user'] . '</span>';

	echo $s;
}

$cols = array(
	// array(<display name>, <display function>)
	array('', '_cv_type', 1),
	array(__('Subject'), '_cv_subject', 1),
	array(__('Author'), '_cv_author', 1),
	array(__('Pop.'), '_cv_rep_viewed', 1),
	array(__('Last reply'), '_cv_last_reply', 2)
);

if (!$error)
{
	$table_class = 'page-table';
	if ($action == 'in-colorbox')
		$table_class = 'colorbox-table';
	echo "<table class=\"$table_class\">";

	echo '<tr>';
	foreach ($cols as $val)
		echo "<th colspan=\"$val[2]\">$val[0]</th>";
	echo '</tr>';


	foreach ($posts as $post)
	{
		echo '<tr>';
		if (prob_future_contest($post['prob_id']))
			for ($i = count($cols); $i; $i --)
				echo '<td>---</td>';
		else
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
<?php
	_make_page_nav();
	echo $db->get_query_amount() . ' database queries. ' . count($posts) . ' posts.';
}
?>
<script type="text/javascript">

<?php if ($action != 'in-colorbox') {?>
table_set_double_bgcolor();
$("a.post-last-reply-user").colorbox();
$("a.post-author").colorbox();
<?php }?>

function set_page(page)
{
	var t = $(".post-topic-list-container");
	t.animate({"opacity" : 0.5}, 1);
	$.ajax({
		"url" : "<?php t_get_link('ajax-post-list', 'action=goto-page', FALSE, FALSE); ?>",
			"type" : "post",
			"cache" : false,
			"data" : ({
				"start_page" : page
<?php
foreach (array('uid', 'subject', 'author', 'type', 'prob_id', 'action') as $item)
	if (is_int($$item) || (is_string($$item) && strlen($$item)))
		echo ', "' . $item . '" : "' . $$item . '"';
?>
			}),
		"success" : function(data) {
			t.animate({"opacity" : 1}, 1);
			<?php if ($action == 'in-colorbox') {?>

			$.colorbox({"html" : data});
			$(".post-topic-list-container a").colorbox();
			<?php } else {?>
			t.html(data);
			<?php } ?>
		}
	});
	return false;

}

function post_list_goto(id)
{
	var page = $("#post-list-goto-form" + id + " input").val();
	set_page(page);
}

$("#post-filter-form").bind("submit", function(){
	var t = $("#posts-view");
	t.animate({"opacity" : 0.5}, 1);
	$.ajax({
		"type" : "post",
			"cache" : false,
			"url" : "<?php t_get_link('ajax-post-list', NULL, FALSE); ?>",
			"data" : $("#post-filter-form").serializeArray(),
		"success" : function(data) {
			t.animate({"opacity": 1}, 1);
			t.html(data);
		}
	});
	return false;
});
$("#filter-apply-button").button();
<?php if (user_check_login() && $action != 'in-colorbox') {?>
$("div.post-new-topic-button a").click(function(){
	$("#post-new-topic-container").slideToggle("slow");
})
	<?php }?>

	<?php if ($action == 'in-colorbox'){?>
	$(".post-topic-list-container a").colorbox();
<?php }?>

</script>
</div><!-- class: post-topic-list-container -->


<?php if ($action == 'goto-page') die;?>

<?php
if (user_check_login() && $action != 'in-colorbox')
{
?>
	<a id="new-topic"></a>
<?php
	require_once $theme_path . 'ajax/post_new_topic.php';
?>
	<script type="text/javascript">
	$("#post-new-topic-container").css("display", "none");
	</script>
<?php
} ?>

