<?php


require_once("../globals.php");
require_once("$srcdir/patient.inc");

// if(isset($_POST['form_id']) && $_POST['form_id']=='family_data'){

    $pid=$_POST['pid'];
    $prefix = 'form_';
    $newdata = array();
    $value  = '';
    $layout = sqlStatement("SELECT * FROM layout_options 
                        WHERE data_type= ?  AND form_id= ? AND group_id= ? AND seq != ? AND seq != ? ORDER BY seq", array(2,'HIS',2,7,9));

    while ($frow = sqlFetchArray($layout)) {
        $value  = '';
        $field_id  = $frow['field_id'];
        $field_id_esc= htmlspecialchars($field_id, ENT_QUOTES);

        foreach ($_POST["$prefix$field_id"] as $key => $val) {
            if (strlen($value)) {
                $value .= '|';
            }
            $value .= $key;
           
        }

        $newdata[$field_id_esc]=$value;


    }

    if(isset($_POST['pid'] )){
        $newdata['pid']=$_POST['pid'];
    }
    
    $s=updateHistoryData($pid, $newdata);
//     print_r($s);

// exit;


    header('Location: health_history.php'); 
  



?>