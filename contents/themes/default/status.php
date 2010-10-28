<?php
/*
 * $File: status.php
 * $Date: Wed Oct 27 22:55:57 2010 +0800
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
 * page argument: [<starting page num:int>]
 * POST: 
 * ['filter']
 *		'filter':
 *			array of used filters
 *			return a complete page
 */

require_once $includes_path . 'record.php';

?>

<script type="text/javascript">
var records = new Array();

function update_table()
{
	$.ajax({
		"type": "post",
		"cache": false,
		"url": "<?php t_get_link('ajax-status-list');?>",
		"data": ({"request": records}),
		"success": function(data) {
			if ($("#status-list-table").size() == 0 || !records.length)
			{
				records.length = 0;
				return;
			}
			JSON.parse(data, function(key, value){
				if (typeof(value) != "string")
					return;
				$("#status-tb-tr-" + key).html(value.substr(1));
				if (value.charAt(0) == '1')
				{
					for (var i = 0; i < records.length; i ++)
						if (records[i] == key)
						{
							records.splice(i, 1);
							break;
						}
					$("#status-tb-tr-" + key + " a.a-record-detail").colorbox({
						"title": "<?php echo __('Record detail');?>"
					});
				}
			});
			if (records.length)
				setTimeout("update_table()", 1000);
		}
	});
}

function start_update_table(rec)
{
	if (!records.length)
	{
		records = rec;
		update_table();
	} else
		records = rec;
}

</script>

<?php

echo '<div class="status-filter" style="margin-right: 10px; float: left;">';
echo __('Filter:');
echo '</div>';
echo '<div style="float:left;">';
echo '<form action="';
t_get_link($cur_page);
echo '" method="post" id="filter-form">';

function _make_input($prompt, $post_name)
{
	if (isset($_POST['filter'][$post_name]))
		$default = $_POST['filter'][$post_name];
	else $default = '';
	$id = _tf_get_random_id();
	echo <<<EOF
<div class="status-filter">
<label for="$id">$prompt</label>
</div>
<div class="status-filter">
<input type="text" name="filter[$post_name]" id="$id" value="$default" />
</div>
EOF;
}

function _make_select($prompt, $post_name, $options)
{
	if (isset($_POST['filter'][$post_name]))
		$default = $_POST['filter'][$post_name];
	else $default = '';
	$id = _tf_get_random_id();
	echo <<<EOF
<div class="status-filter">
<label for="$id">$prompt</label>
</div>
<div class="status-filter">
<select id="$id" name="filter[$post_name]">
EOF;

	asort($options);

	foreach ($options as $disp => $val)
	{
		echo "<option value=\"$val\"";
		if ((string)$val == $default)
			echo ' selected="selected"';
		echo ">$disp</option>";
	}
	echo '</select></div>';
}

function _make_checkbox($prompt, $post_name)
{
	if (isset($_POST['filter'][$post_name]))
		$default = ' checked="checked" ';
	else $default = '';
	$id = _tf_get_random_id();
	echo <<<EOF
<div class="status-filter">
<label for="$id">$prompt</label>
</div>
<div class="status-filter">
<input name="filter[$post_name]" value="1" type="checkbox" id="$id" $default />
</div>
EOF;
}

echo '<div style="float:left">';

_make_input(__('username'), 'username');
_make_input(__('problem code'), 'pcode');
_make_input(__('contest id'), 'cid');

$rows = $db->select_from('plang', array('id', 'name'));

$plang = array(__('ALL') => '');
foreach ($rows as $row)
	$plang[$row['name']] = $row['id'];

echo '</div><div style="clear:both; float:left;">';

_make_select(__('lang.'), 'lid', $plang);
_make_select(__('status'), 'status', array_merge(array(__('ALL') => ''),
	array_flip(record_status_get_all())));

_make_checkbox(__('Ranklist Mode'), 'ranklist');

$p = __('Apply');
echo "
	<div class=\"status-filter\">
	<input type=\"submit\" id=\"filter-apply-button\" value=\"$p\" />
	</div>";

echo '</div>';

echo '</form></div>';

?>

<div style="float:right">
<a title="<?php echo __('Refresh')?>">
	<img src="<?php _url('images/refresh.gif');?>" alt="&lt;refresh&gt;"
	onclick="status_goto_page();" 
	style="cursor: pointer;" />
</a>
</div>

<?php
require_once $theme_path . 'ajax/status_list.php';
?>

<script type="text/javascript">
$("#filter-apply-button").button();

$("#filter-form").bind("submit", function(){
	status_navigate_do("<?php t_get_link('ajax-status-list');?>", 
		$("#filter-form").serializeArray());
	return false;
});

</script>

