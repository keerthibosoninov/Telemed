<?php
/**
 * This report lists prescriptions and their dispensations according
 * to various input selection criteria.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("../drugs/drugs.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

 $form_from_date  = (!empty($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$form_to_date    = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_patient_id = trim($_POST['form_patient_id']);
$form_drug_name  = trim($_POST['form_drug_name']);
$form_lot_number = trim($_POST['form_lot_number']);
$form_facility   = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';

?>
<html>
<head>

<title><?php echo xlt('Prescriptions and Dispensations'); ?></title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">


    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/vue.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/main.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/addmore.js"></script>
   
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/employee_dashboard_style.css">


    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

<?php //Header::setupHeader(['report-helper']); ?>

<script language="JavaScript">

    $(function() {
        oeFixedHeaderSetup(document.getElementById('mymaintable'));
        var win = top.printLogSetup ? top : opener.top;
        win.printLogSetup(document.getElementById('printbutton'));

        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });

    // The OnClick handler for receipt display.
    function show_receipt(payid) {
        // dlgopen('../patient_file/front_payment.php?receipt=1&payid=' + payid, '_blank', 550, 400);
        return false;
    }

</script>

<style type="text/css">

#report_parameters {
    background-color: transparent !important;
    margin-top: 10px;
}

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
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
}

    input[type=date]{
        margin-top:0px;
    }
    .css_button:hover, button:hover, input[type=button]:hover, input[type=submit]:hover {
        background: #3C9DC5;
        text-decoration: none;
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
                                    <p class="text-white head-p">Report - Prescriptions and Dispensations </p>
                                </div>
                            </div>
                        </div>
                        <div class="body-compo">

                            <form name='theform' id='theform' method='post' action='prescriptions_report.php' onsubmit='return top.restoreSession()'>
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                <input type='hidden' name='form_refresh' id='form_refresh' value=''/>

                                <div class="pt-4 pb-4">
                                    <div id="report_parameters">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <p>Facility</p>
                                                <?php dropdown_facility($form_facility, 'form_facility', true); ?>

                                                <!-- <select name="" id="" class="form-control mt-2">
                                                    <option value="">Value 1</option>
                                                    <option value="">value 2</option>
                                                    <option value="">Value 3</option>
                                                </select> -->
                                            </div>
                                            <div class="col-md-2">
                                                <p>From</p>
                                                <input type='date' class='form-control pr-1 pl-1' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
                                            </div>
                                            <div class="col-md-2">
                                                <p>To</p>
                                                <input type='date' class='form-control pr-1 pl-1' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>

                                            </div>
                                            <div class="col-md-2">
                                                <p>Employee ID</p> 
                                                <input type='text' class='form-control  pr-1 pl-1' name='form_patient_id' value='<?php echo attr($form_patient_id); ?>'
                                                            title='<?php echo xla('Optional numeric patient ID'); ?>' />
                                            </div>
                                            <div class="col-md-2">
                                                <p>Drug</p> 
                                                <input type='text' class='form-control pr-1 pl-1' name='form_drug_name'  value='<?php echo attr($form_drug_name); ?>'
                                                            title='<?php echo xla('Optional drug name, use % as a wildcard'); ?>' />
                                            </div>
                                            <div class="col-md-2">
                                                <p>Lot</p>
                                                <input type='text' class='form-control pr-1 pl-1' name='form_lot_number' size='10' maxlength='20' value='<?php echo attr($form_lot_number); ?>'
                                                            title='<?php echo xla('Optional lot number, use % as a wildcard'); ?>' />
                                                
                                            </div>

                                        </div>
                                    
                                        <div class="pt-4 pb-5">
                                            <div class="row">
                                                <div class="col-md-4"></div>
                                                <div class="col-md-2"> <button class="form-save" onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>SEARCH</button></div>
                                                <div class="col-md-2"> <button class="form-save" id='printbutton'>PRINT</button></div>

                                            </div>

                                        </div>
                                    </div>
                                    <div class="table-div ">
                                        <table class="table table-form" id='mymaintable'>
                                            <thead>

                                                <tr class="bg-transparent">
                                                    <th> Employee</th>
                                                    <th>ID </th>
                                                    <th>Rx</th>
                                                    <th>Drug Name </th>
                                                    <th>NDC</th>
                                                    <th>Units</th>
                                                    <th>Refills</th>
                                                    <th>Instructed </th>
                                                    <th>Reactions</th>
                                                    <th>Dispensed</th>
                                                    <th>Qty</th>
                                                    <th>Manufacturer</th>
                                                    <th>LoT</th>

                                                </tr>

                                            </thead>
                                            <tbody>
                                            <?php
                                                if ($_POST['form_refresh']) {
                                                    $sqlBindArray = array();

                                                    $where = "r.date_modified >= ? AND " .
                                                    "r.date_modified <= ?";
                                                    array_push($sqlBindArray, $form_from_date, $form_to_date);

                                                    if ($form_patient_id) {
                                                        $where .= " AND p.pubpid = ?";
                                                        array_push($sqlBindArray, $form_patient_id);
                                                    }

                                                    if ($form_drug_name) {
                                                        $where .= " AND (d.name LIKE ? OR r.drug LIKE ?)";
                                                        array_push($sqlBindArray, $form_drug_name, $form_drug_name);
                                                    }

                                                    if ($form_lot_number) {
                                                        $where .= " AND i.lot_number LIKE ?";
                                                        array_push($sqlBindArray, $form_lot_number);
                                                    }

                                                   $query = "SELECT r.id, r.patient_id, " .
                                                    "r.date_modified, r.dosage, r.route, r.interval, r.refills, r.drug, " .
                                                    "d.name, d.ndc_number, d.form, d.size, d.unit, d.reactions, " .
                                                    "s.sale_id, s.sale_date, s.quantity, " .
                                                    "i.manufacturer, i.lot_number, i.expiration, " .
                                                    "p.pubpid, ".
                                                    "p.fname, p.lname, p.mname, u.facility_id " .
                                                    "FROM prescriptions AS r " .
                                                    "LEFT OUTER JOIN drugs AS d ON d.drug_id = r.drug_id " .
                                                    "LEFT OUTER JOIN drug_sales AS s ON s.prescription_id = r.id " .
                                                    "LEFT OUTER JOIN drug_inventory AS i ON i.inventory_id = s.inventory_id " .
                                                    "LEFT OUTER JOIN patient_data AS p ON p.pid = r.patient_id " .
                                                    "LEFT OUTER JOIN users AS u ON u.id = r.provider_id " .
                                                    "WHERE $where " .
                                                    "ORDER BY p.lname, p.fname, p.pubpid, r.id, s.sale_id";

                                                    $res = sqlStatement($query, $sqlBindArray);

                                                    $last_patient_id      = 0;
                                                    $last_prescription_id = 0;
                                                    while ($row = sqlFetchArray($res)) {
                                                        // If a facility is specified, ignore rows that do not match.
                                                        if ($form_facility !== '') {
                                                            if ($form_facility) {
                                                                if ($row['facility_id'] != $form_facility) {
                                                                    continue;
                                                                }
                                                            } else {
                                                                if (!empty($row['facility_id'])) {
                                                                    continue;
                                                                }
                                                            }
                                                        }

                                                        $patient_name    = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
                                                        $patient_id      = $row['pubpid'];
                                                        $prescription_id = $row['id'];
                                                        $drug_name       = empty($row['name']) ? $row['drug'] : $row['name'];
                                                        $ndc_number      = $row['ndc_number'];
                                                        $drug_units      = text($row['size']) . ' ' .
                                                            generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'), $row['unit']);
                                                        $refills         = $row['refills'];
                                                        $reactions       = $row['reactions'];
                                                        $instructed      = text($row['dosage']) . ' ' .
                                                            generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'), $row['form']) .
                                                            ' ' .
                                                                generate_display_field(array('data_type'=>'1','list_id'=>'drug_interval'), $row['interval']);
                                                        //if ($row['patient_id'] == $last_patient_id) {
                                                        if (strcmp($row['pubpid'], $last_patient_id) == 0) {
                                                            $patient_name = '&nbsp;';
                                                            $patient_id   = '&nbsp;';
                                                            if ($row['id'] == $last_prescription_id) {
                                                                $prescription_id = '&nbsp;';
                                                                $drug_name       = '&nbsp;';
                                                                $ndc_number      = '&nbsp;';
                                                                $drug_units      = '&nbsp;';
                                                                $refills         = '&nbsp;';
                                                                $reactions       = '&nbsp;';
                                                                $instructed      = '&nbsp;';
                                                            }
                                                        }
                                                    ?>
                                            
                                                    <tr>
                                                        <td><?php echo text($patient_name); ?></td>
                                                        <td><?php echo text($patient_id); ?></td>
                                                        <td><?php echo text($prescription_id); ?></td>
                                                        <td><?php echo text($drug_name); ?></td>
                                                        <td><?php echo text($ndc_number); ?></td>
                                                        <td><?php echo $drug_units; ?></td>
                                                        <td><?php echo text($refills); ?></td>
                                                        <td><?php echo $instructed; ?></td>
                                                        <td><?php echo text($reactions); ?></td>
                                                        <td><a href='../drugs/dispense_drug.php?sale_id=<?php echo attr_url($row['sale_id']); ?>'style='color:#0000ff' target='_blank'>
                                                                    <?php echo text(oeFormatShortDate($row['sale_date'])); ?></a></td>
                                                        <td><?php echo text($row['quantity']); ?></td>
                                                        <td><?php echo text($row['manufacturer']); ?></td>
                                                        <td><?php echo text($row['lot_number']); ?></td>
                                                    </tr>
                                                    <?php
                                                        $last_prescription_id = $row['id'];
                                                        $last_patient_id = $row['pubpid'];
                                                    } // end while
                                            
                                                }   
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>


                                </div>


                            
                            </form>
                        </div>
               
                    </window-dashboard>
                </div>
            </div>
        </section>
</body>

</html>
