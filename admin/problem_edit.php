<?php
/*
 * $File: problem_edit.php
 * $Date: Tue Nov 02 20:36:50 2010 +0800
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

/*
 * This page should only be required_once by problem.php 
 */

/**
 * GET arguments:
 *		[edit]: int, the id of problem to be edited, or 0 for adding a new problem
 *		[do]:  indicate the submission of the form
 *		[delete]: 
 *			delete this problem (already confirmed), id must be sent via $_POST['pid']
 *			and verification code must be sent via $_POST['delete_verify']
 *
 * POST arguments:
 *		those in the form
 *		['delete_verify']: verification code for deleting a problem
 *		['edit_verify']: verification code for adding/editing a problem
 *		['ajax_mode']: if set, the first character will be 0 to refresh only page-info div, or
 *			1 to refresh the whole page
 *
 * SESSION variables:
 *		delete_verify, edit_verify
 */

session_add_prefix('edit');

if (isset($_GET['delete']) && !empty($_POST['pid']) &&
	!empty($_POST['delete_verify']) && $_POST['delete_verify'] == session_get('delete_verify'))
{
	prob_delete(intval($_POST['pid']));
}

$fields = array(
	// <column name> => <show function>
	// <column name> => array(<edit function>, <value retrieving function>)
	'id' => 'show_id',
	'title' => array('edit_title', 'get_title'),
	'code' => array('edit_code', 'get_code'),
	'desc' => array('edit_desc', 'get_desc'),
	'io' => array('edit_io', 'get_io'),
	'perm' => array('edit_perm', 'get_perm')
);

$pinfo = NULL;
if (isset($_GET['do']) && !empty($_POST['edit_verify']) && $_POST['edit_verify'] == session_get('edit_verify'))
{
	try
	{
		$pinfo = array('time' => time());
		foreach ($fields as $f)
			if (is_array($f))
				$f[1]();
		if (empty($_GET['edit']))
		{
			if (prob_get_id_by_code($pinfo['code']))
				throw new Exc_runtime(__('problem code %s already exists', $pinfo['code']));
			$pid = $db->insert_into('problems', $pinfo);
			$_GET['edit'] = $pid;
			$cur_page_link  = "index.php?page=$cur_page&amp;edit=$pid";
			$new_prob = TRUE;
			$pinfo['id'] = $pid;
		}
		else
			$db->update_data('problems', $pinfo, array($DBOP['='], 'id', $pinfo['id'] = $_GET['edit']));
		if (isset($_POST['ajax_mode']) && isset($new_prob))
			echo '1';
		else
			echo '0';
		if (isset($new_prob))
			echo '<div id="ajax-page">';
		get_info_div('info', __('Problem successfully added/updated (at %s)', time2str(time())));
		if (isset($new_prob))
			echo '</div>';
	}
	catch (Exc_orzoj $e)
	{
		if (isset($_POST['ajax_mode']))
			echo '0';
		get_info_div('error', __('Failed to add/updated problem: %s', htmlencode($e->msg())));
		$pinfo = NULL;
	}
	if (isset($_POST['ajax_mode']) && !isset($new_prob))
		return;
}
if (is_null($pinfo))
{
	if (!empty($_GET['edit']))
	{
		$pinfo = $db->select_from('problems', array_keys($fields), array(
			$DBOP['='], 'id', $_GET['edit']));
		if (empty($pinfo))
			die('no such problem');
		else
			$pinfo = $pinfo[0];
		if (empty($pinfo['desc']))
			get_info_div('warning', __('This problem has been marked as being deleted'));
	}
}

if (!isset($new_prob))
	echo '<div id="ajax-page"></div>';

echo "<form action='$cur_page_link&amp;do=1' method='post' id='edit-prob-form'>";
foreach ($fields as $f)
{
	if (is_array($f))
		$f = $f[0];
	$f();
}
$edit_verify = get_random_id();
session_set('edit_verify', $edit_verify);
_make_form_hidden('edit_verify', $edit_verify);
echo '<input style="clear:both; float: left" type="submit" value="' . __('submit') . '" />';
echo '</form>';
if (isset($pinfo['id']))
{
	echo '<div style="clear: both; float: left; margin-top: 50px;">';
	echo "<form action='$cur_page_link&amp;delete=1' method='post' id='delete-prob-form'>";
	$delete_verify = get_random_id();
	_make_form_hidden('pid', $pinfo['id']);
	_make_form_hidden('delete_verify', $delete_verify);
	session_set('delete_verify', $delete_verify);
	echo '<button type="button" onclick="delete_prob()">' . __('Delete problem') . '</button>';
	echo '</form>';
	echo '</div>';
}

