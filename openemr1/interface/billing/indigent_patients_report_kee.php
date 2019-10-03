<?php
/**
 * This is the Indigent Patients Report.  It displays a summary of
 * encounters within the specified time period for patients without
 * insurance.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$alertmsg = '';

function bucks($amount)
{
    if ($amount) {
        return oeFormatMoney($amount);
    }

    return "";
}

$form_start_date = (!empty($_POST['form_start_date'])) ?  DateToYYYYMMDD($_POST['form_start_date']) : date('Y-01-01');
$form_end_date  = (!empty($_POST['form_end_date'])) ? DateToYYYYMMDD($_POST['form_end_date']) : date('Y-m-d');


// In the case of CSV export only, a download will be forced.
if ($_POST['form_csvexport']) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=patient_list.csv");
    header("Content-Description: File Transfer");
} else {
    ?>
<html>
<head>

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


.css_button:hover, button:hover, input[type=button]:hover, input[type=submit]:hover {
    background: #3C9DC5;
    text-decoration: none;
}

#report_parameters {
    background-color: transparent !important;
    margin-top: 10px;
}
</style>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/employee_dashboard_style.css">

    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/vue.js"></script>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/main.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/addmore.js"></script>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/style.css">


<?php Header::setupHeader('datetime-picker'); ?>

<title><?php echo xlt('Indigent Patients Report')?></title>

<script language="JavaScript">

    $(function() {
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

</script>

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
                                    <p class="text-white head-p">Indigent Employees </p>
                                </div>
                            </div>
                        </div>
                        <div class="body-compo">
                            <div id="report_parameters_daterange">
                                <span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Indigent Patients'); ?></span>
                                <?php echo text(oeFormatShortDate($form_start_date)) . " &nbsp; " . xlt("to") . " &nbsp; ". text(oeFormatShortDate($form_end_date)); ?>
                          
                            </div>
                            <form method='post' action='indigent_patients_report.php' id='theform' onsubmit='return top.restoreSession()'>
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                <input type='hidden' name='form_refresh' id='form_refresh' value=''/>

                                <div class="container-fluid">
                                    <div class="pt-4 pb-4">

                                        <div id="report_parameters">
                                            <div class="row">
                                                <div class="col-md-4"></div>

                                                <div class="col-md-2">

                                                    <p class="">Visits</p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4"></div>

                                                <div class="col-md-2">
                                                    <p>From</p>
                                                    <input type='date' class='datepicker form-control pr-1 pl-1' name='form_start_date' id="form_start_date"  value='<?php echo attr(oeFormatShortDate($form_start_date)); ?>'>
                                                </div>
                                                <div class="col-md-2">
                                                    <p>To</p> 
                                                    <input type='date' class='datepicker form-control pr-1 pl-1' name='form_end_date' id="form_end_date"  value='<?php echo attr(oeFormatShortDate($form_end_date)); ?>'>
                                                </div>
                                            </div>

                                            <div class="pt-4 pb-5">
                                                <div class="row">
                                                    <div class="col-md-3"></div>
                                                    <div class="col-md-2"> <button class="form-save" onclick='$("#form_csvexport").val("");$("#form_refresh").attr("value","true"); $("#theform").submit();'>SEARCH</button></div>
                                                    <div class="col-md-2"> <button class="form-save" id='printbutton'>PRINT</button></div>
                                                    <div class="col-md-2"> <button class="form-save" onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>Export to CSV</button></div>

                                                </div>

                                            </div>
                                        </div>
                                        <?php
                                        } // end not form_csvexport

                                        if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
                                            if ($_POST['form_csvexport']) {
                                                // CSV headers:
                                                echo '"' . xl('Employee') . '",';
                                                echo '"' . xl('SSN') . '",';
                                                echo '"' . xl('Invoice') . '",';
                                                echo '"' . xl('SVC Date') . '",';
                                                echo '"' . xl('Due Date') . '",';
                                                echo '"' . xl('Amount') . '",';
                                                echo '"' . xl('Paid') . '",';
                                                echo '"' . xl('Balance') . '"' . "\n";
                                            } else {
                                                ?>
                                        <div class="table-div ">
                                            <table class="table table-form">
                                                <thead>

                                                    <tr>
                                                        <th>Employee</th>
                                                        <th>SSN</th>
                                                        <th>Invoice</th>
                                                        <th>SVC Date</th>
                                                        <th>Due Date</th>
                                                        <th>Amount</th>
                                                        <th>Paid</th>
                                                        <th>Balance</th>
                                                    </tr>

                                                </thead>
                                                <!-- <tbody>
                                                    <tr>
                                                        <td>systco</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td>5000</td>
                                                        <td>4000</td>
                                                        <td>1000</td>

                                                    </tr>
                                                </tbody> -->
                                                <tbody>

                                                    <?php
                                                } // end not export
                                                    if ($_POST['form_refresh']) {
                                                        if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
                                                            CsrfUtils::csrfNotVerified();
                                                        }

                                                        $where = "";
                                                        $sqlBindArray = array();

                                                        if ($form_start_date) {
                                                            $where .= " AND e.date >= ?";
                                                            array_push($sqlBindArray, $form_start_date);
                                                        }

                                                        if ($form_end_date) {
                                                            $where .= " AND e.date <= ?";
                                                            array_push($sqlBindArray, $form_end_date);
                                                        }

                                                        $rez = sqlStatement("SELECT " .
                                                        "e.date, e.encounter, p.pid, p.lname, p.fname, p.mname, p.ss " .
                                                        "FROM form_encounter AS e, patient_data AS p, insurance_data AS i " .
                                                        "WHERE p.pid = e.pid AND i.pid = e.pid AND i.type = 'primary' " .
                                                        "AND i.provider = ''$where " .
                                                        "ORDER BY p.lname, p.fname, p.mname, p.pid, e.date", $sqlBindArray);

                                                        $total_amount = 0;
                                                        $total_paid   = 0;

                                                        for ($irow = 0; $row = sqlFetchArray($rez); ++$irow) {
                                                            $patient_id = $row['pid'];
                                                            $encounter_id = $row['encounter'];
                                                            $invnumber = $row['pid'] . "." . $row['encounter'];
                                                            $inv_duedate = '';
                                                            $arow = sqlQuery("SELECT SUM(fee) AS amount FROM drug_sales WHERE " .
                                                            "pid = ? AND encounter = ?", array($patient_id, $encounter_id));
                                                            $inv_amount = $arow['amount'];
                                                            $arow = sqlQuery("SELECT SUM(fee) AS amount FROM billing WHERE " .
                                                            "pid = ? AND encounter = ? AND " .
                                                            "activity = 1 AND code_type != 'COPAY'", array($patient_id, $encounter_id));
                                                            $inv_amount += $arow['amount'];
                                                            $arow = sqlQuery("SELECT SUM(fee) AS amount FROM billing WHERE " .
                                                            "pid = ? AND encounter = ? AND " .
                                                            "activity = 1 AND code_type = 'COPAY'", array($patient_id, $encounter_id));
                                                            $inv_paid = 0 - $arow['amount'];
                                                            $arow = sqlQuery("SELECT SUM(pay_amount) AS pay, " .
                                                            "sum(adj_amount) AS adj FROM ar_activity WHERE " .
                                                            "pid = ? AND encounter = ?", array($patient_id, $encounter_id));
                                                            $inv_paid   += floatval($arow['pay']);
                                                            $inv_amount -= floatval($arow['adj']);
                                                            $total_amount += $inv_amount;
                                                            $total_paid   += $inv_paid;

                                                            $bgcolor = (($irow & 1) ? "#ffdddd" : "#ddddff");
                                                            ?>
                                                            <?php
                                                            if ($_POST['form_csvexport']) {
                                                                echo '"' . text($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']) .'",';
                                                                echo '"' . qescape($row['ss']) . '",';
                                                                echo '"' . qescape(text($invnumber)) . '",';
                                                                echo '"' . text(oeFormatShortDate(substr($row['date'], 0, 10))) . '",';
                                                                echo '"' . text(oeFormatShortDate($inv_duedate)) . '",';
                                                                echo '"' . qescape(bucks($inv_amount)) . '",';
                                                                echo '"' . qescape(bucks($inv_paid)) . '",';
                                                                echo '"' . qescape(bucks($inv_amount - $inv_paid)) . '"' . "\n";
                                                            } else {
                                                            ?>
                                                            <tr>
                                                                <td>&nbsp;<?php echo text($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']); ?></td>
                                                                <td>
                                                                    &nbsp;<?php echo text($row['ss']); ?>
                                                                </td>
                                                                <td>
                                                                    &nbsp;<?php echo text($invnumber); ?></a>
                                                                </td>
                                                                <td>
                                                                    &nbsp;<?php echo text(oeFormatShortDate(substr($row['date'], 0, 10))); ?>
                                                                </td>
                                                                <td>
                                                                    &nbsp;<?php echo text(oeFormatShortDate($inv_duedate)); ?>
                                                                </td>
                                                                <td >
                                                                        <?php echo bucks($inv_amount); ?>&nbsp;
                                                                </td>
                                                                <td >
                                                                        <?php echo bucks($inv_paid); ?>&nbsp;
                                                                </td>
                                                                <td >
                                                                        <?php echo bucks($inv_amount - $inv_paid); ?>&nbsp;
                                                                </td>
                                                            </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                        <?php
                                                        }
                                                        if (!$_POST['form_csvexport']) {
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                &nbsp;<?php echo xlt('Totals'); ?>
                                                            </td>
                                                            <td>
                                                                &nbsp;
                                                            </td>
                                                            <td>
                                                                &nbsp;
                                                            </td>
                                                            <td>
                                                                &nbsp;
                                                            </td>
                                                            <td>
                                                                &nbsp;
                                                            </td>
                                                            <td >
                                                                <?php echo bucks($total_amount); ?>&nbsp;
                                                            </td>
                                                            <td >
                                                                <?php echo bucks($total_paid); ?>&nbsp;
                                                            </td>
                                                            <td >
                                                                <?php echo bucks($total_amount - $total_paid); ?>&nbsp;
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    }
                                                }
                                                   
                                                if (!$_POST['form_csvexport']) {

                                                ?>
                                                </tbody>
                                            </table>
                                        </div>


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

    <?php
        }
        ?>
