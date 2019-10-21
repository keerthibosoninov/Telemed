<?php
/**
 * history_save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("history.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}


//  km updated

$pid=$_POST['pid'];
// Check authorization.
if (acl_check('patients', 'med')) {
    $tmp = getPatientData($pid, "squad");
    if ($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) {
        die(xlt("Not authorized for this squad."));
    }
}

if (!acl_check('patients', 'med', '', array('write','addonly'))) {
    die(xlt("Not authorized"));
}

foreach ($_POST as $key => $val) {
    if ($val == "YYYY-MM-DD") {
        $_POST[$key] = "";
    }
}



// Update history_data:
//
$newdata = array();
$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'HIS' AND uor > 0 AND field_id != '' " .
  "ORDER BY group_id, seq");
while ($frow = sqlFetchArray($fres)) {
    $field_id  = $frow['field_id'];
  //get value only if field exist in $_POST (prevent deleting of field with disabled attribute)
    if (isset($_POST["form_$field_id"])) {
        $newdata[$field_id] = get_layout_form_value($frow);
    }
}


// custom km for risk factors
if(isset($_POST['risk_coc_other'] )){
    $newdata['risk_coc_other']=$_POST['risk_coc_other'];
}
if(isset($_POST['risk_oth_other'] )){
    $newdata['risk_oth_other']=$_POST['risk_oth_other'];
}

// for lifestyle
if(isset($_POST['form_pid'] )){
    $newdata['pid']=$_POST['form_pid'];
}

// print_r($newdata);

$s= updateHistoryData($pid, $newdata);
// print_r($s);

// exit;

header('Location: ../../main/health_history.php'); 

// include_once("history.php");
