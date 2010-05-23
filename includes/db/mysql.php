<?php
/*
 * $File: mysql.php
 * $Date: Sat May 22 22:32:06 2010 +0800
 * $Author: Fan Qijiang <fqj1994@gmail.com>
 */
/**
 * @package dbal
 * @license http://gnu.org/licenses/ GNU GPLv3
 * @author Fan Qijiang <fqj1994@gmail.com>
 * @copyright (c) Fan Qijiang
 * @version phpweb-1.0.0alpha1
 */
/*
	Orz Online Judge is a cross-platform programming online judge.
	Copyright (C) <2010>  (Fan Qijiang) <fqj1994@gmail.com>

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

require_once $root_path.'includes/db/dbal.php';
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
	var $add_after_create_table = 'ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci';
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
		'TEXT' => 'TEXT'
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
			@mysql_query("SET NAMES $charset",$this->linker);
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
	function _create_table($tablename,$structure)
	{
		/* {{{ */
		$cols = $structure['cols'];
		$sql .= 'CREATE TABLE `'.$tablename.'` ( ';
		$current =1;
		$tocount = count($cols);
		foreach ($cols as $colname => $colstruc)
		{
			$tmp = $colname.' ';
			$tmp.= $this->typemap[$colstruc['type']].' ';
			if (isset($colstruc['default']))
			{
				$tmp.= 'DEFAULT \''.$this->_escape_string($colstruc['default']).'\' ';
			}
			if ($colstruc['auto_assign'])
			{
				$tmp.=' auto_increment ';
			}
			if ($structure['primary key'] == $colname)
				$tmp.=' PRIMARY KEY ';
			if ($current != $tocount) $tmp.=',';
			$current++;
			$sql.=$tmp;
		}
		$sql.=') ';
		$sql.=$this->add_after_create_table;
		if ($this->_query($sql)) return true;
		else return false;
		/* }}} */
	}
	/**
	 * @access private
	 */
	function _get_number_of_rows($tablename,$whereclause)
	{
		/* {{{ */
		$sql = 'SELECT count(*) AS ct FROM `'.$tablename.'` ';
		if ($wherec = $this->_build_where_clause($whereclause))
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
		/* }}} */
	}
	/**
	 * @access private
	 */
	function _delete_item($tablename,$whereclause)
	{
		$sql.='DELETE FROM `'.$tablename.'` ';
		if ($where = $this->_build_where_clause($whereclause))
			$sql.=$where;
		if ($this->_query($sql))
			return mysql_affected_rows($this->linker);
		else
			return false;
	}
	/**
	 * @access private
	 */
	function _escape_string($string)
	{
		return mysql_escape_string($string);
	}
	/**
	 * @access private
	 */
	function _insert_into($tablename,$value)
	{
		$sql= 'INSERT INTO `'.$tablename.'` (';
		$rowssql = '';
		$valuesql = '';
		$cid = 1;
		$count = count($value);
		foreach ($value as $rowname => $vv)
		{
			$rowssql.=$rowname;
			if ($cid != $count) $rowssql.=',';
			if (is_array($vv)) $vv = $vv['value'];
			$valuesql.='\''.$this->_escape_string($vv).'\'';
			if ($cid != $count) $valuesql.=',';
			$cid++;
		}
		$sql.=$rowssql.') VALUES('.$valuesql.')';
		if ($this->_query($sql)) return mysql_insert_id($this->linker);
		else return false;
	}
	/**
	 * @access private
	 */
	function _delete_table($tablename)
	{
		$sql = 'DROP TABLE `'.$tablename.'`';
		if ($this->_query($sql)) return true;
		else return false;
	}
	/**
	 * @access private
	 */
	function _table_exists($tablename)
	{
		$sql = 'SELECT * FROM `'.$tablename.'`';
		if ($this->_query($sql))
			return true;
		else
			return false;
	}
	/**
	 * @access private
	 */
	function _select_from($tablename,$rows,$whereclause,$orderby,$offset,$amount)
	{
		/* {{{ */
		$sql = 'SELECT ';
		if (is_array($rows))
		{
			$sql.=implode(',',$rows).' ';
		}
		else
		{
			$sql.=' * ';
		}
		$sql.='FROM `'.$tablename.'`';
		if (is_array($whereclause)) $sql.=$this->_build_where_clause($whereclause);
		if (is_array($orderby))
		{
			$sql.=' ORDER BY ';
			$ordercount = count($orderby);
			$cid = 1;
			foreach ($orderby as $rowname => $orderway)
			{
				if ($orderway == 'DESC') $orderway = 'DESC';
				else $orderway = 'ASC';
				$sql.=$rowname.' '.$orderway;
				if ($cid != $ordercount) $sql.=',';
				$cid++;
			}
		}
		if ($amount > 0)
		{
			if ($offset > 0) $sql.=' LIMIT '.$offset;
			else $sql.='LIMIT 0';
			if ($amount > 0) $sql.=','.$amount;
		}
		if ($rt = $this->_query($sql))
		{
			return $this->_fetch_all_rows($rt);
		}
		else
			return false;
		/* }}} */
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
	function _build_where_clause($whereclause)
	{
		if (is_array($whereclause))
			return ' WHERE '.$this->_dfs_make_where($whereclause).' ';
		else
			return false;
	}
	/**
	 * @access private
	 */
	function _dfs_make_where($whereclause)
	{
		/* {{{ */
		$sql = ' ( ';
		if (is_array($whereclause['param1'])) $sql.=$this->_dfs_make_where($whereclause['param1']);
		else $sql.=$this->_escape_string($whereclause['param1']);
		switch ($whereclause['op1'])
		{
		case 'int_eq': //EQUAL
		case 'text_eq':
			$sql.=' = \''.$this->_escape_string($whereclause['param2']).'\'';
			break;
		case 'int_gt': //GREATER THAN
			$sql.=' > \''.$this->_escape_string($whereclause['param2']).'\'';
			break;
		case 'int_lt'://LESS THAN
			$sql.=' < \''.$this->_escape_string($whereclause['param2']).'\'';
			break;
		case 'int_le'://LESS THAN OR EQUAL
			$sql.=' <= \''.$this->_escape_string($whereclause['param2']).'\'';
			break;
		case 'int_ge'://GREATER THAN OR EQUAL
			$sql.=' >= \''.$this->_escape_string($whereclause['param2']).'\'';
			break;
		case 'logincal_and'://LOGICAL AND
			if (is_array($whereclause['param2']))
				$sql.=' AND '.$this->_dfs_make_where($whereclause['param2']);
			else
				$sql.=' AND '.$whereclause['param2'];
			break;
		}
		$sql.=' ) ';
		return $sql;
		/* }}} */
	}
	/**
	 * @access private
	 */
	function _update_data($tablename,$newvalue,$whereclause = NULL)
	{
		echo "<Br>\n";
		$sql.='UPDATE `'.$tablename.'` SET ';
		foreach ($newvalue as $key => $value)
		{
			if (is_array($value)) $value = $value['value'];
			$newvalue[$key] = $key . ' = \''.mysql_escape_string($value).'\'';
		}
		$sql.= implode(',',$newvalue);
		$sql.=' ';
		if (is_array($whereclause)) $sql.=$this->_build_where_clause($whereclause);
		if ($rt = $this->_query($sql))
		{
			return mysql_affected_rows($this->linker);
		}
		else
			return false;
	}
	/**
	 * @access private
	 */
	function _get_query_amount()
	{
		return $this->querycount;
	}
}
/*
 * vim:foldmethod=marker
 */
