#!/usr/bin/perl -w
#
# Nicholas Stiffler
# Institute of Molecular Biology
# Barkan Lab
# January 19, 2010
#
# Insert barcodes, ears and mutants into respective tables, and associate them with a run id
#

use strict;
use DBI;

my $run_id = $ARGV[0];
open(RUN, $ARGV[1]);

my $db = "mu_illumina_v4";

my $dbh = DBI->connect("DBI:mysql:$db:mysql_read_default_file=./my.cnf")
    or die("Unable to connect to the $db\n" . $DBI::errstr);

my $insert_barcode = $dbh->prepare("INSERT INTO barcode (date_id, barcode) VALUES (?, ?)");
my $select_mutant = $dbh->prepare("SELECT id FROM mutant WHERE name = ?");
my $insert_mutant = $dbh->prepare("INSERT INTO mutant (name) VALUES (?)");
my $insert_ear = $dbh->prepare("INSERT INTO ear (barcode_id, ear, mutant_id) VALUES (?, ?, ?)");

my $barcode = "";
my $barcode_id;
while(<RUN>) {
	chomp;
  my @line = split("\t", $_);
  my $mutant = $line[1];
  if(length($line[0]) > 0) {
    $barcode = $line[0];
    $insert_barcode->execute($run_id, $barcode);
    $barcode_id = $dbh->{'mysql_insertid'};
  }
  my $ear = $line[2];

  

  $select_mutant->execute($mutant);
  my $mutant_id;
  $select_mutant->bind_columns(\$mutant_id);
  if(!$select_mutant->fetch()) {
    $insert_mutant->execute($mutant);
    $mutant_id = $dbh->{'mysql_insertid'};
  }

  $insert_ear->execute($barcode_id, $ear, $mutant_id);

}
