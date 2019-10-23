<?php
/**
 * The address book entry editor.
 * Available from Administration->Addr Book in the concurrent layout.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    tony@mi-squared.com
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2010, 2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// newcode pa
// Collect user id if editing entry
$userid = $_GET['id'];

// Collect type if creating a new entry
$type = $_REQUEST['type'];

$info_msg = "";

function invalue($name)
{
    if (!$_POST[$name]) {
        return "''";
    }

    $fld = add_escape_custom(trim($_POST[$name]));
    return "'$fld'";
}

// new code pa


$popup = empty($_GET['popup']) ? 0 : 1;
$rtn_selection = 0;
if ($popup) {
    $rtn_selection = $_GET['popup'] == 2 ? 1 : 0;
}

$form_fname = trim($_POST['form_fname']);
$form_lname = trim($_POST['form_lname']);
$form_specialty = trim($_POST['form_specialty']);
$form_organization = trim($_POST['form_organization']);
$form_abook_type = trim($_REQUEST['form_abook_type']);
$form_external = $_POST['form_external'] ? 1 : 0;

$sqlBindArray = array();
$query = "SELECT u.*, lo.option_id AS ab_name, lo.option_value as ab_option FROM users AS u " .
  "LEFT JOIN list_options AS lo ON " .
  "list_id = 'abook_type' AND option_id = u.abook_type AND activity = 1 " .
  "WHERE u.active = 1 AND ( u.authorized = 1 OR u.username = '' ) ";
if ($form_organization) {
    $query .= "AND u.organization LIKE ? ";
    array_push($sqlBindArray, $form_organization."%");
}

if ($form_lname) {
    $query .= "AND u.lname LIKE ? ";
    array_push($sqlBindArray, $form_lname."%");
}

if ($form_fname) {
    $query .= "AND u.fname LIKE ? ";
    array_push($sqlBindArray, $form_fname."%");
}

if ($form_specialty) {
    $query .= "AND u.specialty LIKE ? ";
    array_push($sqlBindArray, "%".$form_specialty."%");
}

if ($form_abook_type) {
    $query .= "AND u.abook_type LIKE ? ";
    array_push($sqlBindArray, $form_abook_type);
}

if ($form_external) {
    $query .= "AND u.username = '' ";
}

if ($form_lname) {
    $query .= "ORDER BY u.lname, u.fname, u.mname";
} else if ($form_organization) {
    $query .= "ORDER BY u.organization";
} else {
    $query .= "ORDER BY u.organization, u.lname, u.fname";
}

$query .= " LIMIT 500";
$res = sqlStatement($query, $sqlBindArray);
?>

<!DOCTYPE html>
<html>

<head>



<title><?php echo xlt('Address Book'); ?></title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/style.css">
    <!-- <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/employee_dashboard_style.css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/emp_info_css.css"> -->
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/vue.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/main.js"></script>
    <?php //Header::setupHeader(['common']); ?>
<style>
/* .form-save1 {
  
    background: #3C9DC5;
    color: #fff!important;
    display: block;
    float: left;
    font-weight: 400;
    margin-right: 3px;
    text-decoration: none;
    padding: 9px 12px;
    border: 0;
    width: 96%;
    margin-left: 19px;
} */
.css_button:hover, button:hover, input[type=button]:hover, input[type=submit]:hover {
        background: #3C9DC5;
        text-decoration: none;
    }
</style>
<!-- newcode PA -->
 <script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
<!--<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-1-9-1/jquery.min.js"></script>

<style>
td { font-size:10pt; }

.inputtext {
 padding-left:2px;
 padding-right:2px;
}

.button {
 font-family:sans-serif;
 font-size:9pt;
 font-weight:bold;
}
</style> -->

