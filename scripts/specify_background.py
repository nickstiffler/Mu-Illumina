#!/usr/bin/python

# Nicholas Stiffler
# Barkan Lab
# January 29, 2015
#
# Check if a cluster is background, and update table to indicate

from mu_illumina_v4 import Cluster

for cluster in Cluster.select():
	background = Cluster.select().where(Cluster.chr == cluster.chr).where(((Cluster.start <= cluster.start) & (Cluster.end >= cluster.start)) | ((Cluster.start <= cluster.end) & (Cluster.end >= cluster.end)) | ((Cluster.start > cluster.start) & (Cluster.end < cluster.end)))
	if background.count() > 150:
		Cluster.update(background=1).where(Cluster.id == cluster.id).execute()
		
