<?php
/*
 * $File: contest.php
 * $Date: Thu Oct 28 11:06:02 2010 +0800
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
 * page argument: [<contest id:int>]
 *		contest id: if set, display information about this contest
 */

if (!is_null($page_arg))
{
	$_POST['id'] = $page_arg;
	require_once $theme_path . 'ajax/contest_view.php';
	return;
}

?>

<div id="contest-page">
<div id="contest-tabs">
	<ul>
	<li><a href="<?php t_get_link('show-ajax-contest-list', 'all');?>"
		id="contest-tabs-list-all"><?php echo __('All Contests');?></a></li>

	<li><a href="<?php t_get_link('show-ajax-contest-list', 'past');?>"
		id="contest-tabs-list-past"><?php echo __('Past Contests');?></a></li>

	<li><a href="<?php t_get_link('show-ajax-contest-list', 'current');?>"
		id="contest-tabs-list-current"><?php echo __('Current Contests');?></a></li>

	<li><a href="<?php t_get_link('show-ajax-contest-list', 'upcoming');?>"
		id="contest-tabs-list-upcoming"><?php echo __('Upcoming Contests');?></a></li>
	</ul>
</div>
</div>

<script type="text/javascript">
<?php
foreach (array('all', 'past', 'current', 'upcoming') as $f)
	echo "$('#contest-tabs-list-$f').attr('href', '" . t_get_link('ajax-contest-list', $f, FALSE, TRUE) ."');";
?>
$("#contest-tabs").tabs();
</script>

