<?php
/* 
 * $File: exception.php
 * $Date: Sun Oct 03 17:10:40 2010 +0800
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
 * orzoj exception
 */
class Exc_orzoj extends Exception
{
	public function msg()
	{
		return __("error at %s:%d:\n%s",
		$this->getFile(), $this->getLine(), $this->getMessage());
	}
}

/**
 * inner exceptions, normally caused by developer's fault
 */
class Exc_inner extends Exc_orzoj
{
	public function msg()
	{
		return __("inner error at %s:%d:\n%s\nTraceback: %s",
			$this->getFile(), $this->getLine(), $this->getMessage(),
			$this->getTraceAsString());
	}
}

/**
 * runtime exceptions (often errors caused by user, not unexpected exceptions)
 */
class Exc_runtime extends Exc_orzoj
{
	public function msg()
	{
		return $this->getMessage();
	}
}


/**
 * database exception
 */
class Exc_db extends Exc_orzoj
{
}

