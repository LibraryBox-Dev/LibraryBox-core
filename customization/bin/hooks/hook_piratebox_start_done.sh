#!/bin/sh

# ---- TEMPLATE ----

# Runs on every Startup after the normal init-steps are done
#  get config file 

if [ !  -f $1 ] ; then
  echo "Config-File $1 not found..."
  exit 255
fi

#Load config
. $1

# You can uncommend this line to see when hook is starting:
 echo "------------------ Running $0 ------------------"

# Recreate the content folder, if it was deleted
#  only if it is not already existing.
if [ ! -d  $WWW_CONTENT ] ; then
	# Prepare content folder
	echo "Creating 'content' folder on USB stick and move over stuff"
	mkdir -p $WWW_CONTENT
	cp -r     $PIRATEBOX_FOLDER/www_content/*   $WWW_CONTENT
fi



if [ "$FTP_ENABLED" = "yes" ] ; then
	echo "starting PROFTPD.."

	# Load PirateBox config
	. $PIRATEBOX_FOLDER/conf/ftp/ftp.conf

	# $PROFTPD_CONFIG_FILE
	# $PROFTPD_PID  #####PID#####

#  Define Options
#######  AdminAccess	<-> $ADMIN_ACCESS
#######  AnonAccess	<-> $ENABLE_ANON

	proftpd_opt_admin=""
	proftpd_opt_anon=""

	[ "$ADMIN_ACCESS" = "yes" ] && proftpd_opt_admin="-D AdminAccess"
	[ "$ENABLE_ANON"  = "yes" ] && proftpd_opt_anon="-D AnonAccess"

	if [ "$ADMIN_ACCESS" = "no" ] &&  [ "$ENABLE_ANON"  = "no" ] ; then
		echo "skip ftp, because admin and anon disabled"
	else 
		#Proftpd writes the pidfile for its own
		proftpd  -c $PROFTPD_CONFIG_FILE $proftpd_opt_admin $proftpd_opt_admin $proftpd_opt_sync 
		echo $?
	fi

fi


if [ "$FTP_SYNC_ENABLED" = "yes" ] ; then
	echo "start PROFTPD with Sync config ..." 
	#Proftpd writes the pidfile for its own
	proftpd  -c $PROFTPD_SYNC_CONFIG_FILE 
	echo $?
fi



if [ "$SHOUTBOX_ENABLED" == "no" ] ; then
	# If the shoutbox is disabled, we remove the writable flag
	echo -n "Making shoutbox readonly..."
	chmod a-w $CHATFILE
	echo "done"
fi

 $PIRATEBOX_FOLDER/bin/json_generation.sh  $1

if [ "$FTP_SYNC_CLIENT_ENABLED" == "yes" ] ; then
	echo "Starting sync client"
	$PIRATEBOX_FOLDER/bin/ftp_sync_tool.sh >>  $PIRATEBOX_FOLDER/share/sync.log & 
	echo $! > $FTP_SYNC_CLIENT_PID
fi
