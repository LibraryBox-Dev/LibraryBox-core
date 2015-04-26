<?php

$VERSION = '1.1';

/*  Lighttpd Enhanced Directory Listing Script
 *  ------------------------------------------
 *  Author: Evan Fosmark
 *  Version: 2008.08.07
 *
 *  Since 1.0 ; Matthias Strubel
 *          Modifications for including a download-count.
 *  Since 1.1 ; Jason Griffey
 *			Modifications for responsive design
 *
 *  GNU License Agreement
 *  ---------------------
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *  http://www.gnu.org/licenses/gpl.txt
 */

/*  Revision by KittyKatt
 *  ---------------------
 *  E-Mail:  kittykatt@archlinux.us
 *  Website: http://www.archerseven.com/kittykatt/
 *  Version:  2010.03.01
 *
 *  Revised original code to include hiding for directories prefixed with a "." (or hidden
 *  directories) as the script was only hiding files prefixed with a "." before. Also included more
 *  file extensions/definitions.
 *
 */

$show_hidden_files = true;
$calculate_folder_size = false;
$display_header = true;
$display_readme = true;
$hide_header = true;
$hide_readme = true;


# Enable a specific dl-file prefix which directs to the counter script
$collect_dl_count = true;
# Display the countend downloads to the overview (reads out of an SQLitedb)
$display_dl_count = true;
$dl_stat_func_file = "dl_statistics.func.php";


$folder_statistics = array();

// Various file type associations
$movie_types = array('mpg','mpeg','avi','asf','mp4','aif','aiff','ram', 'asf','au');
$image_types = array('jpg','jpeg','gif','png','tif','tiff','bmp','ico','svg');
$archive_types = array('zip','cab','7z','gz','tar.bz2','tar.gz','tar','rar',);
$document_types = array('txt','text','doc','docx','abw','odt','pdf','rtf','tex','texinfo','ppt','pptx','pps','ppsx','xls','xlsx');
$font_types = array('ttf','otf','abf','afm','bdf','bmf','fnt','fon','mgf','pcf','ttc','tfm','snf','sfd');
$audio_types = array('mp3','ogg','aac','wma','wav','midi','mid','flac');


// Get the path (cut out the query string from the request_uri)
list($path) = explode('?', $_SERVER['REQUEST_URI']);


// Get the path that we're supposed to show.
$path = ltrim(rawurldecode($path), '/');


if(strlen($path) == 0) {
	$path = "./";
}


// Can't call the script directly since REQUEST_URI won't be a directory
if($_SERVER['PHP_SELF'] == '/'.$path) {
	die("Unable to call " . $path . " directly.");
}


// Make sure it is valid.
if(!is_dir($path)) {
	die("<b>" . $path . "</b> is not a valid path.");
}


//
// Get the size in bytes of a folder
//
function foldersize($path) {
	$size = 0;
	if($handle = @opendir($path)){
		while(($file = readdir($handle)) !== false) {
			if(is_file($path."/".$file)){
				$size += filesize($path."/".$file);
			}

			if(is_dir($path."/".$file)){
				if($file != "." && $file != "..") {
					$size += foldersize($path."/".$file);
				}
			}
		}
	}

	return $size;
}


//
// This function returns the file size of a specified $file.
//
function format_bytes($size, $precision=0) {
    $sizes = array('YB', 'ZB', 'EB', 'PB', 'TB', 'GB', 'MB', 'KB', 'B');
    $total = count($sizes);

    while($total-- && $size > 1024) $size /= 1024;
    return sprintf('%.'.$precision.'f', $size).$sizes[$total];
}


//
// This function returns the mime type of $file.
//
function get_file_type($file) {
	global $image_types, $movie_types, $archive_types, $document_types, $font_types , $audio_types;

	$pos = strrpos($file, ".");
	if ($pos === false) {
		return "Unknown File";
	}

	$ext = rtrim(substr($file, $pos+1), "~");
	if(in_array($ext, $image_types)) {
		$type = "Image File";

	} elseif(in_array($ext, $movie_types)) {
		$type = "Video File";

	} elseif(in_array($ext, $archive_types)) {
		$type = "Compressed Archive";

	} elseif(in_array($ext, $document_types)) {
		$type = "Type Document";

	} elseif(in_array($ext, $font_types)) {
		$type = "Type Font";

	} elseif(in_array($ext, $audio_types)) {
		$type = "Audio File";

	} else {
		$type = "File";
	}

	return(strtoupper($ext) . " " . $type);
}

