<?php

require_once("../globals.php");



require_once("$srcdir/pid.inc");
require_once("$srcdir/patient.inc");

    $p_id = $_POST["pid"];
    $ii_no=  $_POST["ii_no"];
    $claim_no = $_POST["claim_no"];
    $address =$_POST["address"];
    $city = $_POST["city"];
    $state = $_POST["state"];
    $zip = $_POST["zip"];
    $country = $_POST["country"];    
    $phone = $_POST["phone"];
    $fax = $_POST["fax"];
    $emailid = $_POST["emailid"];
    $taxid = $_POST["taxid"];
    $policy_no = $_POST["policy_no"];    
    $exp_date = $_POST["exp_date"];

   
 


     $query = ("replace into insurance_data set
        
        ii_number='" . add_escape_custom($ii_no) . "',
        pid='" . add_escape_custom($p_id) . "',
        claim_no='" . add_escape_custom($claim_no) . "',
        subscriber_street='" . add_escape_custom($address) . "',
        subscriber_city='" . add_escape_custom($city) . "',
        subscriber_state='" . add_escape_custom($state) . "',
        subscriber_country='" . add_escape_custom($country) . "',
        subscriber_zip='" . add_escape_custom($zip) . "',
        subscriber_phone='" . add_escape_custom($phone) . "',
        fax='" . add_escape_custom($fax) . "',
        email='" . add_escape_custom($emailid) . "',
        tax_id='" . add_escape_custom($taxid) . "',
        policy_number='" . add_escape_custom($policy_no) . "',
        date='" . add_escape_custom($exp_date) . "'
        ");

        

    $id = sqlInsert($query);


   
      foreach ($_POST["adjuster_fname"] as $key => $value) {

        // print_r($_POST["adjuster_fname"]);
        
        $adjuster_fname = $_POST["adjuster_fname"][$key];
        $adjuster_lname = $_POST["adjuster_lname"][$key];
    

            $query1 = ("replace into adjuster_info set          
            insurance_data_id='" . add_escape_custom($id) . "',
            fname='" . add_escape_custom($adjuster_fname) . "',
            lname='" . add_escape_custom($adjuster_lname) . "'
            ");

          sqlInsert($query1);

      }
   






?>