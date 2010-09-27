<?php
/*
 * $File: mysql.php
 * $Date: Mon Sep 27 20:37:57 2010 +0800
 */
/**
 * @package orzoj-website
 * @subpackage dbal
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

require_once $includes_path . 'db/dbal.php';
/**
 * @ignore
 */
function _mysql_escape_string($string)
{
	return mysql_escape_string($string);
}

/**
 * @ignore
 */
function _mysql_escape_add_brck($str)
{
	return '(' . $str . ')';
}

/**
 * @ignore
 */
class _Mysql_opt
{
	var $nopr, // number of operators
		$opt, // mysql operator
		$esc_func; // escape function
	function __construct($nopr, $opt, $esc_func)
	{
		$this->nopr = $nopr;
		$this->opt = $opt;
		$this->esc_func = $esc_func;
	}

}

$tmp = array(NULL, 'intval');
$DBOP['='] = new _Mysql_opt(2, '=', $tmp);
$DBOP['!='] = new _Mysql_opt(2, '!=', $tmp);
$DBOP['>'] = new _Mysql_opt(2, '>', $tmp);
$DBOP['>='] = new _Mysql_opt(2, '>=', $tmp);
$DBOP['<'] = new _Mysql_opt(2, '<', $tmp);
$DBOP['<='] = new _Mysql_opt(2, '<=', $tmp);

$tmp = array(NULL, '_mysql_escape_string');
$DBOP['=s'] = new _Mysql_opt(2, '=', $tmp);
$DBOP['!=s'] = new _Mysql_opt(2, '!=', $tmp);

$tmp = array('_mysql_escape_add_brck', '_mysql_escape_add_brck');
$DBOP['&&'] = new _Mysql_opt(2, ' && ', $tmp);
$DBOP['||'] = new _Mysql_opt(2, ' || ', $tmp);

$DBOP['!'] = new _Mysql_opt(1, '! ', '_mysql_escape_add_brck');

unset($tmp);
/**
 * @ignore
 */
function _mysql_build_where_clause($whereclause)
{
	if (!is_array($whereclause))
		return false;
	$whereclause = array_reverse($whereclause);
	// postfix expression is easier to handle

	$stack = array();
	foreach ($whereclause as $token)
	{
		if (get_class($token) === '_Mysql_opt')
		{
			if (count($stack) < $token->nopr)
				die(__FILE__ . ': invalid where clause');
			if ($token->nopr == 1)
			{
				$opr = array_pop($stack);
				$func = $token->esc_func;
				if ($func)
					$opr = $func($opr);
				array_push($stack, $token->opt . $opr);
			} else // at present, $token->opr must be 2
				{
					$func = $token->esc_func[0];
					$opr0 = array_pop($stack);
					if ($func)
						$opr0 = $func($opr0);
					$func = $token->esc_func[1];
					$opr1 = array_pop($stack);
					if ($func)
						$opr1 = $func($opr1);
					array_push($stack, $opr0 . $token->opt . $opr1);
				}
		} else array_push($stack, $token);
	}
	if (count($stack) != 1)
		die(__FILE__ . ': invalid where clause');
	return ' WHERE ' . $stack[0];
}


/**
 * MySQL Database Abstract Layer
 */
