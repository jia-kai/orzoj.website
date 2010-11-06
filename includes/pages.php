<?php
/* 
 * $File: pages.php
 * $Date: Sat Nov 06 18:24:03 2010 +0800
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

require_once $includes_path . 'plugin.php';

class Page 
{
	var $id,$title,$slug,$content,$time;
}

$_pages_list = NULL;
$_pages_detail_gotten_by_id = array();
$_pages_detail_gotten_by_slug = array();
$_pages_detail = array();

/**
 * Get Custom Pages' List
 * @return array pages's list array(array('id' => ID,'title' => TITLE))
 */
function pages_get_list()
{
	global $db,$_pages_list;
	static $done = FALSE;
	if ($done)
	{
		return $_pages_list;
	}
	else
	{
		$result = $db->select_from('pages', array('id','title','slug','time'));
		$ans = array();
		foreach ($result as $v)
		{
			$thispage = new Page();
			$thispage->id = $v['id'];
			$thispage->title = $v['title'];
			$thispage->slug = $v['slug'];
			$thispage->time = $v['time'];
			$ans[$thispage->id] = $thispage;
		}
		$ans = filter_apply('after_pages_get_list',$ans);
		$_pages_list = $ans;
		return $ans;
	}
}

/**
 * Get Page Detail
 * @param int|string $value Value of $bywhat
 * @param string $bywhat Search by what(id and slug are available).
 * @return Page|NULL If the page exists,a CustomPage will be returned.Otherwise,NULL.
 */
function pages_get_detail($value, $bywhat = 'id')
{
	global $db,$DBOP,$_pages_detail_gotten_by_id,$_pages_detail_gotten_by_slug,$_pages_detail;
	$where = array();
	if ($bywhat != 'id' && $bywhat != 'slug') return NULL;
	if ($bywhat == 'id')
	{
		if (isset($_pages_detail_gotten_by_id[$value])) return $_pages_detail[$value];
		$where = array($DBOP['='],'id',$value);
	}
	else if ($bywhat == 'slug')
	{
		if (isset($_pages_detail_gotten_by_slug[$value])) return $_pages_detail[$_pages_detail_gotten_by_slug[$value]];
		$where = array($DBOP['=s'],'slug',$value);
	}
	$result = $db->select_from('pages',NULL,$where);
	if (count($result))
	{
		$result = $result[0];
		$page = new Page();
		$page->id = $result['id'];
		$page->title = $result['title'];
		$page->slug = $result['slug'];
		$page->content = $result['content'];
		$page->time = $result['time'];
		$page = filter_apply('after_pages_get_detail',$page);
		$_pages_detail[$page->id] = $page;
		$_pages_detail_gotten_by_id[$page->id] = $page->id;
		$_pages_detail_gotten_by_slug[$page->slug] = $page->id;
		return $page;
	}
	else
		return NULL;
}
/**
 * Add A Custom Page
 * @param string $title the title of new page
 * @param string $slug the URL friendly title
 * @param string $content the content
 * @return int the ID of new page
 */
function pages_add($title, $slug, $content)
{
	global $db;
	$slug = trim($slug);
	if (strlen($slug) == 0) $slug = urlencode($content);
	$id = $db->insert_into('pages',array('title' => $title,'slug' => $slug,'content' => $content,'time' => time()));
	return $id;
}

