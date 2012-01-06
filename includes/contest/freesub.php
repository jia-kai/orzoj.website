<?php
/* 
 * $File: freesub.php
 * $Date: Fri Jan 06 22:31:40 2012 +0800
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
require_once $includes_path . 'record.php';

class Ctal_freesub extends Ctal
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
		return $id;
	}

	public function update_contest()
	{
		$this->check_data();
		global $db, $DBOP;
		$where = array($DBOP['='], 'id', $this->data['id']);
		$val = $this->data;
		unset($val['id']);
		$db->update_data('contests', $val, $where);
	}

	public function delete_contest()
	{
		if (time() >= $this->data['time_start'])
			throw new Exc_runtime(__('contests having already started can not be deleted'));
		global $db, $DBOP;
		$where = array($DBOP['='], 'iid', $this->data['id']);
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
		if (isset($this->res_allow_viewing))
			return $this->res_allow_viewing;
		global $user;
		if (user_check_login())
		{
			if ($user->is_grp_member(GID_SUPER_SUBMITTER))
				return $this->res_allow_viewing = TRUE;
			$user_grp = $user->get_groups();
		}
		else $user_grp = array(GID_GUEST);
		if (time() < $this->data['time_start'])
			return $this->res_allow_viewing = FALSE;
		return $this->res_allow_viewing = prob_check_perm($user_grp, $this->data['perm']);
	}

	public function allow_view_result()
	{
		// XXX: resource permission checker
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
		$show_score = user_check_login();
		if ($show_score)
		{
			array_push($ret[0], __('SCORE'));
			$prob_result = $db->select_from('contests_freesub',
				'prob_result', array($DBOP['&&'],
				$DBOP['='], 'cid', $this->data['id'],
				$DBOP['='], 'uid', $user->id));
			if (count($prob_result))
				$prob_result = json_decode($prob_result[0]['prob_result'], TRUE);
			else
				$prob_result = array();
		}
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
				if ($show_score)
				{
					if (isset($prob_result[$pid]))
						array_push($col, $prob_result[$pid][1]);
					else
						array_push($col, '---');
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
		if (!$this->allow_view_prob())
			throw new Exc_runtime(__('sorry, you can not submit problem in contest #%d now', $this->data['id']));
		submit_add_record($pinfo['id'], $lid, $src, $pinfo['io'],
			RECORD_STATUS_WAITING_TO_BE_FETCHED, $this->data['id']);
		return TRUE;
	}

	public function judge_done($rid)
	{
		global $db, $DBOP;
		$row = $db->select_from('records', array('status', 'pid', 'uid', 'score', 'time'),
			array($DBOP['='], 'id', $rid));
		if (count($row) != 1)
			throw new Exc_inner(__('no such record #%d', $rid));

		$row = $row[0];
		$where = array(
			$DBOP['&&'],
			$DBOP['='], 'cid', $this->data['id'],
			$DBOP['='], 'uid', $row['uid']);
		$val = $db->select_from('contests_freesub', array('prob_result', 'total_score', 'total_time'), $where);
		$res_new = array(
				intval($row['status']), intval($row['score']),
				intval($row['time']), intval($rid));
		if (count($val) == 1)
		{
			$val = $val[0];
			$prob_result = json_decode($val['prob_result'], TRUE);
			if (isset($prob_result[$row['pid']]))
			{
				$cpres = $prob_result[$row['pid']];
				$val['total_score'] -= $cpres[1];
				$val['total_time'] -= $cpres[2];
			}
			$val['total_score'] += $row['score'];
			$val['total_time'] += $row['time'];
			$prob_result[$row['pid']] = $res_new;
			$val['prob_result'] = json_encode($prob_result);
			$db->update_data('contests_freesub', $val, $where);
		} else
		{
			$val = array(
				'cid' => $this->data['id'],
				'uid' => $row['uid'],
				'total_score' => $row['score'],
				'total_time' => $row['time'],
				'prob_result' => json_encode(array(
					intval($row['pid']) => $res_new)));
			$db->insert_into('contests_freesub', $val);
		}
	}

	public function result_is_ready()
	{
		return time() >= $this->data['time_end'];
	}

	public function get_user_amount($where = NULL)
	{
		global $db, $DBOP;
		if (!$this->result_is_ready())
			return NULL;
		db_where_add_and($where, array($DBOP['='], 'cid', $this->data['id']));
		return $db->get_number_of_rows('contests_freesub', $where);
	}

	public function get_rank_list($where = NULL, $offset = NULL, $cnt = NULL)
	{
		global $db, $DBOP;
		if (!$this->result_is_ready() || !$this->allow_view_result())
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
		$rows = $db->select_from('contests_freesub', NULL, $where,
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
		{
			$row['src_len'] = 0;
			$row['time'] = 0;
			$row['mem'] = 0;
		}
	}

	public function allow_view_src($uid)
	{
		return time() >= $this->data['time_end'];
	}
}