<script language="JavaScript">

 var type_options_js = Array();
    <?php
    // Collect the type options. Possible values are:
    // 1 = Unassigned (default to person centric)
    // 2 = Person Centric
    // 3 = Company Centric
    $sql = sqlStatement("SELECT option_id, option_value FROM list_options WHERE " .
    "list_id = 'abook_type' AND activity = 1");
    while ($row_query = sqlFetchArray($sql)) {
        echo "type_options_js[" . js_escape($row_query['option_id']) . "]=" . js_escape($row_query['option_value']) . ";\n";
    }
    ?>

 // Process to customize the form by type
 function typeSelect(a) {
   if(a=='ord_lab'){
      $('#cpoe_span').css('display','inline');
  } else {
       $('#cpoe_span').css('display','none');
       $('#form_cpoe').removeAttr('checked');
  }
  if (type_options_js[a] == 3) {
   // Company centric:
   //   1) Hide the person Name entries
   //   2) Hide the Specialty entry
   //   3) Show the director Name entries
   document.getElementById("nameRow").style.display = "none";
   document.getElementById("specialtyRow").style.display = "none";
   document.getElementById("nameDirectorRow").style.display = "";
  }
  else {
   // Person centric:
   //   1) Hide the director Name entries
   //   2) Show the person Name entries
   //   3) Show the Specialty entry
   document.getElementById("nameDirectorRow").style.display = "none";
   document.getElementById("nameRow").style.display = "";
   document.getElementById("specialtyRow").style.display = "";
  }
 }
 

</script>
<!-- //newcode PA -->
<!-- style tag moved into proper CSS file -->

</head>

<body id="emp_form" class="body_top" style="font-family: 'Open Sans', sans-serif">
<section>
            <div class="body-content body-content2">
                <div class="container-fluid pb-4 pt-4">
                    <window-dashboard title="" class="icon-hide">
                    <div class="head-component">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="compo-head">
                                               
                                                <span>
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/min.svg"
                                                        alt="">
                                                </span>
                                                
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-white head-p">Employee</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="body-compo" style="height:auto;">
                            <div class="container-fluid">
                                
                            <form class='navbar-form' id="table_frm" method='post' action='addrbook_list.php'
              onsubmit='return top.restoreSession()'>
              <input type="hidden" name="form_del" id="form_del" value="form_delt">
<div class="pt-4 pb-4">
<div class="col-md-12">
<table class="table table-form">

 <tbody id="TextBoxContainer11" class="repeat-row ">
     <tr>
  <th title='<?php echo xla('Click to view or edit'); ?>'><?php echo xlt('Organization'); ?></th>
  <th><?php echo xlt('Name'); ?></th>
  <th><?php echo xlt('Local'); ?></th>
  <th><?php echo xlt('Type'); ?></th>
  <th><?php echo xlt('Specialty'); ?></th>
  <th><?php echo xlt('Phone'); ?></th>
  <!-- <th><?php echo xlt('Mobile'); ?></th>
  <th><?php echo xlt('Fax'); ?></th> -->
  <th><?php echo xlt('Email'); ?></th>
  <th><?php echo xlt('Address'); ?></th>
  <th></th>
  <!-- <th><?php echo xlt('City'); ?></th>
  <th><?php echo xlt('State'); ?></th>
  <th><?php echo xlt('Postal'); ?></th> -->
 <!-- </thead> -->
                    </tr>
<?php
 $encount = 0;
while ($row = sqlFetchArray($res)) {
    ++$encount;
    $username = $row['username'];
    if (! $row['active']) {
        $username = '--';
    }

    $displayName = $row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']; // Person Name
    if ($row['suffix'] >'') {
        $displayName .=", ".$row['suffix'];
    }

   

    echo "  <td>" . text($row['organization']) . "</td>\n";
    echo "  <td>" . text($displayName) . "</td>\n";
    echo "  <td>" . ($username ? '*' : '') . "</td>\n";
    echo "  <td>" . generate_display_field(array('data_type'=>'1','list_id'=>'abook_type'), $row['ab_name']) . "</td>\n";
    echo "  <td>" . text($row['specialty']) . "</td>\n";
    echo "  <td>" . text($row['phonew1'])   . "</td>\n";
    
    echo "  <td>" . text($row['email'])     . "</td>\n";
    echo "  <td>" . text($row['street'])    . "</td>\n";
   
    // echo " <td><img style='cursor:pointer' onclick='doedclick_edit(" . attr_js($row['id']) . ")' src='". $GLOBALS['assets_static_relative'] ."/img/edit-text.svg' class='link'><br><a class='link' href='javascript:submitDelete(" . attr_js($row['id']) . ")'><img src='". $GLOBALS['assets_static_relative'] ."/img/delete.svg'  class='xxx pr-2'></a></td>";
        echo " <td><img style='cursor:pointer' idss=".$row['id']." src='". $GLOBALS['assets_static_relative'] ."/img/edit-text.svg' class='link edt'>
        <img style='cursor:pointer' idss=".$row['id']." src='". $GLOBALS['assets_static_relative'] ."/img/delete.svg' class='link dlt'>
        </td>";
        // <a class='link' href='javascript:submitDelete(" . attr_js($row['id']) . ")'><img src='". $GLOBALS['assets_static_relative'] ."/img/delete.svg'  class='xxx pr-2'></a>


    echo " </tr>\n";
}
?>

