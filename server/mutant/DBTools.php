<?php
//require_once '../PMLDatabase/Gene.php';
/*
 * Nicholas Stiffler
 * Institute of Molecular Biology
 * Barkan Lab
 * October 21, 2009
 *
 * DBTools.php
 */

class DBTools {

    /**
     * Our database connection
     * @access private
     * @var mysql connection resource
     */
    private $db;

    /**
     * Constructor sets up database
     */
    public function __construct() {
        $mycnf = parse_ini_file("my.cnf");
		
	
        $this->db = mysqli_connect($mycnf['host'], $mycnf['user'], $mycnf['password'], $mycnf['database']);
        if (mysqli_connect_errno()) {
            error_log("Unable to connect: " . mysqli_connect_error());
        }
        // if (!mysql_select_db($mycnf['database'], $this->db)) {
        //if (!mysql_select_db("maize_mu", $this->db)) {
        //  error_log("Unable to select database: " . mysql_error());
        // }
    }

    public function setOldDB() {
        mysqli_select_db($this->db, "maize_mu");
    }

    public function getRuns() {
        $query = "SELECT * FROM run_date ORDER BY date DESC";
        $runs = array();
        //$runs[] = "All";
        $run_result = mysqli_query($this->db, $query);
        if (!$run_result) {
            error_log("DBTools getRuns: " . mysqli_error($this->db) . " " . $query);
        }
        while ($run = mysqli_fetch_assoc($run_result)) {
            $runs[] = $run;
            //  $runs[$run['id']] = $run['name']; //$run['date'] . " Lane " . $run['lane'];
        }
        return $runs;
    }

    public function getBarcodes($date_id) {
        //  $query = "SELECT barcode.id, barcode.barcode, mutant.name FROM barcode, ear, mutant WHERE ear.barcode_id = barcode.id AND ear.mutant_id = mutant.id AND barcode.date_id = $date_id";
        $query = "SELECT * FROM barcode WHERE date_id = $date_id";
        $barcodes = array();
        //$barcodes = "All";
        $barcode_result = mysqli_query($this->db, $query);
        if (!$barcode_result) {
            error_log("DBTools getBarcodes: " . mysqli_error($this->db) . " " . $query);
        }
        while ($barcode = mysqli_fetch_assoc($barcode_result)) {
            $barcodes[$barcode['id']] = $barcode['barcode'];
        }
        return $barcodes;
    }

    public function getCOI2($id) {
        $coi = new COI($this->db, $id);
        $cluster = array();

        // Run
        $cluster['date'] = $coi->getRunDate();
        $cluster['lane'] = $coi->getRunLane();
        $cluster['barcode'] = $coi->getBarcode();
        $cluster['mutants'] = implode(", ", $coi->getMutants());
        $cluster['ears'] = implode(", ", $coi->getEars());

        // Cluster
        $cluster['chr'] = $coi->getChr();
        $cluster['location'] = implode(" - ", $coi->getLocation());
        $cluster['width'] = $coi->getWidth();
        $cluster['size'] = $coi->getSize();
        $cluster['insertion_site'] = implode(" - ", $coi->getInsertionSite());
        $cluster['insertion_position'] = $coi->getInsertionLocation();
        $cluster['seq'] = self::formatSeq($cluster['seq'], $cluster['insertion_start'] - $cluster['start'], $cluster['insertion_end'] - $cluster['start'] + 1);
        $cluster['identified_gene'] = $coi->getIdentifiedGene();
        $cluster['primer'] = $coi->getPrimer();

        // Ortholog
        $cluster['homolog'] = $coi->getOrtholog();
        $cluster['nucleoid'] = "No";
        if ($coi->isNucleoid()) {
            $cluster['nucleoid'] = "Yes";
        }
        $cluster['domains'] = implode(",", $coi->getDomains());
        $target = $coi->getTargeting();
        foreach ($target AS $method => $prediction) {
            $cluster[$method] = $prediction;
        }
        $cluster['arab'] = $coi->getArab();
        $cluster['arab_desc'] = $coi->getArabDesc();
        $cluster['arab_domains'] = implode(", ", $coi->getArabDomains());
        $arab_target = $coi->getArabTargeting();
        foreach ($target AS $method => $prediction) {
            $cluster["arab_" . $method] = $prediction;
        }

        return $cluster;
    }

