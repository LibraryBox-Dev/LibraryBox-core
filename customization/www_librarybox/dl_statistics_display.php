<?php

/******* Display download - statistics *****

VERSION 0.1 	- 	Matthias Strubel  (c) 2013 - GPL3

Very simple script to get access to the statistic data.

	Following GET-Options are possible:

	sortOrder	= ASC / DESC  - Ascendening or decsending sort order
	sortBy		= url/counter Sort by complete "url" to file, or based on download "counter"
	list_type	= "all"  display all data ; "top" - limit display with top n entries
	top_max		= The maximum number of values to return in "top" mode.
	output_type	= none or html results in a simple html output
			  "json" results in a json structure
			  
	Default values are provided by dl_statistics.conf.php.

	The HTML output is based on a file pointed in  "dl_statistics.conf.php" to.
	That file lays on librarybox in the content folder 
		http://librarybox.us/content/.... 
	which is in reality on the USB stick. That file can simply exchanged without the need
	of touching the logic behind.

	Currently I don't have the path filter programmed in that script.



CHANGELOG:
	0.1 RELEASE 

********************************************/

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
	 } elseif ( $_GET["output_type"] == "debug" ) {
	 	$output_type="debug";
	 }
}


if ( isset ( $_GET['list_type'] )) {
	$list_type= $_GET['list_type'];
}

#----------------------------------
#  Detect which statement 


$result = dl_read_stat_per_path ( '%' , $sortBy , $sort , $list_type ,  $top_max );

#------------------------------------------------
# Output

if ( is_array ( $result ) ) {
	if ( $output_type == "html" ) {
		# Template file for HTML output
		include $config["HTML_TEMPLATE_FILE"];
		output_html ( $result, array (
			'list_type' => $list_type,
			'top_max'   => $top_max ,
			"sortBy"    =>  $sortBy ,
			"sortOrder" => $sort,
			"filter_path" => false ,
			"script_url" => $_ENV['REQUEST_URI'],
		));
	} elseif ( $output_type == "json" ) {
		header('Content-Type: application/json');
		print json_encode ( $result );
	} elseif ( $output_type == "debug" ) {
		print_r($result);
	}

}

?>
