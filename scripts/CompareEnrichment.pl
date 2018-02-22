#!/usr/bin/perl -w

open(ER1, $ARGV[0]);
open(ER2, $ARGV[1]);

my @er1 = ();
while(<ER1>) {
	my @line = split("\t", $_);
	my @positions = ($line[1], $line[2], $line[0], $line[3]);
	push(@er1, \@positions);
}
my $prev = "";
while(<ER2>) {
	my @line = split("\t", $_);
	foreach my $pos (@er1) {
#	while(my ($bac, $pos) = each(%er1)) {
		if(${$pos}[2] eq $line[0]) {
			#print $line[1] . " " . $bac . " " . $line[2] . " " . $line[3] . " " . ${$pos}[0] . " " . ${$pos}[1] . ": ";
			if(($line[1] >= ${$pos}[0] && $line[1] <= ${$pos}[1]) ||
			 ($line[2] >= ${$pos}[0] && $line[2] <= ${$pos}[1]) || 
			 ($line[1] <= ${$pos}[0] && $line[2] >= ${$pos}[1]))
			{
			#	if($line[0] ne $prev) {
				print $line[0] . "\t" . min($line[1], ${$pos}[0]) . "\t" . max($line[2], ${$pos}[1]) . "\t" .($line[3] + ${$pos}[3]) . "\n";
			#	}
				$prev = $line[0];
			}
		}
	}
}

sub min {
	my $one = shift;
	my $two = shift;

	if($one <= $two) {
		return $one;
	}
	return $two;
}

sub max {
	my $one = shift;
	my $two = shift;

	if($one >= $two) {
		return $one;
	}
	return $two;
}
