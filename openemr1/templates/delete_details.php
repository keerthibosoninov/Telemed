<?php

require_once("../interface/globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/erx_javascript.inc.php");



if (isset($_POST["mode"]) && $_POST["mode"] == "pharmacy_delete" && $_POST["action"] == "Delete") {

    $id=$_POST['id'];
        $query = "delete from pharmacies where id=$id";
        $res = sqlStatement($query);
        // header('Location:facilities_add.php');
        // exit();
}

if (isset($_POST["mode"]) && $_POST["mode"] == "insurance_delete" && $_POST["action"] == "Delete") {

    $id=$_POST['id'];
        $query = "delete from insurance_companies where id=$id";
        $res = sqlStatement($query);
        // header('Location:facilities_add.php');
        // exit();
}
    

    

?>