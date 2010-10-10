<?php
/*
 * $File: form_checker.php
 * $Date: Sun Oct 10 19:23:08 2010 +0800
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

if (!isset($_POST['checker']) || !isset($_POST['val']))
	die('what are you doing?');

$checker = $_POST['checker'];
if (!isset($_tf_checker[$checker]))
	die('Oh, no!');

$checker = $_tf_checker[$checker];

echo $checker($_POST['val']);
