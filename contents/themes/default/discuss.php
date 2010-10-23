<?php
/* 
 * $File: discuss.php
 * $Date: Sat Oct 23 15:44:40 2010 +0800
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
 * page argumnet:
 *		id: int
 *			the id of post
 */
?>

<div id="post-view">

<?php
$POSTS_PER_PAGE = 20;

// parse arg
if (isset($page_arg))
{
	$page_arg = $page_arg . '|1';
	require_once $theme_path . 'ajax/post_view_single.php';
}
else
{
	$page_arg = '1';
	require_once $theme_path . 'ajax/post_list.php';
}
?>
</div><!-- id: post-view -->
<script type="text/javascript">
function post_view_set_content(addr)
{
	var t = $("#post-view");
	t.animate({"opacity" : 0.5}, 1);
	$.ajax({
		"url" : addr,
		"success" : function(data) {
			t.animate({"opacity" : 1}, 1);
			t.html(data);
		}
	});
}
</script>

