<?php

/**
 Functions for helping handling with weired char combinations
  (c) 2015 Matthias Strubel GPL-3
**/

function get_utf8_encoded($string) {
        $encoding = mb_detect_encoding($string, "UTF-8, ISO-8859-1" ) ;
        $return_string =  $string;
        if ( $encoding  == "UTF-8" ||   $encoding  == "ASCII" ) {
        } else {
                $return_string = mb_convert_encoding($string, "UTF-8");
        }
        return  $return_string;
}


?>
