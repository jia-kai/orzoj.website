<?php
/*
 * $File: navigation.php
 * $Date: Sat Oct 30 19:37:08 2010 +0800
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

echo '<body class="frame-navigation">
	<ul>';

foreach ($PAGES as $key => $p)
{
	if (is_null($p[0]))
		continue;
	$perm = TRUE;
	for ($i = 2; $i < count($p); $i ++)
		if (!$user->is_grp_member($p[$i]))
		{
			$perm = FALSE;
			break;
		}
	if ($perm)
		echo "<li><a href='index.php?page=$key' target='frame_content'>$p[0]</a></li>";
}

echo '<li><a href="index.php?page=exit" target="_top">' . __('Exit') . '</a></li>';

echo '</ul></body>';

