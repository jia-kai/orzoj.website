<?php

require_once '../pre_include.php';
/*
$db->delete_item('post_topics');
$db->delete_item('posts');
mysql_query('TRUNCATE TABLE post_topics');
mysql_query('TRUNCATE TABLE posts');
 */
$N_POSTS = 30;
$N_POST_TOPIC = 21;


function add_post_topic($prob_id = NULL)
{
	static $i = 0;
	global $db, $DBOP;
	$i ++;
	$time = $i;
	$uid = rand(1, 10);
	if (!is_int($prob_id))
		$prob_id = (rand(0, 20) < 10 ? 0 : rand(1, 10));
	$is_top = (rand(0, 99) < 20) ? 1 : 0;
	$priority = ($is_top ? 5 : 0);
	$is_locked = rand(0, 100) < 20 ? 1 : 0;
	$is_boutique = rand(0, 1);
	$type = rand(1, 3); // vote is temporary disabled
	$subject = 'subject ' . $i;

	$last_reply_time = $time;
	$last_reply_user = $uid;

	$val = array();
	foreach (array('time', 'uid', 'prob_id', 
		'priority', 'is_top', 'is_locked', 'is_boutique',
		'type', 'subject', 'last_reply_time', 'last_reply_user') as $item)
		$val[$item] = $$item;
	$tid = $db->insert_into('post_topics', $val);

	$val = array();
	$floor = 1;
	$tmp = 'CONTENT ' . $tid;
	$content = '';
	for ($j = 0; $j < rand(1, 20); $j ++)
		$content .= $tmp;

	$last_modify_time = 0;
	$last_modify_user = 0;

	if (rand(1, 100) < 30)
	{
		$last_modify_time = $time + rand(1, 10);
		$last_modify_user = rand(1, 10);
	}

	foreach (array('time', 'uid', 'floor', 'tid', 
		'content', 'last_modify_time', 'last_modify_user') as $item)
		$val[$item] = $$item;

	$db->insert_into('posts', $val);
}

function add_post_reply($tid = NULL)
{
	global $db, $DBOP, $N_POST_TOPIC;
	static $tt = 0;
	$tt ++;
	$time = $tt + $N_POST_TOPIC + 10;
	$uid = rand(1, 1);
	if ($tid == NULL)
		$tid = rand(1, $N_POST_TOPIC);
	$topic = $db->select_from('post_topics', array('floor_amount', 'reply_amount', 'viewed_amount'), array($DBOP['='], 'id', $tid));
	$topic = $topic[0];
	$floor = $topic['floor_amount'] + 1;
	$tmp = 'This is a post reply. ';
	$content = '';
	for ($i = 0; $i < rand(1, 20); $i ++)
		$content .= $tmp;
	$last_modify_user = 0;
	$last_modify_time = 0;
	if (rand(0, 100) < 30)
	{
		$last_modify_time = $time + rand(1, 10);
		$last_modify_user = rand(1, 10);
	}
	$val = array();
	foreach (array('time', 'uid', 'tid', 'floor', 'content', 
		'last_modify_time', 'last_modify_user') as $item)
		$val[$item] = $$item;

	$db->insert_into('posts', $val);


	$reply_amount = $topic['reply_amount'] + 1;
	$viewed_amount = $topic['viewed_amount'] + 1;
	$last_reply_time = $time;
	$last_reply_user = $uid;
	$floor_amount = $floor;

	$val = array();
	foreach(array('reply_amount', 'viewed_amount', 
		'last_reply_user', 'last_reply_time', 'floor_amount') as $item)
		$val[$item] = $$item;
	$db->update_data('post_topics', $val, array($DBOP['='], 'id', $tid));
}

/*
for ($i = 0; $i < $N_POST_TOPIC; $i ++)
	add_post_topic();
for ($i = 0; $i < $N_POST_TOPIC; $i ++)
	add_post_topic(1);
for ($i = 0; $i < $N_POSTS; $i ++)
	add_post_reply();
 */
add_post_reply(1);
