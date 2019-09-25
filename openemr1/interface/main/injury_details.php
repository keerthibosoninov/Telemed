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


// for test
$pid=1;





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
    // Header::setupHeader(['datetime-picker', 'jquery-ui', 'jquery-ui-redmond', 'opener', 'moment']); ?>
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

    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/employee_dashboard_style.css">

    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/vue.js"></script>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/main.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/addmore.js"></script>
    <!--  km -->

    <style>
        .hidedata{
            display:none;
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
            <div class="body-content body-content2">
                <div class="container-fluid pb-4 pt-4">
                    <window-dashboard title="Employee Info" class="icon-hide">
                        <div class="head-component">
                            <div class="row">
                                <div class="col-6"></div>
                                <div class="col-6">
                                    <p class="text-white head-p">Employee Info </p>
                                </div>
                            </div>
                        </div>
                        <div class="body-compo">
                            <div class="container-fluid">
                                <ul class="nav  nav-justified compo-info" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#home">Day of Injury</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#menu1">Treatments</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#menu2">Employer Info</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#menu3">Claim Info</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div id="home" class="container tab-pane active">
                                        <form id="home_employee" onsubmit="return  submit_injury();">
                                        <div class="form-inputs">
                                            <div class="pt-5">

                                                <div class="row">
                                                    <div class="col-md-4 relative">
                                                        <p>Date of Injury</p>
                                                        <input type="date" class="form-control" placeholder="First Name" name="emp_injury_date" >
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/date.svg" class="date-time" alt="">
                                                    </div>
                                                    <div class="col-md-4 relative">
                                                        <p>Time of Injury</p>
                                                        <input type="time" class="form-control" placeholder="Middle Name" name="emp_injury_time">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/time.svg" class="date-time" alt="">
                                                    </div>
                                                    <div class="col-md-4">

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-3">

                                                <div class="row">
                                                    <div class="col-md-2 col-lg-1">
                                                        <p>Location</p>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check "><label class="form-check-label fs-14 
                                                                "><input type="checkbox" id="emp_location_check" onclick="getEmployerLocation(<?php echo $pid;?>);" name="optradio" class="form-check-input w-auto">Same as Employer location
                                                            </label></div>
                                                    </div>


                                                </div>
                                            </div>
                                          
                                            <div class="div pt-3">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p>Address</p>
                                                        <textarea name="emp_address" id="emp_address" class="form-control" rows="4" name="emp_address"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-3">

                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <p>City</p>
                                                        <input type="text" class="form-control" placeholder="City" id="emp_city" name="emp_city">
                                                    </div>

                                                    <div class="col-md-3">
                                                        <p>State</p>
                                                        <select name="emp_state" id="emp_state" class="form-control mt-2">
                                                        <?php
                                                            $body = sqlStatement("SELECT option_id, title FROM list_options " .
                                                            "WHERE list_id = 'state' AND activity = 1 ORDER BY seq");
                                                            while ($orow = sqlFetchArray($body)) {
                                                                echo "    <option value='" . attr($orow['option_id']) . "'";
                                                                if ($orow['option_id'] == $form_title) {
                                                                    echo " selected";
                                                                }

                                                                echo ">" . text($orow['title']) . "</option>\n";
                                                            }
                                                          
                                                        ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p>Zip</p>
                                                        <input type="text" class="form-control" placeholder="Zip" id="emp_zip" name="emp_zip">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p>County</p>
                                                        <input type="text" class="form-control" placeholder="County" id="emp_country" name="emp_country">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-3">

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <p>Body Part</p>
                                                        <select name="emp_body_part[]" id="" class="form-control mt-2">
                                                            <option value="">Body Part</option>
                                                        <?php
                                                            $body = sqlStatement("SELECT option_id, title FROM list_options " .
                                                            "WHERE list_id = 'body_part' AND activity = 1 ORDER BY seq");
                                                            while ($orow = sqlFetchArray($body)) {
                                                                echo "    <option value='" . attr($orow['option_id']) . "'";
                                                                if ($orow['option_id'] == $form_title) {
                                                                    echo " selected";
                                                                }

                                                                echo ">" . text($orow['title']) . "</option>\n";
                                                            }
                                                          
                                                        ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p>Cause of Injury</p>
                                                        <select name="emp_injury_cause[]" id="" class="form-control mt-2">
                                                            <option value="">Cause of Injury</option>
                                                            <?php
                                                            $body = sqlStatement("SELECT option_id, title FROM list_options " .
                                                            "WHERE list_id = 'injury_cause' AND activity = 1 ORDER BY seq");
                                                            while ($orow = sqlFetchArray($body)) {
                                                                echo "    <option value='" . attr($orow['option_id']) . "'";
                                                                if ($orow['option_id'] == $form_title) {
                                                                    echo " selected";
                                                                }

                                                                echo ">" . text($orow['title']) . "</option>\n";
                                                            }
                                                          
                                                        ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p>Nature of Injury</p>
                                                        <select name="emp_injury_nature[]" id="" class="form-control mt-2">
                                                            <option value="">Nature of Injury</option>
                                                           
                                                            <?php
                                                            $body = sqlStatement("SELECT option_id, title FROM list_options " .
                                                            "WHERE list_id = 'injury_nature' AND activity = 1 ORDER BY seq");
                                                            while ($orow = sqlFetchArray($body)) {
                                                                echo "    <option value='" . attr($orow['option_id']) . "'";
                                                                if ($orow['option_id'] == $form_title) {
                                                                    echo " selected";
                                                                }

                                                                echo ">" . text($orow['title']) . "</option>\n";
                                                            }
                                                          
                                                        ?>
                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="repeat-row" id="TextBoxContainer">

                                                </div>
                                                <div class="text-center p-3">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" id="btnAdd" alt="">
                                                </div>

                                            </div>

                                            <div class="div pt-3">
                                                <p>Narrative Description</p>
                                                <div class="row">
                                                    <div class="col-4 col-md-2">

                                                        <div class="form-check "><label class="form-check-label fs-14 
                                                                "><input type="radio" onclick="Isdescription(1);" value="1" name="is_description" class="form-check-input w-auto">Yes
                                                            </label></div>
                                                    </div>
                                                    <div class="col-4 col-md-2">

                                                        <div class="form-check "><label class="form-check-label fs-14 
                                                                "><input type="radio" onclick="Isdescription(0);" value="0" checked name="is_description" class="form-check-input w-auto">No
                                                            </label></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="div pt-3 hidedata narr_description">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p>Narrative Description</p>
                                                        <textarea name="emp_description" id="emp_description" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="div pt-3">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p>Notes</p>
                                                        <textarea name="emp_notes" id="" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-4 pb-5">
                                                <button class="form-save" >Save</button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="injury_create" value="1">
                                        <input type="hidden" name="pid"  class="pid" value="<?php echo $pid;?>">
                                        <input type="hidden" value="<?php echo $GLOBALS['webroot']?>" name="webroot">
                                        </form>

                                    </div>
                                    <div id="menu1" class="container tab-pane fade">
                                        <form method='post' name='my_form' action="<?php echo $rootdir?>/forms/treatment_plan/save.php">
                                        <!-- <form name="treatment" id="treatment" onsubmit="submit_treatment();"> -->
                                            <div class="pt-4 pb-4">
                                                <div class="pt-5">
                                                    <p>Hospitalization</p>
                                                    <div class="row">
                                                        <div class="col-md-4 relative">
                                                            <p>Admit</p>
                                                            <input type="date" name="admit_date[]" class="form-control" placeholder="Date">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/date.svg" class="date-time" alt="">
                                                        </div>
                                                        <div class="col-md-4 relative">
                                                            <p>Discharge</p>
                                                            <input type="date"  name="discharge_date[]" class="form-control" placeholder="Middle Name">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/date.svg" class="date-time" alt="">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <p>Location</p>
                                                            <input type="text" name="location[]" class="form-control" placeholder="Location">
                                                        </div>
                                                    </div>
                                                    <div id="TextBoxContainer1">

                                                    </div>
                                                    <div>
                                                        <div class="text-center p-3">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" id="btnadmit" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="pt-3">
                                                    <p>Imaging</p>
                                                    <div class="row">
                                                        <div class="col-md-3 relative">
                                                            <p>Type</p>
                                                            <input type="text" name="imaging_type[]" class="form-control" placeholder="type">

                                                        </div>
                                                        <div class="col-md-3 relative">
                                                            <p>Date</p>
                                                            <input type="date" name="imaging_date[]" class="form-control" placeholder="Date">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/date.svg" class="date-time spl-top39" alt="">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p>Findings</p>
                                                            <textarea name="imaging_findings[]" id="" class="form-control mt-2" rows="4"></textarea>
                                                        </div>
                                                    </div>
                                                    <div id="TextBoxContainer2">

                                                    </div>
                                                    <div>
                                                        <div class="text-center p-3">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" id="btnimage" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="pt-3">
                                                    <p>Ancillary Services</p>
                                                    <div class="row">
                                                        <div class="col-md-4 relative">
                                                            <p>Type</p>
                                                            <input type="text" class="form-control" name="ancillary_type[]" placeholder="Type">

                                                        </div>
                                                        <div class="col-md-4 relative">
                                                            <p>Date</p>
                                                            <input type="date" class="form-control" placeholder="" name="ancillary_date[]">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/date.svg" class="date-time spl-top39" alt="">
                                                        </div>
                                                        <div class="col-md-4 relative">
                                                            <p>Status</p>
                                                            <input type="text" class="form-control" placeholder="Status" name="ancillary_status[]">

                                                        </div>
                                                        <div class="col-md-12">
                                                            <p>Findings</p>
                                                            <textarea name="" id="" class="form-control mt-2" rows="4" name="ancillary_findings[]"></textarea>
                                                        </div>
                                                    </div>
                                                    <div id="TextBoxContainer3">

                                                    </div>
                                                    <div>
                                                        <div class="text-center p-3">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" id="btnFinding" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="pt-3">
                                                    <p>Specialist Referral</p>
                                                    <div class="row">
                                                        <div class="col-md-3 relative">
                                                            <p>Type</p>
                                                            <input type="text" class="form-control" placeholder="type" name="referral_type[]">

                                                        </div>
                                                        <div class="col-md-3 relative">
                                                            <p>Date</p>
                                                            <input type="date" class="form-control" placeholder="" name="referral_date[]">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/date.svg" class="date-time spl-top39" alt="">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p>Findings</p>
                                                            <textarea name="" id="" class="form-control mt-2" rows="4" name="referral_findings[]"></textarea>
                                                        </div>
                                                    </div>
                                                    <div id="TextBoxContainer4">

                                                    </div>
                                                    <div>
                                                        <div class="text-center p-3">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" id="btnspl" alt="">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div>
                                                    <div class="container">
                                                        <div class="row">

                                                            <div class="col-md-5">
                                                                <div>
                                                                    <svg class="circle-chart" viewbox="0 0 33.83098862 33.83098862" width="200" height="200" xmlns="http://www.w3.org/2000/svg">
                                                                            <circle class="circle-chart__background" stroke="#88C9EF" stroke-width="2" fill="none" cx="16.91549431" cy="16.91549431" r="15.91549431" />
                                                                            <circle class="circle-chart__circle" stroke="#3C9DC5" stroke-width="2" stroke-dasharray="50,100" stroke-linecap="round" fill="none" cx="16.91549431" cy="16.91549431" r="15.91549431" />
                                                                            <g class="circle-chart__info">
                                                                            <text class="circle-chart__percent" x="16.91549431" y="15.5" alignment-baseline="central" text-anchor="middle" font-size="8">4/8</text>
                                                                            <!-- <text class="circle-chart__subline" x="16.91549431" y="20.5" alignment-baseline="central" text-anchor="middle" font-size="2">Yay 30% progress!</text> -->
                                                                            </g>
                                                                        </svg>
                                                                </div>



                                                            </div>
                                                            <div class="col-md-7 pt-3">
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <p>Total No. of Orders</p>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <input type="text" class="form-control" value="" name="total_order" required>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <p>Total No. of Visits</p>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <input type="text" class="form-control" value="" name="total_visit" required>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <p>Type of Orders</p>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <input type="text" class="form-control" value="Physical Therapy" name="order_type">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="pid" class="pid" value="<?php echo $pid;?>">
                                                <input type="hidden" name="treatment" value="1">
                                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

                                                <div class="pt-4 pb-5">
                                                    <button class="form-save" type="submit">Save</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div id="menu2" class="container tab-pane fade">
                                         <form id="employer_info" onsubmit="return  submit_employer();">
                                        <div class="pt-4 pb-5">
                                            <div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p>Company Name</p>
                                                        <input type="text" name="cname" class="form-control" placeholder="">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p>Employer ID </p>
                                                        <input type="text" name="emp_id" class="form-control" placeholder="">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p>Address</p>
                                                        <textarea name="address" class="form-control mt-3" rows="4"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <p>City</p> <input type="text" placeholder="City" name="city" class="form-control"></div>
                                                    <!-- <div class="col-md-3">
                                                        <p>State</p>
                                                         <select name="" id="state" class="form-control mt-2">
                                                             <option value="">Value 1</option>
                                                         <option value="">value 2</option>
                                                          <option value="">Value 3</option>
                                                        </select>
                                                    </div> -->
                                                    <div class="col-md-3">
                                                        <p>State</p>
                                                        <select name="emp_state" class="form-control mt-2">
                                                        <?php
                                                            $body = sqlStatement("SELECT option_id, title FROM list_options " .
                                                            "WHERE list_id = 'state' AND activity = 1 ORDER BY seq");
                                                            while ($orow = sqlFetchArray($body)) {
                                                                echo "    <option value='" . attr($orow['option_id']) . "'";
                                                                if ($orow['option_id'] == $form_title) {
                                                                    echo " selected";
                                                                }

                                                                echo ">" . text($orow['title']) . "</option>\n";
                                                            }
                                                          
                                                        ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p>Zip</p> <input type="text" name="zip" placeholder="Zip" class="form-control"></div>
                                                    <div class="col-md-3">
                                                        <p>County</p> <input type="text" name="county" placeholder="County" class="form-control"></div>
                                                </div>
                                                <div class="pt-3">
                                                    <p>Employer Authorization</p>
                                                    <div class="row">
                                                        <div class="col-md-4">

                                                            <p>Supervisor</p>
                                                            <input type="text" name="super[]" class="form-control">
                                                           
                                                        </div>
                                                        <div class="col-md-4">
                                                            <p>Name</p>
                                                            <input type="text" name="s_name[]" class="form-control">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <p>E-Mail ID</p>
                                                            <input type="text" name="s_emailid[]" class="form-control">
                                                        </div>
                                                        <div class="col-md-4">

                                                            <p>Manager</p>
                                                            <input type="text" name="manager[]" class="form-control">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <p>Name</p>
                                                            <input type="text" name="m_name[]" class="form-control">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <p>Phone No.</p>
                                                            <input type="text" name="m_phone[]" class="form-control">
                                                        </div>
                                                        <input type="hidden" name="pid" value="1">
                                                    </div>
                                                    <div id="TextBoxContainer5" class="repeat-row"></div>
                                                    <div class="text-center p-3"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" id="btnemp" alt=""></div>
                                                </div>
                                            </div>
                                            <div class="pt-4 pb-5">
                                                <button id="employer_save" type="submit" class="form-save">Save</button>
                                            </div>
                                        </div>
                                         </form>
                                    </div>
                                   
                                     <!-- //employer detail -->
                                     <!-- claim_info -->
                                    <div id="menu3" class="container tab-pane fade">
                                        <form id="claim_info" onsubmit="return  submit_claim_info();">
                                        <div>
                                            <div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <p>II Number</p>
                                                        <input name="ii_no" type="text" class="form-control">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p>Claim Number</p>
                                                        <input name="claim_no" type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-4">
                                                <p>Adjuster Info</p>
                                                <div class="row pt-2">
                                                    <div class="col-md-4">
                                                        <p>First Name</p>
                                                        <input name="adjuster_fname[]" type="text" class="form-control">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p>last Name</p>
                                                        <input name="adjuster_lname[]" type="text" class="form-control">
                                                    </div>
                                                </div>
                                                <div id="TextBoxContainer6">

                                                </div>
                                                <div>
                                                    <div class="text-center p-3">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" id="btnname" alt="">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-4">
                                                <!-- <p>Address</p> -->
                                                <div class="row pt-2">
                                                    <div class="col-md-12">
                                                        <p>Address </p>
                                                        <textarea name="address" id="" class="form-control pt-3" rows="4"></textarea>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="pt-4">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <p>City</p> <input type="text" name="city" placeholder="City" class="form-control"></div>
                                                    <div class="col-md-3">
                                                        <p>State</p> 
                                                        <select name="state" class="form-control mt-2">
                                                        <?php
                                                            $body = sqlStatement("SELECT option_id, title FROM list_options " .
                                                            "WHERE list_id = 'state' AND activity = 1 ORDER BY seq");
                                                            while ($orow = sqlFetchArray($body)) {
                                                                echo "    <option value='" . attr($orow['option_id']) . "'";
                                                                if ($orow['option_id'] == $form_title) {
                                                                    echo " selected";
                                                                }

                                                                echo ">" . text($orow['title']) . "</option>\n";
                                                            }
                                                          
                                                        ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p>Zip</p> <input type="text" name="zip" placeholder="Zip" class="form-control"></div>
                                                    <div class="col-md-3">
                                                        <p>County</p> <input type="text" name="country" placeholder="County" class="form-control"></div>
                                                </div>
                                            </div>
                                            <div class="pt-4">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <p>Phone</p>
                                                        <input type="text" name="phone" class="form-control">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p>Fax</p>
                                                        <input type="text" name="fax" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="pt-4">
                                                <p>Preffered Contact Method</p>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <p>E-Mail</p>
                                                        <input type="text" name="emailid" class="form-control">
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="pt-4">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <p>Tax ID</p>
                                                        <input type="text" name="taxid" class="form-control">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p>Policy No.</p>
                                                        <input type="text" name="policy_no" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-4">
                                                <div class="row">
                                                    <div class="col-md-4 relative">
                                                        <p>Insurance Expiry Date</p>
                                                        <input type="date" placeholder="First Name" name="exp_date" class="form-control"> <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/date.svg" alt="" class="date-time"></div>

                                                </div>
                                            </div>
                                            <input type="hidden" name="pid" value="1">
                                            <div class="pt-4 pb-5">
                                                <button id="claim_save" type="submit" class="form-save">Save</button>
                                            </div>

                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </window-dashboard>
                </div>
            </div>
        </section>

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
    </body>
    <?php
}
    ?>


