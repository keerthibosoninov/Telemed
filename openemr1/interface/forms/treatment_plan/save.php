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

  //  addForm($encounter, "Treatment Plan", $treatmentid, "treatment_plan", $pid, $userauthorized);
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
