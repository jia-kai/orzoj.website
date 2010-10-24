<?php
/* 
 * $File: oi.php
 * $Date: Sun Oct 24 12:07:40 2010 +0800
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
require_once $includes_path . 'record.php';

class Ctal_oi extends Ctal
{
	public function get_form_fields()
	{
		return NULL;
	}

	public function add_contest()
	{
		global $db;
		$id = $this->data['id'];
		$db->insert_into('contests_oi', array(
			'cid' => $id,
			'uid' => 0,
			'total_score' => sched_add($this->data['time_end'], __FILE__, '_ctal_oi_judge', array($id))
		));
	}

	public function update_contest()
	{
		global $db, $DBOP;
		$row = $db->select_from('contests_oi', 'total_score',
			array($DBOP['='], 'id', $this->data['id']));
		if (count($row) != 1)
			throw new Exc_inner(__('trying to update contest #%d before insertion'));
		sched_update($row[0]['total_score'], $this->data['time_end']);
	}

	public function view_prob(&$pinfo)
	{
		global $PROB_VIEW_PINFO_STATISTICS;
		if (!$this->allow_viewing())
			throw new Exc_runtime(__('sorry, you are not allowed to view this problem now'));
		if (time() >= $this->data['time_end'] &&  prob_future_contest($pinfo['id']))
			throw new Exc_runtime(__('sorry, this problem belongs to a future contest and you are not allowed
 to view it here'));
		if (is_null($pinfo['io']))
			$pinfo['io'] = array($pinfo['code'] . '.in', $pinfo['code'] . '.out');
		$pinfo['grp'] = array();

		foreach ($PROB_VIEW_PINFO_STATISTICS as $f)
			if (isset($pinfo[$f]))
				unset($pinfo[$f]);
	}

	public function allow_viewing()
	{
		if (isset($this->res_allow_viewing))
			return $this->res_allow_viewing;
		if (time() < $this->data['time_start'])
			return $this->res_allow_viewing = FALSE;
		global $user;
		if (user_check_login())
			$user_grp = $user->get_groups();
		else $user_grp = array(GID_GUEST);
		return $this->res_allow_viewing = prob_check_perm($user_grp, $this->data['perm']);
	}

	public function get_prob_list()
	{
		global $db, $DBOP;
		$ret = array(array(
			__('NO.'), __('TITLE'), __('TIME'), __('MEMORY'), __('INPUT'), __('OUTPUT')
		));
		$rows = $db->select_from('map_prob_ct', 'pid', array(
			$DBOP['='], 'cid', $this->data['id']), array('order' => 'ASC'));
		$contest_end = time() >= $this->data['time_end'];
		for ($i = 0; $i < count($rows); $i ++)
		{
			$id = $rows[$i]['pid'];
			if ($contest_end && prob_future_contest($id))
				array_push($ret, NULL);
			else
			{
				$pinfo = $db->select_from('problems', array('code', 'io', 'desc'),
					array($DBOP['='], 'id', $id));
				if (count($pinfo) != 1)
					throw new Exc_inner(__('no such problem #%d for contest #%d',
						$id, $this->data['id']));
				$pinfo = $pinfo[0];
				if (strlen($pinfo['io']))
					$io = unserialize($pinfo['io']);
				else
					$io = array($pinfo['code'] . '.in', $pinfo['code'] . '.out');
				$desc = unserialize($pinfo['desc']);
				array_push($ret, array($i + 1, prob_get_title_by_id($id), 
					$desc['time'], $desc['memory'], $io[0], $io[1],
					$id));
			}
		}
		return $ret;
	}

	public function user_submit($pinfo, $lid, $src)
	{
		if (time() < $this->data['time_start'])
			throw new Exc_runtime(__('please do not submit before the contest starts'));
		if (time() >= $this->data['time_end'])
			throw new Exc_runtime(__('you can not submit after the contest ends'));
		$ltype = plang_get_type_by_id($lid);
		if (is_null($ltype) || !in_array($ltype, array('c', 'cpp', 'pas')))
			throw new Exc_runtime(__('sorry, your programming language is unavailable in this contest'));

		global $user, $db, $DBOP;
		if (!user_check_login())
			throw new Exc_runtime(__('please login first'));
		if (!prob_check_perm($user->get_groups(), $this->data['perm']))
			throw new Exc_runtime(__('sorry, you are not allowed to submit in this contest'));
		$db->delete_item('records', array(
			$DBOP['&&'], $DBOP['&&'], $DBOP['&&'],
			$DBOP['='], 'uid', $user->id,
			$DBOP['='], 'pid', $pinfo['id'],
			$DBOP['='], 'cid', $this->data['id']));
		$io = $pinfo['io'];
		if (is_null($io))
			$io = array($pinfo['code'] . '.in', $pinfo['code'] . '.out');
		submit_add_record($pinfo['id'], $lid, $src, $io,
			RECORD_STATUS_WAITING_FOR_CONTEST, $this->data['id']);
	}

	public function judge_done($rid)
	{
		global $db, $DBOP;
		$row = $db->select_from('records', array('status', 'pid', 'uid', 'score', 'time'),
			array($DBOP['='], 'id', $rid));
		if (count($row) != 1)
			throw new Exc_inner(__('Ctal_oi::judge_done: no such record #%d', $rid));
		$db->transaction_begin();
		$row = $row[0];
		$where = array(
			$DBP['&&'],
			$DBOP['='], 'cid', $this->data['id'],
			$DBOP['='], 'uid', $row['uid']);
		$val = $db->select_from('contests_oi', array('prob_result', 'total_score', 'total_time'), $where);
		$res_new = array(
				intval($row['status']), intval($row['score']),
				intval($row['time']), intval($rid));
		if (count($val) == 1)
		{
			$val = $val[0];
			$val['total_score'] += $row['score'];
			$val['total_time'] += $row['time'];
			$res = json_decode($val['prob_result']);
			$res[intval($row['pid'])] = $res_new;
			$val['prob_result'] = json_encode($res);
			$db->update_data('contests_oi', $val, $where);
		} else
		{
			$val = array(
				'cid' => $this->data['cid'],
				'uid' => $row['uid'],
				'total_score' => $row['score'],
				'total_time' => $row['time'],
				'prob_result' => json_encode(array(
					intval($row['pid']) => $res_new)));
			$db->insert_into('contests_oi', $val);
		}

		$where = array(
			$DBP['&&'],
			$DBOP['='], 'cid', $this->data['id'],
			$DBOP['='], 'uid', 0);
		$row = $db->select_from('contests_oi', 'total_score', $where);
		if (count($row) != 1)
		{
			$db->transaction_rollback();
			throw new Exc_inner(__('No data row for contest #%d', $this->data['id']));
		}
		$row = $row[0];
		$row['total_score'] --;
		if ($row['total_score'] == 0)
			$db->delete_item('contests_oi', $where);
		else
			$db->update_data('contests_oi', $row, $where);

		$db->transaction_commit();
	}

	public function result_is_ready()
	{
		if (isset($this->res_result_is_ready))
			return $this->res_result_is_ready;
		if (!$this->allow_viewing())
			return $this->res_result_is_ready = FALSE;
		global $db, $DBOP;
		return $this->res_result_is_ready = ($db->get_number_of_rows('contests_oi', array(
			$DBOP['&&'],
			$DBOP['='], 'cid', $this->data['id'].
			$DBOP['='], 'uid', 0)) == 0);
	}

	public function get_user_amount($where = NULL)
	{
		global $db, $DBOP;
		if (!$this->result_is_ready())
			return NULL;
		db_where_add_and($where, array($DBP['='], 'cid', $this->data['id']));
		return $db->get_number_of_rows('contests_oi', $where);
	}

	public function get_rank_list($where = NULL, $offset = NULL, $cnt = NULL)
	{
		global $db, $DBOP;
		if (!$this->result_is_ready())
			return NULL;
		$probs = $db->select_from('map_prob_ct', 'pid', array(
			$DOP['='], 'cid', $this->data['id']), array('order' => 'ASC'));
		foreach ($probs as &$p)
			$p = $p['pid'];
		$col = array(__('Nickname'), __('Real name'), __('Total score'), __('Total time'));
		foreach ($probs as $p)
			array_push($col, prob_get_title_by_id($p));
		$ret = array($col);
		db_where_add_and($where, array($DBOP['='], 'cid', $this->data['id']));
		$rows = $db->select_from('contests_oi', NULL, $where,
			array('total_score' => 'DESC', 'total_time' => 'ASC'),
			$offset, $cnt);

		foreach ($rows as $row)
		{
			$cols = array(user_get_nickname_by_id($row['uid']),
				user_get_realname_by_id($row['uid']), $row['total_score'], $row['total_time']);
			$res = json_decode($col['prob_result']);
			foreach ($probs as $p)
			{
				if (isset($res[$p]))
					$col = array(__('Status: %s<br />Score: %d<br />Time: %.3f[sec]<br />',
						record_status_get_str($res[0]),
						$res[1], $res[2] * 1e-6), $res[3]);
				else
					$col = array(__('Not submitted'), 0);
				array_push($cols, $col);
			}
			array_push($ret, $cols);
		}

		return $ret;
	}

	public function filter_record(&$row)
	{
		$row = NULL;
	}
}

function _ctal_oi_judge($cid)
{
	global $db, $DBOP;
	$where = array($DBOP['='], 'cid', $cid);

	$db->transaction_begin();
	$num = $db->update_data('records', array('status' => RECORD_STATUS_WAITING_TO_BE_FETCHED), $where);
	$db->update_data('contests_oi', array('total_score' => $num), $where);
	$db->transaction_commit();
}

