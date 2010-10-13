<?php
/*
 * $File: problem.php
 * $Date: Wed Oct 13 21:40:46 2010 +0800
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

<div id="prob-grp-tree"></div>
<script type="text/javascript">
$(function(){
	$("#prob-grp-tree").jstree({
		"plugins" : [ "themes", "json_data", "cookies"],
		"themes" : {
			"theme" : "default",
			"dots" : false,
			"icons" : false
		},
		"json_data" : {
			"ajax" : {
				"url" : "<?php t_get_link('ajax-prob-group-tree'); ?>"
				,
				"data" : function (node) {
					return {
						"prob_grp_id" : node.attr ? node.attr("id") : 0
					};
				}
			}
		}
	})
});
</script>
</div>