</tbody>
</table>
</div>
</div>
</form>

<?php if ($popup) { ?>
<script type="text/javascript" src="../../library/topdialog.js"></script>
<?php } ?>
<script type="text/javascript" src="../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>

<script language="JavaScript">

<?php if ($popup) {
    require($GLOBALS['srcdir'] . "/restoreSession.php");
} ?>

// Callback from popups to refresh this display.
function refreshme() {
 // location.reload();
 document.forms[0].submit();
}

// Process click to pop up the add window.
function doedclick_add(type) {
 top.restoreSession();
 dlgopen('addrbook_edit.php?type=' + encodeURIComponent(type), '_blank', 650, (screen.availHeight * 75/100));
}

// Process click to pop up the edit window.
function doedclick_edit(userid) {
 let rtn_selection = <?php echo js_escape($rtn_selection); ?>;
 if(rtn_selection) {
    dlgclose('contactCallBack', userid);
 }
 top.restoreSession();
 dlgopen('addrbook_edit.php?userid=' + encodeURIComponent(userid), '_blank', 650, (screen.availHeight * 75/100));

}

</script>
<!-- </div> -->



<!-- newcode PA -->
<?php
 // If we are saving, then save and close the window.
 //
if ($_POST['form_save']) {
 // Collect the form_abook_type option value
 //  (ie. patient vs company centric)
    $type_sql_row = sqlQuery("SELECT `option_value` FROM `list_options` WHERE `list_id` = 'abook_type' AND `option_id` = ? AND activity = 1", array(trim($_POST['form_abook_type'])));
    $option_abook_type = $type_sql_row['option_value'];
 // Set up any abook_type specific settings
    if ($option_abook_type == 3) {
        // Company centric
        $form_title = invalue('form_director_title');
        $form_fname = invalue('form_director_fname');
        $form_lname = invalue('form_director_lname');
        $form_mname = invalue('form_director_mname');
        $form_suffix = invalue('form_director_suffix');
    } else {
        // Person centric
        $form_title = invalue('form_title');
        $form_fname = invalue('form_fname');
        $form_lname = invalue('form_lname');
        $form_mname = invalue('form_mname');
        $form_suffix = invalue('form_suffix');
    }

    if ($userid) {
        $query = "UPDATE users SET " .
        "abook_type = "   . invalue('form_abook_type')   . ", " .
        "title = "        . $form_title                  . ", " .
        "fname = "        . invalue('form_fname')                  . ", " .
        "lname = "        . invalue('form_lname')                  . ", " .
        "mname = "        . invalue('form_mname')                  . ", " .
        "suffix = "       . $form_suffix                 . ", " .
        "specialty = "    . invalue('form_specialty')    . ", " .
        "organization = " . invalue('form_organization') . ", " .
        "valedictory = "  . invalue('form_valedictory')  . ", " .
        "assistant = "    . invalue('form_assistant')    . ", " .
        "federaltaxid = " . invalue('form_federaltaxid') . ", " .
        "upin = "         . invalue('form_upin')         . ", " .
        "npi = "          . invalue('form_npi')          . ", " .
        "taxonomy = "     . invalue('form_taxonomy')     . ", " .
        "cpoe = "         . invalue('form_cpoe')         . ", " .
        "email = "        . invalue('form_email')        . ", " .
        "email_direct = " . invalue('form_email_direct') . ", " .
        "url = "          . invalue('form_url')          . ", " .
        "street = "       . invalue('form_street')       . ", " .
        "streetb = "      . invalue('form_streetb')      . ", " .
        "city = "         . invalue('form_city')         . ", " .
        "state = "        . invalue('form_state')        . ", " .
        "zip = "          . invalue('form_zip')          . ", " .
        "street2 = "      . invalue('form_street2')      . ", " .
        "streetb2 = "     . invalue('form_streetb2')     . ", " .
        "city2 = "        . invalue('form_city2')        . ", " .
        "state2 = "       . invalue('form_state2')       . ", " .
        "zip2 = "         . invalue('form_zip2')         . ", " .
        "phone = "        . invalue('form_phone')        . ", " .
        "phonew1 = "      . invalue('form_phonew1')      . ", " .
        "phonew2 = "      . invalue('form_phonew2')      . ", " .
        "phonecell = "    . invalue('form_phonecell')    . ", " .
        "fax = "          . invalue('form_fax')          . ", " .
        "notes = "        . invalue('form_notes')        . ", "  .
        "country = "      . invalue('form_country')      . ", " .
        "ssn = "          . invalue('form_ssn')          . " " .
        "WHERE id = '" . add_escape_custom($userid) . "'";
        sqlStatement($query);
    } else {
        $userid = sqlInsert("INSERT INTO users ( " .
        "username, password, authorized, info, source, " .
        "title, fname, lname, mname, suffix, " .
        "federaltaxid, federaldrugid, upin, facility, see_auth, active, npi, taxonomy, cpoe, " .
        "specialty, organization, valedictory, assistant, billname, email, email_direct, url, " .
        "street, streetb, city, state, zip, " .
        "street2, streetb2, city2, state2, zip2, " .
        "phone, phonew1, phonew2, phonecell, fax, notes,country,ssn,abook_type "            .
        ") VALUES ( "                        .
        "'', "                               . // username
        "'', "                               . // password
        "0, "                                . // authorized
        "'', "                               . // info
        "NULL, "                             . // source
        $form_title                           . ", " .
        invalue('form_fname')                 . ", " .
        invalue('form_lname')                 . ", " .
        invalue('form_mname')                 . ", " .
        $form_suffix                  . ", " .
        invalue('form_federaltaxid')  . ", " .
        "'', "                               . // federaldrugid
        invalue('form_upin')          . ", " .
        "'', "                               . // facility
        "0, "                                . // see_auth
        "1, "                                . // active
        invalue('form_npi')           . ", " .
        invalue('form_taxonomy')      . ", " .
        invalue('form_cpoe')          . ", " .
        invalue('form_specialty')     . ", " .
        invalue('form_organization')  . ", " .
        invalue('form_valedictory')   . ", " .
        invalue('form_assistant')     . ", " .
        "'', "                               . // billname
        invalue('form_email')         . ", " .
        invalue('form_email_direct')  . ", " .
        invalue('form_url')           . ", " .
        invalue('form_street')        . ", " .
        invalue('form_streetb')       . ", " .
        invalue('form_city')          . ", " .
        invalue('form_state')         . ", " .
        invalue('form_zip')           . ", " .
        invalue('form_street2')       . ", " .
        invalue('form_streetb2')      . ", " .
        invalue('form_city2')         . ", " .
        invalue('form_state2')        . ", " .
        invalue('form_zip2')          . ", " .
        invalue('form_phone')         . ", " .
        invalue('form_phonew1')       . ", " .
        invalue('form_phonew2')       . ", " .
        invalue('form_phonecell')     . ", " .
        invalue('form_fax')           . ", " .
        invalue('form_notes')         . ", " .
        invalue('form_country')       . ", " .
        invalue('form_ssn')           . ", " .
        invalue('form_abook_type')    . " "  .
        ")");
    }
} 
else if ($_POST['form_delete'] && $id) {
    // if ($userid) {
       // Be careful not to delete internal users.
    //    echo "delete";
        // sqlStatement("DELETE FROM users WHERE id = ? AND username = ''", array($id));
    // }
}
// if ($_POST['form_del'] && $_POST['id']) {
//     // if ($userid) {
//        // Be careful not to delete internal users.
//     //    echo "delete";
//     $id=$_POST['id'];
//         sqlStatement("DELETE FROM users WHERE id = ? AND username = ''", array($id));
//     // }
// }


