<?php
/**
 * This report lists  patient immunizations for a given date range.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Ensoftek Inc.
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : '';
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : '';

function tr($a)
{
    return (str_replace(' ', '^', $a));
}

function format_cvx_code($cvx_code)
{

    if ($cvx_code < 10) {
        return "0$cvx_code";
    }

    return $cvx_code;
}

function format_phone($phone)
{

    $phone = preg_replace("/[^0-9]/", "", $phone);
    switch (strlen($phone)) {
        case 7:
            return tr(preg_replace("/([0-9]{3})([0-9]{4})/", "000 $1$2", $phone));
        case 10:
            return tr(preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 $2$3", $phone));
        default:
            return tr("000 0000000");
    }
}

function format_ethnicity($ethnicity)
{

    switch ($ethnicity) {
        case "hisp_or_latin":
            return ("H^Hispanic or Latino^HL70189");
        case "not_hisp_or_latin":
            return ("N^not Hispanic or Latino^HL70189");
        default: // Unknown
            return ("U^Unknown^HL70189");
    }
}

$sqlBindArray = array();
$query =
  "select " .
  "i.patient_id as patientid, " .
  "p.language, ".
  "i.cvx_code , " ;
if ($_POST['form_get_hl7']==='true') {
    $query .=
    "DATE_FORMAT(p.DOB,'%Y%m%d') as DOB, ".
    "concat(p.street, '^^', p.city, '^', p.state, '^', p.postal_code) as address, ".
    "p.country_code, ".
    "p.phone_home, ".
    "p.phone_biz, ".
    "p.status, ".
    "p.sex, ".
    "p.ethnoracial, ".
    "p.race, ".
    "p.ethnicity, ".
    "c.code_text, ".
    "c.code, ".
    "c.code_type, ".
    "DATE_FORMAT(i.vis_date,'%Y%m%d') as immunizationdate, ".
    "DATE_FORMAT(i.administered_date,'%Y%m%d') as administered_date, ".
    "i.lot_number as lot_number, ".
    "i.manufacturer as manufacturer, ".
    "concat(p.fname, '^', p.lname) as patientname, ";
} else {
    $query .= "concat(p.fname, ' ',p.mname,' ', p.lname) as patientname, ".
    "i.vis_date as immunizationdate, "  ;
}

$query .=
  "i.id as immunizationid,i.expiration_date, c.code_text_short as immunizationtitle ".
  "from immunizations i, patient_data p, codes c ".
  "left join code_types ct on c.code_type = ct.ct_id ".
  "where ".
  "ct.ct_key='CVX' and ";

if (!empty($form_from_date)) {
    $query .= "i.vis_date >= ? and " ;
    array_push($sqlBindArray, $form_from_date);
}

if (!empty($form_to_date)) {
    $query .= "i.vis_date <= ? and ";
    array_push($sqlBindArray, $form_to_date);
}

$form_code = isset($_POST['form_code']) ? $_POST['form_code'] : array();
if (empty($form_code)) {
    $query_codes = '';
} else {
    $query_codes = "c.id in (";
    $codes = '';
    foreach ($form_code as $code) {
        $codes .= $code . ',';
    }
    $codes = substr($codes, 0, -1);
    $query_codes .= add_escape_custom($codes) . ") and ";
}

$query .= "i.patient_id=p.pid and ".
$query_codes .
"i.cvx_code = c.code and ";

//do not show immunization added erroneously
$query .=  "i.added_erroneously = 0";

$D="\r";
$nowdate = date('Ymd');
$now = date('YmdGi');
$now1 = date('Y-m-d G:i');
$filename = "imm_reg_". $now . ".hl7";

// GENERATE HL7 FILE
if ($_POST['form_get_hl7']==='true') {
    $content = '';

    $res = sqlStatement($query, $sqlBindArray);

    while ($r = sqlFetchArray($res)) {
        $content .= "MSH|^~\&|OPENEMR||||$nowdate||".
        "VXU^V04^VXU_V04|OPENEMR-110316102457117|P|2.5.1" .
        "$D" ;
        if ($r['sex']==='Male') {
            $r['sex'] = 'M';
        }

        if ($r['sex']==='Female') {
            $r['sex'] = 'F';
        }

        if ($r['status']==='married') {
            $r['status'] = 'M';
        }

        if ($r['status']==='single') {
            $r['status'] = 'S';
        }

        if ($r['status']==='divorced') {
            $r['status'] = 'D';
        }

        if ($r['status']==='widowed') {
            $r['status'] = 'W';
        }

        if ($r['status']==='separated') {
            $r['status'] = 'A';
        }

        if ($r['status']==='domestic partner') {
            $r['status'] = 'P';
        }

        $content .= "PID|" . // [[ 3.72 ]]
        "|" . // 1. Set id
        "|" . // 2. (B)Patient id
        $r['patientid']. "^^^MPI&2.16.840.1.113883.19.3.2.1&ISO^MR" . "|". // 3. (R) Patient indentifier list. TODO: Hard-coded the OID from NIST test.
        "|" . // 4. (B) Alternate PID
        $r['patientname']."|" . // 5.R. Name
        "|" . // 6. Mather Maiden Name
        $r['DOB']."|" . // 7. Date, time of birth
        $r['sex']."|" . // 8. Sex
        "|" . // 9.B Patient Alias
        "2106-3^" . $r['race']. "^HL70005" . "|" . // 10. Race // Ram change
        $r['address'] . "^^M" . "|" . // 11. Address. Default to address type  Mailing Address(M)
        "|" . // 12. county code
        "^PRN^^^^" . format_phone($r['phone_home']) . "|" . // 13. Phone Home. Default to Primary Home Number(PRN)
        "^WPN^^^^" . format_phone($r['phone_biz']) . "|" . // 14. Phone Work.
        "|" . // 15. Primary language
        $r['status']."|" . // 16. Marital status
        "|" . // 17. Religion
        "|" . // 18. patient Account Number
        "|" . // 19.B SSN Number
        "|" . // 20.B Driver license number
        "|" . // 21. Mathers Identifier
        format_ethnicity($r['ethnicity']) . "|" . // 22. Ethnic Group
        "|" . // 23. Birth Plase
        "|" . // 24. Multiple birth indicator
        "|" . // 25. Birth order
        "|" . // 26. Citizenship
        "|" . // 27. Veteran military status
        "|" . // 28.B Nationality
        "|" . // 29. Patient Death Date and Time
        "|" . // 30. Patient Death Indicator
        "|" . // 31. Identity Unknown Indicator
        "|" . // 32. Identity Reliability Code
        "|" . // 33. Last Update Date/Time
        "|" . // 34. Last Update Facility
        "|" . // 35. Species Code
        "|" . // 36. Breed Code
        "|" . // 37. Breed Code
        "|" . // 38. Production Class Code
        ""  . // 39. Tribal Citizenship
        "$D" ;
        $content .= "ORC" . // ORC mandatory for RXA
        "|" .
        "RE" .
        "$D" ;
        $content .= "RXA|" .
        "0|" . // 1. Give Sub-ID Counter
        "1|" . // 2. Administrattion Sub-ID Counter
        $r['administered_date']."|" . // 3. Date/Time Start of Administration
        $r['administered_date']."|" . // 4. Date/Time End of Administration
        format_cvx_code($r['code']). "^" . $r['immunizationtitle'] . "^" . "CVX" ."|" . // 5. Administration Code(CVX)
        "999|" . // 6. Administered Amount. TODO: Immunization amt currently not captured in database, default to 999(not recorded)
        "|" . // 7. Administered Units
        "|" . // 8. Administered Dosage Form
        "|" . // 9. Administration Notes
        "|" . // 10. Administering Provider
        "|" . // 11. Administered-at Location
        "|" . // 12. Administered Per (Time Unit)
        "|" . // 13. Administered Strength
        "|" . // 14. Administered Strength Units
        $r['lot_number']."|" . // 15. Substance Lot Number
        "|" . // 16. Substance Expiration Date
        "MSD" . "^" . $r['manufacturer']. "^" . "HL70227" . "|" . // 17. Substance Manufacturer Name
        "|" . // 18. Substance/Treatment Refusal Reason
        "|" . // 19.Indication
        "|" . // 20.Completion Status
        "A" . // 21.Action Code - RXA
        "$D" ;
    }

  // send the header here
    header('Content-type: text/plain');
    header('Content-Disposition: attachment; filename=' . $filename);

  // put the content in the file
    echo($content);
    exit;
}
?>
<html>
<head>
    <title><?php echo xlt('Immunization Registry'); ?></title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/employee_dashboard_style.css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/style.css">

    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/vue.js"></script>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/main.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/addmore.js"></script>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <script language="JavaScript">
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

        $(function() {
            // var win = top.printLogSetup ? top : opener.top;
            // win.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });
        function printForm(e){
                 var win = top.printLogPrint ? top : opener.top;
                 win.printLogPrint(window);
                 e.preventDefault();
            }
    </script>

    <style type="text/css">
        /* specifically include & exclude from printing */
        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }
            #report_parameters_daterange {
                visibility: visible;
                display: inline;
                margin-bottom: 10px;
            }
            #report_results table {
               margin-top: 0px;
            }
        }
        /* specifically exclude some from the screen */
        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
                display: none;
            }
            #report_results {
                width: 100%;
            }
        }

        #report_parameters {
            background-color: transparent !important;
            margin-top: 10px;
        }

        
        .css_button:hover, button:hover, input[type=button]:hover, input[type=submit]:hover {
            background: #3C9DC5;
            text-decoration: none;
        }

        .table-div{
            height:auto!important;
            overflow:auto;
        }
        thead{
            padding: .75rem;
            border-top: 1px solid #dee2e6;
        }
        .custom-btn{
            margin-top: 1rem;
        }
        .form-save{
            padding-top: 6px;
            padding-bottom: 6px;
        }

        body{
            font-size: 16px;
        }

        .table td, .table th {
            padding: 1.25rem !important;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>

<body class="body_top">

        <section>
            <div class="body-content body-content2">
                <div class="container-fluid pb-4 pt-4">
                    <window-dashboard title="" class="icon-hide">
                        <div class="head-component">
                            <div class="row">
                                <div class="col-6"></div>
                                <div class="col-6">
                                    <p class="text-white head-p">Immunization Registry </p>
                                </div>
                            </div>
                        </div>
                        <div class="body-compo">
                            <div class="container-fluid">
                                <form name='theform' id='theform' method='post' action='immunization_report.php' onsubmit='return top.restoreSession()'>
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
                                    <input type='hidden' name='form_get_hl7' id='form_get_hl7' value=''/>
                                    <div class="pt-4 pb-4">
                                        <div id="report_parameters">
                                            <div class="row">
                                                <div class="col-md-2"></div>
                                                <div class="col-md-4">
                                                    <p>Codes</p>
                                                   
                                                    <?php
                                                    // Build a drop-down list of codes.
                                                    //
                                                    $query1 = "select id, concat('CVX:',code) as name from codes ".
                                                    " left join code_types ct on codes.code_type = ct.ct_id ".
                                                    " where ct.ct_key='CVX' ORDER BY name";
                                                    $cres = sqlStatement($query1);
                                                    echo "   <select multiple='multiple' size='3' name='form_code[]' class='form-control mt-2'>\n";
                                                   
                                                    while ($crow = sqlFetchArray($cres)) {
                                                        $codeid = $crow['id'];
                                                        echo "    <option value='" . attr($codeid) . "'";
                                                        if (in_array($codeid, $form_code)) {
                                                            echo " selected";
                                                        }

                                                        echo ">" . text($crow['name']) . "\n";
                                                    }

                                                    echo "   </select>\n";
                                                    ?>
                                                </div>
                                                <div class="col-md-2">
                                                    <p>From</p>
                                                    <input type='text' name='form_from_date' id="form_from_date"class='datepicker form-control pr-1 pl-1'
                                                         value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>' placeholder="dd-mm-yyyy">
                                                   
                                                </div>
                                                <div class="col-md-2">
                                                    <p>To</p> 
                                                    <input type='text' name='form_to_date' id="form_to_date" class='datepicker form-control pr-1 pl-1'
                                                             value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>' placeholder="dd-mm-yyyy">
                                                   
                                                </div>



                                            </div>
                                            <div class="pt-4 pb-5 custom-btn">
                                                <div class="row">
                                                    <div class="col-md-4"></div>
                                                    <div class="col-md-2"> <button class="form-save" onclick='$("#form_refresh").attr("value","true");
                                                                                                        $("#form_get_hl7").attr("value","false");
                                                                                                        $("#theform").submit();
                                                                                '>SEARCH</button>
                                                    </div>
                                                    <div class="col-md-2"> <button class="form-save" onclick="printForm(event)" id='printbutton'>PRINT</button></div>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="table-div ">
                                            <table class="table table-form">
                                                <thead>

                                                    <tr>
                                                        
                                                        <th>Immunization Code</th>
                                                        <th>Immunization Name</th>
                                                        <th>Date Administered</th>
                                                        <th>Next Due Date </th>
                                                    </tr>

                                                </thead>
                                                <tbody>
                                                    <div id="report_results">

                                                        <?php
                                                        if ($_POST['form_refresh']) {
                                                            $total = 0;
                                                            
                                                            $res = sqlStatement($query, $sqlBindArray);
                                                            
                                                            
                                                            while ($row = sqlFetchArray($res)) {

                                                        ?>
                                                        <tr>
                                                            <!-- <td> <?php echo text($row['patientid']); ?></td>
                                                            <td><?php echo text($row['patientname']); ?></td> -->
                                                            <td> <?php echo text($row['cvx_code']); ?></td>
                                                            <td><?php echo text($row['immunizationtitle']); ?></td>
                                                            <td><?php echo text($row['immunizationdate']); ?></td>
                                                            <td><?php echo text($row['expiration_date']); ?></td>

                                                        </tr>
                                                        <?php
                                                            ++$total;
                                                            }
                                                        }   
                                                        ?>
                                                    </div>
                                                </tbody>
                                            </table>
                                        </div>


                                    </div>
                                </form>

                            </div>
                        </div>
                    </window-dashboard>
                </div>
            </div>
        </section>





</body>
</html>
