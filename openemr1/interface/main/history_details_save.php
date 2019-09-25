<?php


require_once("../globals.php");
require_once("$srcdir/patient.inc");

if(isset($_POST['form_id']) && $_POST['form_id']=='family_data'){


    foreach ($_POST as $key => $value) {

        if($key=='form_id'){
            continue;
        }
        
        $newdata[$key]=$value;
    }
    $pid=$_POST['pid'];
    
    updateHistoryData($pid, $newdata);
    // include_once('health_history.php');

    header('Location: health_history.php'); 
  


}


?>