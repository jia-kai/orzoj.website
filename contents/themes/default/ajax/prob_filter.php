<?php
/*
 * $File: prob_filter.php
 * $Date: Fri Oct 29 21:54:18 2010 +0800
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
/**
 * @ignore
 */
function _make_input($prompt, $post_name, $func_sufix, $page, $button_prompt = NULL)
{
	if (is_null($button_prompt))
		$button_prompt = __('Go');
	$prob_view_page = t_get_link('ajax-prob-view-' . $page, 
		NULL,
	   	TRUE, TRUE);
	$id = get_unique_id();
	$form_id = get_unique_id();
	echo <<<EOF
<tr>
	<td>
		<div style="clear: both; float: left;">
		<label for="$id" class="prob-filter">$prompt</label>
		</div>
		</td>
		<td>
		<form id="$form_id" action="$prob_view_page" method="post">
		<div style="float: left">
			<input id="$id" name="$post_name" type="text" class="prob-filter" />
		</div>
		</form>
	</td>
	<td>
		<div style="float: left">
			<input class="prob-filter-input-button" type="submit" onclick="prob_view_by_$func_sufix();" value="$button_prompt"/>
		</div>
	</td>
</tr>
<script type="text/javascript">
function prob_view_by_$func_sufix()
{
	var t = $("#prob-view");
	t.animate({"opacity" : 0.5}, 1);
	$.ajax({
		"url" : "$prob_view_page",
		"type" : "post",
		"data" : ({"prob-filter" : "$post_name",
					"value" : $("#$id").attr("value")
				}),
		"success" : function(data) {
			t.animate({"opacity" : 1}, 1);
			t.html(data);
		}
	});
	return false;
}
$("#$form_id").bind("submit", function(){
	prob_view_by_$func_sufix(); return false;
})
</script>
EOF;
}
?>
<h1 class="prob-navigator-title"><?php echo __("Problem Nav."); ?></h1>

<div id="prob-filter-list">
<table style="max-width: 150px;">
<?php
_make_input(__('ID'), 'prob-filter-id', 'id', 'single');
_make_input(__('Code'), 'prob-filter-code', 'code', 'single');
_make_input(__('Title'), 'prob-filter-title', 'title', 'by-group', __('Find'));
?>
</table>
</div> <!-- id: prob-filter-list -->

<script type="text/javascript">
$(".prob-filter-input-button").button();
</script>

