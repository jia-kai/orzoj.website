<?php
/* 
 * $File: rewrite.php
 * $Date: Tue Sep 28 23:30:46 2010 +0800
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
 * Parse URL
 * @return array telling what request the user sent.array('type' => CONSTANT::REWRITE** ,'params' => array($key => $value))
 */
function get_method()
{
}

/**
 * Return the URL of Login Page
 * @return string the url
 */
function r_url_Login()
{
}

/**
 * Return the URL of Register Page
 * @return string the url
 */
function r_url_Register()
{
}


/**
 * Return the URL of Viewing Problem
 * @param int $pid ID of the Problem
 * @return string the url
 */
function r_url_ProblemView($pid)
{
}


/**
 * Return the URL of ProblemList
 * @param int $pageid Page No. (starting with 1)
 * @return string the url
 */
function r_url_ProblemList($pageid)
{
}

/**
 * Return the url of Viewing Contest
 * @param int $cid 
 * @return string the url
 */
function r_url_ContestView($cid)
{
}

/**
 * Return the url of ContestList
 * @return string the url
 */
function r_url_ContestList($pageid)
{
}

function r_url_RecordDetail($rid)
{
}

function r_url_RecordList($pageid)
{
}

function r_url_HomePage()
{
}

function r_url_JudgePage()
{
}

function r_url_Pages($id)
{
}

