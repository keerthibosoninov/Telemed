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



if ($_POST['treatment']) {

    $pid = $_POST["pid"];
    $total_orders=$_POST["total_orders"];
    $total_visits=$_POST["total_visits"];
    $order_type=$_POST["order_type"];


    $query = ("replace into patient_treatment set
      
        total_order='" . add_escape_custom($total_orders) . "',
        total_visit='" . add_escape_custom($total_visits) . "',
        order_type='" . add_escape_custom($order_type) . "',
        created_date=NOW(),
        isactive='1',
        isdeleted='0',
        pid='" . add_escape_custom($pid) . "'
      ");

        

  echo  $treatmentid = sqlInsert($query);

  // hospitalization details

    if($_POST["admit_date"]){
  
        foreach ($_POST["admit_date"] as $key => $value) {

          $admit_date=$_POST['admit_date'][$key];
          $discharge_date=$_POST['discharge_date'][$key];
          $location=$_POST['location'][$key];

            $query1 = ("replace into tm_hospitalization_details set
            
            treatment_id='" . add_escape_custom($treatmentid) . "',
            admit_date='" . add_escape_custom($admit_date) . "',
            discharge_date='" . add_escape_custom($discharge_date) . "',
            location='" . add_escape_custom($location) . "',
            isactive=1,
            isdeleted=0
            ");

            sqlInsert($query1);

        }
    }

    // imaging details
    if($_POST["imaging_type"]){
  
      foreach ($_POST["imaging_type"] as $key => $value) {

        $imaging_type=$_POST['imaging_type'][$key];
        $imaging_date=$_POST['imaging_date'][$key];
        $imaging_findings=$_POST['imaging_findings'][$key];

          $query1 = ("replace into tm_imaging_details set
          
          treatment_id='" . add_escape_custom($treatmentid) . "',
          imaging_type='" . add_escape_custom($imaging_type) . "',
          imaging_date='" . add_escape_custom($imaging_date) . "',
          imaging_findings='" . add_escape_custom($imaging_findings) . "',
          isactive=1,
          isdeleted=0
          ");

          sqlInsert($query1);

      }
    }


    //Ancillary Services
    if($_POST["ancillary_type"]){
    
      foreach ($_POST["ancillary_type"] as $key => $value) {

        $ancillary_type=$_POST['ancillary_type'][$key];
        $ancillary_date=$_POST['ancillary_date'][$key];
        $ancillary_status=$_POST['ancillary_status'][$key];
        $ancillary_findings=$_POST['ancillary_findings'][$key];

          $query1 = ("replace into tm_ancillary_details set
          
          treatment_id='" . add_escape_custom($treatmentid) . "',
          ancillary_type='" . add_escape_custom($ancillary_type) . "',
          ancillary_date='" . add_escape_custom($ancillary_date) . "',
          ancillary_status='" . add_escape_custom($ancillary_status) . "',
          ancillary_findings='" . add_escape_custom($ancillary_findings) . "',
          isactive=1,
          isdeleted=0
          ");

          sqlInsert($query1);

      }
    }

    
    //Specialist Referral
    if($_POST["referral_type"]){
    
      foreach ($_POST["referral_type"] as $key => $value) {

        $referral_type=$_POST['referral_type'][$key];
        $referral_date=$_POST['referral_date'][$key];
        $referral_findings=$_POST['referral_findings'][$key];

          $query1 = ("replace into tm_referral_details set
          
          treatment_id='" . add_escape_custom($treatmentid) . "',
          referral_type='" . add_escape_custom($referral_type) . "',
          referral_date='" . add_escape_custom($referral_date) . "',
          referral_findings='" . add_escape_custom($referral_findings) . "',
          isactive=1,
          isdeleted=0
          ");

          sqlInsert($query1);

      }
    }
   

}







?>