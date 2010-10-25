<?php
/*
 * $File: post_list.php
 * $Date: Mon Oct 25 13:13:32 2010 +0800
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

require_once $theme_path . 'prob_func.php';
require_once $includes_path . 'problem.php';
require_once $includes_path . 'post.php';
require_once $theme_path. 'post_func.php';

$POSTS_PER_PAGE = 5;

// TODO: filter
post_view_list_parse_arg();

$uid = NULL;
$depth = NULL;

if (isset($_POST['uid']))
	$uid = intval($_POST['uid']);
if (isset($_POST['depth']))
	$depth = intval($_POST['depth']);

$posts = post_get_list(
	FALSE, 
	($start_page - 1) * $POSTS_PER_PAGE,
	$POSTS_PER_PAGE,
	$depth,
	$uid,
	'DESC',
	'ASC'
);

$open_node_list = array();
/**
 * @ignore
 */
function _build_post_tree($post, $flag = false)
{
	global $open_node_list, $start_page;
	$cur = $post[0];
	$len = count($post);
	echo "<li>\n";
	// subject
	printf('<a href="%s" id="post-%d" onclick="%s"; return false;">%s</a>',
		post_view_single_get_a_href($cur['id']),
		$cur['id'],
		post_view_single_get_a_onclick($cur['id'], $start_page),
		$cur['subject']
	);
	// user
	printf('&nbsp;<a href="%s" class="view-user-info" style="color: blue;">%s</a>',
		t_get_link('ajax-user-info', $cur['uid'], TRUE, TRUE),
		user_get_nickname_by_id($cur['uid'])
	);
	// time
	echo '&nbsp;' . time2str($cur['time']);
	// related problem
	if ($flag)
		printf('&nbsp;<a href="%s" style="color: green;">%s</a>',
			t_get_link('problem', prob_get_code_by_id($cur['prob_id']), TRUE, TRUE),
			prob_get_title_by_id($cur['prob_id'])
		);
	if ($len > 1)
	{
		$open_node_list[] = $cur['id'];
		echo "<ul>\n";
		for ($i = 1; $i < $len; $i ++)
			_build_post_tree($post[$i]);
		echo "</ul>\n";
	}
	echo "</li>\n";
}

?>
<div id="post-tree">
<ul>
<?php
foreach ($posts as $post)
{
	echo '<hr />';
	_build_post_tree($post, TRUE);
}

/*
$s = '';
$n = count($open_node_list);
if ($n > 0)
{
	$s .= '"post-' . $open_node_list[0] . '"';
	for ($i = 1; $i < $n; $i ++)
		$s .= ', "post-' . $open_node_list[$i] . '"';
}

$s = '"core" : {"initially_open" : [ ' . $s . ' ]},';
 */
?>
</ul>
</div><!-- id: post-tree -->

<?php
/**
 * @ignore
 */
function _make_page_link($promt, $page)
{
	$url = post_view_list_get_a_href($page);
	$onclick = post_view_list_get_a_onclick($page);
	echo "<a href=\"$url\" onclick=\"$onclick\"; return false;\">$promt</a>";
}
?>
<div id="post-list-navigator-bottom" style="clear:both">
<?php 
$total_posts= $db->get_number_of_rows('posts', 
	array($DBOP['='], 'pid', 0)
);
$total_page = ceil($total_posts / $POSTS_PER_PAGE);
if ($start_page > 1) _make_page_link('&lt;' . __('Prev'), $start_page - 1); 
if ($start_page < $total_page)
{
	if ($start_page > 1) echo ' | ';
	_make_page_link(__('Next') . '&gt;', $start_page + 1);
}
?>
</div>
<script type="text/javascript">
/*
$("#post-tree").jstree({
	//<?php if ($n > 0) echo $s; ?>
	//"core" : {"initially_open" : ["post-1"]},
	"themes" : { 
		"theme" : "default",
		"dots"  : false,
		"icons" : false
	}
});
 */
$(".view-user-info").colorbox();
</script>
