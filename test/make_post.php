<?php

require_once '../pre_include.php';
mysql_query('TRUNCATE TABLE post_topics');
mysql_query('TRUNCATE TABLE posts');
# $db->delete_item('post_topics');
# $db->delete_item('posts');

$N_POSTS = 200;
$N_POST_TOPIC = 100;

for ($i = 0; $i < $N_POSTS; $i ++)
{
	$tid = rand(1, $N_POST_TOPIC);
	$time = $i + time();
	$uid = rand(1, 10);
	$subject = 'subject ' . $i;
	$content = '';
	for ($j = 0; $j < rand(30, 50); $j ++)
		$content .= 'content ' . $i;
	$db->insert_into('posts',
		array(
			'time' => $time,
			'uid' => $uid,
			'tid' => $tid,
			'content' => $content,
		)
	);
	$cnt = $db->get_number_of_rows('post_topics', array($DBOP['='], 'id', $tid));
	fprintf(fopen('/tmp/orz','aw'),"%d\n", $cnt);
	if ($cnt == 0)
	{
		$db->insert_into('post_topics', 
			array(
				'time' => $time,
				'uid' => $uid,
				'prob_id' => rand(0, 2),
				'reply_amount' => 0,
				'viewed_amount' => rand(1, 10),
				'priority' => 0,
				'is_top' => 0,
				'is_locked' => 0,
				'type' => rand(1, 4),
				'last_reply_time' => $time,
				'last_reply_user' => $uid,
				'subject' => $subject,
				'content' => $content
			)
		);
	}
	else
	{
		$topic = $db->select_from('post_topics', array('reply_amount', 'viewed_amount'), array($DBOP['='], 'id', $tid));
		$topic = $topic[0];
		$db->update_data('post_topics', 
			array(
				'time' => $time,
				'reply_amount' => $topic['reply_amount'] + 1,
				'viewed_amount' => $topic['viewed_amount'] + 1,
				'last_reply_user' => $uid
			),
			array($DBOP['='], 'id', $tid)
		);
	}
}
