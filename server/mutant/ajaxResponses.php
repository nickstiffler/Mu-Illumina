<?php

/*
 * Nicholas Stiffler
 * Institute of Molecular Biology
 * Barkan Lab
 * October 21, 2009
 *
 * Provide responses to ajax requests
 */
//header('Access-Control-Allow-Origin: *');
require_once("DBTools.php");
session_start();
$db = new DBTools();
if (isset($_POST['version']) && $_POST['version'] == "old") {
    $db->setOldDB();
}

//foreach ($_POST AS $key => $value) {
//    if (!is_array($_POST[$key])) {
//        $_POST[$key] = filter_input(INPUT_POST, $key, //mysqli_real_escape_string(trim($value));
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
    "ear" => "getEar");

foreach ($_POST as $action => $value) {

	if (isset($dispatch[$action])) {
        echo call_user_func($dispatch[$action], (filter_input(INPUT_POST, $action, FILTER_SANITIZE_STRING)));
        exit();
    }
}

function getRuns() {
    global $db;
    return json_encode($db->getRuns());
}

function getBarcodes() {
    global $db;
    return json_encode($db->getBarcodes(intval($_POST['get_barcodes'])));
}

function getCOIs() {
    global $db;
    $run_id = 0;
    if (isset($_POST['run_id'])) {
        $run_id = $_POST['run_id'];
    }
    $barcode_id = 0;
    if (isset($_POST['barcode_id'])) {
        $barcode_id = $_POST['barcode_id'];
    }
    $mass_spec = 0;
    if (isset($_POST['mass_spec'])) {
        $mass_spec = 1;
    }
    $term = "";
    if (isset($_POST['text_search'])) {
        $term = $_POST['text_search'];
    }
    $hq = 0;
    if (isset($_POST['hq'])) {
        $hq = $_POST['hq'];
    }
    $ig = 0;
    if (isset($_POST['ig'])) {
        $ig = $_POST['ig'];
    }
    $cp = 0;
    if (isset($_POST['cp'])) {
        $cp = $_POST['cp'];
    }
    $cp_klaas = 0;
    if(isset($_POST['cp_klaas'])) {
        $cp_klaas = $_POST['cp_klaas'];
    }
    $overlap = 0;
    if(isset($_POST['overlap'])) {
        $overlap = $_POST['overlap'];
    }
    $arab_pprs = 0;
    if(isset($_POST['arab_pprs'])) {
        $arab_pprs = $_POST['arab_pprs'];
    }
    $alleles_needed = 0;
    if(isset($_POST['alleles_needed'])) {
        $alleles_needed = $_POST['alleles_needed'];
    }
    return json_encode($db->getCOIs($run_id, $_POST['sort'], $barcode_id, $mass_spec, $cp_klaas, $term, $overlap, $_POST['cluster_size'], $hq, $_POST['search_type'], $ig, $cp, $arab_pprs, $alleles_needed));
}

function updateIdentifiedGene() {
    global $db;
    return $db->updateIdentifiedGene($_POST['identified_gene'], $_POST['coi_id']);
}

function updatePrimer() {
    global $db;
    return $db->updatePrimer($_POST['primer'], $_POST['coi_id']);
}

function getDetails() {
    global $db;
    return json_encode($db->getCOI(intval($_POST['details'])));
}

function getArabidopsis() {
    global $db;
    return $db->getArabidopsis($_POST['row_div']);
}

function fetchExcel() {
    global $db;
    header('Content-disposition: attachment; filename=clusters.xls');
    header('Content-type: application/vnd.ms-excel');
    $data = $db->getExcel($_POST['coi']);
    $flag = false;
    foreach ($data as $row) {
        if (!$flag) { // display field/column names as first row  
            echo implode("\t", array_keys($row)) . "\n";
            $flag = true;
        }
        echo implode("\t", array_values($row)) . "\n";
    }
}

function saveCandidates() {
    global $db;
    if ($_POST['candidates'] == "download") {
        header('Content-disposition: attachment; filename=clusters.xls');
        header('Content-type: application/vnd.ms-excel');
        return fetchExcel();
    }

    $db->saveCandidates($_POST['coi']);

    header('Location: http://teosinte.uoregon.edu/mutant');
}

function updatePCR() {
    global $db;
    echo $db->updatePCR($_POST['pcr'], $_POST['coi_id'], $_POST['pcr_confirmed'], $_POST['heritable'], $_POST['homozygous'], $_POST['comments']);
}

function getEar() {
    global $db;
    
    echo json_encode($db->getEar($_POST['ear'], $_POST['maize']));
}

echo 0;
