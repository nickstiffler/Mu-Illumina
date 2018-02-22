<?php
require_once("DBTools.php");
$db = new DBTools();
$candidates = $db->getCandidates();

?>

<html>
    <body>
        <table border="1">
            <tr>
                <td>Run</td><td>Maize Gene</td><td>Insertion Position</td><td>Nucleoid</td><td>Rice Ortholog</td><td>Arabidopsis Ortholog</td><td>Arabidopsis Description</td><td>Arabidopsis Domains</td><td>Arab TargetP</td><td>Arab Predotar</td><td>Barcode</td><td>Target Gene</td><td>Ear</td><td>Comments</td>
            </tr>


<?php
foreach($candidates AS $candidate) {

    echo "<tr><td>" . $candidate['run_name'] . "</td><td>" . $candidate['maize'] . "</td><td>" . $candidate['insert_pos']  . "</td><td>" . $candidate['nucleoid']  . "</td><td>" . $candidate['rice']  . "</td><td>" . $candidate['arab']  . "</td><td>" . $candidate['arab_desc']  . "</td><td>" . $candidate['arab_domains']  . "</td><td>" . $candidate['arab_tp']  . "</td><td>" . $candidate['arab_pred']  . "</td><td>" . $candidate['barcode']  . "</td><td>" . $candidate['target']  . "</td><td>" . $candidate['ear']  . "</td><td>" . $candidate['comments']  . "</td></tr>";
}

?>

        </table>
    </body>
</html>