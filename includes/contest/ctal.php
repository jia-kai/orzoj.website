<?php
/* 
 * $File: ctal.php
 * $Date: Tue Oct 12 21:42:41 2010 +0800
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
if (!defined('IN_ORZOJ'))
	exit;

/**
 * contest abstract layer
 */
abstract class Ctal
{
	/**
	 * @ignore
	 */
	protected $data;

	/**
	 * construction function
	 * @param array|NULL $data row in the database describing the contest or NULL if only get_form_fields will be called
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}

	/**
	 * echo contest-type specific form fields when adding a new contest
	 * @return void
	 */
	abstract protected function get_form_fields();

	/**
	 * this function is called when a new contest of a this type is added
	 * and data in the 'contests' table are inserted
	 * @return void
	 */
	abstract protected function add_contest();

	/**
	 * this function is called when the contest is updated
	 * and data in the 'contests' table are inserted
	 * @return void
	 */
	abstract protected function update_contest();

	/**
	 * called when user tries to view a problem in this contest
	 * @param array $user_grp the ids of groups the user belonging to
	 * @param array $pinfo problem information, containing $PROB_VIEW_PINFO (defined in problem.php)
	 *		may be modified
	 * @return void
	 * @exception Exc_runtime if permission denied
	 */
	abstract protected function prob_view($user_grp, &$pinfo);

	/**
	 * deal with user submissions for problems in this contest
	 * @param array $pinfo problem information, containing $PROB_SUBMIT_PINFO (defined in problem.php)
	 * @param int $lid programming language id
	 * @param string $src source
	 * @return void
	 */
	abstract protected function user_submit($pinfo, $lid, $src);

	/**
	 * get final rank list of the problem
	 * @param array|NULL $users if not NULL, specify the ids of users needed to be ranked
	 * @return array|NULL a 2-dimension array representing a complete table of the final rank list (including table headers)
	 */
	abstract protected function get_rank_list($users);
}

$CONTEST_TYPE2CLASS = array('oi', 'acm');


/**
 * get the ctal class related to the problem
 * @param int $pid problem id
 * @return Ctal|NULL a Ctal instance or NULL if the problem does not belong to a problem
 */
function ctal_get_class($pid)
{
	$now = time();
	$row = $db->select_from('map_prob_ct', 'cid',
		array($DBOP['&&'], $DBOP['&&'], $DBOP['&&'],
		$DBOP['='], 'pid', $pid,
		$DBOP['<='], 'time_start', $now,
		$DBOP['>'], 'time_end', $now));
	if (count($row))
	{
		$row = $db->select_from('contests', NULL,
			array($DBOP['='], 'id', $row[0]['cid']));
		if (count($row) != 1)
			throw new Exc_inner(__('contest not found'));
		$row = $row[0];
		$type = $CONTEST_TYPE2CLASS[$row['type']];
		require_once $includes_path . "contest/$type.php";
		$type = "Ctal_$type";
		return new $type($row);
	} 
	return NULL;
}

