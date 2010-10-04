<?php
/* 
 * $File: theme-functions-template.php
 * $Date: Mon Oct 04 21:39:52 2010 +0800
 */
/**
 * @package orzoj-website
 * @license http://gnu.org/licenses GNU GPLv3
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
 * register a checker to be used by tf_form_get_text_input()
 * @param callback $func the checker function, which takes a string of user input as argument
 *		and return the checking result as a string
 * @return int checker id
 * @see tf_form_get_text_input
 */
function tf_form_register_checker($func)
{
}

/**
 * get a form input field of text type 
 * @param string $prompt the prompt (the text displayed with this field)
 * @param string $post_name the index in the $_POST array containing the user input
 * @param int|NULL $checker the id of checker returned by tf_register_checker, or NULL if no checker needed
 * @param string|NULL $default the initial value to be displayed in the box (HTML encoded), or NULL if not needed
 * @return string the HTML code of this field
 * @see tf_register_checker
 */
function tf_form_get_text_input($prompt, $post_name, $checker = NULL, $default = NULL)
{
}

/**
 * get a form input field for inputing long text
 * @see tf_form_get_text_input
 */
function tf_form_get_long_text_input($prompt, $post_name, $default = NULL)
{
}

/**
 * get a rich text editor
 * @param string $prompt
 * @param string $editor_name the editor name, used for retrieving data
 * @param string|NULL $default
 * @return string the HTML code
 * @see tf_form_get_editor_data
 */
function tf_form_get_rich_text_editor($prompt, $editor_name, $default = NULL)
{
}

/**
 * get the data (HTML code) from the rich text editor
 * @param string $editor_name editor name
 * @return string the HTML encoded data
 * @see tf_form_get_rich_text_editor
 */
function tf_form_get_rich_text_editor_data($editor_name)
{
}

/**
 * get a theme browser, which will post the id of chosen theme to $post_name
 * @return string
 */
function tf_form_get_theme_browser($prompt, $post_name, $default = NULL)
{
}

/**
 * get a team selector
 * @param int|NULL $default default team id if not NULL
 * @see tf_form_get_team_selector_value
 */
function tf_form_get_team_selector($prompt, $selector_name, $default = NULL)
{
}

/**
 * @return int selected team id, or 0 if none
 * @see tf_form_get_team_selector
 */
function tf_form_get_team_selector_value($selector_name)
{
}

/**
 * get a selector for group id
 * @param string $prompt
 * @param string $selector_name
 * @param NULL|array $default if not NULL, the array of default group ids
 * @return string
 * @see tf_form_get_gid_selector_value
 */
function tf_form_get_gid_selector($prompt, $selector_name, $default = NULL)
{
}

/**
 * get the value of a gid selector
 * @return array selected group ids
 * @see tf_form_get_gid_selector
 */
function tf_form_get_gid_selector_value($selector_name)
{
}

/**
 * get a editor for editing source
 * @param string $prompt
 * @param string $name editor name
 * @param string $default
 * @return string
 */
function tf_form_get_source_editor($prompt, $name, $default = NULL)
{
}

/**
 * @return string the non-HTML-encoded source 
 */
function tf_form_get_source_editor_data($name)
{
}

/**
 * get an avatar browser, which will post the id of chosen avatar with $post_name
 * @see tf_form_get_text_input
 */
function tf_form_get_avatar_browser($prompt, $post_name, $default = NULL)
{
}

/**
 * get a poassword form input
 * @param NULL|string $confirm_input if not NULL, it should be the prompt for confirming password input
 * @see tf_form_get_text_input
 */
function tf_form_get_passwd($prompt, $post_name, $confirm_input = NULL)
{
}

/**
 * get a selction list
 * @param string $prompt
 * @param string $post_name
 * @param array $options in the format array(&lt;display name&rt; => &lt;option value&rt;)
 * @param string $default the value of defaultly selected option
 * @return string the HTML code
 */
function tf_form_get_select($prompt, $post_name, $options, $default = NULL)
{
}

/**
 * convert problem information to HTML code
 * @param array $pinfo as $PROB_VIEW_PINFO described in problem.php
 * @return string
 */
function tf_get_prob_html($pinfo)
{
}

