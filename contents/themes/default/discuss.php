<?php
/* 
 * $File: discuss.php
 * $Date: Fri Oct 29 12:37:23 2010 +0800
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
 * page argument: see ajax/post_view_single.php
 */
?>
<div id="post-page">
	<div id="post-tabs">
	<ul>
	<li><a href="<?php t_get_link('show-ajax-posts', $page_arg); ?>" id="posts"><?php echo __('Posts'); ?></a></li>
	<li><a href="<?php t_get_link('show-ajax-post-new-topic'); ?>" id="post-new-topic"><?php echo __('New Topic'); ?></a></li>
	</ul>
	</div>
</div>
<script type="text/javascript">
$("#posts").attr("href", "<?php t_get_link('ajax-posts', NULL, FALSE);?>");
$("#post-new-topic").attr("href", "<?php t_get_link('ajax-post-new-topic', NULL, FALSE);?>");
$("#post-tabs").tabs();
</script>
