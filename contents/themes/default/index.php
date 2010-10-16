<?php
/*
 * $File: index.php
 * $Date: Sat Oct 16 18:44:55 2010 +0800
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
function _url($file, $return_str = FALSE)
{
	global $theme_path;
	if ($return_str)
		return get_page_url($theme_path . $file);
	echo get_page_url($theme_path . $file);
}

/*
 * pages for user accessing
 */
$PAGES = array(
	// <page name> => array(<display name>, <file>)
	'home' => array(__('Home'), 'home.php'),
	'problem' => array(__('Problems'), 'problem.php'),
	'status' => array(__('Status'), 'status.php'),
	'rank' => array(__('Rank'), 'rank.php'),
	'contest' => array(__('Contest'), 'contest.php'),
	'discuss' => array(__('Discuss'), 'discuss.php'),
	'team' => array(__('User Teams'), 'team.php'),
	'judge' => array(__('Judges'), 'judge.php'),
	'faq' => array(__('FAQ'), 'faq.php')
);

if (isset($_POST['index_navigate_ajax']))
{
	if (!isset($PAGES[$cur_page]))
		die('nothing what you are looking for');
	require_once $theme_path . 'ajax/index_content_with_nav.php';
	die;
}

/*
 * pages for AJAX
 */
$PAGES_AJAX = array(
	// <page name> => <file>
	'ajax-register' => 'ajax/register.php',
	'ajax-form-checker' => 'ajax/form_checker.php',
	'ajax-avatar-browser' => 'ajax/avatar_browser.php',
	'ajax-record-detail' => 'ajax/record_detail.php',
	'ajax-prob-group-tree-ask' => 'ajax/prob_group_tree_ask.php',
	'ajax-prob-view-by-group' => 'ajax/prob_view_by_group.php',
	'ajax-prob-view-single' => 'ajax/prob_view_single.php',
	'ajax-prob-submit' => 'ajax/prob_submit.php',
	'ajax-prob-best-solutions' => 'ajax/prob_best_solutions.php',
	'ajax-status-list' => 'ajax/status_list.php'
);

/**
 * pages for an action
 */
$PAGES_ACTION = array(
	// <page name> => <callback function>
	// return value of <callback function>:
	//  NULL|string string if some message needed to be told to the user
	'action-login' => '_action_login',
	'action-logout' => '_action_logout'
);

if ($cur_page == 'index')
	$cur_page = 'home';

if (substr($cur_page, 0, 10) == 'show-ajax-')
{
	$show_ajax = TRUE;
	$cur_page = substr($cur_page, 5);
}

if (!isset($PAGES[$cur_page]) && !isset($PAGES_AJAX[$cur_page]) &&
	!isset($PAGES_ACTION[$cur_page]))
	die("unknown page: $cur_page");

if (isset($PAGES_AJAX[$cur_page]) && !isset($show_ajax))
{
	require_once $PAGES_AJAX[$cur_page];
	exit;
}

if (isset($PAGES[$cur_page]))
	cookie_set('last_page', serialize(array($cur_page, $page_arg)));

/*
 * @ignore
 */
function _restore_page()
{
	global $cur_page, $page_arg, $PAGES;
	$page_arg = NULL;
	$cur_page = cookie_get('last_page');
	if (is_null($cur_page))
		$cur_page = 'home';
	else list($cur_page, $page_arg) = unserialize($cur_page);

	if (!is_string($cur_page) || !isset($PAGES[$cur_page]))
		die("unknown page: $cur_page");
}

/**
 * @ignore
 */
function _action_login()
{
	_restore_page();
	try
	{
		if (!user_check_login())
			return __('Failed to login');
	}
	catch (Exc_orzoj $e)
	{
		return __('Failed to login: ') . $e->msg();
	}
}

/**
 * @ignore
 */
function _action_logout()
{
	_restore_page();
	try
	{
		user_logout();
	}
	catch (Exc_orzoj $e)
	{
		return __('Error while logging out: ') . $e->msg();
	}
}

