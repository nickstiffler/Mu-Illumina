#!/usr/bin/perl -w
#
# Nicholas Stiffler
# Barkan Lab
# Institute of Molecular Biology
# University of Oregon
#
# Mu-Illumina pipeline
# May 14, 2010
#

use strict;
use PerlIO::gzip;
#use DBI;

my %barcodes = ();
my $wd = $ENV{"HOME"} . "/Documents/mu-illumina-data";
my $mu_reference = $wd . "/mu/mu";
my $maize_reference = $wd . "/Zea_mays.AGPv4/Zea_mays.AGPv4";
my $rice_db = "/home/stiffler/workspace/roz/rice";
my $bg_clusters = $wd . "/background/background_v4.peaks";
#my $barcode_length = 0;
my $bustard = "";
my $lane = 0;
my $out = "";
my $run_id = 0;
my $procs = 16;

#my $dbh = 

parse_command_line();

print STDERR "Fetching sequences...\n";
fetch_bustard();
open(LOG, ">$out/stats.txt");
 print LOG "Barcode\tTotal reads\tMu\tMaize\tClusters\n";

foreach my $barcode (keys %barcodes) {
 print STDERR "[ $barcode ]\n";
 print STDERR "Running bowtie with mu\n";
 run_bowtie_mu($barcode);
 print STDERR "Running bowtie with maize\n";
 run_bowtie_maize($barcode);
 print STDERR "Identifying clusters\n";
 run_find_peaks($barcode);
 #print STDERR "Identifying ortholog\n";
  #run_blast($barcode);
  print LOG "$barcode\t" . $barcodes{$barcode}{'reads'} . "\t" . $barcodes{$barcode}{'mu'} . "\t" . 
			  $barcodes{$barcode}{'maize'} . "\t" . $barcodes{$barcode}{'clusters'} . "\n";
}

sub run_bowtie_mu {
  my $barcode = shift;
  my $mu = `bowtie2 -p $procs --un $out/$barcode/$barcode\_nomu.fq -x $mu_reference $out/$barcode/$barcode.fq | wc -l`;  
  chomp $mu;
  $barcodes{$barcode}{'mu'} = $mu;
}

sub run_bowtie_maize {
  my $barcode = shift;
#	`bowtie --best -l 50 -p 12 -S --sam-nohead $maize_reference $out/$barcode/$barcode\_nomu.fq > $out/temp`;
	`bowtie2 -p $procs --sam-nohead -x $maize_reference $out/$barcode/$barcode\_nomu.fq > $out/temp`;

  #my @sam = split("\n", `bowtie --best -l 50 -p 12 -S --sam-nohead $maize_reference $out/$barcode/$barcode\_nomu.fq`);
  #open(SAM, ">$out/temp");
  #foreach my $sam (@sam) {
#    if($sam =~ /^@/) {
 #     next;
  #  }
   # if($sam !~ /4\t\*\t0\t0\t\*\t\*\t0\t0/) {
    #  $barcodes{$barcode}{'maize'}++;
    #  print SAM "$sam\n";
    #}
  #}
#  close(SAM);
print STDERR "Converting SAM to BAM\n";
  `samtools view -S -b -t $maize_reference.fa.fai $out/temp > $out/unsorted.bam`;
print STDERR "Sorting\n";
  `samtools sort $out/unsorted.bam > $out/$barcode/$barcode.bam`;
  `samtools index $out/$barcode/$barcode.bam`;
print STDERR "Creating pileup\n";
  `samtools mpileup -f $maize_reference.fa $out/$barcode/$barcode.bam > $out/$barcode/$barcode.pileup`;
print STDERR "Converting back to SAM\n";
  `samtools view $out/$barcode/$barcode.bam > $out/$barcode/$barcode.sam`;
print STDERR "Counting number of reads\n";
$barcodes{$barcode}{'maize'} = `cut -f 2 $out/$barcode/$barcode.sam | grep -cv 4`;
chomp $barcodes{$barcode}{'maize'};
  #`sort -k 3,3 -k 4,4n $out/temp > $out/$barcode/$barcode.sam`;
  
}

