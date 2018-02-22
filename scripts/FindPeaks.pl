#!/usr/bin/perl -w

#use lib $ENV{"HOME"} . "/perl_lib/";
use lib $ENV{"HOME"} . "/Documents/mu-illumina-data/scripts";
use BACs;
use PileupChr;
use strict;
use warnings;

if(@ARGV < 3) {
	die("Usage: ./FindPeaks.pl <sam file> <max window> <min hits>");
}

my $file = $ARGV[0];
my $window = $ARGV[1];
my $num_hits = $ARGV[2];

open(HITS, $file);

my @bacs = ();
my $bac = new BACs();
my $count = 0;
while(<HITS>) {
	if(/^@/) {
		next;
	}
	if(/4\t\*\t0\t0\t\*\t\*\t0\t0/) {
		next;
	}
	my @line = split("\t", $_);
	if($bac->getName() eq "") {
		$bac->setName($line[2]);
	}
	if($line[2] ne $bac->getName()) {
		push(@bacs, $bac);
		$bac = new BACs();
		$bac->setName($line[2]);
	}
	#my $mu = 0;
	#if($line[1] == 0 && $line[9] =~ /GAGAT/) {
	#	$mu = index($line[9], "GAGAT");
	#	print $line[3] . " " . $line[9] ."\n";
	#} elsif($line[1] == 16 && 
	 # if($line[9] =~ /ATCTC/) {
	my $mu = -1;
	if($line[1] == 0) {
	  $mu = index($line[9], "GAGAT");
	} elsif($line[1] == 16) {
	  $mu = index($line[9], "ATCTC");
	}
	#my $mu = index($line[9], "ATCTC");
	#	print $line[1] . " " . $line[3] . " " . $line[9] ."\n";
	#}
	$count++;
	$line[5]=~ /(\d+)M/;
	$bac->addSeq($line[3], ($line[3] + $1), $line[1], $mu);
}
push(@bacs, $bac);

my %pus = ();
open(PU, $ARGV[3]);
while(<PU>) {
	my @line = split("\t", $_);
	if(!defined($pus{$line[0]})) {
		$pus{$line[0]} = new PileupChr();
	}
	$pus{$line[0]}->addPileup($line[1], $line[4]);
}


foreach $bac (@bacs) {
	print $bac->toString($num_hits, $window, $count, \%pus);
}
