<?php
/*
 * $File: plugin.php
 * $Date: Wed Apr 14 10:01:31 2010 -0400
 * $Author: Fan Qijiang <fqj1994@gmail.com>
 */
/**
 * @package plugin
 * @license http://gnu.org/licenses/ GNU GPLv3
 * @version phpweb-1.0.0alpha1
 * @author Fan Qijiang <fqj1994@gmail.com>
 * @copyright (c) Fan Qijiang
 */
/*
	Copyright (C) <2009,2010>  (Fan Qijiang) <fqj1994@gmail.com>

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


$filters = array();

/**
 * @ignore
 */
function plugin_add_filter_cmp($a,$b)
{
	return $a['priority'] - $b['priority'];
}

/**
 * Add a filter
 * @param string $hook_name name of hook called by apply_filters(@see apply_filters) and do_action(@see do_action)
 * @param string $func_name name of function to call
 * @param int $priority priority of the function.If it's smaller,it will be executed first
 * @param int $accepted_args the number of accepted_args
 * @return bool success true,otherwise false
 */
function add_filter($hook_name,$func_name,$priority = 10,$accepted_args = 1)
{
	global $filters;
	if (has_filter($hook_name,$func_name)) return false;
	$thisfilter = array(
		'func_name' => $func_name,
		'priority' => $priority,
		'accepted_args' => $accepted_args
	);
	if (!isset($filters[$hook_name])) $filters[$hook_name] = array();
	array_push($filters[$hook_name],$thisfilter);
	usort($filters[$hook_name],plugin_add_filter_cmp);
	return true;
}

/**
 * Check whether a function added to a hook
 * @param string $hook_name name of hook
 * @param string $func_name name of function to check
 * @return bool if exists,TRUE is returned otherwise false
 */
function has_filter($hook_name,$func_name)
{
	global $filters;
	if (!isset($filters[$hook_name])) return false;
	foreach ($filters[$hook_name] as $id => $func)
		if ($func['func_name'] == $func_name) return true;
	return false;
}


/**
 * Apply filters
 * @param string $hook_name is the name of hook you want to execute
 * @param mixed $value a value
 * @param mixed more_param more param is supported,for parameters to call function in the hook
 * @return mixed the new value of $value
 */
function apply_filters($hook_name,$value)
{
	global $filters;
	$args = func_get_args();
	foreach ($filters[$hook_name] as $id => $filter)
	{
		$value = call_user_func_array($filter['func_name'],array_slice($args,1,(int)($filter['accepted_args'])));
	}
	return $value;
}

/**
 * Delete a filter
 * @param string $hook_name which hook to search function
 * @param string $func_name what function to delete
 * @return bool if success,TRUE,else FALSE
 */
function remove_filter($hook_name,$func_name)
{
	global $filters;
	foreach ($filters[$hook_name] as $id => $func)
	{
		if ($func['func_name'] == $func_name)
		{
			unset($filters[$hook_name][$id]);
			return true;
		}
	}
	return false;
}

/**
 * @see add_filter
 * @return bool 
 */
function add_action()
{
	$args = func_get_args();
	return call_user_func_array('add_filter',$args);
}

/**
 * @see has_filter
 * @return bool
 */
function has_action()
{
	$args = func_get_args();
	return call_user_func_array('has_filter',$args);
}

/**
 * @see remove_filter
 * @return bool
 */
function remove_action()
{
	$args = func_get_args();
	return call_user_func_array('remove_filter',$args);
}

/**
 * Apply filters but do not change value(compared to apply_filter)
 * @param string name of hook
 * @param mixed more_param more parameters are supported to call functions
 * @return bool TRUE
 */
function do_action($hook_name)
{
	global $filters;
	$args = func_get_args();
	foreach ($filters[$hook_name] as $id => $filter)
	{
		call_user_func_array($filter['func_name'],array_slice($args,1,(int)($filter['accepted_args'])));
	}
	return true;
}


