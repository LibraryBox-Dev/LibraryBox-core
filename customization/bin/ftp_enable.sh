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

if [ $IS_OPENWRT ] ; then
	MY_HOSTNAME=`uci get system.@system[0].hostname`
else
	MY_HOSTNAME=`hostname`
fi

FTP_CONF_FOLDER=$PIRATEBOX_CONF_FOLDER/ftp
BASIC_FTP_CONFIG=$FTP_CONF_FOLDER/ftp.conf
FTP_SYNC_CLIENT_CONFIG=$FTP_CONF_FOLDER/ftp_sync_client.conf

## Schema-files
SCHEMA_DEAMON_CONF=$FTP_CONF_FOLDER/proftpd.conf.schema
SCHEMA_SYNC_CONF=$FTP_CONF_FOLDER/proftpd_sync.conf.schema
SCHEMA_ANON_CONF=$FTP_CONF_FOLDER/anon_access.conf.schema


##----------------
# Load known configuration files

. $PIRATEBOX_CONF  #This includes the hook-file too
# Used Vars
#   FTP_ENABLED
#   PROFTPD_PID  
#   PROFTPD_CONFIG_FILE   < - OUPUT_PROFTPD_CONFIG for 
#   FTP_SYNC_ENABLED
#   PROFTPD_SYNC_PID  
#   PROFTPD_SYNC_CONFIG_FILE   
#   SHARE_FOLDER          < - ??
#   LIGHTTPD_USER
#   LIGHTTPD_GROUP
#   IPV6_ENABLE           <-  yes/no
#   HOST		  <- hostname

. $BASIC_FTP_CONFIG
#  uses
#	ADMIN_ACCESS
#	BOX_USER
#	SYNC_PORT
#	SYNC_FOLDER
#	ENABLE_ANON
#	ANON_FOLDER


. $FTP_SYNC_CLIENT_CONFIG

##---------------
## Final configuration files
#
OUTPUT_DAEMON_CONF=$FTP_CONF_FOLDER/proftpd.conf
OUTPUT_SYNC_CONF=$FTP_CONF_FOLDER/proftpd_sync.conf
OUTPUT_ANON_CONF=$FTP_CONF_FOLDER/anon_access.conf
#
PERFORMANCE_CONFIG=$FTP_CONF_FOLDER/proftpd_limits.conf


print_line() {

	echo "------------------------------------------------------"

}

print_current_config() {
	print_line
	echo "   FTP enabled                : $FTP_ENABLED "
	echo "   Admin access               : $ADMIN_ACCESS "
	echo "   Special SYNC access        : $FTP_SYNC_ENABLED "
	echo "   SYNC Port                  : $SYNC_PORT "
	echo "   Anonymous login possible   : $ENABLE_ANON "
	echo " -- "
	echo "   FTP Synchronisation active : $FTP_SYNC_CLIENT_ENABLED "
	echo "   FTP Sync hostname          : $SYNC_CLIENT_HOST "
	echo "   FTP Sync password          : $SYNC_CLIENT_STATIC_PASSWORD "
	echo " "
	echo " -- "
	echo "   The hostname this box is   :" $MY_HOSTNAME 
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

	local l_scoreboard=""

	#Save the scoreboard in memory on OpenWRT

	if [ $IS_OPENWRT ] ; then
		l_scoreboard="/tmp/log/proftpd.scoreboard"
	else
		l_scoreboard=$PIRATEBOX_FOLDER"/tmp/proftpd.scoreboard"
	fi
	
	#normal server for admin and anon
	_generate_proftpd_config_ "$SCHEMA_DEAMON_CONF" "$OUTPUT_DAEMON_CONF" "$PROFTPD_PID" "$l_scoreboard"

	#Sync Server with one slot only
	l_scoreboard=$l_scoreboard".sync"
	_generate_proftpd_config_ "$SCHEMA_SYNC_CONF" "$OUTPUT_SYNC_CONF" "$PROFTPD_SYNC_PID" "$l_scoreboard"	

	#ANON Stuff
	sed "s|#####ANON-FOLDER#####|$ANON_FOLDER|"  $SCHEMA_ANON_CONF > $OUTPUT_ANON_CONF


	echo "..done"
}

