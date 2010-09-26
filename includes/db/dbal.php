<?php
/*
 * $File: dbal.php
 * $Date: Sun Sep 26 14:09:43 2010 +0800
 * $Author: Fan Qijiang <fqj1994@gmail.com>, Jiakai <jia.kai66@gmail.com>
*/
/**
 * @package dbal
 * @license http://gnu.org/licenses/ GNU GPLv3
 * @author Fan Qijiang <fqj1994@gmail.com>
 * @version phpweb-1.0.0alpha1
 * @copyright (c) Fan Qijiang
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

/**
 * Database abstract laywer base
 * If dbal.directquery is set to true.
 * Functions which will generate query statements will return the statements(array) instead of querying result.
 */

$DBOP = array();

class dbal
{
	/**
	 * Direct Query(true) or Return SQL Statement(FALSE)
	 */
	var $directquery = true;
	/**
	 * the link resource
	 */
	var $linker;
	/**
	 * database laywer's ID
	 */
	var $dblayer;
	/**
	 * This function is used to connect database.
	 * @param string $host host address to connect
	 * @param int $port port number of host
	 * @param string $user username
	 * @param string $passwd password
	 * @param string $database database name
	 * @return boolean If succees,TRUE is returned.Otherwise,false is returned
	 */
	function connect($host,$port,$user,$passwd,$database)
	{
		return $this->_connect($host,$port,$user,$passwd,$database);
	}
	/**
	 * Close database connection
	 */
	function close()
	{
		return $this->_close();
	}
	/**
	 * Get error message
	 * @return mixed Latest error message is returned
	 */
	function error()
	{
		return $this->_error();
	}
	/**
	 * Create a table in a database
	 * @param string $tablename name of a table
	 * @param mixed $structure structure of a table<br>
	 *   details:<br>
	 *    array(<br>
	 *    'cols' => array(<br>
	 *     $colname => array('type' => $coltype, <br>
	 *						'default' => $defaultvalue , <br>
	 *						'auto_assign' => true/false)<br>
	 *    ),<br>
	 *     'primary key' => $keycolname<br>
	 *     )<br>
	 * @return boolean If succeeded, TRUE will be returned,otherwise, FALSE will 
	 *  be returned,and error infomation is set.
	 * @example doc_examples/dbal_create_table.php
	 */
	function create_table($tablename,$structure)
	{
		return $this->_create_table($tablename,$structure);
	}
	/**
	 * Get the number of rows in a table
	 * @param string $tablename name of a table
	 * @param array whereclause the where clause array
	 * @return bool|int if succeeded ,the number of rows or TRUE will be returned,
	 *  otherwise,FALSE(not ZERO,use === to check) will be returned,
	 *  and you can use $object->error() or $object->errno() to get
	 *  the error information if error is supported by database
	 * @see select_from
	 */
	function get_number_of_rows($tablename,$whereclause = NULL)
	{
		return $this->_get_number_of_rows($tablename,$whereclause);
	}
	/**
	 * Delete items from specific table
	 * @param string $tablename name of a table
	 * @param array whereclause the where clause array
	 * @return bool|int If succeeded,infected rows or TRUE will be returned,\
	 *	otherwise,FALSE(NOT 0) will be returned.
	 * @see select_from
	 */
	function delete_item($tablename,$whereclause = NULL)
	{
		return $this->_delete_item($tablename,$whereclause);
	}
	/**
	 * Insert data into a table
	 * @param string $tablename name of a table
	 * @param array $value what to insert.
	 * @return int|bool If succeded,insert id ,ZERO(0) OR TRUE will be returned,
	 *	otherwise FALSE will be returned.Use === or !== to check.
	 * @see update_data
	 */
	function insert_into($tablename,$value)
	{
		return $this->_insert_into($tablename,$value);
	}
	function delete_table($tablename)
	{
		return $this->_delete_table($tablename);
	}
	function table_exists($tablename)
	{
		return $this->_table_exists($tablename);
	}
	/**
	 * This function is used to select data from a table
	 * @param string $tablename name of the table
	 * @param mixed $rows array(row1,row2,row3,...) OR NULL(means *)
	 * @param array $whereclause array of tokens, in the form of prefix expression
	 *		each element in the array  is either an operator or an operand
	 *		valid operators:
	 *		   '=,'!='		--	equality or inequality test for integer
	 *							the seconde operand will be converted to int
	 *							operands:
	 *								<col name:string>, <value:string or int>
	 *		   '=s','!=s'	--	equality or inequality test for string
	 *							operands:
	 *								<col name:string>, <value:string>
	 *		   '>', '>=', '<', '<='
	 *						--	greater than, greater than or equal to, less than, less than or equal to respectivelty
	 *							the second operand will be converted to int
	 *							operands:
	 *								<col name:string>, <value:string or int>
	 *			'&&', '||', '!'
	 *						--	logical and, logical or, negates value respectively
	 *							operands for '&&' and '||':
	 *								<statement>, <statement>
	 *							operands for '!':
	 *								<statement>
	 *		use $DBOP[str] to get operator object named str
	 *		example: array($DBOP["="], "id", "1") means "id = 1"
	 * @param array $orderby array(row1 => 'ASC'/'DESC',row2 => 'ASC'/'DESC',...);meaning how to sort the result.
	 * @param int $offset meaning start from which.
	 * @param int $amount meaning get how many
	 * @return array An array refering to the data is returned.
	 */
	function select_from($tablename,$rows = NULL,$whereclause = NULL,$orderby = NULL,$offset = NULL,$amount = NULL)
	{
		return $this->_select_from($tablename,$rows,$whereclause,$orderby,$offset,$amount);
	}
	/**
	 * update data in the database
	 * @param string $tablename name of table
	 * @param array $newvalue new value array(row_name => VALUE);VALUE :: value OR array('type' => TYPE,'value' => value)
	 * @param array $whereclause whereclause
	 * @return int|bool if success,affected rows OR TRUE will be returned,otherwise ,false
	 * @see select_from
	 */
	function update_data($tablename,$newvalue,$whereclause = NULL)
	{
		return $this->_update_data($tablename,$newvalue,$whereclause);
	}
	/**
	 * @access private
	 */
	function __destruct()
	{
		$this->close();
	}
	/**
	 * Get Query Amount
	 * @return int query amount
	 */
	function get_query_amount()
	{
		return $this->_get_query_amount();
	}
	/**
	 * Start a transaction
	 */
	function transaction_begin()
	{
		return $this->_transaction_begin();
	}
	/**
	 * Commit a transaction
	 */
	function transaction_commit()
	{
		return $this->_transaction_commit();
	}
	/**
	 * Rollback meaning discard a transaction
	 */
	function transaction_rollback()
	{
		return $this->_transaction_rollback();
	}

	/**
	 * Make a Query
	 */
	function query($query)
	{
		return $this->_query();
	}
	/**
	 * Make queries
	 */
	function queries($queries)
	{
		return $this->_queries($queries);
	}
}

/*
 * vim:shiftwidth=4
 * vim:tabstop=4
 */

