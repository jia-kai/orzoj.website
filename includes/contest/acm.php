<?php
/* 
 * $File: acm.php
 * $Date: Wed Nov 17 15:26:12 2010 +0800
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

class Ctal_acm extends Ctal
{
	/**
	 * check whether $this->data is valid
	 * @exception Exc_runtime
	 */
	private function check_data()
	{
		global $db, $DBOP;
		if ($this->data['time_start'] >= $this->data['time_end'])
			throw new Exc_runtime(__('the contest seems to end before it starts'));
		if ($this->data['time_end'] <= time())
			throw new Exc_runtime(__('the contest seems to have ended in the past'));
		if (!empty($this->data['id']))
		{
			$row = $db->select_from('contests', 'time_start', array($DBOP['='], 'id', $this->data['id']));
			if (empty($row))
				throw new Exc_inner(__('contest not found in database'));
			$t0 = intval($row[0]['time_start']);
			if ($t0 != $this->data['time_start'] && time() >= $t0)
				throw new Exc_runtime(__(''));
		}
		$opts = array('suspend_time', 'penalty_time', 'force_stdio');
		$tmp = array();
		foreach ($opts as $k)
			$tmp[$k] = get_post($k);
		$this->data['opt'] = json_encode($tmp);
	}

	public function get_form_fields()
	{
		$opts = array(
			'input' => array(
				// <form field> => array(<prompt>, <default value>)
				'suspend_time' => array(
					__('how long to block the refreshing of status list before the contest ends (in minutes)'), 60),
				'penalty_time' => array(
					__('penalty time for each rejected runs (in minutes)'), 20)
			),
			'checkbox' => array(
				'force_stdio' => array(
					__('whether to use stdio regardless of the original problem I/O method configuration'), 1)
			)
		);
		if (isset($this->data['opt']))
		{
			$cur_opt = json_decode($this->data['opt'], TRUE);
			foreach ($opts as $k0 => $v0)
				foreach ($v0 as $k1 => $v1)
					$opts[$k0][$k1][1] = $cur_opt[$k1];
		}

		foreach ($opts['input'] as $k => $v)
			form_get_input($v[0], $k, $v[1]);

		foreach ($opts['checkbox'] as $k => $v)
			form_get_checkbox($v[0], $k, $v[1]);
	}

	public function add_contest()
	{
		$this->check_data();
		global $db;
		return $db->insert_into('contests', $this->data);
	}

	public function update_contest()
	{
		$this->check_data();
		global $db, $DBOP;
		$val = $this->data;
		unset($val['id']);
		$db->update_data('contests', $val, array($DBOP['='], 'id', $this->data['id']));
	}

	public function delete_contest()
	{
		if (time() >= $this->data['time_start'])
			throw new Exc_runtime(__('contests having already started can not be deleted'));
		global $db, $DBOP;
		$db->delete_item('contests', array($DBOP['='], 'id', $this->data['id']));
	}

	public function view_prob(&$pinfo)
	{
		global $PROB_VIEW_PINFO_STATISTICS;
		if (!$this->allow_viewing())
			throw new Exc_runtime(__('sorry, you are not allowed to view this problem now'));
		$opt = json_decode($this->data['opt'], TRUE);
		if ($opt['force_stdio'])
			$pinfo['io'] = NULL;
		$pinfo['grp'] = array();

		foreach ($PROB_VIEW_PINFO_STATISTICS as $f)
			if (isset($pinfo[$f]))
				unset($pinfo[$f]);
	}

	public function allow_viewing()
	{
		if (isset($this->res_allow_viewing))
			return $this->res_allow_viewing;
		global $user;
		if (user_check_login())
		{
			if ($user->is_grp_member(GID_ADMIN_CONTEST) || $user->is_grp_member(GID_SUPER_SUBMITTER))
				return $this->res_allow_viewing = TRUE;
			$user_grp = $user->get_groups();
		}
		else $user_grp = array(GID_GUEST);
		if (time() < $this->data['time_start'])
			return $this->res_allow_viewing = FALSE;
		return $this->res_allow_viewing = prob_check_perm($user_grp, $this->data['perm']);
	}

	public function get_prob_list()
	{
		if (!$this->allow_viewing())
			throw new Exc_runtime(__('sorry, you can not get the problem list now'));
		global $db, $DBOP, $user;
		$ret = array(array(
			__('NO.'), __('TITLE'), __('TIME'), __('MEMORY'), __('TOTAL RUNS'), __('ACCEPTED RUNS')
		));
		$show_solved = user_check_login();
		if ($show_solved)
			array_push($ret[0], __('SOLVED?'));
		array_push($ret[0], 1);
		$rows = $db->select_from('map_prob_ct', 'pid', array(
			$DBOP['='], 'cid', $this->data['id']), array('order' => 'ASC'));
		$time_left = $this->data['time_end'] - time();
		for ($i = 0; $i < count($rows); $i ++)
		{
			$pid = $rows[$i]['pid'];
			if ($time_left < 0 && prob_future_contest($pid))
				array_push($ret, NULL);
			else
			{
				$pinfo = $db->select_from('problems', array('code', 'title', 'desc'),
					array($DBOP['='], 'id', $pid));
				if (empty($pinfo))
					throw new Exc_inner(__('no such problem #%d for contest #%d',
						$pid, $this->data['id']));
				$pinfo = $pinfo[0];
				$desc = unserialize($pinfo['desc']);
				$col = array($i + 1, $pinfo['title'],
					$desc['time'], $desc['memory']);
				if ($time_left >= 0 && $time_left < $this->get_opt('suspend_time'))
					array_push($col, '---', '---');
				else
				{
					$tmp = $this->get_opt('prob_sts');
					if (isset($tmp[$pid]))
					{
						$tmp = $tmp[$pid];
						array_push($col, $tmp[0], $tmp[1]);
					}
					else
						array_push($col, 0, 0);
				}
				if ($show_solved)
				{
					$row = $db->select_from('contests_acm', 'prob_result', array(
						$DBOP['&&'],
						$DBOP['='], 'cid', $this->data['id'],
						$DBOP['='], 'uid', $user->id
					));
					$res = '';
					if (isset($row[0][$pid]) && $row[0][$pid][0] == RECORD_STATUS_ACCEPTED)
						$res = __('YES');
					array_push($col, $res);
				}
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
		if (!$this->allow_viewing())
			throw new Exc_runtime(__('sorry, you can not submit problem in contest #%d now', $this->data['id']));

		$io = $pinfo['io'];
		if (is_null($io) || $this->get_opt('force_stdio'))
			$io = array('', '');

		submit_add_record($pinfo['id'], $lid, $src, $io,
			RECORD_STATUS_WAITING_TO_BE_FETCHED, $this->data['id']);
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
		if (!$this->allow_viewing())
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

}

