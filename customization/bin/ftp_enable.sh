#!/bin/sh

##  Script by Matthias Strubel (c) 2013 GPL3  <matthias.strubel@aod-rpg.de>
##
##     Helps users to setup their FTP Server (and user+passwords


### Constants

PIRATEBOX_FOLDER=/opt/piratebox
PIRATEBOX_CONF_FOLDER=$PIRATEBOX_FOLDER/conf
PIRATEBOX_CONF=$PIRATEBOX_CONF_FOLDER/piratebox.conf
PIRATEBOX_HOOK_CONF=$PIRATEBOX_CONF_FOLDER/hook_custom.conf  ##Here is the configuration for enabling FTP during startup stored

IS_OPENWRT=' -e /etc/openwrt_version '

FTP_CONF_FOLDER=$PIRATEBOX_CONF_FOLDER/ftp
BASIC_FTP_CONFIG=$FTP_CONF_FOLDER/ftp.conf


## Schema-files
SCHEMA_DEAMON_CONF=$FTP_CONF_FOLDER/proftpd.conf.schema
SCHEMA_SYNC_CONF=$FTP_CONF_FOLDER/sync_access.conf.schema
SCHEMA_ANON_CONF=$FTP_CONF_FOLDER/anon_access.conf.schema

##----------------
# Load known configuration files

. $PIRATEBOX_CONF  #This includes the hook-file too
# Used Vars
#   FTP_ENABLED
#   PROFTPD_PID  
#   PROFTPD_CONFIG_FILE   < - OUPUT_PROFTPD_CONFIG for 
#   SHARE_FOLDER          < - ??
#   LIGHTTPD_USER
#   LIGHTTPD_GROUP
#   IPV6_ENABLE           <-  yes/no
#   HOST		  <- hostname

. $BASIC_FTP_CONFIG
#  uses
#	ADMIN_ACCESS
#	BOX_USER
#	ENABLE_SYNC
#	SYNC_PORT
#	SYNC_FOLDER
#	ENABLE_ANON
#	ANON_FOLDER

##---------------
## Final configuration files
#
OUTPUT_DAEMON_CONF=$FTP_CONF_FOLDER/proftpd.conf
OUTPUT_SYNC_CONF=$FTP_CONF_FOLDER/sync_access.conf
OUTPUT_ANON_CONF=$FTP_CONF_FOLDER/anon_access.conf


print_line() {

	echo "------------------------------------------------------"

}

print_current_config() {
	print_line
	echo "   FTP enabled             : $FTP_ENABLED "
	echo "   Admin access            : $ADMIN_ACCESS "
	echo "   Special SYNC access     : $ENABLE_SYNC "
	echo "   SYNC Port               : $SYNC_PORT "
	echo "   Anonymous login possible: $ENABLE_ANON "
	echo " "
	print_line
}

print_help_ftp(){
	echo "no help currently available"
}

print_help_anon(){
	print_line
	echo " Anonymous access is a password-less FTP Login using user 'ftp' or 'anonymous' , which allowes users to get in an easy way to download Files"
	echo " Anonymous access is restricted to maximal 2 Clients, and one Client per Host to ensure System stability on OpenWRT"
	echo " Anonymous Users can't upload."
	echo " You can modify this values by hand editing $SCHEMA_ANON_CONF "
	echo ""
	print_line
}

print_help_sync(){
	print_line
	echo " Sync access is on specific daemon running on a separate Port, you can choose"
	echo " This feature is designed for ppl who want to synchronize their Boxes like a private cloud from one. The client downloads the data, no upload happens"
	echo " The user behind the sync-access has an own password, other than admin and has to be set for successful access."
	echo " Sync-Access is restricted to one slot for downloading and has the same TransferSpeed limits like the other accounts. "
	print_line
}

print_help_admin(){
	print_line
	echo " Admin access enables a full-control access to your Box' USB Stick for uploadind, downloading and deleting files"
	echo ""
	print_line
}

