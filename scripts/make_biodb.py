#!/usr/bin/python

from Bio import SeqIO
from BCBio import GFF
from BioSQL import BioSeqDatabase
import sys

server = BioSeqDatabase.open_database(driver="pymysql", user="root",
                     passwd = "420bigmoney", host = "localhost", db="maizeseq")
db = server.new_database("Zea_mays.AGPv4.2")
seq_dict = SeqIO.to_dict(SeqIO.parse(open(sys.argv[1]), "fasta"))
gff = GFF.parse(open(sys.argv[2]), base_dict=seq_dict)

db.load(gff)
server.commit()