# returns a small ID which can be used on CSS for pictures
function get_file_type_id($file) {
	global $image_types, $movie_types, $archive_types, $document_types, $font_types, $audio_types;

	$pos = strrpos($file, ".");
	if ($pos === false) {
                return "file";
        }

	$ext = rtrim(substr($file, $pos+1), "~");
        if(in_array($ext, $image_types)) {
	        $type = "img";
        } elseif(in_array($ext, $movie_types)) {
                $type = "video";
        } elseif(in_array($ext, $audio_types)) {
                $type = "audio";
        } elseif(in_array($ext, $archive_types)) {
                $type = "archive";
	} elseif(in_array($ext, $document_types)) {
	        $type = "doc";
        } elseif(in_array($ext, $font_types)) {
	         $type = "font";
        } else {
                 $type = "file";
        }

        return($type);
}


function get_download_count ($filename  ) {
	global $path;
	global $folder_statistics;

	$full_filename = "/$path".$filename ;

	if ( isset ( $folder_statistics[ $full_filename ] ) ) {
		return  $folder_statistics[ $full_filename ][ 'counter'] ;
	} else { 
		return 0;
	}
}

function get_folder_statistics ($my_path, &$folder_statistics) {
	global $dl_stat_func_file;
	include $dl_stat_func_file ;

	$result = dl_read_stat_per_path_only ( "$my_path"  );

	foreach ( $result as $line ) {
		$folder_statistics [ $line [ 'url' ] ] = array  (
			'url' 		=>  $line [ 'url' ] ,
			'counter' 	=>  $line [ 'counter' ] ,
			);
	}
}

function get_utf8_encoded($string) {
	$encoding = mb_detect_encoding($string, "auto" ) ;
	if ( $encoding  == "UTF-8" ||   $encoding  == "ASCII" ) {
		return $string ;
	} else {
		$return_string = mb_convert_encoding($string, "UTF-8");
		return  $return_string;
	}
}

// Print the heading stuff
$vpath = ($path != "./")?$path:"";
print '<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Index of /'.$vpath.'</title>
		<link href="/content/css/bootstrap.css" rel="stylesheet">
		<link rel="stylesheet" href="/content/css/dir_list.css">

		  	<link rel="prefetch" type="application/l10n" href="/content/locales/locales.ini" />
  			<script type="text/javascript" src="/content/js/l10n.js"></script>
	</head>
	<body>
';

if ( $display_dl_count ) {
	get_folder_statistics ( "/".$vpath , $folder_statistics );	
}	

if ($display_header)
{
	if (is_file($path.'/HEADER'))
	{
		print "<pre>";
		print(nl2br(file_get_contents($path.'/HEADER')));
		print "</pre>";
	}

	if (is_file($path.'/HEADER.html'))
	{
		readfile($path.'/HEADER.html');
	}
}

print "<h2 data-l10n-id='filedirIndex'>Index of /" . $vpath ."</h2>
	<div class='list'>
	<table>";


// Get all of the folders and files.
$folderlist = array();
$filelist = array();
if($handle = @opendir($path)) {
	while(($item = readdir($handle)) !== false) {
		if(is_dir($path.'/'.$item) and $item != '.' and $item != '..') {
			if( $show_hidden_files == "false" ) {
				if(substr($item, 0, 1) == "." or substr($item, -1) == "~") {
				  continue;
				}
			}
			$folderlist[] = array(
				'name' => $item,
				'size' => (($calculate_folder_size)?foldersize($path.'/'.$item):0),
				'modtime'=> filemtime($path.'/'.$item),
				'file_type' => "Directory"
			);
		}

		elseif(is_file($path.'/'.$item)) {
			if ($item === basename($_SERVER['SCRIPT_NAME']))
			{
				continue;
			}
			if ($hide_header)
			{
				if ($item === 'HEADER' || $item === 'HEADER.html')
				{
					continue;
				}
			}
			if ($hide_readme)
			{
				if ($item === 'README' || $item === 'README.html')
				{
					continue;
				}
			}
			if( $show_hidden_files == "false" ) {
				if(substr($item, 0, 1) == "." or substr($item, -1) == "~") {
				  continue;
				}
			}
			$filelist[] = array(
				'name'=> $item,
				'size'=> filesize($path.'/'.$item),
				'modtime'=> filemtime($path.'/'.$item),
				'file_type' => get_file_type($path.'/'.$item),
				'counter'   => get_download_count ($item), ## addslashes needed??
				'img_id'    => get_file_type_id($path.'/'.$item)
			);
		}
	}
	closedir($handle);
}


