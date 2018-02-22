<?php

/*
 * Nicholas Stiffler
 * Institute of Molecular Biology
 * Barkan Lab
 * October 21, 2009
 *
 * Provide responses to ajax requests
 */
header('Access-Control-Allow-Origin: *');
require_once("DBTools.php");
session_start();
$db = new DBTools();
if (isset($_GET['version']) && $_GET['version'] == "old") {
    $db->setOldDB();
}

//foreach ($_GET AS $key => $value) {
//    if (!is_array($_GET[$key])) {
//        $_GET[$key] = filter_input(INPUT_GET, $key, //mysqli_real_escape_string(trim($value));
//    }
//}

$dispatch = array("get_runs" => "getRuns",
    "get_barcodes" => "getBarcodes",
    "update_coi" => "getCOIs",
    "details" => "getDetails",
    "identified_gene" => "updateIdentifiedGene",
    "primer" => "updatePrimer",
    "row_div" => "getArabidopsis",
    "coi" => "saveCandidates",
    "pcr" => "updatePCR",
    "ear" => "getEar",
	"download" => "fetchExcel");

foreach ($_GET as $action => $value) {

	if (isset($dispatch[$action])) {
        echo call_user_func($dispatch[$action], (filter_input(INPUT_GET, $action, FILTER_SANITIZE_STRING)));
        exit();
    }
}

function getRuns() {
    global $db;
    return json_encode($db->getRuns());
}

function getBarcodes() {
    global $db;
    return json_encode($db->getBarcodes(intval($_GET['get_barcodes'])));
}

function getCOIs() {
    global $db;
    $run_id = 0;
    if (isset($_GET['run_id'])) {
        $run_id = $_GET['run_id'];
    }
    $barcode_id = 0;
    if (isset($_GET['barcode_id'])) {
        $barcode_id = $_GET['barcode_id'];
    }
    $mass_spec = 0;
    if (isset($_GET['mass_spec'])) {
        $mass_spec = 1;
    }
    $term = "";
    if (isset($_GET['text_search'])) {
	    $term = $_GET['text_search'];
    }
    $hq = 0;
    if (isset($_GET['hq'])) {
        $hq = $_GET['hq'];
    }
    $ig = 0;
    if (isset($_GET['ig'])) {
        $ig = $_GET['ig'];
    }
    $cp = 0;
    if (isset($_GET['cp'])) {
        $cp = $_GET['cp'];
    }
    $cp_klaas = 0;
    if(isset($_GET['cp_klaas'])) {
        $cp_klaas = $_GET['cp_klaas'];
    }
    $overlap = 0;
    if(isset($_GET['overlap'])) {
        $overlap = $_GET['overlap'];
    }
    $arab_pprs = 0;
    if(isset($_GET['arab_pprs'])) {
        $arab_pprs = $_GET['arab_pprs'];
    }
    $alleles_needed = 0;
    if(isset($_GET['alleles_needed'])) {
        $alleles_needed = $_GET['alleles_needed'];
    }
	$cois = $db->getCOIs($run_id, $_GET['sort'], $barcode_id, $term, $overlap, $_GET['search_type']);
    return json_encode(array("count" => count($cois), "data" => array_slice($cois, $_GET['page'] * 100, 100)));
}

function updateIdentifiedGene() {
    global $db;
    return $db->updateIdentifiedGene($_GET['identified_gene'], $_GET['coi_id']);
}

function updatePrimer() {
    global $db;
    return $db->updatePrimer($_GET['primer'], $_GET['coi_id']);
}

function getDetails() {
    global $db;
    return json_encode($db->getCOI(intval($_GET['details'])));
}

function getArabidopsis() {
    global $db;
    return $db->getArabidopsis($_GET['row_div']);
}

function fetchExcel() {
    global $db;
    header('Content-disposition: attachment; filename=clusters.xls');
    header('Content-type: application/vnd.ms-excel');
	$cois = $db->getCOIs($_GET['run_id'], $_GET['sort'], $_GET['barcode_id'], $_GET['text_search'], $_GET['overlap'], $_GET['search_type']);
//    $data = $db->getExcel(explode(",", $_GET['download']));
    $flag = false;
    foreach ($cois as $row) {
        if (!$flag) { // display field/column names as first row  
            echo implode("\t", array_keys($row)) . "\n";
            $flag = true;
        }
		$row['arabidopsis'] = $db->getArabidopsis($row['id']);
		$row['maize'] = implode(", ", $row['maize']);
        echo implode("\t", array_values($row)) . "\n";
    }
}

function saveCandidates() {
    global $db;
    if ($_GET['candidates'] == "download") {
        header('Content-disposition: attachment; filename=clusters.xls');
        header('Content-type: application/vnd.ms-excel');
        return fetchExcel();
    }

    $db->saveCandidates($_GET['coi']);

    header('Location: http://teosinte.uoregon.edu/mutant');
}

function updatePCR() {
    global $db;
    echo $db->updatePCR($_GET['pcr'], $_GET['coi_id'], $_GET['pcr_confirmed'], $_GET['heritable'], $_GET['homozygous'], $_GET['comments']);
}

function getEar() {
    global $db;
    
    echo json_encode($db->getEar($_GET['ear'], $_GET['maize']));
}

echo 0;
