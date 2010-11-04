<?php
/*
 * $File: group.php
 * $Date: Thu Nov 04 19:27:35 2010 +0800
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
 * page arguments:
 *	GET:
 *		[edit]:int	output a form to edit a group (ajax_mode)
 *			id of group to be edited 
 *		[do_edit]:int  indicate the submission of edit group form (ajax_mode)
 *			the value is the id of group edited
 *			return 0 for failure followed by error message
 *			1 for success with the URL to jump to
 *	POST:
 *		those in the form
 *		[edit_verify, add_verify, batch_verify]:string verification code
 *		[delete]: indicate deletion request (already confirmed)
 *		[move]: indicate moving request
 *	SESSION:
 *		[edit_verify, add_verify, batch_verify]:string verification code
 */

abstract class Grp_admin
{
	/**
	 * $type used in form_get_gid_selector
	 */
	abstract protected function gid_selector_type();

	abstract protected function form_get_gid_select($prompt, $post_name, $default = NULL);

	/**
	 * @return array an array containig 'desc', 'name', 'pgid' as keys
	 * @exception Exc_runtime
	 */
	abstract protected function get_info($gid);

	/**
	 * @return array array of int, containing ids of all children of group $gid, including it self
	 */
	abstract protected function get_all_children($gid);

	/**
	 * @exception Exc_runtime
	 */
	abstract protected function validate_info(&$info);

	abstract protected function update_cache_add($gid);
	abstract protected function update_cache_delete($gid);

	abstract protected function update_db($gid, $info);

	abstract protected function new_grp($info);

	abstract protected function del_grp($gid);

	public function work()
	{
		global $cur_page_link;
		try
		{
			if (!empty($_GET['edit']))
				$this->echo_edit($_GET['edit']);
			if (isset($_GET['do_edit']))
			{
				$this->do_edit($_GET['do_edit']);
				global $cur_page;
				echo "1index.php?page=$cur_page";
				return;
			}
			if (!empty($_POST['batch_verify']) && $_POST['batch_verify'] == session_get('batch_verify'))
			{
				$val = form_get_gid_selector_val('batch_grp');
				if (isset($_POST['delete']))
					$this->do_del($val);
				else if (isset($_POST['move']))
					$this->do_move($val, get_post('move_grp_target'));
				session_set('batch_verify', NULL);
			}
		}
		catch (Exc_orzoj $e)
		{
			if (isset($_GET['do_edit']))
				die('0' . __('Failed to finish requested operations: %s', $e->msg()));
			get_info_div('error', __('Failed to finish requested operations: %s', htmlencode($e->msg())));
		}
		if (isset($_POST['ajax_mode']))
			die;
		echo '<div class="grp-batch-edit">';
		echo "<form action='$cur_page_link&amp;batch=1' method='post' id='grp-batch-edit-form'>";
		echo '<div class="form-title">' . __('Batch Editing') . '</div>';
		form_get_gid_selector('batch_grp', $this->gid_selector_type(), NULL, 'set_edit');
		echo '<button type="button" onclick="delete_grp()">' . __('Delete selected groups') . '</button><br /><br />';
		$this->form_get_gid_select(__('Change parent group of selected groups to:'), 'move_grp_target');
		echo '&nbsp;<button type="button" onclick="move_grp()">' . __('Confirm') . '</button>';
		$code = get_random_id();
		session_set('batch_verify', $code);
		form_get_hidden('batch_verify', $code);
		echo '</form>';
		echo '</div>';

		echo '<div id="edit-grp-div"></div>';

		$this->echo_edit(0);

?>
<script type="text/javascript">
function delete_grp()
{
	if (confirm("<?php echo __('Are you sure do delete these groups?');?>"))
	{
		$("#grp-batch-edit-form").append("<input type='hidden' name='delete' value='1' />");
		submit_batch_edit_form();
	}
}

function move_grp()
{
	$("#grp-batch-edit-form").append("<input type='hidden' name='move' value='1' />");
	submit_batch_edit_form();
}

function submit_batch_edit_form()
{
	$("#grp-batch-edit-form").submit();
}

function set_edit(id)
{
	$.ajax({
		'url': '<?php echo $cur_page_link?>&edit=' + id,
		'type': 'post',
		'cache': false,
		'data': {'ajax_mode': 1},
		'success': function(data){
			$("#edit-grp-div").html(data);
		}
	});
}

function edit_grp_submit(f)
{
	f = $("#" + f);
	$.ajax({
		'url': f.attr('action'),
		'cache': false,
		'type': 'post',
		'data': f.serializeArray(),
		'success': function(data) {
			if (data.charAt(0) == 1)
				window.location = data.substr(1);
			else
				alert(data.substr(1));
		}
	});
}

</script>
<?php
	}

