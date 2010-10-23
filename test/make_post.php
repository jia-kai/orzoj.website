<?php

require_once '../pre_include.php';
$db->delete_item('posts');
$POST_AMOUNT = 50;

for ($i = 0; $i < $POST_AMOUNT; $i ++)
	$db->insert_into('posts', 
		array(
			'time' => time(),
			'uid' => rand(1, 10),
			'prob_id' => rand(0, 2),
			'pid' => rand(0, min($i, 10)),
			'rid' => -1,
			'score' => rand(1, 10),
			'is_top' => 0,
			'type' => 'normal',
			'last_reply_time' => time(),
			'last_reply_user' => rand(1, 10),
			'subject' => 'subject ' . ($i + 1),
			'content' => 'content ' . ($i + 1),
			'last_modify_time' => time(),
			'last_modify_user' => rand(1, 10)
		)
	);
