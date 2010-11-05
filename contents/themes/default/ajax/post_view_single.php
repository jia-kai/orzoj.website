<?php
/*
 * $File: post_view_single.php
 * $Date: Fri Nov 05 10:00:14 2010 +0800
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
 * page argument:<tid=int>|<start_page=int>| ...(see below) or int: tid
 * also POST:
 *		tid: int
 *			the id of topic
 *		start_page: int
 *			the start page of a single post
 *		post_list_start_page: int
 *		post_list_type: string
 *		post_list_uid: int
 *		post_list_subject: string
 *		post_list_author: string
 *		post_list_prob_id: int
 *		action: string ['submit', 'submit-nosj', 'new_viewer', 'goto-page', 'goto-page-nojs', 'in-colorbox'] or array
 * POST:
 *		content: string
 *			reply content
 */

require_once $theme_path . 'post_func.php';
require_once $includes_path . 'avatar.php';

$POSTS_PER_PAGE = 20;

foreach (array('tid', 'start_page', 'post_list_start_page', 
	'post_list_type', 'post_list_uid', 'post_list_subject', 
	'post_list_author', 'post_list_prob_id') as $item)
	$$item = NULL;


$start_page = 1;
$tid = NULL;
$post_list_args = array('start_page', 'type', 'uid', 'subject', 'author', 'prob_id');
foreach ($post_list_args as $arg)
{
	$name = 'post_list_' . $arg;
	$$name = NULL;
}

if (isset($page_arg))
{
	if (is_numeric($page_arg))
	{
		$tid = intval($page_arg);
	}
	else
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
}

foreach (array('tid', 'start_page', 'post_list_start_page', 
	'post_list_type', 'post_list_uid', 'post_list_subject', 
	'post_list_author', 'post_list_prob_id', 'action') as $item)
	if (isset($_POST[$item]))
		$$item = $_POST[$item];

foreach (array('tid', 'start_page', 'post_list_start_page', 'post_list_uid', 'post_list_prob_id') as $item)
	$$item = intval($$item);

if (is_string($post_list_type))
	if (array_search($post_list_type, $POST_TYPE_SET) === FALSE)
		$post_list_type = NULL;

$action = explode(',', $action);

/**
 * @ignore
 */
function _action($item)
{
	global $action;
	return array_search($item, $action) !== FALSE;
}

if (_action('new_viewer'))
	post_topic_increase_statistic($tid,	'viewed_amount');

if (empty($tid))
	die(__('Which topic do you want to see?'));

if (_action('submit') || _action('submit-nojs'))
{
	try
	{
		post_reply();
		if (_action('submit'))
			echo 1;
	}
	catch (Exc_orzoj $e)
	{
		if (_action('submit'))
			die('0' . $e->msg());
	}
}

$fields = array('time', 'tid', 
	'content', 'floor',
	'nickname_last_modify_user', 'username_last_modify_user',
	'username_uid', 'nickname_uid'
);


$total_post = post_get_post_reply_amount($tid);
$total_page = ceil($total_post / $POSTS_PER_PAGE);

if ($start_page < 1) $start_page = 1;
if ($start_page > $total_page) $start_page = $total_page;
try
{
	$posts = post_get_post_reply_list($tid, $fields, ($start_page - 1) * $POSTS_PER_PAGE, $POSTS_PER_PAGE);
}
catch (Exc_orzoj $e)
{
	die($e->msg());
}

$topic = post_get_topic($tid);
?>
<?php if ((!(_action('goto-page') || _action('submit'))) || (_action('in-colorbox'))) {?>

<div class="posts-content-container">
<div class="posts-all-container" style="clear: both;">
<?php }?>

<a id="top"></a>

<div class="post-view-single-navigator-top">

<?php
// Back to list
$param = array($post_list_start_page, $post_list_type, $post_list_uid, $post_list_subject, $post_list_author, $post_list_prob_id, (_action('in-colorbox') ? 'in-colorbox' : ''));

$href = ' href="' . call_user_func_array('post_list_get_a_href', $param) . '"';