    public function getCOI($id) {
        // Fetch run date, run lane, barcode, chromosome, location, size, insertion site, sequence, maize
        $query = "SELECT * FROM run_date, barcode, cluster WHERE run_date.id = barcode.date_id AND barcode.id = cluster.barcode_id AND cluster.id = $id";
        $result = mysqli_query($this->db, $query);
        $cluster = mysqli_fetch_assoc($result);


        // Fetch ears
        $query = "SELECT * FROM ear WHERE ear.barcode_id = " . $cluster['barcode_id'];
        $ear_result = mysqli_query($this->db, $query);
        $mutants = array();
        $ears = array();
        while ($ear = mysqli_fetch_assoc($ear_result)) {
            $ears[] = $ear['ear'];

            // Fetch mutant
            $query = "SELECT * FROM mutant WHERE id = " . $ear['mutant_id'];
            $mutant_result = mysqli_query($this->db, $query);
            $mutant = mysqli_fetch_assoc($mutant_result);
            $mutants[] = $mutant['name'];
        }
        //$cluster['ears'] = implode(", ", $ears);
        $cluster['ears'] = $ears;
        $cluster['mutants'] = implode(", ", $mutants);

		$query = "SELECT * FROM maize_gene WHERE cluster_id = " . $cluster['id'];
		$maize_result = mysqli_query($this->db, $query);
		$cluster['maize'] = array();
		while($maize = mysqli_fetch_assoc($maize_result)) {
			$cluster['maize'][] = $maize['accession'];
		}

        // Fetch insertion site
        $cluster['insert_loc'] = "Unidentified";

        if ($cluster['insertion_start'] > $cluster['insertion_end']) {
            $cluster['insertion_start'] = 0;
            $cluster['insertion_end'] = 0;
        }

		/*
        if ($cluster['insertion_start'] != 0) {
		// Check to see if the insertion is in a exon or intron
	    $chrom = $cluster['chr'];
	    if (substr($chrom, 0, 3) !== "chr") {
		    $chrom = "chr" . $chrom;
	    }
            $query = "SELECT * FROM trans_feature, transcript WHERE chr = '" . $chrom . "' AND trans_feature.trans_id = transcript.id AND trans_feature.start <= " . $cluster['insertion_start'] . " AND trans_feature.end >= " . $cluster['insertion_end'];
            $result = mysqli_query($this->db, $query);

            if ($trans = mysqli_fetch_assoc($result)) {

                if ($trans['type'] == "exon") {
                    $cluster['insert_loc'] = "Exon";
                } else {
                    $cluster['insert_loc'] = "Intron";
                }
            }
            // Check if it is in a UTR
            if ($cluster['insert_loc'] == "Unidentified") {
                $query = "SELECT * FROM transcript WHERE chr = '" . $chrom . "' AND start < " . $cluster['insertion_start'] . " AND end > " . $cluster['insertion_end'];
                $result = mysqli_query($this->db, $query);

                if ($row = mysqli_fetch_assoc($result)) {

                    $diff = ($row['end'] - $row['start']) / 4;
                    if ($cluster['insertion_start'] < ($row['start'] + $diff)) {
                        if ($row['strand'] == 1) {
                            $cluster['insert_loc'] = "5' UTR";
                        } else {
                            $cluster['insert_loc'] = "3' UTR";
                        }
                    } else if ($cluster['insertion_end'] > ($row['end'] - $diff)) {
                        if ($row['strand'] == 1) {
                            $cluster['insert_loc'] = "3' UTR";
                        } else {
                            $cluster['insert_loc'] = "5' UTR";
                        }
                    }
                }
            }
        }

        */
		// Fetch alternate maize gene
		/*
		$query = "SELECT * FROM alt_gene WHERE cluster_id = $id";
		$result = mysqli_query($this->db, $query);
		$cluster['alt_gene'] = "";
		if($row = mysqli_fetch_assoc($result)) {
			$cluster['alt_gene'] = $row['genemodel'];
		}
	`	*/
        // Fetch ortholog
		/*
        $query = "SELECT * FROM rice7 WHERE cluster_id = $id";
        $result = mysqli_query($this->db, $query);
        $cluster['accession'] = "";
        $cluster['organism'] = "Rice";
        if ($row = mysqli_fetch_assoc($result)) {
            $cluster['accession'] = $row['accession'];
            //$cluster['organism'] = $row['organism'];
            // Fetch nucleoid
            $cluster['mass_spec'] = "No";

            $query = "SELECT * FROM mass_spec WHERE maize = '" . str_replace("T", "P", $cluster['maize']) . "'";
            if ($cluster['maize'] == "") {
                $query = "SELECT * FROM mass_spec WHERE rice LIKE '" . $cluster['accession'] . "%'";
            }
            // if($cluster['organism'] == "Rice") {
            //    $query = "SELECT * FROM mass_spec WHERE rice LIKE '%" . $cluster['accession'] . "%'";
            // } else if($cluster['organism'] == "Sorghum") {
            //        $query = "SELECT * FROM mass_spec, arab_ortholog WHERE ortholog = '" . $cluster['accession'] . "' AND arabidopsis = arab";
            //     }
            $result = mysqli_query($this->db, $query);
            if (mysqli_fetch_row($result)) {
                $cluster['mass_spec'] = "Yes";
            }

            $cluster['cp_proteins'] = "No";

            $query = "SELECT * FROM cp_proteins WHERE maize = '" . str_replace("T", "P", $cluster['maize']) . "'";
            if ($cluster['maize'] == "") {
                $query = "SELECT * FROM cp_proteins WHERE rice LIKE '" . $cluster['accession'] . "%'";
            }

            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCOI: $query " . mysqli_error());
            }
            if (mysqli_fetch_row($result)) {
                $cluster['cp_proteins'] = "Yes";
            }


            // Fetch domains
            $query = "SELECT * FROM domains WHERE gene LIKE '%" . $cluster['accession'] . "%'";
            $domains = array();
            $domain_result = mysqli_query($this->db, $query);
            while ($domain = mysqli_fetch_assoc($domain_result)) {
                $domains[] = $domain['description'];
            }
            $cluster['domains'] = implode(", ", array_unique($domains, SORT_STRING));

            // Fetch targeting
            $query = "SELECT * FROM target WHERE gene LIKE '%" . $cluster['accession'] . "%'";
            $target_result = mysqli_query($this->db, $query);
            while ($target = mysqli_fetch_assoc($target_result)) {
                $cluster[$target['method']] = $target['prediction'];
            }
        }

	$ortholog = str_replace("T", "P", $cluster['maize']);
//	$ortholog = substr($cluster['maize'], 0, -4);

        if ($ortholog == "") {
            $ortholog = 'LOC_' . $cluster['accession'];
        }
		*/
        // Fetch arabidopsis, description
		foreach($cluster['maize'] as $maize) {
			$query = "SELECT * FROM arab_ortholog, arabidopsis WHERE ortholog = '$maize' AND CONCAT(arabidopsis, '.1') = accession";
	//$query = "SELECT * FROM arab_ortholog, arabidopsis WHERE ortholog LIKE '" . $ortholog . "%' AND arab_ortholog.arabidopsis = arabidopsis.accession";
//	error_log($query);
	     	$arab_result = mysqli_query($this->db, $query);
     		//$cluster['arab_desc'] = "";
        //luster['arab_domains'] = "";
       		$cluster['arab'] = array();
//        $cluster['arab_targetp'] = "";
  //      $cluster['arab_predotar'] = "";
    	    if ($arab = mysqli_fetch_assoc($arab_result)) {
        	    $cluster['arab'][$arab['arabidopsis']] = $arab['defline'];
            // Fetch arab domains
			/*
            $query = "SELECT * FROM domains WHERE gene = '" . $arab['accession'] . "'";
            $domains = array();
            $domain_result = mysqli_query($this->db, $query);
            while ($domain = mysqli_fetch_assoc($domain_result)) {
                $domains[] = $domain['description'];
            }
            $cluster['arab_domains'] = implode(", ", array_unique($domains, SORT_STRING));

            // Fetch arab targeting
            $query = "SELECT * FROM target WHERE gene = '" . $arab['accession'] . "'";
            $target_result = mysqli_query($this->db, $query);
            while ($target = mysqli_fetch_assoc($target_result)) {
                $cluster['arab_' . $target['method']] = $target['prediction'];
            }
			*/
        	}
		}

        //$cluster['seq'] = self::formatSeq($cluster['seq'], $cluster['insertion_start'] - $cluster['start'], $cluster['insertion_end'] - $cluster['start'] + 1);

        //if (isset($cluster['domains'])) {
        //    $cluster['domains'] = wordwrap($cluster['domains'], 50, "<br />");
        //}
       // $cluster['arab_desc'] = wordwrap($cluster['arab_desc'], 50, "<br />");
        //$cluster['arab_domains'] = wordwrap($cluster['arab_domains'], 50, "<br />");
        return $cluster;
    }

