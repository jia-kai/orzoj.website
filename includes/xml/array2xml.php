<?php
if (!defined('IN_ORZOJ')) exit;
class ArrayToXML
{
	function ArrayToXML($arr = NULL)
	{
		if (is_array($arr))
		{
/*			$xmlstring = "<?xml version=\"1.0\"?>\n"; */
			$xmlstring= $this->xml_generator($arr['_ELEMENTS']);
			return $xmlstring;
		}
		else
			return true;
	}
	function xml_generator($arr)
	{
		$st = '';
		foreach ($arr as $key => $v)
		{
			$st .= '<'.$v['_NAME'];
			foreach ($v as $name => $vv)
			{
				if ($name != '_NAME' && $name != '_ELEMENTS' && $name != '_DATA')
					$st.= ' '.$name.'="'.$vv.'"';
			}
			$st .= '>';
			if (count($v['_ELEMENTS']) > 0)
				$st .= $this->xml_generator($v['_ELEMENTS']);
			else
				$st .= $v['_DATA'];
			$st .= '</'.$v['_NAME'].'>';
		}
		return $st;
	}
}