$onclick = ' onclick="' . call_user_func_array('post_list_get_a_onclick', $param) . '"';

if (_action('in-colorbox'))
{
	$arg = call_user_func_array('post_list_pack_arg', $param);
	$href = 'href="' . t_get_link('ajax-post-list', $arg, TRUE, TRUE) . '"';
	$onclick = '';
}
echo "<a $href $onclick>"
	. '<button type="button">' . __('Back to list') . '</button></a>';

?>
</div><!-- class: post-view-single-navigator-top -->
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
	global $post_list_uid, $post_list_subject, $post_list_author, $post_list_prob_id, $action;

	if (_action('in-colorbox'))
	{
		$arg = post_view_single_pack_arg($tid, $page, $post_list_start_page, $post_list_type, $post_list_uid, $post_list_subject, $post_list_author, $post_list_prob_id, implode(',', $action));
		return sprintf('<a href="%s">%s</a>',
			t_get_link('ajax-post-view-single', $arg, TRUE, TRUE),
			$text
			);
	}
	return sprintf('<a href="%s" onclick="%s">%s</a>',
		post_view_single_get_a_href($tid, $page, $post_list_start_page, $post_list_type, $post_list_uid, $post_list_subject, $post_list_author, $post_list_prob_id, 'goto-page-nojs'),
		"set_page($page); return false;",
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
	if ($total_page > 1)
		$ret .= '&lt;' . _make_page_link(__('First page'), 1) . '&gt; | ';
	if ($start_page > 1)
		$ret .= '&lt;' . _make_page_link(__('Prev'), $start_page - 1);
	if ($start_page < $total_page)
		$ret .= ($start_page > 1 ? ' | ' : '') . _make_page_link(__('Next') . '&gt;', $start_page + 1);
	if ($total_page > 1)
		$ret .= ' | &lt;' . _make_page_link(__('Last page'), $total_page) . '&gt; ';
	echo $ret;
}

/**
 * @ignore
 */
function _make_goto_form()
{
	global $start_page, $total_page;
	static $cnt = 0;
	$cnt ++;
	$ID = 'posts-goto-form' . $cnt;
	$id = get_random_id();
	$GoToPage = __('Go to page');
?>
	<form action="#" class="posts-goto-form" id="<?php echo $ID; ?>" method="post" onsubmit="posts_goto(<?php echo $cnt; ?>); return false;">
<label for="<?php echo $id;?>" style="float: left"><?php echo $GoToPage; ?></label>
<input value="<?php echo $start_page; ?>" name="goto_page" id="<?php echo $id; ?>" style="float: left; width: 30px" type="text" />
/<?php echo $total_page; ?>
</form>
<?php
}
function _make_posts_nav()
{
	echo '<div class="posts-nav">';
	_make_page_nav(); 
	_make_goto_form();
	echo '</div>';
}
_make_posts_nav();
?>

<div id="post-view-single-content" style="clear: both;">
<div class="post-subject">
<?php 
if ($topic['is_top'])
	echo '<span class="post-subject-sticky">[' . __('Sticky') . ']</span>';
if ($topic['is_boutique'])
	echo '<span class="post-subject-boutique">[' . __('Boutique') . ']</span>';
if ($topic['is_locked'])
	echo '<span class="post-subject-locked">[' . __('Locked') . ']</span>';
echo $topic['subject'];
?>
</div><!-- class: post-subject -->

<?php
/**
 * @ignore
 */
function _get_man_href($action)
{
	global $tid;
	return t_get_link('ajax-post-manipulate', 'tid=' . $tid . '|action=' . $action, TRUE, TRUE);
}
/**
 * @ignore
 */