    static function formatSeq($seq, $insert_start, $insert_end) {
        if ($insert_start < 0 || $insert_end < 0) {
            $insert_start = 0;
            $insert_end = 0;
        }

        $new_seq = "";
        $inserted = false;
        $line_len = 50;
        for ($i = 0; $i < strlen($seq); $i += $line_len) {
            if (!$inserted && $insert_start < $i + $line_len) {
                $new_seq .= substr($seq, $i, $insert_start - $i);
                $new_seq .= '<span class="insertion">';
                if ($insert_end > $i + $line_len) {
                    $i += $line_len;
                    $new_seq .= substr($seq, $insert_start, $i - $insert_start);
                    $new_seq .= '<br />';
                    $new_seq .= substr($seq, $i, $insert_end - $i);
                    $new_seq .= '</span>';
                    $new_seq .= substr($seq, $insert_end, $i + $line_len - $insert_end);
                } else {
                    $new_seq .= substr($seq, $insert_start, $insert_end - $insert_start);
                    $new_seq .= '</span>';
                    $new_seq .= substr($seq, $insert_end - $i, $i + $line_len - $insert_end);
                }
                $inserted = true;
            } else {
                $new_seq .= substr($seq, $i, $line_len);
            }
            $new_seq .= "<br />";
        }
        return $new_seq;
    }

    static function cmp($a, $b) {
        return strcmp($a['ortholog'], $b['ortholog']);
    }

    public function getCOIs($run_id, $sort, $barcode_id, $term, $overlap, $search_type) {

        $coi_result = $this->runQuery($run_id, $barcode_id, $search_type, "", $sort);
        $cois = array();
        while ($coi = mysqli_fetch_assoc($coi_result)) {
	// TODO: Iterate over all maize genes and fetch arab ortholog	
			$query = "SELECT * FROM maize_gene LEFT JOIN translate_version ON accession = v4 WHERE cluster_id = " . $coi['id'];
			$result = mysqli_query($this->db, $query);
			$coi['maize'] = array();
			while($row = mysqli_fetch_assoc($result)) {
				$coi['maize'][] = $row['accession'];
				if($row['v3'] !== null) {
					$coi['maize'][] = $row['v3'];
				}
			}
			$coi['maize'] = array_values(array_unique($coi['maize']));
			if(count($coi['maize']) == 0) {
				continue;
			}	
            if ($search_type == "arab") {
                $ortholog = $coi['maize'];
                if ($coi['maize'] == "" || substr($coi['maize'], 0, 5) != "GRMZM") {
                    $ortholog = "LOC_" . $coi['ortholog'];
                } else {
                    $ortholog = str_replace("T", "P", $ortholog);
                }

				$query = "SELECT * FROM arab_ortholog WHERE ortholog = '$ortholog'";
                $result = mysqli_query($this->db, $query);
                $row = mysqli_fetch_assoc($result);

                if ($row['arabidopsis'] != $term) {
                    continue;
                }
            }
            
			if($term !== "") {
				if(stripos($term, "GRMZM") === 0 || stripos($term, "AC") === 0 || stripos($term, "Zm") === 0) {
					$search_type = "maize";
					if(!in_array($term, $coi['maize'])) {
						continue;					

					}
				}
				else if(stripos($term, "AT") === 0) {
					$search_type = "arab";
					$match = false;

					foreach($coi['maize'] as $maize) {
						$query = "SELECT * FROM arab_ortholog WHERE ortholog = '$maize'";
						$result = mysqli_query($this->db, $query);
						while($row = mysqli_fetch_assoc($result)) {
							if($term === $row['arabidopsis']) {
								$match = true;
							}
						}
					}
					
					if(!$match) {
						continue;
					}

				}
				else {
					$search_type = "ear";
				}
			}

            // Identify if maize gene has been PCR confirmed
	    /*if ($coi['maize'] != "") { 
	    $query = "SELECT * FROM ear_annotation WHERE maize LIKE '" . $coi['maize'] . "%'";
            $ear_result = mysqli_query($this->db, $query);
            if (mysqli_fetch_row($ear_result)) {
                $coi['maize'] .= " *";
            }
            }*/	
	    // Search PML Database and link to it if found
	    /*if(count($gene) > 0) {
	    	$link = "<a href='http://teosinte.uoregon.edu/PMLDatabase/view.php?id=" . $gene[0]->getId() . "' target='_blank'>" . $coi['maize'] . "</a>";
		$coi['maize'] = $link;
	    }*/
        	$coi_ear = $this->addMutant($coi, $search_type, $term);
			if(!$coi_ear) {
	        	$coi = $this->addMutant($coi, "gene", $term);
			} else {
				$coi = $coi_ear;
			}
	   /* $query = "SELECT * FROM alt_gene WHERE cluster_id = " . $coi['id'];
	    $alt_gene_result = mysqli_query($this->db, $query);
	    $row = mysqli_fetch_assoc($alt_gene_result);
	    if (isset($row['genemodel'])) {
		    $coi['alt_gene'] = $row['genemodel'];
		   // error_log($row['genemodel']);
	    }*/


	    	if ($coi) {
	    		$cois[] = $coi;
            }
        }
        if ($sort == "homolog") {
            usort($cois, array("DBTools", "cmp"));
        }

        if ($overlap === "true") {
            $cois_new = array();

            $barcodes = explode(",", $barcode_id);
            // Separate the cois into barcode bins
            $barcodes_cois = array();
            for ($i = 0; $i < count($barcodes); $i++) {
                $barcodes_cois[] = array();
                foreach ($cois AS $coi) {
                    if ($coi['barcode_id'] == $barcodes[$i]) {
                        $barcodes_cois[count($barcodes_cois) - 1][] = $coi;
                    }
                }
            }
            // Make sure the cluster exists in all barcode bins before it can be saved


            foreach ($barcodes_cois[0] AS $coi) {
                $present = array();
                $present[] = $coi;

                for ($i = 1; $i < count($barcodes_cois); $i++) {
                    $compare = $this->compareCluster($barcodes_cois[$i], $coi);
                    if ($compare !== FALSE) {
                        $present[] = $compare;
                        continue;
                    }
                }
                if (count($present) == count($barcodes)) {

                    $cois_new = array_merge($cois_new, $present);
                }
            }

	    $cois = $cois_new;
        }

        return $cois;
    }


