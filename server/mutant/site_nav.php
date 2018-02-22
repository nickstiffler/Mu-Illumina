<?php
/*
 * Nicholas Stiffler
 * Institute of Molecular Biology
 * Barkan Lab
 * October 21, 2009
 *
 * static functions for providing the basic html of the site
 */


if(isset($_POST["popup"])) {
    switch($_POST['popup']) {
        case "insertiondetails":
            echo insertion_details();
            break;
        case "candidates":
            echo save_candidates();
            break;
        default:
            echo 0;
            break;
    }
}

function insertion_details() {
    $content =<<<HTM
        <form name="edit_coi"><input type="hidden" name="id" value="0"></form>
        <div class="box">
        
        <p class="heading">Run <img id="run_img" src="ajax-loader.gif" /></p>
        <div class="row"><span class="label">Run</span><span id="run_date"></span></div>
        <!-- <div class="row"><span class="label">Lane</span><span id="lane"></span></div> -->
        <div class="row"><span class="label">Barcode</span><span id="barcode"></span></div>
       <!-- <div class="row"><span class="label">Target Gene</span><span id="mutant"></span></div> -->
        <div class="row"><span class="label">Ear</span><span id="ear" class="content"></span></div>
        <div id="pcr" style="display: none; background-color: gray;">
            <form name="ear_annotation">
            <input name="ear_id" value="0" type="hidden" /></form>
            <div class="row"><span class="label">&nbsp;</span><span>PCR Confirmed?
                <select name="pcr_confirmed" id="pcr_confirmed">
                <option value="0"></option>
                <option value="1">Yes</option>
                <option value="2">No</option>
                </select>
                </span></div>
            <div class="row"><span class="label">&nbsp;</span><span>Is Heritable?
                <select name="heritable" id="heritable">
                <option value="0"></option>
                <option value="1">Yes</option>
                <option value="2">No</option>
                </select>
            </span></div>
            <div class="row"><span class="label">&nbsp;</span><span>Is Homozygous?
                <select name="homozygous" id="homozygous">
                <option value="0"></option>
                <option value="1">Yes</option>
                <option value="2">No</option>
                </select>
            </span></div>
            <div class="row"><span class="label">&nbsp;</span><span>Comments?
                <textarea name="comments" id="comments"></textarea>
            </span></div>
            <div class="row"><span class="label">&nbsp;</span><span><a href="#" onclick="savePCR()">save</a> <a href="#" onclick="document.getElementById('pcr').style.display='none'">cancel</a></span></div>
            
        </div>
        <div class="row"></div>
        </div>
        <div class="box">
        <p class="heading">Cluster <img id="cluster_img" src="ajax-loader.gif" /></p>
        <div class="row"><span class="label">Second Maize Gene</span><span id="alt_gene"></span></div>
        <div class="row"><span class="label">Chromosome</span><span id="chr"></span></div>
        <div class="row"><span class="label">Location</span><span id="loc"></span></div>
        <div class="row"><span class="label">Width</span><span id="width"></span></div>
        <div class="row"><span class="label">Size</span><span id="size"></span></div>
        <div class="row"><span class="label">Maize Gene</span><span id="maize"></span></div>
        <div class="row"><span class="label">Insertion Site</span><span id="insert"></span></div>
        <div class="row"><span class="label">Insertion Position</span><span id="insert_loc"></span></div>
        <div class="row"><span class="label">Sequence</span><span class="content" id="seq"></span></div>
        
        <div class="row"><span class="label">Identified Gene</span><span id="identified_gene"></span></div>
        <div class="row"><span class="label">Primer</span><span class="content" id="primer"></span></div>
        <div class="row"></div>
        </div>
        <div class="box">
        <p class="heading">Ortholog<img id="homolog_img" src="ajax-loader.gif" /></p>
        <div class="row"><span class="label">Ortholog</span><span id="ortholog"></span></div>
        <div class="row"><span class="label">Nucleoid Proteome</span><span id="mass_spec"></span></div>
        <div class="row"><span class="label">Protein Experimentally Identified in the Chloroplast</span><span id="cp_prot"></span></div>
        <div class="row"><span class="label">Domains</span><span class="content" id="domains"></span></div>
        <div class="row"><span class="label">TargetP</span><span id="tp"></span></div>
        <div class="row"><span class="label">Predotar</span><span id="pred"></span></div>
        <div class="row"><span class="label">Arabidopsis</span><span id="arab"></span></div>
        <div class="row"><span class="label">Arab. Description</span><span id="description" class="content"></span></div>
        <div class="row"><span class="label">Arab. Domains</span><span id="arab_domains" class="content"></span></div>
        <div class="row"><span class="label">Arab. TargetP</span><span id="arab_tp"></span></div>
        <div class="row"><span class="label">Arab. Predotar</span><span id="arab_pred"></span></div>
        <div class="row"></div>
        
        </div>
HTM;

    return $content;




}

function save_candidates() {
    $content =<<<HTM
    
HTM;
    return $content;
}
