#!/usr/bin/perl

#get load avg for 1;5;15 min
$avg = `uptime`;

#   9:36pm  up 15 days, 11:37,  2 users,  load average: 0.14, 0.13, 0.10

$avg =~ s/^.*:\s(\d+\.\d{2}),?\s(\d+\.\d{2}),?\s(\d+\.\d{2})$//;

print "1min:$1 5min:$2 15min:$3";
