<?php

if (!isset($_GET['client_date'])) {
	exit(0);
} else {
	$date = date("F j, Y", $_GET['client_date']);
}

#Encrypt combination of IP + Date + Useragend. So one user only have a specific string per day
$client_string = $_ENV['REMOTE_ADDR'] . $_ENV['HTTP_USER_AGENT'] . $date ;

$sha = sha1($client_string);

include ("vc.func.php");

vc_save_visitor (  $sha , $date, $debug ); 

print json_encode ( array($sha, $date) );