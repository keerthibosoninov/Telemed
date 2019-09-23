<?php

require_once("../globals.php");



require_once("$srcdir/pid.inc");
require_once("$srcdir/patient.inc");

 

if ($_POST['injury_create']) {

   

    $body=  $_POST["emp_notes"];
    $pid = $_POST["pid"];
    $title =$_POST["emp_notes"];
    $injury_date = $_POST["emp_injury_date"];
    $injury_time = $_POST["emp_injury_time"];
    $description = $_POST["emp_description"];
    $emp_address = $_POST["emp_address"];
    $emp_city = $_POST["emp_city"];
    $emp_state = $_POST["emp_state"];
    $emp_zip = $_POST["emp_zip"];
    $emp_country = $_POST["emp_country"];
 


     $query = ("replace into pnotes set
        date=NOW(),
        activity='1',
        body='" . add_escape_custom($body) . "',
        pid='" . add_escape_custom($pid) . "',
        title='" . add_escape_custom($title) . "',
        injury_date='" . add_escape_custom($injury_date) . "',
        injury_time='" . add_escape_custom($injury_time) . "',
        description='" . add_escape_custom($description) . "',
        address='" . add_escape_custom($emp_address) . "',
        city='" . add_escape_custom($emp_city) . "',
        state='" . add_escape_custom($emp_state) . "',
        zip='" . add_escape_custom($emp_zip) . "',
        country='" . add_escape_custom($emp_country) . "',
        update_date=NOW()");

        

    $id = sqlInsert($query);

  if($_POST["emp_body_part"]){
   
      foreach ($_POST["emp_body_part"] as $key => $value) {

        $emp_body_part=$_POST['emp_body_part'][$key];
        $emp_injury_cause=$_POST['emp_injury_cause'][$key];
        $emp_injury_nature=$_POST['emp_injury_nature'][$key];

          $query1 = ("replace into employee_injury_details set
          
          pnote_id='" . add_escape_custom($id) . "',
          body_part='" . add_escape_custom($emp_body_part) . "',
          injury_cause='" . add_escape_custom($emp_injury_cause) . "',
          injury_nature='" . add_escape_custom($emp_injury_nature) . "'
          ");

          sqlInsert($query1);

      }
    }
    // }

  }
// 



function get_injuryDetails($pid){

    $lres=sqlStatement("Select * from pnotes where deleted=0 and pid=$pid order by id desc limit 1");
    $lrow = sqlFetchArray($lres);



    $pnoteid=$lrow['id'];

    $data['basic']=$lrow;

    $parts=sqlStatement("Select * from employee_injury_details where pnote_id=$pnoteid and  isdeleted=0");
    while($row=sqlFetchArray($parts)){

      $data['injury']=$row;


    }

  return $data;


}

if ($_POST['location'] && $_POST['location']=='loc') {

    $pid=$_POST['pid'];
    
    $query = "SELECT postal_code,city,state,country,street from employer_data where pid= ? "
          ;
        $sqlBindArray = array();
        array_push($sqlBindArray, $pid);
        $res = sqlStatement($query, $sqlBindArray);
        $result_data = array();
        $emp_data = sqlFetchArray($res);

     echo   json_encode($emp_data);

    
      
}



// conset

if ($_POST['conset_insert'] && $_POST['conset_insert']=='1') {

  
  $pid = $_POST["pid"];
  $hipaa_voice =$_POST["hipaa_voice"];
  $hipaa_mail = $_POST["hipaa_mail"];
  $hipaa_allowsms = $_POST["hipaa_allowsms"];
  $hipaa_allowemail = $_POST["hipaa_allowemail"];
  $email_verified = $_POST["email_verified"];
  


   sqlStatement("update patient_data  set
     
      hipaa_voice='" . add_escape_custom($hipaa_voice) . "',
      hipaa_mail='" . add_escape_custom($hipaa_mail) . "',
      hipaa_allowsms='" . add_escape_custom($hipaa_allowsms) . "',
      hipaa_allowemail='" . add_escape_custom($hipaa_allowemail) . "',
      email_verified='" . add_escape_custom($email_verified) . "'
      where id=$pid
      ");
 
}

function getPatientData_conset($pid){

  $conset=sqlStatement("Select hipaa_voice,hipaa_mail,hipaa_allowsms,hipaa_allowemail,email_verified from patient_data where id=$pid");
   $row=sqlFetchArray($conset);

  return $row;

}







?>