function _get_val(&$array, $key, $default = NULL)
{
	return is_array($array) && isset($array[$key]) ? $array[$key] : $default;
}

function _make_form_input($prompt, $post_name, $default = NULL, $add_div = TRUE)
{
	$id = get_unique_id();
	if (is_null($default))
		$default = '';
	if ($add_div)
		echo '<div class="form-field">';
	echo "<label for='$id'>$prompt</label><input type='text' name='$post_name' value='$default' id='$id' />";
	if ($add_div)
		echo '</div>';
}

function _make_form_hidden($name, $value)
{
	echo "<input type='hidden' name='$name' value='$value' />";
}

function _make_form_textarea($prompt, $post_name, $default = NULL, $small = FALSE)
{
	$id = get_unique_id();
	if (is_null($default))
		$default = '';
	$class = $small ? 'small' : 'big';
	echo "<div class='form-field'><label for='$id'>$prompt</label>
		<textarea class='$class' id='$id' name='$post_name'>$default</textarea></div>";
}

function get_post($name)
{
	if (!isset($_POST[$name]))
		throw new Exc_runtime(__('incomplete post'));
	return $_POST[$name];
}

function show_id()
{
	global $pinfo;
	$id = _get_val($pinfo, 'id');
	if (!empty($id))
		echo '<div class="form-field"><label>' . __('Problem id:') . '</label>' . $pinfo['id'] . '</div>';
}

function edit_code()
{
	global $pinfo;
	_make_form_input(__('Problem code:'), 'code', _get_val($pinfo, 'code'));
}

function get_code()
{
	$val = get_post('code');
	prob_validate_code($val);
	global $pinfo;
	$pinfo['code'] = $val;
}

function edit_title()
{
	global $pinfo;
	_make_form_input(__('Problem title:'), 'title', _get_val($pinfo, 'title'));
}

function get_title()
{
	global $pinfo;
	$pinfo['title'] = htmlencode(get_post('title'));
}

function edit_desc()
{
	global $pinfo, $root_path;
	if (!is_null($pinfo) && !empty($pinfo['desc']))
		$desc = unserialize($pinfo['desc']);
	else
		$desc = array();
	_make_form_input(__('Time limit:'), 'desc[time]', _get_val($desc, 'time', __('1 second')));
	_make_form_input(__('Memory limit:'), 'desc[memory]', _get_val($desc, 'memory', __('256 MB')));
	$fields = array(
		'desc' => __('Description'),
		'input_fmt' => __('Input Format'),
		'output_fmt' => __('Output Format'));
	foreach ($fields as $key => $val)
	{
		$id = get_random_id();
		echo "<div class='form-field'><label for='$id'>$val</label><br />";
		echo "<textarea id='$id' name='desc[$key]'>";
		echo htmlspecialchars(_get_val($desc, $key));
		echo '</textarea><script type="text/javascript">
			CKEDITOR.replace("' . $id . '");
		</script></div>';
	}

	$fields = array(
		'input_samp' => __('Sample Input'),
		'output_samp' => __('Sample Output'),
		'range' => __('Range'),
		'source' => __('Source'),
		'hint' => __('Hint')
	);

	$small = 0;
	foreach ($fields as $key => $val)
	{
		$small ++;
		_make_form_textarea($val, "desc[$key]", _get_val($desc, $key), $small > 2);
	}
}

