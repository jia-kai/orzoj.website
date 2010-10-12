<?php
/*
 * $File: po.php
 * $Date: Tue Oct 12 11:03:20 2010 UTC
 * $Author: Qijiang Fan <fqj1994@gmail.com>
 * This file is edited for Orz Online Judge
 */
/**
 * @package orzoj-website
 * @subpackage l10n
 * @license http://apache.org/licenses/LICENSE-2.0.txt Apache License
 */
/*
   Copyright [2010] [Qijiang Fan]

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
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

