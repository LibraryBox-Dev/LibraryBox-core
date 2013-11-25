#!/bin/sh

##  Script for utilizise lftp client for syncing
#     script needs a a config file and generates upon that 
#     a lftp-script and runs the client

DEBUG=${DEBUG:=false}

PIRATEBOX_FOLDER=${PIRATEBOX_FOLDER:=/opt/piratebox}

LFTP_HELPER_FUNCTIONS="$PIRATEBOX_FOLDER/lib/lftp_helper.sh"

# First implementation of dedicated avahi cli hostname lookup
NODE_NAME_HELPER="$PIRATEBOX_FOLDER/lib/node_name_resolution.sh" 

SYNC_CLIENT_LFTP_FILE="$PIRATEBOX_FOLDER/tmp/lftp_sync.command"
SYNC_CLIENT_CONFIG_FILE="$PIRATEBOX_FOLDER/conf/ftp/ftp_sync_client.conf"

if [ -e $LFTP_HELPER_FUNCTIONS ] ; then
	. $LFTP_HELPER_FUNCTIONS
else
	echo "Can't load helper functions at $LFTP_HELPER_FUNCTIONS"
	exit 255
fi

if [ -e $NODE_NAME_HELPER ] ; then
	. $NODE_NAME_HELPER
else 
	echo "Can't load node name helper stuff at $NODE_NAME_HELPER "
	exit 255
fi


if [ ! -e $SYNC_CLIENT_CONFIG_FILE ] ; then
	echo "Config file  $SYNC_CLIENT_CONFIG_FILE does not exist"
	exit 255
fi

. $SYNC_CLIENT_CONFIG_FILE


if [ "$SYNC_CLIENT_ENABLED" == "no" ]; then 
	exit 0
fi

if   [ "$SYNC_CLIENT_STATIC_IP" == "empty" ] && [ "$SYNC_CLIENT_HOST" == "empty" ] ; then
	echo "IP or host  is not set, exiting."
	exit 255
fi

if [ "$SYNC_CLIENT_STATIC_PORT" == "empty" ] ; then
	echo "Port is not set, exiting."
	exit 255
fi

if [ "$SYNC_CLIENT_STATIC_USER" == "empty" ]; then
	echo "User 'empty' is not valid"
	exit 255
fi



if  [ "$SYNC_CLIENT_HOST" != "empty" ] && [ ! -z "$SYNC_CLIENT_HOST" ]  ; then
######## Well....
### if the host is set, we are going to run avahi-browse in a loop and see
###   when we get an resolution from the avahi mdns request
###   then we map it into SYNC_CLIENT_STATIC_IP and create the config
	date=$(date +"%b %d %T")
	echo $date "Doing avahi hostname lookup for $SYNC_CLIENT_HOST"
	SYNC_CLIENT_STATIC_IP=""
	until [ ! -z $SYNC_CLIENT_STATIC_IP ] 
	do
		date=$(date +"%b %d %T")
		resolve_node_hostname "$SYNC_CLIENT_HOST"
		SYNC_CLIENT_STATIC_IP=$NODE_IP
		if [  -z $SYNC_CLIENT_STATIC_IP ]  ; then
			date=$(date +"%b %d %T")
			echo $date "hostname not found; wait  $SYNC_CLIENT_REPEAT_TIME"
			sleep  $SYNC_CLIENT_REPEAT_TIME
		fi
	done



fi 

ftp_lftp_generate_command_member "$SYNC_CLIENT_SCHEMA_FILE"  "$SYNC_CLIENT_LFTP_FILE"

if [ "$?" == "0" ] ; then
	if [  "$SYNC_CLIENT_REPEAT" == "yes" ]; then 
		until [ "$SYNC_CLIENT_REPEAT" != "yes" ] 
		do 	
			date=$(date +"%b %d %T")
			echo "$date Launching client" 
			ftp_lftp_run_lftp  "$SYNC_CLIENT_LFTP_FILE"
			date=$(date +"%b %d %T") 
			echo "$date Mirror process waiting $SYNC_CLIENT_REPEAT_TIME for restart"
			sleep $SYNC_CLIENT_REPEAT_TIME
		done
	else
		ftp_lftp_run_lftp  "$SYNC_CLIENT_LFTP_FILE"
	fi
else
	echo  ".. error during generation, exit."
 	exit 255
fi	
