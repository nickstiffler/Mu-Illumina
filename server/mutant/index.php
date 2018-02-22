<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>Illumina Insertion Database</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
        <meta http-equiv="content-style-type" content="text/css" />
        
        
        <script type="text/javascript" src="midb.js"></script>
        <link href="style.css" rel="stylesheet" type="text/css" />
       
    </head>
    <body onload="updateRunList();">
        <div id="boxes">
            <div id="dialog" class="window">
                <a href="#" class="close">close</a>
                <div id="details"></div>
               
            </div>
            <div id="mask"></div>
            </div>
        <div id="content">
            <div class="box" style="height: 330px;">

                <form name="search" action="">

                     <!-- <input name="run_button" type="button" onclick="showDropDown('run_date_div')" value="select" /> -->
                    <div id="run_date_div">
                        Run <input type="button" value="Select All" onclick="selectRun('all');" /><!--<input type="button" value="PML" onclick="selectRun('pml');" /><input type="button" value="Illumina" onclick="selectRun('illumina');" /> --><br />
                        
                       <!-- <select name="date" multiple>
                        </select><br /><input onclick="acceptDropDown('run_date_div')" type="button" value="close" /> -->

                    </div>
                     <!--<input name="bc_button" type="button" onclick="showDropDown('barcode_div')" value="select" /> -->
                    <div id="barcode_div">
                        Barcode<br />
                       <!-- <select name="barcode" multiple="multiple">
                        </select><br /><input onclick="acceptDropDown('barcode_div')" type="button" value="close" /> -->
                    </div>
                    <div id="options_div">
                    
                    
                    <div id="limit_box">
                    Limit To:<br />
                     <input type="checkbox" name="mass_spec" onchange="clearTable();" />
                     Nucleoid Proteome<br />
                     <input type="checkbox" name="cp_klaas" onchange="clearTable()"/>
                     van Wijk-curated Maize Chloroplast Proteome (updated 01/09/2012)<br />
                    <!-- <input type="checkbox" name="high_quality" onchange="clearTable();" />
                   Well Supported Clusters<br /> -->
                   <input type="checkbox" name="interesting" onChange="clearTable()" /> 
                    Favorite Genes (second alleles, updated 08/31/2012)<br />
                    <input type="checkbox" name="cp" onChange="clearTable()" />
                    Chloroplast Targeting<br />
                    <input type="checkbox" name="overlap" onChange="clearTable()" />
                    Limit to insertions in common among selected barcodes (new, 09/15/2012)<br />
                    <input type="checkbox" name="arab_pprs" onChange="clearTable()" />
                    Maize P-type PPRs (updated, 04/01/2014)<br />
                    <input type="checkbox" name="alleles_needed" onchange="clearTable()" />
                    Needed second alleles (new, 04/18/2013)
                    <br /><br />
                    </div>
                    Search
                    <input type="text" name="text_search" size="15" onkeyup="clearTable()" />
                    <select name="search_type" onchange="clearTable()">
                        <option value="maize">Maize Gene ID</option>
                        <option value="rice">Rice Gene ID</option>
                        <option value="arab">Arabidopsis Gene ID</option>
                        <option value="gene">Target Gene</option>
                        <option value="ear">Ear</option>
                    </select><br />
                    Minimum Cluster Size
                    <select name="cluster_size" onchange="clearTable()">
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="400" selected="selected">400</option>
                        <option value="800">800</option>
                        <option value="1600">1600</option>
                    </select><br />
                   <p>An * next to the run name indicates it has been aligned directly to the Maize v3 genome, and not just converted from Maize v2 alignments.</p> 
                    <a href="/mutant_v2">This setting will search maize genome v3. If you want to search v2, click here.</a><br />
                    <p><b>Displaying <span id="count">0</span> clusters.</b></p>
                    </div>

                </form>

            </div>
            <div class="box">
                <form name="download" action="ajaxResponses.php" method="post">
                    <table id="coi_table" width="100%">
                        <input type="hidden" name="candidates" value="save" />
                        <tr>
                            <th><input type="button" value="Save" onclick="saveCandidates()"/><input type="button" value="Download" onclick="downloadClusters()" /><br /><input type="button" value="Select  All" onclick="selectAll()" /></th>
                            <th><a href="#" onclick="setSort('run_date');">Run</a></th>
                           <!-- <th><a href="#" onclick="setSort('lane');">Lane</a></th> -->
                            <th><a href="#" onclick="setSort('barcode');">Barcode</a></th>
                            <th><a href="#" onclick="setSort('gene');">Targeted Gene</a></th>
                            <th><a href="#" onclick="setSort('maize');">Maize Gene</a></th>
                            <th><a href="#" onclick="setSort('homolog');">Ortholog</a></th>
                            <th><a href="#" onclick="setSort('loc');">Cluster Location</a></th>
                            <th>Details</th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="box" id="new_search">
                Press the "search" button to display results.
                <input type="button" value="search" onClick="updateCOI()" />
                <img id="search_img" src="ajax-loader.gif" style="visibility: hidden;" alt="" />
            </div>
            <div id="bg" class="grey"></div>
            <div id="popup"></div>
            <div id="runs"></div>
            <div id="progress" class="grey"></div>
            
        </div>
    </body>
</html>
