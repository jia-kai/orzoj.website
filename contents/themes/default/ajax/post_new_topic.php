<?php
/*
 * $File: post_new_topic.php
 * $Date: Tue Nov 02 13:20:01 2010 +0800
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
 * page argumnet:
 *		'submit'
 *
 * POST:
 *		prob_code
 *		type
 *		subject
 *		content
 */

require_once $includes_path . 'post.php';

if ($page_arg == 'submit')
{
	try
	{
		die('1' . post_add_topic());
	}
	catch (Exc_orzoj $e)
	{
		echo '0';
		die('0' . $e->msg());
	}
}

$post_url = t_get_link('show-ajax-post-new-topic', NULL, TRUE, TRUE);
?>
<div id="post-new-topic-container">
<form id="post-new-topic-form" method="post" action="<?php echo $post_url; ?>">
	<table>
<?php post_add_topic_get_form(); ?>
	<tr><td></td><td colspan="2"><input id="post-new-topic-submit-button" type="submit" value="<?php echo __('Submit'); ?>" /></td></tr>
	</table>
</form>
<script type="text/javascript">
<?php
$Editor = "CKEDITOR.instances.$editor_id"; 
$url = t_get_link('ajax-post-new-topic', 'submit', FALSE, TRUE); 
?>
$("#post-new-topic-form").bind("submit", function(){
	var prob_code = $("#post-new-topic-form input[name='prob_code']").val();
	var type = $("#post-new-topic-form select[name='type']").val();
	var subject = $("#post-new-topic-form input[name='subject']").val();
	var content = <?php echo $Editor; ?>.getData();

	$.colorbox({"html" : "<?php echo __('Submitting...'); ?>"});
	var t = $("#post-page");
	t.animate({"opacity" : 0.5}, 1);
	$.ajax({
		"type" : "post",
		"cache" : false,
		"url" : "<?php echo $url; ?>",
		"data" : ({
			"prob_code" : prob_code,
			"type" : type,
			"subject" : subject,
			"content" : content
		}),
		"success" : function(data) {
			t.animate({"opacity" : 1}, 1);
			if (data.charAt(0) == '0')
			{
				$.colorbox({
					"title" : "<?php echo __('Oops!');?>",
					"html" : data.substr(1)
				});
			}
			else if (data.charAt(0) == '1')
			{
				tid = data.substr(1);
<?php
$post_list_args = array('start_page', 'uid', 'subject', 'author', 'type');
foreach ($post_list_args as $item)
	if (!isset($$item))
		$$item = NULL;
$data = array();
foreach ($post_list_args as $item)
	$data[] = '"' . $item . '" : "' . $$item. '" ';

$url = t_get_link('ajax-post-view-single', NULL, FALSE, TRUE);
?>
				$.ajax({
					"type" : "post",
					"cache" : false,
					"url" : "<?php echo $url; ?>",
					"data" : ({<?php echo implode(',', $data); ?>, "tid": tid}),
					"success" : function(data) {
						setTimeout('$.colorbox({"html" : "<?php echo __('Congruatulation! New topic has been successfully posted'); ?>"});', 1000);
						setTimeout("$.colorbox.close();", 2000);
						$("#posts-view").html(data);
					}
				})
			}
			else alert("????");
		}
	});
	return false;

});
</script>
</div><!-- id: post-new-topic-container -->

