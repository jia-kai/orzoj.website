<?php
/*
 * $File: gid_selector.php
 * $Date: Wed Oct 20 08:31:08 2010 +0800
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
 * page argument: none
 * POST:
 *		input_id, cur_val:
 *			see index.php
 *		pgid: if this argument is set, return the children in json data for jstree
 *			pgid == 0 means all
 *		gid: if this argument is set, return the description for this group
 */

if (isset($_POST['pgid']))
{
	$pgid = intval($_POST['pgid']);
	$ret = array();
	$rows = $db->select_from('user_grps', array('id', 'name'),
		array($DBOP['='], 'pgid', $pgid));
	foreach ($rows as $row)
	{
		$tmp = array(
			'data' => array(
				'title' => $row['name'],
				'attr' => array(
					'onclick' => 'show_desc(\'' . $row['id'] . '\')'
				)
			),
			'attr' => array(
				'id' => $row['id'],
				'name' => $row['name']
			)
		);
		$nchd = $db->get_number_of_rows('user_grps',
			array($DBOP['='], 'pgid', $row['id']));
		if ($nchd)
			$tmp['state'] = 'closed';
		$ret[] = $tmp;
	}
	die(json_encode($ret));
}

if (isset($_POST['gid']))
{
	$row = $db->select_from('user_grps', 'desc', array(
		$DBOP['='], 'id', $_POST['gid']));
	if (count($row) != 1)
		die('no such group');
	die($row[0]['desc']);
}

if (!isset($_POST['input_id']) || !isset($_POST['cur_val']))
	die('incomplete post');

?>

<div style="clear: both">
<div id="gid-treeview"></div>
<select id="gid-selector-select" multiple="multiple">
<?php
$init = explode(',', $_POST['cur_val']);
if (is_array($init))
	foreach ($init as $val)
		if (strlen($val))
			echo "<option>$val</option>\n";
?>
</select>
</div>
<div id="gid-selector-grp-desc">
</div>

<script type="text/javascript">
$("#gid-treeview").jstree({
	"plugins" : [ "themes", "json_data", "ui"],
	"themes" : {
		"theme" : "default",
		"dots" : false,
		"icons" : false
	},
	"json_data" : {
		"ajax" : {
			"url" : "<?php t_get_link('ajax-gid-selector'); ?>" ,
			'type': 'post',
			'cache': false,
			"data" : function (node) {
				return {
					"pgid" : (node.attr ? node.attr("id") : 0)
				};
			}
		}
	}
});

function show_desc(gid)
{
	$.ajax({
		"type": "post",
		"cache": "false",
		"url": "<?php t_get_link('ajax-gid-selector')?>",
		"data": ({"gid": gid}),
		"success": function(data){
			$("#gid-selector-grp-desc").html(data);
		}
	});
}

function gid_selector_add()
{
	var treeview = $("#gid-treeview");
	var node = treeview.jstree('get_selected');
	if (!node.length)
	{
		alert("<?php echo __('Please select a group first');?>");
		return false;
	}
	for (var i = 0; i < node.length; i ++)
	{
		var ok = true, name = $(node[i]).attr("name"),
			now = $("#gid-selector-select option");
		for (var j = 0; j < now.length; j ++)
			if ($(now[j]).html() == name)
			{
				ok = false;
				break;
			}
		if (ok)
			$("#gid-selector-select").append("<option>" + name
			+ "</option>");
	}
}

function gid_selector_remove()
{
	$("#gid-selector-select :selected").remove();
}

function gid_selector_ok()
{
	var str = "";
	$.each($("#gid-selector-select option"), function(key, val){
		str = str + $(val).html() + ",";
	});
	$("#<?php echo $_POST['input_id']?>").val(str.substr(0, str.length - 1));
}

</script>