    public function getCOIs2($run_id, $sort, $barcode_id, $mass_spec, $cp_klaas, $term, $overlap, $size, $hq, $search_type, $interesting_genes, $cp, $arab_pprs, $alleles_needed) {

        $coi_result = $this->runQuery($size, $run_id, $barcode_id, $search_type, $term, $hq, $sort);
        $cois = array();
        while ($coi = mysqli_fetch_assoc($coi_result)) {



            if ($interesting_genes == 1) {

                if ($coi['maize'] == "") {
                    continue;
                }
                if (strpos($coi['maize'], "GRMZM") === 0) {
                    $query = "SELECT * FROM interesting_genes WHERE maize = '" . substr($coi['maize'], 0, 13) . "'";
                } else if (strpos($coi['maize'], "AC") === 0) {
                    $query = "SELECT * FROM interesting_genes WHERE maize = '" . $coi['maize'] . "'"; //. str_replace("T", "", $coi['maize']) . "'";
                }

                if ($result = mysqli_query($this->db, $query)) {
                    if (!mysqli_fetch_row($result)) {
                        continue;
                    }
                } else {
                    error_log("DBTools getCOIs: " . mysqli_error() . " $query");
                }
            }
            
            if($arab_pprs == 1) {
                if($coi['maize'] == "") {
                    continue;
                }
                $query = "SELECT * FROM ptype_pprs WHERE maize = '" . substr($coi['maize'], 0, 13) . "'";
                if ($result = mysqli_query($this->db, $query)) {
                    if (!mysqli_fetch_row($result)) {
                        continue;
                    }
                } else {
                    error_log("DBTools getCOIs: " . mysqli_error() . " $query");
                }
            }
            $coi['ortholog'] = "";
//            $query = "SELECT accession FROM rice7 WHERE cluster_id = " . $coi['id'];
//            $result = mysqli_query($this->db, $query);
//            if ($row = mysqli_fetch_assoc($result)) {
//                $coi['ortholog'] = $row['accession'];
//                if ($search_type == "rice") {
//                    if (stripos($coi['ortholog'], $term) === FALSE) {
//                        continue;
//                    }
//                }
//            } else if ($search_type == "rice") {
//                continue;
//            }

            if ($coi['maize'] == "" && $coi['ortholog'] == "") {
                continue;
            }

            if ($search_type == "arab") {
                $ortholog = $coi['maize'];
                if ($coi['maize'] == "" || substr($coi['maize'], 0, 5) != "GRMZM") {
                    $ortholog = "LOC_" . $coi['ortholog'];
                } else {
                    $ortholog = str_replace("T", "P", $ortholog);
                }

		$query = "SELECT * FROM arab_ortholog WHERE ortholog = '$ortholog'";
		error_log($query);
                $result = mysqli_query($this->db, $query);
                $row = mysqli_fetch_assoc($result);

                if ($row['arabidopsis'] != $term) {
                    continue;
                }
            }
/*
            if ($arab_pprs == 1) {
                $ortholog = $coi['maize'];
                if ($coi['maize'] == "" || substr($coi['maize'], 0, 5) != "GRMZM") {
                    $ortholog = "LOC_" . $coi['ortholog'];
                } else {
                    $ortholog = str_replace("T", "P", $ortholog);
                }

                $query = "SELECT * FROM arab_pprs, arab_ortholog WHERE arab_pprs.arab = arab_ortholog.arabidopsis AND ortholog = '$ortholog'";
                $result = mysqli_query($this->db, $query);
                if (!mysqli_fetch_row($result)) {
                    continue;
                }
            }
*/
            if ($cp == 1) {

                $query = "SELECT * FROM target WHERE (gene = '" . $coi['maize'] . "' OR gene = '" . $coi['ortholog'] . "') AND (prediction = 'Choroplast' OR prediction = 'possibly plastid' OR prediction = 'plastid')";
                $result = mysqli_query($this->db, $query);
                if (!mysqli_fetch_row($result)) {
                    continue;
                }
            }


            if ($mass_spec == 1) {

                $query = "SELECT * FROM mass_spec WHERE maize = '" . str_replace("T", "P", $coi['maize']) . "'";
                if ($coi['maize'] == "") {
                    $query = "SELECT * FROM mass_spec WHERE rice LIKE '" . $coi['ortholog'] . "%'";
                }


                $result = mysqli_query($this->db, $query);
                if (!mysqli_fetch_row($result)) {
                    continue;
                }
            }

            if ($cp_klaas == 1) {
                $query = "SELECT * FROM cp_proteins WHERE maize = '" . str_replace("T", "P", $coi['maize']) . "'";
                if ($coi['maize'] == "") {
                    // continue;
                    $query = "SELECT * FROM cp_proteins WHERE rice LIKE '" . $coi['ortholog'] . "%'";
                }


                $result = mysqli_query($this->db, $query);
                if (!mysqli_fetch_row($result)) {
                    continue;
                }
            }

	    $maize = $coi['maize'];
//	    if($coi['maize'] !== "") {
            if(substr($coi['maize'], 0, 5) == "GRMZM") {
                 $maize = substr($coi['maize'], 0, 13);
            }
                
            $gene = Gene::searchGenes("maize", $maize);
            if($alleles_needed == 1) {
                if($coi['maize'] == "") {
                    continue;
                }
               if(count($gene) == 0) {
                    continue;
                }
                if($gene[0]->getField("allele_needed") == 0) {
                    continue;
		}
            }

            // Identify if maize gene has been PCR confirmed
	    if ($coi['maize'] != "") { 
	    $query = "SELECT * FROM ear_annotation WHERE maize LIKE '" . $coi['maize'] . "%'";
            $ear_result = mysqli_query($this->db, $query);
            if (mysqli_fetch_row($ear_result)) {
                $coi['maize'] .= " *";
            }
            }	
	    // Search PML Database and link to it if found
	    if(count($gene) > 0) {
	    	$link = "<a href='http://teosinte.uoregon.edu/PMLDatabase/view.php?id=" . $gene[0]->getId() . "' target='_blank'>" . $coi['maize'] . "</a>";
		$coi['maize'] = $link;
	    }
//	    }

            $coi = $this->addMutant($coi, $search_type, $term);
	    $query = "SELECT * FROM alt_gene WHERE cluster_id = " . $coi['id'];
	    $alt_gene_result = mysqli_query($this->db, $query);
	    $row = mysqli_fetch_assoc($alt_gene_result);
	    if (isset($row['genemodel'])) {
		    $coi['alt_gene'] = $row['genemodel'];
		   // error_log($row['genemodel']);
	    }


	    if ($coi) {
	    	$cois[] = $coi;
            }
        }
        if ($sort == "homolog") {
            usort($cois, array("DBTools", "cmp"));
        }

        if ($overlap == 1) {
            $cois_new = array();

            $barcodes = explode(",", $barcode_id);
            // Separate the cois into barcode bins
            $barcodes_cois = array();
            for ($i = 0; $i < count($barcodes); $i++) {
                $barcodes_cois[] = array();
                foreach ($cois AS $coi) {
                    if ($coi['barcode_id'] == $barcodes[$i]) {
                        $barcodes_cois[count($barcodes_cois) - 1][] = $coi;
                    }
                }
            }
            // Make sure the cluster exists in all barcode bins before it can be saved


            foreach ($barcodes_cois[0] AS $coi) {
                $present = array();
                $present[] = $coi;

                for ($i = 1; $i < count($barcodes_cois); $i++) {
                    $compare = $this->compareCluster($barcodes_cois[$i], $coi);
                    if ($compare !== FALSE) {
                        $present[] = $compare;
                        continue;
                    }
                }
                if (count($present) == count($barcodes)) {

                    $cois_new = array_merge($cois_new, $present);
                }
            }

	    $cois = $cois_new;
        }

        return $cois;
    }

