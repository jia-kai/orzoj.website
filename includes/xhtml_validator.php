<?php
/*
 * $File: xhtml_validator.php
 * $Date: Thu Oct 07 21:22:38 2010 +0800
 *
 * downloaded from: http://www.phpclasses.org/package/2411-PHP-Validate-XHTML-and-other-types-of-XML-documents.html
 * Author: marian
 * License: GNU Lesser General Public License (LGPL) (http://www.opensource.org/licenses/lgpl-license.html)
 *
 * edited by jiakai<jia.kai66@gmail.com>
 */

if (!defined('IN_ORZOJ'))
	exit;

define ('_XHTML_VALIDATOR_WRONG_ATTR_FORMAT', __('format for attribute ":attr" in element ":el" is wrong'));
define ('_XHTML_VALIDATOR_ATTR_NOT_ALLOWED', __('attribute ":attr" is not allowed in element ":el"'));
define ('_XHTML_VALIDATOR_MISSING_ATTR', __('attribute ":attr" is missing in element ":el"'));
define ('_XHTML_VALIDATOR_TEXT_IN_ELEMENT', __('no text should occur inside element ":el", or ":el" is not properly closed'));
define ('_XHTML_VALIDATOR_EL_NOT_ALLOWED', __('element ":el" is not allowed'));
define ('_XHTML_VALIDATOR_EL_WRONG_PLACE', __('":el1" can not occur directly in ":el2"'));
define ('_XHTML_VALIDATOR_NO_OPEN_EL_IN_ROOT', __('found element ":el" without corresponding opening element'));
define ('_XHTML_VALIDATOR_NO_OPEN_EL', _XHTML_VALIDATOR_NO_OPEN_EL_IN_ROOT);
define ('_XHTML_VALIDATOR_MUST_CLOSE', __('element ":el" must be closed'));
define ('_XHTML_VALIDATOR_NO_CLOSING_EL', __('closing element not found for ":el"'));
define ('_XHTML_VALIDATOR_CANNOT_BE_EMPTY', __('element ":el" should not be empty'));
define ('_XHTML_VALIDATOR_SHOW_HIDE_BTN', __('show/hide code'));
define ('_XHTML_VALIDATOR_TXT_POSITION', __('position'));

class Xhtml_validator {
	// all allowed tags (without closing tag)
	public static $allowed_tags = array('p','h1','h2','h3','h4','div','ul','ol','li','table','tr','td','th','a','span','div','br','hr','img','em','strong');
	// tag that are empty
	public static $empty_tags = array('br','hr','img');
	// allowed child tags (1 level linit)
	public static $in_tag = array(	'root'=>array('p','ul','ol','table','img','h1','h2','h3','h4','span','div','br','hr','a','em','strong'),
									'table'=>array('tr'),
									'tr'=>array('td','th'),
									'ul'=>array('li'),
									'ol'=>array('li')
							);
	// tags that can contain only another tags, no text (1 level limit)
	public static $tags_only = array('ul','ol','table','tr');
	// allowed attributes
	public static $allowed_attr = array('p'=>array('class','id'),
									'br'=>array(),
									'img'=>array('src','class','width','height','title','alt','id'),
									'h1'=>array('class','id'),
									'h2'=>array('class','id'),
									'h3'=>array('class','id'),
									'h4'=>array('class','id'),
									'hr'=>array(),
									'ul'=>array(),
									'ol'=>array(),
									'li'=>array(),
									'em'=>array(),
									'strong'=>array(),
									'table'=>array('class','id'),
									'tr'=>array('class','id'),
									'td'=>array('class','id','colspan','rowspan'),
									'th'=>array('class','id','colspan','rowspan'),
									'span'=>array('class','id'),
									'div'=>array('class','id','style'),
									'a'=>array('href','name','id','class','target'),
									'script'=>array('type')
								);
	// needed attributes
	public static $needed_attr = array(	'img'=>array('src','width','height','alt')
								);
	// ignored tags array(open,close)
	// "open" - without "<"
	// "close" - without ">"
	public static $ignored_elements = array(
									array('!--','--'),
									array('script','</script'),
									array('?','?'),	// XML first line
									array('!DOCTYPE','')
									);
								
