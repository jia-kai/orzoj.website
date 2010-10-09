<?php
/*
 * $File: index.php
 * $Date: Sat Oct 09 22:05:17 2010 +0800
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
 * @ignore
 */
function _url($file)
{
	global $theme_path;
	echo get_page_url($theme_path . $file);
}

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

if (!isset($cur_page) || !isset($PAGES[$cur_page]))
	die('unknown page');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php t_get_html_head(); ?>
	<title>Orz Online Judge</title>
	<link rel="stylesheet" type="text/css" href="<?php _url('style.css'); ?>" />
	<link rel="icon" type="image/vnd.microsoft.icon" href="<? _url('images/favicon.ico'); ?>" />
	<link rel="icon" type="image/jpeg" href="<?php _url('images/favicon.jpg'); ?>" />

	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="expires" content="Wed, 23 Aug 2006 12:40:27 UTC" />

	<link href="<?php _url('scripts/jquery/ui-css/ui.custom.css'); ?>" rel="stylesheet" type="text/css" />
	<link href="<?php _url('scripts/jquery/fancybox/jquery.fancybox-1.3.1.css'); ?>" rel="stylesheet" type="text/css" />

	<script type="text/javascript" src="<?php _url('scripts/jquery/jquery.js');?>"></script>
	<script type="text/javascript" src="<?php _url('scripts/jquery/ui.js');?>"></script>
	<script type="text/javascript" src="<?php _url('scripts/jquery/fancybox/jquery.mousewheel-3.0.2.pack.js');?>"></script>
	<script type="text/javascript" src="<?php _url('scripts/jquery/fancybox/jquery.fancybox-1.3.1.pack.js');?>"></script>

	
	<script type="text/javascript">
		$(document).ready(function(){
			var h = $("#content").height() + 10;
			$("img.bgleft").height(h);
			$("img.bgright").height(h);
			$("#navigator").buttonset();
			$("button").button();
			$("#user-register").fancybox();
		});
	</script>

</head>
<body>

	<div style="margin:10px;"></div>
	<div id="page">
		<div id="header">
			<img src="<?php _url('images/banner.gif');?>" class="banner" alt="banner" />
			<div class="banner-right">
				<form action="?login" id="login-form" method="POST">
					<table class="in-form" border="0">
						<?php user_check_login_get_form(); ?>
					</table>
					<a href="<?php _url('user_register.php'); ?>" id="user-register">
						<button type="button" class="in-form" ><?php echo __('Register'); ?></button>
					</a>
					<button type="submit" class="in-form" ><?php echo __('Login'); ?></button>
				</form>
			</div>
		</div>

		<div class="clearer"></div>

		<div class="navigator">
			<div id="navigator">
			<form action="#">
<?php

foreach ($PAGES as $name => $value)
{
	$id = "nav_$name";
	echo "<input type=\"radio\" name=\"navigator\" class=\"navigator\" id=\"$id\" ";
	if ($name == $cur_page)
		echo ' checked="checked" ';
	echo "/><label for=\"$id\" class=\"navigator\">$value[0]</label>\n";
}

?>
			</form>
			</div>
		</div>
		<div class="clearer"></div>

		<img src="<?php _url('images/bg_cornerul.jpg');?>" alt="corner" class="bgcornerl" />
		<img src="<?php _url('images/empty.gif');?>" alt="top" class="bgtop" />
		<img src="<?php _url('images/bg_cornerur.jpg');?>" alt="corner" class="bgcornerr" />
		<div class="clearer"></div>

		<div  class="bgleft" ></div>
		<img src="<?php _url('images/empty.gif'); ?>" alt="left" class="bgleft" />
		<div id="content">
			dfjioasjfiojsd iojdsof joasidj osdafjo fjsoda
			<br />
			<br />
			<br />
			<br />
			<br />
			<br />
			<br />
			<br />
		</div>
		<img src="<?php _url('images/empty.gif'); ?>" alt="right" class="bgright" />
	
		<div class="clearer"></div>
		<img src="<?php _url('images/bg_cornerdl.jpg');?>" alt="corner" class="bgcornerl" />
		<img src="<?php _url('images/empty.gif');?>" alt="top" class="bgbottom" />
		<img src="<?php _url('images/bg_cornerdr.jpg');?>" alt="corner" class="bgcornerr" />

		<?php t_get_footer(); ?>
	</div> <!-- id: page -->

</body>
</html>