if(!isset($_GET['sort'])) {
	$_GET['sort'] = 'name';
}

// Figure out what to sort files by
$file_order_by = array();
foreach ($filelist as $key=>$row) {
    $file_order_by[$key]  = $row[$_GET['sort']];
}

// Figure out what to sort folders by
$folder_order_by = array();
foreach ($folderlist as $key=>$row) {
    $folder_order_by[$key]  = $row[$_GET['sort']];
}

// Order the files and folders
if(isset($_GET['order'])) {
	array_multisort($folder_order_by, SORT_DESC, $folderlist);
	array_multisort($file_order_by, SORT_DESC, $filelist);
} else {
	array_multisort($folder_order_by, SORT_ASC, $folderlist);
	array_multisort($file_order_by, SORT_ASC, $filelist);
	$order = "&amp;order=desc";
}


// Show sort methods
print "<thead><tr>";

$sort_methods = array();
$sort_methods['name'] = "<div data-l10n-id='filedirName'>Name</div>";
//$sort_methods['modtime'] = "Last Modified";
$sort_methods['size'] = "<div data-l10n-id='filedirSize' class='s hidden-sm hidden-xs'>Size</div>";
$sort_methods['file_type'] = "<div data-l10n-id='filedirType' class='t hidden-sm hidden-xs'>Type</div>";

if ( $display_dl_count ) {
	$sort_methods['counter'] = "<div data-l10n-id='filedirDownloads' class='c hidden-xs'>Downloads</div>";
}

foreach($sort_methods as $key=>$item) {
	if($_GET['sort'] == $key) {
		print "<th><a href='?sort=$key$order'>$item</a></th>";
	} else {
		print "<th><a href='?sort=$key'>$item</a></th>";
	}
}
print "</tr></thead><tbody>";



// Parent directory link
if($path != "./") {
	print "<tr><td data-l10n-id='filedirParDir' class='n'><a id='folder' href='..'></a>/</td>";
	//print "<td class='m'> </td>";
	print "<td class='s hidden-sm hidden-xs'> </td>";
	print "<td class='t hidden-sm hidden-xs'>Directory</td></tr>\n";
}



// Print folder information
foreach($folderlist as $folder) {
	print "<tr><td class='n'><a id='folder' href='" . $folder['name']. "'>" .get_utf8_encoded($folder['name']). "</a>/</td>";
	//print "<td class='m'>" . date('Y-M-d H:i:s', $folder['modtime']) . "</td>";
	print "<td class='s hidden-sm hidden-xs'>" . (($calculate_folder_size)?format_bytes($folder['size'], 2):'--') . " </td>";
	print "<td class='t hidden-sm hidden-xs'>" . $folder['file_type']                    . "</td></tr>\n";
}



// This simply creates an extra line for file/folder seperation
print "<tr><td colspan='4' style='height:7px;'></td></tr>\n";



// Print file information
foreach($filelist as $file) {

	global $collect_dl_count;

	$file_link_prefix="";

	if ( $collect_dl_count ) {
		$file_link_prefix="/dl_statistics_counter.php?DL_URL=/" . urlencode($path);
	}

	print "<tr><td class='n'><a id='".$file['img_id']."' href='$file_link_prefix" . urlencode($file['name']). "'>" .get_utf8_encoded($file['name']). "</a></td>";
	// print "<td class='m'>" . date('Y-M-d H:i:s', $file['modtime'])   . "</td>";
	print "<td class='s hidden-sm hidden-xs'>" . format_bytes($file['size'],2)           . " </td>";
	print "<td class='t hidden-sm hidden-xs'>" . $file['file_type']                      . "</td>";
	if ( $display_dl_count ) {
		print "<td class='c hidden-sm hidden-xs'>" . $file['counter'] . "</td>";
	}
	print "</tr>\n";
}



// Print ending stuff
print "</tbody>
	</table>
	</div>";

if ($display_readme)
{
	if (is_file($path.'/README'))
	{
		print "<pre>";
		print(nl2br(file_get_contents($path.'/README')));
		print "</pre>";
	}

	if (is_file($path.'/README.html'))
	{
		readfile($path.'/README.html');
	}
}

print "<div class='foot'>LibraryBox v2.1</div>
	</body>
	</html>";


