<?php
/*
 * $File: prob_view_single.php
 * $Date: Tue Nov 02 22:52:07 2010 +0800
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
 * page argument: parsed in ../prob_func.php : prob_view_single_parse_arg()
 * POST:
 *		'prob-filter': string
 *			either 'prob-filter-id' or 'prob-filter-code'
 */
require_once $includes_path . 'problem.php';
require_once $theme_path . 'prob_func.php';
require_once $theme_path . 'post_func.php';

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
	echo '<div id="prob-view-single-navigator-top">';

	// Submit
	echo '<a class="need-colorbox" href="' . t_get_link('ajax-prob-submit', "$pid", TRUE, TRUE) . '"><button type="button">'
		. __('Submit') . '</button></a>';

	// Best solutions 
	echo '<a class="need-colorbox" href="'
		. t_get_link('ajax-prob-best-solutions', "$pid", TRUE, TRUE) . '"><button type="button">'
		. __('Best solutions') . '</button></a>';

	function _make_post_list_button($prompt, $type = NULL)
	{
		global $pid;
		$arg = post_list_pack_arg(1, $type, NULL, NULL, NULL, $pid, 'in-colorbox');
		echo '<a class="need-colorbox" href="'
			. t_get_link('ajax-post-list', $arg, TRUE, TRUE) . '"><button>'
			. $prompt
			. '</button></a>';
	}
	// Discuss 
	_make_post_list_button(__('Discuss'));

	// Solution
	_make_post_list_button(__('Solution'), 'solution');
	// Back to list
	if ($start_page == -1) // from a unknown place..
	{
		$gid = 0; 
		$startpage = 1;
		$sort_col = 'id';
		$sort_way = 'ASC';
		$title_pattern_show = NULL;
	}

	echo '<a href="' . prob_view_by_group_get_a_href($gid, $start_page, $sort_col, $sort_way, $title_pattern_show, TRUE)
		. '" onclick="' . prob_view_by_group_get_a_onclick($gid, $start_page, $sort_col, $sort_way, $title_pattern_show, FALSE) . '"><button type="button">'. __('Back to list') . '</button></a>';

	echo '</div> <!-- id: prob-view-single-navigator-top -->';

	/* problem description */

	try
	{
		echo prob_view($pid);
	}
	catch (Exc_orzoj $e)
	{
		echo '<div style="clear: both;">' .
			__('Failed to view problem: %s', htmlencode($e->msg())) .
			'</div>';
	}

	$pcode = prob_get_code_by_id($pid);
	echo '<div id="prob-view-single-page-addr">';
	echo '<div>' . __('URL of this problem:') . '</div>';
	echo '<button type="button" onclick="bookmark_page()">' . __('Bookmark this problem') . '</button>';
	echo '<span><input id="page-addr" readonly="readonly" type="text" value="http://' . $_SERVER['HTTP_HOST'];
	t_get_link('problem', $pcode);
	echo '"/></span>';
	echo '</div>';

?>

	<script type="text/javascript">
	$(".need-colorbox").colorbox();
	$("button").button();

	function bookmark_page()
	{
		title = "<?echo __('Problem') . ' - ' . prob_get_title_by_id($pid) . ' - ' . $pcode;?>";
		url = $("#page-addr").val();
		if (window.sidebar) 
			window.sidebar.addPanel(title, url, "");
		else if(window.opera && window.print)
		{
			var elem = document.createElement('a');
			elem.setAttribute('href', url);
			elem.setAttribute('title', title);
			elem.setAttribute('rel', 'sidebar');
			elem.click();
		}
		else if(window.external)
			window.external.AddFavorite(url,title);
		else
			alert("<?php echo __('Sorry, bookmarking this page is not supported on your browser.');?>");
	}

	</script>

<?php
}
catch (Exc_runtime $e)
{
	die(__('Error while showing the problem: %s', htmlencode($e->msg())));
}
?>


