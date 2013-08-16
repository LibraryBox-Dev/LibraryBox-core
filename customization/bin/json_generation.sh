#!/bin/sh

#  Matthias Strubel (c) 2013  - GPL3
# Generate a JSON file, which relfects some part of the current configuration

# First parameter is the piratebox.conf 
### Used parameter : JSON_FILE 

. $1

# json.conf contains some information about modules on the frontend
. $PIRATEBOX_FOLDER/conf/json.conf

### JSON convert functions
. $PIRATEBOX_FOLDER/lib/json_func.sh


####
# DROOPY_ENABLED  => upload_droopy
# DROOPY_PORT     => droopy_port
# HOST		  => droopy_host 

json_droopy_enabled=`convert_yn_to_tf $DROOPY_ENABLED`

echo "Generating json configuration file: $JSON_FILE"

echo "" > $JSON_FILE
echo "{ \"librarybox\" : { \"module\" : { " >> $JSON_FILE
#------------ upload configuration
echo -n "   \"upload\" : { \"status\" : $json_droopy_enabled , \"file\" : \"$UPLOAD_MODULE_FILE\" " >> $JSON_FILE
#-----------  droopy specialities
if [ "$DROOPY_ENABLED" == "yes" ] ; then
	echo -n ", " >> $JSON_FILE
	echo -n "  \"upload_style\" : \"droopy\" , " >> $JSON_FILE
	echo -n "  \"droopy_port\" : \"$DROOPY_PORT\", \"droopy_host\" : \"$HOST\" "  >> $JSON_FILE
fi
echo " } " >> $JSON_FILE


#---------------
echo " }  } " >>  $JSON_FILE
