#!/bin/sh
for po in po/*.po
do
    echo -n "$po ... "
    msgmerge --sort-output $po po/cacti.pot -o $po
done
