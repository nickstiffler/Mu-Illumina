package PileupChr;

use strict;
use warnings;

sub new {
	my ($class) = @_;
	my $self = {};
	
	$self->{pileups} = ();

	bless $self, $class;
	return $self;
}

sub addPileup {
	my ($self, $pos, $pu) = @_;
	my $fwd_count = 0;
	$fwd_count++ while($pu =~ m/[.ACGTN]/g);
	my $rev_count = 0;
	$rev_count++ while($pu =~ m/[,acgtn]/g);
	
	my $value = $fwd_count * $rev_count;
	#if($value == 0) {
	#	$value = $fwd_count + $rev_count;
	#}
	#my $depth = $fwd_count + $rev_count;
	#my $value = (($fwd_count / $depth) * 100.0) * (($rev_count / $depth) * 100.0);
	#print "$depth\n";
	if($value > 0) {
		${$self->{pileups}}{$pos} = $value;
	}
}

sub getInsertion {
	my ($self, $start, $end) = @_;
	my $insertion_pos = 0;
	my $value = 0;
	for(my $i = $start; $i <= $end; $i++) {
	#	foreach my $pos (keys(%{$self->{pileups}})) {
		
	#	if($pos < $start) {
	#		next;
	#	}
	#	if($pos > $end) {
	#		last;
	#	}
	#	print ${$self->{pileups}}{$pos} . "\n";
		if(defined(${$self->{pileups}}{$i}) && ${$self->{pileups}}{$i} > $value) {
			$insertion_pos = $i;
			$value = ${$self->{pileups}}{$i};
		}
	}

	return $insertion_pos;
}

1;
