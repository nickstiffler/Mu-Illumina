#!/usr/bin/python

import sys
from pathlib import Path
from mu_illumina_v4 import Barcode, Cluster, GeneCoords, MaizeGene
from Bio import SeqIO
from progressbar import ProgressBar
from BioSQL import BioSeqDatabase

run_id = sys.argv[1]
clusters = Path(sys.argv[2])
counts = 0
for peaks in clusters.glob('**/*_nobg.peaks'):
	with peaks.open() as f:
		while f.readline():
			counts += 1

#print("Indexing reference genome")
#maize_dict = SeqIO.index(sys.argv[3], "fasta")
server = BioSeqDatabase.open_database(driver="pymysql", user="root",
                     passwd = "420bigmoney", host = "localhost", db="maizeseq")
db = server["Zea_mays.AGPv4-GFF3"]

print("Loading clusters")
pbar = ProgressBar(max_value=counts).start()
i = 0
for barcode in Barcode.select().where(Barcode.date == run_id):
	bc_dir = clusters / barcode.barcode
	for peaks in bc_dir.glob("*_nobg.peaks"):
		for line in open(peaks):
			cols = line.strip().split()
			insertion_start = 0
			insertion_end = 0
			if(int(cols[6]) != 0): 
				insertion_start = int(cols[6]) - 5
				insertion_end = int(cols[6]) + 5
			rec = db.lookup(gi=cols[0])[int(cols[1]):int(cols[2])]
			genes = set()
			for gene in GeneCoords.select().where(GeneCoords.chr == cols[0]).where(GeneCoords.start < int(cols[1])).where(GeneCoords.end > int(cols[1])):
				genes.add(gene.accession)
			for gene in GeneCoords.select().where(GeneCoords.chr == cols[0]).where(GeneCoords.start < int(cols[2])).where(GeneCoords.end > int(cols[2])):
				genes.add(gene.accession)
			for gene in GeneCoords.select().where(GeneCoords.chr == cols[0]).where(GeneCoords.start > int(cols[1])).where(GeneCoords.end < int(cols[2])):
				genes.add(gene.accession)

			cluster = Cluster.create(chr = cols[0], start = cols[1], end = cols[2], size = cols[3], insertion_start = insertion_start, insertion_end = insertion_end, barcode = barcode.id, seq = rec.seq, primer = "", maize = "", identified_gene = "")
			i += 1
			for gene in genes:
				MaizeGene.create(accession = gene, cluster_id = cluster.id)

			pbar.update(i)
	
pbar.finish()
