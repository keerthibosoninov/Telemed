<?php
/**
 * This report shows upcoming appointments with filtering and
 * sorting by patient, practitioner, appointment type, and date.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ron Pulcer <rspulcer_2k@yahoo.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2005-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ron Pulcer <rspulcer_2k@yahoo.com>
 * @copyright Copyright (c) 2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("../../library/patient.inc");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/appointments.inc.php";
require_once "$srcdir/clinical_rules.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

# Clear the pidList session whenever load this page.
# This session will hold array of patients that are listed in this
# report, which is then used by the 'Superbills' and 'Address Labels'
# features on this report.
unset($_SESSION['pidList']);
unset($_SESSION['apptdateList']);

$alertmsg = ''; // not used yet but maybe later
$patient = $_REQUEST['patient'];

if ($patient && !isset($_POST['form_from_date'])) {
    // If a specific patient, default to 2 years ago.
    $tmp = date('Y') - 2;
    $from_date = date("$tmp-m-d");
    $to_date = date('Y-m-d');
} else {
    $from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
    $to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
}

$show_available_times = false;
if ($_POST['form_show_available']) {
    $show_available_times = true;
}

$chk_with_out_provider = false;
if ($_POST['with_out_provider']) {
    $chk_with_out_provider = true;
}

$chk_with_out_facility = false;
if ($_POST['with_out_facility']) {
    $chk_with_out_facility = true;
}

$provider  = $_POST['form_provider'];
$facility  = $_POST['form_facility'];  //(CHEMED) facility filter
$form_orderby = getComparisonOrder($_REQUEST['form_orderby']) ?  $_REQUEST['form_orderby'] : 'date';

// Reminders related stuff
$incl_reminders = isset($_POST['incl_reminders']) ? 1 : 0;
function fetch_rule_txt($list_id, $option_id)
{
    $rs = sqlQuery(
        'SELECT title, seq from list_options WHERE list_id = ? AND option_id = ? AND activity = 1',
        array($list_id, $option_id)
    );
    $rs['title'] = xl_list_label($rs['title']);
    return $rs;
}
function fetch_reminders($pid, $appt_date)
{
    $rems = test_rules_clinic('', 'passive_alert', $appt_date, 'reminders-due', $pid);
    $seq_due = array();
    $seq_cat = array();
    $seq_act = array();
    foreach ($rems as $ix => $rem) {
        $rem_out = array();
        $rule_txt = fetch_rule_txt('rule_reminder_due_opt', $rem['due_status']);
        $seq_due[$ix] = $rule_txt['seq'];
        $rem_out['due_txt'] = $rule_txt['title'];
        $rule_txt = fetch_rule_txt('rule_action_category', $rem['category']);
        $seq_cat[$ix] = $rule_txt['seq'];
        $rem_out['cat_txt'] = $rule_txt['title'];
        $rule_txt = fetch_rule_txt('rule_action', $rem['item']);
        $seq_act[$ix] = $rule_txt['seq'];
        $rem_out['act_txt'] = $rule_txt['title'];
        $rems_out[$ix] = $rem_out;
    }

    array_multisort($seq_due, SORT_DESC, $seq_cat, SORT_ASC, $seq_act, SORT_ASC, $rems_out);
    $rems = array();
    foreach ($rems_out as $ix => $rem) {
        $rems[$rem['due_txt']] .= (isset($rems[$rem['due_txt']]) ? ', ':'').
            $rem['act_txt'].' '.$rem['cat_txt'];
    }

    return $rems;
}
?>

<html>

<head>
    <title><?php echo xlt('Appointments Report'); ?></title>
    
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/employee_dashboard_style.css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/emp_info_css.css">
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/vue.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/main.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/addmore.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/panzoom.min.js"></script>
    <?php //Header::setupHeader(["datetime-picker","report-helper"]); ?>

    <script type="text/javascript">
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

        function dosort(orderby) {
            var f = document.forms[0];
            f.form_orderby.value = orderby;
            f.submit();
            return false;
        }

        function oldEvt(eventid) {
            dlgopen('../main/calendar/add_edit_event.php?eid=' + encodeURIComponent(eventid), 'blank', 775, 500);
        }

        function refreshme() {
            // location.reload();
            document.forms[0].submit();
        }
        </script>

        <style type="text/css">


        /* css_PA */
        .form-save {
    background-color: #3C9DC5;
    padding: 4px;
    width: 100%;
    border: none;
    outline: none;
    color: white;
}
input[type=date]{
            margin-top:0px;
        }
        input[type=text]{
            margin-top:0px;
        }

