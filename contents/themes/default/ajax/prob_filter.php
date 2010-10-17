<?php
/*
 * $File: prob_filter.php
 * $Date: Sat Oct 16 23:21:54 2010 +0800
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
function _make_input($prompt, $post_name)
{
	if (isset($_POST['prob_filter'][$post_name]))
		$default = $_POST['prob_filter'][$post_name];
	else 
		$default = '';
	$id = _tf_get_random_id();
	echo <<<EOF
<tr>
<td>
	<div style="clear: both; float: left;">
	<label for="$id" class="prob-filter">$prompt</label>
	</div>
</td>
<td>
	<div style="float: left">
	<input id="$id" type="text" name="$post_name" value="$default" class="prob-filter" ></input>
	</div>
</td>
</tr>
EOF;
}
?>
<h1 class="prob-navigator-title"><?php echo __("Problem Nav."); ?></h1>

<div id="prob-filter-list">
<form action="<?php t_get_link($cur_page);?>" method="post" id="prob-filter-form">
<table>
<?php
_make_input(__('ID'), 'ID');
_make_input(__('Code'), 'code');
?>
</table>
</form>
</div> <!-- id: prob-filter-list -->


<script type="text/javascript">
</script>