    private function compareCluster($cois, $row) {
        // error_log("Count" . count($cois));
        foreach ($cois AS $coi) {
            if ($row['chr'] == $coi['chr']) {
                if (($row['start'] >= $coi['start'] && $row['start'] <= $coi['end']) ||
                        ($row['end'] >= $coi['start'] && $row['end'] <= $coi['end']) ||
                        ($row['start'] <= $coi['start'] && $row['end'] >= $coi['end'])) {

                    return $coi;
                }
            }
        }
        return FALSE;
    }

	private function runQuery($run_id, $barcode_id, $search_type, $term, $sort) {
        // Build query
		$size = 200;
         $query = "SELECT cluster.id, chr, start, end, barcode_id, date, lane, barcode, maize, name, insertion_start, insertion_end FROM run_date, barcode, cluster WHERE run_date.id = barcode.date_id AND barcode.id = cluster.barcode_id AND cluster.size > $size AND background = 0";
	if($search_type === "ear" && strlen(trim($term)) > 0) {
		$query = "SELECT cluster.id, chr, start, end, cluster.barcode_id, date, lane, barcode, maize, name FROM run_date, barcode, cluster, ear WHERE run_date.id = barcode.date_id AND barcode.id = cluster.barcode_id AND cluster.size > $size AND ear.barcode_id = cluster.barcode_id AND ear.ear = '$term'";
	} else if ($search_type === "gene" && strlen(trim($term)) > 0) {
		$query = "SELECT cluster.id, chr, start, end, cluster.barcode_id, date, lane, barcode, maize, run_date.name FROM run_date, barcode, cluster, ear, mutant WHERE run_date.id = barcode.date_id AND barcode.id = cluster.barcode_id AND cluster.size > $size AND ear.barcode_id = cluster.barcode_id AND ear.mutant_id = mutant.id AND mutant.name = '$term'";
	
	}
      $where = array();
        if (trim($term) == "") {
            $search_type = "";
        }
        $runs = explode(",", $run_id);
        if (count($run_id) > 0) {
            if (count($runs) == 1) {
                if ($run_id != "All") {


                    $where[] = "run_date.id = $run_id";
                }
            } else {
                $where[] = "(run_date.id = " . implode(" OR run_date.id = ", $runs) . ")";
            }
        }
        $barcodes = explode(",", $barcode_id);
        if (count($barcodes) > 0 && $barcode_id != 0) {
            if (count($barcodes) == 1) {
                if ($barcode_id != "All") {
                    $where[] = "barcode.id = $barcode_id";
                }
            } else {
                $where[] = "(barcode.id = " . implode(" OR barcode.id = ", $barcodes) . ")";
            }
        }
        if ($search_type == "maize") {
            $where[] = "maize LIKE ('$term%')";
        }

        if ($search_type == "loc") {

            $location = preg_split("/[:-]/", str_replace(" ", "", $term));
            if (count($location) == 3) {
                $where[] = 'cluster.chr = "' . $location[0] . '" AND cluster.start >= ' . $location[1] . ' AND cluster.end <= ' . $location[2];
            }
        }
        // if($search_type == "homolog") {
        //    $where[] =  "MATCH(ortholog.accession) AGAINST('$term*' IN BOOLEAN MODE)";
        //}
        if (count($where) > 0) {
            $query .= " AND " . implode(" AND ", $where);
        }


        if ($sort == "run_date") {
            $query .= " ORDER BY run_date.name";
        } else if ($sort == "lane") {
            $query .= " ORDER BY lane";
        } else if ($sort == "barcode") {
            $query .= " ORDER BY barcode";
            // } else if($sort == "gene") {
            // $query .= " ORDER BY name";
        } else if ($sort == "maize") {
            $query .= " ORDER BY maize";
        } else if ($sort == "loc") {
            $query .= " ORDER BY chr, start";
        }


        // Execute query

        $coi_result = mysqli_query($this->db, $query);
        if (!$coi_result) {
            error_log("DBTools getCOIs: " . mysqli_error() . " " . $query);
            return 0;
        }
        return $coi_result;
    }

