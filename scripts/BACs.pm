package BACs;

use SeqBin;
use strict;
use warnings;

sub new {
    my ($class) = @_;
    my $self = {
    	name => "",
    	bins => [new SeqBin()]
    };
   
    bless $self, $class;
    return $self;
}

sub setName {
	my ($self, $name) = @_;
	$self->{name} = $name;
}

sub getName {
	my ($self) = @_;
	return $self->{name};
}

sub addSeq {
	my ($self, $start, $end, $strand, $mu) = @_;
	if(!${$self->{bins}}[@{$self->{bins}} - 1]->addSeq($start, $end, $strand, $mu)) {
		my $sb = new SeqBin();
		$sb->addSeq($start, $end, $strand, $mu);
		push(@{$self->{bins}}, $sb);
	}
}

sub toString {
	my ($self, $minCount, $maxLength, $totalReads, $pileups) = @_;
	foreach my $bin (@{$self->{bins}}) {
		if($bin->getLength() <= $maxLength && $bin->getCount() >= $minCount) {
		#	my $normal = 10000000 * $bin->getCount() / $totalReads;
		#	if($normal > 1000) {
				my $insertion = 0;
				if(defined(${$pileups}{$self->{name}})) {
					$insertion = ${$pileups}{$self->{name}}->getInsertion($bin->getStart(), $bin->getEnd());
				}
				print $self->{name} . "\t" . $bin->getStart() . "\t" . $bin->getEnd() . "\t" . $bin->getCount() . "\t" . $bin->getInsertStart() . "\t" . $bin->getInsertEnd() . "\t$insertion\n";
		#	}
		}
	}
}

1;
