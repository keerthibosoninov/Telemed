<?php
/**
 * This report lists patients that were seen within a given date
 * range, or all patients if no date range is entered.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Prepare a string for CSV export.
function qescape($str)
{
    $str = str_replace('\\', '\\\\', $str);
    return str_replace('"', '\\"', $str);
}

$from_date = DateToYYYYMMDD($_POST['form_from_date']);
$to_date   = DateToYYYYMMDD($_POST['form_to_date']);
if (empty($to_date) && !empty($from_date)) {
    $to_date = date('Y-12-31');
}

if (empty($from_date) && !empty($to_date)) {
    $from_date = date('Y-01-01');
}

$form_provider = empty($_POST['form_provider']) ? 0 : intval($_POST['form_provider']);

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

<title><?php echo xlt('Patient List'); ?></title>

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
    <!--  km -->

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

<script language="JavaScript">

$(function() {
    oeFixedHeaderSetup(document.getElementById('mymaintable'));
    top.printLogSetup(document.getElementById('printbutton'));

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


.css_button:hover, button:hover, input[type=button]:hover, input[type=submit]:hover {
    background: #3C9DC5;
    text-decoration: none;
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
                                    <p class="text-white head-p">Employee List </p>
                                </div>
                            </div>
                        </div>
                        <div class="body-compo">
                            <div id="report_parameters_daterange">
                                <span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Patient List'); ?></span>
                                <?php if (!(empty($to_date) && empty($from_date))) { ?>
                                <?php echo text(oeFormatShortDate($from_date)) ." &nbsp; " . xlt('to') . " &nbsp; " . text(oeFormatShortDate($to_date)); ?>
                                <?php } ?>                            
                            </div>
                            <form name='theform' id='theform' method='post' action='patient_list.php' onsubmit='return top.restoreSession()'>
                                <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
                                <input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

                                <div class="container-fluid">
                                    <div class="pt-4 pb-4">
                                        <div id="report_parameters">
                                            <div class="row" >
                                                <div class="col-md-2"></div>
                                                <div class="col-md-4">
                                                    <p>Providers</p>
                                                    <?php
                                                    generate_form_field(array('data_type' => 10, 'field_id' => 'provider',
                                                    'empty_title' => '-- All --'), $_POST['form_provider']);
                                                    ?>
                                                </div>
                                                <div class="col-md-2">
                                                    <p>From</p>
                                                    <input class='form-control pr-1 pl-1' type='date' name='form_from_date' id="form_from_date"  value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
                                                </div>
                                                <div class="col-md-2">
                                                    <p>To</p>
                                                    <input class='form-control pr-1 pl-1 ' type='date' name='form_to_date' id="form_to_date" value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
                                                    <!-- <input type="date" placeholder="" class="form-control pr-1 pl-1"> -->
                                                </div>

                                            </div>
                                            <div class="pt-4 pb-5">
                                                <div class="row">
                                                    <div class="col-md-3"></div>
                                                    <div class="col-md-2"> <button class="form-save" onclick='$("#form_csvexport").val(""); $("#form_refresh").attr("value","true"); $("#theform").submit();'>SEARCH</button></div>
                                                    <div class="col-md-2"> <button class="form-save" id='printbutton'>PRINT</button></div>
                                                    <div class="col-md-2"> <button class="form-save"  onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>Export to CSV</button></div>
                                                </div>

                                            </div>
                                        </div>
                                        <?php
                                            } // end not form_csvexport

                                        if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
                                            if ($_POST['form_csvexport']) {
                                                // CSV headers:
                                                echo '"' . xl('Last Visit') . '",';
                                                echo '"' . xl('First') . '",';
                                                echo '"' . xl('Last') . '",';
                                                echo '"' . xl('Middle') . '",';
                                                echo '"' . xl('ID') . '",';
                                                echo '"' . xl('Street') . '",';
                                                echo '"' . xl('City') . '",';
                                                echo '"' . xl('State') . '",';
                                                echo '"' . xl('Zip') . '",';
                                                echo '"' . xl('Home Phone') . '",';
                                                echo '"' . xl('Work Phone') . '"' . "\n";
                                            } else {
                                                ?>
                                                <div class="table-div"  id="">
                                                    <table class="table table-form" >
                                                        <thead>

                                                            <tr class="bg-transparent">
                                                                <th> Last Visit</th>
                                                                <th>Employee </th>
                                                                <th>ID</th>
                                                                <th>Address</th>
                                                                <th>Phone</th>
                                                                <th>E-Mail ID</th>

                                                            </tr>

                                                        </thead>
                                                
                                                <tbody>
                                             
                                            <?php
                                            } // end not export
                                            $totalpts = 0;
                                            $sqlArrayBind = array();
                                            $query = "SELECT " .
                                            "p.fname, p.mname, p.lname, p.street, p.city, p.state, " .
                                            "p.postal_code, p.phone_home, p.phone_biz, p.pid, p.pubpid, " .
                                            "count(e.date) AS ecount, max(e.date) AS edate, " .
                                            "i1.date AS idate1, i2.date AS idate2, " .
                                            "c1.name AS cname1, c2.name AS cname2 " .
                                            "FROM patient_data AS p ";
                                            if (!empty($from_date)) {
                                                $query .= "JOIN form_encounter AS e ON " .
                                                "e.pid = p.pid AND " .
                                                "e.date >= ? AND " .
                                                "e.date <= ? ";
                                                array_push($sqlArrayBind, $from_date .' 00:00:00', $to_date . ' 23:59:59');
                                                if ($form_provider) {
                                                    $query .= "AND e.provider_id = ? ";
                                                    array_push($sqlArrayBind, $form_provider);
                                                }
                                            } else {
                                                if ($form_provider) {
                                                    $query .= "JOIN form_encounter AS e ON " .
                                                    "e.pid = p.pid AND e.provider_id = ? ";
                                                    array_push($sqlArrayBind, $form_provider);
                                                } else {
                                                    $query .= "LEFT OUTER JOIN form_encounter AS e ON " .
                                                    "e.pid = p.pid ";
                                                }
                                            }

                                            $query .=
                                            "LEFT OUTER JOIN insurance_data AS i1 ON " .
                                            "i1.pid = p.pid AND i1.type = 'primary' " .
                                            "LEFT OUTER JOIN insurance_companies AS c1 ON " .
                                            "c1.id = i1.provider " .
                                            "LEFT OUTER JOIN insurance_data AS i2 ON " .
                                            "i2.pid = p.pid AND i2.type = 'secondary' " .
                                            "LEFT OUTER JOIN insurance_companies AS c2 ON " .
                                            "c2.id = i2.provider " .
                                            "GROUP BY p.lname, p.fname, p.mname, p.pid, i1.date, i2.date " .
                                            "ORDER BY p.lname, p.fname, p.mname, p.pid, i1.date DESC, i2.date DESC";
                                            $res = sqlStatement($query, $sqlArrayBind);

                                            $prevpid = 0;
                                            while ($row = sqlFetchArray($res)) {
                                                if ($row['pid'] == $prevpid) {
                                                    continue;
                                                }

                                                $prevpid = $row['pid'];
                                                $age = '';
                                                if ($row['DOB']) {
                                                    $dob = $row['DOB'];
                                                    $tdy = $row['edate'] ? $row['edate'] : date('Y-m-d');
                                                    $ageInMonths = (substr($tdy, 0, 4)*12) + substr($tdy, 5, 2) -
                                                        (substr($dob, 0, 4)*12) - substr($dob, 5, 2);
                                                    $dayDiff = substr($tdy, 8, 2) - substr($dob, 8, 2);
                                                    if ($dayDiff < 0) {
                                                        --$ageInMonths;
                                                    }

                                                    $age = intval($ageInMonths/12);
                                                }
                                                if ($_POST['form_csvexport']) {
                                                    echo '"' . oeFormatShortDate(substr($row['edate'], 0, 10)) . '",';
                                                    echo '"' . qescape($row['lname']) . '",';
                                                    echo '"' . qescape($row['fname']) . '",';
                                                    echo '"' . qescape($row['mname']) . '",';
                                                    echo '"' . qescape($row['pubpid']) . '",';
                                                    echo '"' . qescape(xl($row['street'])) . '",';
                                                    echo '"' . qescape(xl($row['city'])) . '",';
                                                    echo '"' . qescape(xl($row['state'])) . '",';
                                                    echo '"' . qescape($row['postal_code']) . '",';
                                                    echo '"' . qescape($row['phone_home']) . '",';
                                                    echo '"' . qescape($row['phone_biz']) . '"' . "\n";
                                                } else {
                                                ?>
                                                    <tr>
                                                        <td> <?php echo text(oeFormatShortDate(substr($row['edate'], 0, 10))); ?></td>
                                                        <td><?php echo text($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']); ?> </td>
                                                        <td><?php echo text($row['pubpid']); ?></td>
                                                        <td><?php echo xlt($row['street']); ?><br><?php echo xlt($row['city']); ?><?php if($row['state']){ echo ", ".$row['state'];} ?></td>
                                                        <td><?php echo text($row['phone_home']); ?></td>
                                                        <td><?php echo text($row['email']); ?></td>

                                                    </tr>
                                                    <?php
                                                } // end not export
                                                ++$totalpts;
                                            }
                                            if (!$_POST['form_csvexport']) {
                                                ?>
                                                  </tbody>
                                                </table>
                                            </div>
                                        <?php
                                            }
                                        }
                                        ?>
                                            

                                    <?php         
                                    if (!$_POST['form_csvexport']) {
                                    ?>

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



       
    
    
