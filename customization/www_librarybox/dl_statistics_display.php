<?php

/******* Display download - statistics *****/

require_once  "dl_statistics.conf.php";
include "dl_statistics.func.php";

$config=dl_get_config();

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


$result= dl_read_stat_per_path ( '%' , $sortBy , $sort , $list_type ,  $top_max );

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
