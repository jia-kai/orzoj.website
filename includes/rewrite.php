<?php
/* 
 * $File: rewrite.php
 * $Date: Sat May 22 23:07:56 2010 +0800
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

require $root_path.'includes/common.php';

function rewrite_generate_url($type,$info)
{
	$rewrite_mode = get_option('rewrite_mode');
	$hosturl = get_option('host_url_http');
	$hosturl_https =  get_option('host_url_https');
	//XXX:HOW TO DETERMIN HTTPS
	if ($_SERVER['HTTP_PROTOCAL'] == 'https' && $hosturl_https) $useurl = $hosturl_https;
	else
		$useurl = $hosturl;
	switch ($type)
	{
	case 'problemview':
		if ($rewrite_mode == 1) return $useurl.'problemview/'.$info['pid'];
		else if ($rewrite_mode == 2) return $useurl.'index.php/problemview/',$info['pid'];
		else return $useurl.'index.php?action=problemview&pid='.$info['pid'];
		break;
	case 'problemlist':
		if ($rewrite_mode == 1) return $useurl.'problemlist/page/'.$info['pageid'];
		else if ($rewrite_mode == 2) return $useurl.'index.php/problemlist/'.$info['pageid'];
		else return $useurl.'index.php?action=problemlist&pageid='.$info['pageid'];
		break;
	case 'contestview':
		if ($rewrite_mode == 1) return $useurl.'contestview/'.$info['cid'];
		else if ($rewrite_mode == 2) return $useurl.'index.php/contestview/'.$info['cid'];
		else return $useurl.'index.php?action=contestview&cid='.$info['cid'];
		break;
	case 'contestlist':
		break;
	case 'announcementview':
		break;
	case 'announcementlist':
		break;
	case 'statuslist':
		break;
	case 'statusview':
		break;
	case 'page':
		break;
	case 'homepage':
		break;
	case '404':
	default:
		break;
	}
}


function rewrite_parse_url($url)
{
	$parsed = parse_url($url);
}

function rewrite_generate_regexp()
{
}

