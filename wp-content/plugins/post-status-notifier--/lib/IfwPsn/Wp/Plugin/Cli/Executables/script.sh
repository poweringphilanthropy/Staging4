#!/bin/sh
# echo $@
# exit

TARGET_FILE=$0
cd `dirname $TARGET_FILE`
# LOC_DIR=`pwd`
PHYS_DIR=`pwd -P`
cd $PHYS_DIR

php -f script.php -- $@