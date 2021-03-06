<?php
/* 
 * $File: oi.php
 * $Date: Fri Jan 06 21:27:57 2012 +0800
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
		$this->check_data();
		global $db;
		$id = $db->insert_into('contests', $this->data);
		$this->data['id'] = $id;
		$db->insert_into('contests_oi', array(
			'cid' => $id,
			'uid' => 0,
			'total_score' => sched_add($this->data['time_end'], __FILE__, '_ctal_oi_judge', array($id))
		));
		return $id;
	}

	public function update_contest()
	{
		$this->check_data();
		global $db, $DBOP;
		$where = array($DBOP['='], 'cid', $this->data['id']);
		$row = $db->select_from('contests_oi', 'total_score', $where);
		if (count($row) != 1)
			throw new Exc_inner(__('trying to update contest #%d before insertion', $this->data['id']));
		sched_update($row[0]['total_score'], $this->data['time_end']);
		$val = $this->data;
		unset($val['id']);
		$where[1] = 'id';
		$db->update_data('contests', $val, $where);
	}

	public function delete_contest()
	{
		if (time() >= $this->data['time_start'])
			throw new Exc_runtime(__('contests having already started can not be deleted'));
		global $db, $DBOP;
		$where = array($DBOP['='], 'cid', $this->data['id']);
		$row = $db->select_from('contests_oi', 'total_score', $where);
		if (count($row) != 1)
			throw new Exc_inner(__('trying to delete contest #%d before insertion'));
		sched_remove($row[0]['total_score']);
		$db->delete_item('contests_oi', $where);
		$where[1] = 'id';
		$db->delete_item('contests', $where);
	}

	public function view_prob(&$pinfo)
	{
		global $PROB_VIEW_PINFO_STATISTICS;
		if (!$this->allow_view_prob())
			throw new Exc_runtime(__('sorry, you are not allowed to view this problem now'));
		if (time() >= $this->data['time_end'] &&  prob_future_contest($pinfo['id']))
			throw new Exc_runtime(__('sorry, this problem belongs to a future contest and you are not allowed
 to view it here'));
		$pinfo['grp'] = array();

		foreach ($PROB_VIEW_PINFO_STATISTICS as $f)
			if (isset($pinfo[$f]))
				unset($pinfo[$f]);
	}

	public function allow_view_prob()
	{
		if (isset($this->res_allow_view_prob))
			return $this->res_allow_view_prob;
		global $user;
		if (user_check_login())
		{
			if ($user->is_grp_member(GID_SUPER_SUBMITTER))
				return $this->res_allow_view_prob = TRUE;
			$user_grp = $user->get_groups();
		}
		else $user_grp = array(GID_GUEST);
		if (time() < $this->data['time_start'])
			return $this->res_allow_view_prob = FALSE;
		return $this->res_allow_view_prob = prob_check_perm($user_grp, $this->data['perm']);
	}

	public function allow_view_result()
	{
		// XXX
		return TRUE;
	}

	public function get_prob_list()
	{
		if (!$this->allow_view_prob())
			throw new Exc_runtime(__('sorry, you can not get the problem list now'));
		global $db, $DBOP, $user;
		$ret = array(array(
			__('NO.'), __('TITLE'), __('TIME'), __('MEMORY'), __('INPUT'), __('OUTPUT')
		));
		$show_sumitted = user_check_login();
		if ($show_sumitted)
			array_push($ret[0], __('SUBMITTED?'));
		array_push($ret[0], 1);
		$rows = $db->select_from('map_prob_ct', 'pid', array(
			$DBOP['='], 'cid', $this->data['id']), array('order' => 'ASC'));
		$contest_end = time() >= $this->data['time_end'];
		for ($i = 0; $i < count($rows); $i ++)
		{
			$pid = $rows[$i]['pid'];
			if ($contest_end && prob_future_contest($pid))
				array_push($ret, NULL);
			else
			{
				$pinfo = $db->select_from('problems', array('code', 'io', 'desc'),
					array($DBOP['='], 'id', $pid));
				if (count($pinfo) != 1)
					throw new Exc_inner(__('no such problem #%d for contest #%d',
						$pid, $this->data['id']));
				$pinfo = $pinfo[0];
				if (strlen($pinfo['io']))
					$io = unserialize($pinfo['io']);
				else
					$io = array(__('standard input'), __('standard output'));
				$desc = unserialize($pinfo['desc']);
				$col = array($i + 1, prob_get_title_by_id($pid), 
					$desc['time'], $desc['memory'], $io[0], $io[1]);
				if ($show_sumitted)
					array_push($col, is_null($this->get_record_id($pid)) ? ' ' : __('YES'));
				array_push($col, $pid);
				array_push($ret, $col);
			}
		}
		return $ret;
	}

	public function user_submit($pinfo, $lid, $src)
	{
		global $user, $db, $DBOP;
		if (!user_check_login())
			throw new Exc_runtime(__('please login first'));
		if (!$this->allow_view_prob())
			throw new Exc_runtime(__('sorry, you can not submit problem in contest #%d now', $this->data['id']));
		$ltype = plang_get_type_by_id($lid);
		if (is_null($ltype) || !in_array($ltype, array('c', 'cpp', 'pas')))
			throw new Exc_runtime(__('sorry, your programming language is unavailable in this contest'));

		$db->transaction_begin();
		$rid = $this->get_record_id($pinfo['id']);
		if (!is_null($rid))
		{
			$db->delete_item('records', array(
				$DBOP['='], 'id', $rid));
			$db->delete_item('sources', array(
				$DBOP['='], 'rid', $rid));
		}

		if ($user->is_grp_member(GID_SUPER_SUBMITTER))
		{
			$st = RECORD_STATUS_WAITING_TO_BE_FETCHED;
			$cid = 0;
		}
		else
		{
			$st = RECORD_STATUS_WAITING_FOR_CONTEST;
			$cid = $this->data['id'];
		}
		submit_add_record($pinfo['id'], $lid, $src, $pinfo['io'], $st, $cid);
		$db->transaction_commit();

		return FALSE;
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
			$DBOP['&&'],
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
			$res = json_decode($val['prob_result'], TRUE);
			$res[$row['pid']] = $res_new;
			$val['prob_result'] = json_encode($res);
			$db->update_data('contests_oi', $val, $where);
		} else
		{
			$val = array(
				'cid' => $this->data['id'],
				'uid' => $row['uid'],
				'total_score' => $row['score'],
				'total_time' => $row['time'],
				'prob_result' => json_encode(array(
					intval($row['pid']) => $res_new)));
			$db->insert_into('contests_oi', $val);
		}

		$where = array(
			$DBOP['&&'],
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
		if (!$this->allow_view_prob())
			return $this->res_result_is_ready = FALSE;
		global $db, $DBOP;
		return $this->res_result_is_ready = ($db->get_number_of_rows('contests_oi', array(
			$DBOP['&&'],
			$DBOP['='], 'cid', $this->data['id'],
			$DBOP['='], 'uid', 0)) == 0);
	}

	public function get_user_amount($where = NULL)
	{
		global $db, $DBOP;
		if (!$this->result_is_ready())
			return NULL;
		db_where_add_and($where, array($DBOP['='], 'cid', $this->data['id']));
		return $db->get_number_of_rows('contests_oi', $where);
	}

	public function get_rank_list($where = NULL, $offset = NULL, $cnt = NULL)
	{
		global $db, $DBOP;
		if (!$this->result_is_ready())
			return NULL;
		$probs = $db->select_from('map_prob_ct', 'pid', array(
			$DBOP['='], 'cid', $this->data['id']), array('order' => 'ASC'));
		foreach ($probs as $key => $p)
			$probs[$key] = $p['pid'];
		$col = array(__('RANK'), __('USERNAME'), __('REAL NAME'),
			__('TOTAL SCORE'), __('TOTAL TIME [SEC]'));
		foreach ($probs as $p)
			array_push($col, prob_future_contest($p) ? '---' : prob_get_title_by_id($p));
		$ret = array($col);
		db_where_add_and($where, array($DBOP['='], 'cid', $this->data['id']));
		$rows = $db->select_from('contests_oi', NULL, $where,
			array('total_score' => 'DESC', 'total_time' => 'ASC'),
			$offset, $cnt);

		if (!is_null($offset))
			$rank = $offset;
		else
			$rank = 0;
		foreach ($rows as $row)
		{
			$cols = array(
				++ $rank,
				array(user_get_username_by_id($row['uid']), 'uid', $row['uid']),
				user_get_realname_by_id($row['uid']),
				$row['total_score'],
				sprintf('%.3f', $row['total_time'] / 1000000));
			$res = json_decode($row['prob_result'], TRUE);
			foreach ($probs as $p)
			{
				if (prob_future_contest($p))
				{
					array_push($cols, '---');
					continue;
				}
				if (isset($res[$p]))
				{
					$r = &$res[$p];
					$col = array(sprintf('%s<br />%d<br />%.3f[sec]',
						record_status_get_str($r[0]),
						$r[1], $r[2] * 1e-6), 'rid', $r[3]);
					unset($r);
				}
				else
					$col = __('Not submitted');
				array_push($cols, $col);
			}
			array_push($ret, $cols);
		}

		return $ret;
	}

	public function filter_record(&$row)
	{
		global $user;
		if (time() < $this->data['time_end'] && (!user_check_login() || $user->id != $row['uid']))
			$row = NULL;
	}

	public function allow_view_src($uid)
	{
		return time() >= $this->data['time_end'];
	}

	/**
	 * @param int $pid problem id
	 * @return int|NULL record id or NULL
	 */
	private function get_record_id($pid)
	{
		if (!user_check_login())
			return NULL;
		global $db, $DBOP, $user;
		$row = $db->select_from('records', 'id', array(
			$DBOP['&&'], $DBOP['&&'],
			$DBOP['='], 'uid', $user->id,
			$DBOP['='], 'pid', $pid,
			$DBOP['='], 'cid', $this->data['id']));
		if (count($row) != 1)
			return NULL;
		return intval($row[0]['id']);
	}

	/**
	 * check whether $this->data is valid
	 * @exception Exc_runtime
	 */
	private function check_data()
	{
		if ($this->data['time_start'] >= $this->data['time_end'])
			throw new Exc_runtime(__('the contest seems to end before it starts'));
		if ($this->data['time_end'] <= time())
			throw new Exc_runtime(__('the contest seems to have ended in the past'));
	}
}

function _ctal_oi_judge($cid)
{
	global $db, $DBOP;
	$where = array($DBOP['='], 'cid', $cid);

	$db->transaction_begin();
	$num = $db->update_data('records', array('status' => RECORD_STATUS_WAITING_TO_BE_FETCHED), $where);
	if ($num == 0)
		$db->delete_item('contests_oi', $where);
	else
		$db->update_data('contests_oi', array('total_score' => $num), $where);
	$db->transaction_commit();
}

