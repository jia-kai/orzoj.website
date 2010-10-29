<?php
/*
 * $File: problem.php
 * $Date: Fri Oct 29 13:14:02 2010 +0800
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
 * page argument: [<problem code: int>]
 *		problem code: if set, the specific problem will be showed, otherwise						the problem list will be showed.
 */


require_once $theme_path . 'prob_func.php';
require_once $includes_path . 'problem.php';



?>
<script type="text/javascript">
function prob_view_set_content(addr)
{
	var t = $("#prob-view");
	t.animate({"opacity" : 0.5}, 1);
	$.ajax({
		"url" : addr,
		"success" : function(content) {
			t.animate({"opacity" : 1}, 1);
			t.html(content);
		}
	});
}
</script>

<div id="prob-container">
<div id="prob-navigator">
<?php require_once $theme_path . 'ajax/prob_filter.php'?>
<?php require_once $theme_path . 'ajax/prob_group_tree.php'; ?>
</div> <!-- id: prob-navigator -->

<div id="prob-view">
<?php
if (isset($page_arg))
{
	$pid = prob_get_id_by_code($page_arg);
	if ($pid === NULL) // no such problem
		die('just for fun');
	$gid = -1;
	$start_page = -1;
	require_once $theme_path . 'ajax/prob_view_single.php';
}
else
	require_once $theme_path . 'ajax/prob_view_by_group.php';
?>

</div> <!-- id: prob-view -->
</div> <!-- id: prob-container -->
