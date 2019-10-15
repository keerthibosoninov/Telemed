<?php
/**
 * This module shows relative insurance usage by unique patients
 * that are seen within a given time period.  Each patient that had
 * a visit is counted only once, regardless of how many visits.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../library/acl.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Might want something different here.
//
// if (! acl_check('acct', 'rep')) die("Unauthorized access.");

$form_from_date = (!empty($_POST['form_from_date'])) ?  DateToYYYYMMDD($_POST['form_from_date']) : '';
$form_to_date   = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');

if ($_POST['form_csvexport']) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=insurance_distribution.csv");
    header("Content-Description: File Transfer");
    // CSV headers:
    if (true) {
        echo '"Insurance",';
        echo '"Charges",';
        echo '"Visits",';
        echo '"Patients",';
        echo '"Pt Pct"' . "\n";
    }
} else {
    ?>
<html>
<head>

<title><?php echo xlt('Patient Insurance Distribution'); ?></title>


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

    <!--  km -->

    <?php Header::setupHeader('datetime-picker'); ?>

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
                                    <p class="text-white head-p">Employee Insurance Distribution </p>
                                </div>
                            </div>
                        </div>
                        <div class="body-compo">
                            <div id="report_parameters_daterange">
                                <span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Patient Insurance Distribution'); ?></span>
                                <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt("to") . " &nbsp; ". text(oeFormatShortDate($form_to_date)); ?>
                          
                            </div>
                            <form name='theform' method='post' action='insurance_allocation_report.php' id='theform' onsubmit='return top.restoreSession()'>
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
                                <input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
                                <div class="container-fluid">
                                    <div class="pt-4 pb-4">

                                        <div id="report_parameters">
                                            <div class="row">
                                                <div class="col-md-4"></div>

                                                <div class="col-md-2">
                                                    <p>From</p>
                                                    <input type='date' class='datepicker form-control pr-1 pl-1' name='form_from_date' id="form_from_date"  value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>

                                                </div>
                                                <div class="col-md-2">
                                                    <p>To</p>
                                                    <input type='date' class='datepicker form-control pr-1 pl-1' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
                                                </div>




                                            </div>

                                            <div class="pt-4 pb-5">
                                                <div class="row">
                                                    <div class="col-md-3"></div>
                                                    <div class="col-md-2"> <button class="form-save" onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").val(""); $("#theform").submit();'>SEARCH</button></div>
                                                    <div class="col-md-2"> <button class="form-save"  id='printbutton'>PRINT</button></div>
                                                    <div class="col-md-2"> <button class="form-save" onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>Export to CSV</button></div>

                                                </div>

                                            </div>
                                        </div>
                                        <div class="table-div ">
                                            <table class="table table-form">
                                                <thead>

                                                    <tr>
                                                        <th>Primary Insurance</th>
                                                        <th>Charges</th>
                                                        <th>Visits</th>
                                                        <th>Emloyees</th>
                                                        <th>Percentage (%)</th>

                                                    </tr>

                                                </thead>
                                                <tbody>
                                                    <?php
                                                    } // end not export
                                                    if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
                                                        $query = "SELECT b.pid, b.encounter, SUM(b.fee) AS charges, " .
                                                        "MAX(fe.date) AS date " .
                                                        "FROM form_encounter AS fe, billing AS b " .
                                                        "WHERE fe.date >= ? AND fe.date <= ? " .
                                                        "AND b.pid = fe.pid AND b.encounter = fe.encounter " .
                                                        "AND b.code_type != 'COPAY' AND b.activity > 0 AND b.fee != 0 " .
                                                        "GROUP BY b.pid, b.encounter ORDER BY b.pid, b.encounter";

                                                    $res = sqlStatement($query, array((!empty($form_from_date)) ? $form_from_date : '0000-00-00', $form_to_date));
                                                    $insarr = array();
                                                    $prev_pid = 0;
                                                    $patcount = 0;

                                                    while ($row = sqlFetchArray($res)) {
                                                        $patient_id = $row['pid'];
                                                        $encounter_date = $row['date'];
                                                        $irow = sqlQuery("SELECT insurance_companies.name " .
                                                        "FROM insurance_data, insurance_companies WHERE " .
                                                        "insurance_data.pid = ? AND " .
                                                        "insurance_data.type = 'primary' AND " .
                                                        "insurance_data.date <= ? AND " .
                                                        "insurance_companies.id = insurance_data.provider " .
                                                        "ORDER BY insurance_data.date DESC LIMIT 1", array($patient_id, $encounter_date));
                                                        $plan = $irow['name'] ? $irow['name'] : '-- No Insurance --';
                                                        $insarr[$plan]['visits'] += 1;
                                                        $insarr[$plan]['charges'] += sprintf('%0.2f', $row['charges']);
                                                        if ($patient_id != $prev_pid) {
                                                            ++$patcount;
                                                            $insarr[$plan]['patients'] += 1;
                                                            $prev_pid = $patient_id;
                                                        }
                                                    }

                                                    ksort($insarr);

                                                    foreach ($insarr as $key => $val) {
                                                        if ($_POST['form_csvexport']) {
                                                            echo '"' . $key                                                . '",';
                                                            echo '"' . oeFormatMoney($val['charges'])                      . '",';
                                                            echo '"' . $val['visits']                                      . '",';
                                                            echo '"' . $val['patients']                                    . '",';
                                                            echo '"' . sprintf("%.1f", $val['patients'] * 100 / $patcount) . '"' . "\n";
                                                        } else {
                                                            ?>
                                                    <tr>
                                                        <td><?php echo text($key); ?></td>
                                                        <td><?php echo text(oeFormatMoney($val['charges'])); ?></td>
                                                        <td><?php echo text($val['visits']); ?></td>
                                                        <td><?php echo text($val['patients']); ?></td>
                                                        <td><?php printf("%.1f", $val['patients'] * 100 / $patcount) ?></td>
                                                    </tr>
                                                    <?php
                                                            } // end not export
                                                        } // end while
                                                    } // end if

                                                if (! $_POST['form_csvexport']) {
                                                    ?>

   
                                                </tbody>
                                            </table>
                                        </div>


                                    </div>
                                </div>
                            </form>
                        </div>
                    </window-dashboard >
                </div>
            </div>
        </section>
    </body>

</html>
    <?php
} // end not export
?>
<!--  -->
 