.css_button:hover, button:hover, input[type=button]:hover, input[type=submit]:hover {
        background: #3C9DC5;
        text-decoration: none;
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
        </style>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<!-- <div id="overDiv"
    style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Appointments'); ?></span> -->

<div id="report_parameters_daterange"><?php echo text(oeFormatShortDate($from_date)) ." &nbsp; " . xlt('to') . " &nbsp; ". text(oeFormatShortDate($to_date)); ?>
</div>
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
                                            <p class="text-white head-p">Appointments</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="body-compo" style="height:auto;">
                            <div class="container-fluid">
<form method='post' name='theform' id='theform' action='appointments_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<!-- <div id="report_parameters"> -->
<div id="report_parameters" class="pt-4 pb-4">
                                <div class="row">
                                    <div class="col-md-1"></div>
                                    <div class="col-md-3">
                                        <p>Facility</p>
                                        <?php dropdown_facility($facility, 'form_facility'); ?>
                                    </div>
                                    <div class="col-md-2">
                                        <p>From</p>
                                        <!-- <input type="date" placeholder="" class="form-control pr-1 pl-1"> -->
                                        <input type='date' name='form_from_date' id="form_from_date" class='datepicker form-control pr-1 pl-1'
                                         size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
                                    </div>
                                    <div class="col-md-2">
                                        <p>To</p> 
                                        <!-- <input type="date" placeholder="" class="form-control pr-1 pl-1"> -->
                                        <input type='date' name='form_to_date' id="form_to_date" class='datepicker form-control pr-1 pl-1'
                                        size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
                                    </div>
                                    <div class="col-md-3">
                                        <p>Provider</p>
                                        <?php               
                                        $query = "SELECT id, lname, fname FROM users WHERE ".
                                                 "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; 
                                        $ures = sqlStatement($query);
                                        echo "   <select name='form_provider' class='form-control'>\n";
                                        echo "    <option value=''>-- " . xlt('All') . " --\n";

                                        while ($urow = sqlFetchArray($ures)) {
                                        $provid = $urow['id'];
                                        echo "    <option value='" . attr($provid) . "'";
                                        if ($provid == $_POST['form_provider']) {
                                        echo " selected";
                                        }
                                        echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                                        }
                                        echo "   </select>\n";
                                        ?>
                                    </div>
                                    </div>
                                    <div class="row">
                                    <div class="col-md-3"></div>
                                    <div class="col-md-3">
                                        <p>Status</p>
                                        <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                        <?php generate_form_field(array('data_type'=>1,'field_id'=>'apptstatus','list_id'=>'apptstat','empty_title'=>'All'), $_POST['form_apptstatus']);?>

                                    </div>
                                    <div class="col-md-3">
                                        <p>Category</p>
                                        <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                        <select id="form_apptcat" name="form_apptcat" class="form-control">
                                        <?php
                                            $categories=fetchAppointmentCategories();
                                            echo "<option value='ALL'>".xlt("All")."</option>";
                                        while ($cat=sqlFetchArray($categories)) {
                                            echo "<option value='".attr($cat['id'])."'";
                                            if ($cat['id']==$_POST['form_apptcat']) {
                                                echo " selected='true' ";
                                            }

                                            echo    ">".text(xl_appt_category($cat['category']))."</option>";
                                        }
                                        ?>
                                    </select>
                                    </div>

                                </div>

                                <div class="pt-4 pb-5">
                                    <div class="row">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-2">
                                             <button class="form-save" onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>SEARCH</button>
                                        </div>
                                        <?php //if ($_POST['form_refresh'] || $_POST['form_orderby']) { ?>
                                        <div class="col-md-2"> 
                                            <button class="form-save" id='printbutton'>PRINT</button>
                                        </div>
                                        <div class="col-md-2"> 
                                            <button class="form-save" onclick='window.open("../patient_file/printed_fee_sheet.php?fill=2", "_blank").opener = null'>SUPER BILLS</button>
                                        </div>
                                        <div class="col-md-2">
                                             <button class="form-save" onclick='window.open("../patient_file/addr_appt_label.php", "_blank").opener = null'>ADDRESS LABELS</button>
                                        </div>
                                        <?php //} ?>
                                    </div>

                                </div>

                               
                                </div>
<!--original table  -->
<!-- <table>
    <tr>
        <td width='650px'>
        <div style='float: left'>

        <table class='text'>
            <tr>
                <td class='control-label'><?php echo xlt('Facility'); ?>:</td>
                <td><?php dropdown_facility($facility, 'form_facility'); ?>
                </td>
                <td class='control-label'><?php echo xlt('Provider'); ?>:</td>
                <td><?php

               

                $query = "SELECT id, lname, fname FROM users WHERE ".
                  "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; 

                $ures = sqlStatement($query);

                echo "   <select name='form_provider' class='form-control'>\n";
                echo "    <option value=''>-- " . xlt('All') . " --\n";

                while ($urow = sqlFetchArray($ures)) {
                    $provid = $urow['id'];
                    echo "    <option value='" . attr($provid) . "'";
                    if ($provid == $_POST['form_provider']) {
                        echo " selected";
                    }

                    echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                }

                echo "   </select>\n";
                ?>
                </td>
            </tr>
            <tr>
                <td class='control-label'><?php echo xlt('From'); ?>:</td>
                <td><input type='text' name='form_from_date' id="form_from_date"
                    class='datepicker form-control'
                    size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
                </td>
                <td class='control-label'><?php echo xlt('To'); ?>:</td>
                <td><input type='text' name='form_to_date' id="form_to_date"
                    class='datepicker form-control'
                    size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
                </td>
            </tr>

            <tr>
                <td class='control-label'><?php echo xlt('Status'); # status code drop down creation ?>:</td>
                <td>
                    <?php generate_form_field(array('data_type'=>1,'field_id'=>'apptstatus','list_id'=>'apptstat','empty_title'=>'All'), $_POST['form_apptstatus']);?>
            </td>
                <td><?php echo xlt('Category') #category drop down creation ?>:</td>
                <td>
                                    <select id="form_apptcat" name="form_apptcat" class="form-control">
                                        <?php
                                            $categories=fetchAppointmentCategories();
                                            echo "<option value='ALL'>".xlt("All")."</option>";
                                        while ($cat=sqlFetchArray($categories)) {
                                            echo "<option value='".attr($cat['id'])."'";
                                            if ($cat['id']==$_POST['form_apptcat']) {
                                                echo " selected='true' ";
                                            }

                                            echo    ">".text(xl_appt_category($cat['category']))."</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <div class="checkbox">
                        <label><input type='checkbox' name='form_show_available'
                        <?php  echo ($show_available_times) ? ' checked' : ''; ?>> <?php echo xlt('Show Available Times'); # check this to show available times on the report ?>
                        </label>
                    </div>
                </td>
                <td></td>
                <td>
                    <div class="checkbox">
                        <label><input type="checkbox" name="incl_reminders" id="incl_reminders"
                        <?php echo ($incl_reminders ? ' checked':''); # This will include the reminder for the patients on the report ?>>
                        <?php echo xlt('Show Reminders'); ?>
                        </label>
                    </div>
                </td>

            <tr>
                <td></td>
                <?php # these two selects will show entries that do not have a facility or a provider ?>
                <td>
                    <div class="checkbox">
                        <label><input type="checkbox" name="with_out_provider" id="with_out_provider" <?php echo ($chk_with_out_provider) ? "checked" : ""; ?>><?php echo xlt('Without Provider'); ?>
                        </label>
                    </div>
                </td>
                <td></td>
                <td>
                    <div class="checkbox">
                        <label><input type="checkbox" name="with_out_facility" id="with_out_facility" <?php echo ($chk_with_out_facility) ? "checked" : ""; ?>>&nbsp;<?php echo xlt('Without Facility'); ?>
                        </label>
                    </div>
                </td>
            </tr>

        </table>

        </div>

        </td>
        <td align='left' valign='middle' height="100%">
        <table style='border-left: 1px solid; width: 100%; height: 100%'>
            <tr>
                <td>
                    <div class="text-center">
                        <div class="btn-group" role="group">
                            <a href='#' class='btn btn-default btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                                <?php echo xlt('Submit'); ?>
                            </a>
                            <?php if ($_POST['form_refresh'] || $_POST['form_orderby']) { ?>
                                <a href='#' class='btn btn-default btn-print' id='printbutton'>
                                    <?php echo xlt('Print'); ?>
                                </a>
                                <a href='#' class='btn btn-default btn-transmit' onclick='window.open("../patient_file/printed_fee_sheet.php?fill=2", "_blank").opener = null' onsubmit='return top.restoreSession()'>
                                    <?php echo xlt('Superbills'); ?>
                                </a>
                                <a href='#' class='btn btn-default btn-transmit' onclick='window.open("../patient_file/addr_appt_label.php", "_blank").opener = null' onsubmit='return top.restoreSession()'>
                                    <?php echo xlt('Address Labels'); ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </td>
            </tr>
                        <tr>&nbsp;&nbsp;<?php echo xlt('Most column headers can be clicked to change sort order') ?></tr>
        </table>
        </td>
    </tr>
</table> -->
<!-- //original table -->

</div>
<!-- end of search parameters --> <?php
if ($_POST['form_refresh'] || $_POST['form_orderby']) {
    $showDate = ($from_date != $to_date) || (!$to_date);
    ?>

<div id="report_results" class="table-div ">
    <table class="table table-form">

    <thead>
        <tr>
        <th><a href="nojs.php" onclick="return dosort('doctor')"
    <?php echo ($form_orderby == "doctor") ? " " : ""; ?>><?php echo xlt('Provider'); ?>
        </a></th>

        <th <?php echo $showDate ? '' : 'style="display:none;"' ?>><a href="nojs.php" onclick="return dosort('date')"
    <?php echo ($form_orderby == "date") ? " " : ""; ?>><?php echo xlt('Date'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('time')"
    <?php echo ($form_orderby == "time") ? " " : ""; ?>><?php echo xlt('Time'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('patient')"
    <?php echo ($form_orderby == "patient") ? "" : ""; ?>><?php echo xlt('Employee'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('pubpid')"
    <?php echo ($form_orderby == "pubpid") ? " " : ""; ?>><?php echo xlt('ID'); ?></a>
        </th>

            <!-- <th><?php echo xlt('Home'); //Sorting by phone# not really useful ?></th> -->

                <th><?php echo xlt('Contact'); //Sorting by phone# not really useful ?></th>

        <th><a href="nojs.php" onclick="return dosort('type')"
    <?php echo ($form_orderby == "type") ? " " : ""; ?>><?php echo xlt('Type'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('status')"
            <?php echo ($form_orderby == "status") ? " " : ""; ?>><?php  echo xlt('Status'); ?></a>
        </th>
</tr>
    </thead>
    <tbody>
        <!-- added for better print-ability -->
    <?php

    $lastdocname = "";
    //Appointment Status Checking
        $form_apptstatus = $_POST['form_apptstatus'];
        $form_apptcat=null;
    if (isset($_POST['form_apptcat'])) {
        if ($form_apptcat!="ALL") {
            $form_apptcat=intval($_POST['form_apptcat']);
        }
    }

    //Without provider and facility data checking
    $with_out_provider = null;
    $with_out_facility = null;

    if (isset($_POST['with_out_provider'])) {
        $with_out_provider = $_POST['with_out_provider'];
    }

    if (isset($_POST['with_out_facility'])) {
        $with_out_facility = $_POST['with_out_facility'];
    }

    $appointments = fetchAppointments($from_date, $to_date, $patient, $provider, $facility, $form_apptstatus, $with_out_provider, $with_out_facility, $form_apptcat);

    if ($show_available_times) {
        $availableSlots = getAvailableSlots($from_date, $to_date, $provider, $facility);
        $appointments = array_merge($appointments, $availableSlots);
    }

    $appointments = sortAppointments($appointments, $form_orderby);
    $pid_list = array();  // Initialize list of PIDs for Superbill option
    $apptdate_list = array(); // same as above for the appt details
    $totalAppontments = count($appointments);

    foreach ($appointments as $appointment) {
        array_push($pid_list, $appointment['pid']);
        array_push($apptdate_list, $appointment['pc_eventDate']);
        $patient_id = $appointment['pid'];
        $docname  = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];

        $errmsg  = "";
        $pc_apptstatus = $appointment['pc_apptstatus'];

        ?>

        <tr id='p1.<?php echo attr($patient_id) ?>' >
        <td >&nbsp;<?php echo ($docname == $lastdocname) ? "" : text($docname) ?>
        </td>

        <td  <?php echo $showDate ? '' : 'style="display:none;"' ?>><?php echo text(oeFormatShortDate($appointment['pc_eventDate'])) ?>
        </td>

        <td ><?php echo text(oeFormatTime($appointment['pc_startTime'])) ?>
        </td>

        <td >&nbsp;<?php echo text($appointment['fname'] . " " . $appointment['lname']) ?>
        </td>

        <td >&nbsp;<?php echo text($appointment['pubpid']) ?></td>

        <!-- <td class="detail">&nbsp;<?php echo text($appointment['phone_home']) ?></td> -->

        <td >&nbsp;<?php echo text($appointment['phone_cell']) ?></td>

        <td >&nbsp;<?php echo text(xl_appt_category($appointment['pc_catname'])) ?></td>

        <td >&nbsp;
            <?php
                //Appointment Status
            if ($pc_apptstatus != "") {
                echo text(getListItemTitle('apptstat', $pc_apptstatus));
            }
            ?>
        </td>
    </tr>

        <?php
        if ($patient_id && $incl_reminders) {
            // collect reminders first, so can skip it if empty
            $rems = fetch_reminders($patient_id, $appointment['pc_eventDate']);
        }
        ?>
        <?php
        if ($patient_id && (!empty($rems) || !empty($appointment['pc_hometext']))) { // Not display of available slot or not showing reminders and comments empty ?>
    <tr valign='top' id='p2.<?php echo attr($patient_id) ?>' >
       <td colspan=<?php echo $showDate ? '"3"' : '"2"' ?> class="detail" />
       <td colspan=<?php echo ($incl_reminders ? "3":"6") ?> class="detail" align='left'>
            <?php
            if (trim($appointment['pc_hometext'])) {
                echo '<b>'.xlt('Comments') .'</b>: '.text($appointment['pc_hometext']);
            }

            if ($incl_reminders) {
                echo "<td class='detail' colspan='3' align='left'>";
                $new_line = '';
                foreach ($rems as $rem_due => $rem_items) {
                    echo "$new_line<b>$rem_due</b>: ".attr($rem_items);
                    $new_line = '<br>';
                }

                echo "</td>";
            }
            ?>
        </td>
    </tr>
            <?php
        } // End of row 2 display

        $lastdocname = $docname;
    }

    // assign the session key with the $pid_list array - note array might be empty -- handle on the printed_fee_sheet.php page.
        $_SESSION['pidList'] = $pid_list;
        $_SESSION['apptdateList'] = $apptdate_list;
    ?>
    <!-- <tr>
        <td colspan="10" align="left"><?php echo xlt('Total number of appointments'); ?>:&nbsp;<?php echo text($totalAppontments);?></td>
    </tr> -->
    </tbody>
</table>
</div>
<!-- end of search results -->
<?php } else { ?>
<!-- <div class='text'><?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div> -->
<?php } ?> <input type="hidden" name="form_orderby"
    value="<?php echo attr($form_orderby) ?>" /> <input type="hidden"
    name="patient" value="<?php echo attr($patient) ?>" /> <input type='hidden'
    name='form_refresh' id='form_refresh' value='' />
</form>

</div>
</div>

</window-dashboard>
</div>
</div>
</section>

<script type="text/javascript">

<?php
if ($alertmsg) {
    echo " alert(" . js_escape($alertmsg) . ");\n";
}
?>

</script>

</body>

</html>
