<?php

/********************************

    HTML Generation function for PHP-Download-Counter Statistics

    will be called by dl_statistics_display.php with an 

	
$result = array  (
		array ( 'url' => "/Shared/......" , "counter" => 4 ),
		array ( 'url' => "/Shared/......" , "counter" => 5 ),
		)


(optional) $arguments   =   array  (
		....
		);

   The HTML is separated in that file, to enable easy exchangable looks

   *******************************/


function  print_header () {


echo <<<EOD

	<html> 
	<head><title>Download-Statistics</title></head>
	<body>
EOD;


}

function print_table_head() {

echo <<<EOD
	<table>
	<tr><th></th><th><a href="dl_statistics_display.php?sortBy=url">Filename:</a></th><th data-l10n-id="statsDownloads"><a href="dl_statistics_display.php?sortBy=counter&sortOrder=DESC">Downloads:</th></tr>
EOD;
}

function print_table_line($no , $filename = "" , $count = 0) {

echo <<<EOD
	<tr><td></td><td>$filename</td><td>$count</td></tr>
EOD;

}

function print_footer() {

echo <<<EOD
	</table>
	</body>
	</html>
EOD;

}

function  output_html   ( $result = array () , $arguments = array ()  ) {

	print_header();
	print_table_head();


	foreach ( $result as $no => $line ) {
		print_table_line ( $no, $line['url'], $line['counter'] );

	}

	print_footer();
}

