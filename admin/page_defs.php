<?php
/*
 * $File: page_defs.php
 * $Date: Sat Nov 06 20:16:36 2010 +0800
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

$PAGES = array(
	// <page name> => array(<display name|NULL>, <file|NULL>, <required group0>, <required group1>, ...)
	'nav' => array(NULL, 'navigation.php'),
	'default' => array(NULL, 'content_default.php'),
	'prob' => array(__('Problem Administration'), 'problem.php', GID_ADMIN_PROB),
	'prob_grp' => array(__('Problem Group Administration'), 'problem_grp.php', GID_ADMIN_PROB),
	'contest' => array(__('Contest Administration'), 'contest.php', GID_ADMIN_CONTEST),
	'user' => array(__('User Administration'), 'user.php', GID_ADMIN_USER),
	'plugin' => array(__('Plugin Administration'),'plugin.php',GID_ADMIN_PLUGIN)
);

