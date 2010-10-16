<?php
/*
 * $File: prob_best_solutions.php
 * $Date: Sat Oct 16 18:51:27 2010 +0800
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
$pid = 0;
if (sscanf($page_arg, "%d", $pid) != 1)
	die(__("Sir, what can I do for you?"));
if (!user_check_login())
	die(__("Please login first."));
?>

<script type="text/javascript">
$.ajax({
	"type" : "post",
	"cache" : false,
	"url" : "<?php t_get_link('ajax-status-list'); ?>",
	"data" : ({"prob_best_solutions" : <?php echo $pid; ?>}),
	"success" : function(data){$.colorbox({"html" : data});}
});
</script>
