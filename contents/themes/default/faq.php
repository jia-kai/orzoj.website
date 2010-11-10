<?php
/*
 * $File: faq.php
 * $Date: Wed Nov 10 18:39:20 2010 +0800
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
function make_faq($Q, $A)
{
	echo '<li>';
	echo '<div class="faq">';
	echo '<div class="faq-q"><span class="faq-q">' . __('Question: ') . '</span>' . $Q . '</div>';
	echo '<div class="faq-a"><span class="faq-a">' . __('Answer: ') . '</span>' . $A . '</div>';
	echo '</div>';
	echo '</li>';
}

$faq = array(
	__('How do I know when to use a file as input and output and when not?') => __('Please take a careful look at the problem description, it will show you what to do.'),
	__('Why should I share my code with others, for in many other Online Judges, they do not suppose to do so?') => __('We think the "Open Source" idea should spread to everyone here. Your code will give your friends some help, and you can get new thoughts via reading others code. Many great works are achieved by opening source to people all over the world, and they helped improve it(e.g Linux). We hope everyone here share your code to others, and that will make a great learning environment.'),
	__('I\'m not going to share my code, how can I close this function?') => __('We are so sorry to hear that, but we respect your decision. You can determine users in some certain groups to see your code, and you can just select `Nobody`.'),
	__('If I chose to share my code to everybody, what if they see my code during the contest?') => __('Don\'t worry, the visibility of code in the contest is determined by contest rules. In most type of the contest(like OI, ACM, etc.), the contest rule will forbid others from seeing your code. When the contest is finished, your code in the contest will be visible to others again.'),
);
$cnt = 0;
echo '<ol>';
foreach ($faq as $Q => $A)
{
	if ($cnt ++)
		echo '<hr />';
	make_faq($Q, $A);
}
echo '</ol>';
?>
