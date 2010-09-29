<?php
/*
 * $File: po.php
 * $Date: Wed Sep 29 14:25:48 2010 +0800
 */
/**
 * @package orzoj-website
 * @subpackage l10n
 * @license http://gnu.org/licenses/ GNU GPLv3
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
if (!defined('IN_ORZOJ')) exit;

/**
 * A class to translate text by using .po file
 */
class POReader
{
	/**
	 * name of .po file
	 */
	var $filename;
	/**
	 * use cache or not
	 */
	var $use_cache = true;
	/**
	 * @access private
	 */
	private $cache = array();
	/* {{{ decode */
	/**
	 * @access private
	 */
	function decode($con)
	{
		return str_replace(array('\\"',"\\n",'\\t','\\r'),array('"',"\n","\t","\r"),$con);
	}
	/* }}} */
	/**
	 * translate a text.Not recommand,because of the function __ and _gettext.
	 * @param string $text original text
	 * @return string translation
	 */
	function translate($text)
	{
		if (isset($this->cache[$text])) return $this->cache[$text];
		if (!is_readable($this->filename)) return $text;
		$pattern=//'/msgctxt\s+"(.*?(?<!\\\\))"'
			 '/msgid\s+"(.*?(?<!\\\\))"'
			 . '\s+msgstr\s+"(.*?(?<!\\\\))"/'; 
		//FIXME : Multiple line msgid and msgstr bugs
		$content = file_get_contents($this->filename);
		$n = preg_match_all($pattern,$content,$matches);
		for ($i=0;$i<$n;++$i)
		{
			if ($this->decode($matches[1][$i]) == $text)
			{
				$translation=$this->decode($matches[2][$i]);
				if (!$this->use_cache) return $translation;
			}
			$this->cache[$this->decode($matches[1][$i])] = $this->decode($matches[2][$i]);
		}
		if (strlen($translation))return $translation;
		else return $text;
	}
}

/*
 * vim:foldmethod=marker
 */

