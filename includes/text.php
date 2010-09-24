<?php
/* 
 * $File: text.php
 * $Date: Sat Jul 17 23:08:43 2010 +0800
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


/**
 * Strip HTML Tags
 * @param string $text original html
 * @param array $allowable Allowed Tags
 * @return string HTML Code After Being Stripped
 */
function text_strip_htmltags_allow($text,$allowable= NULL)
{
	if (is_array($allowable))
		$allowable = $allowable;
	else
	{
		$allowable = explode(',',$allowable);
	}
	$allows = "";
	foreach ($allowable as $key => $allowed)
	{
		$allows .= '<'.$allowed.'>';
	}
	return strip_tags($text,$allows);
}


/**
 * Check Markup Language's Tags
 * @param string $text Markup Language's Content
 * @return string Code after checking add automatic-closing.
 */
function text_checktags($text)
{
	$single_tags = array('meta','img','br','link','area');
	preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU',$text,$rs);
	$openedtags = $rs[1];
	foreach ($openedtags as $key => $v)
	{
		$openedtags[$key] = strtolower($v);
	}
	preg_match_all('#</([a-z]+)>#U',$text,$rs);
	$closedtags = $rs[1];
	foreach ($closedtags as $key => $v)
	{
		$closedtags[$key] = strtolower($v);
	}
	if (count($openedtags) == count($closedtags))
	{
		return $text;
	}
	$openedtags = array_reverse($openedtags);
	$len = count($openedtags);
	for ($i=0;$i < $len;$i++)
	{
		if (!in_array($openedtags[$i],$single_tags))
		{
			if (!in_array($openedtags[$i],$closedtags))
			{
				if (isset($openedtags[$i+1]) && $next_tag = $openedtags[$i+1])
				{
					$text = preg_replace('#</'.$next_tag.'#iU','</'.$openedtags[$i].'></'.$next_tag,$text);
				}
				else
				{
					$text .= '</'.$openedtags[$i].'>';
				}
			}
		}
	}
	return $text;
}