# Generates all config files based upon the configuration
generate() {
	echo -n "Generating FTP Configuration"

	local l_allow_admin=""
	local l_scoreboard=""
	local l_allow_anon=""
	local l_allow_sync=""
	local l_ipv6="no"

	[ "$IPV6_ENABLE" = "yes" ] &&  l_ipv6="on"


	#Save the scoreboard in memory on OpenWRT

	if [ $IS_OPENWRT ] ; then
		l_scoreboard="/tmp/log/proftpd.scoreboard"
	else
		l_scoreboard=$PIRATEBOX_FOLDER"/tmp/proftpd.scoreboard"
	fi

	l_allow_sync="Include $OUTPUT_SYNC_CONF \n"
	l_allow_anon="Include $OUTPUT_ANON_CONF \n"
 	l_allow_admin="AllowUser  $BOX_USER"

	sed  "s|#####HOSTNAME#####|$HOST|"  $SCHEMA_DEAMON_CONF > $OUTPUT_DAEMON_CONF

	sed  "s|#####IPV6#####|$l_ipv6|" 	-i  $OUTPUT_DAEMON_CONF
	sed  "s|#####BOX_USER#####|$BOX_USER|" 	-i $OUTPUT_DAEMON_CONF
	sed  "s|#####ADMIN_ACCESS#####|$l_allow_admin|" -i $OUTPUT_DAEMON_CONF
	sed  "s|#####SCOREBOARD_PATH#####|$l_scoreboard|" -i $OUTPUT_DAEMON_CONF
	sed  "s|#####INCLUDE_ANON_ACCESS#####|$l_allow_anon|" -i $OUTPUT_DAEMON_CONF
	sed  "s|#####INCLUDE_SYNC_ACCESS#####|$l_allow_sync|" -i $OUTPUT_DAEMON_CONF
	sed  "s|#####PID#####|$PROFTPD_PID|" -i $OUTPUT_DAEMON_CONF
	sed  "s|#####ADMIN_FOLDER#####|$ADMIN_FOLDER|" -i  $OUTPUT_DAEMON_CONF
	sed  "s|#####BOX_SYSTEM_USER#####|$BOX_SYSTEM_USER|" -i $OUTPUT_DAEMON_CONF

	#SYNC Stuff
	sed  "s|#####HOSTNAME#####|$HOST|" $SCHEMA_SYNC_CONF  > $OUTPUT_SYNC_CONF
	sed  "s|#####SYNC-PORT#####|$SYNC_PORT|" -i $OUTPUT_SYNC_CONF
	sed  "s|#####SYNC-FOLDER#####|$SYNC_FOLDER|" -i $OUTPUT_SYNC_CONF
	sed  "s|#####SYNC_SYSTEM_USER#####|$SYNC_SYSTEM_USER|" -i $OUTPUT_SYNC_CONF

	#ANON Stuff
	sed "s|#####ANON-FOLDER#####|$ANON_FOLDER|"  $SCHEMA_ANON_CONF > $OUTPUT_ANON_CONF


	echo "..done"
}

_exit_menu_() {
	generate
	exit 0
}

_toggle_() {
	local func=$1

	#on default always no
	local new="no"
	local func_content=$(eval "echo \$${func}")

	if [ "$func_content" = "no" ] ; then
		new="yes"
	fi

	local config_file=""

	case $func in
		("FTP_ENABLED") 	config_file=$PIRATEBOX_HOOK_CONF ;;
		(*)			config_file=$BASIC_FTP_CONFIG ;;
	esac

	sed "s|$func=\"$func_content\"|$func=\"$new\"|" -i $config_file  
	
	. $config_file

}

mainmenu() {
	while true
	do
		print_line
		echo "   Current configuration:"
		print_current_config
		echo "  1 -  Enable / Disable FTP during Startup (Toggle) "
		echo "  2 -  Enable / Disable Admin access "
		echo "  3 -  Enable / Disable sync-setup"
		echo "  4 -  Enable / Disable Anonymous access "
		echo "  5 -  Set password for Sync-Access "
		echo "  6 -  Set password for admin-access "
		echo " "
		echo " With choosing hn like h1 , you get some help about the topic"
		echo " Every other button is a clean exit. "
		echo " "
		read -p " Coose an option: " option

		case $option in 
			("1")	_toggle_ "FTP_ENABLED"   ;;
			("2")   _toggle_ "ADMIN_ACCESS" ;;
			("3")   _toggle_ "ENABLE_SYNC"  ;;
			("4")   _toggle_ "ENABLE_ANON"  ;;
			("5")	passwd $SYNC_SYSTEM_USER ;;
			("6")   passwd $BOX_SYSTEM_USER ;;
			("h1")  print_help_ftp ;;
			("h2")  print_help_admin ;;
			("h3")  print_help_sync ;;
			("h4")  print_help_anon ;;
			(*)	_exit_menu_ ;;
		esac
		option = ""
	done

}


if [ "$1" = "generate" ] ; then
	generate
else
	mainmenu
fi

