<?php
/* 
 * $File: ctal.php
 * $Date: Sun Oct 03 20:43:08 2010 +0800
 */
/**
 * @package orzoj-website
 * @subpackage contest
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

/**
 * contest abstract layer
 */
abstract class Ctal
{
	/**
	 * get contest-type specific form fields when adding a new contest
	 * @return NULL|string NULL if no extra fields, or form fileds to be added in HTML
	 */
	abstract protected function get_form_fields();

	/**
	 * add a new contest of a specific type
	 * @param int $id contest id in the 'contests' table (the row is already inserted) (in transaction)
	 * @return void
	 */
	abstract protected function add_contest($id);

	/**
	 * called when user tries to view a problem in this contest
	 * @param int $cid contest id
	 * @param array $pinfo a row described in the 'problems' table containing information about this problem
	 * @return array|NULL modified problem information or NULL if problem not allowed to be accessed
	 */
	abstract protected function show_prob($cid, $pinfo);

	/**
	 * deal with user submissions for problems in this contest
	 * @param int $cid contest id
	 * @param int $pid problem id
	 * @param int $lid programming language id
	 * @param string $src source
	 * @return void
	 */
	abstract protected function user_submit($cid, $pid, $lid, $src);

	/**
	 * get final rank list of the problem
	 * @param int $cid contest id
	 * @param array|NULL $users if not NULL, specify the ids of users needed to be ranked
	 * @return array|NULL a 2-dimension array representing a complete table of the final rank list (including table headers)
	 */
	abstract protected function get_rank_list($cid, $users);
}

$CONTEST_ID2CLASS = array('oi', 'acm');

