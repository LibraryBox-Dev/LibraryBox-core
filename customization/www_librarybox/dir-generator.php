<?php

$VERSION = '0.4';

/*  Lighttpd Enhanced Directory Listing Script
 *  ------------------------------------------
 *  Author: Evan Fosmark
 *  Version: 2008.08.07
 *
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

// Various file type associations
$movie_types = array('mpg','mpeg','avi','asf','mp3','wav','mp4','wma','aif','aiff','ram', 'midi','mid','asf','au','flac');
$image_types = array('jpg','jpeg','gif','png','tif','tiff','bmp','ico');
$archive_types = array('zip','cab','7z','gz','tar.bz2','tar.gz','tar','rar',);
$document_types = array('txt','text','doc','docx','abw','odt','pdf','rtf','tex','texinfo',);
$font_types = array('ttf','otf','abf','afm','bdf','bmf','fnt','fon','mgf','pcf','ttc','tfm','snf','sfd');


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
	global $image_types, $movie_types, $archive_types, $document_types, $font_types;

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

	} else {
		$type = "File";
	}

	return(strtoupper($ext) . " " . $type);
}



// Print the heading stuff
$vpath = ($path != "./")?$path:"";
print '<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Index of /'.$vpath.'</title>
		<style type="text/css">
		a, a:active {text-decoration: none; color: blue;}
		a:visited {color: #48468F;}
		a:hover, a:focus {text-decoration: underline; color: red;}
		body {background-color: #F5F5F5;}
		h2 {margin-bottom: 12px;}
		table {margin-left: 12px; padding:0px; border-collapse:collapse;}
		th, td { font-family: "Courier New", Courier, monospace; font-size: 10pt; text-align: left;}
		th { font-weight: bold; padding-right: 14px; padding-bottom: 3px;}
		td {padding-right: 14px;}
		td.s, th.s {text-align: right;}
		div.list { background-color: white; border-top: 1px solid #646464; border-bottom: 1px solid #646464; padding-top: 10px; padding-bottom: 14px;}
		div.foot, div.script_title { font-family: "Courier New", Courier, monospace; font-size: 10pt; color: #787878; padding-top: 4px;}
		div.script_title {float:right;text-align:right;font-size:8pt;color:#999;}
		</style>
	</head>
	<body>
';

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

print "<h2>Index of /" . $vpath ."</h2>
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
				'file_type' => get_file_type($path.'/'.$item)
			);
		}
	}
	fclose($handle);
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
if($_GET['order']) {
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
$sort_methods['name'] = "Name";
$sort_methods['modtime'] = "Last Modified";
$sort_methods['size'] = "Size";
$sort_methods['file_type'] = "Type";

foreach($sort_methods as $key=>$item) {
	if($_GET['sort'] == $key) {
		print "<th class='n'><a href='?sort=$key$order'>$item</a></th>";
	} else {
		print "<th class='n'><a href='?sort=$key'>$item</a></th>";
	}
}
print "</tr></thead><tbody>";



// Parent directory link
if($path != "./") {
	print "<tr><td class='n'><a href='..'>Parent Directory</a>/</td>";
	print "<td class='m'> </td>";
	print "<td class='s'> </td>";
	print "<td class='t'>Directory</td></tr>";
}



// Print folder information
foreach($folderlist as $folder) {
	print "<tr><td class='n'><a href='" . addslashes($folder['name']). "'>" .htmlentities($folder['name']). "</a>/</td>";
	print "<td class='m'>" . date('Y-M-d H:i:s', $folder['modtime']) . "</td>";
	print "<td class='s'>" . (($calculate_folder_size)?format_bytes($folder['size'], 2):'--') . " </td>";
	print "<td class='t'>" . $folder['file_type']                    . "</td></tr>";
}



// This simply creates an extra line for file/folder seperation
print "<tr><td colspan='4' style='height:7px;'></td></tr>";



// Print file information
foreach($filelist as $file) {
	print "<tr><td class='n'><a href='" . addslashes($file['name']). "'>" .htmlentities($file['name']). "</a></td>";
	print "<td class='m'>" . date('Y-M-d H:i:s', $file['modtime'])   . "</td>";
	print "<td class='s'>" . format_bytes($file['size'],2)           . " </td>";
	print "<td class='t'>" . $file['file_type']                      . "</td></tr>";
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

print "	<div class='script_title'>Lighttpd Enhanced Directory Listing Script</div>
	<div class='foot'>". $_ENV['SERVER_SOFTWARE'] . "</div>
	</body>
	</html>";


/* -------------------------------------------------------------------------------- *\
	I hope you enjoyed my take on the enhanced directory listing script!
	If you have any questions, feel free to contact me.

	Regards,
	   Evan Fosmark < me@evanfosmark.com >
\* -------------------------------------------------------------------------------- */
