<?php
/*
 * $File: index.php
 * $Date: Fri Jan 06 14:16:01 2012 +0800
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

header("Content-Type: text/html; charset=utf-8");

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

if (is_null($cur_page))
	$cur_page = 'home';

/*
 * pages for AJAX
 */
$PAGES_AJAX = array(
	// <page name> => <file>
	'ajax-user-register' => 'ajax/user_register.php',
	'ajax-user-update-info' => 'ajax/user_update_info.php',
	'ajax-user-chpasswd' => 'ajax/user_chpasswd.php',
	'ajax-user-info' => 'ajax/user_info.php',
	'ajax-form-checker' => 'ajax/form_checker.php',
	'ajax-avatar-browser' => 'ajax/avatar_browser.php',
	'ajax-gid-selector' => 'ajax/gid_selector.php',
	'ajax-record-detail' => 'ajax/record_detail.php',
	'ajax-prob-group-tree-ask' => 'ajax/prob_group_tree_ask.php',
	'ajax-prob-view-by-group' => 'ajax/prob_view_by_group.php',
	'ajax-prob-view-single' => 'ajax/prob_view_single.php',
	'ajax-prob-submit' => 'ajax/prob_submit.php',
	'ajax-prob-best-solutions' => 'ajax/prob_best_solutions.php',
	'ajax-status-list' => 'ajax/status_list.php',
	'ajax-rank-list' => 'ajax/rank_list.php',
	'ajax-contest-list' => 'ajax/contest_list.php',
	'ajax-contest-view' => 'ajax/contest_view.php',
	'ajax-contest-view-prob' => 'ajax/contest_view_prob.php',
	'ajax-contest-view-result' => 'ajax/contest_view_result.php',
	'ajax-contest-submit' => 'ajax/contest_submit.php',
	'ajax-post-list' => 'ajax/post_list.php',
	'ajax-post-view-single' => 'ajax/post_view_single.php',
	'ajax-post-new-topic' => 'ajax/post_new_topic.php',
	'ajax-post-manipulate' => 'ajax/post_manipulate.php',
	'ajax-judge' => 'ajax/judge.php'
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

if (isset($PAGES_ACTION[$cur_page]))
{
	$msg = $PAGES_ACTION[$cur_page]();
	if (is_string($msg))
		$startup_msg = htmlencode($msg);
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
	'judge' => array(__('Judges'), 'judge.php'),
	'pages' => array(__('Pages'),'pages.php','hidden' => TRUE),
	'faq' => array(__('FAQ'), 'faq.php')
);

$SYS_PAGES = pages_get_list();

foreach ($SYS_PAGES as $v)
{
	$PAGES['pages_'.$v->id] = array($v->title,'pages.php','sys_pages_id' => $v->id);
}

if (isset($PAGES_ACTION[$cur_page]))
	_restore_page();

if (isset($_POST['index_navigate_ajax']))
{
	if (!isset($PAGES[$cur_page]))
	{
		set_error(404,'Not Found');
		die('nothing what you are looking for');
	}
	require_once $theme_path . 'ajax/index_content_with_nav.php';
	die;
}

if (substr($cur_page, 0, 10) == 'show-ajax-')
{
	$show_ajax = TRUE;
	$cur_page = substr($cur_page, 5);
}

if (!isset($PAGES[$cur_page]) && !isset($PAGES_AJAX[$cur_page]) &&
	!isset($PAGES_ACTION[$cur_page]))
{
	set_error(404,'Not Found');
	die("unknown page: $cur_page");
}

if (isset($PAGES_AJAX[$cur_page]) && !isset($show_ajax))
{
	require_once $PAGES_AJAX[$cur_page];
	exit;
}

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
	{
		set_error(404,'Not Found');
		die("unknown page: $cur_page");
	}
}

/**
 * @ignore
 */
