<?php
require_once 'pre_include.php';

$rows = $odb->select_from('problemdata', array('dataurl', 'problemid'),
	array($DBOP['!='], 'problemid', 1));

foreach ($rows as $row)
	printf("/tmp/%s %s\n", $row['dataurl'], odb_get_prob_code($row['problemid']));

