<?php
/*
 * $File: home.php
 * $Date: Thu Nov 04 16:18:18 2010 +0800
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
?>
<div class="home-container">
<div class="home-title"><?php echo __('Welcome to <b>%s</b>!', $website_name);?></div>

<div class="home-content">
Orz Online Judge is on the way.<br />
This site is now temporary running for test, all data will not be reserved when it is to hold contests.<br />
If you find any bugs, or have some suggestions, PLEASE tell us via email:<br />
support@orzoj.org
to help improve this site. ALL KINDS OF SUGGESTIONS are WELCOME!<br />
THANKS FOR YOU HELP!<br />
</div>
<?php
$people = array('Ted', 'Theo', 'FYD',
	'Lonely King', '张超Q', 'Rayan',
   	'卡男', '囧', '工', '风哥');
function _get_cleaner()
{
	global $people;
	$last_time = option_get('last_time');
	$lastday = strftime('%d', $last_time);
	$time = time();
	option_set('last_time', $time);
	$today = strftime('%d', $time);
	if ($lastday != $today)
	{
		$cleaner = $people[rand(0, count($people) - 1)];
		option_set('cleaner', $cleaner);
	}
	else
		$cleaner = option_get('cleaner');
	echo $cleaner;
}
?>
Cleaner Today: <?php _get_cleaner();?>
</div>