<!--  km updation sfor injury details -->

<script>

    function submit_injury(){

    
   
        $webroot=  "<?php echo $GLOBALS['webroot'];?>";
    

        $.ajax({
            type: 'POST',
            url: $webroot+"/interface/new/new_injury_save.php",
            data: $('#home_employee').serialize(),   
            success: function(data){
            // alert(data);
            // console.log(data);
            
            }
        });

    }

    // function submit_treatment(){

    
   
    //     $webroot=  "<?php echo $GLOBALS['webroot'];?>";


    //     $.ajax({
    //         type: 'POST',
    //         url: $webroot+"/interface/new/new_injury_save.php",
    //         data: $('#treatment').serialize(),   
    //         success: function(data){
    //         alert(data);
    //         console.log(data);
            
    //         }
    //     });

    // }



    function GetDynamicTextBox(value) {


       
        
   
            return `  <div class="row">
                        <div class="col-md-4">
                            <select name="emp_body_part[]" id="" class="form-control mt-2">
                                <option value="">Body Part</option> 
                            <?php
                                $body = sqlStatement("SELECT option_id, title FROM list_options " .
                                        "WHERE list_id = 'body_part' AND activity = 1 ORDER BY seq");
                                while ($orow = sqlFetchArray($body)) {
                                echo "    <option value='" . attr($orow['option_id']) . "'";
                                if ($orow['option_id'] == $form_title) {
                                    echo " selected";
                                }

                                echo ">" . text($orow['title']) . "</option>\n";
                            }
                        
                            ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">

                            <select name="emp_injury_cause[]" id="" class="form-control mt-2">
                                <option value="">Cause of Injury</option>
                                <?php
                                    $body = sqlStatement("SELECT option_id, title FROM list_options " .
                                                            "WHERE list_id = 'injury_cause' AND activity = 1 ORDER BY seq");
                                                            while ($orow = sqlFetchArray($body)) {
                                                                echo "    <option value='" . attr($orow['option_id']) . "'";
                                                                if ($orow['option_id'] == $form_title) {
                                                                    echo " selected";
                                                                }

                                                                echo ">" . text($orow['title']) . "</option>\n";
                                                            }
                                                          
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 delete-row">

                            <select name="emp_injury_nature[]" id="" class="form-control mt-2">
                                <option value="">Nature of Injury</option>
                                                           
                                <?php
                                                            $body = sqlStatement("SELECT option_id, title FROM list_options " .
                                                            "WHERE list_id = 'injury_nature' AND activity = 1 ORDER BY seq");
                                                            while ($orow = sqlFetchArray($body)) {
                                                                echo "    <option value='" . attr($orow['option_id']) . "'";
                                                                if ($orow['option_id'] == $form_title) {
                                                                    echo " selected";
                                                                }

                                                                echo ">" . text($orow['title']) . "</option>\n";
                                                            }
                                                          
                                ?>
                            </select>
                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove" alt="">
                    </div>

                </div>`
    }


    function getEmployerLocation($pid){
        

        $webroot=  "<?php echo $GLOBALS['webroot'];?>";

        if($('#emp_location_check').prop("checked") == true){
            
            $.post($webroot+"/interface/new/new_injury_save.php",
            {
                location: "loc",
                pid:$pid,
            },
            function(result){
              $arr= jQuery.parseJSON(result);

              $('#emp_address').val($arr['street']);
              $('#emp_state').val($arr['state']);
              $('#emp_country').val($arr['country']);
              $('#emp_zip').val($arr['postal_code']);
              $('#emp_city').val($arr['city']);
            });
        }else{
            $('#emp_address').val("");
              $('#emp_state').val('');
              $('#emp_country').val('');
              $('#emp_zip').val('');
              $('#emp_city').val('');
        }
    

        

    }


    

  


