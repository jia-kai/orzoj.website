<?php
/*
 * $File: user.php
 * $Date: Thu Nov 04 20:46:29 2010 +0800
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

/*
 * page argument:
 *	GET:
 *		[filter]: indicate the submission of search user form
 *		[sort_col]:string
 *		[sort_way]:int 0: ASC; otherwise DESC
 *		[edit]:int the id of user to be edited
 *		[pgnum]:int page number, starting at 0
 *
 *	POST:
 *		those in the form
 *		[pgnum]:int page number, starting at 1
 *
 *	SESSION:
 *		[sort_col, sort_way, pgnum]
 */

