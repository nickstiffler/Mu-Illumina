#!/usr/bin/python
#
# Given a fastq.gz and a list of barcodes,
# demultiplex into separate files
#
# Nicholas Stiffler
# August 2017
#

import sys, gzip

fq = gzip.open(sys.argv[1], 'r')

bcs = {}
bc = open(sys.argv[2])

for line in bc:
	bcs[line.strip()] = gzip.open(line.strip() + ".fq.gz", 'wb')

line = fq.readline()
header = ""
seq = ""
plus = ""
qual = ""
while line:
	header = line
	seq = fq.readline().decode("utf-8")
	plus = fq.readline()
	qual = fq.readline()

	for bc in bcs:
		if seq.startswith(bc):
			bcs[bc].write(header)
			bcs[bc].write(seq[len(bc):].encode("utf-8"))
			bcs[bc].write(plus)
			bcs[bc].write(qual[len(bc):])
	line = fq.readline()		
	
