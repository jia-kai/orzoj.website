<?php
/*
 * $File: home.php
 * $Date: Wed Nov 10 10:55:11 2010 +0800
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

require_once $includes_path . 'contest/ctal.php';
require_once $includes_path . 'post.php';

$LINKS = array(
	'Jiakai的博客' => 'http://jiakai.blogoose.com/',
	'FQJ1994的博客' => 'http://www.fqj1994.org',
	'Tim的博客' => 'http://www.zxytim.com'
);

?>
<div class="home-container">
<div class="home-title"><?php echo __('Welcome to <b>%s</b>!', $website_name);?></div>

<?php
function _gen_id($item)
{
	return;
}

function _gen_name($item)
{
	echo '<td>';
	echo '<a target="_blank" href="';
	t_get_link('contest', $item['id']);
	echo '">';
	echo $item['name'];
	echo '</a>';
	echo '</td>';
}

function _short_time_str($time)
{
	return strftime('%H:%M:%S %b %d', $time);
}

function _gen_time_start($item)
{
	echo '<td>';
	echo _short_time_str($item['time_start']);
	echo '</td>';
}

function _gen_time_end($item)
{
	echo '<td>';
	echo _short_time_str($item['time_end']);
	echo '</td>';
}

function gen_contest_list($type, $amount, $title, $order_by = array('time_start' => 'ASC'))
{
	$type_set = array('past' => -1, 'current' => 0, 'upcoming' => 1);
	$fields = array('id', 'name', 'time_start', 'time_end');
	$type = $type_set[$type];
	$list = ctal_get_list($fields, $type, $order_by, 0, $amount);
	echo '<div class="home-list">';
	echo '<div class="home-list-title">' . $title. '</div>';
	echo '<table class="page-table">';
	echo '<tr>';
	$cols = array(__('Contest Name'), __('Time Start'), __('Time end'));
	foreach ($cols as $head)
		echo '<th>' . $head . '</th>';
	echo '</td>';
	foreach ($list as $item)
	{
		echo '<tr>';
		if (is_null($item))
			for ($i = count($cols); $i; $i --)
				echo '<td>--</td>';
		else
			foreach ($item as $key => $val)
			{
				$func = '_gen_' . $key;
				$func($item);
			}
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
}

function gen_newest_problems()
{
	echo '<div class="home-list">';
	echo '<div class="home-list-title">' . __('Newest Problems') . '</div>';
	echo '<table class="page-table">';
	$fields = array('id', 'title', 'code', 'cnt_ac_user', 'cnt_submit_user');
	$probs = prob_get_list($fields, NULL, NULL, array('id' => 'DESC'), 0, 5);
	echo '<tr>';
	$cols = array(__('ID'), __('Title'), __('Dif.'));
	foreach($cols as $head)
		echo '<th>' . $head . '</th>';
	echo '</tr>';
	foreach ($probs as $prob)
	{
		echo '<tr>';
		if (is_null($prob))
			for ($i = count($cols); $i; $i --)
				echo '<td>--</td>';
		else
		{
			echo '<td>' . $prob['id'] . '</td>';
			echo '<td><a style="color: #9999ee" title="' . $prob['code'] . '" target="_blank" href="' . t_get_link('problem', $prob['code'], TRUE, TRUE) . '">' . $prob['title'] . '</a></td>';
			echo '<td>' . $prob['cnt_ac_user'] . '/' . $prob['cnt_submit_user'] . '</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
}

/**
 * @ignore
 */
function _cv_type($post)
{
	global $theme_path, $POST_TYPE_SET;
	$alt = $POST_TYPE_SET[$post['type']];
	echo '<td>';
	echo '<img src="' . _url('images/post-type-' . $alt. '.png', TRUE) . '" alt="' . $alt . '" title="' . $alt . '"/>';
	echo '</td>';
}

/**
 * @ignore
 */
function _cv_subject($post)
{
	$s = '';
	$s .= '<td>';
	$s .= '<div class="post-topic-subject">';
	if ($post['is_top'])
		$s .= '<span class="post-subject-sticky">[' . __('Sticky') . ']</span>';
	if ($post['is_locked'])
		$s .= '<span class="post-subject-locked">[' . __('Locked') . ']</span>';
	$s .= '<a style="color: #9999ee" target="_blank" class="post-list-topic-subject" href="' ;
	$s .= t_get_link('discuss', $post['id'], TRUE, TRUE);
	$s .= '">' . $post['subject'] . '</a>';
	if ($post['prob_id'])
	{
		$s .= ' ';
		$prob_code = prob_get_code_by_id($post['prob_id']);
		$s .= '<a style="color: #ccccff" class="post-list-prob-code" target="_blank" href="' . t_get_link('problem', $prob_code, TRUE, TRUE) . '">[' . $prob_code . ']</a>';
	}
	if ($post['is_boutique'])
		$s .= '<span class="post-subject-boutique">[' . __('Boutique') . ']</span>';
	$s .= '</div>';
	$s .= '</td>';
	echo $s;
}


/**
 * @ignore
 */
function _cv_author($post)
{
	$s = '';
	$s .= '<td>';
	$s .= '<a class="post-author" '
		. 'title="' . $post['username_uid'] . '" '
		. 'href="' . t_get_link('ajax-user-info', $post['uid'], TRUE, TRUE) . '" '
		. '>' . $post['nickname_uid'] . '</a>';
	$s .= '</td>';
	echo $s;
}

/**
 * @ignore
 */
