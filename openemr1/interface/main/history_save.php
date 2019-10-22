<?php


require_once("../globals.php");
require_once("$srcdir/patient.inc");

// if(isset($_POST['form_id']) && $_POST['form_id']=='family_data'){

    $pid=$_POST['pid'];
    $prefix = 'form_';
    $newdata = array();
    $value  = '';
    $value2  = '';
    $inarray=array();
    $layout = sqlStatement("SELECT * FROM layout_options 
                        WHERE data_type= ?  AND form_id= ? AND group_id= ? AND seq != ? AND seq != ? ORDER BY seq", array(2,'HIS',2,7,9));
    // history_mother || history_father etc..
    while ($frow = sqlFetchArray($layout)) {
        $value  = '';
        $value2  = '';
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

 
    $list = sqlStatement("SELECT * FROM list_options 
    WHERE list_id= ?  ORDER BY seq", array('familyhistory'));
    while ($frow = sqlFetchArray($list)) {

        $inarray[]=$frow['option_id'];
    }

  
    foreach($inarray as $key =>$value){

      
        if (strlen($value2)) {
            $value2 .= '|';
        }
        $value2 .=$value.":". $_POST["note_$value"];
       
    }


    $newdata['history_notes']=$value2;
    
    if(isset($_POST['pid'] )){
        $newdata['pid']=$_POST['pid'];
    }
    
    updateHistoryData($pid, $newdata);
 
    header('Location: health_history.php'); 
  



?>