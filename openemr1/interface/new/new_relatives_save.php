<?php

require_once("../globals.php");



// require_once("$srcdir/pid.inc");
// require_once("$srcdir/patient.inc");

 

// if (isset($_POST['fname'])) {

//   $result = sqlQuery("select max(pid)+1 as pid from patient_data"); 
//   $newpid = 1;
//   if ($result['pid'] > 1) 
//   {
//     $newpid = $result['pid'];
//   }
//   setpid($newpid);
//   if ($pid == null) 
//   {
//     $pid = 0;
//   }


   if (isset($_POST['cancer'])) {

    $cancer=  $_POST["cancer"];
    $diabetes=  $_POST["diabetes"];
    $epilepsy=  $_POST["epilepsy"];
    $suicide = $_POST["suicide"];
    $tuberclosis =$_POST["tuberclosis"];
    $bp =$_POST["bp"];    
    $heart_pblm =$_POST["heart_pblm"];
    $stroke =$_POST["stroke"];
    $mental_illness =$_POST["mental_illness"];
    $pid =$_POST["pid"];
     
     $query = ("replace into history_data set
        
     relatives_cancer='" . add_escape_custom($cancer) . "',        
     relatives_tuberculosis='" . add_escape_custom($tuberclosis) . "',
     relatives_high_blood_pressure='" . add_escape_custom($bp) . "',     
     relatives_diabetes='" . add_escape_custom($diabetes) . "',
     relatives_heart_problems='" . add_escape_custom($heart_pblm) . "',        
     relatives_stroke='" . add_escape_custom($stroke) . "',
     relatives_epilepsy='" . add_escape_custom($epilepsy) . "',
     relatives_mental_illness='" . add_escape_custom($mental_illness) . "',        
     relatives_suicide='" . add_escape_custom($suicide) . "',     
     pid='" . add_escape_custom($pid) . "',
     date=NOW()");        

     sqlInsert($query);
   }

//    if (isset($_POST['cancer'])) {

//     $cancer=  $_POST["cancer"];
//     $diabetes=  $_POST["diabetes"];
//     $epilepsy=  $_POST["epilepsy"];
//     $suicide = $_POST["suicide"];
//     $tuberclosis =$_POST["tuberclosis"];
//     $bp =$_POST["bp"];    
//     $heart_pblm =$_POST["heart_pblm"];
//     $stroke =$_POST["stroke"];
//     $mental_illness =$_POST["mental_illness"];
//     $pid =$_POST["pid"];
     
//      $query = ("replace into history_data set
        
//      relatives_cancer='" . add_escape_custom($cancer) . "',        
//      relatives_tuberculosis='" . add_escape_custom($tuberclosis) . "',
//      relatives_high_blood_pressure='" . add_escape_custom($bp) . "',     
//      relatives_diabetes='" . add_escape_custom($diabetes) . "',
//      relatives_heart_problems='" . add_escape_custom($heart_pblm) . "',        
//      relatives_stroke='" . add_escape_custom($stroke) . "',
//      relatives_epilepsy='" . add_escape_custom($epilepsy) . "',
//      relatives_mental_illness='" . add_escape_custom($mental_illness) . "',        
//      relatives_suicide='" . add_escape_custom($suicide) . "',     
//      pid='" . add_escape_custom($pid) . "',
//      date=NOW()");        

//      sqlInsert($query);
//    }


?>