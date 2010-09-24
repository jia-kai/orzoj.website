<?php
/* 
 * $File: theme.php
 * $Date: Sat Jul 17 23:07:38 2010 +0800
 * $Author: Fan Qijiang <fqj1994@gmail.com>
 */
/**
 * @package orzoj-phpwebsite
 * @license http://gnu.org/licenses GNU GPLv3
 * @version phpweb-1.0.0alpha1
 * @copyright (C) Fan Qijiang
 * @author Fan Qijiang <fqj1994@gmail.com>
 */
/*
	Orz Online Judge is a cross-platform programming online judge.
	Copyright (C) <2010>  (Fan Qijiang) <fqj1994@gmail.com>

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

if (!defined('IN_ORZOJ')) exit;

require_once $root_path.'includes/plugin.php';
require_once $root_path.'includes/common.php';

$the_headers = array();

function t_html_head()
{
	$headers = apply_filters('get_html_head','');
	echo $headers;
}

function t_webname()
{
	global $option_webname;
	if (isset($option_webname))
	{
		echo htmlspecialchars($option_webname);
	}
	else
	{
		echo 'Orz Online Judge Demo Site';
	}
}

function t_footer()
{
	$footer = apply_filters('get_footers','');
	echo $footer;
}

function t_charset()
{
	echo 'UTF-8';
}

function t_siteurl()
{
	echo site_siteurl();
}

function t_email()
{
	echo site_email();
}

function t_get_register_form()
{
	$register_form = array(
		'name' => 'orzoj_user_register',
		'method' => 'POST',
		'action' => rewrite_generate_url('register_submission'),
		'target' => '_self',
		'content' => array(),
		'button' => array()
	);
	$register_form = apply_filters('get_register_form',$register_form);
	return $register_form;
}