function _make_post_topic_manipulation()
{
	global $topic;
	foreach (array('is_top', 'is_boutique', 'is_locked') as $item)
		$$item = $topic[$item];
	echo '<div class="post-topic-manipulation">';
	$Sticky = $is_top ? __('Unstick') : __('Stick');
	$Boutique = $is_boutique ? __('Unset boutique') : __('Set outique');
	$Locked = $is_locked ? __('Unlock') : __('Lock');
	$href_sticky = _get_man_href($is_top ? 'unstick' : 'stick');
	$href_boutique = _get_man_href($is_boutique ? 'unset_boutique' : 'set_boutique');
	$href_locked = _get_man_href($is_locked ? 'unlock' : 'lock');
	$href_delete = _get_man_href('delete');
	echo '<a class="post-subject-sticky" href="' . $href_sticky. '">[' . $Sticky. ']</a>';
	echo '<a class="post-subject-boutique" href="' . $href_boutique . '">[' . $Boutique . ']</a>';
	echo '<a class="post-subject-locked" href="' . $href_locked . '">[' . $Locked . ']</a>';
	echo '<a class="post-subject-delete" href="' . $href_delete. '">[' . __('DELETE') . ']</a>';
	echo '</div>';
}
// modify delete, set topic attrib
if (!_action('in-colorbox') && user_check_login())
	if ($user->is_grp_member(GID_ADMIN_POST))
		_make_post_topic_manipulation();
?>

<?php
$table_class = 'posts-table'; 
if (_action('in-colorbox'))
	$table_class = 'colorbox-table';
?>
<table class="<?php echo $table_class; ?>">
<tr>
	<th width="160px"><?php echo __('Author'); ?></th>
	<th><?php echo __('Content'); ?></th>
</tr>
<?php
foreach ($posts as $post)
{
	$avatar_url = avatar_get_url_by_user_id($post['uid']);
	$avatar_alt = __('Avatar');
	$nickname_uid = $post['nickname_uid'];
	$username_uid = $post['username_uid'];
	$nickname_url = t_get_link('ajax-user-info', $post['uid'], TRUE, TRUE);
	$floor = $post['floor'];
	$Floor = __('#%d', $floor);
	$content = $post['content'];
	$PostTime = __('Posted: ') . time2str($post['time']);
	$Reply_href = $DeletePost = '';
	if (user_check_login() && !_action('in-colorbox'))
	{
		$Reply_href = '<div class="posts-reply-a"><a href="#rep" onclick="append_reply(' . $floor. '); return false;">' . __('Reply') . '</div>';
		if ($user->is_grp_member(GID_ADMIN_POST))
			$DeletePost = '<div class="posts-delete-post-reply"><a href="' 
			. t_get_link('ajax-post-manipulate', 'pid=' . $post['id']. '|action=delete_post|tid=' . $tid . '|start_page=' . $start_page, TRUE, TRUE)
			. '">' . __('Delete this post') . '</a></div>';
	}
	$Top = __('To top');
	$author = "<a href=\"$nickname_url\" title=\"$username_uid\">$nickname_uid</a>";
	if (_action('in-colorbox'))
		$author = "<span title=\"$username_uid\">$nickname_uid</span>";
	echo <<<EOF
<tr>
	<td valign="top">
		<div class="posts-author">
			<div class="posts-avatar"><img src="$avatar_url" alt="$avatar_alt" title="$username_uid"/></div>
			<div class="posts-nickname">$author</div>
			<div class="posts-to-top"><a href="#top">$Top</a></div>
		</div>
	</td>
	<td valign="top">
		<div class="posts-container">
			<div class="posts-time">$PostTime</div>
			<div class="posts-floor">$Floor</div>
			$DeletePost
			<div class="posts-content">$content</div>
			$Reply_href
		</div>
	</td>
</tr>
EOF;
}

?>
</table>
<?php _make_posts_nav(); ?>
<script type="text/javascript">
/* common */
$("button").button();

<?php if (!_action('in-colorbox')) {?>
$(".posts-table tr:odd").addClass("posts-table-color-odd");
$(".posts-table tr:even").addClass("posts-table-color-even");
<?php } ?>

