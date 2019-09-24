<?php

require_once("../globals.php");



require_once("$srcdir/pid.inc");
require_once("$srcdir/patient.inc");

 

// if ($_POST['injury_create']) {

   

    $cname=  $_POST["cname"];
    $emp_id = $_POST["emp_id"];
    $address =$_POST["address"];
    $city = $_POST["city"];
    $emp_state = $_POST["emp_state"];
    $zip = $_POST["zip"];
    $county = $_POST["county"];
    $p_id = $_POST["pid"];

   
 


     $query = ("replace into employer_data set
        
        company_name='" . add_escape_custom($cname) . "',
        pid='" . add_escape_custom($p_id) . "',
        employer_id='" . add_escape_custom($emp_id) . "',
        street='" . add_escape_custom($address) . "',
        city='" . add_escape_custom($city) . "',
        state='" . add_escape_custom($emp_state) . "',
        country='" . add_escape_custom($county) . "',
        zip='" . add_escape_custom($zip) . "'
        ");

        

    $id = sqlInsert($query);


   
      foreach ($_POST["super"] as $key => $value) {

        // print_r($_POST["super"]);
        
        $super = $_POST["super"][$key];
        $s_name = $_POST["s_name"][$key];
        $s_emailid = $_POST["s_emailid"][$key];       
        $manager = $_POST["manager"][$key];
        $m_name = $_POST["m_name"][$key];
        $m_phone = $_POST["m_phone"][$key];

            $query1 = ("replace into employer_authorization set
          
           employer_data_id='" . add_escape_custom($id) . "',
          supervisor='" . add_escape_custom($super) . "',
          supervisor_name='" . add_escape_custom($s_name) . "',
          supervisor_email='" . add_escape_custom($s_emailid) . "',
          manager='" . add_escape_custom($manager) . "',
          manager_name='" . add_escape_custom($m_name) . "',
          manager_phone='" . add_escape_custom($m_phone) . "'
          ");

          sqlInsert($query1);

      }
    // }

  // }
// 



// function get_injuryDetails($pid){

//   $lres=sqlStatement("Select * from pnotes where deleted=0 and pid=$pid order by id desc limit 1");
//   $lrow = sqlFetchArray($lres);



//   $pnoteid=$lrow['id'];

//   $data['basic']=$lrow;

//   $parts=sqlStatement("Select * from employee_injury_details where pnote_id=$pnoteid and  isdeleted=0");
//   while($row=sqlFetchArray($parts)){

//   $data['injury']=$row;


//   }

//   return $data;


// }





?>