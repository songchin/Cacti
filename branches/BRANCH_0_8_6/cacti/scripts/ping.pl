#!/usr/bin/perl

open(PROCESS, "ping -c 1 $ARGV[0] | grep icmp_seq |");
$ping = <PROCESS>;
close(PROCESS);
$ping =~ m/(.*time=)(.*) (ms|usec)/;

if ($2 == "") {
	print "U"; 		# avoid cacti errors, but do not fake rrdtool stats
}elsif ($3 eq "usec") {
	print $2/1000;	# re-calculate in units of "ms"
}else{
	print $2;
}