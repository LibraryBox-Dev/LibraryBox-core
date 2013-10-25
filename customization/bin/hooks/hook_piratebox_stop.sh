#!/bin/sh

# ---- TEMPLATE ----

# Runs on every Stop before anything is stopped
#  get config file 

if [ !  -f $1 ] ; then
  echo "Config-File $1 not found..."
  exit 255
fi

#Load config
. $1

# You can uncommend this line to see when hook is starting:
 echo "------------------ Running $0 ------------------"


if [ -e  "$PROFTPD_PID" ]; then
	echo "Stopping proftpd..."
	kill $(cat $PROFTPD_PID)
	echo $?
	rm $PROFTPD_PID
fi

if [ -e  "$PROFTPD_SYNC_PID" ]; then
        echo "Stopping Sync proftpd..."
        kill $(cat $PROFTPD_SYNC_PID)
        echo $?
        rm $PROFTPD_SYNC_PID
fi



if [ -e "$FTP_SYNC_CLIENT_PID" ] ; then
	echo "Killing FTP Sync client..."
	kill $(cat $FTP_SYNC_CLIENT_PID)
	echo $?
	rm $FTP_SYNC_CLIENT_PID
fi