function get_desc()
{
	global $PROB_DESC_FIELDS_ALLOW_XHTML;
	$desc_post = get_post('desc');
	if (!is_array($desc_post))
		throw new Exc_runtime('invalid desc type');
	$fields = array(
		'time' => __('time limit'),
		'memory' => __('memory limit'),
		'desc' => __('problem description'),
		'input_fmt' => __('input format'),
		'output_fmt' => __('output format'),
		'input_samp' => __('sample input'),
		'output_samp' => __('sample output'),
		'range' => __('range'),
		'source' => __('problem source'),
		'hint' => __('hint')
	);
	$fields_required = array(
		'time', 'memory', 'desc', 'input_fmt', 'output_fmt',
		'input_samp', 'output_samp', 'range');
	$desc = array();
	foreach ($fields as $f => $name)
	{
		if (!isset($desc_post[$f]))
			throw new Exc_runtime(__('incomplete post'));
		if (in_array($f, $fields_required) && empty($desc_post[$f]))
			throw new Exc_runtime(__('%s can not be empty', $name));
		$val = $desc_post[$f];
		if (in_array($f, $PROB_DESC_FIELDS_ALLOW_XHTML))
		{
			try
			{
				xhtml_validate($val);
			}
			catch (Exc_xhtml $e)
			{
				throw new Exc_runtime(__('XHTML validation error for field %s: %s', $name, $e->msg()));
			}
		}
		else
			$val = htmlspecialchars($val);
		$desc[$f] = $val;
	}
	global $pinfo;
	$pinfo['desc'] = serialize($desc);
}

function edit_io()
{
	global $pinfo;
	if (empty($pinfo['io']))
	{
		$io = array('', '');
		$checked = array('checked="checked"', '');
	}
	else
	{
		$io = unserialize($pinfo['io']);
		$checked = array('', 'checked="checked"');
	}
	echo '<div class="form-field">';
	echo '<label>' . __('Problem I/O:') . '</label>';
	echo '<div class="form-field-prob-io">';
	echo '<div>';
	echo '<span>' . __('I/O method:') . '</span>';

	$id = get_unique_id();
	echo "<input type='radio' onchange='$(\"#prob-io\").slideUp()' name='io_method' value='0' id='$id' $checked[0] />";
	echo "<label for='$id'>" . __('Standard I/O') . '</label>';
	$id = get_unique_id();
	echo "<input type='radio' onchange='$(\"#prob-io\").slideDown()' name='io_method' value='1' id='$id' $checked[1] />";
	echo "<label for='$id'>" . __('File I/O') . '</label>';

	echo '</div>';
	echo '<div id="prob-io">';
	_make_form_input(__('Input filename:'), 'io0', $io[0], FALSE);
	echo '<br />';
	_make_form_input(__('Output filename:'), 'io1', $io[1], FALSE);
	echo '</div></div></div>';
}

function get_io()
{
	global $pinfo;
	if (get_post('io_method') == '0')
		$pinfo['io'] = NULL;
	else
	{
		prob_validate_io($io0 = get_post('io0'));
		prob_validate_io($io1 = get_post('io1'));
		$pinfo['io'] = serialize(array($io0, $io1));
	}
}

function edit_perm()
{
	global $pinfo;
	echo '<div class="form-field">';
	echo '<label style="float: left">' . __('Set problem permission') .
		'</label><div style="float: left">';
	form_get_perm_editor('perm', _get_val($pinfo, 'perm'));
	echo '</div></div>';
}

function get_perm()
{
	global $pinfo;
	$pinfo['perm'] = form_get_perm_editor_val('perm');
}

?>
<script type="text/javascript">
$(document).ready(function(){
	var f = $("#edit-prob-form");
	f.append('<input type="hidden" name="ajax_mode" value="1" />');
	f.bind('submit', function(){
		var f = $("#edit-prob-form");
		$.ajax({
			'type': 'post',
			'cache': false,
			'url': f.attr('action'),
			'data': f.serializeArray(),
			'success': function(data){
				if (data.charAt(0) == '0')
					$('#ajax-page').html(data.substr(1));
				else
					$('#ajax-page').parent().html(data.substr(1));
				window.scrollTo(0, 0);
			}
		});
		return false;
	});
	<?php if (empty($pinfo['io'])) echo '$("#prob-io").slideUp();' ?>
});

function delete_prob()
{
	if (confirm("<?php echo __('Are you sure to delete this problem?')?>"))
	{
		document.getElementById('delete-prob-form').submit();
		window.location="index.php?page=prob";
	}
}

</script>