sub run_find_peaks {
  my $barcode = shift;
  `FindPeaks.pl $out/$barcode/$barcode.sam 2000 200 $out/$barcode/$barcode.pileup > $out/$barcode/$barcode.peaks`;
  `DiffEnrichment.pl $bg_clusters $out/$barcode/$barcode.peaks > $out/$barcode/$barcode\_nobg.peaks`;
  $barcodes{$barcode}{'clusters'} = `wc -l $out/$barcode/$barcode\_nobg.peaks | cut -d ' ' -f 1`;
  chomp $barcodes{$barcode}{'clusters'};
  #my $insert_cluster = $dbh->prepare("INSERT INTO cluster (");
}
sub fetch_bustard {
#  my $zeros = "000";
 # for(my $i = 1; $i <= 120; $i++) {
#my @tiles = (1101, 1102, 1103, 1104, 1105, 1106, 1107, 1108, 1201, 1202, 1203,
#1204, 1205, 1206, 1207, 1208, 2101, 2102, 2103, 2104, 2105, 2106, 2107, 2108, 2201, 2202, 2203, 2204, 2205, 2206, 2207, 2208);
	my @files = <$bustard/*.fastq.gz>;


#for(my $i = 0; $i < 32; $i++) {
 #   my $tile = $tiles[$i];
  #  my $file = "$bustard/s_$lane\_1_$tile\_qseq.txt";
foreach my $file (@files) {	
	open(QSEQ, "gzip -dc $file |");
	#    open(QSEQ, "<:gzip", "$file");
 	while(<QSEQ>) {
	if(eof(QSEQ)) {
		last;
	}
    #  my @line = split("\t", $_);
      	my $name = $_;
	my $seq = <QSEQ>;
	my $name2 = <QSEQ>;
	my $qual = <QSEQ>;
	foreach my $barcode (keys %barcodes) {
	my $barcode_length = length($barcode);
	 if($barcodes{$barcode}{'fh'} == 0) {
	    mkdir "$out/$barcode";
	    open(my $fh, ">$out/$barcode/$barcode.fq");
	    $barcodes{$barcode}{'fh'} = $fh;
	}
	if(substr($seq, 0, $barcode_length) eq $barcode) {
	  $barcodes{$barcode}{'reads'}++;
#	  $line[8] =~ s/\./N/g;
	chomp($name);
	
	  print {$barcodes{$barcode}{'fh'}}  $name . "_$barcode\n";
	  print {$barcodes{$barcode}{'fh'}} substr($seq, $barcode_length);
	  print {$barcodes{$barcode}{'fh'}} $name2;
	  # convert Illumina quality to phred (sanger) quality
	 # my @qual = split("", substr($line[9], $barcode_length));
	 # foreach my $sq (@qual) {
	  #  print {$barcodes{$barcode}{'fh'}} chr(ord($sq) - 31);
	  #}
	  print {$barcodes{$barcode}{'fh'}} substr($qual, $barcode_length);
	}
      }
    } 
   close(QSEQ);
  }
}

sub parse_command_line {
  while (@ARGV) {
    $_ = shift @ARGV;
    if    ($_ =~ /^-b$/) { 
      my $bc = shift @ARGV; 
      open(BC, $bc);
      while(<BC>) {
	chomp;
	$barcodes{$_} = {'fh' => 0,
			  'reads' => 0,
			  'mu' => 0,
			  'maize' => 0,
			  'clusters' => 0};
	#$barcode_length = length($_);
      }
    }
    elsif ($_ =~ /^-B$/) { $bustard = shift @ARGV; }
    elsif ($_ =~ /^-l$/) { $lane = shift @ARGV; }
    elsif ($_ =~ /^-o$/) { $out = shift @ARGV; }
    elsif ($_ =~ /^-m$/) { $mu_reference = shift @ARGV; }
    elsif ($_ =~ /^-M$/) { $maize_reference = shift @ARGV; }
    elsif ($_ =~ /^-r$/) { $rice_db = shift @ARGV; }
    elsif ($_ =~ /^-c$/) { $bg_clusters = shift @ARGV; }
    elsif ($_ =~ /^-i$/) { $run_id = shift @ARGV; }
    elsif ($_ =~ /^-p$/) { $procs = shift @ARGV; }
    elsif ($_ =~ /^-h$/) { usage(); }
    else {
      print STDERR "Unknown command line option: '$_'\n";
      usage();
    }
  }

  
  if (keys %barcodes == 0 || $bustard eq "" || $lane == 0 || $out eq "") {
    usage();
  }
}

sub usage {
  print STDERR <<EOQ; 
mu-illumina.pl -b barcodes -B bustard -l lane -o out -i [-m mu -M maize -r rice -c bg]
    b: file listing barcodes
    B: path to bustard files
    l: lane in run
    o: output directory
    i: Id for run table in database
    m: path to bowtie formatted mu reference
    M: path to bowtie formatted maize reference
    r: path to blast formatted rice nucleotide genes
    c: path to backgroud clusters
    p: number of processors to user
    h: display this help message

EOQ

exit(0);
}
