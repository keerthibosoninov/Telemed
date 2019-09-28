<?php

require_once("../globals.php");



require_once("$srcdir/pid.inc");
require_once("$srcdir/patient.inc");

 

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


if(isset($_POST['form_id']) && $_POST['form_id']=='current_data'){


    $subjective=  $_POST["subjective"];
    $objective=  $_POST["objective"];
    $assessment=  $_POST["assessment"];
    $plan = $_POST["plan"];   
    $activity =$_POST["activity"];
    $pid =$_POST["pat_id"];

    $height=  $_POST["height"];
    $weight=  $_POST["weight"];
    $temperature=  $_POST["temp"];
    $temp_method = $_POST["temp_method"];   
    $pulse =$_POST["pulse"];
    $bp =$_POST["bp"];
    $bp1=explode("/",$bp);
     $bpd=$bp1[0];
     $bps=$bp1[1];

     $facility2 =$_POST["facility2"];
     $reason2 =$_POST["reason2"];

        
     $query = ("replace into form_soap set
        
     subjective='" . add_escape_custom($subjective) . "',        
     objective='" . add_escape_custom($objective) . "',
     assessment='" . add_escape_custom($assessment) . "',     
     plan='" . add_escape_custom($plan) . "',
           
     activity='" . add_escape_custom($activity) . "',     
     pid='" . add_escape_custom($pid) . "',
     date=NOW()");        

     $id=sqlInsert($query);
     if($id)
     {
      $query1 = ("replace into form_vitals set
        
      height='" . add_escape_custom($height) . "',        
      weight='" . add_escape_custom($weight) . "',
      temperature='" . add_escape_custom($temperature) . "',     
      temp_method='" . add_escape_custom($temp_method) . "',
      pulse='" . add_escape_custom($pulse) . "',
      bpd='" . add_escape_custom($bpd) . "',
      bps='" . add_escape_custom($bps) . "',
      activity='" . add_escape_custom($activity) . "',     
      pid='" . add_escape_custom($pid) . "',
      date=NOW()");        
 
      sqlInsert($query1);

     }

    
     sqlStatement(
       "UPDATE form_encounter SET      
           reason = '" . add_escape_custom($reason2) . "',
           facility ='" . add_escape_custom($facility2) . "', 
           date = NOW() WHERE pid = '" . add_escape_custom($pid) . "'"
   );
 //   sqlStatement($query2);
   }




?>