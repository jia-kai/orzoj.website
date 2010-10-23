<?php
/*
 * $File: post_view_single.php
 * $Date: Sat Oct 23 15:52:19 2010 +0800
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

require_once $theme_path . 'post_func.php';
/*
 * page argument: 
 *		id: int
 *			post id
 *		start_page: int
 *			the page reveal after click back to posts
 */

if (!isset($page_arg))
	die('No arguments...');

post_view_single_parse_arg();

$post = $db->select_from('posts', NULL,
	array($DBOP['='], 'id', $id)
);
$post = $post[0];

// TODO: set top
?>
<div id="post-view-single">
	<div id="post-view-single-subject">
		<?php echo $post['subject']; ?>
	</div>
	<div>
	</div>
</div>

