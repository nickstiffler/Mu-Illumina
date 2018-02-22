# Mu-Illumina

Frontend, backend, and database scripts for building and running the Mu-Illumina data analysis platform developed by the Barkan Lab at the University of Oregon. 

## Use of Illumina sequencing to identify transposon insertions underlying mutant phenotypes in high-copy Mutator lines of maize.
Williams-Carrier R, Stiffler N, Belcher S, Kroeger T, Stern DB, Monde RA, Coalter R, Barkan A.

## Introduction
This system was developed by the Barkan Lab to provide tools for researchers to identify the mu insertion causing an observed phenotype in a ear. Illumina high-throughput sequencing is utilized and aligned to the maize reference genome. The pipeline then identifies each "cluster" of reads (contiguous region of aligned reads) as a potential insertion site. These clusters are then imported into a MySQL database. The database also contains annotations to associate the cluster with maize genes. A web based frotend allows the researcher to search and explore the database to find the candidate gene for the phenotype.

## Pipeline
The scripts folder contains several tools needed to run the analysis pipeline as well as importing the data into the database.