    private function runQuery2($size, $run_id, $barcode_id, $search_type, $term, $hq, $sort) {
        // Build query
         $query = "SELECT cluster.id, chr, start, end, barcode_id, date, lane, barcode, maize, name FROM run_date, barcode, cluster WHERE run_date.id = barcode.date_id AND barcode.id = cluster.barcode_id AND cluster.size > $size AND background = 0";
	if($search_type === "ear" && strlen(trim($term)) > 0) {
		$query = "SELECT cluster.id, chr, start, end, cluster.barcode_id, date, lane, barcode, maize, name FROM run_date, barcode, cluster, ear WHERE run_date.id = barcode.date_id AND barcode.id = cluster.barcode_id AND cluster.size > $size AND ear.barcode_id = cluster.barcode_id AND ear.ear = '$term'";
	} else if ($search_type === "gene" && strlen(trim($term)) > 0) {
		$query = "SELECT cluster.id, chr, start, end, cluster.barcode_id, date, lane, barcode, maize, run_date.name FROM run_date, barcode, cluster, ear, mutant WHERE run_date.id = barcode.date_id AND barcode.id = cluster.barcode_id AND cluster.size > $size AND ear.barcode_id = cluster.barcode_id AND ear.mutant_id = mutant.id AND mutant.name = '$term'";
	
	}
      $where = array();
        if (trim($term) == "") {
            $search_type = "";
        }
        $runs = explode(",", $run_id);
        if (count($run_id) > 0) {
            if (count($runs) == 1) {
                if ($run_id != "All") {


                    $where[] = "run_date.id = $run_id";
                }
            } else {
                $where[] = "(run_date.id = " . implode(" OR run_date.id = ", $runs) . ")";
            }
        }
        $barcodes = explode(",", $barcode_id);
        if (count($barcodes) > 0 && $barcode_id != 0) {
            if (count($barcodes) == 1) {
                if ($barcode_id != "All") {
                    $where[] = "barcode.id = $barcode_id";
                }
            } else {
                $where[] = "(barcode.id = " . implode(" OR barcode.id = ", $barcodes) . ")";
            }
        }
        if ($search_type == "maize") {
            $where[] = "maize LIKE ('$term%')";
        }

        if ($hq == 1) {
            $where[] = "interesting = 1";
        }
        if ($search_type == "loc") {

            $location = preg_split("/[:-]/", str_replace(" ", "", $term));
            if (count($location) == 3) {
                $where[] = 'cluster.chr = "' . $location[0] . '" AND cluster.start >= ' . $location[1] . ' AND cluster.end <= ' . $location[2];
            }
        }
        // if($search_type == "homolog") {
        //    $where[] =  "MATCH(ortholog.accession) AGAINST('$term*' IN BOOLEAN MODE)";
        //}
        if (count($where) > 0) {
            $query .= " AND " . implode(" AND ", $where);
        }


        if ($sort == "run_date") {
            $query .= " ORDER BY date";
        } else if ($sort == "lane") {
            $query .= " ORDER BY lane";
        } else if ($sort == "barcode") {
            $query .= " ORDER BY barcode";
            // } else if($sort == "gene") {
            // $query .= " ORDER BY name";
        } else if ($sort == "maize") {
            $query .= " ORDER BY maize";
        } else if ($sort == "loc") {
            $query .= " ORDER BY chr, start";
        }


        // Execute query

        $coi_result = mysqli_query($this->db, $query);
        if (!$coi_result) {
            error_log("DBTools getCOIs: " . mysqli_error() . " " . $query);
            return 0;
        }
        return $coi_result;
    }

    private function addMutant($coi, $search_type, $term) {
        // Search for ear/mutant
        $query = "SELECT * FROM ear WHERE barcode_id = " . $coi['barcode_id'];
        if ($search_type == "ear" && strlen(trim($term)) > 0) {
            $query .= " AND ear.ear = '$term'";
        }
        $ear_result = mysqli_query($this->db, $query);
		if(!$ear_result) {
			error_log(mysqli_error($this->db) . " " . $query);
		}
        $ears = array();
        $mutants = array();
        while ($ear = mysqli_fetch_assoc($ear_result)) {
            $ears[] = $ear['ear'];
            $query = "SELECT * FROM mutant WHERE id = " . $ear['mutant_id'];
            if ($search_type == "gene" && strlen(trim($term)) > 0) {
                $query .= " AND mutant.name = '$term'";
            }
            $mutant_result = mysqli_query($this->db, $query);
            while ($mutant = mysqli_fetch_assoc($mutant_result)) {
                $mutants[] = $mutant['name'];
            }
        }
        if (count($mutants) > 0) {

            $coi['ear'] = implode($ears, ", ");
            $coi['mutant'] = implode($mutants, ", ");
            return $coi;
        } else {
            return FALSE;
        }
    }

    public function updateIdentifiedGene($gene, $id) {
        $query = 'UPDATE cluster SET identified_gene = "' . $gene . '" WHERE id = ' . $id;
        if (!mysqli_query($this->db, $query)) {
            error_log("DBTools updateIdentifiedGene: " . mysqli_error() . " $query");
        }

        return 1;
    }

    public function updatePrimer($primer, $id) {
        $query = 'UPDATE cluster SET primer = "' . $primer . '" WHERE id = ' . $id;
        if (!mysqli_query($this->db, $query)) {
            error_log("DBTools updatePrimer: " . mysqli_error() . " $query");
        }
        return 1;
    }

    public function getArabidopsis($coi_id) {
//        $query = "SELECT accession FROM ortholog WHERE cluster_id = $coi_id UNION SELECT maize AS accession FROM cluster WHERE id = $coi_id";
		$query = "SELECT arabidopsis.accession, arabidopsis.defline FROM maize_gene, arab_ortholog, arabidopsis WHERE maize_gene.cluster_id = $coi_id AND maize_gene.accession = arab_ortholog.ortholog AND CONCAT(arab_ortholog.arabidopsis, '.1') = arabidopsis.accession";


        $result = mysqli_query($this->db, $query);
		if(!$result) {
			error_log(mysqli_error($this->db) . " " . $query);
		}
        $arabidopsis = "";
		while ($row = mysqli_fetch_row($result)) {
         /*   if ($row[0] == "") {
                continue;
            }
            $ortholog = $row[0];
	    if (substr_compare($row[0], "GRMZM", 0, 5) === 0) {
 		     $ortholog = str_replace("T", "P", $row[0]);
   
	    } else if (substr_compare($row[0], "AC", 0, 2) === 0) {
		    
		     $ortholog = str_replace("T", "P", $row[0]);
		   // $ortholog = str_replace("_T02", "_P01", $row[0]);
            } else {
                $ortholog = "LOC_" . $ortholog;
            }
            //error_log($ortholog);
            $query = "SELECT arabidopsis, defline FROM arab_ortholog, arabidopsis WHERE ortholog LIKE '" . $ortholog . "%' AND arabidopsis = accession";
  	    $result2 = mysqli_query($this->db, $query);
            if ($row = mysqli_fetch_assoc($result2)) {
                $arabidopsis = $row['arabidopsis'] . ": " . $row['defline'];
                break;
            }*/
			$arabidopsis .= $row[0] . " " . $row[1];

        }
        return $arabidopsis;
    }

