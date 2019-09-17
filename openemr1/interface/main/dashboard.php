<?php
/**
 * Message and Reminder Center UI
 *
 * @Package OpenEMR
 * @link http://www.open-emr.org
 * @author OpenEMR Support LLC
 * @author Roberto Vasquez robertogagliotta@gmail.com
 * @author Rod Roark rod@sunsetsystems.com
 * @author Brady Miller brady.g.miller@gmail.com
 * @author Ray Magauran magauran@medfetch.com
 * @copyright Copyright (c) 2010 OpenEMR Support LLC
 * @copyright Copyright (c) 2017 MedEXBank.com
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/gprelations.inc.php");
require_once "$srcdir/user.inc";
require_once("$srcdir/MedEx/API.php");

require_once "$srcdir/appointments.inc.php";


use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

//validation library
$use_validate_js = 1;
require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php");
//Gets validation rules from Page Validation list.
$collectthis = collectValidationPageRules("/interface/main/messages/messages.php");
if (empty($collectthis)) {
    $collectthis = "{}";
} else {
    $collectthis = json_sanitize($collectthis[array_keys($collectthis)[0]]["rules"]);
}

$MedEx = new MedExApi\MedEx('MedExBank.com');

if ($GLOBALS['medex_enable'] == '1') {
    if ($_REQUEST['SMS_bot']) {
        $result = $MedEx->login('1');
        $MedEx->display->SMS_bot($result);
        exit();
    }
    $logged_in = $MedEx->login();
}

$setting_bootstrap_submenu = prevSetting('', 'setting_bootstrap_submenu', 'setting_bootstrap_submenu', ' ');
//use $uspfx as the first variable for page/script specific user settings instead of '' (which is like a global but you have to request it).
$uspfx = substr(__FILE__, strlen($webserver_root)) . '.';
$rcb_selectors = prevSetting($uspfx, 'rcb_selectors', 'rcb_selectors', 'block');
$rcb_facility = prevSetting($uspfx, 'form_facility', 'form_facility', '');
$rcb_provider = prevSetting($uspfx, 'form_provider', 'form_provider', $_SESSION['authUserID']);

if (($_POST['setting_bootstrap_submenu']) ||
    ($_POST['rcb_selectors'])) {
    // These are not form elements. We only ever change them via ajax, so exit now.
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="<?php echo $webroot; ?>/interface/main/messages/css/reminder_style.css?v=<?php echo $v_js_includes; ?>" type="text/css">
    <link rel="stylesheet"  href="<?php echo $GLOBALS['web_root']; ?>/library/css/bootstrap_navbar.css?v=<?php echo $v_js_includes; ?>" type="text/css">

    <?php Header::setupHeader(['datetime-picker', 'jquery-ui', 'jquery-ui-redmond', 'opener', 'moment']); ?>
    <script>
        var xljs1 = '<?php echo xla('Preferences updated successfully'); ?>';
        var format_date_moment_js = '<?php echo attr(DateFormatRead("validateJS")); ?>';
        <?php require_once "$srcdir/restoreSession.php"; ?>
    </script>

    <script type="text/javascript" src="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/js/reminder_appts.js?v=<?php echo $v_js_includes; ?>"></script>

    <link rel="shortcut icon" href="<?php echo $webroot; ?>/sites/default/favicon.ico" />

        <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="MedEx Bank">
    <meta name="author" content="OpenEMR: MedExBank">
    <meta name="viewport" content="width=device-width, initial-scale=1">


       <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/style.css">

    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/vue.js"></script>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/main.js"></script>
    <style>
        @media only screen and (max-width: 768px) {
            [class*="col-"] {
                width: 100%;
                text-align: left!important;
            }
            .navbar-toggle>span.icon-bar {
                background-color: #68171A ! important;
            }
            .navbar-default .navbar-toggle {
                border-color: #4a4a4a;
            }
            .navbar-default .navbar-toggle:focus, .navbar-default .navbar-toggle:hover {
                background-color: #f2f2f2 !important;
                font-weight: 900 !important;
                color: #000000 !important;
            }
            .navbar-color {
                background-color: #E5E5E5;
            }
            .icon-bar {
                background-color: #68171A;
            }
            .navbar-header {
                float: none;
            }
            .navbar-toggle {
                display: block;
                background-color: #f2f2f2;
            }
            .navbar-nav {
                float: none!important;
            }
            .navbar-nav>li {
                float: none;
            }
            .navbar-collapse.collapse.in {
                z-index: 100;
                background-color: #dfdfdf;
                font-weight: 700;
                color: #000000 !important;
            }

        }

        </style>

<?php
if (($GLOBALS['medex_enable'] == '1') && (empty($_REQUEST['nomenu'])) && ($GLOBALS['disable_rcb'] != '1')) {
    $MedEx->display->navigation($logged_in);
    echo "<br />";
}

if (!empty($_REQUEST['go'])) { ?>
    <?php
    if (($_REQUEST['go'] == "setup") && (!$logged_in)) {
        echo "<title>" . xlt('MedEx Setup') . "</title></head><body class='body_top'>";
        $stage = $_REQUEST['stage'];
        if (!is_numeric($stage)) {
            echo "<br /><span class='title'>$stage " . xlt('Warning') . ": " . xlt('This is not a valid request') . ".</span>";
        } else {
            $MedEx->setup->MedExBank($stage);
        }
    } elseif ($_REQUEST['go'] == "addRecall") {
        echo "<title>" . xlt('New Recall') . "</title></head><body class='body_top'>";
        $MedEx->display->display_add_recall();
    } elseif ($_REQUEST['go'] == 'Recalls') {
        echo "<title>" . xlt('Recall Board') . "</title></head><body class='body_top'>";
        $MedEx->display->display_recalls($logged_in);
    } elseif ((($_REQUEST['go'] == "setup") || ($_REQUEST['go'] == 'Preferences')) && ($logged_in)) {
        echo "<title>MedEx" . xlt('Preferences') . "</title></head><body class='body_top'>";
        $MedEx->display->preferences();
    } elseif ($_REQUEST['go'] == 'icons') {
        echo "<title>MedEx" . xlt('Icons') . "</title></head><body class='body_top'>";
        $MedEx->display->icon_template();
    } elseif ($_REQUEST['go'] == 'SMS_bot') {
        echo "<title>MedEx" . xlt('SMS') . "</title></head><body class='body_top'>";
        $MedEx->display->SMS_bot($logged_in);
        exit;
    } else {
        echo "<title>" . xlt('MedEx Setup') . "</title></head><body class='body_top'>";
        echo xlt('Warning: Navigation error. Please refresh this page.');
    }
} else {
    //original message.php stuff
    
    if ($GLOBALS['enable_help'] == 1) {
        $help_icon = '<a class="pull-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#676666" title="' . xla("Click to view Help") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    } elseif ($GLOBALS['enable_help'] == 2) {
        $help_icon = '<a class="pull-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#DCD6D0 !Important" title="' . xla("To enable help - Go to  Administration > Globals > Features > Enable Help Modal") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    } elseif ($GLOBALS['enable_help'] == 0) {
        $help_icon = '';
    }
    $heading_caption = xlt('Messages') . ', ' . xlt('Reminders');
    if ($GLOBALS['disable_rcb'] != '1') {
        $heading_caption .= ', ' . xlt('Recalls');
    }
    
    $arrOeUiSettings = array(
        'heading_title' => $heading_caption,
        'include_patient_name' => false,// use only in appropriate pages
        'expandable' => false,
        'expandable_files' => array(""),//all file names need suffix _xpd
        'action' => "",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "",//only for actions - reset, link or back
        'show_help_icon' => true,
        'help_file_name' => "message_center_help.php"
    );
    $oemr_ui = new OemrUI($arrOeUiSettings);

    echo "<title>" . xlt('Message Center') . "</title>
    </head>
    <body class='body_top'>";
    ?>
    <section>
            <div class="body-content">
                <div>
                    <div class="drag-sec row">
                        
                        <div class="col-md-6 droptarget" id="drag-a">
                            <window-dashboard title="Availability">
                           
                                <div class="head-component">
                                <div class="container-fluid">
                                    <div class="row">
                                    <div class="col-6">
                                          <div class="compo-head">
                                        <span class="spl-mouse">
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/drag.svg" alt="">
                                            </span>
                                        <span >
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/min.svg" alt="">
                                            </span>
                                        <span onclick="SimpleSearch(this);" id="Appointments" >
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/search-white.svg" alt="">
                                          
                                        </span>
                                        <input id="txtAppointments" type="text" class="component-search">
                                            </div>
                                        </div>
                                    <div class="col-6">
                                        <p class="text-white head-p">Availability</p>
                                        </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="body-compo">
                                <div class="container-fluid">
                                    <!-- <div class="wrapper">
                                        <label for="datepickertwo">
                                                            <input type="text" id="datepickertwo" placeholder="Choose date" autocomplete="off">
                                                        </label>
                                    </div>
                                    <div class="row task-box">
                                        <div class="col-3 left-date text-white text-center">
                                            <h5>3:00 PM</h5>
                                            <h5>08</h5>
                                            <h6>Thurs</h6>
                                        </div>
                                        <div class="col-7 task-para">
                                            <div class="">
                                                <p>Meeting with Mr. Scott Summer X-Ray, ECG verification</p>
                                            </div>
                                        </div>
                                        <div class="col-2 task-para">
                                            <div class="d-block pt-4">
                                                <a href=""><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/seen.svg" alt=""></a>
                                                <p>view</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row task-box">
                                        <div class="col-3 left-date text-white text-center">
                                            <h5>4:00 PM</h5>
                                            <h5>08</h5>
                                            <h6>Thurs</h6>
                                        </div>
                                        <div class="col-7 task-para">
                                            <div class="">
                                                <p>Meeting with Dr. Watson Case meeting regarding - Don’s case</p>
                                            </div>
                                        </div>
                                        <div class="col-2 task-para">
                                            <div class="d-block pt-4">
                                                <a href=""><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/seen.svg" alt=""></a>
                                                <p>view</p>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                                    </div>
                            </window-dashboard>

                           
                           
                        </div>
                        <div class="col-md-6 droptarget" id="drag-b">
                            <window-dashboard title="Appointments">
                                <div class="head-component">
                                <div class="container-fluid">
                                    <div class="row">
                                    <div class="col-6">
                                        <div class="compo-head">
                                        <span style="display:none;" class="spl-mouse">
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/drag.svg" alt="">
                                            </span>
                                        <span style="display:none;">
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/min.svg" alt="">
                                            </span>
                                        <span onclick="SimpleSearch(this);" id="Appointments" style="display:none;">
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/search-white.svg" alt="">
                                          
                                        </span>
                                        <input id="txtAppointments" type="text" class="component-search">
                                            </div>
                                        </div>
                                    <div class="col-6">
                                        <p class="text-white head-p">Appointments</p>
                                        </div>
                                        </div>
                                    </div>
                                </div>

                                 <div class="body-compo">
                                <div class="container-fluid">
                                    <div class="wrapper">
                                        <label for="datepicker">
                                                        <input type="text" id="datepicker" placeholder="Choose date" autocomplete="off">
                                                    </label>
                                    </div>
                                    <?php

                                    $from_date=$to_date=date('Y-m-d');

                                     //  form_apptstatus > for None
                                     $form_apptstatus='-';
                                     $appointments = fetchAppointments($from_date, $to_date, $patient, $provider, $facility, $form_apptstatus, $with_out_provider, $with_out_facility, $form_apptcat);

                                     if ($show_available_times) {
                                         $availableSlots = getAvailableSlots($from_date, $to_date, $provider, $facility);
                                         $appointments = array_merge($appointments, $availableSlots);
                                     }

                                    //  print_r( $appointments);

                                   
                                 
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

                                        // print_r($appointment);
                                
                                        ?>
                                    <div class="row task-box">
                                        <div class="col-3 left-date text-white text-center">

                                        <?php
                                        $starttime=$appointment['pc_startTime'];
                                        $startdate=$appointment['pc_eventDate'];
                                        ?>
                                            <h5><?php echo date('G:i',strtotime($starttime)); ?>&nbsp;<?php echo text(date('a', $starttime)); ?></h5>
                                            <h5><?php echo date('m',strtotime($starttime)) ?></h5>
                                            <h6><?php echo date('D',strtotime($starttime)) ?></h6>
                                        </div>
                                        <div class="col-7 task-para">
                                            <div class="">
                                                <p>Meeting with <?php echo text($appointment['fname'] . " " . $appointment['lname']);?></p>
                                            </div>
                                        </div>
                                        <div class="col-2 task-para">
                                            <div class="task-icon">
                                                <a href=""><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/ico-users.svg" alt=""></a>
                                                <a href=""><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/ico-nouser.svg" alt=""></a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php
                                    }
                                    ?>

                                </div>

                                     </div>

                            </window-dashboard>

                        </div>
                        <div class="col-md-6 droptarget" id="drag-c">
                            <window-dashboard title="Visits history">
                                <div class="head-component">
                                <div class="container-fluid">
                                    <div class="row">
                                    <div class="col-6">
                                          <div class="compo-head">
                                        <span style="display:none;" class="spl-mouse">
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/drag.svg" alt="">
                                            </span>
                                        <span style="display:none;">
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/min.svg" alt="">
                                            </span>
                                        <span onclick="SimpleSearch(this);" id="Appointments" >
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/search-white.svg" alt="">
                                          
                                        </span>
                                        <input id="txtAppointments" type="text" class="component-search">
                                            </div>
                                        </div>
                                    <div class="col-6">
                                        <p class="text-white head-p">Visits history</p>
                                        </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="body-compo">
                                <div class="container-fluid">
                                    <div class="wrapper">
                                        <label for="datepickertwo">
                                                            <input type="text" id="datepickertwo" placeholder="Choose date" autocomplete="off">
                                                        </label>
                                    </div>

                                    <?php

                                        $from_date=$to_date=date('Y-m-d');
                                        //  form_apptstatus > for checked out
                                        $form_apptstatus='>';
                                        $appointments = fetchAppointments($from_date, $to_date, $patient, $provider, $facility, $form_apptstatus, $with_out_provider, $with_out_facility, $form_apptcat);

                                        if ($show_available_times) {
                                            $availableSlots = getAvailableSlots($from_date, $to_date, $provider, $facility);
                                            $appointments = array_merge($appointments, $availableSlots);
                                        }

                                        //  print_r( $appointments);



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

                                            // print_r($appointment);

                                            ?>
                                    <div class="row task-box">
                                        
                                        <div class="col-3 left-date text-white text-center">
                                        <?php
                                        $starttime=$appointment['pc_startTime'];
                                        $startdate=$appointment['pc_eventDate'];
                                        ?>
                                            <h5><?php echo date('G:i',strtotime($starttime)); ?>&nbsp;<?php echo text(date('a', $starttime)); ?></h5>
                                            <h5><?php echo date('m',strtotime($starttime)) ?></h5>
                                            <h6><?php echo date('D',strtotime($starttime)) ?></h6>
                                        </div>
                                        <div class="col-7 task-para">
                                            <div class="">
                                                <p>Meeting with <?php echo text($appointment['fname'] . " " . $appointment['lname']);?></p>
                                            </div>
                                        </div>
                                        <div class="col-2 task-para">
                                            <div class="d-block pt-4">
                                                <a href=""><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/seen.svg" alt=""></a>
                                                <p>view</p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                        }
                                        ?>
                                  
                                </div>
                                    </div>
                            </window-dashboard>


                        </div>
                        <div class="col-md-6 droptarget" id="drag-d">

                        <window-dashboard title="Tasks">
                                <div class="head-component">
                                <div class="container-fluid">
                                    <div class="row">
                                    <div class="col-6">
                                          <div class="compo-head">
                                        <span style="display:none;" class="spl-mouse">
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/drag.svg" alt="">
                                            </span>
                                        <span style="display:none;">
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/min.svg" alt="">
                                            </span>
                                        <span onclick="SimpleSearch(this);" id="Appointments" >
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/search-white.svg" alt="">
                                          
                                        </span>
                                        <input id="txtAppointments" type="text" class="component-search">
                                            </div>
                                        </div>
                                    <div class="col-6">
                                        <p class="text-white head-p">Tasks</p>
                                        </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="body-compo">
                                <div class="container-fluid">
                                    <div class="wrapper">
                                        <label for="datepickertwo">
                                                            <input type="text" id="datepickertwo" placeholder="Choose date" autocomplete="off">
                                                        </label>
                                    </div>
                                    <?php

                                    $active = 'all';
                                    $show_all == 'yes';
                                     $result = getPnotesByUser($active, $show_all, $_SESSION['authUser'], false, $sortby, $sortorder, $begin, $listnumber);
                                     while ($myrow = sqlFetchArray($result)) {
                                         $name = $myrow['user'];
                                         $name = $myrow['users_lname'];
                                         if ($myrow['users_fname']) {
                                             $name .= ", " . $myrow['users_fname'];
                                         }
                                         $patient = $myrow['pid'];
                                         if ($patient > 0) {
                                             $patient = $myrow['patient_data_lname'];
                                             if ($myrow['patient_data_fname']) {
                                                 $patient .= ", " . $myrow['patient_data_fname'];
                                             }
                                         } else {
                                             $patient = "* " . xl('Patient must be set manually') . " *";
                                         }
                                         print_r($row);
                                        }

                                        echo 1;

                                    ?>
                                    <div class="row task-box">
                                        <div class="col-3 left-date text-white text-center">
                                            <h5>3:00 PM</h5>
                                            <h5>08</h5>
                                            <h6>Thurs</h6>
                                        </div>
                                        <div class="col-7 task-para">
                                            <div class="">
                                                <p>Meeting with Mr. Scott Summer X-Ray, ECG verification</p>
                                            </div>
                                        </div>
                                        <div class="col-2 task-para">
                                            <div class="d-block pt-4">
                                                <a href=""><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/seen.svg" alt=""></a>
                                                <p>view</p>
                                            </div>
                                        </div>
                                    </div>
                                   
                                </div>
                                    </div>
                            </window-dashboard>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    <!--<div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?>">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-header clearfix">
                    <?php echo  $oemr_ui->pageHeading() . "\r\n"; ?>
                </div>
            </div>
        </div>
        <div class="row" >
            <div class="col-sm-12">
                VIVEK
            </div>
        </div>
       
    </div>--><!--end of container div-->
    <?php $oemr_ui->oeBelowContainerDiv();?>
    <?php
    //home of the help modal ;)
    //$GLOBALS['enable_help'] = 0; // Please comment out line if you want help modal to function on this page
    if ($GLOBALS['enable_help'] == 1) {
        echo "<script>var helpFile = 'message_center_help.php'</script>";
        //help_modal.php lives in interface, set path accordingly
        require "../help_modal.php";
    }
    ?>
    <script language="javascript">

        var collectvalidation = <?php echo $collectthis; ?>;

        $(function (){
            $("#reminders-div").hide();
            $("#recalls-div").hide();
            $("#sms-div").hide();
            $("#messages-li").click(function(){
                $("#messages-div").show(250);
                $("#reminders-div").hide(250);
                $("#recalls-div").hide(250);
                $("#sms-div").hide(250);
                $("#li-mess").addClass("active");
                $("#li-remi").removeClass("active");
                $("#li-reca").removeClass("active");
                $("#li-sms").removeClass("active");

            });
            $("#reminders-li").click(function(){
                $("#messages-div").hide(250);
                $("#reminders-div").show(250);
                $("#recalls-div").hide(250);
                $("#sms-div").hide(250);
                $("#li-remi").addClass("active");
                $("#li-mess").removeClass("active");
                $("#li-reca").removeClass("active");
                $("#li-sms").removeClass("active");
            });
            $("#recalls-li").click(function(){
                $("#messages-div").hide(250);
                $("#reminders-div").hide(250);
                $("#recalls-div").show(250);
                $("#sms-div").hide(250);
                $("#li-remi").removeClass("active");
                $("#li-mess").removeClass("active");
                $("#li-reca").addClass("active");
                $("#li-sms").removeClass("active");
            });
            $("#sms-li").click(function(){
                $("#messages-div").hide(250);
                $("#reminders-div").hide(250);
                $("#recalls-div").hide(250);
                $("#sms-div").show(250);
                $("#li-remi").removeClass("active");
                $("#li-mess").removeClass("active");
                $("#li-reca").removeClass("active");
                $("#li-sms").addClass("active");
            });

           

        });
        $(function (){
            $( "ul.navbar-nav" ).children().click(function(){
                $(".collapse").collapse('hide');
            });
        });
        $(function (){
            //for jquery tooltip to function if jquery 1.12.1.js is called via jquery-ui in the Header::setupHeader
            // the relevant css file needs to be called i.e. jquery-ui-darkness
            $('#see-all-tooltip').attr( "title", "<?php echo xla('Click to show messages for all users'); ?>" );
            $('#see-all-tooltip').tooltip();
            $('#just-mine-tooltip').attr( "title", "<?php echo xla('Click to show messages for only the current user'); ?>" );
            $('#just-mine-tooltip').tooltip();
        });
        $(function () {
            var f = $("#smsForm");
            $("#SMS_patient").autocomplete({
                source: "save.php?go=sms_search",
                minLength: 2,
                select: function (event, ui) {
                    event.preventDefault();
                    $("#SMS_patient").val(ui.item.label + ' ' + ui.item.mobile);
                    $("#sms_pid").val(ui.item.pid);
                    $("#sms_mobile").val(ui.item.mobile);
                    $("#sms_allow").val(ui.item.allow);
                }
            });
        });
        jQuery.ui.autocomplete.prototype._resizeMenu = function () {
            var ul = this.menu.element;
            ul.outerWidth(this.element.outerWidth());
        };
        $(function () {
            $("#newnote").click(function (event) {
                NewNote(event);
            });
            $("#printnote").click(function () {
                PrintNote();
            });
            var obj = $("#form_message_status");
            obj.onchange = function () {
                SaveNote();
            };
            $("#cancel").click(function () {
                CancelNote();
            });
            $("#note").focus();

            //clear button in messages
            $("#clear_user").click(function(){
                $("#assigned_to_text").val("<?php echo xls('Select Users From The Dropdown List'); ?>");
                $("#assigned_to").val("");
                $("#users").val("--");
            });

            //clear inputs of patients
            $("#clear_patients").click(function(){
                $("#reply_to").val("");
                $("#form_patient").val("");
            });
        });

        var NewNote = function (event) {
            top.restoreSession();
            if(document.getElementById("form_message_status").value !== 'Done'){
                collectvalidation.assigned_to = {
                    presence: {message: "<?php echo xls('Recipient required unless status is Done'); ?>"}
                }
            }
            else{
                delete collectvalidation.assigned_to;
            }

            $('#newnote').attr('disabled', true);

            var submit = submitme(1, event, 'new_note', collectvalidation);
            if(!submit){
                $('#newnote').attr('disabled', false);
            }
            else {
                $("#new_note").submit();
            }
        };
        var PrintNote = function () {
            top.restoreSession();
            window.open('../../patient_file/summary/pnotes_print.php?noteid=' + <?php echo js_url($noteid); ?>, '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
        };

        var SaveNote = function () {
            <?php if ($noteid) { ?>
            top.restoreSession();
            $("#task").val("save");
            $("#new_note").submit();
            <?php } ?>
        };

        var CancelNote = function () {
            top.restoreSession();
            $("#task").val("");
            $("#new_note").submit();
        };

        function gotoReport(doc_id, pname, pid, pubpid, str_dob) {
            EncounterDateArray = [];
            CalendarCategoryArray = [];
            EncounterIdArray = [];
            Count = 0;
            <?php
    if (isset($enc_list) && sqlNumRows($enc_list) > 0) {
        while ($row = sqlFetchArray($enc_list)) {
            ?>
                EncounterIdArray[Count] = '<?php echo attr($row['encounter']); ?>';
            EncounterDateArray[Count] = '<?php echo attr(oeFormatShortDate(date("Y-m-d", strtotime($row['date'])))); ?>';
            CalendarCategoryArray[Count] = '<?php echo attr(xl_appt_category($row['pc_catname'])); ?>';
            Count++;
                    <?php
        }
    }
                    ?>
            top.restoreSession();
            $.ajax({
                type: 'get',
                url: '<?php echo $GLOBALS['webroot'] . "/library/ajax/set_pt.php";?>',
                data: {
                    set_pid: pid,
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                },
                async: false
            });
            parent.left_nav.setPatient(pname, pid, pubpid, '', str_dob);
            parent.left_nav.setPatientEncounter(EncounterIdArray, EncounterDateArray, CalendarCategoryArray);
            <?php if ($GLOBALS['new_tabs_layout']) { ?>
            var docurl = '../controller.php?document&view' + "&patient_id=" + encodeURIComponent(pid) + "&document_id=" + encodeURIComponent(doc_id) + "&";
            var paturl = 'patient_file/summary/demographics.php?pid=' + encodeURIComponent(pid);
            parent.left_nav.loadFrame('dem1', 'pat', paturl);
            parent.left_nav.loadFrame('doc0', 'enc', docurl);
            top.activateTabByName('enc', true);
            <?php } else { ?>
            var docurl = '<?php  echo $GLOBALS['webroot'] . "/controller.php?document&view"; ?>' + "&patient_id=" + encodeURIComponent(pid) + "&document_id=" + encodeURIComponent(doc_id) + "&";
            var paturl = '<?php  echo $GLOBALS['webroot'] . "/interface/patient_file/summary/demographics.php?pid="; ?>' + encodeURIComponent(pid);
            var othername = (window.name === 'RTop') ? 'RBot' : 'RTop';
            parent.frames[othername].location.href = paturl;
            location.href = docurl;
            <?php } ?>
        }

        // This is for callback by the find-patient popup.
        function setpatient(pid, lname, fname, dob) {
            var f = document.getElementById('new_note');
            f.form_patient.value += lname + ', ' + fname + '; ';
            f.reply_to.value += pid + ';';
            <?php if ($noteid) { ?>
            //used when direct messaging service inserts a pnote with indeterminate patient
            //to allow the user to assign the message to a patient.
            top.restoreSession();
            $("#task").val("savePatient");
            $("#new_note").submit();
            <?php } ?>
        }

        // This is for callback by the multi_patients_finder popup.
        function setMultiPatients(patientsList) {
            var f = document.getElementById('new_note');
            f.form_patient.value='';
            f.reply_to.value='';
            $.each(patientsList, function (key, patient) {
                f.form_patient.value += patient.lname + ', ' + patient.fname + '; ';
                f.reply_to.value += patient.pid + ';';
            })

            <?php if ($noteid) { ?>
            //used when direct messaging service inserts a pnote with indeterminate patient
            //to allow the user to assign the message to a patient.
            top.restoreSession();
            $("#task").val("savePatient");
            $("#new_note").submit();
            <?php } ?>
        }

        // This invokes the find-patient popup.
        function sel_patient() {
            dlgopen('../../main/calendar/find_patient_popup.php', '_blank', 625, 400);
        }

        function multi_sel_patient() {
            $('#reply_to').trigger('click');
            var url = '../../main/finder/multi_patients_finder.php'
            // for edit selected list
            if ($('#reply_to').val() !== '') {
                url = url + '?patients=' + $('#reply_to').val() + '&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>';
            }
            dlgopen(url, '_blank', 625, 400);
        }

        function addtolist(sel) {
            $('#assigned_to').trigger("click");
            var itemtext = document.getElementById('assigned_to_text');
            var item = document.getElementById('assigned_to');
            if (sel.value !== '--') {
                if (item.value) {
                    if (item.value.indexOf(sel.value) === -1) {
                        itemtext.value = itemtext.value + ' ; ' + sel.options[sel.selectedIndex].text;
                        item.value = item.value + ';' + sel.value;
                    }
                } else {
                    itemtext.value = sel.options[sel.selectedIndex].text;
                    item.value = sel.value;
                }
            }
        }

        function SMS_direct() {
            var pid = $("#sms_pid").val();
            var m = $("#sms_mobile").val();
            var allow = $("#sms_allow").val();
            if ((pid === '') || (m === '')) {
                alert('<?php echo xls("MedEx needs a valid mobile number to send SMS messages..."); ?>');
            } else if (allow === 'NO') {
                alert('<?php echo xls("This patient does not allow SMS messaging!"); ?>');
            } else {
                top.restoreSession();
                window.open('messages.php?nomenu=1&go=SMS_bot&pid=' + encodeURIComponent(pid) + '&m=' + encodeURIComponent(m), 'SMS_bot', 'width=370,height=600,resizable=0');
            }
        }
    </script>
    <?php
}
    ?>
</body>
</html>
