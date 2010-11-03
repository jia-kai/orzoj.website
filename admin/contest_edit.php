<?php
/*
 * $File: contest_edit.php
 * $Date: Wed Nov 03 09:22:43 2010 +0800
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
 * This page should only be required_once by contest.php 
 */

/**
 * GET arguments:
 *		[edit]:int the id of contest to be edited, or 0 for adding a new contest
 *		[delete]:
 *			delete this contest (already confirmed), id must be sent via $_POST['pid']
 *			and verification code must be sent via $_POST['delete_verify']
 *			the contest must not have started
 *		[do]: indicate the submission of the form
 * POST arguments:
 *		those in the form
 *		['delete_verify']: verification code for deleting a contest
 *		['edit_verify']: verification code for adding/editing a contest
 *		['ajax_mode']: if set, the first character will be 0 to refresh only page-info div, or
 *			1 to refresh the whole page
 *		[type]:int the contest type for adding a new contest (valid when edit=0)
 * SESSION variables:
 *		delete_verify, edit_verify
 */
session_add_prefix('edit');

if (empty($_GET['edit']) && !isset($_POST['type']))
{
	echo "<form action='$cur_page_link' method='post'>";
	form_get_select(__('Contest type:'), 'type', array_flip(ctal_get_typename_all()));
	echo '<br /><input type="submit" value="' . __('Next step') . '" />';
	echo '</form>';
	return;
}

if (isset($_GET['do']))
{
	try
	{
	}
	catch(Exc_orzoj $e)
	{
	}
}
else
{
	if (empty($_GET['edit']))
	{
		if (!isset($type = $CONTEST_TYPE2CLASS[$_POST['type']]))
			die('no such contest type');
		require_once $includes_path . "contest/$type.php";
		$ct = new Ctal_$type();
	}
	else
	{
		try
		{
			$ct = ctal_get_class_by_cid($_GET['edit']);
		} catch (Exc_inner $e)
		{
			die(htmlencode($e));
		}
	}
}