function _action_login()
{
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
	global $page_arg;
	try
	{
		user_logout($page_arg);
	}
	catch (Exc_orzoj $e)
	{
		return __('Error while logging out: ') . $e->msg();
	}
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

	<script type="text/javascript" src="<?php echo get_page_url($root_path . 'contents/editors/ckeditor/ckeditor.js');?>"></script>

	<link rel="stylesheet" type="text/css" href="<?php _url('style.css'); ?>" />

	<script type="text/javascript">

		function table_set_double_bgcolor()
		{
			$(".page-table tr:odd").addClass("page-table-bgcolor1");
			$(".page-table tr:even").addClass("page-table-bgcolor2");
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
						<div style="float: right;">
						<?php _tf_form_generate_body('user_check_login_get_form'); ?>
						<button type="submit" class="in-form" ><?php echo __('Login'); ?></button>
						<a href="<?php t_get_link('ajax-user-register'); ?>"
						id="user-register"><button type="button" class="in-form" ><?php
							echo __('Register'); ?></button></a>
						</div>
					</form>
<?php
}
else
{
	$hello = __('Hello, %s!', $user->nickname);
	$sts = &$user->get_statistics();
	$p = __('Solved prob.: %d Submitted prob.: %d <br />AC Ratio: %.2f%%',
		$sts['cnt_ac_prob'], $sts['cnt_submitted_prob'], $sts['ac_ratio'] * 100);
	echo <<<EOF
<div style="float: left">
<img src="$user->avatar" alt="avatar" style="max-height: 128px; max-width: 128px;" />
</div>
<div style="float: left; margin: 5px">
$hello<br />
$p <br />
<div id="user-options">
EOF;
	$options = array(
		// <text> => array(<page name>, <page arg>, <whether to use colorbox>)
		__('User info.') => array('ajax-user-info', NULL, TRUE),
		__('Update profile') => array('ajax-user-update-info', NULL, TRUE),
		__('Change password') => array('ajax-user-chpasswd', NULL, TRUE),
		__('Logout') => array('action-logout', user_logout_get_code(), FALSE)
	);
	echo '<ul>';
	foreach ($options as $opt => $val)
	{
		echo '<li>';
		echo '<a href="';
		t_get_link($val[0], $val[1]);
		if ($val[2])
			echo '" class="index-need-colorbox';
		echo "\">&lt;$opt&gt;</a><br />\n";
		echo '</li>';
	}
	echo '</ul>';
	echo '
		</div> <!-- id: user-options -->
		</div>';
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

	<div id="gid-selector-box" title="<?php echo __('User group selector');?>" >
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
		
		$(".index-need-colorbox").colorbox();

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
			} else if (!$("#" + pwd1_id).val().length)
			{
				$(result_div_id).css("display", "inline");
				$(result_div_id).html("<?php echo __('You must set a password');?>");
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
				w = ct.width() + 100;
				if (w < 500)
					ct.width(400); // can not IE see min-width in CSS ?!
				w = ct.width() + 100;
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
			avatar_browser_close();
		}

		function avatar_browser_close()
		{
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

		function gid_selector(input_id)
		{
			$.ajax({
				"type": "post",
				"cache": false,
				"url": "<?php t_get_link('ajax-gid-selector');?>",
				"data": ({"input_id": input_id, "cur_val": $("#" + input_id).val()}),
				"success": function(data){
					var d = $("#gid-selector-box");
					d.show();
					d.html(data);
					d.dialog({
						"modal": true,
						"resizable": false,
						"minWidth": 600,
						"buttons": {
							"<?php echo __('ADD');?> ->" : function() {
								gid_selector_add();
							},
							"<?php echo __('REMOVE');?>" : function() {
								gid_selector_remove();
							},
							"<?php echo __('CANCEL');?>": function() {
								$(this).dialog("close");
							},
							"<?php echo __('OK');?>": function() {
								gid_selector_ok();
								$(this).dialog("close");
							}
						},
						"show": "fade",
						"hide": "fade"
					});
				}
			});
		}

	</script>
<?php
$is_old_browser = (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6') !== false) || (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 5') !== false);
if ($is_old_browser || $userbrowser == USER_BROWSER_MSIE)
{
?>
<div class="oldbrowser">
<div class="oldbrowser-content" style="text-align: center;">
<?php
	if ($is_old_browser)
	{
		echo __('Your browser is <b>OUT OF DATE</b>.Please upgrade your browser.') . '<br />';
		echo __('If you insist on using your <b>OUT OF DATE</b> browser, you will not get the best browsing experience.') . '<br />';
		echo __('As follows are some <b>UP-TO-DATE</b> browsers(In alphabet order).') . '<br />';
	}
	else
	{
		echo __('We do not suggest using <b>Microsoft Internet Explorer</b> as it doesn\'t work well in some places') . '<br />';
		echo __('As follows are some other browsers.') . '<br />';
	}
	function _new_browser_a($brow,$downurl)
	{
		echo '<a href="'.$downurl.'" alt="" target="_blank" title="'.$brow.'"><img src="'._url('images/newbrowsers/'.$brow.'.gif',TRUE),'" style="float: none"/></a>';
	}
	echo '<div class="oldbrowser-brow" style="margin-left: auto; margin-right: auto;">';
	_new_browser_a('chrome','http://www.google.com/chrome/');
	_new_browser_a('firefox','http://www.mozilla.com/firefox/');
	_new_browser_a('opera','http://www.opera.com/browser/download/');
	_new_browser_a('safari','http://www.apple.com/safari/');
	echo '</div>';
	if ($is_old_browser)
	{
		echo __('We do not suggest using <b>Microsoft Internet Explorer</b> as it <b>DOES NOT WORK WELL</b> in many places') . '<br />';
		_new_browser_a('ie8','http://go.microsoft.com/fwlink/?LinkId=161354');
	}

?>
</div>
<div class="oldbrowser-title">
<?php echo __('IMPORTANT NOTICE')?>
</div>
</div>
<script type="text/javascript">
	$("div.oldbrowser-title").click(function(){
		$("div.oldbrowser-content").slideToggle("slow");
	});
</script>

<?php
}
?>
</body>

</html>