	private function echo_edit($gid)
	{
		global $cur_page_link;
		echo '<div style="clear:both; float: left">';
		$form_id = get_random_id();
		echo "<form action='$cur_page_link&amp;do_edit=$gid' method='post' id='$form_id'>";
		if ($gid)
		{
			echo '<div class="form-title">' . __('Edit Group') . '</div>';
			$info = $this->get_info($gid);
			echo '<div class="form-field">';
			echo '<label>' . __('Group id:') . '</label>';
			echo $gid;
			echo '</div>';
			$code = get_random_id();
			form_get_hidden('edit_verify', $code);
			session_set('edit_verify', $code);
		}
		else
		{
			echo '<div class="form-title">' . __('Add Group') . '</div>';
			$info = array('name' => '', 'desc' => '', 'pgid' => NULL);
			$code = get_random_id();
			form_get_hidden('add_verify', $code);
			session_set('add_verify', $code);
		}
		form_get_input(__('Group name:'), 'name', $info['name']);
		form_get_input(__('Group description:'), 'desc', $info['desc']);
		echo '<div class="form-field">';
		$this->form_get_gid_select(__('Parent group:'), 'pgid', $info['pgid']);
		echo '</div>';

		echo '<div class="form-field" style="float: none; text-align: center">';
		echo "<button type='button' onclick='edit_grp_submit(\"$form_id\")'>" . __('submit') . '</button>';
		echo '</div>';

		form_get_hidden('ajax_mode', 1);

		echo '</form>';
		echo '</div>';
	}

	private function do_edit($gid)
	{
		if ($gid)
		{
			if (empty($_POST['edit_verify']) || $_POST['edit_verify'] != session_get('edit_verify'))
				return;
		}
		else if (empty($_POST['add_verify']) || $_POST['add_verify'] != session_get('add_verify'))
			return;

		$info = array();
		foreach (array('name', 'desc', 'pgid') as $f)
		{
			if (!isset($_POST[$f]))
				throw new Exc_runtime(__('incomplete post: field %s not found', $f));
			$info[$f] = $_POST[$f];
		}

		$this->validate_info($info);
		if ($gid)
		{
			$orig_info = $this->get_info($gid);
			if ($orig_info['pgid'] != $info['pgid'])
				$this->do_move(array($gid), $info['pgid']);
			else
				$this->update_db($gid, $info);
		}
		else 
			$this->new_grp($info);

		session_set('edit_verify', NULL);
		session_set('add_verify', NULL);
	}

	private function do_del($grps)
	{
		foreach ($grps as $g)
			$this->del_grp($g);
	}

	private function do_move($grps, $tgid)
	{
		try
		{
			$info = $this->get_info($tgid);
		}
		catch (Exc_orzoj $e)
		{
			throw new Exc_runtime(__('target group #%d does not exist', $tgid));
		}
		if (in_array(intval($tgid), $grps))
			throw new Exc_runtime(__('sorry, I can not change the parent of group #%d("%s") to itself...',
				$info['id'], $info['name']));
		foreach ($grps as $g)
		{
			$info = $this->get_info($g);
			if ($info['pgid'] != $tgid)
			{
				unset($info['name']);
				unset($info['desc']);
				$info['pgid'] = $tgid;
				global $db;
				$db->transaction_begin();
				$this->update_db($g, $info);
				$tmp = $this->get_all_children($g);
				foreach ($tmp as $g)
				{
					$this->update_cache_delete($g);
					$this->update_cache_add($g);
				}
				$db->transaction_commit();
			}
		}
	}
}