</script>

<script>
function submit_employer(){
    
   
    $webroot=  "<?php echo $GLOBALS['webroot'];?>";


    $.ajax({
        type: 'POST',
        url: $webroot+"/interface/new/new_employer_save.php",
        data: $('#employer_info').serialize(),   
        success: function(data){
        // alert(data);
        // console.log(data);
        
        }
    });

}


function submit_claim_info(){
    
   
    $webroot=  "<?php echo $GLOBALS['webroot'];?>";
    
    
    $.ajax({
        type: 'POST',
        url: $webroot+"/interface/new/new_claim_save.php",
        data: $('#claim_info').serialize(),   
        success: function(data){
        // alert(data);
        console.log(data);
        
        }
    });
    
    }

</script>

<script>
    function GetDynamicTextBox1(value) {
        return `    <div class="row pt-4">
        <div class="col-md-4 relative">
            <p>Admit</p>
            <input type="date" name="admit_date[]" class="form-control" placeholder="Date">
            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/date.svg" class="date-time" alt="">
        </div>
        <div class="col-md-4 relative">
            <p>Discharge</p>
            <input type="date"  name="discharge_date[]" class="form-control" placeholder="Middle Name">
            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/date.svg" class="date-time" alt="">
        </div>
        <div class="col-md-4 delete-row">
            <p>Location</p>
            <input type="text" name="location[]" class="form-control" placeholder="Location">
            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove1" alt="">
        </div>
    </div>`
    }

    function GetDynamicTextBox3(value) {
        return `     <div class="row pt-3">
        <div class="col-md-4 relative">
            <p>Type</p>
            <input type="text" class="form-control" name="ancillary_type[]" placeholder="Type">

        </div>
        <div class="col-md-4 relative">
            <p>Date</p>
            <input type="date" class="form-control" placeholder="" name="ancillary_date[]">

            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/date.svg" class="date-time spl-top39" alt="">
        </div>
        <div class="col-md-4 relative delete-row">
            <p>Status</p>
            <input type="text" class="form-control" placeholder="Status" name="ancillary_status[]">
            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove3" alt="">
        </div>
        <div class="col-md-12">
            <p>Findings</p>
            <textarea  id="" class="form-control mt-2" rows="4" name="ancillary_findings[]"></textarea>
        </div>
    </div>`
    }

    function GetDynamicTextBox4(value) {
        return `     <div class="row">
        <div class="col-md-3 relative">
            <p>Type</p>
            <input type="text" class="form-control" placeholder="type" name="referral_type[]">

        </div>
        <div class="col-md-3 relative">
            <p>Date</p>
            <input type="date" class="form-control" placeholder="" name="referral_date[]">
            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/date.svg" class="date-time spl-top39" alt="">
        </div>
        <div class="col-md-6 delete-row">
            <p>Findings</p>
            <textarea  id="" class="form-control mt-2" rows="4" name="referral_findings[]"></textarea>
            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove4" alt="">
        </div>
    </div>`
    }

    function GetDynamicTextBox2(value) {
        return `     <div class="row">
        <div class="col-md-3 relative">
            <p>Type</p>
            <input type="text" name="imaging_type[]" class="form-control" placeholder="type">

        </div>
        <div class="col-md-3 relative">
            <p>Date</p>
            <input type="date" name="imaging_date[]" class="form-control" placeholder="Date">
            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/date.svg" class="date-time spl-top39" alt="">
        </div>
        <div class="col-md-6 delete-row">
            <p>Findings</p>
            <textarea name="imaging_findings[]" id="" class="form-control mt-2" rows="4"></textarea>
            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove2" alt="">
        </div>
    </div>`
    }


    function GetDynamicTextBox5(value) {
        return `      <div class="row">
        <div class="col-md-4">

            <p>Supervisor</p>
            <input type="text" name="super[]" class="form-control">
        </div>
        <div class="col-md-4">
            <p>Name</p>
            <input type="text" name="s_name[]" class="form-control">
        </div>
        <div class="col-md-4">
            <p>E-Mail ID</p>
            <input type="text" name="s_emailid[]"  class="form-control">
        </div>
        <div class="col-md-4">

            <p>Manager</p>
            <input type="text" name="manager[]" class="form-control">
        </div>
        <div class="col-md-4">
            <p>Name</p>
            <input type="text" name="m_name[]" class="form-control">
        </div>
        <div class="col-md-4 delete-row">
            <p>Phone No.</p>
            <input type="text" name="m_phone[]" class="form-control">
            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove5" alt="">
        </div>
    </div>`
    }


    function GetDynamicTextBox6(value) {
        return `<div class="row pt-2">
            <div class="col-md-4">
                <p>First Name</p>
                <input name="adjuster_fname[]" type="text" class="form-control">
            </div>
            <div class="col-md-4 delete-row">
                <p>last Name</p>
                <input name="adjuster_lname[]" type="text" class="form-control">
                <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove6" alt="">
            </div>
            </div>`
    }


</script>



    </body>

</html>