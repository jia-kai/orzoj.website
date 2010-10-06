<?php
/*
 * $File: dbal.php
 * $Date: Wed Oct 06 09:58:35 2010 +0800
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
if (!defined('IN_ORZOJ'))
	exit;

$DBOP = array();

/**
 * Database abstract laywer base
 * If dbal.direct_query is set to true,
 * functions generating query statements will return the statements(array) instead of querying result.
 * @exception Exc_db
 */
abstract class Dbal
{
	/**
	 * TRUE: do sql query
	 * FALSE: return an array of query(-ies)
	 */
	public $direct_query = TRUE;

	/**
	 * charset of table
	 */
	protected $charset = 'utf8';

	/**
	 * set the prefix of table names
	 * @param string $prefix the prefix to be added before table names
	 * @return void
	 */
	abstract protected function set_prefix($prefix);

	/**
	 * connect to the database.
	 * @param string $host host address to connect
	 * @param int $port port number of host
	 * @param string $user username
	 * @param string $passwd password
	 * @param string $database database name
	 * @return void
	 */
	abstract protected function connect($host, $port, $user, $passwd, $database);

	/**
	 * close the database connection
	 * @return void
	 */
	abstract protected function close();

	/**
	 * create a table in the database
	 * @param string $tablename name of the table
	 * @param mixed $structure structure of the table <pre>
	 *    array(
	 *    'cols' =&gt; array(
	 *     &lt;col name&gt; =&gt; array('type' =&gt; &lt;coltype&gt;:INT32|INT64|TEXT|TEXT200, 
	 *						'default' =&gt; &lt;default value&gt; , 
	 *						'auto_assign' =&gt; true|false)
	 *    ),
	 *	 ['primary_key' =&gt; $keycolname,]
	 *	 ['index' =&gt; array(
	 *		 array(
	 *			 ['type' =&gt; 'UNIQUE',]
	 *			 'cols' =&gt; array(&lt;col0 name&gt;, &lt;col1 name&gt;, ...)
	 *		 )
	 *	 ),
	 *	 ['index_len' =&gt; array(
	 *		 &lt;col name&gt; =&gt; &lt;length of index on this col&gt;
	 *	 )]]
	 *   for columns of type TEXT, length must be specified if you want to create an
	 *	 index on it </pre>
	 * @return void
	 */
	abstract protected function create_table($tablename, $structure);

	/**
	 * delete a table
	 * @param string $tablename name of a table
	 * @return void
	 */
	abstract protected function delete_table($tablename);

	/**
	 * test whether table exists
	 * @param string $tablename name of a table
	 * @return bool
	 */
	abstract protected function table_exists($tablename);

	/**
	 * get the number of rows in a table
	 * @param string $tablename name of a table
	 * @param array whereclause the where clause array
	 * @return int number of rows
	 * @see select_from
	 */
	abstract protected function get_number_of_rows($tablename, $whereclause = NULL);

	/**
	 * delete items from specified table
	 * @param string $tablename name of a table
	 * @param array whereclause the where clause array
	 * @return int numer of affected rows or TRUE
	 * @see select_from
	 */
	abstract protected function delete_item($tablename, $whereclause = NULL);

	/**
	 * insert data into a table
	 * @param string $tablename name of a table
	 * @param array $value what to insert.
	 * @return int insert id or 0 if id unavailable
	 * @see update_data
	 */
	abstract protected function insert_into($tablename, $value);

	/**
	 * select data from a table
	 * @param string $tablename name of the table
	 * @param mixed $cols array of column names or string of column name or NULL(means all columns)
	 * @param array $whereclause array of tokens, in the form of prefix expression <pre>
	 *		each element in the array  is either an operator or an operand
	 *		valid operators:
	 *		   '=,'!='		--	equality or inequality test for integer
	 *							the seconde operand will be converted to int
	 *							operands:
	 *								&lt;col name:string&gt;, &lt;value:string or int&gt;
	 *		   '=s','!=s'	--	equality or inequality test for string
	 *							operands:
	 *								&lt;col name:string&gt;, &lt;value:string&gt;
	 *		   'like'		--	pattern matching, an underscore (_) in pattern stands for (matches)
	 *							any single character; a percent sign (%) matches any
	 *							string of zero or more characters. 
	 *		   '&gt;', '&gt;=', '&lt;', '&lt;='
	 *						--	greater than, greater than or equal to, less than, less than or equal to respectivelty
	 *							the second operand will be converted to int
	 *							operands:
	 *								&lt;col name:string&gt;, &lt;value:string or int&gt;
	 *			'&&', '||', '!'
	 *						--	logical and, logical or, negates value respectively
	 *							operands for '&&' and '||':
	 *								&lt;statement&gt;, &lt;statement&gt;
	 *							operands for '!':
	 *								&lt;statement&gt;
	 *		use $DBOP[str] to get operator object named str
	 *		example: array($DBOP["="], "id", "1") means "id = 1" </pre>
	 * @param array $orderby array(<col name> => 'ASC'|'DESC') meaning how to sort the result.
	 * @param int $offset meaning start from which.
	 * @param int $amount meaning get how many
	 * @return array the data fetched from database
	 */
	abstract protected function
		select_from($tablename, $cols = NULL, $whereclause = NULL, $orderby = NULL, $offset = NULL, $amount = NULL);

	/**
	 * update data in the database
	 * @param string $tablename name of table
	 * @param array $newvalue new value array(row_name => VALUE);VALUE :: value OR array('type' => TYPE,'value' => value)
	 * @param array $whereclause whereclause
	 * @return int number of affected rows or TRUE
	 * @see select_from
	 */
	abstract protected function update_data($tablename, $newvalue, $whereclause = NULL);

	/**
	 * @ignore
	 */
	function __destruct()
	{
		$this->close();
	}

	/**
	 * get total number of queries
	 * @return int query amount
	 */
	abstract protected function get_query_amount();

	/**
	 * start a transaction
	 * Note: transaction is not allowed to be nested
	 * if an SQL error happens in a transaction, it will be rollbacked
	 * automatically
	 * @return void
	 */
	abstract protected function transaction_begin();

	/**
	 * commit a transaction
	 * @return void
	 */
	abstract protected function transaction_commit();

	/**
	 * rollback a transaction, meaning discarding it
	 * @return void
	 */
	abstract protected function transaction_rollback();
}

/*
 * vim:shiftwidth=4
 * vim:tabstop=4
 */

