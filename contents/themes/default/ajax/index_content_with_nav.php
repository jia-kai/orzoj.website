<?php
/*
 * $File: index_content_with_nav.php
 * $Date: Sat Nov 06 17:08:44 2010 +0800
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


cookie_set('last_page', serialize(array($cur_page, $page_arg)));

echo '
<div class="navigator">
	<div id="navigator">
';

foreach ($PAGES as $name => $value)
{
	if (isset($value['hidden'])) continue;
	if ($name == $cur_page)
		echo '<a href="#" id="nav_disable">';
	else
	{
		if (isset($value['sys_pages_id']))
		{
			if ($value['sys_pages_id'] == $page_arg)
			{
				echo '<a href="#" id="nav_disable">',$value[0],'</a>';
				continue;
			}
			$addr = t_get_link('pages',$value['sys_pages_id'],TRUE,TRUE);
		}
		else
			$addr = t_get_link($name, NULL, TRUE, TRUE);
		echo "<a href=\"$addr\" onclick=\"index_navigate('$addr'); return false;\">";
	}
	echo "$value[0]</a>\n";
}

?>
	</div>
</div>

<img src="<?php _url('images/bg_cornerul.jpg');?>" alt="corner" class="bgcornerl" />
<img src="<?php _url('images/empty.gif');?>" alt="top" class="bgtop" />
<img src="<?php _url('images/bg_cornerur.jpg');?>" alt="corner" class="bgcornerr" />

<div id="content">
	<div id="content-opacity">
	<?php require_once($theme_path . $PAGES[$cur_page][1]); ?>
	</div>
</div>

<img src="<?php _url('images/bg_cornerdl.jpg');?>" alt="corner" class="bgcornerl" />
<img src="<?php _url('images/empty.gif');?>" alt="top" class="bgbottom" />
<img src="<?php _url('images/bg_cornerdr.jpg');?>" alt="corner" class="bgcornerr" />

<?php t_get_footer(); ?>

<script type="text/javascript">
	$("#navigator").buttonset();
	var t=$("#nav_disable");
	t.button("disable");
	t.addClass("ui-state-active");
	t.removeClass("ui-button-disabled");
	t.removeClass("ui-state-disabled");
</script>

