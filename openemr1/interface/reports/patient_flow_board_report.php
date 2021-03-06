<?php
/**
 * Patient Flow Board (Patient Tracker) (Report Based on the appointment report)
 *
 * This program used to select and print the information captured in the Patient Flow Board program,
 * allowing the user to select and print the desired information.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("../../library/patient.inc");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/appointments.inc.php";
require_once("$srcdir/patient_tracker.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$patient = $_POST['patient'];

if ($patient && !isset($_POST['form_from_date'])) {
    // If a specific patient, default to 2 years ago.
    $tmp = date('Y') - 2;
    $from_date = date("$tmp-m-d");
    $to_date = date('Y-m-d');
} else {
    $from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
    $to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
}

# check box information
$chk_show_details = false;
if ($_POST['show_details']) {
    $chk_show_details = true;
}

$chk_show_drug_screens = false;
if ($_POST['show_drug_screens']) {
    $chk_show_drug_screens = true;
}

$chk_show_completed_drug_screens = false;
if ($_POST['show_completed_drug_screens']) {
    $chk_show_completed_drug_screens = true;
}

# end check box information

$provider  = $_POST['form_provider'];
$facility  = $_POST['form_facility'];  #(CHEMED) facility filter
$form_orderby = getComparisonOrder($_POST['form_orderby']) ?  $_POST['form_orderby'] : 'date';
if ($_POST["form_patient"]) {
    $form_patient = isset($_POST['form_patient']) ? $_POST['form_patient'] : '';
}

$form_pid = isset($_POST['form_pid']) ? $_POST['form_pid'] : '';
if ($form_patient == '') {
    $form_pid = '';
}
?>

<html>

<head>
    <title><?php echo xlt('Employee Flow Board Report'); ?></title>
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

        // CapMinds :: invokes  find-patient popup.
        function sel_patient() {
            dlgopen('../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
        }

        // CapMinds :: callback by the find-patient popup.
        function setpatient(pid, lname, fname, dob) {
            var f = document.theform;
            f.form_patient.value = lname + ', ' + fname;
            f.form_pid.value = pid;
        }
    </script>

    <style type="text/css">
   
   
        /* specifically include & exclude from printing */
        @media print {
            #report_parameter {
                    visibility: hidden;
                    display: none;
            }
            #report_parameters_daterange {
                    visibility: visible;
                    display: inline;
            }
            #report_result table {
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

        .table-div{
                height:auto!important;
                overflow:auto;
            }
    </style>
     <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>
     <style>
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
        .form-save{
                padding: 5px;
            font-family: 'Open Sans', sans-serif;
            font-size: 16px;
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
   
    </style>
</head>

<body class="body_top" style="font-family: 'Open Sans', sans-serif">

<!-- Required for the popup date selectors -->
<!-- <div id="overDiv"
    style="position: absolute; visibility: hidden; z-index: 1000;"></div>
<?php if ($GLOBALS['drug_screen']) { #setting the title of the page based o if drug screening is enabled ?>
<span class='title'><?php echo xlt('Patient Flow Board'); ?> - <?php echo xlt('Drug Screen Report'); ?></span>
<?php } else { ?>
<span class='title'><?php echo xlt('Patient Flow Board Report'); ?></span>
<?php } ?> -->


<div id="report_parameters_daterange"><?php echo text(oeFormatShortDate($from_date)) ." &nbsp; " . xlt('to') . " &nbsp; ". text(oeFormatShortDate($to_date)); #sets date range for calendars ?>
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
                                            <p class="text-white head-p">Employee Flow Board</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="body-compo" style="height:auto;">
                            <div class="container-fluid">
<form method='post' name='theform' id='theform' action='patient_flow_board_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<!-- <div id="report_parameters">

<table>
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

                # Build a drop-down list of providers.
                #

                $query = "SELECT id, lname, fname FROM users WHERE ".
                  "authorized = 1  ORDER BY lname, fname"; #(CHEMED) facility filter

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

                ?></td>

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
                <td><?php generate_form_field(array('data_type'=>1,'field_id'=>'apptstatus','list_id'=>'apptstat','empty_title'=>'All'), $_POST['form_apptstatus']);?></td>
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
            <td>
            &nbsp;&nbsp;<span class='text'><?php echo xlt('Patient'); ?>: </span>
            </td>
            <td>
            <input type='text' size='20' name='form_patient' class='form-control' style='cursor:pointer;cursor:hand' value='<?php echo ($form_patient) ? attr($form_patient) : xla('Click To Select'); ?>' onclick='sel_patient()' title='<?php echo xla('Click to select employee'); ?>' />
            <input type='hidden' name='form_pid' value='<?php echo attr($form_pid); ?>' />
            </td>

                <td colspan="2">
                    <div class="checkbox">
                        <label><input type="checkbox" name="show_details" id="show_details" <?php echo ($chk_show_details) ? "checked" : ""; ?>>&nbsp;<?php echo xlt('Show Details'); ?></label>
                    </div>
                </td>
            </tr>
            <tr>

            </tr>
            <?php if ($GLOBALS['drug_screen']) { ?>
            <tr>
                <?php # these two selects will are for the drug screen entries the Show Selected for Drug Screens will show all
                  # that have a yes for selected. If you just check the Show Status of Drug Screens all drug screens will be displayed
                  # if both are selected then only completed drug screens will be displayed. ?>
            <td colspan="2">
                <div class="checkbox">
                    <label><input type="checkbox" name="show_drug_screens" id="show_drug_screens" <?php echo ($chk_show_drug_screens) ? "checked" : ""; ?>>&nbsp;<?php echo xlt('Show Selected for Drug Screens'); ?></label>
                </div>
            </td>
            <td colspan="2">
                <div class="checkbox">
                    <label><input type="checkbox" name="show_completed_drug_screens" id="show_completed_drug_screens" <?php echo ($chk_show_completed_drug_screens) ? "checked" : ""; ?>>&nbsp;<?php echo xlt('Show Status of Drug Screens'); ?></label>
                </div>
            </td>
            </tr>
            <?php } ?>

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
                            <?php } ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>&nbsp;&nbsp;<?php echo xlt('Most column headers can be clicked to change sort order') ?></tr>
        </table>
        </td>
    </tr>
