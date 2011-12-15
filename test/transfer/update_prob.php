<?php
require_once 'pre_include.php';
require_once $includes_path . 'user.php';

$rows = $odb->select_from('problem'); //,  NULL, array($DBOP['!='], 'id', 1));

foreach ($rows as $row)
{
	$code = odb_get_prob_code($row['id']);
	$db->update_data('problems', array(
		'io' => serialize(array("$code.in", "$code.out")),
	), array($DBOP['=s'], 'code', $code));
}