class dbal_mysql extends dbal
{
	/**
	 * @access private
	 */
	function __construct()
	{
		$this->dblayer = 'mysql';
	}
	/**
	 * charset of table
	 */
	var $charset = 'utf8';
	/**
	 * decleare engine,character,collate after creating
	 */
	var $add_after_create_table = 'ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_bin';
	/**
	 * Query Counts
	 */
	var $querycount = 0;
	/**
	 * Type map of MySQL
	 */
	var $typemap = array(
		'INT32' => 'INT',
		'INT64' => 'BIGINT',
		'TEXT' => 'TEXT',
		'TEXT200' => 'VARCHAR(200)'
	);
	/**
	 * @access private
	 */
	function _connect($host,$port,$user,$passwd,$database)
	{
		if ($this->linker) @mysql_close($this->linker);
		if ($port > 0) $host = $host.':'.$port;
		if (@$this->linker = mysql_connect($host,$user,$passwd))
		{
			@mysql_query("SET NAMES {$this->charset}",$this->linker);
			$this->querycount++;
			if (@mysql_select_db($database,$this->linker))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		return FALSE;
	}
	/**
	 * @access private
	 */
	function _close()
	{
		if ($this->linker) @mysql_close($this->linker);
	}
	/**
	 * @access private
	 */
	function _error()
	{
		if ($this->linker) return @mysql_error($this->linker);
		else return @mysql_error();
	}
	/**
	 * @access private
	 */
	function _query($statement)
	{
		$this->querycount++;
		return @mysql_query($statement,$this->linker);
	}
	/**
	 * @access private
	 */
	function _create_table($tablename, $structure)
	{
		$cols = $structure['cols'];
		$sql = 'CREATE TABLE `'.$tablename.'` ( ';
		$current =1;
		$tocount = count($cols);
		foreach ($cols as $colname => $colstruc)
		{
			$tmp = '`'.$colname.'` ';
			$tmp.= $this->typemap[$colstruc['type']].' ';
			if (isset($colstruc['default']))
			{
				$tmp.= 'DEFAULT \'' . _mysql_escape_string($colstruc['default']) . '\' ';
			}
			if (isset($colstruc['auto_assign']) && $colstruc['auto_assign'])
			{
				$tmp.=' AUTO_INCREMENT ';
			}
			if (isset($structure['primary_key']) && $structure['primary_key'] == $colname)
				$tmp.=' PRIMARY KEY ';
			if ($current != $tocount)
				$tmp.=',';
			$current++;
			$sql.=$tmp;
		}
		$sql.=') ';
		$sql.=$this->add_after_create_table;

		$sql = array($sql);

		if (isset($structure['index']))
			foreach($structure['index'] as $val)
			{
				$sql_index = 'ALTER TABLE `' . $tablename .'`';
				if (isset($val['type']))
				{
					$t = $val['type'];
					if ($t == 'UNIQUE')
						$sql_index .= ' ADD UNIQUE INDEX (';
					else die(__FILE__ . ': unknown index type: ' . $t);
				} else
					$sql_index .= ' ADD INDEX (';

				foreach ($val['cols'] as $col)
				{
					$sql_index .= '`' . $col . '`';
					$t = $structure['cols'][$col]['type'];
					if ($t == 'TEXT' || $t == 'TEXT200')
						$sql_index .= '(' . $structure['index_len'][$col] . ')';
					$sql_index .= ',';
				}
				$sql_index[strlen($sql_index) - 1] = ')';
				array_push($sql, $sql_index);
			}


		if ($this->direct_query)
		{
			if ($this->_queries($sql)) return true;
			else return false;
		}
		else
			return $sql;
	}
	/**
	 * @access private
	 */
	function _get_number_of_rows($tablename,$whereclause)
	{
		$sql = 'SELECT count(*) AS ct FROM `'.$tablename.'` ';
		if ($wherec = _mysql_build_where_clause($whereclause))
		{
			$sql .= $wherec;
		}
		$status = $this->_query($sql);
		if ($result = $this->_fetch_row($status))
		{
			return $result['ct'];
		}
		else
			return false;
	}
	/**
	 * @access private
	 */
	function _delete_item($tablename,$whereclause)
	{
		$sql = 'DELETE FROM `' . $tablename . '` ';
		if ($where = _mysql_build_where_clause($whereclause))
			$sql .= $where;
		if ($this->direct_query)
		{
			if ($this->_query($sql))
				return mysql_affected_rows($this->linker);
			else
				return false;
		}
		else
			return array($sql);
	}
	/**
	 * @access private
	 */
	function _insert_into($tablename,$value)
	{
		$sql = 'INSERT INTO `' . $tablename.'`';
		$rowssql = '';
		$valuesql = '';
		$cid = 1;
		$count = count($value);
		foreach ($value as $filedname => $vv)
		{
			$rowssql .= '`' . $filedname . '`';
			if ($cid != $count)
				$rowssql .= ',';
			if (is_array($vv))
				$vv = $vv['value'];
			$valuesql .= '\'' . _mysql_escape_string($vv) . '\'';
			if ($cid != $count)
				$valuesql .= ',';
			$cid++;
		}
		$sql .= '(' . $rowssql . ') VALUES(' . $valuesql . ')';
		if ($this->direct_query)
		{
			if ($this->_query($sql))
				return mysql_insert_id($this->linker);
			else return false;
		}
		else
			return array($sql);
	}
	/**
	 * @access private
	 */
	function _delete_table($tablename)
	{
		$sql = 'DROP TABLE `'.$tablename.'`';
		if ($this->direct_query)
		{
			if ($this->_query($sql)) return true;
			else return false;
		}
		else
			return array($sql);
	}
	/**
	 * @access private
	 */
	function _table_exists($tablename)
	{
		$sql = 'SELECT * FROM `'.$tablename.'`';
		if ($this->direct_query)
		{
			if ($this->_query($sql))
				return true;
			else
				return false;
		}
		else
			return array($sql);
	}
	/**
	 * @access private
	 */
	function _select_from($tablename,$rows,$whereclause,$orderby,$offset,$amount)
	{
		$sql = 'SELECT ';
		if (is_array($rows))
		{
			foreach ($rows as $row) 
				$sql .= '`' . $row . '`,';
			$sql[strlen($sql) - 1] = ' ';
		}
		else if ($rows == NULL)
		{
			$sql.= ' * ';
		}
		else
			$sql.=' `'.$rows.'` ';
		$sql.='FROM `'.$tablename.'`';
		if (is_array($whereclause)) $sql.=_mysql_build_where_clause($whereclause);
		if (is_array($orderby))
		{
			$sql.=' ORDER BY ';
			$ordercount = count($orderby);
			$cid = 1;
			foreach ($orderby as $filedname => $orderway)
			{
				if ($orderway == 'DESC')
					$orderway = 'DESC';
				else
					$orderway = 'ASC';
				$sql .= '`' . $filedname . '` ' . $orderway;
				if ($cid != $ordercount)
					$sql .= ',';
				$cid++;
			}
		}
		if ($amount > 0)
		{
			if ($offset > 0) $sql.=' LIMIT '.$offset;
			else $sql.='LIMIT 0';
			if ($amount > 0) $sql.=','.$amount;
		}
		if ($this->direct_query)
		{
			if ($rt = $this->_query($sql))
			{
				return $this->_fetch_all_rows($rt);
			}
			else
				return false;
		}
		else
			return array($sql);
	}
	/**
	 * @access private
	 */
	function _fetch_row($resource)
	{
		return @mysql_fetch_array($resource);
	}
	/**
	 * @access private
	 */
	function _fetch_all_rows($resource)
	{
		$result = array();
		while ($tmp = $this->_fetch_row($resource))
		{
			$result[] = $tmp;
		}
		return $result;
	}
	/**
	 * @access private
	 */
	function _update_data($tablename,$newvalue,$whereclause = NULL)
	{
		$sql ='UPDATE `'.$tablename.'` SET ';
		foreach ($newvalue as $key => $value)
		{
			if (is_array($value)) $value = $value['value'];
			$newvalue[$key] = '`' . $key . '` = \'' . mysql_escape_string($value) . '\'';
		}
		$sql .= implode(',', $newvalue);
		$sql .= ' ';
		if (is_array($whereclause))
			$sql .= _mysql_build_where_clause($whereclause);
		if ($this->direct_query)
		{
			if ($rt = $this->_query($sql))
			{
				return mysql_affected_rows($this->linker);
			}
			else
				return false;
		}
		else
			return array($sql);
	}
	/**
	 * @access private
	 */
	function _get_query_amount()
	{
		return $this->querycount;
	}
	/**
	 * @access private
	 */
	function _transaction_begin()
	{
		if ($this->direct_query)
		{
			return $this->_query("BEGIN;");
		}
		else
			return array("BEGIN;");
	}
	/**
	 * @access private
	 */
	function _transaction_commit()
	{
		if ($this->direct_query)
		{
			return $this->_query("COMMIT;");
		}
		else
			return array("COMMIT;");
	}
	/**
	 * @access private
	 */
	function _transaction_rollback()
	{
		if ($this->direct_query)
		{
			return $this->_query("ROLLBACK;");
		}
		else
			return array("ROLLBACK;");
	}
	/**
	 * @access private
	 */
	function _queries($queries)
	{
		$this->transaction_begin();
		foreach ($queries as $query)
		{
			if ($this->_query($query))
				continue;
			else
			{
				$this->transaction_rollback();
				return false;
			}
		}
		$this->transaction_commit();
		return true;
	}
}

