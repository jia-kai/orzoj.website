<?php
/*
 * $File: problem.php
 * $Date: Tue Oct 12 10:50:21 2010 +0800
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

?>
<div id="prob-left">
<h1 class="prob-left-title"><?php echo __("Problem Groups") . '<br />'; ?></h1>
<?php
/**
 * @ignore
 */
function _build_prob_grp_list($pgid)
{
	global $db, $DBOP;
	$grps = $db->select_from('prob_grps', array('id', 'name'),
		array($DBOP['='], 'pgid', $pgid));
	$flag = FALSE;
	if (count($grps)) $flag = TRUE;
	if ($flag && $pgid != 0) echo '<ul>';
	foreach ($grps as $grp)
	{
		echo '<li>';
	   	echo '<a href="#">' . $grp['name'] . '</a>';
		_build_prob_grp_list($grp['id']);
		echo '</li>';
	}
	if ($flag && $pgid != 0) echo '</ul>';
}
?>
<div class="prob-grp">
<ul id="prob-grp-tree" class="treeview">
<?php _build_prob_grp_list(0);?>
</ul>


<script type="text/javascript">
ddtreemenu.createTree("prob-grp-tree", true);
</script>
</div>
</div>
