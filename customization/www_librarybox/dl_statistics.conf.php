<?php

/******** Config File for download statistics *****/

// Everything must be inside the function, because within the dirlisting
// the globals term does not work correct :(

function dl_get_config () {

	$config = array (

	   "SQLITE_FILE" => "sqlite:/opt/piratebox/share/dl_statistics.sqlite",
	   "HTML_TEMPLATE_FILE" => "content/dl_statistics.html.php" ,
	   "sortBy" => 'url',  #url, count are possibilities
	   "sortOrder" => 'ASC' , # ASC, DESC
	   "top_max" => "5",  #Display top n on option "top"
	   "output_type" => "html" , # Display HTML per default or only JSON
	   "list_type" => "all" , #Display "all" or only "top" on default

	);
	return $config ;
}

global $config;

?>
