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


if(isset($_POST['form_del']) && $_POST['form_del']=='form_delt'){


    $id=  $_GET['id'];
    
     if($id)
     {
       sqlStatement("DELETE FROM users WHERE id = ? AND username = ''", array($id));
      //  echo $id;
     }

    
    
   }




?>