#!/usr/bin/perl

#get load avg for 1;5;15 min
#uptime reports only those intervals! (gandalf)
open(PROCESS,"uptime |");
$avg = <PROCESS>;
$avg =~ s/.*:\s*//;
close(PROCESS);

if ($ARGV[0] eq "1") {
	$avg = `echo "$avg" | awk '\{print \$1 \}'`;
}

if ($ARGV[0] eq "5") {
	$avg = `echo "$avg" | awk '\{print \$2 \}'`;
}

if ($ARGV[0] eq "15") {
	$avg = `echo "$avg" | awk '\{print \$3 \}'`;
}

chomp $avg;
$avg =~ s/,//g;
$avg =~ s/\n//;
print $avg;
