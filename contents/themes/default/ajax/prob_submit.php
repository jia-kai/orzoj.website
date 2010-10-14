<?php
/*
 * $File: prob_submit.php
 * $Date: Thu Oct 14 21:21:11 2010 +0800
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

if (!is_string($page_arg))
	die("Hello? What are you doing?");

$pid = 0;
if (sscanf($page_arg, "%d", $pid) != 1)
	die("Eh? What do want me to do?");
if (!user_check_login())
	die(__("Please login first."));
require_once $includes_path . 'submit.php';
?>

<form action="#" id="submit-form">
<?php _tf_form_generate_body('submit_src_get_form', $pid); ?>
<div style="text-align: right">
	<button id="submit-button" type="submit" class="in-form"><?php echo __("Good Luck^ ^"); ?></button>
</div>
</form>
