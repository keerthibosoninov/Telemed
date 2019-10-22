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
require_once("$srcdir/encounter.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/payment_jav.inc.php");
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



// km


$curdate = date_create(date("Y-m-d"));
date_sub($curdate, date_interval_create_from_date_string("7 days"));
$sub_date = date_format($curdate, 'Y-m-d');

// Set the default dates for Lab document search
$form_from_doc_date = ( $_GET['form_from_doc_date'] ) ? $_GET['form_from_doc_date'] : oeFormatShortDate($sub_date);
$form_to_doc_date = ( $_GET['form_to_doc_date'] ) ? $_GET['form_to_doc_date'] : oeFormatShortDate(date("Y-m-d"));

if ($GLOBALS['date_display_format'] == 1) {
    $title_tooltip = "MM/DD/YYYY";
} elseif ($GLOBALS['date_display_format'] == 2) {
    $title_tooltip = "DD/MM/YYYY";
} else {
    $title_tooltip = "YYYY-MM-DD";
}


// km updated



?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet"
        href="<?php echo $webroot; ?>/interface/main/messages/css/reminder_style.css?v=<?php echo $v_js_includes; ?>"
        type="text/css">
    <link rel="stylesheet"
        href="<?php echo $GLOBALS['web_root']; ?>/library/css/bootstrap_navbar.css?v=<?php echo $v_js_includes; ?>"
        type="text/css">

    <?php 
    //Header::setupHeader(['datetime-picker', 'jquery-ui', 'jquery-ui-redmond', 'opener', 'moment']); ?>
    <script>
    var xljs1 = '<?php echo xla('
    Preferences updated successfully '); ?>';
    var format_date_moment_js = '<?php echo attr(DateFormatRead("validateJS")); ?>'; 
    <?php require_once "$srcdir/restoreSession.php"; ?>
    </script>

    <script type="text/javascript"
        src="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/js/reminder_appts.js?v=<?php echo $v_js_includes; ?>">
    </script>

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

    <!-- <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/employee_dashboard_style.css"> -->

    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/vue.js"></script>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/main.js"></script>
    <style>
    .fs-9 {
        font-size: 9px;
    }
    .map .body-compo {
        height: 470px
    }

    .map-width {
        width: 80%;
        margin: auto
    }

    .map-zoom {
        height: 300px;
        width: 100%;
        overflow: hidden
    }

    @media only screen and (max-width: 768px) {
        [class*="col-"] {
            width: 100%;
            text-align: left !important;
        }

        .navbar-toggle>span.icon-bar {
            background-color: #68171A ! important;
        }

        .navbar-default .navbar-toggle {
            border-color: #4a4a4a;
        }

        .navbar-default .navbar-toggle:focus,
        .navbar-default .navbar-toggle:hover {
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
            float: none !important;
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
    <section style="display:none">
        <div class="body-content">
            <div>
                <div class="drag-sec row">
                    <div class="col-md-6 droptarget" id="drag-a">
                        <window-dashboard title="calender">
                            <div class="head-component">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="compo-head">
                                                <span class="spl-mouse">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/drag.svg"
                                                        alt="">
                                                </span>
                                                <span>
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/min.svg"
                                                        alt="">
                                                </span>
                                                <span onclick="SimpleSearch(this);" id="Appointments">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/search-white.svg"
                                                        alt="">

                                                </span>
                                                <input id="txtAppointments" type="text" class="component-search">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-white head-p">Calendar</p>
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

                        <window-dashboard task="task">
                            <div class="head-component" style="margin: auto;margin-top: 10px;">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="compo-head">
                                                <span class="spl-mouse">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/drag.svg"
                                                        alt="">
                                                </span>
                                                <span>
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/min.svg"
                                                        alt="">
                                                </span>
                                                <span onclick="SimpleSearch(this);" id="Appointments">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/search-white.svg"
                                                        alt="">

                                                </span>
                                                <input id="txtAppointments" type="text" class="component-search">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-white head-p">Task</p>
                                        </div>
                                    </div>
                                    <ul class="nav  nav-justified compo-nav" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#home">My Tasks</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#menu1">In Progress</a>
                                        </li>

                                    </ul>

                                    <div class="tab-content">
                                    <div id="home" class="container tab-pane active"><br>
                                    <?php                                   
                                   $count = 0;
                                   $active='all';
                                   $show_all = 'yes';
                                   $result = getPnotesByUser($active, $show_all, $_SESSION['authUser'], false, $sortby = '', $sortorder = '', $begin = '', $listnumber = '');
                                   while ($myrow = sqlFetchArray($result))
                                    {
                                     
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
                                       $count++;
                                    //    var_dump($name);
                                   


                            ?>



                                            <div class="task-box">
                                                <div class="container-fluid pt-2 pb-2">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <span class="Progressing">Progressing</span>
                                                        </div>
                                                        <div class="col-6 text-right">
                                                            <p class="fs-13">
                                                                <!-- <?php  echo attr(oeFormatShortDate(date("Y-m-d", strtotime($myrow['date'])))); ?> -->
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="pt-2">
                                                        <p class="fs-14">
                                                            <?php echo text($name); ?>-<?php echo text($myrow['user']); ?>
                                                        </p>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-8 pt-2 mt-1">
                                                            <div class="progress-task">
                                                                <div class="progress-bar" style="width:70%"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4 text-right">
                                                            <span class="fs-14 pr-2 ">70%</span>
                                                            <span>
                                                                <input class="styled-checkbox" id="styled-checkbox-2"
                                                                    type="checkbox" value="value2">
                                                                <label for="styled-checkbox-2"></label></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } ?>
                                            <!--<div class="task-box">
                                                <div class="container-fluid pt-2 pb-2">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <span class="delayed">delayed</span>
                                                        </div>
                                                        <div class="col-6 text-right">
                                                            <p class="fs-13">5 mins ago</p>
                                                        </div>
                                                    </div>
                                                    <div class="pt-2">
                                                        <p class="fs-14">Assign Mr. Jor El an ENT specialist</p>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-8 pt-2 mt-1">
                                                            <div class="progress-task">
                                                                <div class="progress-bar" style="width:70%"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4 text-right">
                                                            <span class="fs-14 pr-2 ">70%</span>
                                                            <span>
                                                                    <input class="styled-checkbox" id="styled-checkbox-3" type="checkbox" value="value2" >
                                                                    <label for="styled-checkbox-3"></label></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>-->
                                        </div>
                                        <div id="menu1" class="container tab-pane fade"><br>
                                            <?php                                   
                                   $count = 0;
                                   $active='1';
                                   $show_all = 'yes';
                                   $result = getPnotesByUser($active, $show_all, $_SESSION['authUser'], false, $sortby = '', $sortorder = '', $begin = '', $listnumber = '');
                                   while ($myrow = sqlFetchArray($result))
                                    {
                                     
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
                                       $count++;
                                    //    var_dump($name);
                                   


                            ?>

                                            <div class="task-box">
                                                <div class="container-fluid pt-2 pb-2">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <span class="Progressing">Progressing</span>
                                                        </div>
                                                        <div class="col-6 text-right">
                                                            <p class="fs-13">
                                                                <!-- <?php  echo attr(oeFormatShortDate(date("Y-m-d", strtotime($myrow['date'])))); ?> -->
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="p-2">
                                                        <p class="fs-14">
                                                            <?php echo text($name) ?>-<?php echo text($myrow['user']); ?>
                                                        </p>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-8 pt-2 mt-1">
                                                            <div class="progress-task">
                                                                <div class="progress-bar" style="width:70%"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <span class="fs-14 pr-2 ">70%</span>
                                                            <span>
                                                                <input class="styled-checkbox" id="styled-checkbox-2"
                                                                    type="checkbox" value="value2">
                                                                <label for="styled-checkbox-2"></label></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } ?>
                                           
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="body-compo">
                                <div class="container-fluid">



                                </div>
                            </div>
                        </window-dashboard>

                        <window-dashboard>
                            <div class="head-component" style="margin: auto;margin-top: 10px;">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="compo-head">
                                                <span class="spl-mouse">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/drag.svg"
                                                        alt="">
                                                </span>
                                                <span>
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/min.svg"
                                                        alt="">
                                                </span>
                                                <span onclick="SimpleSearch(this);" id="Appointments">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/search-white.svg"
                                                        alt="">

                                                </span>
                                                <input id="txtAppointments" type="text" class="component-search">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-white head-p">Virtual Waiting Room</p>
                                        </div>
                                    </div>



                                </div>
                            </div>

                            <div class="body-compo">
                                <div class="container-fluid ">
                                    <div class="task-box container-fluid">
                                        <div class="row">
                                            <div class="col-7">
                                                <div class="row">
                                                    <div class="col-4 p-0">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/img.svg"
                                                            class="w-100" alt="">
                                                    </div>
                                                    <div class="col-8 spl-flex p-0">
                                                        <div>
                                                            <p class="fs-14">Ms. Martha Wayne</p>
                                                            <p class="fs-12">Costco, C.A</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-5">
                                                <div class="row h-100">
                                                    <div class="col-6 p-0 pt-3">
                                                        <div class="worklink">
                                                            <p class="fs-10">worklink</p>
                                                        </div>
                                                        <div class="cc-flex">
                                                            <div>
                                                                <div class="round-numb">
                                                                    <p>7</p>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="round-numb2">
                                                                    <p>41</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 accept-big h-100">
                                                        <p class="fs-12 text-white">ACCEPT</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="task-box container-fluid">
                                        <div class="row">
                                            <div class="col-7">
                                                <div class="row">
                                                    <div class="col-4 p-0">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/img.svg"
                                                            class="w-100" alt="">
                                                    </div>
                                                    <div class="col-8 spl-flex p-0">
                                                        <div>
                                                            <p class="fs-14">Ms. Martha Wayne</p>
                                                            <p class="fs-12">Costco, C.A</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-5">
                                                <div class="row h-100">
                                                    <div class="col-6 p-0 pt-3">
                                                        <div class="worklink">
                                                            <p class="fs-10">worklink</p>
                                                        </div>
                                                        <div class="cc-flex">
                                                            <div>
                                                                <div class="round-numb">
                                                                    <p>7</p>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="round-numb2">
                                                                    <p>41</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 accept-big h-100">
                                                        <p class="fs-12 text-white">ACCEPT</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="task-box container-fluid">
                                        <div class="row">
                                            <div class="col-7">
                                                <div class="row">
                                                    <div class="col-4 p-0">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/img.svg"
                                                            class="w-100" alt="">
                                                    </div>
                                                    <div class="col-8 spl-flex p-0">
                                                        <div>
                                                            <p class="fs-14">Ms. Martha Wayne</p>
                                                            <p class="fs-12">Costco, C.A</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-5">
                                                <div class="row h-100">
                                                    <div class="col-6 p-0 pt-3">
                                                        <div class="worklink">
                                                            <p class="fs-10">worklink</p>
                                                        </div>
                                                        <div class="cc-flex">
                                                            <div>
                                                                <div class="round-numb">
                                                                    <p>7</p>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="round-numb2">
                                                                    <p>41</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 accept-big h-100">
                                                        <p class="fs-12 text-white">ACCEPT</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </window-dashboard>


                    </div>





                    <div class="col-md-6 droptarget" id="drag-b">
                        <window-dashboard class="map">
                            <div class="head-component">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="compo-head">
                                                <span class="spl-mouse">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/drag.svg"
                                                        alt="">
                                                </span>
                                                <span>
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/min.svg"
                                                        alt="">
                                                </span>
                                                <span onclick="SimpleSearch(this);" id="Appointments">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/search-white.svg"
                                                        alt="">

                                                </span>
                                                <input id="txtAppointments" type="text" class="component-search">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-white head-p">Map</p>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="body-compo">
                                <div class="container-fluid">


                                    <!-- <div class="wrapper">
                                        <label for="datepicker">
                                                        <input type="text" id="datepicker" placeholder="Choose date" autocomplete="off">
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
                                            <div class="task-icon">
                                                <a href=""><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/ico-users.svg" alt=""></a>
                                                <a href=""><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/ico-nouser.svg" alt=""></a>
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
                                            <div class="task-icon">
                                                <a href=""><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/ico-users.svg" alt=""></a>
                                                <a href=""><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/ico-nouser.svg" alt=""></a>
                                            </div>
                                        </div>
                                    </div> -->


                                    <div class="map-zoom">
                                        <div class="map-width zoomable">
                                            <svg xmlns="http://www.w3.org/2000/svg " version="1.2 "
                                                viewbox="0 0 1000 680 ">


                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="AK "
                                                        d="M161.1 453.7l-.3 85.4 1.6 1 3.1.2 1.5-1.1h2.6l.2 2.9 7 6.8.5 2.6 3.4-1.9.6-.2.3-3.1 1.5-1.6 1.1-.2 1.9-1.5 3.1 2.1.6 2.9 1.9 1.1 1.1 2.4 3.9 1.8 3.4 6 2.7 3.9 2.3 2.7 1.5 3.7 5 1.8 5.2 2.1 1 4.4.5 3.1-1 3.4-1.8 2.3-1.6-.8-1.5-3.1-2.7-1.5-1.8-1.1-.8.8
                                                1.5 2.7.2 3.7-1.1.5-1.9-1.9-2.1-1.3.5 1.6 1.3 1.8-.8.8s-.8-.3-1.3-1c-.5-.6-2.1-3.4-2.1-3.4l-1-2.3s-.3 1.3-1 1c-.6-.3-1.3-1.5-1.3-1.5l1.8-1.9-1.5-1.5v-5h-.8l-.8 3.4-1.1.5-1-3.7-.6-3.7-.8-.5.3 5.7v1.1l-1.5-1.3-3.6-6-2.1-.5-.6-3.7-1.6-2.9-1.6-1.1v-2.3l2.1-1.3-.5-.3-2.6.6-3.4-2.4-2.6-2.9-4.8-2.6-4-2.6
                                                1.3-3.2V542l-1.8 1.6-2.9 1.1-3.7-1.1-5.7-2.4h-5.5l-.6.5-6.5-3.9-2.1-.3-2.7-5.8-3.6.3-3.6 1.5.5 4.5 1.1-2.9 1 .3-1.5 4.4 3.2-2.7.6 1.6-3.9 4.4-1.3-.3-.5-1.9-1.3-.8-1.3 1.1-2.7-1.8-3.1 2.1-1.8 2.1-3.4 2.1-4.7-.2-.5-2.1 3.7-.6v-1.3l-2.3-.6 1-2.4 2.3-3.9v-1.8l.2-.8
                                                4.4-2.3 1 1.3h2.7l-1.3-2.6-3.7-.3-5 2.7-2.4 3.4-1.8 2.6-1.1 2.3-4.2 1.5-3.1 2.6-.3 1.6 2.3 1 .8 2.1-2.7 3.2-6.5 4.2-7.8 4.2-2.1 1.1-5.3 1.1-5.3 2.3 1.8 1.3-1.5 1.5-.5 1.1-2.7-1-3.2.2-.8 2.3h-1l.3-2.4-3.6 1.3-2.9 1-3.4-1.3-2.9 1.9h-3.2l-2.1 1.3-1.6.8-2.1-.3-2.6-1.1-2.3.6-1
                                                1-1.6-1.1v-1.9l3.1-1.3 6.3.6 4.4-1.6 2.1-2.1 2.9-.6 1.8-.8 2.7.2 1.6 1.3 1-.3 2.3-2.7 3.1-1 3.4-.6 1.3-.3.6.5h.8l1.3-3.7 4-1.5 1.9-3.7 2.3-4.5 1.6-1.5.3-2.6-1.6 1.3-3.4.6-.6-2.4-1.3-.3-1 1-.2 2.9-1.5-.2-1.5-5.8-1.3 1.3-1.1-.5-.3-1.9-4 .2-2.1 1.1-2.6-.3
                                                1.5-1.5.5-2.6-.6-1.9 1.5-1 1.3-.2-.6-1.8v-4.4l-1-1-.8 1.5h-6.1l-1.5-1.3-.6-3.9-2.1-3.6v-1l2.1-.8.2-2.1 1.1-1.1-.8-.5-1.3.5-1.1-2.7 1-5 4.5-3.2 2.6-1.6 1.9-3.7 2.7-1.3 2.6 1.1.3 2.4 2.4-.3 3.2-2.4 1.6.6 1 .6h1.6l2.3-1.3.8-4.4s.3-2.9 1-3.4c.6-.5 1-1 1-1l-1.1-1.9-2.6.8-3.2.8-1.9-.5-3.6-1.8-5-.2-3.6-3.7.5-3.9.6-2.4-2.1-1.8-1.9-3.7.5-.8
                                                6.8-.5h2.1l1 1h.6l-.2-1.6 3.9-.6 2.6.3 1.5 1.1-1.5 2.1-.5 1.5 2.7 1.6 5 1.8 1.8-1-2.3-4.4-1-3.2 1-.8-3.4-1.9-.5-1.1.5-1.6-.8-3.9-2.9-4.7-2.4-4.2 2.9-1.9h3.2l1.8.6 4.2-.2 3.7-3.6 1.1-3.1 3.7-2.4 1.6 1 2.7-.6 3.7-2.1 1.1-.2 1 .8 4.5-.2 2.7-3.1h1.1l3.6 2.4
                                                1.9 2.1-.5 1.1.6 1.1 1.6-1.6 3.9.3.3 3.7 1.9 1.5 7.1.6 6.3 4.2 1.5-1 5.2 2.6 2.1-.6 1.9-.8 4.8 1.9zM46 482.6l2.1 5.3-.2 1-2.9-.3-1.8-4-1.8-1.5H39l-.2-2.6 1.8-2.4 1.1 2.4 1.5 1.5zm-2.6 33.5l3.7.8 3.7 1 .8 1-1.6 3.7-3.1-.2-3.4-3.6zM22.7 502l1.1 2.6 1.1
                                                1.6-1.1.8-2.1-3.1V502zM9 575.1l3.4-2.3 3.4-1 2.6.3.5 1.6 1.9.5 1.9-1.9-.3-1.6 2.7-.6 2.9 2.6-1.1 1.8-4.4 1.1-2.7-.5-3.7-1.1-4.4 1.5-1.6.3zm48.9-4.5l1.6 1.9 2.1-1.6-1.5-1.3zm2.9 3l1.1-2.3 2.1.3-.8 1.9h-2.4zm23.6-1.9l1.5 1.8 1-1.1-.8-1.9zm8.8-12.5l1.1 5.8
                                                2.9.8 5-2.9 4.4-2.6-1.6-2.4.5-2.4-2.1 1.3-2.9-.8 1.6-1.1 1.9.8 3.9-1.8.5-1.5-2.4-.8.8-1.9-2.7 1.9-4.7 3.6-4.8 2.9zm42.3-19.8l2.4-1.5-1-1.8-1.8 1z ">
                                                        <title>Alaska</title>


                                                    </path>
                                                </a>

                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="HI " d="M233.1 519.3l1.9-3.6 2.3-.3.3.8-2.1 3.1zm10.2-3.7l6.1 2.6 2.1-.3 1.6-3.9-.6-3.4-4.2-.5-4 1.8zm30.7 10l3.7 5.5 2.4-.3 1.1-.5 1.5 1.3 3.7-.2 1-1.5-2.9-1.8-1.9-3.7-2.1-3.6-5.8 2.9zm20.2 8.9l1.3-1.9 4.7 1 .6-.5 6.1.6-.3 1.3-2.6
                                                1.5-4.4-.3zm5.3 5.2l1.9 3.9 3.1-1.1.3-1.6-1.6-2.1-3.7-.3zm7-1.2l2.3-2.9 4.7 2.4 4.4 1.1 4.4 2.7v1.9l-3.6 1.8-4.8 1-2.4-1.5zm16.6 15.6l1.6-1.3 3.4 1.6 7.6 3.6 3.4 2.1 1.6 2.4 1.9 4.4 4 2.6-.3 1.3-3.9 3.2-4.2 1.5-1.5-.6-3.1 1.8-2.4 3.2-2.3 2.9-1.8-.2-3.6-2.6-.3-4.5.6-2.4-1.6-5.7-2.1-1.8-.2-2.6
                                                2.3-1 2.1-3.1.5-1-1.6-1.8z ">
                                                        <title>Hawaii</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="AL " d="M628.5 466.4l.6.2 1.3-2.7 1.5-4.4 2.3.6 3.1 6v1l-2.7 1.9 2.7.3 5.2-2.5-.3-7.6-2.5-1.8-2-2 .4-4 10.5-1.5 25.7-2.9 6.7-.6 5.6.1-.5-2.2-1.5-.8-.9-1.1 1-2.6-.4-5.2-1.6-4.5.8-5.1 1.7-4.8-.2-1.7-1.8-.7-.5-3.6-2.7-3.4-2-6.5-1.4-6.7-1.8-5-3.8-16-3.5-7.9-.8-5.6.1-2.2-9
                                                .8-23.4 2.2-12.2.8-.2 6.4.2 16.7-.7 31-.3 14.1 2.8 18.8 1.6 14.7z ">
                                                        <title>Alabama</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="AR "
                                                        d="M587.3 346.1l-6.4-.7.9-3.1 3.1-2.6.6-2.3-1.8-2.9-31.9 1.2-23.3.7-23.6.3 1.5 6.9.1 8.5 1.4 10.9.3 38.2 2.1 1.6 3-1.2 2.9 1.2.4 10.1 25.2-.2 26.8-.8.9-1.9-.3-3.8-1.7-3.1 1.5-1.4-1.4-2.2.7-2.4 1.1-5.9 2.7-2.3-.8-2.2 4-5.6
                                                2.5-1.1-.1-1.7-.5-1.7 2.9-5.8 2.5-1.1.2-3.3 2.1-1.4.9-4.1-1.4-4 4.2-2.4.3-2.1 1.2-4.2.9-3.1z ">
                                                        <title>Arkansas</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="AZ "
                                                        d="M135.1 389.7l-.3 1.5.5 1 18.9 10.7 12.1 7.6 14.7 8.6 16.8 10 12.3 2.4 25.4 2.7 6-39.6 7-53.1 4.4-31-24.6-3.6-60.7-11-.2 1.1-2.6 16.5-2.1 3.8-2.8-.2-1.2-2.6-2.6-.4-1.2-1.1-1.1.1-2.1 1.7-.3 6.8-.3 1.5-.5 12.5-1.5 2.4-.4
                                                3.3 2.8 5 1.1 5.5.7 1.1 1.1.9-.4 2.4-1.7 1.2-3.4 1.6-1.6 1.8-1.6 3.6-.5 4.9-3 2.9-1.9.9-.1 5.8-.6 1.6.5.8 3.9.4-.9 3-1.7 2.4-3.7.4z ">
                                                        <title>Arizona</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="CA "
                                                        d="M122.7 385.9l-19.7-2.7-10-1.5-.5-1.8v-9.4l-.3-3.2-2.6-4.2-.8-2.3-3.9-4.2-2.9-4.7-2.7-.2-3.2-.8-.3-1 1.5-.6-.6-3.2-1.5-2.1-4.8-.8-3.9-2.1-1.1-2.3-2.6-4.8-2.9-3.1H57l-3.9-2.1-4.5-1.8-4.2-.5-2.4-2.7.5-1.9 1.8-7.1.8-1.9v-2.4l-1.6-1-.5-2.9-1.5-2.6-3.4-5.8-1.3-3.1-1.5-4.7-1.6-5.3-3.2-4.4-.5-2.9.8-3.9h1.1l2.1-1.6
                                                1.1-3.6-1-2.7-2.7-.5-1.9-2.6-2.1-3.7-.2-8.2.6-1.9.6-2.3.5-2.4-5.7-6.3V236l.3-.5.3-3.2-1.3-4-2.3-4.8-2.7-4.5-1.8-3.9 1-3.7.6-5.8 1.8-3.1.3-6.5-1.1-3.6-1.6-4.2L14 184l.8-3.2 1.5-4.2 1.8-.8.3-1.1 3.1-2.6 5.2-11.8.2-7.4 1.69-4.9 38.69 11.8 25.6 6.6-8 31.3-8.67
                                                33.1L88.84 250 131 312.3l17.1 26.1-.4 3.1 2.8 5.2 1.1 5.4 1 1.5.7.6-.2 1.4-1.4 1-3.4 1.6-1.9 2.1-1.7 3.9-.5 4.7-2.6 2.5-2.3 1.1-.1 6.2-.6 1.9 1 1.7 3 .3-.4 1.6-1.4 2-3.9.6zM48.8 337l1.3 1.5-.2 1.3-3.2-.1-.6-1.2-.6-1.5zm1.9 0l1.2-.6 3.6 2.1 3.1 1.2-.9.6-4.5-.2-1.6-1.6zm20.7
                                                19.8l1.8 2.3.8 1 1.5.6.6-1.5-1-1.8-2.7-2-1.1.2v1.2zm-1.4 8.7l1.8 3.2 1.2 1.9-1.5.2-1.3-1.2s-.7-1.5-.7-1.9v-2.2z ">
                                                        <title>California</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="CO "
                                                        d="M380.2 235.5l-36-3.5-79.1-8.6-2.2 22.1-7 50.4-1.9 13.7 34 3.9 37.5 4.4 34.7 3 14.3.6z ">
                                                        <title>Colorado</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="CT "
                                                        d="M852 190.9l3.6-3.2 1.9-2.1.8.6 2.7-1.5 5.2-1.1 7-3.5-.6-4.2-.8-4.4-1.6-6-4.3 1.1-21.8 4.7.6 3.1 1.5 7.3v8.3l-.9 2.1 1.7 2.2z ">
                                                        <title>Connecticut</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="DE "
                                                        d="M834.4 247.2l-1 .5-3.6-2.4-1.8-4.7-1.9-3.6-2.3-1-2.1-3.6.5-2 .5-2.3.1-1.1-.6.1-1.7 1-2 1.7-.2.3 1.4 4.1 2.3 5.6 3.7 16.1 5-.3 6-1.1z ">
                                                        <title>Delaware</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="FL "
                                                        d="M750.2 445.2l-5.2-.7-.7.8 1.5 4.4-.4 5.2-4.1-1-.2-2.8H737l-5.3.7-32.4 1.9-8.2-.3-1.7-1.7-2.5-4.2H681l-6.6.5-35.4 4.2-.3 2.8 1.6 1.6 2.9 2 .3 8.4 3.3-.6 6-2.1 6-.5 4.4-.6 7.6 1.8 8.1 3.9 1.6 1.5 2.9 1.1 1.6 1.9.3 2.7 3.2-1.3h3.9l3.6-1.9
                                                3.7-3.6 3.1.2.5-1.1-.8-1 .2-1.9 4-.8h2.6l2.9 1.5 4.2 1.5 2.4 3.7 2.7 1 1.1 3.4 3.4 1.6 1.6 2.6 1.9.6 5.2 1.3 1.3 3.1 3 3.7v9.5l-1.5 4.7.3 2.7 1.3 4.8 1.8 4 .8-.5 1.5-4.5-2.6-1-.3-.6 1.6-.6 4.5 1 .2 1.6-3.2 5.5-2.1 2.4 3.6 3.7 2.6 3.1 2.9 5.3 2.9 3.9 2.1
                                                5 1.8.3 1.6-2.1 1.8 1.1 2.6 4 .6 3.6 3.1 4.4.8-1.3 3.9.3 3.6 2.3 3.4 5.2.8 3.4.3 2.9 1.1 1 1.3.5 2.4-1 1.5-1.6 3.9-.2 3.1-1.5 2.7-3.2-.5-1.9-.3-2.4.6-1.9-.3-1.9 2.4-1.3.3-3.4-.6-1.8-.5-12-1.3-7.6-4.5-8.2-3.6-5.8-2.6-5.3-2.9-2.9-2.9-7.4.7-1.4 1.1-1.3-1.6-2.9-4-3.7-4.8-5.5-3.7-6.3-5.3-9.4-3.7-9.7-2.3-7.3zm17.7
                                                132.7l2.4-.6 1.3-.2 1.5-2.3 2.3-1.6 1.3.5 1.7.3.4 1.1-3.5 1.2-4.2 1.5-2.3 1.2zm13.5-5l1.2 1.1 2.7-2.1 5.3-4.2 3.7-3.9 2.5-6.6 1-1.7.2-3.4-.7.5-1 2.8-1.5 4.6-3.2 5.3-4.4 4.2-3.4 1.9z ">
                                                        <title>Florida</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">

                                                    <path id="GA "
                                                        d="M750.2 444.2l-5.6-.7-1.4 1.6 1.6 4.7-.3 3.9-2.2-.6-.2-3h-5.2l-5.3.7-32.3 1.9-7.7-.3-1.4-1.2-2.5-4.3-.8-3.3-1.6-.9-.5-.5.9-2.2-.4-5.5-1.6-4.5.8-4.9 1.7-4.8-.2-2.5-1.9-.7-.4-3.2-2.8-3.5-1.9-6.2-1.5-7-1.7-4.8-3.8-16-3.5-8-.8-5.3.1-2.3
                                                3.3-.3 13.6-1.6 18.6-2 6.3-1.1.5 1.4-2.2.9-.9 2.2.4 2 1.4 1.6 4.3 2.7 3.2-.1 3.2 4.7.6 1.6 2.3 2.8.5 1.7 4.7 1.8 3 2.2 2.3 3 2.3 1.3 2 1.8 1.4 2.7 2.1 1.9 4.1 1.8 2.7 6 1.7 5.1 2.8.7 2.1 1.9 2 5.7 2.9 1.6 1.7-.8.4 1.2-3.3 6.2.5 2.6-1.5 4.2-2.3 10 .8 6.3z ">
                                                        <title>Georgia</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="IA "
                                                        d="M556.8 183.6l2.1 2.1.3.7-2 3 .3 4 2.6 4.1 3.1 1.6 2.4.3.9 1.8.2 2.4 2.5 1 .9 1.1.5 1.6 3.8 3.3.6 1.9-.7 3-1.7 3.7-.6 2.4-2.1 1.6-1.6.5-5.7 1.5-1.6 4.8.8 1.8 1.7 1.5-.2 3.5-1.9 1.4-.7 1.8v2.4l-1.4.4-1.7 1.4-.5 1.7.4 1.7-1.3
                                                1-2.3-2.7-1.4-2.8-8.3.8-10 .6-49.2 1.2-1.6-4.3-.4-6.7-1.4-4.2-.7-5.2-2.2-3.7-1-4.6-2.7-7.8-1.1-5.6-1.4-1.9-1.3-2.9 1.7-3.8 1.2-6.1-2.7-2.2-.3-2.4.7-2.4 1.8-.3 61.1-.6 21.2-.7z ">
                                                        <title>Iowa</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="ID " d="M175.3 27.63l-4.8 17.41L166 65.9l-3.4 16.22-.4 9.67 1.2 4.44 3.5 2.66-.2 3.91-3.9 4.4-4.5 6.6-.9 2.9-1.2 1.1-1.8.8-4.3 5.3-.4 3.1-.4 1.1.6 1 2.6-.1 1.1 2.3-2.4 5.8-1.2 4.2-8.8 35.3 20.7 4.5 39.5 7.9 34.8 6.1 4.9-29.2
                                                3.8-24.1-2.7-2.4-.4-2.6-.8-1.1-2.1 1-.7 2.6-3.2.5-3.9-1.6-3.8.1-2.5.7-3.4-1.5-2.4.2-2.4 2-2-1.1-.7-4 .7-2.9-2.5-2.9-3.3-2.6-2.7-13.1-.1-4.7-.3-.1-.2.4-5.1 3.5-1.7-.2-2.9-3.4-.2-3.1 7-17.13-.4-1.94-3.4-1.15-.6-1.18-2.6-3.46-4.6-10.23-3.2-1.53-2-4.95 1.3-4.63-3.2-7.58
                                                4.4-21.52z ">
                                                        <title>Idaho</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="IL "
                                                        d="M618.7 214.3l-.8-2.6-1.3-3.7-1.6-1.8-1.5-2.6-.4-5.5-15.9 1.8-17.4 1h-12.3l.2 2.1 2.2.9 1.1 1.4.4 1.4 3.9 3.4.7 2.4-.7 3.3-1.7 3.7-.8 2.7-2.4 1.9-1.9.6-5.2 1.3-1.3 4.1.6 1.1 1.9 1.8-.2 4.3-2.1 1.6-.5 1.3v2.8l-1.8.6-1.4
                                                1.2-.4 1.2.4 2-1.6 1.3-.9 2.8.3 3.9 2.3 7 7 7.6 5.7 3.7v4.4l.7 1.2 6.6.6 2.7 1.4-.7 3.5-2.2 6.2-.8 3 2 3.7 6.4 5.3 4.8.8 2.2 5.1 2 3.4-.9 2.8 1.5 3.8 1.7 2.1 1.6-.3 1-2.2 2.4-1.7 2.8-1 6.1 2.5.5-.2v-1.1l-1.2-2.7.4-2.8 2.4-1.6 3.4-1.2-.5-1.3-.8-2 1.2-1.3
                                                1-2.7v-4l.4-4.9 2.5-3 1.8-3.8 2.5-4-.5-5.3-1.8-3.2-.3-3.3.8-5.3-.7-7.2-1.1-15.8-1.4-15.3-.9-11.7z ">
                                                        <title>Illinois</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="IN "
                                                        d="M622.9 216.1l1.5 1 1.1-.3 2.1-1.9 2.5-1.8 14.3-1.1 18.4-1.8 1.6 15.5 4.9 42.6-.6 2.9 1.3 1.6.2 1.3-2.3 1.6-3.6 1.7-3.2.4-.5 4.8-4.7 3.6-2.9 4 .2 2.4-.5 1.4h-3.5l-1.4-1.7-5.2 3 .2 3.1-.9.2-.5-.9-2.4-1.7-3.6 1.5-1.4 2.9-1.2-.6-1.6-1.8-4.4.5-5.7
                                                1-2.5 1.3v-2.6l.4-4.7 2.3-2.9 1.8-3.9 2.7-4.2-.5-5.8-1.8-3.1-.3-3.2.8-5.3-.7-7.1-.9-12.6-2.5-30.1z ">
                                                        <title>Indiana</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="KS "
                                                        d="M485.9 259.5l-43.8-.6-40.6-1.2-21.7-.9-4.3 64.8 24.3 1 44.7 2.1 46.3.6 12.6-.3.7-35-1.2-11.1-2.5-2-2.4-3-2.3-3.6.6-3 1.7-1.4v-2.1l-.8-.7-2.6-.2-3.5-3.4z ">
                                                        <title>Kansas</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="KY " d="M607.2 331.8l12.6-.7.1-4.1h4.3l30.4-3.2 45.1-4.3 5.6-3.6 3.9-2.1.1-1.9 6-7.8 4.1-3.6 2.1-2.4-3.3-2-2.5-2.7-3-3.8-.5-2.2-2.6-1.4-.9-1.9-.2-6.1-2.6-2-1.9-1.1-.5-2.3-1.3.2-2 1.2-2.5 2.7-1.9-1.7-2.5-.5-2.4 1.4h-2.3l-1.8-2-5.6-.1-1.8-4.5-2.9-1.5-2.1.8-4.2.2-.5
                                                2.1 1.2 1.5.3 2.1-2.8 2-3.8 1.8-2.6.4-.5 4.5-4.9 3.6-2.6 3.7.2 2.2-.9 2.3-4.5-.1-1.3-1.3-3.9 2.2.2 3.3-2.4.6-.8-1.4-1.7-1.2-2.7 1.1-1.8 3.5-2.2-1-1.4-1.6-3.7.4-5.6 1-2.8 1.3-1.2 3.4-1 1 1.5 3.7-4.2 1.4-1.9 1.4-.4 2.2 1.2 2.4v2.2l-1.6.4-6.1-2.5-2.3.9-2
                                                1.4-.8 1.8 1.7 2.4-.9 1.8-.1 3.3-2.4 1.3-2.1 1.7z ">
                                                        <title>Kentucky</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="LA "
                                                        d="M526.9 485.9l8.1-.3 10.3 3.6 6.5 1.1 3.7-1.5 3.2 1.1 3.2 1 .8-2.1-3.2-1.1-2.6.5-2.7-1.6.8-1.5 3.1-1 1.8 1.5 1.8-1 3.2.6 1.5 2.4.3 2.3 4.5.3 1.8 1.8-.8 1.6-1.3.8 1.6 1.6 8.4 3.6 3.6-1.3 1-2.4 2.6-.6 1.8-1.5 1.3 1 .8 2.9-2.3.8.6.6
                                                3.4-1.3 2.3-3.4.8-.5-2.1-.3.8-1.6-.2-1.5 2.1-.5 1.1-1.3.6.8.6 3.1 4.2.6 4 1.9 1 1.5h2.9l1.1 1 2.3-3.1V493h-1.3l-3.4-2.7-5.8-.8-3.2-2.3 1.1-2.7 2.3.3.2-.6-1.8-1v-.5h3.2l1.8-3.1-1.3-1.9-.3-2.7-1.5.2-1.9 2.1-.6 2.6-3.1-.6-1-1.8 1.8-1.9 1.9-1.7-2.2-6.5-3.4-3.4
                                                1-7.3-.2-.5-1.3.2-33.1 1.4-.8-2.4.8-8.5 8.6-14.8-.9-2.6 1.4-.4.4-2-2.2-2 .1-1.9-2-4.5-.4-5.1.1-.7-26.4.8-25.2.1.4 9.7.7 9.5.5 3.7 2.6 4.5.9 4.4 4.3 6 .3 3.1.6.8-.7 8.3-2.8 4.6 1.2 2.4-.5 2.6-.8 7.3-1.3 3 .2 3.7z ">
                                                        <title>Louisiana</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="MA "
                                                        d="M887.5 172.5l-.5-2.3.8-1.5 2.9-1.5.8 3.1-.5 1.8-2.4 1.5v1l1.9-1.5 3.9-4.5 3.9-1.9 4.2-1.5-.3-2.4-1-2.9-1.9-2.4-1.8-.8-2.1.2-.5.5 1 1.3 1.5-.8 2.1 1.6.8 2.7-1.8 1.8-2.3 1-3.6-.5-3.9-6-2.3-2.6h-1.8l-1.1.8-1.9-2.6.3-1.5
                                                2.4-5.2-2.9-4.4-3.7 1.8-1.8 2.9-18.3 4.7-13.8 2.5-.6 10.6.7 4.9 22-4.8 11.2-2.8 2 1.6 3.4 4.3 2.9 4.7zm12.5 1.4l2.2-.7.5-1.7 1 .1 1 2.3-1.3.5-3.9.1zm-9.4.8l2.3-2.6h1.6l1.8 1.5-2.4 1-2.2 1z ">
                                                        <title>Massachusetts</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="MD "
                                                        d="M834.8 264.1l1.7-3.8.5-4.8-6.3 1.1-5.8.3-3.8-16.8-2.3-5.5-1.5-4.6-22.2 4.3-37.6 7.6 2 10.4 4.8-4.9 2.5-.7 1.4-1.5 1.8-2.7 1.6.7 2.6-.2 2.6-2.1 2-1.5 2.1-.6 1.5 1.1 2.7 1.4 1.9 1.8 1.3 1.4 4.8 1.6-.6 2.9 5.8 2.1 2.1-2.6
                                                3.7 2.5-2.1 3.3-.7 3.3-1.8 2.6v2.1l.3.8 2 1.3 3.4 1.1 4.3-.1 3.1 1 2.1.3 1-2.1-1.5-2.1v-1.8l-2.4-2.1-2.1-5.5 1.3-5.3-.2-2.1-1.3-1.3s1.5-1.6 1.5-2.3c0-.6.5-2.1.5-2.1l1.9-1.3 1.9-1.6.5 1-1.5 1.6-1.3 3.7.3 1.1 1.8.3.5 5.5-2.1 1 .3 3.6.5-.2 1.1-1.9 1.6 1.8-1.6
                                                1.3-.3 3.4 2.6 3.4 3.9.5 1.6-.8 3.2 4.2 1 .4zm-14.5.2l1.1 2.5.2 1.8 1.1 1.9s.9-.9.9-1.2c0-.3-.7-3.1-.7-3.1l-.7-2.3z ">
                                                        <title>Maryland</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="ME "
                                                        d="M865.8 91.9l1.5.4v-2.6l.8-5.5 2.6-4.7 1.5-4-1.9-2.4v-6l.8-1 .8-2.7-.2-1.5-.2-4.8 1.8-4.8 2.9-8.9 2.1-4.2h1.3l1.3.2v1.1l1.3 2.3 2.7.6.8-.8v-1l4-2.9 1.8-1.8 1.5.2 6 2.4 1.9 1 9.1 29.9h6l.8 1.9.2 4.8 2.9 2.3h.8l.2-.5-.5-1.1
                                                2.8-.5 1.9 2.1 2.3 3.7V85l-2.1 4.7-1.9.6-3.4 3.1-4.8 5.5h-1.3c-.6 0-1-2.1-1-2.1l-1.8.2-1 1.5-2.4 1.5-1 1.5 1.6 1.5-.5.6-.5 2.7-1.9-.2v-1.6l-.3-1.3-1.5.3-1.8-3.2-2.1 1.3 1.3 1.5.3 1.1-.8 1.3.3 3.1.2 1.6-1.6 2.6-2.9.5-.3 2.9-5.3 3.1-1.3.5-1.6-1.5-3.1 3.6
                                                1 3.2-1.5 1.3-.2 4.4-1.1 6.3-2.2-.9-.5-3.1-4-1.1-.2-2.5-11.7-37.43zm36.5 15.6l1.5-1.5 1.4 1.1.6 2.4-1.7.9zm6.7-5.9l1.8 1.9s1.3.1 1.3-.2c0-.3.2-2 .2-2l.9-.8-.8-1.8-2 .7z ">
                                                        <title>Maine</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="MI "
                                                        d="M644.5 211l19.1-1.9.2 1.1 9.9-1.5 12-1.7.1-.6.2-1.5 2.1-3.7 2-1.7-.2-5.1 1.6-1.6 1.1-.3.2-3.6 1.5-3 1.1.6.2.6.8.2 1.9-1-.4-9.1-3.2-8.2-2.3-9.1-2.4-3.2-2.6-1.8-1.6 1.1-3.9 1.8-1.9 5-2.7 3.7-1.1.6-1.5-.6s-2.6-1.5-2.4-2.1c.2-.6.5-5
                                                .5-5l3.4-1.3.8-3.4.6-2.6 2.4-1.6-.3-10-1.6-2.3-1.3-.8-.8-2.1.8-.8 1.6.3.2-1.6-2.6-2.2-1.3-2.6h-2.6l-4.5-1.5-5.5-3.4h-2.7l-.6.6-1-.5-3.1-2.3-2.9 1.8-2.9 2.3.3 3.6 1 .3 2.1.5.5.8-2.6.8-2.6.3-1.5 1.8-.3 2.1.3 1.6.3 5.5-3.6 2.1-.6-.2v-4.2l1.3-2.4.6-2.4-.8-.8-1.9.8-1
                                                4.2-2.7 1.1-1.8 1.9-.2 1 .6.8-.6 2.6-2.3.5v1.1l.8 2.4-1.1 6.1-1.6 4 .6 4.7.5 1.1-.8 2.4-.3.8-.3 2.7 3.6 6 2.9 6.5 1.5 4.8-.8 4.7-1 6-2.4 5.2-.3 2.7-3.2 3.1zm-33.3-72.4l-1.3-1.1-1.8-10.4-3.7-1.3-1.7-2.3-12.6-2.8-2.8-1.1-8.1-2.2-7.8-1-3.9-5.3.7-.5 2.7-.8
                                                3.6-2.3v-1l.6-.6 6-1 2.4-1.9 4.4-2.1.2-1.3 1.9-2.9 1.8-.8 1.3-1.8 2.3-2.3 4.4-2.4 4.7-.5 1.1 1.1-.3 1-3.7 1-1.5 3.1-2.3.8-.5 2.4-2.4 3.2-.3 2.6.8.5 1-1.1 3.6-2.9 1.3 1.3h2.3l3.2 1 1.5 1.1 1.5 3.1 2.7 2.7 3.9-.2 1.5-1 1.6 1.3 1.6.5 1.3-.8h1.1l1.6-1 4-3.6
                                                3.4-1.1 6.6-.3 4.5-1.9 2.6-1.3 1.5.2v5.7l.5.3 2.9.8 1.9-.5 6.1-1.6 1.1-1.1 1.5.5v7l3.2 3.1 1.3.6 1.3 1-1.3.3-.8-.3-3.7-.5-2.1.6-2.3-.2-3.2 1.5h-1.8l-5.8-1.3-5.2.2-1.9 2.6-7 .6-2.4.8-1.1 3.1-1.3 1.1-.5-.2-1.5-1.6-4.5 2.4h-.6l-1.1-1.6-.8.2-1.9 4.4-1 4-3.2
                                                6.9zm-29.6-56.5l1.8-2.1 2.2-.8 5.4-3.9 2.3-.6.5.5-5.1 5.1-3.3 1.9-2.1.9zm86.2 32.1l.6 2.5 3.2.2 1.3-1.2s-.1-1.5-.4-1.6c-.3-.2-1.6-1.9-1.6-1.9l-2.2.2-1.6.2-.3 1.1z ">
                                                        <title>Michigan</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="MN "
                                                        d="M464.6 66.79l-.6 3.91v10.27l1.6 5.03 1.9 3.32.5 9.93 1.8 13.45 1.8 7.3.4 6.4v5.3l-1.6 1.8-1.8 1.3v1.5l.9 1.7 4.1 3.5.7 3.2v35.9l60.3-.6 21.2-.7-.5-6-1.8-2.1-7.2-4.6-3.6-5.3-3.4-.9-2-2.8h-3.2l-3.5-3.8-.5-7 .1-3.9 1.5-3-.7-2.7-2.8-3.1
                                                2.2-6.1 5.4-4 1.2-1.4-.2-8 .2-3 2.6-3 3.8-2.9 1.3-.2 4.5-5 1.8-.8 2.3-3.9 2.4-3.6 3.1-2.6 4.8-2 9.2-4.1 3.9-1.8.6-2.3-4.4.4-.7 1.1h-.6l-1.8-3.1-8.9.3-1 .8h-1l-.5-1.3-.8-1.8-2.6.5-3.2 3.2-1.6.8h-3.1l-2.6-1v-2.1l-1.3-.2-.5.5-2.6-1.3-.5-2.9-1.5.5-.5 1-2.4-.5-5.3-2.4-3.9-2.6h-2.9l-1.3-1-2.3.6-1.1
                                                1.1-.3 1.3h-4.8v-2.1l-6.3-.3-.3-1.5h-4.8l-1.6-1.6-1.5-6.1-.8-5.5-1.9-.8-2.3-.5-.6.2-.3 8.2-30.1-.03z ">
                                                        <title>Minnesota</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="MO "
                                                        d="M593.1 338.7l.5-5.9 4.2-3.4 1.9-1v-2.9l.7-1.6-1.1-1.6-2.4.3-2.1-2.5-1.7-4.5.9-2.6-2-3.2-1.8-4.6-4.6-.7-6.8-5.6-2.2-4.2.8-3.3 2.2-6 .6-3-1.9-1-6.9-.6-1.1-1.9v-4.1l-5.3-3.5-7.2-7.8-2.3-7.3-.5-4.2.7-2.4-2.6-3.1-1.2-2.4-7.7.8-10
                                                .6-48.8 1.2 1.3 2.6-.1 2.2 2.3 3.6 3 3.9 3.1 3 2.6.2 1.4 1.1v2.9l-1.8 1.6-.5 2.3 2.1 3.2 2.4 3 2.6 2.1 1.3 11.6-.8 40 .5 5.7 23.7-.2 23.3-.7 32.5-1.3 2.2 3.7-.8 3.1-3.1 2.5-.5 1.8 5.2.5 4.1-1.1z ">
                                                        <title>Missouri</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="MS "
                                                        d="M604.3 472.5l2.6-4.2 1.8.8 6.8-1.9 2.1.3 1.5.8h5.2l.4-1.6-1.7-14.8-2.8-19 1-45.1-.2-16.7.2-6.3-4.8.3-19.6 1.6-13 .4-.2 3.2-2.8 1.3-2.6 5.1.5 1.6.1 2.4-2.9 1.1-3.5 5.1.8 2.3-3 2.5-1 5.7-.6 1.9 1.6 2.5-1.5 1.4 1.5 2.8.3
                                                4.2-1.2 2.5-.2.9.4 5 2 4.5-.1 1.7 2.3 2-.7 3.1-.9.3.6 1.9-8.6 15-.8 8.2.5 1.5 24.2-.7 8.2-.7 1.9-.3.6 1.4-1 7.1 3.3 3.3 2.2 6.4z ">
                                                        <title>Mississippi</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="MT "
                                                        d="M361.1 70.77l-5.3 57.13-1.3 15.2-59.1-6.6-49-7.1-1.4 11.2-1.9-1.7-.4-2.5-1.3-1.9-3.3 1.5-.7 2.5-2.3.3-3.8-1.6-4.1.1-2.4.7-3.2-1.5-3 .2-2.1 1.9-.9-.6-.7-3.4.7-3.2-2.7-3.2-3.3-2.5-2.5-12.6-.1-5.3-1.6-.8-.6 1-4.5 3.2-1.2-.1-2.3-2.8-.2-2.8
                                                7-17.15-.6-2.67-3.5-1.12-.4-.91-2.7-3.5-4.6-10.41-3.2-1.58-1.8-4.26 1.3-4.63-3.2-7.57 4.4-21.29L222 37.3l18.4 3.4 32.3 5.3 29.3 4 29.2 3.5 30.8 3.07z ">
                                                        <title>Montana</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="NC "
                                                        d="M786.7 357.7L774 350l-3.1-.8-16.6 2.1-1.6-3-2.8-2.2-16.7.5-7.4.9-9.2 4.5-6.8 2.7-6.5 1.2-13.4 1.4.1-4.1 1.7-1.3 2.7-.7.7-3.8 3.9-2.5 3.9-1.5 4.5-3.7 4.4-2.3.7-3.2 4.1-3.8.7 1 2.5.2 2.4-3.6 1.7-.4 2.6.3 1.8-4 2.5-2.4.5-1.8.1-3.5
                                                4.4.1 38.5-5.6 57.5-12.3 2 4.8 3.6 6.5 2.4 2.4.6 2.3-2.4.2.8.6-.3 4.2-2.6 1.3-.6 2.1-1.3 2.9-3.7 1.6-2.4-.3-1.5-.2-1.6-1.3.3 1.3v1h1.9l.8 1.3-1.9 6.3h4.2l.6 1.6 2.3-2.3 1.3-.5-1.9 3.6-3.1 4.8H828l-1.1-.5-2.7.6-5.2 2.4-6.5 5.3-3.4 4.7-1.9 6.5-.5 2.4-4.7.5-5.1
                                                1.5zm49.3-26.2l2.6-2.5 3.2-2.6 1.5-.6.2-2-.6-6.1-1.5-2.3-.6-1.9.7-.2 2.7 5.5.4 4.4-.2 3.4-3.4 1.5-2.8 2.4-1.1 1.2z ">
                                                        <title>North Carolina</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="ND "
                                                        d="M471 126.4l-.4-6.2-1.8-7.3-1.8-13.61-.5-9.7-1.9-3.18-1.6-5.32V70.68l.6-3.85-1.8-5.54-28.6-.59-18.6-.6-26.5-1.3-25.2-2.16-.9 14.42-4.7 50.94 56.8 3.9 56.9 1.7z ">
                                                        <title>North Dakota</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="NE "
                                                        d="M470.3 204.3l-1-2.3-.5-1.6-2.9-1.6-4.8-1.5-2.2-1.2-2.6.1-3.7.4-4.2 1.2-6-4.1-2.2-2-10.7.6-41.5-2.4-35.6-2.2-4.3 43.7 33.1 3.3-1.4 21.1 21.7 1 40.6 1.2 43.8.6h4.5l-2.2-3-2.6-3.9.1-2.3-1.4-2.7-1.9-5.2-.4-6.7-1.4-4.1-.5-5-2.3-3.7-1-4.7-2.8-7.9-1-5.3z ">
                                                        <title>Nebraska</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="NH "
                                                        d="M881.7 141.3l1.1-3.2-2.7-1.2-.5-3.1-4.1-1.1-.3-3-11.7-37.48-.7.08-.6 1.6-.6-.5-1-1-1.5 1.9-.2 2.29.5 8.41 1.9 2.8v4.3l-3.9 4.8-2.4.9v.7l1.1 1.9v8.6l-.8 9.2-.2 4.7 1 1.4-.2 4.7-.5 1.5 1 1.1 5.1-1.2 13.8-3.5 1.7-2.9 4-1.9z ">
                                                        <title>New Hampshire</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="NJ "
                                                        d="M823.7 228.3l.1-1.5 2.7-1.3 1.7-2.8 1.7-2.4 3.3-3.2v-1.2l-6.1-4.1-1-2.7-2.7-.3-.1-.9-.7-2.2 2.2-1.1.2-2.9-1.3-1.3.2-1.2 1.9-3.1V193l2.5-3.1 5.6 2.5 6.4 1.9 2.5 1.2.1 1.8-.5 2.7.4 4.5-2.1 1.9-1.1 1 .5.5 2.7-.3 1.1-.8 1.6
                                                3.4.2 9.4.6 1.1-1.1 5.5-3.1 6.5-2.7 4-.8 4.8-2.1 2.4h-.8l-.3-2.7.8-1-.2-1.5-4-.6-4.8-2.3-3.2-2.9-1-2z ">
                                                        <title>New Jersey</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="NM "
                                                        d="M270.2 429.4l-16.7-2.6-1.2 9.6-15.8-2 6-39.7 7-53.2 4.4-30.9 34 3.9 37.4 4.4 32 2.8-.3 10.8-1.4-.1-7.4 97.7-28.4-1.8-38.1-3.7.7 6.3z ">
                                                        <title>New Mexico</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="NV "
                                                        d="M123.1 173.6l38.7 8.5 26 5.2-10.6 53.1-5.4 29.8-3.3 15.5-2.1 11.1-2.6 16.4-1.7 3.1-1.6-.1-1.2-2.6-2.8-.5-1.3-1.1-1.8.1-.9.8-1.8 1.3-.3 7.3-.3 1.5-.5 12.4-1.1 1.8-16.7-25.5-42.1-62.1-12.43-19 8.55-32.6 8.01-31.3z ">
                                                        <title>Nevada</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="NY "
                                                        d="M843.4 200l.5-2.7-.2-2.4-3-1.5-6.5-2-6-2.6-.6-.4-2.7-.3-2-1.5-2.1-5.9-3.3-.5-2.4-2.4-38.4 8.1-31.6 6-.5-6.5 1.6-1.2 1.3-1.1 1-1.6 1.8-1.1 1.9-1.8.5-1.6 2.1-2.7 1.1-1-.2-1-1.3-3.1-1.8-.2-1.9-6.1 2.9-1.8 4.4-1.5 4-1.3 3.2-.5
                                                6.3-.2 1.9 1.3 1.6.2 2.1-1.3 2.6-1.1 5.2-.5 2.1-1.8 1.8-3.2 1.6-1.9h2.1l1.9-1.1.2-2.3-1.5-2.1-.3-1.5 1.1-2.1v-1.5h-1.8l-1.8-.8-.8-1.1-.2-2.6 5.8-5.5.6-.8 1.5-2.9 2.9-4.5 2.7-3.7 2.1-2.4 2.4-1.8 3.1-1.2 5.5-1.3 3.2.2 4.5-1.5 7.4-2.2.7 4.9 2.4 6.5.8 5-1
                                                4.2 2.6 4.5.8 2-.9 3.2 3.7 1.7 2.7 10.2v5.8l-.6 10.9.8 5.4.7 3.6 1.5 7.3v8.1l-1.1 2.3 2.1 2.7.5.9-1.9 1.8.3 1.3 1.3-.3 1.5-1.3 2.3-2.6 1.1-.6 1.6.6 2.3.2 7.9-3.9 2.9-2.7 1.3-1.5 4.2 1.6-3.4 3.6-3.9 2.9-7.1 5.3-2.6 1-5.8 1.9-4 1.1-1-.4z ">
                                                        <title>New York</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="OH "
                                                        d="M663.8 211.2l1.7 15.5 4.8 41.1 3.9-.2 2.3-.8 3.6 1.8 1.7 4.2 5.4.1 1.8 2h1.7l2.4-1.4 3.1.5 1.5 1.3 1.8-2 2.3-1.4 2.4-.4.6 2.7 1.6 1 2.6 2 .8.2 2-.1 1.2-.6v-2.1l1.7-1.5.1-4.8 1.1-4.2 1.9-1.3 1 .7 1 1.1.7.2.4-.4-.9-2.7v-2.2l1.1-1.4
                                                2.5-3.6 1.3-1.5 2.2.5 2.1-1.5 3-3.3 2.2-3.7.2-5.4.5-5V230l-1.2-3.2 1.2-1.8 1.3-1.2-.6-2.8-4.3-25.6-6.2 3.7-3.9 2.3-3.4 3.7-4 3.9-3.2.8-2.9.5-5.5 2.6-2.1.2-3.4-3.1-5.2.6-2.6-1.5-2.2-1.3z ">
                                                        <title>Ohio</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="OK "
                                                        d="M411.9 334.9l-1.8 24.3-.9 18 .2 1.6 4 3.6 1.7.9h.9l.9-2.1 1.5 1.9 1.6.1.3-.2.2-1.1 2.8 1.4-.4 3.5 3.8.5 2.5 1 4.2.6 2.3 1.6 2.5-1.7 3.5.7 2.2 3.1 1.2.1v2.3l2.1.7 2.5-2.1 1.8.6 2.7.1.7 2.3 4.4 1.8 1.7-.3 1.9-4.2h1.3l1.1
                                                2.1 4.2.8 3.4 1.3 3 .8 1.6-.7.7-2.7h4.5l1.9.9 2.7-1.9h1.4l.6 1.4h3.6l2-1.8 2.3.6 1.7 2.2 3 1.7 3.4.9 1.9 1.2-.3-37.6-1.4-10.9-.1-8.6-1.5-6.6-.6-6.8.1-4.3-12.6.3-46.3-.5-44.7-2.1-41.5-1.8-.4 10.7z ">
                                                        <title>Oklahoma</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="OR "
                                                        d="M67.44 158.9l28.24 7.2 27.52 6.5 17 3.7 8.8-35.1 1.2-4.4 2.4-5.5-.7-1.3-2.5.1-1.3-1.8.6-1.5.4-3.3 4.7-5.7 1.9-.9.9-.8.7-2.7.8-1.1 3.9-5.7 3.7-4 .2-3.26-3.4-2.49-1.2-4.55-13.1-3.83L132.9 85l-14.8.37-1.1-1.31-5.1 1.84-4.5-.48-2.4-1.58-1.3.54-4.68-.29-1.96-1.43-4.84-1.77-1.1-.07-4.45-1.27-1.76
                                                1.52-6.26-.24-5.31-3.85.21-9.28-2.05-3.5-4.1-.6-.7-2.5-2.4-.5-5.8 2.1-2.3 6.5-3.2 10-3.2 6.5-5 14.1-6.5 13.6-8.1 12.6-1.9 2.9-.8 8.6-1.3 6 2.71 3.5z ">
                                                        <title>Oregon</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="PA " d="M736.6 192.2l1.3-.5 5.7-5.5.7 6.9 33.5-6.5 36.9-7.8 2.3 2.3 3.1.4 2 5.6 2.4 1.9 2.8.4.1.1-2.6 3.2v3.1l-1.9 3.1-.2 1.9 1.3 1.3-.2 1.9-2.4 1.1 1 3.4.2 1.1 2.8.3.9 2.5 5.9 3.9v.4l-3.1 3-1.5 2.2-1.7 2.8-2.7 1.2-1.4.3-2.1
                                                1.3-1.6 1.4-22.4 4.3L757 241l-11.3 1.4-3.9.7-5.1-22.4-4.3-25.9z ">
                                                        <title>Pennsylvania</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="RI "
                                                        d="M873.6 175.7l-.8-4.4-1.6-6 5.7-1.5 1.5 1.3 3.4 4.3 2.8 4.4-2.8 1.4-1.3-.2-1.1 1.8-2.4 1.9-2.8 1.1z ">
                                                        <title>Rhode Island</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="SC "
                                                        d="M759 413.6l-2.1-1-1.9-5.6-2.5-2.3-2.5-.5-1.5-4.6-3-6.5-4.2-1.8-1.9-1.8-1.2-2.6-2.4-2-2.3-1.3-2.2-2.9-3.2-2.4-4.4-1.7-.4-1.4-2.3-2.8-.5-1.5-3.8-5.4-3.4.1-3.9-2.5-1.2-1.2-.2-1.4.6-1.6 2.7-1.3-.8-2 6.4-2.7 9.2-4.5 7.1-.9
                                                16.4-.5 2.3 1.9 1.8 3.5 4.6-.8 12.6-1.5 2.7.8 12.5 7.4 10.1 8.3-5.3 5.4-2.6 6.1-.5 6.3-1.6.8-1.1 2.7-2.4.6-2.1 3.6-2.7 2.7-2.3 3.4-1.6.8-3.6 3.4-2.9.2 1 3.2-5 5.3-2.3 1.6z ">
                                                        <title>South Carolina</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="SD " d="M471 181.1l-.9 3.2.4 3 2.6 2-1.2 5.4-1.8 4.1 1.5 3.3.7 1.1-1.3.1-.7-1.6-.6-2-3.3-1.8-4.8-1.5-2.5-1.3-2.9.1-3.9.4-3.8 1.2-5.3-3.8-2.7-2.4-10.9.8-41.5-2.4-35.6-2.2L354 162l2.8-34 .4-5 56.9 3.9 56.9 1.7v2.7l-1.3 1.5-2 1.5-.1
                                                2.2 1.1 2.2 4.1 3.4.5 2.7v35.9z ">
                                                        <title>South Dakota</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="TN "
                                                        d="M670.8 359.6l-13.1 1.2-23.3 2.2-37.6 2.7-11.8.4.9-.6.9-4.5-1.2-3.6 3.9-2.3.4-2.5 1.2-4.3 3-9.5.5-5.6.3-.2 12.3-.2 13.6-.8.1-3.9 3.5-.1 30.4-3.3 54-5.2 10.3-1.5 7.6-.2 2.4-1.9 1.3.3-.1 3.3-.4 1.6-2.4 2.2-1.6 3.6-2-.4-2.4.9-2.2
                                                3.3-1.4-.2-.8-1.2-1.1.4-4.3 4-.8 3.1-4.2 2.2-4.3 3.6-3.8 1.5-4.4 2.8-.6 3.6-2.5.5-2 1.7-.2 4.8z ">
                                                        <title>Tennessee</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="TX "
                                                        d="M282.8 425.6l37 3.6 29.3 1.9 7.4-97.7 54.4 2.4-1.7 23.3-1 18 .2 2 4.4 4.1 2 1.1h1.8l.5-1.2.7.9 2.4.2 1.1-.6v-.2l1 .5-.4 3.7 4.5.7 2.4.9 4.2.7 2.6 1.8 2.8-1.9 2.7.6 2.2 3.1.8.1v2.1l3.3 1.1 2.5-2.1 1.5.5 2.1.1.6 2.1 5.2
                                                2 2.3-.5 1.9-4h.1l1.1 1.9 4.6.9 3.4 1.3 3.2 1 2.4-1.2.7-2.3h3.6l2.1 1 3-2h.4l.5 1.4h4.7l1.9-1.8 1.3.4 1.7 2.1 3.3 1.9 3.4 1 2.5 1.4 2.7 2 3.1-1.2 2.1.8.7 20 .7 9.5.6 4.1 2.6 4.4.9 4.5 4.2 5.9.3 3.1.6.8-.7 7.7-2.9 4.8 1.3 2.6-.5 2.4-.8 7.2-1.3 3 .3 4.2-5.6
                                                1.6-9.9 4.5-1 1.9-2.6 1.9-2.1 1.5-1.3.8-5.7 5.3-2.7 2.1-5.3 3.2-5.7 2.4-6.3 3.4-1.8 1.5-5.8 3.6-3.4.6-3.9 5.5-4 .3-1 1.9 2.3 1.9-1.5 5.5-1.3 4.5-1.1 3.9-.8 4.5.8 2.4 1.8 7 1 6.1 1.8 2.7-1 1.5-3.1 1.9-5.7-3.9-5.5-1.1-1.3.5-3.2-.6-4.2-3.1-5.2-1.1-7.6-3.4-2.1-3.9-1.3-6.5-3.2-1.9-.6-2.3.6-.6.3-3.4-1.3-.6-.6-1
                                                1.3-4.4-1.6-2.3-3.2-1.3-3.4-4.4-3.6-6.6-4.2-2.6.2-1.9-5.3-12.3-.8-4.2-1.8-1.9-.2-1.5-6-5.3-2.6-3.1v-1.1l-2.6-2.1-6.8-1.1-7.4-.6-3.1-2.3-4.5 1.8-3.6 1.5-2.3 3.2-1 3.7-4.4 6.1-2.4 2.4-2.6-1-1.8-1.1-1.9-.6-3.9-2.3v-.6l-1.8-1.9-5.2-2.1-7.4-7.8-2.3-4.7v-8.1l-3.2-6.5-.5-2.7-1.6-1-1.1-2.1-5-2.1-1.3-1.6-7.1-7.9-1.3-3.2-4.7-2.3-1.5-4.4-2.6-2.9-1.7-.5zm174.4
                                                141.7l-.6-7.1-2.7-7.2-.6-7 1.5-8.2 3.3-6.9 3.5-5.4 3.2-3.6.6.2-4.8 6.6-4.4 6.5-2 6.6-.3 5.2.9 6.1 2.6 7.2.5 5.2.2 1.5z ">
                                                        <title>Texas</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="UT "
                                                        d="M228.4 305.9l24.6 3.6 1.9-13.7 7-50.5 2.3-22-32.2-3.5 2.2-13.1 1.8-10.6-34.7-6.1-12.5-2.5-10.6 52.9-5.4 30-3.3 15.4-1.7 9.2z ">
                                                        <title>Utah</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="VA "
                                                        d="M834.7 265.2l-.2 2.8-2.9 3.8-.4 4.6.5 3.4-1.8 5-2.2 1.9-1.5-4.6.4-5.4 1.6-4.2.7-3.3-.1-1.7zm-60.3 44.6l-38.6 5.6-4.8-.1-2.2-.3-2.5 1.9-7.3.1-10.3 1.6-6.7.6 4.1-2.6 4.1-2.3v-2.1l5.7-7.3 4.1-3.7 2.2-2.5 3.6 4.3 3.8.9 2.7-1
                                                2-1.5 2.4 1.2 4.6-1.3 1.7-4.4 2.4.7 3.2-2.3 1.6.4 2.8-3.2.2-2.7-.8-1.2 4.8-10.5 1.8-5.2.5-4.7.7-.2 1.1 1.7 1.5 1.2 3.9-.2 1.7-8.1 3-.6.8-2.6 2.8-2.2 1.1-2.1 1.8-4.3.1-4.6 3.6 1.4 6.6 3.1.3-5.2 3.4 1.2-.6 2.9 8.6 3.1 1.4 1.8-.8 3.3-1.3 1.3-.5 1.7.5 2.4
                                                2 1.3 3.9 1.4 2.9 1 4.9.9 2.2 2.1 3.2.4.9 1.2-.4 4.7 1.4 1.1-.5 1.9 1.2.8-.2 1.4-2.7-.1.1 1.6 2.3 1.5.1 1.4 1.8 1.8.5 2.5-2.6 1.4 1.6 1.5 5.8-1.7 3.7 6.2z ">
                                                        <title>Virginia</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="VT "
                                                        d="M832.7 111.3l2.4 6.5.8 5.3-1 3.9 2.5 4.4.9 2.3-.7 2.6 3.3 1.5 2.9 10.8v5.3l11.5-2.1-1-1.1.6-1.9.2-4.3-1-1.4.2-4.7.8-9.3v-8.5l-1.1-1.8v-1.6l2.8-1.1 3.5-4.4v-3.6l-1.9-2.7-.3-5.79-26.1 6.79z ">
                                                        <title>Vermont</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank "></a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="WA "
                                                        d="M74.5 67.7l-2.3-4.3-4.1-.7-.4-2.4-2.5-.6-2.9-.5-1.8 1-2.3-2.9.3-2.9 2.7-.3 1.6-4-2.6-1.1.2-3.7 4.4-.6-2.7-2.7-1.5-7.1.6-2.9v-7.9l-1.8-3.2 2.3-9.4 2.1.5 2.4 2.9 2.7 2.6 3.2 1.9 4.5 2.1 3.1.6 2.9 1.5 3.4 1 2.3-.2V22l1.3-1.1
                                                2.1-1.3.3 1.1.3 1.8-2.3.5-.3 2.1 1.8 1.5 1.1 2.4.6 1.9 1.5-.2.2-1.3-1-1.3-.5-3.2.8-1.8-.6-1.5V19l1.8-3.6-1.1-2.6L91.9 8l.3-.8 1.4-.8L98 7.9l9.7 2.7 8.6 1.9 20 5.7 23 5.7 15 3.49-4.8 17.56-4.5 20.83-3.4 16.25-.4 9.18-12.9-3.72-15.3-3.47-14.5.32-1.1-1.53-5.7
                                                2.09-3.9-.42-2.6-1.79-1.7.65-4.15-.25-1.72-1.32-5.16-1.82-1.18-.16-4.8-1.39-1.92 1.65-5.65-.25-4.61-3.35zm9.6-55.4l2-.2.5 1.4 1.5-1.6h2.3l.8 1.5-1.5 1.7.6.8-.7 2-1.4.4s-.9.1-.9-.2c0-.3 1.5-2.6 1.5-2.6l-1.7-.6-.3 1.5-.7.6-1.5-2.3z ">
                                                        <title>Washington</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="WI "
                                                        d="M541.4 109.9l2.9.5 2.9-.6 7.4-3.2 2.9-1.9 2.1-.8 1.9 1.5-1.1 1.1-1.9 3.1-.6 1.9 1 .6 1.8-1 1.1-.2 2.7.8.6 1.1 1.1.2.6-1.1 4 5.3 8.2 1.2 8.2 2.2 2.6 1.1 12.3 2.6 1.6 2.3 3.6 1.2L609 138l1.6 1.4 1.5.9-1.1 2.3-1.8 1.6-2.1
                                                4.7-1.3 2.4.2 1.8 1.5.3 1.1-1.9 1.5-.8.8-2.3 1.9-1.8 2.7-4 4.2-6.3.8-.5.3 1-.2 2.3-2.9 6.8-2.7 5.7-.5 3.2-.6 2.6.8 1.3-.2 2.7-1.9 2.4-.5 1.8.6 3.6.6 3.4-1.5 2.6-.8 2.9-1 3.1 1.1 2.4.6 6.1 1.6 4.5-.2 3-15.9 1.8-17.5 1H567l-.7-1.5-2.9-.4-2.6-1.3-2.3-3.7-.3-3.6
                                                2-2.9-.5-1.4-2.1-2.2-.8-3.3-.6-6.8-2.1-2.5-7-4.5-3.8-5.4-3.4-1-2.2-2.8h-3.2l-2.9-3.3-.5-6.5.1-3.8 1.5-3.1-.8-3.2-2.5-2.8 1.8-5.4 5.2-3.8 1.6-1.9-.2-8.1.2-2.8 2.4-2.8z ">
                                                        <title>Wisconsin</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="WV " d="M758.9 254.3l5.8-6 2.6-.8 1.6-1.5 1.5-2.2 1.1.3 3.1-.2 4.6-3.6 1.5-.5 1.3 1 2.6 1.2 3 3-.4 4.3-5.4-2.6-4.8-1.8-.1 5.9-2.6 5.7-2.9 2.4-.8 2.3-3 .5-1.7 8.1-2.8.2-1.1-1-1.2-2-2.2.5-.5 5.1-1.8 5.1-5 11 .9 1.4-.1 2-2.2 2.5-1.6-.4-3.1
                                                2.3-2.8-.8-1.8 4.9-3.8 1-2.5-1.3-2.5 1.9-2.3.7-3.2-.8-3.8-4.5-3.5-2.2-2.5-2.5-2.9-3.7-.5-2.3-2.8-1.7-.6-1.3-.2-5.6.3.1 2.4-.2 1.8-1V275l1.7-1.5.1-5.2.9-3.6 1.1-.7.4.3 1 1.1 1.7.5 1.1-1.3-1-3.1v-1.6l3.1-4.6 1.2-1.3 2 .5 2.6-1.8 3.1-3.4 2.4-4.1.2-5.6.5-4.8v-4.9l-1.1-3
                                                .9-1.3.8-.7 4.3 19.3 4.3-.8 11.2-1.3z ">
                                                        <title>West Virginia</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <path id="WY "
                                                        d="M353 161.9l-1.5 25.4-4.4 44-2.7-.3-83.3-9.1-27.9-3 2-12 6.9-41 3.8-24.2 1.3-11.2 48.2 7 59.1 6.5z ">
                                                        <title>Wyoming</title>
                                                    </path>
                                                </a>
                                                <a xlink:title="Kandy " xlink:href="#" target="_blank ">
                                                    <g id="DC ">
                                                        <title>District of Columbia</title>
                                                        <path id="DC1 "
                                                            d="M801.8 253.8l-1.1-1.6-1-.8 1.1-1.6 2.2 1.5z " />
                                                        <circle id="DC2 " stroke="#FFFFFF " stroke-width="1.5 "
                                                            cx="801.3 " cy="251.8 " r="5 " opacity="1 " />
                                                        <!-- Set opacity to "0 " to hide DC circle -->
                                                    </g>
                                                </a>
                                                <g>



                                                    <g id="Group_218" x="348" y="500">
                                                        <g id="Group_217" data-name="Group 217"
                                                            transform="translate(0 4.108)">
                                                            <path id="Path_n" data-name="Path 3234"
                                                                d="M1115.86,414.39a15.726,15.726,0,0,1,8.062,5.115,24.325,24.325,0,0,1,5.588,12.3c.236,1.4.323,2.836.471,4.256.017.158,0,.318,0,.512h-46.4c0-.475-.022-.93,0-1.38a26.553,26.553,0,0,1,4.232-13.407,17.221,17.221,0,0,1,8.061-6.834c.558-.22,1.143-.386,1.747-.587.691,1.451,1.361,2.852,2.029,4.257q3.277,6.886,6.562,13.769c.107.22.357.372.539.555a2.512,2.512,0,0,0,.5-.544q4.2-8.792,8.383-17.589C1115.7,414.681,1115.773,414.555,1115.86,414.39Zm3.461,15.239c.616,0,1.171.014,1.726,0a.842.842,0,0,0,.9-.936c.008-.328,0-.656,0-.983-.006-.71-.3-1-1.028-1.008-.52,0-1.038,0-1.53,0a.609.609,0,0,1-.063-.148c0-.491-.006-.983-.012-1.476-.008-.706-.31-1-1.028-1.01-.278,0-.558,0-.838,0-.742,0-1.044.3-1.051,1.04-.005.518,0,1.036,0,1.594-.6,0-1.143-.006-1.682,0a.861.861,0,0,0-.954.939c-.008.3,0,.591,0,.885,0,.8.3,1.1,1.1,1.1.5,0,1,0,1.451,0,.06.088.078.1.078.114,0,.508,0,1.017.012,1.525a.864.864,0,0,0,.961.98c.329,0,.656,0,.986,0a.854.854,0,0,0,.969-.965C1119.327,430.762,1119.321,430.24,1119.321,429.63Z"
                                                                transform="translate(-1083.574 -387.487)"
                                                                fill-rule="evenodd" fill="url(#linear-gradient)" />
                                                            <path id="Path_n" data-name="Path 3235"
                                                                d="M1098.487,388.652a12.734,12.734,0,1,1,12.546,12.746A12.73,12.73,0,0,1,1098.487,388.652Z"
                                                                transform="translate(-1088.053 -375.951)"
                                                                fill-rule="evenodd" fill="url(#linear-gradient)" />
                                                            <path id="Path_n" data-name="Path 3236"
                                                                d="M1115.008,430.452c-1.283-2.8-2.546-5.534-3.789-8.282a.781.781,0,0,1,.044-.579c.579-1.212,1.192-2.41,1.767-3.624a.673.673,0,0,0-.043-.547c-.307-.485-.681-.927-.985-1.411a.933.933,0,0,1-.11-.614,13.522,13.522,0,0,1,.383-1.527.435.435,0,0,1,.306-.264c1.7.03,3.4.078,5.18.127.136.535.314,1.2.461,1.867a.594.594,0,0,1-.133.387c-.316.46-.635.92-.982,1.356a.564.564,0,0,0-.065.7q.906,1.774,1.763,3.573a.765.765,0,0,1,.03.575c-1.222,2.7-2.464,5.4-3.7,8.1A1.312,1.312,0,0,1,1115.008,430.452Z"
                                                                transform="translate(-1091.866 -387.259)"
                                                                fill-rule="evenodd" fill="url(#linear-gradient)" />
                                                            <path id="Path_n" data-name="Path 3237"
                                                                d="M1132.278,433.793c0,.61.006,1.132,0,1.655a.854.854,0,0,1-.969.965c-.329,0-.657,0-.986,0a.864.864,0,0,1-.961-.98c-.011-.509-.007-1.017-.012-1.525,0-.014-.018-.026-.078-.114-.448,0-.949,0-1.451,0-.8-.006-1.094-.3-1.1-1.1,0-.295-.006-.59,0-.885a.861.861,0,0,1,.954-.939c.539-.008,1.078,0,1.682,0,0-.558,0-1.075,0-1.594.006-.74.309-1.037,1.051-1.04.28,0,.559,0,.837,0,.718,0,1.02.3,1.028,1.01.006.493.007.985.012,1.476a.6.6,0,0,0,.063.148c.492,0,1.01,0,1.53,0,.724.006,1.022.3,1.028,1.008,0,.327.006.655,0,.983a.842.842,0,0,1-.9.936C1133.449,433.807,1132.894,433.793,1132.278,433.793Z"
                                                                transform="translate(-1096.532 -391.65)" fill="#29abe2"
                                                                stroke="#dfdfdf" stroke-width="0.75"
                                                                fill-rule="evenodd" />
                                                        </g>
                                                        <text id="_3" data-name="3" transform="translate(18.541 23)"
                                                            fill="#00649a" stroke="#00649a" stroke-width="0.75"
                                                            font-size="18" font-family="SegoeUI, Segoe UI">
                                                            <tspan x="0" y="0">3</tspan>
                                                        </text>
                                                    </g>


                                                </g>

                                                <path id="frames " fill="none " style="fill:none!important; "
                                                    stroke="#A9A9A9 " stroke-width="2 "
                                                    d="M215 493v55l36 45M0 425h147l68 68h85l54 54v46 " />
                                            </svg>


                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 ">
                                            <div class="task-box">
                                                <div class="container-fluid">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="row">
                                                                <div class="col-3 p-0 "><img
                                                                        src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/img.svg"
                                                                        alt="" class="w-100"></div>
                                                                <div class="col-9 p-0 h-user relative">
                                                                    <div class="flex">
                                                                        <div class="w-100 text-left pl-2">
                                                                            <p class="fs-14">DR. DANNY TREJO</p>
                                                                            <p class="fs-12">Costco, C.A</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="overlay-data">
                                                                        <div class="text-white w-100 text-center">
                                                                            <p class="fs-12">johndoe@workcare.ca</p>
                                                                            <hr class="white-hr">
                                                                            <p class="fs-12">+1 259 326 12456</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 ">
                                            <div class="task-box">
                                                <div class="container-fluid">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="row">
                                                                <div class="col-3 p-0 "><img
                                                                        src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/img.svg"
                                                                        alt="" class="w-100"></div>
                                                                <div class="col-9 p-0 h-user relative">
                                                                    <div class="flex">
                                                                        <div class="w-100 text-left pl-2">
                                                                            <p class="fs-14">DR. DANNY TREJO</p>
                                                                            <p class="fs-12">Costco, C.A</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="overlay-data">
                                                                        <div class="text-white w-100 text-center">
                                                                            <p class="fs-12">johndoe@workcare.ca</p>
                                                                            <hr class="white-hr">
                                                                            <p class="fs-12">+1 259 326 12456</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 ">
                                            <div class="task-box">
                                                <div class="container-fluid">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="row">
                                                                <div class="col-3 p-0 "><img
                                                                        src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/img.svg"
                                                                        alt="" class="w-100"></div>
                                                                <div class="col-9 p-0 h-user relative">
                                                                    <div class="flex">
                                                                        <div class="w-100 text-left pl-2">
                                                                            <p class="fs-14">DR. DANNY TREJO</p>
                                                                            <p class="fs-12">Costco, C.A</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="overlay-data">
                                                                        <div class="text-white w-100 text-center">
                                                                            <p class="fs-12">johndoe@workcare.ca</p>
                                                                            <hr class="white-hr">
                                                                            <p class="fs-12">+1 259 326 12456</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </window-dashboard>
                        <!-- <window-dashboard>
                            <div class="head-component" style="margin: auto;margin-top: 10px;">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="compo-head">
                                                <span class="spl-mouse">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/drag.svg"
                                                        alt="">
                                                </span>
                                                <span>
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/min.svg"
                                                        alt="">
                                                </span>
                                                <span onclick="SimpleSearch(this);" id="Appointments">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/search-white.svg"
                                                        alt="">

                                                </span>
                                                <input id="txtAppointments" type="text" class="component-search">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-white head-p">Request</p>
                                        </div>
                                    </div>



                                </div>
                            </div>

                            <div class="body-compo">
                                <div class="container-fluid ">
                                    <div class="task-box ">
                                        <div class="container-fluid ">
                                            <div class="row ">
                                                <div class="col-8 ">
                                                    <div class="row ">
                                                        <div class="col-4 pl-0 ">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/img.svg"
                                                                class="w-100 " alt=" ">
                                                        </div>
                                                        <div class="col-8 ">
                                                            <div class="flex ">
                                                                <div class="w-100 text-left ">
                                                                    <p class="fs-14 ">Ms. Martha Wayne</p>
                                                                    <p class="fs-12 ">Costco, C.A</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-4 ">
                                                    <div class="row h-100 ">
                                                        <div class="col-8  date-big h-100 pt-2 ">
                                                            <h5>4:00 PM</h5>
                                                            <h5 class="b-700 fs-28">08</h5>
                                                            <p>Thurs</p>
                                                        </div>
                                                        <div class="col-4 p-0 pt-3 ">
                                                            <div class="text-center ">
                                                                <div class="pt-3 ">
                                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/msg.svg"
                                                                        alt=" ">
                                                                </div>
                                                                <div class="pt-3 ">
                                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/call.svg"
                                                                        alt=" ">
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="task-box ">
                                        <div class="container-fluid ">
                                            <div class="row ">
                                                <div class="col-8 ">
                                                    <div class="row ">
                                                        <div class="col-4 pl-0 ">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/img.svg"
                                                                class="w-100 " alt=" ">
                                                        </div>
                                                        <div class="col-8 ">
                                                            <div class="flex ">
                                                                <div class="w-100 text-left ">
                                                                    <p class="fs-14 ">Mr. Tony Stark</p>
                                                                    <p class="fs-12 ">Stark Corporation, N.Y</p>
                                                                    <p class="fs-12 ">ME&T</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-4 ">
                                                    <div class="row h-100 ">
                                                        <div class="col-8  date-big h-100 pt-2 ">
                                                            <h5>4:00 PM</h5>
                                                            <h5 class="b-700 fs-28">08</h5>
                                                            <p>Thurs</p>
                                                        </div>
                                                        <div class="col-4 p-0 pt-3 ">
                                                            <div class="text-center ">
                                                                <div class="pt-3 ">
                                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/msg.svg"
                                                                        alt=" ">
                                                                </div>
                                                                <div class="pt-3 ">
                                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/call.svg"
                                                                        alt=" ">
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="task-box ">
                                        <div class="container-fluid ">
                                            <div class="row ">
                                                <div class="col-8 ">
                                                    <div class="row ">
                                                        <div class="col-4 pl-0 ">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/img.svg"
                                                                class="w-100 " alt=" ">
                                                        </div>
                                                        <div class="col-8 ">
                                                            <div class="flex ">
                                                                <div class="w-100 text-left ">
                                                                    <p class="fs-14 ">Ms. Martha Wayne</p>
                                                                    <p class="fs-12 ">Costco, C.A</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-4 ">
                                                    <div class="row h-100 ">
                                                        <div class="col-8  date-big h-100 pt-2 ">
                                                            <h5>4:00 PM</h5>
                                                            <h5 class="b-700 fs-28">08</h5>
                                                            <p>Thurs</p>
                                                        </div>
                                                        <div class="col-4 p-0 pt-3 ">
                                                            <div class="text-center ">
                                                                <div class="pt-3 ">
                                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/msg.svg"
                                                                        alt=" ">
                                                                </div>
                                                                <div class="pt-3 ">
                                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/call.svg"
                                                                        alt=" ">
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </window-dashboard> -->
                        <window-dashboard>
                            <div class="head-component" style="margin: auto;margin-top: 10px;">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="compo-head">
                                                <span class="spl-mouse">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/drag.svg"
                                                        alt="">
                                                </span>
                                                <span>
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/min.svg"
                                                        alt="">
                                                </span>
                                                <span onclick="SimpleSearch(this);" id="Appointments">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/search-white.svg"
                                                        alt="">

                                                </span>
                                                <input id="txtAppointments" type="text" class="component-search">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-white head-p">Request</p>
                                        </div>
                                    </div>



                                </div>
                            </div>

                            <div class="body-compo">
                                <div class="container-fluid ">
                                <?php
                                    $from_date=$to_date=date('Y-m-d');
                                  

                                     //  form_apptstatus > for None
                                     $form_apptstatus='-';
                                     $appointments = fetchAppointments($from_date, $to_date, $patient, $provider, $facility, $form_apptstatus, $with_out_provider, $with_out_facility, $form_apptcat,$search_like);

                                     if ($show_available_times) {
                                         $availableSlots = getAvailableSlots($from_date, $to_date, $provider, $facility);
                                         $appointments = array_merge($appointments, $availableSlots);
                                     }

                                  
                                     $appointments = sortAppointments($appointments, $form_orderby);
                                     $pid_list = array();  // Initialize list of PIDs for Superbill option
                                     $apptdate_list = array(); // same as above for the appt details
                                     $totalAppontments = count($appointments);
                                    //  var_dump($appointments);
                                     foreach ($appointments as $appointment) {
                                        array_push($pid_list, $appointment['pid']);
                                        array_push($apptdate_list, $appointment['pc_eventDate']);
                                        $patient_id = $appointment['pid'];
                                        $docname  = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];
                                
                                        $errmsg  = "";
                                        $pc_apptstatus = $appointment['pc_apptstatus'];

                                // print_r($appointment);
                                        ?>
            

                                    <div class="task-box ">
                                        <div class="container-fluid ">
                                            <div class="row ">
                                                <div class="col-8 ">
                                                    <div class="row ">
                                                        <div class="col-4 pl-0 ">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/img.svg"
                                                                class="w-100 " alt=" ">
                                                        </div>
                                                        <div class="col-8 ">
                                                            <div class="flex ">
                                                                <div class="w-100 text-left ">
                                                                    <p class="fs-14"> <?php echo text($docname);?>
                                                                    <p class="fs-14 "><?php echo text($appointment['title']);?> <?php echo text($appointment['fname'] . " " . $appointment['lname']);?></p>
                                                                    <p class="fs-12 "><?php echo text($appointment['city']);?>, <?php echo text($appointment['state']);?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-4 ">
                                                    <div class="row h-100 ">
                                                        <div class="col-8  date-big h-100 pt-2 ">
                                                        <?php
                                                        $starttime=$appointment['pc_startTime'];
                                                        $startdate=$appointment['pc_eventDate'];
                                                        ?>
                                                      
                                                            <h5><?php echo date('G:i',strtotime($starttime)); ?>&nbsp;<?php echo text(date('a', $starttime)); ?></h5>
                                                            <h5 class="b-700 fs-28"><?php echo date('d',strtotime($startdate)); ?></h5>
                                                            <p><?php echo date('D',strtotime($startdate)); ?></p>
                                                        </div>
                                                        <div class="col-4 p-0 pt-3 ">
                                                            <div class="text-center ">
                                                                <div class="pt-3 ">
                                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/msg.svg"
                                                                        alt=" ">
                                                                </div>
                                                                <div class="pt-3 ">
                                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/call.svg"
                                                                        alt=" ">
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
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

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- section for employee dashboard -->
    <section >
        <div class="body-content body-content2">
            <div class="pl-1 pr-1">
                <div class="row ">
                    <div class="col-lg-4 e-info ">
                        <div class="container-fluid pt-4 pb-4">
                            <div class="row">
                                <div class="col-4 p-1">
                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/user.svg"
                                        class="w-100" alt="">
                                </div>
                                <div class="col-8 p-1 text-right">
                                    <p class="fs-20 b-700">Mr. THOMAS WAYNE</p>
                                    <p class="fs-16">Male, 50 years</p>
                                    <p class="fs-16">Wayne Corporation, C.A</p>
                                </div>
                            </div>
                            <div>
                                <div class="row pt-4">

                                    <div class="col-8 p-1 ">
                                        <div class="doctor-name">
                                            <p class="fs-14">Primary Treatment Provider</p>
                                            <!-- <p class="fs-18">DR. MILO MURPHY</p> -->
                                            <input type="text" value="DR. MILO MURPHY" class="form-control b-700">
                                        </div>
                                    </div>
                                    <div class="col-4 p-1">
                                        <div class="flex">
                                            <div>
                                                <a href="#"><img
                                                        src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/add.svg"
                                                        alt=""></a>
                                                <a href="#"><img
                                                        src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/editdoc.svg"
                                                        alt=""></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="doc-edit">
                                    <p>Alerts</p>
                                    <textarea name="" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="doc-edit">
                                    <p>Drugs Screen Needed</p>
                                    <textarea name="" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="doc-edit">
                                    <p>Client Note</p>
                                    <textarea name="" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="doc-edit">
                                    <p>Allergies</p>
                                    <textarea name="" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 vh-over-flow">
                        <div class="container-fluid">
                            <div class="row pb-4">
                                <div class="info-update">
                                    <div class="">
                                        <input type="radio" class="" name="optradio" id="all">
                                         <label style="font-size: 13px;">
                                            All
                                        </label>
                                    </div>
                                    <div class="">
                                        <input type="radio" class="" name="optradio" id="enc_notes">
                                        <label style="font-size: 13px;">
                                            Encounter Notes
                                        </label>
                                    </div>
                                    <div class=" ">
                                        <input type="radio" class="" name="optradio" id="lab">
                                        <label style="font-size: 13px;">
                                            Labs
                                        </label>
                                    </div>
                                </div>
                                <div class="info-update">
                                    <div class="" >
                                        <input type="radio" class="" name="optradio" id="meds">
                                        <label style="font-size: 13px;">
                                            Meds
                                        </label>
                                    </div>

                                    <div class="">
                                        <input type="radio" class="" name="optradio" id="vital">
                                        <label style="font-size: 13px;">
                                            Vitals
                                        </label>
                                    </div>
                                    <div class=" ">
                                        <input type="radio" class="" name="optradio" id="receipt">
                                        <label style="font-size: 13px;">
                                            Receipts
                                        </label>
                                    </div>
                                    <div class=" ">
                                        <input type="radio" class="" name="optradio" id="other">
                                        <label style="font-size: 13px;">
                                            Others
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="update" id="encounter">
                                <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/notes.svg" class="notes"
                                    alt="">
                                <div class="update-content">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-2 p-0">
                                                <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/user.svg"
                                                    class="w-100" alt="">
                                            </div>
                                            <div class="col-10">
                                                <?php
                                                $res = sqlStatement("select date from pnotes where pid = ? and deleted != 1 and activity = 1 order by date desc limit 1", array($pid));
                                                $result = sqlFetchArray($res);
                                                $date=$result['date'];
                                                
                                                if($date){
                                                ?>
                                                    <p class="fs-9  font-light text-right">| <?php echo time_elapsed_string($date); ?></p>

                                                <?php
                                                }
                                                ?>
                                                <p class="fs-10">Medical Encounter Notes</p>
                                                <hr>
                                                <div>
                                                    <?php


                                                    // refrences from  interface->patient_file->report->custom_report.php
                                                    $res = sqlStatement("select * from pnotes where pid = ? and deleted != 1 and activity = 1 order by date desc", array($pid));
                                                    while ($result = sqlFetchArray($res)) {

                                                        echo "<p class='fs-12'>".$result['body']." </p>";
                                                        echo "<br>";
                                                    
                                                    }

                                                ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                             <!-- medication  -->
                             <div class="update" id="medicationlists">
                                <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/vitals.svg" class="notes"
                                    alt="">
                                <div class="update-content">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-2 p-0">
                                                <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/user.svg"
                                                    class="w-100" alt="">
                                            </div>
                                            <div class="col-10">
                                                <?php
                                                $pid = $_SESSION['pid']; 
                                                $sql = "SELECT * FROM lists WHERE pid = ? AND type = 'medication' ORDER BY date desc LIMIT 1 ";
                                                $res = sqlStatement($sql, array($pid));
                                                $myrow = sqlFetchArray($res);
                                                $date=$myrow['date'];

                                                if($date){
                                                ?>
                                                    <p class="fs-9  font-light text-right">| <?php echo time_elapsed_string($date); ?></p>
                                                    
                                                   
                                                <?php
                                                }
                                                ?>
                                                  <p class="fs-10">Dr.&nbsp;<?php echo text($myrow['referredby']); ?>&nbsp;submitted the Medication</p>  
                                                  <hr>
                                                <div>
                                                  
                                                    <?php
                                                                                                                               
                                                    $sql = "SELECT * FROM lists WHERE pid = ? AND type = 'medication' ORDER BY date desc";
                                                    $res = sqlStatement($sql, array($pid));
                                                   
                                                    if (sqlNumRows($res)>0) {                                        
                                                    
                                                        while ($myrow = sqlFetchArray($res))
                                                        {
                                                    
                                                            $title = $myrow['title'];
                                                            ?>
                                                         
                                                            
                                                            <p class="fs-12"> <?= $title ?> </p>
                                                        <?php
                                                        }
                                                    }
                                                    else
                                                    {
                                                    ?>
                                                        <p class="fs-10">Medications</p>
                                                        <hr>
                                                        <p class="fs-12"> Nothing Recorded </p>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- //medication -->
                            <div class="update" id="vitals">
                                <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/vitals.svg"
                                    class="notes" alt="">
                                <div class="update-content">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-2 p-0">
                                                <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/user.svg"
                                                    class="w-100" alt="">
                                            </div>
                                            <?php 
                                            function US_weight($pounds, $mode = 1)
                                            {
                                            
                                                if ($mode==1) {
                                                    return $pounds . " " . xl('lb') ;
                                                } else {
                                                    $pounds_int=floor($pounds);
                                                    $ounces=round(($pounds-$pounds_int)*16);
                                                    return $pounds_int . " " . xl('lb') . " " . $ounces . " " . xl('oz');
                                                }
                                            }
                                            $pid = $_SESSION['pid'];
                                            $patient_data = getPatientData($pid);
                                            $patient_age = getPatientAge($patient_data['DOB']);

                                                                                         
                                            $result=sqlQuery("SELECT * FROM form_vitals AS FORM_VITALS LEFT JOIN forms AS FORMS ON FORM_VITALS.id = FORMS.form_id WHERE FORM_VITALS.pid=? AND FORMS.deleted != '1' ORDER BY FORM_VITALS.date DESC", array($pid));

                                            if ($result) {
                                                
                                            ?>
                                            <div class="col-10">
                                                <p class="fs-9  font-light text-right">| <?php echo time_elapsed_string($result['date']); ?></p>

                                                <p class="fs-10">Vitals</p>
                                                <hr>
                                                <div>
                                                <?php 
                                               
                                                foreach ($result as $key => $value) {
                                                    if ($key == "id" || $key == "pid" ||
                                                      $key == "user" || $key == "groupname" ||
                                                      $key == "authorized" || $key == "activity" ||
                                                      $key == "date" || $value == "" ||
                                                      $value == "0000-00-00 00:00:00" || $value == "0.0" ) {
                                                        // skip certain data
                                                        continue;
                                                    }
                                        
                                                    if ($value == "on") {
                                                        $value = "yes";
                                                    }
                                        
                                                    $key = ucwords(str_replace("_", " ", $key));
                                        
                                                 
                                        
                                                    if ($key == "Bps") {
                                                        $bps = $value;
                                                        if ($bpd) {
                                                            ?>
                                                            <p class="fs-12">Blood Pressure- <?= text($bps) . "/". text($bpd) ?></p>
                                                            <?php
                                                        } else {
                                                            continue;
                                                        }
                                                    } elseif ($key == "Bpd") {
                                                        $bpd = $value;
                                                        if ($bps) {
                                                            ?>
                                                            <p class="fs-12">Blood Pressure- <?= text($bps) . "/". text($bpd) ?></p>
                                                            <?php
                                                        } else {
                                                            continue;
                                                        }
                                                    
                                                    ?>
                                               
                                                    <?php 
                                                    } 
                                            
                                                    elseif ($key == "Weight") {
                                                        $value=$result['weight'];
                                                        $convValue = number_format($value*0.45359237,2);

                                                        
                                                        $mode=$GLOBALS['us_weight_format'];
                        
                                                        if ($GLOBALS['units_of_measurement'] == 2) {                                                      
                                                        ?>
                                                         <p class="fs-12"> Weight -&nbsp;<?= $convValue ?>&nbsp;kg(<?= US_weight($value,$mode)?>)</p>
                                                       <?php

                                                        } elseif ($GLOBALS['units_of_measurement'] == 3) {
                                                       
                                                        ?>
                                                         <p class="fs-12"> Weight -?&nbsp; <?= US_weight($value, $mode) ?></p> 
                                                         <?php
                                                        } elseif ($GLOBALS['units_of_measurement'] == 4) {
                                                       
                                                        ?>
                                                         <p class="fs-12"> Weight -&nbsp;<?= $convValue ?>&nbsp;kg</p>
                                                         <?php
                                                        } else { 
                                                       
                                                        ?>
                                                        <p class="fs-12"> Weight -&nbsp;<?= US_weight($value, $mode) ?>(<?= $convValue ?>kg)</p>
                                                        
                                                         <?php
                                                        }
                                                    }

                                                    elseif ($key == "Height" || $key == "Waist Circ"  || $key == "Head Circ") {
                                                        $convValue = round(number_format($value*2.54, 2), 1);
                                                       
                                                        if ($GLOBALS['units_of_measurement'] == 2) {
                                                            ?>
                                                            <p class="fs-12"> <?= xlt($key) ?> -<?= text($convValue) ?>&nbsp;cm(&nbsp;<?= text($value) ?>&nbsp;in)</p>
                                                            <?php
                                                        } elseif ($GLOBALS['units_of_measurement'] == 3) {
                                                            ?>
                                                            <p class="fs-12"><?= xlt($key) ?> -<?= text($value) ?>&nbsp;in</p>
                                                            <?php
                                                        } elseif ($GLOBALS['units_of_measurement'] == 4) {
                                                            ?>
                                                            <p class="fs-12"> <?= xlt($key) ?> -<?= text($convValue) ?>&nbsp;cm</p>
                                                            <?php
                                                        } else { // = 1 or not set
                                                            ?>
                                                            <p class="fs-12"> <?= xlt($key) ?> -<?= text($value) ?>&nbsp;in(&nbsp;<?= text($convValue) ?>&nbsp;cm)</p>
                                                            <?php
                                                        }
                                                    } 
                                                    elseif ($key == "Temperature") {
                                                        $convValue = number_format((($value-32)*0.5556), 2);
                                                        // show appropriate units
                                                        if ($GLOBALS['units_of_measurement'] == 2) {
                                                            ?>
                                                        <p class="fs-12"> <?= xlt($key) ?> -<?= text($convValue) ?>&nbsp;C&nbsp;<?= text($value) ?>&nbsp;F</p>
                                                        <?php
                                                        
                                                        } elseif ($GLOBALS['units_of_measurement'] == 3) {
                                                        ?>
                                                            <p class="fs-12"> <?= xlt($key) ?> -<?= text($value) ?>&nbsp;F</p>
                                                            <?php
                                                        } elseif ($GLOBALS['units_of_measurement'] == 4) {
                                                            ?>
                                                            <p class="fs-12"> <?= xlt($key) ?> -<?= text($convValue) ?>&nbsp;C</p>
                                                            <?php
                                                        } else { // = 1 or not set
                                                            ?>
                                                            <p class="fs-12"> <?= xlt($key) ?> -<?= text($value) ?>&nbsp;F&nbsp;<?= text($convValue) ?>&nbsp;C</p>
                                                            <?php
                                                        }
                                                    }
                                                     elseif ($key == "Pulse" || $key == "Respiration"  || $key == "Oxygen Saturation" || $key == "BMI") {
                                                        $value = number_format($value, 0);
                                                        if ($key == "Oxygen Saturation") {
                                                            ?>
                                                            <p class="fs-12"> <?= xlt($key) ?> -<?= text($value) ?>&nbsp;%</p>
                                                            <?php
                                                        } elseif ($key == "BMI") {
                                                            ?>
                                                            <p class="fs-12"> <?= xlt($key) ?> -<?= text($value) ?>&nbsp;kg/m^2</p>
                                                            <?php
                                                        } else { //pulse and respirations
                                                            ?>
                                                            <p class="fs-12"> <?= xlt($key) ?> -<?= text($value) ?>&nbsp;per min</p>
                                                            <?php
                                                        }
                                                    } 
                                                    else {
                                                        ?>
                                                        <!-- <p class="fs-12"> <?= xlt($key) ?> -<?= text($value) ?></p> -->
                                                        <?php
                                                    }
                                                    ?>
                                                  
                                                 <?php 
                                            }
                                             ?>
                                               </div>                                                                                                
                                            </div>
                                    <?php } else { ?>
                                        <div class="col-10">
                                        <p class="fs-10">Vitals</p>
                                                <hr>
                                                <div>
                                                    <p class="fs-12">No vitals have been documented.</p>
                                                    

                                                </div>
                                            </div>
                                    <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="update" id="imaging">
                                <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/image.svg" class="notes"
                                    alt="">
                                <div class="update-content">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-2 p-0">
                                                <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/user.svg"
                                                    class="w-100" alt="">
                                            </div>
                                           
                                                <?php
                                                    $current_user = $_SESSION["authId"];
                                                    $date_filter = '';
                                                        $query_array = array();
                                                    if ($form_from_doc_date) {
                                                        $form_from_doc_date = DateToYYYYMMDD($form_from_doc_date);
                                                        $date_filter = " DATE(d.date) >= ? ";
                                                                array_push($query_array, $form_from_doc_date);
                                                    }

                                                    if ($form_to_doc_date) {
                                                        $form_to_doc_date = DateToYYYYMMDD($form_to_doc_date);
                                                        $date_filter .= " AND DATE(d.date) <= ? ";
                                                                array_push($query_array, $form_to_doc_date);
                                                    }

                                                    // Get the category ID for lab reports.
                                                    $query = "SELECT rght FROM categories WHERE name = ?";
                                                    $catIDRs = sqlQuery($query, array($GLOBALS['lab_results_category_name']));
                                                    $catID = $catIDRs['rght'];

                                                    $query = "SELECT d.*,CONCAT(pd.fname,' ',pd.lname) AS pname,GROUP_CONCAT(n.note ORDER BY n.date DESC SEPARATOR '|') AS docNotes,
                                                        GROUP_CONCAT(n.date ORDER BY n.date DESC SEPARATOR '|') AS docDates FROM documents d
                                                        INNER JOIN patient_data pd ON d.foreign_id = pd.pid
                                                        LEFT JOIN categories_to_documents ctd ON d.id = ctd.document_id AND ctd.category_id = ?
                                                        LEFT JOIN notes n ON d.id = n.foreign_id
                                                        WHERE " . $date_filter . " GROUP BY d.id ORDER BY date DESC";
                                                        array_unshift($query_array, $catID);
                                                    $resultSet = sqlStatement($query, $query_array);
                                                    if (sqlNumRows($resultSet)) {
                                                        $row1 = sqlFetchArray($resultSet);
                                                    ?>
                                                    <div class="col-10">
                                                        <p class="fs-9  font-light text-right">| <?php echo time_elapsed_string($row1['date']); ?></p>

                                                        
                                                        <p class="fs-10">Labs</p>
                                                        <hr>
                                                        <div>
                                                        <?php
                                                        while ($row = sqlFetchArray($resultSet)) {

                                                            $url = $GLOBALS['webroot'] . "/controller.php?document&retrieve&patient_id=" . attr_url($row["foreign_id"]) . "&document_id=" . attr_url($row["id"]) . '&as_file=false';
                                                    ?>
                                                    
                                                    <img src="<?php echo $url; ?>"
                                                        class="w-100" alt="">

                                                    

                                                    <?php
                                                        }
                                                        ?>
                                                        </div>
                                                        </div>
                                                    <?php   
                                                    }else{
                                                    ?>
                                                        <div class="col-10">
                                                            <p class="fs-10">Labs</p>
                                                            <hr>
                                                            <div>
                                                                <p class="fs-12">No Labs have been documented.</p>
                                                            </div>
                                                        </div>
                                                    <?php
                                                    }
                                                    ?> 
                                                    
                                                    

                                                <!-- </div>
                                            </div> -->

                                               
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">

                        <div id="accordion">
                            <div class="">
                                <div class="acco-head" style="font-size: 16px;" data-toggle="collapse" href="#collapseOne">

                                    Visit Over View

                                </div>
                                <div id="collapseOne" class="collapse show" data-parent="#accordion">
                                    <div class="acco-body">
                                        <p class="fs-14 b-700">Type - <span class="b-400">Wokers Comp Follow Up</span>
                                        </p>
                                        <p class="fs-14 b-700">Claims Number - <span class="b-400">WC 24587</span> </p>
                                        <p class="fs-14 b-700">Triage Number -<span class="b-400">21459</span> </p>
                                        <p class="fs-14 b-700">Body Part(s) -<span class="b-400">Lumbar Spine</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="">
                                <div class="acco-head" style="font-size: 16px;" data-toggle="collapse" href="#collapseTwo">

                                    Medications

                                </div>
                                <div id="collapseTwo" class="collapse show" data-parent="#accordion">
                                    <div class="acco-body">
                                        <!-- medication list -->
                                        <?php
                                        $pid = $_SESSION['pid'];                                       
                                        $sql = "SELECT * FROM lists WHERE pid = ? AND type = 'medication' ORDER BY begdate";
                                        $res = sqlStatement($sql, array($pid));
                                        // var_dump($pid);
                                        if (sqlNumRows($res)>0) {                                        
                                         
                                         while ($myrow = sqlFetchArray($res))
                                        {
                                     
                                            $title = $myrow['title'];
                                       ?>
                                        <p class="fs-14"> <?= $title ?> </p>
                                        <?php
                                            }
                                        }
                                        else
                                        {
                                            ?>
                                             <p class="fs-14"> No Results </p>
                                            <?php
                                        }
                                        ?>
                                        <!-- <p class="fs-14"> Quetiapine Actavis .25mg </p>
                                        <p class="fs-14"> Alprax .25 mg</p>
                                        <p class="fs-14"> Haloperidol -</p>
                                        <p class="fs-14"> Xanax </p>
                                        <p class="fs-14"> Dimethyltryptamine -</p> -->
                                        <!-- //medication list -->
                                    </div>
                                </div>
                            </div>
                            <!-- imagingtab -->
                            <div class="">
                                <div class="acco-head" style="font-size: 16px;" data-toggle="collapse" href="#collapseThree">
                                    Imaging

                                </div>
                                <div id="collapseThree" class="collapse" data-parent="#accordion">
                                    <div class="acco-body">
                                        <div class="container-fluid">
                                            
                                            <?php
                                                    $current_user = $_SESSION['pid'];
                                                    $date_filter = '';
                                                        $query_array = array();
                                                    if ($form_from_doc_date) {
                                                        $form_from_doc_date = DateToYYYYMMDD($form_from_doc_date);
                                                        $date_filter = "DATE(d.date) >= ? ";
                                                            array_push($query_array, $form_from_doc_date);
                                                    }

                                                    if ($form_to_doc_date) {
                                                        $form_to_doc_date = DateToYYYYMMDD($form_to_doc_date);
                                                        $date_filter .= " AND DATE(d.date) <= ? ";
                                                                array_push($query_array, $form_to_doc_date);
                                                    }

                                                    // Get the category ID for lab reports.

                                                    $query = "SELECT rght FROM categories WHERE name = ?";
                                                    // $catIDRs = sqlQuery($query, array($GLOBALS['lab_results_category_name']));
                                                    $catIDRs = sqlQuery($query, 'Lab Report');
                                                    $catID = $catIDRs['rght'];

                                                    $query = "SELECT d.*,CONCAT(pd.fname,' ',pd.lname) AS pname,GROUP_CONCAT(n.note ORDER BY n.date DESC SEPARATOR '|') AS docNotes,
                                                        GROUP_CONCAT(n.date ORDER BY n.date DESC SEPARATOR '|') AS docDates FROM documents d
                                                        INNER JOIN patient_data pd ON d.foreign_id = pd.pid
                                                        LEFT JOIN categories_to_documents ctd ON d.id = ctd.document_id AND ctd.category_id = ?
                                                        LEFT JOIN notes n ON d.id = n.foreign_id
                                                        WHERE " . $date_filter . " GROUP BY d.id ORDER BY date DESC";
                                                        array_unshift($query_array, $catID);
                                                    $resultSet = sqlStatement($query, $query_array);
                                                    
                                                    if (sqlNumRows($resultSet)) {
                                                        while ($row = sqlFetchArray($resultSet)) {

                                                            $url = $GLOBALS['webroot'] . "/controller.php?document&retrieve&patient_id=" . attr_url($row["foreign_id"]) . "&document_id=" . attr_url($row["id"]) . '&as_file=false';
                                                    ?>
                                                    <div class="row">                                                  
                                                        <div class="col-8 p-0">
                                                            <img src="<?php echo $url; ?>" class="w-100" alt="">
                                                    
                                                        </div>
                                                        <div class="col-4">
                                                            <p class="b-700 fs-13">Type:</p>
                                                        
                                                            <p class=" fs-13"><?= attr_url($row["docNotes"]) ?></p>
                                                        
                                                            <p class="b-700 fs-13">Date:</p>
                                                            <!-- <p class=" fs-13"><?= $row["docdate"]; ?></p> -->
                                                            <p class=" fs-13"><?= date("d/m/y", strtotime($row["docdate"])); ?></p>
                                                            
                                                            <p class="b-700 fs-13">Part:</p>
                                                            <p class=" fs-13"><?= $row["documentationOf"] ?></p>
                                                        </div>
                                                   
                                                    </div>
                                                    <br>
                                                    <?php
                                                        }
                                                    }
                                                    else{
                                                        ?>
                                                         <div class="col-12">
                                                        <p>No Records.</p>
                                                    </div>
                                                        <?php
                                                    }
                                                    ?> 
                                                
                                                

                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- //imaging -->
                            <div class="">
                                <div class="acco-head" style="font-size: 16px;" data-toggle="collapse" href="#collapsefour">
                                    Physiotherapy
                                </div>
                                <div id="collapsefour" class="collapse" data-parent="#accordion">
                                    <div class="acco-body" style="font-size: 16px;">
                                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                        incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                                    </div>
                                </div>
                            </div>
                            <div class="">
                                <div class="acco-head" style="font-size: 16px;" data-toggle="collapse" href="#collapsefive">
                                    Completed Tasks
                                </div>
                                <div id="collapsefive" class="collapse" data-parent="#accordion">
                                <!-- completed task -->
                                <?php                                   
                                   $count = 0;
                                   $active='0';
                                   $show_all = 'yes';
                                   $result = getPnotesByUser($active, $show_all, $_SESSION['authUser'], false, $sortby = '', $sortorder = '', $begin = '', $listnumber = '');
                                   if (sqlNumRows($result)>0)
                                   {
                                   while ($myrow = sqlFetchArray($result))
                                    {
                                     
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
                                       $count++;                                   

                            ?>

                                    <div class="acco-body" style="font-size: 14px;">
                                        <!-- Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                        incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. -->
                                        <?php echo text($name); ?>&nbsp;-<?php echo text($myrow['user']); ?>
                                    </div>
                                    <?php  } }
                                    else
                                    { ?>
                                    <div class="acco-body" style="font-size: 14px;">
                                        <!-- Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                        incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. -->
                                       Nill
                                    </div>
                                    <?php }  ?>
                                    <!-- //completed task -->
                                </div>
                            </div>
                            <div class="">
                                <div class="acco-head" style="font-size: 16px;" data-toggle="collapse" href="#collapsesix">
                                    Refferals

                                </div>
                                <div id="collapsesix" class="collapse" data-parent="#accordion">
                                <div class="acco-body">
                                <p class="pt-5 pb-5">nill</p>
                                </div>
                                <!-- <div class="acco-body">
                                <?php
                                        $pid = $_SESSION['pid'];                                       
                                        $sql = "SELECT * FROM patient_data WHERE pid = ? ";
                                        $res = sqlStatement($sql, array($pid));
                                        // var_dump($pid);
                                        if (sqlNumRows($res)>0) {                                        
                                         
                                         while ($myrow = sqlFetchArray($res))
                                        {
                                     
                                            $title = $myrow['title'];
                                       ?>
                                    
                                        <p class="pt-5 pb-5">nill</p>
                                    
                                        <?php }
                                        }
                                        else {
                                        ?>
                                       
                                        <p class="pt-5 pb-5">nill</p>
                                    
                                        <?php } ?>
                                        </div> -->
                                </div>
                            </div>
                            <div class="">
                                <div class="acco-head" style="font-size: 16px;" data-toggle="collapse" href="#collapseseven">
                                    Not desided

                                </div>
                                <div id="collapseseven" class="collapse" data-parent="#accordion">
                                    <div class="acco-body" style="font-size: 16px;">
                                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                        incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
      
    </script>

    <!--end of container div-->
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
    var collectvalidation = <?php echo $collectthis; ?> ;

    $(function() {
        $("#reminders-div").hide();
        $("#recalls-div").hide();
        $("#sms-div").hide();
        $("#messages-li").click(function() {
            $("#messages-div").show(250);
            $("#reminders-div").hide(250);
            $("#recalls-div").hide(250);
            $("#sms-div").hide(250);
            $("#li-mess").addClass("active");
            $("#li-remi").removeClass("active");
            $("#li-reca").removeClass("active");
            $("#li-sms").removeClass("active");

        });
        $("#reminders-li").click(function() {
            $("#messages-div").hide(250);
            $("#reminders-div").show(250);
            $("#recalls-div").hide(250);
            $("#sms-div").hide(250);
            $("#li-remi").addClass("active");
            $("#li-mess").removeClass("active");
            $("#li-reca").removeClass("active");
            $("#li-sms").removeClass("active");
        });
        $("#recalls-li").click(function() {
            $("#messages-div").hide(250);
            $("#reminders-div").hide(250);
            $("#recalls-div").show(250);
            $("#sms-div").hide(250);
            $("#li-remi").removeClass("active");
            $("#li-mess").removeClass("active");
            $("#li-reca").addClass("active");
            $("#li-sms").removeClass("active");
        });
        $("#sms-li").click(function() {
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
    $(function() {
        $("ul.navbar-nav").children().click(function() {
            $(".collapse").collapse('hide');
        });
    });
    $(function() {
        //for jquery tooltip to function if jquery 1.12.1.js is called via jquery-ui in the Header::setupHeader
        // the relevant css file needs to be called i.e. jquery-ui-darkness
        $('#see-all-tooltip').attr("title", "<?php echo xla('Click to show messages for all users'); ?>");
        $('#see-all-tooltip').tooltip();
        $('#just-mine-tooltip').attr("title",
            "<?php echo xla('Click to show messages for only the current user'); ?>");
        $('#just-mine-tooltip').tooltip();
    });
    $(function() {
        var f = $("#smsForm");
        $("#SMS_patient").autocomplete({
            source: "save.php?go=sms_search",
            minLength: 2,
            select: function(event, ui) {
                event.preventDefault();
                $("#SMS_patient").val(ui.item.label + ' ' + ui.item.mobile);
                $("#sms_pid").val(ui.item.pid);
                $("#sms_mobile").val(ui.item.mobile);
                $("#sms_allow").val(ui.item.allow);
            }
        });
    });
    jQuery.ui.autocomplete.prototype._resizeMenu = function() {
        var ul = this.menu.element;
        ul.outerWidth(this.element.outerWidth());
    };
    $(function() {
        $("#newnote").click(function(event) {
            NewNote(event);
        });
        $("#printnote").click(function() {
            PrintNote();
        });
        var obj = $("#form_message_status");
        obj.onchange = function() {
            SaveNote();
        };
        $("#cancel").click(function() {
            CancelNote();
        });
        $("#note").focus();

        //clear button in messages
        $("#clear_user").click(function() {
            $("#assigned_to_text").val("<?php echo xls('Select Users From The Dropdown List'); ?>");
            $("#assigned_to").val("");
            $("#users").val("--");
        });

        //clear inputs of patients
        $("#clear_patients").click(function() {
            $("#reply_to").val("");
            $("#form_patient").val("");
        });
    });

    var NewNote = function(event) {
        top.restoreSession();
        if (document.getElementById("form_message_status").value !== 'Done') {
            collectvalidation.assigned_to = {
                presence: {
                    message: "<?php echo xls('Recipient required unless status is Done'); ?>"
                }
            }
        } else {
            delete collectvalidation.assigned_to;
        }

        $('#newnote').attr('disabled', true);

        var submit = submitme(1, event, 'new_note', collectvalidation);
        if (!submit) {
            $('#newnote').attr('disabled', false);
        } else {
            $("#new_note").submit();
        }
    };
    var PrintNote = function() {
        top.restoreSession();
        window.open('../../patient_file/summary/pnotes_print.php?noteid=' + <?php echo js_url($noteid); ?> ,
            '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
    };

    var SaveNote = function() {
        <?php
        if ($noteid) {
            ?>
            top.restoreSession();
            $("#task").val("save");
            $("#new_note").submit(); 
            <?php
        } ?>
    };

    var CancelNote = function() {
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
                EncounterIdArray[Count] = '<?php echo attr($row['encounter ']); ?>';
                EncounterDateArray[Count] = '<?php echo attr(oeFormatShortDate(date("Y-m-d", strtotime($row['date '])))); ?>';
                CalendarCategoryArray[Count] = '<?php echo attr(xl_appt_category($row['pc_catname '])); ?>';
                Count++; 
                <?php
            }
        } 
        ?>
        top.restoreSession();
        $.ajax({
            type: 'get',
            url: '<?php echo $GLOBALS['
            webroot '] . "/library/ajax/set_pt.php";?>',
            data: {
                set_pid: pid,
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            },
            async: false
        });
        parent.left_nav.setPatient(pname, pid, pubpid, '', str_dob);
        parent.left_nav.setPatientEncounter(EncounterIdArray, EncounterDateArray, CalendarCategoryArray); 
        <?php
        if ($GLOBALS['new_tabs_layout']) {
            ?>
            var docurl = '../controller.php?document&view' + "&patient_id=" + encodeURIComponent(pid) +
                "&document_id=" + encodeURIComponent(doc_id) + "&";
            var paturl = 'patient_file/summary/demographics.php?pid=' + encodeURIComponent(pid);
            parent.left_nav.loadFrame('dem1', 'pat', paturl);
            parent.left_nav.loadFrame('doc0', 'enc', docurl);
            top.activateTabByName('enc', true); 
            <?php
        } else {
            ?>
            var docurl = '<?php  echo $GLOBALS['
            webroot '] . "/controller.php?document&view"; ?>' + "&patient_id=" + encodeURIComponent(pid) +
                "&document_id=" + encodeURIComponent(doc_id) + "&";
            var paturl = '<?php  echo $GLOBALS['
            webroot '] . "/interface/patient_file/summary/demographics.php?pid="; ?>' + encodeURIComponent(pid);
            var othername = (window.name === 'RTop') ? 'RBot' : 'RTop';
            parent.frames[othername].location.href = paturl;
            location.href = docurl; 
            <?php
        } ?>
    }

    // This is for callback by the find-patient popup.
    function setpatient(pid, lname, fname, dob) {
        var f = document.getElementById('new_note');
        f.form_patient.value += lname + ', ' + fname + '; ';
        f.reply_to.value += pid + ';'; 
        <?php
        if ($noteid) {
            ?>
            //used when direct messaging service inserts a pnote with indeterminate patient
            //to allow the user to assign the message to a patient.
            top.restoreSession();
            $("#task").val("savePatient");
            $("#new_note").submit(); 
            <?php
        } ?>
    }

    // This is for callback by the multi_patients_finder popup.
    function setMultiPatients(patientsList) {
        var f = document.getElementById('new_note');
        f.form_patient.value = '';
        f.reply_to.value = '';
        $.each(patientsList, function(key, patient) {
                f.form_patient.value += patient.lname + ', ' + patient.fname + '; ';
                f.reply_to.value += patient.pid + ';';
            })

            <?php
        if ($noteid) {
            ?>
            //used when direct messaging service inserts a pnote with indeterminate patient
            //to allow the user to assign the message to a patient.
            top.restoreSession();
            $("#task").val("savePatient");
            $("#new_note").submit(); 
            <?php
        } ?>
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
            url = url + '?patients=' + $('#reply_to').val() +
                '&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>';
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
            window.open('messages.php?nomenu=1&go=SMS_bot&pid=' + encodeURIComponent(pid) + '&m=' + encodeURIComponent(
                m), 'SMS_bot', 'width=370,height=600,resizable=0');
        }
    }
    </script>
    <script>
    // var area = document.querySelector('.zoomable')
    // panzoom(area, {
    //     maxZoom: 3.5,
    //     minZoom: .5
    // });
    </script>
<script>
     $('input[type="radio"]').click(function() {
       if($(this).attr('id') == 'meds') {
            $('#medicationlists').show();  
            $('#encounter').hide();   
            $('#vitals').hide();
            $('#imaging').hide();      
       }
       else  if($(this).attr('id') == 'vital') {
            $('#vitals').show();  
            $('#encounter').hide();   
            $('#medicationlists').hide();
            $('#imaging').hide();      
       }  
       else  if($(this).attr('id') == 'enc_notes') {
            $('#vitals').hide();  
            $('#encounter').show();   
            $('#medicationlists').hide();
            $('#imaging').hide();      
       }    
       else  if($(this).attr('id') == 'receipt') {
            $('#vitals').hide();  
            $('#encounter').hide();   
            $('#medicationlists').hide();
            $('#imaging').hide();      
       }
       else  if($(this).attr('id') == 'other') {
            $('#vitals').hide();  
            $('#encounter').hide();   
            $('#medicationlists').hide();
            $('#imaging').hide();      
       }   
       else  if($(this).attr('id') == 'lab') {
            $('#vitals').hide();  
            $('#encounter').hide();   
            $('#medicationlists').hide();
            $('#imaging').show();      
       }    
       
       else {
            $('#medicationlists').show();   
            $('#encounter').show();   
            $('#vitals').show();
            $('#imaging').show();
       }
   });
</script>


    </body>
    <?php
}
    ?>
    </body>

</html>