if ($_POST['form_save']) {
  // Close this window and redisplay the updated list.
    echo "<script language='JavaScript'>\n";
    // if ($info_msg) {
    //     echo " alert(".js_escape($info_msg).");\n";
    // }

    echo "window.location.href='addrbook_list.php'";
    
    // echo " if (opener.refreshme) opener.refreshme();\n";
    echo "</script>\n";
    // exit();
}

if ($userid) {
    $row = sqlQuery("SELECT * FROM users WHERE id = ?", array($userid));
}

if ($type) { // note this only happens when its new
  // Set up type
    $row['abook_type'] = $type;
}

?>

<script language="JavaScript">
//    function submitDelete($id) {
//         sqlStatement("DELETE FROM users WHERE id = ? AND username = ''", array($id));

//         }
 $(function() {
  // customize the form via the type options
  typeSelect(<?php echo js_escape($row['abook_type']); ?>);
  if(typeof abook_type != 'undefined' && abook_type == 'ord_lab') {
    $('#cpoe_span').css('display','inline');
   }
 });
</script>

                        <form method='post' name='theform' id="theform" action='addrbook_list.php?id=<?php echo attr_url($userid) ?>'>
                            <!-- action='0.php' -->
                            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

                            <div class="pt-4 pb-4">
                                    <?php if (acl_check('admin', 'practice')) { // allow choose type option if have admin access ?>

                            <div class="row">
                                
                                <div class="col-md-3">
                                    <p>Type</p>
                                    <?php
                                    echo generate_select_list('form_abook_type', 'abook_type', $row['abook_type'], '', 'Unassigned', '', 'typeSelect(this.value)');
                                        ?>
                                </div>
                                <?php } // end of if has admin access ?>

 
                                <div class="col-md-3">
                                    <p>Name</p>

                                    <input type='text' size='10' name='form_fname' placeholder='First name' class='form-control pr-1 pl-1' maxlength='50' value='<?php echo attr($row['fname']); ?>' />
                                </div>
                                <div class="col-md-3">
                                    <p class="invisible">To</p>
                                    <input type='text' size='4' name='form_mname' placeholder="Middile Name" class="form-control pr-1 pl-1" maxlength='50' value='<?php echo attr($row['mname']); ?>' />

                                </div>
                                <div class="col-md-3">
                                    <p class="invisible">To</p>
                                    <input type='text' size='10' name='form_lname' placeholder="Last Name" class="form-control pr-1 pl-1" maxlength='50' value='<?php echo attr($row['lname']); ?>'/>                                                                                            

                                </div>
                            </div>

                            </div>

                            <div class="row pt-4">
                                <div class="col-md-3">
                                    <p>Speciality</p>
                                    <input type='text' size='40' class="form-control pr-1 pl-1" name='form_specialty' maxlength='250'
                                    value='<?php echo attr($row['specialty']); ?>'
                                    style='width:100%'  />
                                </div>
 
                                <div class="col-md-3">
                                    <p>Organization</p>
                                    <input type='text' size='40' name='form_organization' class="form-control pr-1 pl-1" maxlength='250'
                                    value='<?php echo attr($row['organization']); ?>'
                                    style='width:100%'  />
                                    <span id='cpoe_span' style="display:none;">
                                        <input type='checkbox' title="<?php echo xla('CPOE'); ?>" name='form_cpoe' id='form_cpoe' value='1' <?php echo ($row['cpoe']=='1') ? "CHECKED" : ""; ?>/>
                                        <label for='form_cpoe'><b><?php echo xlt('CPOE'); ?></b></label>
                                    </span>
                                </div>
 
                                <div class="col-md-3">
                                    <p>Valedictory</p>
                                    <input type='text' size='40' name='form_valedictory' class="form-control pr-1 pl-1" maxlength='250'
                                    value='<?php echo attr($row['valedictory']); ?>'
                                    style='width:100%'  />
                                </div>
                            </div>

                            <div class="row pt-4"> 
                                <div class="col-md-3">
                                    <p>Phone</p>
                                    <input type='text' size='11' name='form_phonew1' value='<?php echo attr($row['phone']); ?>'
                                    maxlength='30' class='form-control pr-1 pl-1' />&nbsp;
                                
                                </div>
 
                                <div class="col-md-3">
                                    <p>E-Mail</p>
                                    <input type='text' size='40' name='form_email' maxlength='250'
                                    value='<?php echo attr($row['email']); ?>'
                                    style='width:100%' class='form-control pr-1 pl-1' />
                                </div>
                                <div class="col-md-3">
                                    <p>Fax</p>                                       
                                    <input type='text' size='11' name='form_fax' value='<?php echo attr($row['fax']); ?>' maxlength='30' class='form-control pr-1 pl-1' />
                                </div>

                                <div class="col-md-3">
                                    <p>Website</p>
                                    <input type='text' size='40' name='form_url' maxlength='250'
                                    value='<?php echo attr($row['url']); ?>'
                                    style='width:100%' class='form-control pr-1 pl-1' />
                                </div>
                            </div>


                                <div class="row pt-4">
                                    <div class="col-md-12">
                                        <p>Address</p>
                                        <textarea name='form_street' class="form-control pt-3" rows="3"><?php echo attr($row['street']); ?></textarea>
                                    </div>

                                </div>

                                <div class="row pt-4">
                                    <div class="col-md-3">
                                        <p>City</p>
                                        <input type='text' size='10' name='form_city' maxlength='30' value='<?php echo attr($row['city']); ?>' class='form-control pr-1 pl-1' />
                                    </div>
                                    <div class="col-md-3">
                                        <p>State</p>
                                        <select name="form_state" id="" class="form-control mt-2">
                                                        <?php
                                                            $body = sqlStatement("SELECT option_id, title FROM list_options " .
                                                            "WHERE list_id = 'state' AND activity = 1 ORDER BY seq");
                                                            while ($orow = sqlFetchArray($body)) {
                                                                echo "    <option value='" . attr($orow['option_id']) . "'";
                                                                if ($orow['option_id'] == $form_title) {
                                                                    echo " selected";
                                                                }

                                                                echo ">" . text($orow['title']) . "</option>\n";
                                                            }
                                                          
                                                        ?>
                                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <p>Country</p>
                                        <input type='text' size='10' name='form_country' maxlength='30' value='<?php echo attr($row['country']); ?>' class='form-control pr-1 pl-1' />
                                    </div>
                                    <div class="col-md-3">
                                        <p>Zip</p>
                                        <input type='text' size='10' name='form_zip' maxlength='20' value='<?php echo attr($row['zip']); ?>' class='form-control pr-1 pl-1' />
                                    </div>
                                </div>

                                <div class="row pt-4">
                                    <div class="col-md-3">
                                        <p>NPI</p>
                                        <input type="text" name='form_npi' value='<?php echo attr($row['npi']); ?>' placeholder="" class="form-control pr-1 pl-1">
                                    </div>
                                    <div class="col-md-3">
                                        <p>TIN</p>
                                        <input type="text" name='form_federaltaxid'  value='<?php echo attr($row['federaltaxid']); ?>' placeholder="" class="form-control pr-1 pl-1">
                                    </div>
                                    <div class="col-md-3">
                                        <p>Taxonomy</p>
                                        <input type="text" name='form_taxonomy' value='<?php echo attr($row['taxonomy']); ?>' placeholder="" class="form-control pr-1 pl-1">
                                    </div>
                                    <div class="col-md-3">
                                        <p>SSN</p>
                                        <input type="text" name='form_ssn' value='<?php echo attr($row['ssn']); ?>' placeholder="" class="form-control pr-1 pl-1">
                                    </div>
                                </div>
                                <div class="row pt-4">
                                    <div class="col-md-12">
                                        <p>Notes</p>
                                        <textarea name='form_notes' class="form-control pt-3" rows="3"><?php echo text($row['notes']) ?></textarea>
                                    </div>

                                </div>


                            <div class="pt-4 pb-2 col-md-12">
                                <input type='submit' name='form_save' class="form-save" value='<?php echo xla('Save'); ?>' />                              
                            </div>
                        </form>


<?php    $use_validate_js = 1;?>
<?php validateUsingPageRules($_SERVER['PHP_SELF']);?>
<!-- </div> -->
<!-- //new Code PA -->

</div>
</div>

</window-dashboard>
</div>
</div>
</section>

<script>
    $(".edt").click(function() {
// alert();
	thiss=$(this);
    $id=thiss.attr('idss');
    // alert($id);

    // alert("edit_proc_employee.php?id="+ $id);
    // $("#emp_form").remove();
// $("#emp_form").load("edit_proc_employee.php?id="+$id);
$("#emp_form").load("addrbook_list.php?id="+$id);



});

$(".dlt").click(function() {
    if(confirm('Are you sure?')){
        thiss=$(this);
        $id=thiss.attr('idss');
        $webroot=  "<?php echo $GLOBALS['webroot'];?>";


        $.ajax({
            type: 'POST',
            url: $webroot+"/interface/usergroup/delete_proc_employee.php?id="+$id,  
            data: $('#table_frm').serialize(),
            success: function(data){
            
            window.location.href="addrbook_list.php";
            }
        });

    }
});
    </script>
</body>
</html>