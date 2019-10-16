<?php
/**
 * This report lists referrals for a given date range.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
?>
<html>
<head>
    <title><?php echo xlt('Referrals'); ?></title>

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

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <script language="JavaScript">
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

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

         // The OnClick handler for referral display.

        function show_referral(transid) {
            dlgopen('../patient_file/transaction/print_referral.php?transid=' + encodeURIComponent(transid),
                '_blank', 550, 400,true); // Force new window rather than iframe because of the dynamic generation of the content in print_referral.php
            return false;
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

        #report_parameters {
            background-color: transparent !important;
            margin-top: 10px;
        }

    </style>

    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/style.css">

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
                                    <p class="text-white head-p">Referrals </p>
                                </div>
                            </div>
                        </div>
                        <div class="body-compo">
                            <div class="container-fluid">
                                <form name='theform' id='theform' method='post' action='referrals_report.php' onsubmit='return top.restoreSession()'>
                                    <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

                                    <div class="pt-4 pb-4">
                                        <div  id="report_parameters">
                                            <div class="row">
                                                <div class="col-md-2"></div>
                                                <div class="col-md-4">
                                                    <!-- <p>Employee ID</p> -->
                                                    <p> Facility</p>
                                                    <?php dropdown_facility(($form_facility), 'form_facility', true); ?>
                                                    <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                                </div>

                                                <div class="col-md-2">
                                                    <p>From</p>
                                                    <input type='date' name='form_from_date' id="form_from_date"  value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>' class='form-control pr-1 pl-1'>
                                                    
                                                </div>
                                                <div class="col-md-2">
                                                    <p>To</p>
                                                    <input type='date' name='form_to_date' id="form_to_date"  value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>' class="form-control pr-1 pl-1">
                                                    
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
                                            <table class="table table-form">
                                                <thead>

                                                    <tr class="bg-transparent">
                                                        <th>Refer To </th>
                                                        <th> Refer Date</th>

                                                        <th>Reply Date</th>
                                                        <th>Employee </th>
                                                        <th>ID</th>
                                                        <th>Reason</th>


                                                    </tr>

                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if ($_POST['form_refresh']) {
                                                    
                    
                                                        $query = "SELECT t.id, t.pid, " .
                                                        "d1.field_value AS refer_date, " .
                                                        "d3.field_value AS reply_date, " .
                                                        "d4.field_value AS body, " .
                                                        "ut.organization, uf.facility_id, p.pubpid, " .
                                                        "CONCAT(uf.fname,' ', uf.lname) AS referer_name, " .
                                                        "CONCAT(ut.fname,' ', ut.lname) AS referer_to, " .
                                                        "CONCAT(p.fname,' ', p.lname) AS patient_name " .
                                                        "FROM transactions AS t " .
                                                        "LEFT JOIN patient_data AS p ON p.pid = t.pid " .
                                                        "JOIN      lbt_data AS d1 ON d1.form_id = t.id AND d1.field_id = 'refer_date' " .
                                                        "LEFT JOIN lbt_data AS d3 ON d3.form_id = t.id AND d3.field_id = 'reply_date' " .
                                                        "LEFT JOIN lbt_data AS d4 ON d4.form_id = t.id AND d4.field_id = 'body' " .
                                                        "LEFT JOIN lbt_data AS d7 ON d7.form_id = t.id AND d7.field_id = 'refer_to' " .
                                                        "LEFT JOIN lbt_data AS d8 ON d8.form_id = t.id AND d8.field_id = 'refer_from' " .
                                                        "LEFT JOIN users AS ut ON ut.id = d7.field_value " .
                                                        "LEFT JOIN users AS uf ON uf.id = d8.field_value " .
                                                        "WHERE t.title = 'LBTref' AND " .
                                                        "d1.field_value >= ? AND d1.field_value <= ? " .
                                                        "ORDER BY ut.organization, d1.field_value, t.id";
                                                        $res = sqlStatement($query, array($form_from_date, $form_to_date));

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

                                                            ?>                      
                                                            <tr>
                                                                <td><?php
                                                                        if ($row['organization']!=null || $row['organization']!='') {
                                                                            echo text($row['organization']);
                                                                        } else {
                                                                            echo text($row['referer_to']);
                                                                        }
                                                                        ?>
                                                                </td>
                                                                <td><a href='#' onclick="return show_referral(<?php echo js_escape($row['id']); ?>)">
                                                                    <?php echo text(oeFormatShortDate($row['refer_date'])); ?>&nbsp;</a>
                                                                </td>
                                                                <td><?php echo text(oeFormatShortDate($row['reply_date'])) ?></td>
                                                                <td><?php echo text($row['patient_name']) ?></td>
                                                                <td><?php echo text($row['pubpid']) ?></td>
                                                                <td><?php echo text($row['body']) ?></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div> <!-- end of results -->

                                                   

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
