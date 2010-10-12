<?php
/* 
 * $File: oi.php
 * $Date: Tue Oct 12 20:45:11 2010 +0800
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

require_once $includes_path . 'contest/ctal.php';
require_once $includes_path . 'problem.php';
require_once $includes_path . 'sched.php';
require_once $includes_path . 'record.inc.php';

class Ctal_oi extends Ctal
{
	public function get_form_fields()
	{
	}

	public function add_contest()
	{
	}

	public function prob_view($user_grp, &$pinfo)
	{
		if (!prob_check_perm($user_grp, $this->data->perm))
			throw new Exc_runtime('Sorry, you are not allowed to view this problem now');
		if (is_null($pinfo['io']))
			$pinfo['io'] = array($pinfo['code'] . '.in', $pinfo['code'] . '.out');
	}

	public function user_submit($pinfo, $lid, $src)
	{
		global $user, $db, $DBOP;
		$db->delete_item('records', array(
			$DBOP['&&'], $DBOP['&&'], $DBOP['&&'],
			$DBOP['='], 'uid', $user->id,
			$DBOP['='], 'pid', $pinfo['id'],
			$DBOP['='], 'status', RECORD_STATUS_WAITING_FOR_CONTEST));
		submit_add_record($pinfo['id'], $lid, $src,
			RECORD_STATUS_WAITING_FOR_CONTEST);
	}

	public function get_rank_list()
	{
	}
}

