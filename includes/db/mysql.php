<?php
/*
 * $File: mysql.php
 * $Date: Fri Feb 11 18:29:47 2011 +0800
 */
/**
 * @package orzoj-website
 * @subpackage dbal
 * @license http://opensource.org/licenses/apache2.0 Apache License 2.0
 */
/*
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

if (!defined('IN_ORZOJ'))
	exit;

require_once $includes_path . 'db/dbal.php';

/**
 * @ignore
 */
function _mysql_escape_string($string)
{
	return '\'' . mysql_escape_string($string) . '\'';
}

/**
 * @ignore
 */
function _mysql_add_brck($str)
{
	return '(' . $str . ')';
}

/**
 * @ignore
 */
function _mysql_escape_col_name($str)
{
	return '`' . $str . '`';
}

/**
 * @ignore
 */
function _mysql_build_cols($cols)
{
	if (is_string($cols))
		return '`' . $cols . '`';
	$ret = '(';
	foreach ($cols as $col)
		$ret .= '`' . $col . '`,';
	$ret[strlen($ret) - 1] = ')';
	return $ret;
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

$tmp = array('_mysql_escape_col_name', 'intval');
global $DBOP;
$DBOP['='] = new _Mysql_opt(2, '=', $tmp);
$DBOP['!='] = new _Mysql_opt(2, '!=', $tmp);
$DBOP['>'] = new _Mysql_opt(2, '>', $tmp);
$DBOP['>='] = new _Mysql_opt(2, '>=', $tmp);
$DBOP['<'] = new _Mysql_opt(2, '<', $tmp);
$DBOP['<='] = new _Mysql_opt(2, '<=', $tmp);

$tmp = array('_mysql_escape_col_name', '_mysql_escape_string');
$DBOP['=s'] = new _Mysql_opt(2, '=', $tmp);
$DBOP['!=s'] = new _Mysql_opt(2, '!=', $tmp);
$DBOP['like'] = new _Mysql_opt(2, ' LIKE ', $tmp);

$tmp = array('_mysql_add_brck', '_mysql_add_brck');
$DBOP['&&'] = new _Mysql_opt(2, ' && ', $tmp);
$DBOP['||'] = new _Mysql_opt(2, ' || ', $tmp);

$DBOP['!'] = new _Mysql_opt(1, '! ', '_mysql_add_brck');

$DBOP['in'] = new _Mysql_opt(2, ' IN ',
	array('_mysql_build_cols', '_mysql_add_brck'));

unset($tmp);

/**
 * @ignore
 */
function _mysql_build_where_clause($whereclause)
{
	if (!is_array($whereclause) || empty($whereclause))
		return FALSE;
	if (count($whereclause) < 3)
		throw new Exc_inner(__('invalid where clause'));
	$whereclause = array_reverse($whereclause);
	// postfix expression is easier to handle

	$stack = array();
	foreach ($whereclause as $token)
	{
		if (is_object($token) && get_class($token) === '_Mysql_opt')
		{
			if (count($stack) < $token->nopr)
				throw new Exc_inner(__('invalid where clause'));
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
		throw new Exc_inner(__('invalid where clause'));
	return ' WHERE ' . $stack[0];
}


/**
 * MySQL Database Abstract Layer
 */
class Dbal_mysql extends Dbal
{
	private
		/**
		 * mysql link returned by mysql_connect
		 */
		$linker = NULL,

		/**
		 * the number of nested transactions
		 */
		$transaction_cnt = 0,

		/**
		 * whether the transaction has been rollbacked
		 */
		$transaction_rollback_flag = FALSE,

		/**
		 * set engine, character and collate after creating a new table
		 */
		$add_after_create_table = 'ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_bin',

		/**
		 * query count
		 */
		$query_cnt = 0,

		/**
		 * type map of MySQL
		 */
		$typemap = array(
			'INT32' => 'INT',
			'INT64' => 'BIGINT',
			'TEXT' => 'TEXT',
			'TEXT200' => 'VARCHAR(200)',
			'BINARY' => 'BLOB'
		),

		/**
		 * table prefix
		 */
		$table_prefix = '';

	public function set_prefix($prefix)
	{
		$this->table_prefix = $prefix;
	}

	public function connect($host, $port, $user, $passwd, $database)
	{
		$this->close();
		if ($port > 0)
			$host = $host . ':' . $port;
		if ($this->linker = @mysql_connect($host, $user, $passwd, TRUE))
		{
			$this->query("SET NAMES {$this->charset}", $this->linker);
			if (!@mysql_select_db($database, $this->linker))
				throw new Exc_db(__('SQL error: %s', $this->get_err_msg()));
		} else throw new Exc_db(__('failed to conenct to database: %s', $this->get_err_msg()));
	}

	public function close()
	{
		if ($this->linker)
		{
			while ($this->transaction_cnt)
				$this->transaction_rollback();
			@mysql_close($this->linker);
			$this->linker = NULL;
		}
	}

	public function create_table($tablename, $structure)
	{
		$tablename = $this->table_prefix . $tablename;

		$cols = $structure['cols'];
		$sql = 'CREATE TABLE `' . $tablename . '` ( ';
		$current = 1;
		$tocount = count($cols);
		foreach ($cols as $colname => $colstruc)
		{
			$tmp = '`'.$colname.'` ';
			$tmp.= $this->typemap[$colstruc['type']].' ';
			if (isset($colstruc['default']))
			{
				$tmp.= ' DEFAULT ' . _mysql_escape_string($colstruc['default']) . ' ';
			}
			if (isset($colstruc['auto_increment']) && $colstruc['auto_increment'])
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
					else throw new Exc_inner(__('unknown index type: %s', $t));
				} else
					$sql_index .= ' ADD INDEX (';

				foreach ($val['cols'] as $col)
				{
					$sql_index .= '`' . $col . '`';
					$t = $structure['cols'][$col]['type'];
					if ($t == 'TEXT' || $t == 'TEXT200' || $t == 'BINARY')
						$sql_index .= '(' . $structure['index_len'][$col] . ')';
					$sql_index .= ',';
				}
				$sql_index[strlen($sql_index) - 1] = ')';
				array_push($sql, $sql_index);
			}


		if ($this->direct_query)
			$this->queries($sql);
		else
			return $sql;
	}

	public function get_number_of_rows($tablename, $whereclause = NULL)
	{
		$tablename = $this->table_prefix . $tablename;
		$sql = 'SELECT count(*) AS ct FROM `' . $tablename . '` ';
		if ($wherec = _mysql_build_where_clause($whereclause))
		{
			$sql .= $wherec;
		}
		$status = $this->query($sql);
		if ($result = $this->fetch_row($status))
			return intval($result['ct']);
		throw new Exc_db(__('unexpected SQL error'));
	}

	public function delete_item($tablename, $whereclause = NULL)
	{
		$tablename = $this->table_prefix . $tablename;
		$sql = 'DELETE FROM `' . $tablename . '` ';
		if ($where = _mysql_build_where_clause($whereclause))
			$sql .= $where;
		if ($this->direct_query)
		{
			$this->query($sql);
			return @mysql_affected_rows($this->linker);
		}
		else
			return array($sql);
	}

	public function insert_into($tablename, $value)
	{
		$tablename = $this->table_prefix . $tablename;
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
			$valuesql .= _mysql_escape_string($vv);
			if ($cid != $count)
				$valuesql .= ',';
			$cid++;
		}
		$sql .= '(' . $rowssql . ') VALUES(' . $valuesql . ')';
		if ($this->direct_query)
		{
			$this->query($sql);
			return @mysql_insert_id($this->linker);
		}
		else
			return array($sql);
	}

	public function delete_table($tablename)
	{
		$tablename = $this->table_prefix . $tablename;
		$sql = 'DROP TABLE `'.$tablename.'`';
		if ($this->direct_query)
		{
			$this->query($sql);
		}
		else
			return array($sql);
	}

	public function table_exists($tablename)
	{
		$tablename = $this->table_prefix . $tablename;
		try
		{
			$sql = 'SELECT * FROM `'. $tablename . '`';
			if ($this->direct_query)
			{
				$this->query($sql);
				return TRUE;
			}
			else
				return array($sql);
		}
		catch (Exception $e)
		{
			return FALSE;
		}
	}

	public function select_from($tablename, $cols = NULL, $whereclause = NULL,
		$orderby = NULL, $offset = NULL, $amount = NULL, $col_as = NULL, $return_query_str = FALSE)
	{
		$tablename = $this->table_prefix . $tablename;
		$sql = 'SELECT ';
		if (is_array($cols))
		{
			if (is_array($col_as))
			{
				foreach ($cols as $col)
				{
					$sql .= '`' . $col . '`';
					if (isset($col_as[$col]))
						$sql .= ' AS `' . $col_as[$col] . '`';
					$sql .= ',';
				}
			}
			else
			{
				foreach ($cols as $col) 
					$sql .= '`' . $col . '`,';
			}
			$sql[strlen($sql) - 1] = ' ';
		}
		else if ($cols === NULL)
		{
			$sql.= ' * ';
		}
		else
			$sql.=' `' . $cols . '` ';
		$sql.='FROM `'.$tablename.'`';
		if (is_array($whereclause))
			$sql .= _mysql_build_where_clause($whereclause);
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
				$sql .= '`' . $filedname . '` ' . $orderway . ' ';
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
		if ($return_query_str)
			return $sql;
		if ($this->direct_query)
		{
			$rt = $this->query($sql);
			return $this->fetch_all_rows($rt);
		}
		else
			return array($sql);
	}

	public function update_data($tablename, $newvalue, $whereclause = NULL)
	{
		$tablename = $this->table_prefix . $tablename;
		$sql ='UPDATE `'.$tablename.'` SET ';
		foreach ($newvalue as $key => $val)
			$sql .= '`' . $key . '` = ' . _mysql_escape_string($val) . ',';
		$sql[strlen($sql) - 1] = ' ';
		if (is_array($whereclause))
			$sql .= _mysql_build_where_clause($whereclause);
		if ($this->direct_query)
		{
			$rt = $this->query($sql);
			return @mysql_affected_rows($this->linker);
		}
		else
			return array($sql);
	}

	public function get_query_amount()
	{
		return $this->query_cnt;
	}

	public function transaction_begin()
	{
		$this->transaction_cnt ++;
		if ($this->transaction_cnt == 1)
		{
			$queries = array('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE', 'START TRANSACTION');
			if (!$this->direct_query)
				return $queries;
			foreach ($queries as $q)
				$this->query($q);
		}
		if (!$this->direct_query)
			return array();
	}

	public function transaction_commit()
	{
		if (!$this->transaction_cnt)
			throw Exc_inner(__('attempt to commit/rollback before starting transaction'));
		$this->transaction_cnt --;
		if (!$this->transaction_cnt)
		{
			if ($this->transaction_rollback_flag)
			{
				$query = 'ROLLBACK';
				$this->transaction_rollback_flag = FALSE;
			}
			else
				$query = 'COMMIT';
			if ($this->direct_query)
				$this->query($query);
			else
				return array($query);
		}
		if (!$this->direct_query)
			return array();
	}

	public function transaction_rollback()
	{
		$this->transaction_rollback_flag = TRUE;
		return $this->transaction_commit();
	}

	/**
	 * fetch a row in the mysql resource
	 * @return array result
	 */
	private function fetch_row($resource)
	{
		return @mysql_fetch_array($resource, MYSQL_ASSOC);
	}

	/**
	 * fetch all rows in the mysql resource
	 * @return array array of rows
	 */
	private function fetch_all_rows($resource)
	{
		$result = array();
		while ($tmp = $this->fetch_row($resource))
		{
			$result[] = $tmp;
		}
		return $result;
	}

	/**
	 * get latest get_err_msg message
	 * @return string get_err_msg message
	 */
	private function get_err_msg()
	{
		if ($this->linker)
			return '[errno ' . mysql_errno($this->linker) .
			'] ' . mysql_error($this->linker);
		return '[errno ' . mysql_errno() . '] ' . mysql_error();
	}

	/**
	 * apply a mysql query
	 * @param string @query the query to be applied
	 * @return the value returned by mysqlquery
	 * @exception Exc_db
	 */
	private function query($query)
	{
		$this->query_cnt++;
		if ($this->record_query)
			array_push($this->query_log, $query);

		$ret = @mysql_query($query, $this->linker);
		if ($ret === FALSE)
		{
			$msg = __('SQL query error [query: %s]: %s', $query, $this->get_err_msg());
			if ($this->transaction_cnt)
				$this->transaction_rollback();
			throw new Exc_db($msg);
		}
		return $ret;
	}

	/**
	 * apply a series of queries in a transaction
	 * @param array $queries array of queries
	 * @return void
	 */
	private function queries($queries)
	{
		$this->transaction_begin();
		foreach ($queries as $query)
			$this->query($query);
		$this->transaction_commit();
	}
}


