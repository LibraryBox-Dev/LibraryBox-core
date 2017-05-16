#!/bin/sh

# Setting up a few things during BootUp.
#    - Enable content folder on /opt/piratebox/share/content
#    - Swap WWW folder with LibraryBox ones
#    

# Hook for modifcation stuff before 
#          piratebox/bin/install  ... part2 
# is run.

if [ !  -f $1 ] ; then
  echo "Config-File $1 not found..."
  exit 255
fi

#Load config
. $1

# You can uncommend this line to see when hook is starting:
 echo "------------------ Running $0 ------------------"

## Exchange WWWW
echo "Doing www folder exchange..."
mv    $WWW_FOLDER $PIRATEBOX_FOLDER/www_old
mv    $PIRATEBOX_FOLDER/www_librarybox $WWW_FOLDER
cp -rv  $PIRATEBOX_FOLDER/www_old/cgi-bin $WWW_FOLDER/cgi-bin
cp -v  $PIRATEBOX_FOLDER/www_old/generate_204 $WWW_FOLDER

$PIRATEBOX_FOLDER/bin/install_piratebox.sh \
    $1 \
    hostname librarybox.lan
test ! -e $WWW_FOLDER/index.html && \
    ln -f $WWW_FOLDER/redirect.html $WWW_FOLDER/index.html 


$PIRATEBOX_FOLDER/bin/ftp_enable.sh generate

