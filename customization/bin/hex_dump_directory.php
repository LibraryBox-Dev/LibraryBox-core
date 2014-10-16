<?php

### Script to create a hex-based dump of filenames located in the given folder
##  This is a command line tool, but can be used via browser as well, but
##  Plaintext encoding is wrong and use "Show Source" to display correct format.
##
##   Matthias Strubel  - 2014
##
##  Usage:  add in front "php" or "php-cli"
##    hex_dump_directory.php 		-  Current directory recursive down
##    hex_dump_directory.php /root/     -  Display the content of /root/
##

function hex_dump($data, $newline="\n")
{
### Copied from
###    http://stackoverflow.com/questions/1057572/how-can-i-get-a-hex-dump-of-a-string-in-php
  static $from = '';
  static $to = '';

  static $width = 16; # number of bytes per line

  static $pad = '.'; # padding for non-visible characters

  if ($from==='')
  {
    for ($i=0; $i<=0xFF; $i++)
    {
      $from .= chr($i);
      $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
    }
  }

  $hex = str_split(bin2hex($data), $width*2);
  $chars = str_split(strtr($data, $from, $to), $width);

  $offset = 0;
  foreach ($hex as $i => $line)
  {
    echo sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']' . $newline;
    $offset += $width;
  }
}


function dump_dir_r($folder) {

	if ($handle = opendir($folder)) {
	echo "------------------- $folder --------------------- \n";
	echo "Directory handle: $handle\n";
	echo "Files:\n";

	/* Das ist der korrekte Weg, ein Verzeichnis zu durchlaufen. */
	while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != "..") {
			if ( is_dir( $file ) ) {
				dump_dir_r($file);
			} else {
				$found_encoding=mb_detect_encoding($file, "auto");
				echo "$file\n";
				echo "Encoding detected:  $found_encoding  \n";

				hex_dump("$file");

				if (  !( $found_encoding == 'UTF-8' ||  $found_encoding == 'ASCII') ) {
	 				$file_utf8 = mb_convert_encoding($file, "UTF-8");
					echo "\nConverted to UTF-8: $file_utf8 \n";
					hex_dump($file_utf8);
				}
			}
		}
    	}
	echo "---------------------------------------------------- \n";
   	 closedir($handle);
	}

}

echo "Environment variables:\n";
print_r($_ENV );
echo "Internal encoding: ", mb_internal_encoding(), "\n";

#echo "Known encodings:";
#print_r( mb_list_encodings () );

$path="./";                                              
                                                                                  
if ( isset( $argv[1] ) && $argv[1] != "" ) {                                      
        $path=$argv[1];                                                        
}                                                                              
echo "Starting dump in $path \n" ;
dump_dir_r($path);

?>