	private $txt;
	private $tree = array();
	private $position = 0;

	public function __construct($txt) {
		$this->txt = str_replace("\r\n","\n",$txt);
	}

	public static function find_end($txt,$name) {
		$pos = strpos($txt,'</'.$name.'>');
		if ($pos === false) {
			throw new Exception();
		}
		if (strpos($txt,'<'.$name) !== false) {
			if (strpos($txt,'<'.$name) < $pos) {
				$pos += 3+strlen($name)+self::find_end(substr($txt,$pos+strlen($name)+3),$name);
			}
		}
		return ($pos);
	}

	private static function error_prepare($txt,$param) {
		foreach ($param as $key => $value) {
			$txt = str_replace(':'.$key,$value,$txt);
		}
		return($txt);
	}
	
	private static function is_ignored($open_tag) {
		foreach (self::$ignored_elements as $ignore_key => $element) {
			if ($open_tag == $element[0]) {
				return ($ignore_key);
			}
		}
		return (false);
	}
	
	private function parse_tag($tag) {
		$space = strpos($tag,' ');
		if ($space === false) {
			$space = strpos($tag,"\n");
		}
		if ($space === false) {
			$out = array(substr($tag,1,-1),array(),self::is_ignored(substr($tag,1,-1)));
			return($out);
		}
		$out[0] = trim(substr($tag,1,$space));
		$ignored = self::is_ignored($out[0]);
		if ($ignored !== false) {
			return (array($out[0],null,$ignored));
		}
		$out[1] = array();
		$tmp = preg_split("/\s+/",trim(substr($tag,$space,-1)));
		$tmp_key = 0;
		foreach ($tmp as $key => $val) {
			if ($val == '/') {
 				break;
			}
			if (substr($val,-1) == '/') {
				$tmp[$key] = substr($val,0,-1);
				$val = $tmp[$key];
			}
			if (preg_match("/^[^\"]+\"?$/",$val)) {
				if (!preg_match("/^[a-z]+=\"[^\"]*$/",$tmp[$tmp_key])) {
					throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_WRONG_ATTR_FORMAT,array('el'=>$out[0],'attr'=>$val)),$this->position+strpos($tag,$val),array($this->position,$this->position+strlen($tag)));
				}
				$tmp[$tmp_key] .= ' '.$val;
				unset($tmp[$key]);
			} else {
				$tmp_key = $key;
			}
		}
		foreach ($tmp as $j => $a) {
			// attributes
			if ($a == '/') {
				break;
			}
			
			if (!preg_match("/^[a-z]+=\".*\"$/",$a)) {
				throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_WRONG_ATTR_FORMAT,array('el'=>$out[0],'attr'=>$a)),$this->position+strpos($tag,$a),array($this->position,$this->position+strlen($tag)));
			}
			$eq_pos = strpos($a,'=');
			if (isset(self::$allowed_attr[$out[0]])) {
				if (!in_array(substr($a,0,$eq_pos),self::$allowed_attr[$out[0]])) {
					throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_ATTR_NOT_ALLOWED,array('el'=>$out[0],'attr'=>substr($a,0,$eq_pos))),$this->position+strpos($tag,$a),array($this->position,$this->position+strlen($tag)));
				}
			}
			$out[1][substr($a,0,$eq_pos)] = substr($a,$eq_pos+2,-1);
			unset($tmp[$j]);
		}
		if (isset(self::$needed_attr[$out[0]])) {
			foreach (self::$needed_attr[$out[0]] as $a) {
				if (!isset($out[1][$a])) {
					throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_MISSING_ATTR,array('el'=>$out[0],'attr'=>$a)),$this->position,array($this->position,$this->position+strlen($tag)));
				}
			}
		}
		$out[2] = false;
		return ($out);
	}
	
	private function trim_txt($txt) {
		$tmp = strpos($txt,'<');
		$this->position += $tmp;
		$txt = substr($txt,$tmp);
		$tmp = strrpos($txt,'>')+1;
		$txt = substr($txt,0,$tmp);
		return ($txt);
	}
	
	public function load() {
		$this->tree = $this->get_branch($this->txt);
	}
	
	private function is_closed($txt) {
		if (substr($txt,-2,1) == '/') {
			return (true);
		} else {
			return (false);
		}
	}
	
	public function get_branch($buf,$opener='root',$range=false) {
		$tree = array();
		while (strpos($buf,'<') !== false) {
			if (in_array($opener,self::$tags_only)) {
				$tmp = trim($buf);
				if (substr($tmp,0,1) != "<" || substr($tmp,-1) != ">") {
					throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_TEXT_IN_ELEMENT,array('el'=>$opener)),$this->position,$range);
				}
			}
			$buf = $this->trim_txt($buf);
			$tag = substr($buf,0,strpos($buf,'>')+1);
			list($name,$attr,$ignored) = $this->parse_tag($tag);
			if ($ignored !== false) {
				$pos = strpos($buf,self::$ignored_elements[$ignored][1].'>');
				if ($pos === false) {
					throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_NO_CLOSING_EL,array('el'=>self::$ignored_elements[$ignored][0])),$this->position);
				}
				$offset = $pos+strlen(self::$ignored_elements[$ignored][1]);
				$this->position += $offset;
				$buf = substr($buf,$offset);
				continue;
			}
			$closing = (substr($name,0,1) == '/') ? true : false;
			if (!$closing && !in_array($name,self::$allowed_tags)) {
				throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_EL_NOT_ALLOWED,array('el'=>$name)),$this->position,$range);
			}
			if (isset(self::$in_tag[$opener]) && !$closing) {
				if (!in_array($name,self::$in_tag[$opener])) {
					throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_EL_WRONG_PLACE,array('el1'=>$name,'el2'=>$opener)),$this->position,$range);
				}
			}
			if ($closing) {
				// closing tag
				if ($opener === 'root') {
					throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_NO_OPEN_EL_IN_ROOT,array('el'=>substr($name,1))),$this->position,$range);
				} else {
					throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_NO_OPEN_EL,array('el'=>substr($name,1))),$this->position,$range);
				}
			} elseif (in_array($name,self::$empty_tags)) {
				// empty tag
				if (!self::is_closed($tag)) {
					throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_MUST_CLOSE,array('el'=>$name)),$this->position,array($this->position,$this->position+strlen($tag)));
				}
				$buf = substr($buf,strlen($tag));
				if ($opener == 'root') {
					$this->position += strlen($tag);
				}
				$tree[] = array('name'=>$name,'attr'=>$attr,'child'=>array(),'pos'=>$this->position);
			} else {
				// open tag
				if (self::is_closed($tag)) {
					if ($opener == 'root') {
						$range = array($this->position,$this->position+strlen($tag));
					}
					throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_CANNOT_BE_EMPTY,array('el'=>$name)),$this->position,$range);
				}
				try {
					$end = $this->find_end(substr($buf,strlen($tag)),$name);
				} catch (Exception $e) {
					if ($opener == 'root') {
						$range = array($this->position,$this->position+strlen($tag));
					}
					throw new Xhtml_validator_exc(self::error_prepare(_XHTML_VALIDATOR_NO_CLOSING_EL,array('el'=>$name)),$this->position,$range);
				}
				$tmp = substr($buf,strlen($tag),$end);
				$this->position += strlen($tag);
				$tmp_pos = $this->position;
				$child = $this->get_branch($tmp,$name,array($this->position,$this->position+$end));
				if (count($child) == 0) {
					$this->position += $end;
				}
				$tree[] = array('name'=>$name,'attr'=>$attr,'child'=>$child,'pos'=>$this->position);
				$buf = substr($buf,$end+strlen($tag)+strlen($name)+3);
				$this->position = $tmp_pos + $end + strlen($name) + 3;
			}
		}
		return($tree);
	}
	
	public function show_tree() {
		$this->show_branch($this->tree);
	}
	
	public function show_branch($branch) {
		echo '<ul>';
		foreach ($branch as $key => $value) {
			echo '<li>'.$value['name'];
			if (count($value['attr']) > 0) {
				echo ' (';
				foreach ($value['attr'] as $k => $v) {
					echo $k.'=&quot;'.$v.'&quot; ';
				}
				echo ')';
			}
			if (count($value['child']) > 0) {
				$this->show_branch($value['child']);
			}
			echo '</li>';
		}
		echo '</ul>';
	}
}

