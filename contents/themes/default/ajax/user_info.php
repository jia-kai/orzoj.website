<?php
/*
 * $File: user_info.php
 * $Date: Tue Oct 26 10:43:28 2010 +0800
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

require_once $includes_path . 'team.php';

/**
 * page argument: [<user id:int>]
 */

if (!is_string($page_arg))
{
	if (!user_check_login())
		die('please login first');
	$uid = $user->id;
} else
	$uid = intval($page_arg);

function _fd_avatar($val)
{
	echo '<div style="float: left">' . __('Avatar:') . '</div>
		<img src="' . $val . '" alt="avatar" style="float:left" />';
}

function _fd_team($val)
{
	echo '<div style="float: left">';
	echo __('Team:');
	$tmp = new Team();
	if (!$tmp->set_val($val))
	{
		echo '&lt;' . __('unknown') . '&gt;</div>';
		return;
	}
	echo '</div>
	<img id="user-info-team-img" src="' . $tmp->img . '" alt="&lt;team image&gt;" />';
	echo $tmp->name;
}

function _fd_self_desc($val)
{
	echo '<div style="float:left">' . __('Self description:') . '</div>';
	echo '<div id="user-info-self-desc">' . $val . '</div>';
}

function _fd_reg_time($val)
{
	echo __('Registration time:') . ' ' . time2str($val);
}

function _fd_last_login_time($val)
{
	echo __('Last login time:') . ' ' . time2str($val);
}

$fields = array(
	// <field name> => <display field name>
	// <field name> => array(<function to display this field>)
	'id' => __('User id:'),
	'username' => __('Username:'),
	'nickname' => __('Nickname:'),
	'realname' => __('Real name:'),
	'email' => __('Email:'),
	'avatar' => array('_fd_avatar'),
	'tid' => array('_fd_team'),
	'self_desc' => array('_fd_self_desc'),
	'reg_time' => array('_fd_reg_time'),
	'reg_ip' => __('Registration ip:'),
	'last_login_time' => array('_fd_last_login_time'),
	'last_login_ip' => __('Last login IP:')

);

if (!user_check_login() || !($user->is_grp_member(GID_UINFO_VIEWER) ||
	$user->is_grp_member(GID_ADMIN_USER) || $user->id == $uid))
{
	unset($fields['realname']);
	unset($fields['reg_ip']);
	unset($fields['last_login_ip']);
}

try
{
	$tuser = new User($uid);
	$tuser->set_val_detail();
}
catch (Exc_orzoj $e)
{
	die(__('Failed to get user info: %s', htmlencode($e->msg())));
}

foreach ($fields as $f => $disp)
{
	echo '<div style="clear: both; float: left;">';
	if (is_array($disp))
	{
		$func = $disp[0];
		$func($tuser->$f);
	} else echo $disp . ' ' . $tuser->$f;
	echo '</div>';
}

