<?php
/*
 * $File: mo.php
 * $Date: Wed Sep 29 14:25:41 2010 +0800
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
 * A class to get translation from GNU MO File
 */
class MOReader
{
	/**
	 * name of .mo file.
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
	/**
	 * @access private
	 */
	private $BYTEORDER = 0; // 0=small endian , 1=big endian
	/**
	 * @access private
	 */
	function read_int($fpointer,$offset)
	{
		fseek($fpointer,$offset,SEEK_SET);
		if ($this->BYTEORDER== 0)
			return array_shift(unpack('V',fread($fpointer,4)));
		else
			return array_shift(unpack('N',fread($fpointer,4)));

	}
	/**
	 * @access private
	 */
	function read_char_array($fpointer,$offset,$length)
	{
		fseek($fpointer,$offset,SEEK_SET);
		$ans = NULL;
		for ($i=1;$i<=$length;$i++)
			$ans.=chr(array_shift(unpack('c',fread($fpointer,1))));
		return $ans;
	}
	/**
	 * @access private
	 */
	function get_translation_0($fpointer,$text)
	{
		$total = $this->read_int($fpointer,8);
		$original_offset = $this->read_int($fpointer,12);
		$translation_offset = $this->read_int($fpointer,16);
		for ($i=0;$i < $total;$i++)
		{
			$length = $this->read_int($fpointer,$original_offset + $i * 8);
			$offset = $this->read_int($fpointer,$original_offset + $i * 8 + 4);
			if ($length > 0)
			{
				$orig = $this->read_char_array($fpointer,$offset,$length);
				if ($orig == $text)
				{
					$trans_length = $this->read_int($fpointer,$translation_offset + $i *8);
					$trans_offset = $this->read_int($fpointer,$translation_offset + $i * 8 + 4);
					if ($trans_length > 0)
					{
						$translation = $this->read_char_array($fpointer,$trans_offset,$trans_length);
						return $translation;
					}
				}
			}
		}
		return $text;
	}
	/**
	 * Translate a text<br>Not recommand to use it.Because there 
	 * are <i>__</i> and <i>_gettext</i> in <i>includes/l10n.php</i>
	 * @param string $text text of original language
	 * @return string the translation
	 */
	function translate($text)
	{
		if (isset($this->cache[$text])) return $this->cache[$text];
		else
		{
			if (!(is_readable($this->filename))) return $text;
			$FILE_POINTER = fopen($this->filename,"rb");
			fseek($FILE_POINTER,0,SEEK_SET);
			$magic = $this->read_int($FILE_POINTER,0);

			//$MAGIC_BIG_ENDIAN = "\x95\x04\x12\xde";//2500072158;
			//$MAGIC_SMALL_ENDIAN = "\xde\x12\x04\x95";//3725722773;

			$MAGIC_SMALL_ENDIAN =  -1794895138;
			$MAGIC_BIG_ENDIAN =  -569244523;

			if ($magic == $MAGIC_BIG_ENDIAN)
				$this->BYTEORDER = 1;
			else if ($magic == $MAGIC_SMALL_ENDIAN)
				$this->BYTEORDER = 0;
			else
			{
				fclose($FILE_POINTER);
				return $text;
			}
			$revision = $this->read_int($FILE_POINTER,4);
			if ($revision == 0)
			{
				$translation = $this->get_translation_0($FILE_POINTER,$text);
				if ($this->use_cache) $this->cache[$text] = $translation;
				fclose($FILE_POINTER);
				return $translation;
			}
			else
			{
				fclose($FILE_POINTER);
				return $text;
			}
		}
	}
}


