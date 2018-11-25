#!/bin/sh

TARGET_FILE=$0
cd `dirname $TARGET_FILE`
LOC_DIR=`pwd`

sh ../lib/IfwPsn/Wp/Plugin/Cli/Executables/script.sh $LOC_DIR $@
