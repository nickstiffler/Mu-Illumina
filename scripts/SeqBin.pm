package SeqBin;

use strict;
use warnings;

my $gap_length = 100;

sub new {
  my ($class) = @_;
  my $self = {
    count => 0,
    start => 0,
    end => 0,
    #mu => [],
    insert_start => 0,
    insert_end => 0
  };
    
  bless $self, $class;
  return $self;
}

sub addSeq {
  my ($self, $start, $end, $strand, $mu) = @_;
  if($self->{count} > 0) {
    if($self->{start} <= $end && ($self->{end} + $gap_length) >= $start) {
      $self->{count}++;
      if($end > $self->{end}) {
        $self->{end} = $end;
      }
      if($mu != -1 && $self->{insert_start} == 0) {
	if($strand == 0) {
	  $self->{insert_end} = $mu + $start;
	  #push(@{$self->{mu}}, $mu + $start);
	} elsif($strand == 16 && $self->{insert_end} != 0) {
	  $self->{insert_start} = $mu + $start;
	#  push(@{$self->{mu}}, $end - ($mu + $start) + $start);
	}
      }
    } else {
      return 0;
    }
} else {
    $self->{count} = 1;
    $self->{start} = $start;
    $self->{end} = $end;
  }
  return 1;
}

sub getCount {
  my ($self) = @_;
  return $self->{count};
}

sub getLength {
  my ($self) = @_;
  return ($self->{end} - $self->{start} + 1);
}

sub getStart {
  my ($self) = @_;
  return $self->{start};
}

sub getEnd {
  my ($self) = @_;
  return $self->{end};
}

sub getInsertStart {
  my ($self) = @_;
  #if(@{$self->{mu}} == 0) {
  #  return 0;
  #}
  #my $total = 0;
  #foreach my $start (@{$self->{mu}}) {
  #  $total += $start;
  #}
  return $self->{insert_start};
  #return int(($total / @{$self->{mu}}) - 10.5);
}

sub getInsertEnd {
  my ($self) = @_;
  # if(@{$self->{mu}} == 0) {
  #  return 0;
  #}
  #my $total = 0;
  #foreach my $start (@{$self->{mu}}) {
  #  $total += $start;
  #}
  #return int(($total / @{$self->{mu}}) + 10.5);
  return $self->{insert_end};
}

1;
