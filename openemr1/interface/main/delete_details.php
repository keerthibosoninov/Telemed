<?php

require_once("../globals.php");




if(isset($_POST['form']) && $_POST['form']=="medical_history" && isset($_POST['action']) && $_POST['action']=="delete"){
    $id=$_POST['id'];
    sqlStatement("DELETE FROM medical_history where id=$id");
    
}
if(isset($_POST['form']) && $_POST['form']=="surgical_history" && isset($_POST['action']) && $_POST['action']=="delete"){
    $id=$_POST['id'];
    sqlStatement("DELETE FROM surgical_history where id=$id");
   
}


?>