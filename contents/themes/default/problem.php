<?php
/*
 * $File: problem.php
 * $Date: Tue Oct 12 00:31:57 2010 +0800
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
<?php echo __("Problem Groups") . '<br />'; ?>
<?php
/**
 * @ignore
 */
function _build_prob_grp_list($pgid, $blank)
{
	global $db, $DBOP;
	$grps = $db->select_from('prob_grps', array('id', 'title'),
		array($DBOP['='], 'pgid', $pgid));
	foreach ($grps as $grp)
	{
		echo '<li>' . $blank . $grp['title'] . '</li>';
		_build_prob_grp_list($grp['id'], $blank . '&nbsp;&nbsp;');
	}
}
?>
<div class="prob-grp">
<?php _build_prob_grp_list(0, '');?>
</div>
</div>