function _get_time_str($time)
{
	$now = time();
	$today = strftime('%d %b %Y', $now);
	$timeday = strftime('%d %b %Y', $time);
	if ($today == $timeday)
		return strftime('%H:%M', $time);
	else
		return strftime('%d %b', $time);
	$time = strftime('%H:%M:%S %d %b %Y', $time);
}

/**
 * @ignore
 */
function _cv_last_reply($post)
{
	$s = '';
	$s .= '<td>';
	$time = _get_time_str($post['last_reply_time']);
	$s .= $time . '</td>';

	$s .= '<td>';
	$s .= '<a class="post-last-reply-user" href="' . t_get_link('ajax-user-info', $post['last_reply_user'], TRUE, TRUE) . '"'
		. ' title="' . $post['username_last_reply_user'] . '">' . $post['nickname_last_reply_user'] . '</a>';
	$s .= '</td>';
	echo $s;
}


function gen_newest_posts()
{
	echo '<div class="home-list">';
	echo '<div class="home-list-title">' . __('Newest Posts') . '</div>';
	echo '<table class="page-table">';
	$fields = array('last_reply_time', 'last_reply_user', 
		'nickname_last_reply_user', 'username_last_reply_user', 
		'username_uid', 'nickname_uid',
		'subject', 
		'is_top', 'is_locked', 'is_boutique', 
		'type', 'reply_amount', 'viewed_amount', 'prob_id');
	$topics = post_get_topic_list($fields, NULL, 0, 8);
	$cols = array(
		array('', 1, '_cv_type'),
		array(__('Subject'), 1, '_cv_subject'),
		array(__('Author'), 1, '_cv_author'),
		array(__('Last reply'), 2, '_cv_last_reply')
	);
	echo '<tr>';
	foreach($cols as $col)
		echo "<th colspan=\"$col[1]\">" . $col[0]. '</th>';
	echo '</tr>';
	foreach ($topics as $topic)
	{
		echo '<tr>';
		foreach ($cols as $col)
		{
			$func = $col[2];
			$func($topic);
		}
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
}

function gen_top_users()
{
	echo '<div class="home-list">';
	echo '<div class="home-sub-list-title">' . __('Top 10 Users') . '</div>';
	echo '<table class="page-table">';
	$id2rank = user_get_users_rank(0, 10);
	echo '<tr><th>' . __('Rank') . '</th><th>' . __('Name'). '</th></tr>';

	/* XXX should this function supported originally? */
	/*
	$where = NULL;
	global $db, $DBOP;
	foreach ($id2rank as $uid => $rank)
		db_where_add_or($where, array($DBOP['='], 'id', $uid));

	$nicknames = $db->select_from('users', array('id', 'nickname'), $where);
	$id2nickname = array();
	foreach ($nicknames as $nickname)
		$id2nickname[$nickname['id']] = $nickname['nickname'];
	 */
	foreach ($id2rank as $uid => $rank)
	{
		echo '<tr>';
		echo '<td>' . $rank . '</td>';
		echo '<td><a style="color: #9999ee" class="open-in-colorbox" href="'
			. t_get_link('ajax-user-info', $uid, TRUE, TRUE) . '" title="'
			. user_get_username_by_id($uid) . '">'
			. user_get_nickname_by_id($uid) //$id2nickname[$uid] 
			. '</a></td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
}

function gen_new_friends()
{
	global $db, $DBOP;
	$nshow = 5;
	$nusers = $db->get_number_of_rows('users');
	$users = $db->select_from('users', array('id', 'nickname', 'username'),
		array($DBOP['>='], 'id', $nusers - $nshow),
		array('id' => 'DESC'),
		0,
		$nshow
	);
	echo '<div class="home-list">';
	echo '<div class="home-sub-list-title">' . __('New Friends') . '</div>';
	echo '<table class="page-table">';
	echo '<tr><th>' . __('ID') . '</th><th>' . __('Name'). '</th></tr>';
	foreach ($users as $user)
	{
		echo '<tr>';
		echo '<td>' . $user['id'] . '</td>';
		echo '<td><a style="color: #9999ee" class="open-in-colorbox" href="' 
			. t_get_link('ajax-user-info', $user['id'], TRUE, TRUE) . '" title="'
			. $user['username'] . '">'
			. $user['nickname']
			. '</a></td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
}

function gen_links()
{
	global $LINKS;
	echo '<div class="home-list">';
	echo '<div class="home-sub-list-title">' . __('Links') . '</div>';
	foreach ($LINKS as $name => $addr)
		echo "<div class=\"home-links\"><a style=\"color: #9999ee\" target=\"_blank\" href=\"$addr\">$name</a></div>";
	echo '</div>';
}

?>

<div class="home-content">	
	<div id="home-column-main">
<?php 
gen_contest_list('current', 5, __('Current Contest'));
gen_contest_list('upcoming', 5, __('Upcoming Contest'));
gen_newest_problems();
gen_newest_posts();
?>
	</div><!-- id: column-main -->
	<div id="home-column-sub">
<?php
gen_top_users();
gen_new_friends();
gen_links();
?>
	</div><!-- id: column-sub -->
</div><!-- class: home-content -->
</div><!-- class: home-container -->
<script type="text/javascript">
table_set_double_bgcolor();
$("a.post-author").colorbox();
$("a.post-last-reply-user").colorbox();
$("a.open-in-colorbox").colorbox();
</script>
<?php
/*
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
Cleaner Today: <?php _get_cleaner();*/?>

