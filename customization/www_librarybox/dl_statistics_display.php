<?php

/******* Display download - statistics *****/

include "dl_statistics.conf.php";


$sort=$config["sortOrder"];
$sortBy=$config["sortBy"];
$top_max=$config["top_max"];
$output_type=$config["output_type"];
$list_type=$config["list_type"];

if ( isset ($_GET['sortOrder'] )) {
	if ( $_GET['sortOrder'] == 'ASC' ) {
		$sort='ASC';
	} else {
		$sort='DESC';
	}
}

if ( isset ($_GET['sortBy']) ) {
	if ( $_GET["sortBy"] == "url" ) {
		$sortBy="url";
	} elseif (  $_GET["sortBy"] == "counter" ) {
		$sortBy="counter";
	}
}

if ( isset ($_GET['top_max'] )) {
		$top_max =  $_GET['top_max'];
}


if ( isset ($_GET['output_type'] )) {
	 if ( $_GET["output_type"] == "json" ) {
	 	$output_type= "json";
	 } elseif ( $_GET["output_type"] == "html" ) {
	 	$output_type="html";
	 }
}


if ( isset ( $_GET['list_type'] )) {
	$list_type= $_GET['list_type'];
}

#----------------------------------
#  Detect which statement 

$sth= false ;

$result= false;

if ( !  $db = new PDO (  $config['SQLITE_FILE'] ) ) {
	die ( "Error, couldn't open database ". $err );
}


if ( $list_type == "all" ) { 
 	$sth = $db->prepare ( "SELECT url, counter FROM dl_statistics ORDER by $sortBy $sort ");
} elseif ( $list_type == "top" ) {
	$sth = $db->prepare ( "SELECT url, counter FROM dl_statistics ORDER by $sortBy $sort LIMIT 1 , :max ");
	$sth->bindParam (':max' , $top_max, PDO::PARAM_INT  );
}

if ( $sth ) {

	if ( !  $sth->execute() ) {
		die ( "Error executing statement: ". $sth->errorInfo());
	}

	$result = $sth->fetchAll();
	# Tidy array up, I only want named keys
	foreach (  $result as &$line ) {
		unset ( $line[0] );
		unset ( $line[1] );
	}

} else {
  print_r ($db->errorInfo());
  die ("\n no valid statement could be found");
}

#------------------------------------------------
# Output

if ( is_array ( $result ) ) {
	if ( $output_type == "html" ) {
		# Template file for HTML output
		include $config["HTML_TEMPLATE_FILE"];
		output_html ( $result );
	} elseif ( $output_type == "json" ) {
		header('Content-Type: application/json');
		print json_encode ( $result );
	}

}

?>
