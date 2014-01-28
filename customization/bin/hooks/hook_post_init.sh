#!/bin/sh

# ---- TEMPLATE ----

# Hook for modifcation stuff right after
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
 echo "Creating predefined folders ..."
 
 mkdir -p $SHARE_FOLDER/Shared/text
 mkdir -p $SHARE_FOLDER/Shared/audio
 mkdir -p $SHARE_FOLDER/Shared/video
 mkdir -p $SHARE_FOLDER/Shared/software

