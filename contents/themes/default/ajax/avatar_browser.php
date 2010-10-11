<?php
/*
 * $File: avatar_browser.php
 * $Date: Mon Oct 11 15:43:09 2010 +0800
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

define('PAGE_SIZE', 16);

if (!isset($_POST['input_id']) || !isset($_POST['pgnum']) || !isset($_POST['img_id']))
{
	echo htmlencode(print_r($_POST, TRUE));
	die('I am dying');
}

$input_id = $_POST['input_id'];
$img_id = $_POST['img_id'];
$pgnum = intval($_POST['pgnum']);

$tot = avatar_get_amount();
$offset = PAGE_SIZE *  $pgnum;
if ($offset >= $tot)
	die('pgnum too large');

$cnt = PAGE_SIZE;
if ($cnt + $offset > $tot)
	$cnt = $tot - $offset;

$list = avatar_list($offset, $cnt);

?>

<div id="avatar-browser-container">

<div style="text-align: center"><?php echo __('Avatar browser'); ?></div>
<div class="clearer"></div>

<?php
foreach ($list as $val)
{
	$f = avatar_get_url_by_file($val['file']);
	$id = $val['id'];
	echo <<<EOF
<div class="avatar-img-outline">
<div class="avatar-img">
<a href="#" onclick="avatar_set('$img_id', '$f', '$input_id', '$id')">
<img src="$f" width="100%" height="100%" alt="img" />
</a></div></div>
EOF;
}

echo '
	<div class="clearer"></div>
	<div class="avatar-browser-nav">';

if ($offset)
{
	$p = __('&lt; Previous page');
	$t = $pgnum - 1;
	echo <<<EOF
<a href="#" onclick="avatar_browser('$input_id', '$img_id', '$t')">$p</a> | 
EOF;
}

$pgnum ++;
$page_cnt = (int)(($tot - 1) / PAGE_SIZE + 1);
echo __('Page %d of %d', $pgnum, $page_cnt);

if ($pgnum < $page_cnt)
{
	$p = __('Next page &gt;');
	echo <<<EOF
 | <a href="#" onclick="avatar_browser('$input_id', '$img_id', '$pgnum')">$p</a>
EOF;
}

echo '</div>';

?>

</div> <!-- id: avatar-browser-container-->