</table>

</div> -->
<!-- end of search parameters --> 

<div class="pt-4 pb-4">
                                <div id="report_parameter" class="row">
                                    <div class="col-md-1"></div>
                                    <div class="col-md-3">
                                        <p>Facility</p>
                                        <?php dropdown_facility($facility, 'form_facility'); ?>
                                    </div>
                                    <div class="col-md-2">
                                        <p>From</p>
                                       
                                        <input type='date' name='form_from_date' id="form_from_date"
                    class='datepicker form-control pr-1 pl-1'
                    size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
                                    </div>
                                    <div class="col-md-2">
                                        <p>To</p>
                                         
                                         <input type='text' name='form_to_date' id="form_to_date"
                    class='datepicker form-control pr-1 pl-1'
                    size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
                                        </div>
                                    <div class="col-md-3">
                                        <p>Provider</p>
                                        <?php                
                $query = "SELECT id, lname, fname FROM users WHERE ".
                  "authorized = 1  ORDER BY lname, fname"; #(CHEMED) facility filter

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
                                <div id="report_parameter" class="row">
                                    <div class="col-md-3"></div>
                                    <div class="col-md-3">
                                        <p>Employee</p>
                                        <input type='text' size='20' name='form_patient' class='form-control' style='cursor:pointer;cursor:hand' value='<?php echo ($form_patient) ? attr($form_patient) : xla('Click To Select'); ?>' onclick='sel_patient()' title='<?php echo xla('Click to select patient'); ?>' />
                                        <input type='hidden' name='form_pid' value='<?php echo attr($form_pid); ?>' />
                                    </div>
                                    <div class="col-md-3">
                                        <p>Category</p>
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
                                <div id="report_parameter" class="pt-4 pb-5">
                                    <div class="row">
                                        <div class="col-md-4"></div>
                                        <div class="col-md-2"> 
                                            <button class="form-save" onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>SEARCH</button>
                                        </div>
                                        <div class="col-md-2"> <button class="form-save" id='printbutton'>PRINT</button></div>

                                    </div>

                                </div>
