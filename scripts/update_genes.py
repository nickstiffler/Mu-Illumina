#!/usr/bin/python

from mu_illumina_v4 import Cluster, GeneCoords, MaizeGene

for cluster in Cluster.select().where(Cluster.chr.startswith("B73V4_ctg")):
	genes = set()
	for gene in GeneCoords.select().where(GeneCoords.chr == cluster.chr).where(GeneCoords.start < cluster.start).where(GeneCoords.end > cluster.start):
		genes.add(gene.accession)
	for gene in GeneCoords.select().where(GeneCoords.chr == cluster.chr).where(GeneCoords.start < cluster.end).where(GeneCoords.end > cluster.end):
		genes.add(gene.accession)
	for gene in GeneCoords.select().where(GeneCoords.chr == cluster.chr).where(GeneCoords.start > cluster.start).where(GeneCoords.end < cluster.end):
		genes.add(gene.accession)

	for gene in genes:
		MaizeGene.create(accession = gene, cluster_id = cluster.id)
	
