<?php

$debug = false;

if ( ! isset ($_GET['debug']) ) {

	Header("Content-type:  image/gif"); 
	Header("Expires: Wed, 11 Nov 1998 11:11:11 GMT"); 
	Header("Cache-Control: no-cache"); 
	Header("Cache-Control: must-revalidate"); 

	// This prints the raw 1 pixel gif to the browser 
	// make sure this is one long line! 

	printf ("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c",71,73,70,56,57,97,1,0,1,0,128,255,0,192,192,192,0,0,0,33,249,4,1,0,0,0,0,44,0,0,0,0,1,0,1,0,0,2,2,68,1,0,59); 

} else {
	$debug = true;
}


$date = date ( 'Y-m-d' );

#Encrypt combination of IP + Date + Useragend. So one user only have a specific string per day
$client_string = $_ENV['REMOTE_ADDR'] . $_ENV['HTTP_USER_AGENT'] . $date ;

$sha = sha1($client_string);

include ("vc.func.php");

vc_save_visitor (  $sha , $debug ); 


?> 