<?php
if ($_POST['form_refresh'] || $_POST['form_orderby']) {
    ?>
<div id="report_result" class="table-div ">
<table class="table table-form">

    <thead>
    <?php if (!$chk_show_drug_screens && !$chk_show_completed_drug_screens) { # the first part of this block is for the Patient Flow Board report ?>
        <th>Provider
            <!-- <a href="nojs.php" onclick="return dosort('doctor')"
        <?php echo ($form_orderby == "doctor") ? " " : ""; ?>>
        <?php  echo xlt('Provider'); ?>
        </a> -->
    </th>

        <th>Date
            <!-- <a href="nojs.php" onclick="return dosort('date')"
        <?php echo ($form_orderby == "date") ? " " : ""; ?>><?php  echo xlt('Date'); ?></a> -->
        </th>

        <th>Time
            <!-- <a href="nojs.php" onclick="return dosort('time')"
        <?php echo ($form_orderby == "time") ? " " : ""; ?>><?php  echo xlt('Time'); ?></a> -->
        </th>

        <th>Employee
            <!-- <a href="nojs.php" onclick="return dosort('patient')"
        <?php echo ($form_orderby == "patient") ? " " : ""; ?>>&nbsp;&nbsp;&nbsp;<?php  echo xlt('Employee'); ?></a> -->
        </th>

        <th>ID
            <!-- <a href="nojs.php" onclick="return dosort('pubpid')"
        <?php echo ($form_orderby == "pubpid") ? " " : ""; ?>>&nbsp;<?php  echo xlt('ID'); ?></a> -->
        </th>

        <th>Type
            <!-- <a href="nojs.php" onclick="return dosort('type')"
        <?php echo ($form_orderby == "type") ? " " : ""; ?>><?php  echo xlt('Type'); ?></a> -->
        </th>

        <!-- <?php if ($chk_show_details) { ?>
        <th><a href="nojs.php" onclick="return dosort('trackerstatus')"
            <?php echo ($form_orderby == "trackerstatus") ? " " : ""; ?>><?php  echo xlt('Status'); ?></a>
        </th>
        <?php } else { ?>
        <th><a href="nojs.php" onclick="return dosort('trackerstatus')"
            <?php echo ($form_orderby == "trackerstatus") ? " " : ""; ?>><?php  echo xlt('Final Status'); ?></a>
        </th>
        <?php } ?> -->


        <th><?php
        if ($chk_show_details) { # not sure if Sorting by Arrive Time is useful
            echo xlt('Start Time');
        } else {
            echo xlt('Arrive Time');
        }?></th>

        <th><?php
        if ($chk_show_details) {   # not sure if Sorting by Discharge Time is useful
            echo xlt('End Time');
        } else {
            echo xlt('Discharge Time');
        }?></th>

        <th><?php echo xlt('Total Time'); # not adding Sorting by Total Time yet but can see that it might be useful ?></th>

    <?php } else { # this section is for the drug screen report ?>
        <th><a href="nojs.php" onclick="return dosort('doctor')"
        <?php echo ($form_orderby == "doctor") ? " " : ""; ?>><?php  echo xlt('Provider'); ?>
        </a></th>

        <th><a href="nojs.php" onclick="return dosort('date')"
        <?php echo ($form_orderby == "date") ? " " : ""; ?>><?php  echo xlt('Date'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('time')"
        <?php echo ($form_orderby == "time") ? " " : ""; ?>><?php  echo xlt('Time'); ?></a>
        </th>

        <th><a href="nojs.php" onclick="return dosort('patient')"
        <?php echo ($form_orderby == "patient") ? " " : ""; ?>>&nbsp;&nbsp;&nbsp;&nbsp;<?php  echo xlt('Employee'); ?></a>
        </th>

        <?php if (!$chk_show_completed_drug_screens) { ?>
        <th><a href="nojs.php" onclick="return dosort('pubpid')"
            <?php echo ($form_orderby == "pubpid") ? " " : ""; ?>>&nbsp;<?php  echo xlt('ID'); ?></a>
        </th>
        <?php } else { ?>
        <th><a href="nojs.php" onclick="return dosort('pubpid')"
            <?php echo ($form_orderby == "pubpid") ? " " : ""; ?>>&nbsp;<?php  echo xlt('ID'); ?></a>
        </th>
        <?php } ?>

        <th><?php echo xlt('Drug Screen'); # not sure if Sorting by Drug Screen is useful ?></th>

        <?php if (!$chk_show_completed_drug_screens) { ?>
         <th>&nbsp;</th>
        <?php } else { ?>
         <th><a href="nojs.php" onclick="return dosort('completed')"
            <?php echo ($form_orderby == "completed") ? " " : ""; ?>><?php  echo xlt('Completed'); ?></a>
         </th>
        <?php } ?>

     <th></th><th></th><th></th>

    <?php } ?>
    </thead>
    <tbody>
        <!-- added for better print-ability -->
    <?php

    $lastdocname = "";
    #Appointment Status Checking
    $form_apptstatus = $_POST['form_apptstatus'];
    $form_apptcat=null;
    if (isset($_POST['form_apptcat'])) {
        if ($form_apptcat!="ALL") {
            $form_apptcat=intval($_POST['form_apptcat']);
        }
    }

    #Without provider and facility data checking
    $with_out_provider = null;
    $with_out_facility = null;

    # get the appointments also set the trackerboard flag to true (last entry in the fetchAppointments call so we get the tracker stuff)
    $appointments = fetchAppointments($from_date, $to_date, $patient, $provider, $facility, $form_apptstatus, $with_out_provider, $with_out_facility, $form_apptcat, true);
    # sort the appointments by the appointment time
    $appointments = sortAppointments($appointments, $form_orderby);
    # $j is used to count the number of patients that match the selected criteria.
    $j=0;
    //print_r2($appointments);
    foreach ($appointments as $appointment) {
        $patient_id = $appointment['pid'];
        $tracker_id = $appointment['pt_tracker_id'];
        $last_seq = $appointment['lastseq'];
        $docname  = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];
        # only get items with a tracker id.
        if ($tracker_id == '') {
            continue;
        }

        # only get the drug screens that are set to yes.
        if ($chk_show_drug_screens ==1) {
            if ($appointment['random_drug_test'] != '1') {
                continue;
            }
        }

        #if a patient id is entered just get that patient.
        if (strlen($form_pid) !=0) {
            if ($appointment['pid'] != $form_pid) {
                continue;
            }
        }

        $errmsg  = "";
        $newarrive = '';
        $newend = '';
        $no_visit = 1;
        # getting arrive time and end time from the elements file.
        if ($tracker_id != 0) {
            $newarrive = collect_checkin($tracker_id);
            $newend = collect_checkout($tracker_id);
        }

        if ($newend != '' && $newarrive != '') {
            $no_visit = 0;
        }

        $tracker_status = $appointment['status'];
        # get the time interval for the entire visit. to display seconds add last option of true.
        # get_Tracker_Time_Interval($newarrive, $newend, true)
        $timecheck2 = get_Tracker_Time_Interval($newarrive, $newend);
        # Get the tracker elements.
        $tracker_elements = collect_Tracker_Elements($tracker_id);
        # $j is incremented for a patient that made it for display.
        $j=$j+1;
        ?>

    <tr >
        <?php
        if (!$chk_show_drug_screens && !$chk_show_completed_drug_screens) { # the first part of this block is for the Patient Flow Board report ?>
        <td>&nbsp;<?php echo ($docname == $lastdocname) ? "" : $docname ?>
        </td>

        <td ><?php echo text(oeFormatShortDate($appointment['pc_eventDate'])) ?>
        </td>

        <td ><?php echo text(oeFormatTime($appointment['pc_startTime'])) ?>
        </td>

        <td >&nbsp;<?php echo text($appointment['fname'] . " " . $appointment['lname']) ?>
        </td>

        <td >&nbsp;<?php echo text($appointment['pubpid']) ?>
        </td>

        <td >&nbsp;<?php echo text(xl_appt_category($appointment['pc_catname'])) ?>
        </td>

        <!-- <td class="detail">
            <?php
                //Appointment Status
            if ($chk_show_details) {
                if ($no_visit != 1) {
                    echo xlt('Complete Visit Time');
                }
            } else {
                if ($tracker_status != "") {
                    echo text(getListItemTitle('apptstat', $tracker_status));
                }
            }
            ?>
        </td> -->

        <td >&nbsp;<?php echo text(substr($newarrive, 11)) ?>
        </td>

        <td >&nbsp;<?php echo text(substr($newend, 11)) ?>
        </td>

            <?php if ($no_visit != 1) { ?>
        <td >&nbsp;<?php echo text($timecheck2) ?></td>
        <?php } else { ?>
        <td >&nbsp;</td>
        <?php } ?>
            <?php
            if ($chk_show_details) { # lets show the detail lines
                  $i = '0';
                  $k = '0';
                for ($x = 1; $x <= $last_seq; $x++) {
                    ?>
        <tr >
        <td >

                    <?php
                # get the verbiage for the status code
                    $track_stat = $tracker_elements[$i][status];
                # Get Interval alert time and status color.
                    $colorevents = (collectApptStatusSettings($track_stat));
                    $alert_time = '0';
                    $alert_color = $colorevents['color'];
                    $alert_time = $colorevents['time_alert'];
                    if (is_checkin($track_stat) || is_checkout($track_stat)) {  #bold the check in and check out times in this block.
                        ?>
            <td ><b>
                        <?php
                    } else { ?>
            <td >
                        <?php
                    }

                    echo text(getListItemTitle("apptstat", $track_stat));
                    ?>
            </b></td>
                    <?php
                    if (is_checkin($track_stat) || is_checkout($track_stat)) {  #bold the check in and check out times in this block.
                        ?>
             <td >&nbsp;<?php echo text(substr($tracker_elements[$i][start_datetime], 11)); ?></td>
                        <?php
                    } else { ?>
            <td >&nbsp;<?php echo text(substr($tracker_elements[$i][start_datetime], 11)); ?></td>
                        <?php # figure out the next time of the status
                    }

                    $k = $i+1;
                    if ($k < $last_seq) {
                    # get the start time of the next status to determine the total time in this status
                        $start_tracker_time = $tracker_elements[$i][start_datetime];
                        $next_tracker_time = $tracker_elements[$k][start_datetime];
                    } else {
                    # since this is the last status the start and end are equal
                        $start_tracker_time = $tracker_elements[$i][start_datetime];
                        $next_tracker_time = $tracker_elements[$i][start_datetime];
                    }

                    if (is_checkin($track_stat) || is_checkout($track_stat)) {  #bold the check in and check out times in this block. ?>
                <td ><b>&nbsp;<?php echo text(substr($next_tracker_time, 11)) ?></b></td><?php
                    } else { ?>
            <td >&nbsp;<?php echo text(substr($next_tracker_time, 11)) ?></td>
                        <?php # compute the total time of the status
                    }

                    $tracker_time = get_Tracker_Time_Interval($start_tracker_time, $next_tracker_time);
                # add code to alert if over time interval for status
                    $timecheck = round(abs(strtotime($start_tracker_time) -  strtotime($next_tracker_time)) / 60, 0);
                    if ($timecheck > $alert_time && ($alert_time != '0')) {
                        if (is_checkin($track_stat) || is_checkout($track_stat)) {  #bold the check in and check out times in this block. ?>
 <td >&nbsp;<?php echo text($tracker_time); ?></td><?php
                        } else { ?>
            <td >&nbsp;<?php echo text($tracker_time); ?></td><?php
                        }
                    } else {
                        if (is_checkin($track_stat) || is_checkout($track_stat)) { #bold the check in and check out times in this block. ?>
                    <td ><b>&nbsp;<?php echo text($tracker_time); ?></b></td><?php
                        } else { ?>
                    <td >&nbsp;<?php echo text($tracker_time); ?></td><?php
                        }
                    }

                    $i++;
                }
            }
            ?>
        </td>
        </tr>

            <?php
        } else { # this section is for the drug screen report ?>
        <td >&nbsp;<?php echo ($docname == $lastdocname) ? "" : text($docname); ?>
        </td>

        <td ><?php echo text(oeFormatShortDate($appointment['pc_eventDate'])) ?>
        </td>

        <td><?php echo text(oeFormatTime($appointment['pc_startTime'])) ?>
        </td>

        <td >&nbsp;<?php echo text($appointment['fname'] . " " . $appointment['lname']) ?>
        </td>

        <td >&nbsp;<?php echo text($appointment['pubpid']) ?></td>

        <td >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo ($appointment['random_drug_test'] == '1') ? xlt('Yes') : xlt('No'); ?></td>

            <?php if ($chk_show_completed_drug_screens) { ?>
          <td >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo ($appointment['drug_screen_completed'] == '1') ? xlt('Yes') : xlt('No'); ?></td>
        <?php } else { ?>
          <td >&nbsp; </td>
        <?php } ?>

            <?php # these last items are used to complete the screen ?>
        <td>&nbsp;</td>

        <td >&nbsp;</td>

        <td >&nbsp;</td>
        <?php } ?>
    </tr>

        <?php
        $lastdocname = $docname;
    } # end for
    ?>
    <!-- <tr>
        <?php if (!$chk_show_drug_screens && !$chk_show_completed_drug_screens) { # is it Patient Flow Board or Drug screen ?>
        <td colspan="10" align="left"><?php echo xlt('Total number of Patient Flow Board entries'); ?>&nbsp;<?php echo text($j);?>&nbsp;<?php echo xlt('Patients'); ?></td>
        <?php } else { ?>
        <td colspan="10" align="left"><?php echo xlt('Total number of Drug Screen entries'); ?>&nbsp;<?php echo text($j);?>&nbsp;<?php echo xlt('Patients'); ?></td>
        <?php } ?>
    </tr> -->
    </tbody>
</table>
</div>
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

</body>

</html>
