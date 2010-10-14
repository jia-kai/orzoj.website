<?php
/* 
 * $File: functions.php
 * $Date: Thu Oct 14 14:29:39 2010 +0800
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
 * @ignore
 */
$_tf_checker = array();
$_tf_cur_checker_div = NULL;

/**
 * register a checker to be used by tf_form_get_text_input()
 * @param callback $func the checker function, which takes a string of user input as argument
 *		and return the checking result as a string
 * @return int checker id
 * @see tf_form_get_text_input
 */
function tf_form_register_checker($func)
{
	global $_tf_checker;
	$id = sha1(serialize($func));
	$_tf_checker[$id] = $func;
	return $id;
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
	global $_tf_checker, $_tf_cur_checker_div;
	$id = _tf_get_random_id();
	if (!is_null($checker))
	{
		$checker = <<<EOF
onblur='form_checker("$checker", "$id", "$_tf_cur_checker_div")'
EOF;
	} else $checker = '';
	return sprintf('<tr><td><label  for="%s">%s</label></td>
		<td><input type="text" id="%s" name="%s" %s %s /></td></tr>' . "\n",
		$id, $prompt, $id, $post_name,
		is_null($default) ? '' : sprintf('value="%s"', htmlcode($default)),
		$checker);
}

/**
 * get a form input field for inputing long text
 * @see tf_form_get_text_input
 */
function tf_form_get_long_text_input($prompt, $post_name, $default = NULL)
{
	if (is_null($default))
		$default = '';
	else $default = htmlencode($default);
	$id = _tf_get_random_id();
	return "<tr><td><label for=\"$id\">$prompt</label></td><td>
		<textarea name=\"$post_name\" id=\"$id\">$default</textarea>
		<br /></td></tr>\n";
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
	return "<tr><td></td><td>this is an editor</td></tr>";
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
	global $theme_path;
	$id = _tf_get_random_id();
	$idi = _tf_get_random_id();
	if (is_null($default))
		$default = 0;
	$default_file = avatar_get_url($default);
	$browse = array(get_page_url($theme_path . 'images/browse_avatar.gif'), __('Browse'));
	return <<<EOF
<tr>
<td>$prompt</td>
<td>
	<input type="hidden" name="$post_name" value="$default" id="$id" />
	<img src="$default_file" alt="avatar" id="$idi" />
	<a href="#" onclick="avatar_browser('$id', '$idi', 0)" style="float:right">
		<img src="$browse[0]" alt="browse" width="16" height="16" />$browse[1]
	</a>
</td>
</tr>
EOF;
}

/**
 * get a poassword form input
 * @param NULL|string $confirm_input if not NULL, it should be the prompt for confirming password input
 * @see tf_form_get_text_input
 */
function tf_form_get_passwd($prompt, $post_name, $confirm_input = NULL)
{
	$id = _tf_get_random_id();
	$str = "<tr><td><label for=\"$id\">$prompt</label></td>
		<td><input type=\"password\" name=\"$post_name\" id=\"$id\" /></td></tr>\n";
	if (is_string($confirm_input))
	{
		global $_tf_cur_checker_div;
		$id1 = _tf_get_random_id();
		$str .= <<<EOF
<tr><td><label for="$id1">$confirm_input</label></td><td>
<input id="$id1" type="password" onblur='form_verify_passwd("$id", "$id1", "$_tf_cur_checker_div")' />
</td></tr>
EOF;
	}
	return $str;
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
	$id = _tf_get_random_id();
	$str = "<tr><td><label for=\"$id\">$prompt</label></td>
		<td><select name=\"$post_name\" id=\"$id\">";
	foreach ($options as $name => $value)
	{
		$str .= "<option value=\"$value\" ";
		if ($value == $default)
			$str .= 'selected="selected"';
		$str .= ">$name</option>\n";
	}
	$str .= "</select></td></tr>\n";
	return $str;
}

/**
 * get a hidden field for posting values
 * @param string $post_name
 * @param string $post_value
 * @return string
 */
function tf_form_get_hidden($post_name, $post_value)
{
}

/**
 * convert problem information to HTML code
 * @param array $pinfo as $PROB_VIEW_PINFO described in problem.php
 * @return string
 */
function tf_get_prob_html($pinfo)
{
	$desc = unserialize($pinfo['desc']);
	$content  = '
<div id="prob-view-single">
<div id="prob-view-single-title">'
. $pinfo['title'] . '(' . $pinfo['code']. ')</div>' 
	.'<div id="prob-view-single-subtitle">'
		. __('Time Limit: ') . $desc['time'] . ' '
		. __('Memory Limit: ') . $desc['memory'] . '<br />'
		. __('Total Submit: ') . $pinfo['cnt_submit'] . ' '
		. __('Accepted: ') . $pinfo['cnt_ac']
		. '</div> <!-- id: prob-view-single-subtitle-->'
		. '<div id="prob-view-single-desc">';
	$translate = array(
		'desc' => __('Description'), 
		'input_fmt' => __('Input Format'), 
		'output_fmt' => __('Output Format'), 
		'input_samp' => __('Input Sample'), 
		'output_samp' => __('Output Sample'),
		'source' => __('Source'), 
		'hint' => __('Hint')
	);
	foreach ($desc as $key => $item)
		if (isset($translate[$key]))
		{
			$content .= '<p>' . $translate[$key] . '</p>';
			$content .= '<div id="prob-view-single-content">'
				. $item . '<br /></div>';
		}
	$content .= '</div> <!-- id: prob-view-single-desc-->'
		. '</div>';
	return $content;
}

/**
 * @ignore
 */
function _tf_form_generate_body($gen_func)
{
	global $_tf_cur_checker_div;
	$ckid = _tf_get_random_id();
	$_tf_cur_checker_div = $ckid;
	echo "<div class=\"form-checker-result\" id=\"$ckid\">place holder</div>\n";
	echo '<table border="0" style="clear:both">';
	$gen_func();
	echo '</table>';
}

/**
 * @ignore
 */
function _tf_get_random_id()
{
	return 'i' . md5(uniqid(mt_rand(), TRUE));
}

