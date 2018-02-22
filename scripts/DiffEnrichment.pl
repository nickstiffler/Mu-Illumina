#!/usr/bin/perl -w

open(ER1, $ARGV[0]);
open(ER2, $ARGV[1]);

my @er1 = ();
while(<ER1>) {
	my @line = split("\t", $_);
	my @positions = ($line[1], $line[2], $line[0]);
	push(@er1, \@positions);
}

while(<ER2>) {
	my $er2_diff = 0;
	my @line = split("\t", $_);
	foreach my $pos (@er1) {
#	while(my ($bac, $pos) = each(%er1)) {
		if(${$pos}[2] eq $line[0]) {
			#print $line[1] . " " . $bac . " " . $line[2] . " " . $line[3] . " " . ${$pos}[0] . " " . ${$pos}[1] . ": ";
			if(($line[1] >= ${$pos}[0] && $line[1] <= ${$pos}[1]) ||
			($line[2] >= ${$pos}[0] && $line[2] <= ${$pos}[1]) || 
			($line[1] <= ${$pos}[0] && $line[2] >= ${$pos}[1]))
			{
			#	print $_;
				#print "passed\n";
				$er2_diff = 1;
			}
		}
	}
	if($er2_diff == 0) {
		print $_;
	}
}


