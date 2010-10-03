<?php
/* 
 * $File: theme-functions-template.php
 * $Date: Sun Oct 03 15:37:48 2010 +0800
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
 * @param int|NULL $checked the id of checker returned by tf_register_checker, or NULL if no checker needed
 * @param string|NULL $default the initial value to be displayed in the box (plain
 *		text, not HTML encoded), or NULL if not needed
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
 * @return string|NULL return NULL on error
 * @see tf_form_get_rich_text_editor
 */
function tf_form_get_editor_data($editor_name)
{
}


/**
 * get an avatar browser, which will post the id of chosen avatar with $post_name
 * @see tf_form_get_text_input
 */
function tf_form_get_avatar_browser($prompt, $post_name)
{
}

/**
 * get a poassword form input
 * @param bool|string $confirm_input if not FALSE, it should be the prompt for confirming password input
 * @see tf_form_get_text_input
 */
function tf_form_get_passwd($prompt, $post_name, $confirm_input = FALSE)
{
}

/**
 * get a selction list
 * @param string $prompt
 * @param string $post_name
 * @param array $options in the format array(&lt;display name&rt; => &lt;option value&rt;)
 * @return string the HTML code
 */
function tf_form_get_select($prompt, $post_name, $options)
{
}

