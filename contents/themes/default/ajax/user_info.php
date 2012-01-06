<?php
/*
 * $File: user_info.php
 * $Date: Fri Jan 06 14:16:21 2012 +0800
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

function make_field_name($val)
{
	echo '<div class="user-info-field-name">';
	echo $val;
	echo '</div>';
}

function make_field_value($val)
{
	echo '<div class="user-info-field-value">';
	echo $val;
	echo '</div>';
}

function fd_avatar($val)
{
	make_field_name(__('Avatar:'));
	make_field_value('<img src="' . $val . '" alt="avatar" style="float:left" />');
}

function fd_self_desc($val)
{
	make_field_name(__('Self description:'));
	make_field_value('<div class="user-info-self-desc">' . $val . '</div>');
}

function fd_reg_time($val)
{
	make_field_name(__('Registration time:'));
	make_field_value(time2str($val));
}

function fd_last_login_time($val)
{
	make_field_name(__('Last login time:'));
	make_field_value(time2str($val));
}

$fields = array(
	// <field name> => <display field name>
	// <field name> => array(<function to display this field>)
	'id' => __('User id:'),
	'username' => __('Username:'),
	'nickname' => __('Nickname:'),
	'realname' => __('Real name:'),
	'email' => __('Email:'),
	'avatar' => array('fd_avatar'),
	'self_desc' => array('fd_self_desc'),
	'reg_time' => array('fd_reg_time'),
	'reg_ip' => __('Registration ip:'),
	'last_login_time' => array('fd_last_login_time'),
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

echo '<div class="user-info-page">';

foreach ($fields as $f => $disp)
{
	if (is_array($disp))
	{
		$func = $disp[0];
		$func($tuser->$f);
	}
	else
	{
		make_field_name($disp);
		make_field_value($tuser->$f);
	}
}

$sts_fields = array(
	'cnt_ac' => __('Accepted submissions'),
	'cnt_unac' => __('Unaccepted submissions'),
	'cnt_ce' => __('Compilation-error submissions'),
	'cnt_submit' => __('Total submissions'),
	'cnt_ac_prob' => __('Problems solved'),
	'cnt_ac_prob_blink' => __('Problems solved on first submission'),
	'cnt_ac_prob_blink' => __('Problems solved on first submission'),
	'cnt_submitted_prob' => __('Problems ever submitted'),
	'cnt_ac_submission_sum' => __('Sum of submissions until first AC for each problem')
);

make_field_name(__('Statistics'));
$content = '<table class="colorbox-table">';
$sts = &$tuser->get_statistics();
foreach ($sts_fields as $f => $disp)
	$content .= "<tr><td>$disp</td><td>$sts[$f]</td></tr>";
$content .= '</table>';
make_field_value($content);


echo '</div>';

