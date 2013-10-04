#!/bin/sh

##  Script for utilizise lftp client for syncing
#     script needs a a config file and generates upon that 
#     a lftp-script and runs the client

DEBUG=${DEBUG:=false}

PIRATEBOX_FOLDER=${PIRATEBOX_FOLDER:=/opt/piratebox}

LFTP_HELPER_FUNCTIONS="$PIRATEBOX_FOLDER/lib/lftp_helper.sh"

SYNC_CLIENT_LFTP_FILE="$PIRATEBOX_FOLDER/tmp/lftp_sync.command"
SYNC_CLIENT_CONFIG_FILE="$PIRATEBOX_FOLDER/conf/ftp/ftp_sync_client.conf"

if [ -e $LFTP_HELPER_FUNCTIONS ] ; then
	. $LFTP_HELPER_FUNCTIONS
else
	echo "Can't load hhelper functions at $ LFTP_HELPER_FUNCTIONS"
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

if [ "$SYNC_CLIENT_STATIC_IP" == "empty" ] ; then
	echo "IP is not set, exiting."
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


ftp_lftp_generate_command_member "$SYNC_CLIENT_SCHEMA_FILE"  "$SYNC_CLIENT_LFTP_FILE"

if [ "$?" == "0" ] ; then
	if [  "$SYNC_CLIENT_REPEAT" == "yes" ]; then 
		until [ "$SYNC_CLIENT_REPEAT" != "yes" ] 
		do 	
			[[ $DEBUG ]] && echo "Launching client" 
			ftp_lftp_run_lftp  "$SYNC_CLIENT_LFTP_FILE"
			sleep $SYNC_CLIENT_REPEAT_TIME
		done
	else
		ftp_lftp_run_lftp  "$SYNC_CLIENT_LFTP_FILE"
	fi
else
	echo  ".. error during generation, exit."
 	exit 255
fi	