function set_page(page)
{
	var t = $(".posts-all-container");
	t.animate({"opacity" : 0.5}, 1);
	$.ajax({
		"url" : "<?php t_get_link('ajax-post-view-single', post_view_single_pack_arg($tid, $start_page, $post_list_start_page, $post_list_type, $post_list_uid, $post_list_subject, $post_list_author, $post_list_prob_id, 'goto-page,' . implode(',', $action)), FALSE, FALSE); ?>",
			"type" : "post",
			"cache" : false,
			"data" : ({"start_page" : page}),
		"success" : function(data) {
			t.animate({"opacity" : 1}, 1);
			<?php if (_action('in-colorbox')) {?>
			$.colorbox({"html" : data});
			$(".post-view-single-navigator-top a").colorbox();
			$("button").button();
			<?php } else {?>
			t.html(data);
			<?php }?>
		}
	});
	return false;
}

function posts_goto(id)
{
	page = $("#posts-goto-form" + id +" input").val();
	set_page(page);
}
</script>

</div><!-- id: post-view-single-content -->

<?php if ((_action('goto-page') || _action('submit')) && !_action('in-colorbox')) die;?>

</div><!-- class: posts-all-container -->

<script type="text/javascript">
<?php if (_action('in-colorbox')) { ?>
$(".post-view-single-navigator-top a").colorbox();
$("table.colorbox-table").css("width", "900px");
$("div.posts-nickname a").colorbox();
$("div.posts-nav a").colorbox();
$(".posts-content a").attr("target", "_blank");
<?php } else {?>
$(".posts-nickname a").colorbox();
<?php } ?>
</script>
<?php /* Reply form */ ?>

<?php
if (user_check_login() && !_action('in-colorbox') && (!$topic['is_locked'] || ($topic['is_locked'] && $user->is_grp_member(GID_ADMIN_POST)))) { 
	$post_url = t_get_link('show-ajax-post-view-single', 
		post_view_single_pack_arg($tid, 1000000, $post_list_start_page, $post_list_type, $post_list_uid, $post_list_subject, $post_list_author, $post_list_prob_id, 'submit-nojs'),
		TRUE, TRUE);
?>
<span style="float: left;"><?php echo __('Reply'); ?></span>
<form style="clear: both;" method="post" id="post-reply-form" action="<?php echo $post_url; ?>">
<table id="post-reply-table">
	<?php post_reply_get_form($tid)?>
	<a id="rep"></a>
</table>
<input id="post-reply-submit-button" type="submit" value="<?php echo __('Submit')?>" />
<?php // TODO checkcode? ?>
</form>
<?php }?>

</div><!-- class : posts-content-container -->





<?php /* Reply */ ?>
<?php if (user_check_login() && !_action('in-colorbox') && (!$topic['is_locked'] || ($topic['is_locked'] && $user->is_grp_member(GID_ADMIN_POST)))) {?> 
	<script type="text/javascript">
	/* logined */
	$(".post-topic-manipulation a").colorbox();
	$(".posts-delete-post-reply a").colorbox();
	$("#post-reply-submit-button").button();
	<?php $InReplyTo = __('In reply to'); ?>
	<?php $Editor = "CKEDITOR.instances.$editor_id";?>
	function append_reply(floor)
	{
		<?php echo $Editor; ?>.insertHtml(
			"<?php echo $InReplyTo; ?> #" + floor + "<?php echo __(': \n'); ?>"
		);
	}
<?php
	$url = t_get_link('ajax-post-view-single', 
		post_view_single_pack_arg($tid, 1000000, $post_list_start_page, $post_list_type, $post_list_uid, $post_list_subject, $post_list_author, $post_list_prob_id, 'submit'),
		FALSE, TRUE);
?>
	$("#post-reply-form").bind("submit", function(){
		var content = <?php echo $Editor; ?>.getData();
		//$("#post-reply-form").serializeArray(),

		var t = $(".posts-all-container");
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
				else if (data.charAt(0) == '1')
				{
					t.html(data.substr(1));
					<?php echo $Editor; ?>.setData("");
					setTimeout("$.colorbox.close();", 1000);
				}
				else
					$.colorbox({"html" : data});
		}
		});
		return false;
	});
	</script>
<?php }?>

