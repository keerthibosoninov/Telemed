<?php
/**
 * treatment plan form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Naina Mohamed <naina@capminds.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012-2013 Naina Mohamed <naina@capminds.com> CapMinds Technologies
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// km commented the code of checking encounter
// if (!$encounter) { // comes from globals.php
//     die(xlt("Internal error: we do not seem to be in an encounter!"));
// }

// $id = 0 + (isset($_GET['id']) ? $_GET['id'] : '');

// $sets = "pid = ?,
//   groupname = ?,
//   user = ?,
//   authorized = ?,
//   activity = 1,
//   date = NOW(),
//   provider = ?,
//   client_name = ?,
//   client_number = ?,
//   admit_date = ?,
//   presenting_issues = ?,
//   patient_history = ?,
//   medications = ?,
//   anyother_relevant_information = ?,
//   diagnosis = ?,
//   treatment_received = ?,
//   recommendation_for_follow_up = ?,
//   total_order = ?,
//   order_type= ?,
//   total_visit =?

//   ";


$sets = "pid = ?,
  total_order = ?,
  order_type= ?,
  total_visit =?

  ";

// if (empty($id)) {
    $treatmentid = sqlInsert(
        "INSERT INTO form_treatment_plan SET $sets",
        [

            $_POST["pid"],
            // $_SESSION["authProvider"],
            // $_SESSION["authUser"],
            // $userauthorized,
            // $_POST["provider"],
            // $_POST["client_name"],
            // $_POST["client_number"],
            // $_POST["admit_date"],
            // $_POST["presenting_issues"],
            // $_POST["patient_history"],
            // $_POST["medications"],
            // $_POST["anyother_relevant_information"],
            // $_POST["diagnosis"],
            // $_POST["treatment_received"],
            // $_POST["total_order"],
            // $_POST["recommendation_for_follow_up"],
            $_POST["total_order"],
            $_POST["order_type"],
            $_POST["total_visit"]

           
            
        ]
    );


    // admit-details
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

   addForm($encounter, "Treatment Plan", $treatmentid, "treatment_plan", $pid, $userauthorized);
// } else {
    // sqlStatement(
    //     "UPDATE form_treatment_plan SET $sets WHERE id = ?",
    //     [
    //         $_SESSION["pid"],
    //         $_SESSION["authProvider"],
    //         $_SESSION["authUser"],
    //         $userauthorized,
    //         $_POST["provider"],
    //         $_POST["client_name"],
    //         $_POST["client_number"],
    //         $_POST["admit_date"],
    //         $_POST["presenting_issues"],
    //         $_POST["patient_history"],
    //         $_POST["medications"],
    //         $_POST["anyother_relevant_information"],
    //         $_POST["diagnosis"],
    //         $_POST["treatment_received"],
    //         $_POST["recommendation_for_follow_up"],
    //         $id
    //     ]
    // );
// }

// $_SESSION["encounter"] = $encounter;
// formHeader("Redirecting....");
// formJump();
// formFooter();

// $url=$GLOBALS['webroot']."/"
// header("Location: http://www.redirect.to.url.com/");

?>