if (isset($PAGES_ACTION[$cur_page]))
{
	$msg = $PAGES_ACTION[$cur_page]();
	if (is_string($msg))
		$startup_msg = htmlencode($msg);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php t_get_html_head(); ?>
	<title><?php echo $website_name; ?></title>
	<link rel="icon" type="image/vnd.microsoft.icon" href="<? _url('images/favicon.ico'); ?>" />
	<link rel="icon" type="image/jpeg" href="<?php _url('images/favicon.jpg'); ?>" />
	<meta http-equiv="pragma" content="no-cache" />

	<link href="<?php _url('scripts/jquery/ui-css/ui.custom.css'); ?>" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="<?php _url('scripts/jquery/jquery.js');?>"></script>
	<script type="text/javascript" src="<?php _url('scripts/jquery/ui.js');?>"></script>

	<link href="<?php _url('scripts/jquery/colorbox/colorbox.css'); ?>" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="<?php _url('scripts/jquery/colorbox/colorbox-min.js');?>"></script>

	<script type="text/javascript" src="<?php _url("scripts/jquery/jstree/jquery.jstree.js"); ?>"></script>
	<script type="text/javascript" src="<?php _url("scripts/jquery/jquery.cookie.js"); ?>"></script>

	<script type="text/javascript" src="<?php _url("scripts/json2.js"); ?>"></script>

	<link rel="stylesheet" type="text/css" href="<?php _url('style.css'); ?>" />

	<script type="text/javascript">
		function table_set_double_bgcolor()
		{
			$(".orzoj-table tr:odd").addClass("table-bgcolor1");
			$(".orzoj-table tr:even").addClass("table-bgcolor2");
		}
		$(document).ready(function(){
			$("button").button();

			<?php
if (!user_check_login())
	echo '$("#user-register").colorbox({"escKey": false, "arrowKey": false});';
if (isset($startup_msg))
{
	$OK = __("OK");
	echo <<<EOF
$("#dialog-startup-msg").dialog({
			"modal": true,
			"buttons": {
				"$OK": function() {
					$(this).dialog("close");
				}
			},
			"show": "scale",
			"hide": "scale"
		});
EOF;
}
?>
		});
	</script>

</head>
<body>

	<div id="page">
		<div id="banner">
			<img src="<?php _url('images/banner.gif');?>" class="banner" alt="banner" />
			<div id="banner-right">
				<div id="user-info">
<?php
if (!user_check_login())
{
?>
					<form action="<?php t_get_link('action-login') ?>" method="post">
						<?php _tf_form_generate_body('user_check_login_get_form'); ?>
						<div style="float: right;">
						<button type="submit" class="in-form" ><?php echo __('Login'); ?></button>
						<a href="<?php t_get_link('ajax-register'); ?>"
						id="user-register"><button type="button" class="in-form" ><?php echo __('Register'); ?></button></a>
						</div>
					</form>
<?php
}
else
{
	$p = __('AC/Submission: %d/%d', $user->cnt_ac, $user->cnt_submit);
	echo <<<EOF
<div style="float: left">
<img src="$user->avatar" alt="avatar" style="max-height: 128px; max-width: 128px;" />
</div>
<div style="float: left; margin: 5px">
$user->username <br />
$p <br />
EOF;
	echo '<a href="';
	t_get_link('user-info');
	echo '">&lt;' . __('User info.') . '&gt;</a> <a href="';
	t_get_link('action-logout');
	echo '">&lt;' . __('Logout') . '&gt;</a></div>';
}
?>
				</div> <!-- id: user-info -->
			</div> <!-- id: banner-right -->
		</div> <!-- id: banner -->

		<div id="content-with-nav">
<?php
if (isset($show_ajax))
{
	require_once $theme_path . $PAGES_AJAX[$cur_page];
	echo '<div id="show-ajax-info-div">';
	echo __('This is an AJAX page. Please visit %s to find the complete page.',
		"<a href=\"$website_root\">$website_name</a>");
	echo '</div>';
}
else
	require_once $theme_path . 'ajax/index_content_with_nav.php'; 
?>
		</div>
	</div> <!-- id: page -->

	<div id="avatar-browser">
	</div>
	<div id="avatar-browser-overlay">
	</div>

<?php
if (isset($startup_msg))
{
	$title = __('Message');
	echo " <div id=\"dialog-startup-msg\" title=\"$title\">$startup_msg
		</div>";
}
?>

	<script type="text/javascript">

		function form_checker(checker_id, input_id, result_div_id)
		{
			input_id = "#" + input_id;
			result_div_id = '#' + result_div_id;
			$(result_div_id).css("display", "inline");
			$.colorbox.resize();
			$(result_div_id).html('<img alt="loading" src="<?php _url('images/loading.gif');?>" />');
			$.ajax({
				"type": "post",
				"cache": false,
				"url":  "<?php t_get_link('ajax-form-checker');?>",
				"data": ({"checker" : checker_id, "val" : $(input_id).val()}),
				"success": function(data)
				{
					if (!data.length)
						$(result_div_id).css("display", "none");
					var ele = document.activeElement;
					$(result_div_id).html(data);
					$.colorbox.resize();
					ele.focus();
				}
			});
		}

		function form_verify_passwd(pwd1_id, pwd2_id, result_div_id)
		{
			result_div_id = '#' + result_div_id;
			if ($("#" + pwd1_id).val() != $("#" + pwd2_id).val())
			{
				$(result_div_id).css("display", "inline");
				$(result_div_id).html("<?php echo __('Passwords do not match');?>");
			} else
				$(result_div_id).css("display", "none");
			$.colorbox.resize();
		}

		function resize_avatar_browser(size, animate)
		{
			var ol = $("#avatar-browser-overlay");
			ol.width($(document).width());
			ol.height($(document).height());
			ol = $("#avatar-browser");
			var ww = $(window).width();
			var wh = $(window).height();
			if (size == -1)
			{
				var ct = $("#avatar-browser-container");
				w = ct.width();
				if (w < 400)
					ct.width(400); // can not IE see min-width in CSS ?!
				w = ct.width();
				h = ct.height();
			} else
			{
				var w = size;
				var h = size;
			}
			if (w > ww - 100)
				w = ww - 100;
			if (h > wh - 100)
				h = wh - 100;
			if (animate)
				ol.animate({"width": w, "height": h,
					"left": (ww - w) / 2, "top": (wh - h) / 2}, "slow");
			else
			{
				ol.offset({
					"left": (ww - w) / 2,
					"top": (wh - h) / 2});
				ol.width(w);
				ol.height(h);
			}
		}

		var resize_avatar_browser_handler = function(){resize_avatar_browser(-1, true);};
		var avatar_browser_init_done = false;

		function avatar_browser(input_id, img_id, pgnum)
		{
			var ol = $("#avatar-browser");
			if (!avatar_browser_init_done)
			{
				$("#avatar-browser-overlay").css("display", "inline");
				ol.css("display", "inline");
				$(document).resize(resize_avatar_browser_handler);
				$(window).resize(resize_avatar_browser_handler);
				avatar_browser_init_done = true;
			}
			resize_avatar_browser(16, false);
			ol.css("background-color", "transparent");
			ol.css("border-style", "none");
			ol.html('<img src="<?php _url('images/loading.gif');?>" alt="loading" />');

			$.ajax({
				"type": "post",
				"cache": false,
				"url":  "<?php t_get_link('ajax-avatar-browser');?>",
				"data": ({"input_id": input_id, "img_id" : img_id, "pgnum" : pgnum}),
				"success": function(data)
				{
					ol.html(data);
					ol.css("background-color", "white");
					ol.css("border-style", "solid");
					resize_avatar_browser(-1, true);
				}
			});
		}

		function avatar_set(img_id, file, input_id, aid)
		{
			$("#" + img_id).attr("src", file);
			$("#" + input_id).val(aid);
			$("#avatar-browser").css("display", "none");
			$("#avatar-browser-overlay").css("display", "none");
			$(document).unbind("resize", resize_avatar_browser_handler);
			$(window).unbind("resize", resize_avatar_browser_handler);
			avatar_browser_init_done = false;
		}

		function load_js_css_file(filename, filetype)
		{
			if (filetype == "js")
			{
				var fileref = document.createElement('script');
				fileref.setAttribute("type","text/javascript");
				fileref.setAttribute("src", filename);
			}
			else if (filetype == "css")
			{
				var fileref = document.createElement("link");
				fileref.setAttribute("rel", "stylesheet");
				fileref.setAttribute("type", "text/css");
				fileref.setAttribute("href", filename);
			}
			if (typeof(fileref) != "undefined")
				document.getElementsByTagName("head")[0].appendChild(fileref);
		}

		function index_navigate(addr)
		{
			$("#content-opacity").animate({"opacity": 0.5}, 1.2);
			$.ajax({
				"type": "post",
				"cache": false,
				"url": addr,
				"data": ({"index_navigate_ajax": "1"}),
				"success": function(data) {
					$("#content-with-nav").html(data);
					$("#content-opacity").animate({"opacity": 1}, 1.2);
				}
			});
		}


	</script>

</body>

</html>
