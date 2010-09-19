#!/bin/sh

LIME_FETCH_URL=http://trac.symfony-project.org/browser/tools/lime/trunk/lib/lime.php?format=txt
LIME_TEST=`dirname $0`/test/lib/lime.php
if [ ! -e $LIME_TEST ];
then
    wget -O $LIME_TEST $LIME_FETCH_URL
fi
