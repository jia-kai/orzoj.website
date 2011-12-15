<?php
/*
 * $File: plugin.php
 * $Date: Thu Dec 15 14:28:30 2011 +0800
 */
/**
 * @package orzoj-website
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


/**
 * @ignore
 */
$_filters = array();

/**
 * @ignore
 */
function _filter_cmp_priority($a, $b)
{
	return $a['priority'] - $b['priority'];
}

/**
 * @ignore
 */
$_filter_need_sort = array();

/**
 * @ignore
 */
function _filter_sort($hook_name)
{
	global $_filter_need_sort, $_filters;
	if (isset($_filter_need_sort[$hook_name]))
	{
		unset($_filter_need_sort[$hook_name]);
		usort($_filters[$hook_name], '_filter_cmp_priority');
	}
}

/**
 * @ignore
 */
function _filter_changed($hook_name)
{
	global $_filter_need_sort;
	$_filter_need_sort[$hook_name] = TRUE;
}

/**
 * @ignore
 */

/**
 * add a filter (filter_add() should be called by plugins)
 * @param string $hook_name name of hook called by filter_apply(@see filter_apply)
 * @param callback $func the function to be called
 * @param int $priority priority of the function.
 *			The smaller, the earlier to be executed (must be within [-10, 10])
 * @return void
 */
function filter_add($hook_name, $func, $priority = 0)
{
	if ($priority < -10 || $priority > 10)
		throw new Exc_inner(__('invalid priority: %d', $priority));
	global $_filters;
	$thisfilter = array(
		'func' => $func,
		'priority' => $priority,
	);
	if (!isset($_filters[$hook_name]))
		$_filters[$hook_name] = array();
	array_push($_filters[$hook_name], $thisfilter);

	_filter_changed($hook_name);
}

/**
 * Check whether a function has been added to a hook
 * @param string $hook_name name of hook
 * @param string $func_name name of function to check
 * @return bool 
 */
function filter_exists($hook_name,$func_name)
{
	global $_filters;
	if (!isset($_filters[$hook_name]))
		return FALSE;
	foreach ($_filters[$hook_name] as $func)
		if ($func['func_name'] == $func_name)
			return TRUE;
	return FALSE;
}

/**
 * delete a filter
 * @param string $hook_name 
 * @param callback $func
 * @return bool whether succeed
 */
function filter_remove($hook_name, $func)
{
	global $_filters;
	if (!isset($_filters[$hook_name]))
		return FALSE;
	foreach ($_filters[$hook_name] as $id => $ft)
	{
		if ($ft['func'] == $func)
		{
			unset($_filters[$hook_name][$id]);
			_filter_changed($hook_name);
			return TRUE;
		}
	}
	return FALSE;
}

/**
 * apply filters and iterate on $value
 * @param string $hook_name is the name of hook to be called
 * @param mixed $value
 * @param mixed more_param more param is supported,
 *		which will be passed to functions in the hook
 * @return mixed the final value after iteration
 */
function filter_apply($hook_name, $value)
{
	_filter_sort($hook_name);
	global $_filters;
	$args = array_slice(func_get_args(), 1);
	if (isset($_filters[$hook_name]))
		foreach ($_filters[$hook_name] as $id => $filter)
			$args[0] = call_user_func_array($filter['func'], $args);
	return $args[0];
}

/**
 * apply filters without iterating
 * @param string name of the hook
 * @param mixed more_param
 * @return void
 */
function filter_apply_no_iter($hook_name)
{
	_filter_sort($hook_name);
	global $_filters;
	$args = array_slice(func_get_args(), 1);
	if (isset($_filters[$hook_name]))
		foreach ($_filters[$hook_name] as $id => $filter)
			call_user_func_array($filter['func'], $args);
}


$_plugin_enabled_plugins = unserialize(option_get('enabled_plugins','a:0:{}'));
foreach ($_plugin_enabled_plugins as $plugin)
{
	require_once $root_path . 'contents/plugins/' . $plugin . '/plugin.php';
}