    public function getExcel($cois) {
        $candidates = array();
        foreach ($cois AS $candidate) {
            //  $query = "SELECT * FROM candidates";
            //$candidates_result = mysqli_query($this->db, $query);
            //if (!$candidates_result) {
            //        error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
            // }
            //forea($candidate = mysqli_fetch_assoc($candidates_result)) {
            $candidates[]['cluster_id'] = $candidate;

            $cluster_id = $candidate;
            $run_name = "";
            $chr = "";
            $start = 0;
            $end = 0;
            $maize = "";
            $insert_pos = "";
            $nucleoid = "No";
            $rice = "";
            $arab = "";
            $arab_desc = "";
            $arab_domains = "";
            $arab_tp = "";
            $arab_pred = "";
            $barcode = "";
            $barcode_id = 0;
            $target = "";
            $ear = "";
            $query = "SELECT chr, start, end, run_date.name, maize, insert_loc, barcode, barcode.id FROM cluster, barcode, run_date WHERE run_date.id = barcode.date_id AND cluster.barcode_id = barcode.id AND cluster.id = $cluster_id";
            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
                //  return "select cluster";
            }
            if ($row = mysqli_fetch_assoc($result)) {
                $run_name = $row['name'];
                $maize = $row['maize'];
                $insert_pos = $row['insert_loc'];
                $barcode = $row['barcode'];
                $barcode_id = $row['id'];
                $chr = $row['chr'];
                $start = $row['start'];
                $end = $row['end'];
            }
            $query = "SELECT ear, name FROM ear, mutant WHERE barcode_id = $barcode_id AND ear.mutant_id = mutant.id";
            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
                //  return "select ear";
            }
            if ($row = mysqli_fetch_assoc($result)) {
                $ear = $row['ear'];
                $target = $row['name'];
            }
            $query = "SELECT accession FROM rice7 WHERE cluster_id = $cluster_id";
            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
                // return "select rice";
            }
            if ($row = mysqli_fetch_assoc($result)) {
                $rice = $row['accession'];
            }
            $query = "SELECT * FROM mass_spec WHERE rice = '$rice.1'";
            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
                // return "select nucleoid";
            }
            if ($row = mysqli_fetch_assoc($result)) {
                $nucleoid = "Yes";
            }
            $query = "SELECT arabidopsis, defline, method, prediction FROM arab_ortholog, arabidopsis, target WHERE ortholog = '$rice.1' AND accession = arabidopsis AND gene = arabidopsis";
            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
                // return "select arab";
            }
            while ($row = mysqli_fetch_assoc($result)) {
                $arab = $row['arabidopsis'];
                $arab_desc = $row['defline'];
                if ($row['method'] == "targetp") {
                    $arab_tp = $row['prediction'];
                } else if ($row['method'] == "predotar") {
                    $arab_pred = $row['prediction'];
                }
            }

            $query = "SELECT description FROM domains WHERE gene = '$arab'";
            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
                // return "select domains";
            }
            $domains = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $domains[] = $row['description'];
            }

            $index = count($candidates) - 1;

            $candidates[$index]['run_name'] = $run_name;
            $candidates[$index]['chr'] = $chr;
            $candidates[$index]['start'] = $start;
            $candidates[$index]['end'] = $end;
            $candidates[$index]['maize'] = $maize;
            $candidates[$index]['insert_pos'] = $insert_pos;
            $candidates[$index]['nucleoid'] = $nucleoid;
            $candidates[$index]['rice'] = $rice;
            $candidates[$index]['arab'] = $arab;
            $candidates[$index]['arab_desc'] = $arab_desc;
            $candidates[$index]['arab_domains'] = implode(", ", $domains);
            $candidates[$index]['arab_tp'] = $arab_tp;
            $candidates[$index]['arab_pred'] = $arab_pred;
            $candidates[$index]['barcode'] = $barcode;
            $candidates[$index]['target'] = $target;
            $candidates[$index]['ear'] = $ear;

            //$query = "INSERT INTO candidates (maize, insert_pos, nucleoid, rice, arab, arab_desc, arab_domains, arab_tp, arab_pred, barcode, target, ear) VALUES ('" .
            //      $maize . "', '" . $insert_pos . "', '" . $nucleoid . "', '" . $rice . "', '" . $arab . "', '" . $arab_desc . "', '" . implode(", ", $domains) . "', '" . $arab_tp . "', '" .
            //    $arab_pred . "', '" . $barcode . "', '" . $target . "', '" . $ear . "')";
        }
		error_log(print_r($candidates, true));
        return $candidates;
    }

    public function saveCandidates($cois) {

        foreach ($cois AS $coi) {

            $query = "INSERT INTO candidates (cluster_id) VALUE ($coi)";

            if (!mysqli_query($this->db, $query)) {
                error_log("DBTools saveCandidates: " . mysqli_error() . " " . $query);
                return 0;
            }
        }
        return 1;
    }

    function getCandidates() {
        $query = "SELECT * FROM candidates";
        $candidates_result = mysqli_query($this->db, $query);
        $candidates = array();
        if (!$candidates_result) {
            error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
        }
        while ($candidate = mysqli_fetch_assoc($candidates_result)) {
            $candidates[] = $candidate;

            $cluster_id = $candidate['cluster_id'];
            $run_name = "";
            $maize = "";
            $insert_pos = "";
            $nucleoid = "No";
            $rice = "";
            $arab = "";
            $arab_desc = "";
            $arab_domains = "";
            $arab_tp = "";
            $arab_pred = "";
            $barcode = "";
            $barcode_id = 0;
            $target = "";
            $ear = "";
            $query = "SELECT run_date.name, maize, insert_loc, barcode, barcode.id FROM cluster, barcode, run_date WHERE run_date.id = barcode.date_id AND cluster.barcode_id = barcode.id AND cluster.id = $cluster_id";
            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
                //  return "select cluster";
            }
            if ($row = mysqli_fetch_assoc($result)) {
                $run_name = $row['name'];
                $maize = $row['maize'];
                $insert_pos = $row['insert_loc'];
                $barcode = $row['barcode'];
                $barcode_id = $row['id'];
            }
            $query = "SELECT ear, name FROM ear, mutant WHERE barcode_id = $barcode_id AND ear.mutant_id = mutant.id";
            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
                //  return "select ear";
            }
            if ($row = mysqli_fetch_assoc($result)) {
                $ear = $row['ear'];
                $target = $row['name'];
            }
            $query = "SELECT accession FROM rice7 WHERE cluster_id = $cluster_id";
            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
                // return "select rice";
            }
            if ($row = mysqli_fetch_assoc($result)) {
                $rice = $row['accession'];
            }
            $query = "SELECT * FROM mass_spec WHERE rice = '$rice.1'";
            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
                // return "select nucleoid";
            }
            if ($row = mysqli_fetch_assoc($result)) {
                $nucleoid = "Yes";
            }
            $query = "SELECT arabidopsis, defline, method, prediction FROM arab_ortholog, arabidopsis, target WHERE ortholog = '$rice.1' AND accession = arabidopsis AND gene = arabidopsis";
            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
                // return "select arab";
            }
            while ($row = mysqli_fetch_assoc($result)) {
                $arab = $row['arabidopsis'];
                $arab_desc = $row['defline'];
                if ($row['method'] == "targetp") {
                    $arab_tp = $row['prediction'];
                } else if ($row['method'] == "predotar") {
                    $arab_pred = $row['prediction'];
                }
            }

            $query = "SELECT description FROM domains WHERE gene = '$arab'";
            $result = mysqli_query($this->db, $query);
            if (!$result) {
                error_log("DBTools getCandidates: " . mysqli_error() . " " . $query);
                // return "select domains";
            }
            $domains = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $domains[] = $row['description'];
            }

            $index = count($candidates) - 1;

            $candidates[$index]['run_name'] = $run_name;
            $candidates[$index]['maize'] = $maize;
            $candidates[$index]['insert_pos'] = $insert_pos;
            $candidates[$index]['nucleoid'] = $nucleoid;
            $candidates[$index]['rice'] = $rice;
            $candidates[$index]['arab'] = $arab;
            $candidates[$index]['arab_desc'] = $arab_desc;
            $candidates[$index]['arab_domains'] = implode(", ", $domains);
            $candidates[$index]['arab_tp'] = $arab_tp;
            $candidates[$index]['arab_pred'] = $arab_pred;
            $candidates[$index]['barcode'] = $barcode;
            $candidates[$index]['target'] = $target;
            $candidates[$index]['ear'] = $ear;

            //$query = "INSERT INTO candidates (maize, insert_pos, nucleoid, rice, arab, arab_desc, arab_domains, arab_tp, arab_pred, barcode, target, ear) VALUES ('" .
            //      $maize . "', '" . $insert_pos . "', '" . $nucleoid . "', '" . $rice . "', '" . $arab . "', '" . $arab_desc . "', '" . implode(", ", $domains) . "', '" . $arab_tp . "', '" .
            //    $arab_pred . "', '" . $barcode . "', '" . $target . "', '" . $ear . "')";
        }
        return $candidates;
    }

    function updatePCR($ear_id, $coi_id, $pcr_confirmed, $heritable, $homozygous, $comments) {
        $query = "SELECT maize FROM cluster WHERE id = $coi_id";
        $result = mysqli_query($this->db, $query);
        $maize = "";
        if ($row = mysqli_fetch_row($result)) {
            $maize = $row[0];
        } else {
            error_log("DBTools updatePCR: " . mysqli_error() . " $query");
        }

        $query = "SELECT * FROM ear_annotation WHERE ear_id = $ear_id AND coi_id = $coi_id";

	$result = mysqli_query($this->db, $query);
	error_log($query);
        if (!$result) {
            error_log("DBTools updatePCR: " . mysqli_error() . " $query");
	}
	if ($row = mysqli_fetch_assoc($result)) {
		error_log($comments);
            $query = "UPDATE ear_annotation SET pcr_confirmed = $pcr_confirmed, heritable = $heritable, homozygous = $homozygous, comments = '$comments', maize = '$maize' WHERE id = " . $row['id'];
		if(mysqli_query($this->db, $query) !== TRUE) {
			error_log("DBTools updatePCR: " . $mysqli->error . " " . $query);
		}
            return 1;
        } else {
            $query = "INSERT INTO ear_annotation (pcr_confirmed, heritable, homozygous, ear_id, comments, maize, coi_id) VALUES ($pcr_confirmed, $heritable, $homozygous, $ear_id, '$comments', '$maize', $coi_id)";
            mysqli_query($this->db, $query);
            return 1;
        }
        return 0;
    }

    function getEar($ear_id, $maize) {
        $query = "SELECT * FROM ear, mutant WHERE ear.id = $ear_id AND mutant.id = mutant_id";
        $result = mysqli_query($this->db, $query);
        $ear_row = mysqli_fetch_assoc($result);
        $ear = array();
        $ear['id'] = $ear_id;
        $ear['mutant'] = $ear_row['name'];
        $ear['name'] = $ear_row['ear'];
        $ear['pcr_confirmed'] = 0;
        $ear['heritable'] = 0;
        $ear['homozygous'] = 0;
        $ear['comments'] = "";

        $query = "SELECT * FROM ear_annotation WHERE ear_id = $ear_id";
        $result = mysqli_query($this->db, $query);
	while ($ear_row = mysqli_fetch_assoc($result)) {
            if (substr($ear_row['maize'], 0, 13) == $maize) {
                //error_log($ear_row['maize'] . " " . $maize);
                //  $ear['comments'] = "This ear has been annotated for maize gene " . $ear_row['maize'];
                // return $ear;
                $ear['pcr_confirmed'] = $ear_row['pcr_confirmed'];
                $ear['heritable'] = $ear_row['heritable'];
                $ear['homozygous'] = $ear_row['homozygous'];
                $ear['comments'] = $ear_row['comments'];
            }
        }
        return $ear;
    }

}

?>
