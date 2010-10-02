<?php
/* 
 * $File: theme-functions-template.php
 * $Date: Sat Oct 02 21:23:22 2010 +0800
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
 * register a checker to be used by tf_get_form_text_input()
 * @param callback $func the checker function, which takes a string of user input as argument
 *		and return the checking result as a string
 * @return int checker id
 * @see tf_get_form_text_input
 */
function tf_register_checker($func)
{
}

/**
 * get a form input field of text type 
 * @param string $prompt the prompt (the text displayed with this field)
 * @param string $post_name the index in the $_POST array containing the user input
 * @param int|NULL $checked the id of checker returned by tf_register_checker, or NULL if no checker needed
 * @return string the HTML code of this field
 * @see tf_register_checker
 */
function tf_get_form_text_input($prompt, $post_name, $checker = NULL)
{
}

/**
 * get a form input field for inputing long text
 * @see tf_get_form_text_input
 */
function tf_get_form_long_text_input($prompt, $post_name)
{
}


/**
 * get an avatar browser, which will post the id of chosen avatar with $post_name
 * @see tf_get_form_text_input
 */
function tf_get_form_avatar_browser($prompt, $post_name)
{
}

/**
 * get a poassword form input, which requires a user confirm of the password
 * @see tf_get_form_text_input
 */
function tf_get_form_passwd_with_verifier($post_name)
{
}

/**
 * get a selction list
 * @param string $prompt
 * @param string $post_name
 * @param array $options in the format array(&lt;display name&rt; => &lt;option value&rt;)
 * @return string the HTML code
 */
function tf_get_form_select($prompt, $post_name, $options)
{
}