# Because we have to run two daemons, because one gets confused about
#  configuration, we have to generate 2 config files , which looks nearly the same
_generate_proftpd_config_(){
	local schema_file=$1 ; shift 
	local config_file=$1 ; shift
	local l_pid=$1 ; shift 
	local l_scoreboard=$1 ; shift


	local l_ipv6="no"
	local l_allow_admin=""
	local l_allow_anon=""
	local l_perf=""

 	l_allow_admin="AllowUser  $BOX_SYSTEM_USER"
	l_allow_anon="Include $OUTPUT_ANON_CONF \n"
	[ "$IPV6_ENABLE" = "yes" ] &&  l_ipv6="on"
	l_perf="Include $PERFORMANCE_CONFIG \n"

	sed  "s|#####HOSTNAME#####|$HOST|"  $schema_file > $config_file

	sed  "s|#####IPV6#####|$l_ipv6|" 	-i  $config_file
	sed  "s|#####BOX_USER#####|$BOX_USER|" 	-i $config_file
	sed  "s|#####ADMIN_ACCESS#####|$l_allow_admin|" -i $config_file
	sed  "s|#####SCOREBOARD_PATH#####|$l_scoreboard|" -i $config_file
	sed  "s|#####INCLUDE_PERFORMANCE#####|$l_perf|" -i $config_file
	sed  "s|#####INCLUDE_ANON_ACCESS#####|$l_allow_anon|" -i $config_file
	sed  "s|#####PID#####|$l_pid|" -i $config_file
	sed  "s|#####ADMIN_FOLDER#####|$ADMIN_FOLDER|" -i  $config_file
	sed  "s|#####BOX_SYSTEM_USER#####|$BOX_SYSTEM_USER|" -i $config_file
	sed  "s|#####BOX_SYSTEM_GROUP#####|$BOX_SYSTEM_GROUP|" -i $config_file

	sed  "s|#####SYNC-PORT#####|$SYNC_PORT|" -i $config_file
	sed  "s|#####SYNC-FOLDER#####|$SYNC_FOLDER|" -i $config_file
	sed  "s|#####SYNC_SYSTEM_USER#####|$SYNC_SYSTEM_USER|" -i $config_file

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
		("FTP_SYNC_ENABLED") 	config_file=$PIRATEBOX_HOOK_CONF ;;
		("FTP_SYNC_CLIENT_ENABLED") 	config_file=$PIRATEBOX_HOOK_CONF ;;
		(*)			config_file=$BASIC_FTP_CONFIG ;;
	esac

	sed "s|$func=\"$func_content\"|$func=\"$new\"|" -i $config_file  
	
	. $config_file

}


_change_value_(){
	local varname=$1

	local new=$(eval "echo \$${varname}")
	local old=$new

        read -p " New value for $varname : " new

        case $varname in
		("SYNC_CLIENT_HOST")	        config_file=$FTP_SYNC_CLIENT_CONFIG ;;
		("SYNC_CLIENT_STATIC_PASSWORD")	config_file=$FTP_SYNC_CLIENT_CONFIG ;;
		(*)	config_file=$BASIC_FTP_CONFIG ;;
	esac

	sed "s|$varname=\"$old\"|$varname=\"$new\"|" -i $config_file

	. $config_file
}


mainmenu() {
	while true
	do
		print_line
		echo "   Current configuration:"
		print_current_config
		echo "  1 -  Enable / Disable FTP "
		echo "  2 -  Enable / Disable Admin Access "
		echo "  3 -  Enable / Disable Sync Master "
		echo "  4 -  Enable / Disable Anonymous Access "
		echo "  5 -  Set password for Sync Master "
		echo "  6 -  Set password for Admin Access "
		echo " "
		echo "  7 -  Enable Sync Client "
		echo "  8 -     Client host  "
		echo "  9 -     Client password  "
		echo " "
		echo " Enter h and a number for help about the topic. For example, h8 for Client host help"
		echo " Every other button is a clean exit. "
		echo " "
		read -p " Choose an option: " option

		case $option in 
			("1")	_toggle_ "FTP_ENABLED"   ;;
			("2")   _toggle_ "ADMIN_ACCESS" ;;
			("3")   _toggle_ "FTP_SYNC_ENABLED"  ;;
			("4")   _toggle_ "ENABLE_ANON"  ;;
			("5")	echo "System-User for Sync Access is $SYNC_SYSTEM_USER"  && passwd $SYNC_SYSTEM_USER ;;
			("6")   echo "System-User for Admin access is $BOX_SYSTEM_USER" &&  passwd $BOX_SYSTEM_USER ;;
			("7")   _toggle_ "FTP_SYNC_CLIENT_ENABLED"  ;;
		   	("8")   _change_value_ "SYNC_CLIENT_HOST"  ;;
		   	("9")   _change_value_ "SYNC_CLIENT_STATIC_PASSWORD" ;;
			("h1")  print_help_ftp ;;
			("h2")  print_help_admin ;;
			("h3")  print_help_sync ;;
			("h4")  print_help_anon ;;
			(*)	_exit_menu_ ;;
		esac
		option=""
	done

}




if [ "$1" = "generate" ] ; then
	generate
else
	mainmenu
fi

