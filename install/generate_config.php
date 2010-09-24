<?php
function conf_file_generate($dblayer,$dbhost,$dbport,$dbusername,$dbpassword,$dbname,$tablepre)
{
	$dbusername = addslashes($dbusername);
	$dbpassword = addslashes($dbpassword);
	$dbname = addslashes($dbname);
	$tablepre = addslashes($tablepre);
	$str = <<<EOF
<?php
\$db_type = '$dblayer';
\$db_host = '$dbhost';
\$db_port = '$dbport';
\$db_user = '$dbusername';
\$db_password = '$dbpassword';
\$db_dbname = '$dbname';
\$tablepre = '$tablepre';
EOF;
	$fpointer = fopen("config.php","w");
	if ($fpointer)
	{
		fprintf($fpointer,"%s",$str);
		fclose($fpointer);
		return true;
	}
	else
		return false;
}