class Xhtml_validator_exc extends Exception {
	public $msg;
	public $pos;
	public $range;
	
	public function __construct($msg,$pos=0,$range=false) {
		$this->msg = $msg;
		$this->pos = $pos;
		$this->range = $range;
	}

	public function msg()
	{
	}
}

class Xhtml_validator_show_err {
	private $txt;
	private $errmsg;
	private $pos;
	private $org_pos;
	private $txt_length;
	private $range;
	private $gray_color = '#666';
	private $highlight_bg = '#337';
	private $highlight = '#fff';
	private $mark = '&rArr;';
	private $mark_color = '#f00';
	
	public function __construct($txt,Xhtml_validator_exc $e) {
		$this->txt = str_replace("\r\n","\n",$txt);
		$this->errmsg = htmlencode($e->msg);
		$this->pos = $e->pos;
		$this->range = $e->range;
		$this->org_pos = $e->pos;
		$this->txt_length = strlen($txt);
	}
	
	private function set_offset($o) {
		$this->pos -= $o;
		$this->range[0] -= $o;
		$this->range[1] -= $o;
	}
	
	public function show($limit=false) {
		if ($this->range === false) {
			$this->range[0] = ($this->pos > 20) ? $this->pos-20 : 1;
			$this->range[1] = (strlen($this->txt) < $this->pos+20) ? strlen($this->txt)-1 : $this->pos+20;
		}
		$buf = $this->txt;
		if ($limit !== false) {
			if ($this->pos + $limit < strlen($buf)) {
				if ($this->pos+$limit < $this->range[1]) {
					$buf = substr($buf,0,$this->range[1]+10);
				} else {
					$buf = substr($buf,0,$this->pos+$limit);
				}
			}
			if ($this->pos - $limit > 0){
				if ($this->pos - $limit > $this->range[0]) {
					$buf = substr($buf,$this->range[0]-1);
					$this->set_offset($this->range[0]-1);
				} else {
					$buf = substr($buf,$this->pos-$limit);
					$this->set_offset($this->pos-$limit);
				}
			}
		}
		$buf = substr($buf,0,$this->pos).'[!pos!]'.substr($buf,$this->pos);
		if ($this->range[0] == 0) {
			$this->range[0]++;
		}
		if ($this->range[0] > $this->pos && $this->range[0]<$this->pos+8) {
			$this->range[0] = ($this->pos == 0) ? 8 : $this->pos;
		}
		$buf = substr($buf,0,$this->range[0]).'[!range_0!]'.substr($buf,$this->range[0],$this->range[1]-$this->range[0]+7).'[!range_1!]'.substr($buf,$this->range[1]+7);
		$buf = htmlencode($buf);
		$buf = str_replace('[!range_0!]','<span style="background-color: '.$this->highlight_bg.';color:'.$this->highlight.';">',$buf);
		$buf = str_replace('[!range_1!]','</span>',$buf);
		$buf = str_replace('[!pos!]','<span style="color: '.$this->mark_color.'; font-weight: bold;">'.$this->mark.'</span>',$buf);
		$out = '<div style="font-weight: bold">'.$this->errmsg.', '._XHTML_VALIDATOR_TXT_POSITION.': '.$this->org_pos.' ('.floor(100 *$this->org_pos / $this->txt_length).'%)
		<a href="#" onclick="javascript:document.getElementById(\'validator_code\').style.display=(document.getElementById(\'validator_code\').style.display == \'none\') ? \'block\' : \'none\';return(false);">'._XHTML_VALIDATOR_SHOW_HIDE_BTN.'</a></div><br />';
		$out .= '<div id="validator_code" style="display:none; color: '.$this->gray_color.'">'.$buf.'</div>';
		return($out);
	}
}

