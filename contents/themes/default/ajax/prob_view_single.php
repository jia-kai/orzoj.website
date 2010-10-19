<?php
/*
 * $File: prob_view_single.php
 * $Date: Tue Oct 19 11:31:52 2010 +0800
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

try
{
	$start_page = -1;
	$sort_col = 'id';
	$sort_way = 'ASC';
	$gid = NULL;
	if (isset($_POST['prob-filter'])) // for prob-filter
	{
		if (!isset($_POST['value']))
			throw new Exc_inner("Incomplete post.");
		if ($_POST['prob-filter'] == 'prob-filter-id')
		{
			$id_str = $_POST['value'];
			$len = strlen($id_str);
			if ($len == 0)
				die(__("Give me an id please."));
			$pid = 0;
			for ($i = 0; $i < $len; $i ++)
				if (!($id_str[$i] >= '0' && $id_str[$i] <= '9'))
					die(__('Give me an INT please - -!'));
				else
					$pid = $pid * 10 + $id_str[$i] - '0';
			if ($db->get_number_of_rows('problems', array($DBOP['='], 'id', $pid)) == 0)
				die(__('No such problem whose id is \'%d\'.', $pid));
		}
		else if ($_POST['prob-filter'] == 'prob-filter-code')// prob-filter-code
		{
			$code = $_POST['value'];
			if (strlen($code) == 0)
				die(__("Give me a code please."));
			$pid = prob_get_id_by_code($code);
			if ($pid === NULL)
				die(__('No such problem whose code is \'%s\'', $code));
		}
		else
		{
			throw new Exc_inner(__('Unknown problem filter.'));
		}

	}
	else
		prob_view_single_parse_arg();
	/* ----- navigation button ----*/
	$content = '';

	$content = '<div id="prob-view-single-navigator-top">';

	// Submit
	$content .= '<a id="prob-submit-link" href="' . t_get_link('ajax-prob-submit', "$pid", TRUE, TRUE) . '"><button type="button">'
		. __('Submit') . '</button></a>';

	// Best solutions 
	$content .= '<a id="prob-best-solutions" href="' . t_get_link('ajax-prob-best-solutions', "$pid", TRUE, TRUE) . '"><button type="button">'
		. __('Best solutions') . '</button></a>';

	// Discuss TODO

	// Back to list
	if ($start_page == -1) // from a unknown place..
	{
		$gid = 0; 
		$startpage = 1;
		$sort_col = 'id';
		$sort_way = 'ASC';
	}

	$content .= '<a href="' . prob_view_by_group_get_a_href($gid, $start_page, $sort_col, $sort_way, TRUE) 
		. '" id="prob-view-single-back"'
		. ' onclick="' . prob_view_by_group_get_a_onclick($gid, $start_page, $sort_col, $sort_way, $title_pattern_show, FALSE) . '"><button type="button">';
	$content .= __('Back to list');
	$content .= '</button></a>';

	$content .= '</div>';  // id: prob-view-single-navigator-top

	/* problem description */
	$content .= prob_view($pid);
	// javascript
	$content .= '
		<script type="text/javascript">$("button").button();
$("#prob-submit-link").colorbox();
$("#prob-all-submissions").colorbox();
$("#prob-best-solutions").colorbox();
$("button").button();
	</script>
	';
	echo $content;
}
catch (Exc_runtime $e)
{
	die(__('Hello buddy: %s', $e->msg()));
}
?>

