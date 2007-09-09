#!/bin/sh
for po in po/*.po
do
  LANG=`echo $po | sed 's#po/##;s#\.po$##'`
  mkdir -p $LANG/LC_MESSAGES
  echo -n "$po ... "
  msgfmt -v --statistic -o $LANG/LC_MESSAGES/cacti.mo $po
done
