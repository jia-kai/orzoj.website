<?php
/*
 * $File: prob_view_single.php
 * $Date: Thu Oct 14 21:08:45 2010 +0800
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
require_once $includes_path . 'problem.php';
require_once $theme_path . 'prob_func.php';

prob_view_single_parse_arg();

try
{
	// navigator
	$content = '<div id="prob-view-single-navigator"><table><tr>';
	if ($start_page != -1)
		// previous page
		$content .= '<td><a href="' . prob_view_by_group_get_a_href($gid, $start_page) 
		. '" onclick="' . prob_view_by_group_get_a_onclick($gid, $start_page) .'">'
		. __('previous page')
		. '</a></td>';

	$content .= '<td><a id="prob-submit-link" href="' . t_get_link('ajax-prob-submit', "$pid", TRUE, TRUE) . '">' . __('submit') . '</a></td>';
	$content .= '</tr></table></div>';
	// content
	$content .= prob_view($pid);

	echo $content;
}
catch (Exc_runtime $e)
{
	die(__('Hello buddy: %s', $e->msg()));
}
?>
<script type="text/javascript">
	$("#prob-submit-link").colorbox({"escKey" : false});
</script>

