# Mu-Illumina

Frontend, backend, and database scripts for building and running the Mu-Illumina data analysis platform developed by the Barkan Lab at the University of Oregon. 

### Use of Illumina sequencing to identify transposon insertions underlying mutant phenotypes in high-copy Mutator lines of maize.
Williams-Carrier R, Stiffler N, Belcher S, Kroeger T, Stern DB, Monde RA, Coalter R, Barkan A.

## Introduction
This system was developed by the Barkan Lab to provide tools for researchers to identify the mu insertion causing an observed phenotype in a ear. Illumina high-throughput sequencing is utilized and aligned to the maize reference genome. The pipeline then identifies each "cluster" of reads (contiguous region of aligned reads) as a potential insertion site. These clusters are then imported into a MySQL database. The database also contains annotations to associate the cluster with maize genes. A web based frotend allows the researcher to search and explore the database to find the candidate gene for the phenotype.

## Scripts
The scripts folder contains several tools needed to run the analysis pipeline as well as importing the data into the database.

### Demultiplex
Mu-Illumina samples will typically be pooled together in a single Illumina library. The will be a unique DNA "barcode" prefixed to each read that identifies the sample. This will need to be demuliplex. There are a number of tools available publically, but here we provide a simple tool for executing this step. It takes a raw gzipped FASTQ file and a file containing a list of barcodes as input and outputs a separate file for each barcode, with the barcode trimmed off.

demux.py -i reads.fq.gz -b barcodefile

### Pipeline
The pipeline tool expects a multi-core system or high performance compute environment to run. High memory isn't as much of a concern as multi-cores. The pipeline starts by execute multiple samples, running through the pipeline steps one at a time. It starts by aligning the sample to the mu sequence. A high portion of the reads are mu sequence and these should be filtered before aligning to the genome because it also contains mu sequence. The remaining reads are then aligned to the maize reference genome using the bowtie aligner. This outputs a SAM file, which is then converted using samtools to BAM, sorted, and the converted back to a SAM. Also a pipeup file is created. The pileup and SAM are used as input to the cluster finding tool. The folder containing the demultiplexed fastq files and the file listing the barcodes are needed for input

mu-illumina_bulk.py -i input_folder -b barcode_file

### Build Database
Next step is to begin loading the output from the pipeline onto the database. Each sample is associated with a set of ears and target genes. A tab delimited file containing the barcode, ears, and genes for each sample is needed in the database. There also needs to be a run input in the run table. There is currently no script for that, so it must be inserted manually.

InputRunData.pl -i ears_table -r run_id

### Load Clusters
The pipeline produced a file with the extention "peaks" for each sample. These files need to be loaded in the database. The peaks file defines the region (chromosome, start, end, number of aligned reads) for each cluster. This script loads that information into the cluster table, and then queries the other tables for maize gene and sequence. It only needs the run ID and the folder with the pipeline output.

load_clusters.py -r run_id -i output_folder

### Specify Background Clusters
Many lines of maize contain mu that appear in every ear. This is noisey and makes it difficult for the researcher to find and identify the real mu insertion site. If a cluster appears in the database more than 150 times, we flag it as background and it will be hidden from the database interface. It takes no arguments.

specify_background.py

## Server
The server code is entirely custom PHP. The only configuration needed is to create a my.cnf file that contains the login information for your MySQL server.

## Client
The client a built using Angular. In the environments folder, the URLs need to be set for both the dev and prod servers. Periodocially, the external maize databases change their URL and this will need to be updated in the client code.
