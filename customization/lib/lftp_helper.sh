#!/bin/sh

DEBUG=${DEBUG:=false}


ftp_lftp_generate_command_member(){
	local client_schema=$1 ; shift ;
	local lftp_command_file=$1 ; shift ;

	if [ ! -e  $client_schema ] ; then
		echo "FTP Client schema file $client_schema not available"
		return 1
	fi

	sed  "s|###SYNC_LFTP_CONFIG###|$SYNC_CLIENT_CONFIG|" $client_schema >  $lftp_command_file
	sed  "s|###SYNC_PORT###|$SYNC_CLIENT_STATIC_PORT|" -i  $lftp_command_file
	sed  "s|###SYNC_IP###|$SYNC_CLIENT_STATIC_IP|" -i  $lftp_command_file
	sed  "s|###SYNC_USER###|$SYNC_CLIENT_STATIC_USER|" -i $lftp_command_file 
	sed  "s|###SYNC_PASSWORD###|$SYNC_CLIENT_STATIC_PASSWORD|" -i  $lftp_command_file

	sed  "s|###SYNC_REMOTE_FOLDER###|$SYNC_CLIENT_REMOTE_FOLDER|" -i $lftp_command_file
	sed  "s|###SYNC_LOCAL_FOLDER###|$SYNC_CLIENT_LOCAL_FOLDER|" -i $lftp_command_file

	return 0 
}


ftp_lftp_run_lftp(){
	local lftp_command_file=$1 ; shift ;
	lftp -f $lftp_command_file 

	return $?
}
