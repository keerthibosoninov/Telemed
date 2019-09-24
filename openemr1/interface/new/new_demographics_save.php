<?php

require_once("../globals.php");



require_once("$srcdir/pid.inc");
require_once("$srcdir/patient.inc");

 

if (isset($_POST['fname'])) {

  $result = sqlQuery("select max(pid)+1 as pid from patient_data"); 
  $newpid = 1;
  if ($result['pid'] > 1) 
  {
    $newpid = $result['pid'];
  }
  setpid($newpid);
  if ($pid == null) 
  {
    $pid = 0;
  }


   

    $fname=  $_POST["fname"];
    $mname=  $_POST["mname"];
    $lname=  $_POST["lname"];

    $gender = $_POST["gender"];
    $address =$_POST["address"];
    $dob =$_POST["dob"];
    $type =$_POST["type"];
    $ss_no =$_POST["ss_no"];
    $phone =$_POST["phone"];
    $emailid =$_POST["emailid"];
    $employ_status =$_POST["employ_status"];
    $job =$_POST["job"];
    $dept =$_POST["dept"];
    $date_hire =$_POST["date_hire"];
    $send_link =$_POST["send_link"];
    $send_link_id =$_POST["send_link_id"];        

    $city = $_POST["city"];
    $state = $_POST["state"];
    $zip = $_POST["zip"];
    $country = $_POST["country"];   

   
 


     $query = ("replace into patient_data set
        
     fname='" . add_escape_custom($fname) . "',        
     mname='" . add_escape_custom($mname) . "',
     lname='" . add_escape_custom($lname) . "',
     sex='" . add_escape_custom($gender) . "',        
     street='" . add_escape_custom($address) . "',
     DOB='" . add_escape_custom($dob) . "',
     type='" . add_escape_custom($type) . "',        
     ss='" . add_escape_custom($ss_no) . "',
     phone_contact='" . add_escape_custom($phone) . "',
     email='" . add_escape_custom($emailid) . "',        
     employment_status='" . add_escape_custom($employ_status) . "',
     occupation='" . add_escape_custom($job) . "',
     department='" . add_escape_custom($dept) . "',
     date_of_hire='" . add_escape_custom($date_hire) . "',        
     send_link='" . add_escape_custom($send_link) . "',
     send_link_id='" . add_escape_custom($send_link_id) . "',
        city='" . add_escape_custom($city) . "',
        state='" . add_escape_custom($state) . "',
        county='" . add_escape_custom($country) . "',
        zip='" . add_escape_custom($zip) . "',
        pid='" . add_escape_custom($pid) . "',
        date=NOW()");        

    sqlInsert($query);
    // echo $pid;
    $_SESSION["patient_id"]=$pid;


  }

  if (isset($_POST['auth_name'])) {
          
      $auth_name=  $_POST["auth_name"];
      $relation=  $_POST["relation"];
      $contact_no=  $_POST["contact_no"];  
      $roi = $_POST["roi"];
      $pid=$_SESSION["pid"];
              
       $query1 = ("replace into patient_authorization set          
       author_name='" . add_escape_custom($auth_name) . "',        
       relation='" . add_escape_custom($relation) . "',
       contact_no='" . add_escape_custom($contact_no) . "',
       roi='" . add_escape_custom($roi) . "',           
       pid='" . add_escape_custom($pid) . "'
        ");        
  
      sqlInsert($query1);
      echo $auth_name;
      // echo $pid;
          
    }

    // function get_authorizationDetails($pid)
    // {
    //       $lres=sqlStatement("Select * from patient_authorization where deleted=0 and pid=$pid order by id");
    //       $lrow = sqlFetchArray($lres);
         
    //           // $data['authors']=$lrow;
    //           return $lrow;
        
          
    // }


?>