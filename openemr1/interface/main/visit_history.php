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


$curdate = date_create(date("Y-m-d"));
date_sub($curdate, date_interval_create_from_date_string("7 days"));
$sub_date = date_format($curdate, 'Y-m-d');

// Set the default dates for Lab document search
$form_from_doc_date = ( $_GET['form_from_doc_date'] ) ? $_GET['form_from_doc_date'] : oeFormatShortDate($sub_date);
$form_to_doc_date = ( $_GET['form_to_doc_date'] ) ? $_GET['form_to_doc_date'] : oeFormatShortDate(date("Y-m-d"));
$pid=1;

if ($GLOBALS['date_display_format'] == 1) {
    $title_tooltip = "MM/DD/YYYY";
} elseif ($GLOBALS['date_display_format'] == 2) {
    $title_tooltip = "DD/MM/YYYY";
} else {
    $title_tooltip = "YYYY-MM-DD";
}

//validation library
$use_validate_js = 1;
require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php");
// require_once($GLOBALS['webroot'] . "/controller.php");
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
    <link rel="stylesheet"
        href="<?php echo $webroot; ?>/interface/main/messages/css/reminder_style.css?v=<?php echo $v_js_includes; ?>"
        type="text/css">
    <link rel="stylesheet"
        href="<?php echo $GLOBALS['web_root']; ?>/library/css/bootstrap_navbar.css?v=<?php echo $v_js_includes; ?>"
        type="text/css">

    <?php //Header::setupHeader(['datetime-picker', 'jquery-ui', 'jquery-ui-redmond', 'opener', 'moment']); ?>
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
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/emp_info_css.css">

    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/vue.js"></script>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/main.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/addmore.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/panzoom.min.js"></script>
    <!-- <script language="javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script> -->
    <!-- relatives_tab_css -->
<style>
    .activatetextarea .active-text {
    pointer-events: all;
    border: 1px solid silver;
    background-color: #ececec00
}
.xx {
    cursor: pointer;
}
.active-text {
    pointer-events: none;
    border: 0px solid silver;
    background-color: #ececec;
}
</style>
<!-- //relatives_tab_css -->

    <!-- <style>
        /* .compo-info
        {
            font-size: 15px;
        } */
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
    </style> -->
    


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
    

  
    <!-- visit_history -->
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
                        <div class="body-compo" style="height:auto;">
                        <div class="container-fluid">
                            <ul class="nav  nav-justified compo-info" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#home">Create Visit</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#menu1">Current</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#menu2">History</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                
                               
                                <div id="home" class="container tab-pane active">
                                <form id="new-encounter-form" method='post' action="<?php echo $GLOBALS['webroot']; ?>/interface/forms/newpatient/save.php" name='new_encounter'>

                                <div class="form-inputs">
                                        <div class="pt-5">

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <p>Visit Category</p>
                                                   

                                            <select  name='pc_catid' id='pc_catid' class='form-control col-sm-12'>
                                            <option value='_blank'>-- <?php echo xlt('Select One'); ?> --</option>
                                            <?php
                                            //Bring only patient ang group categories
                                            $visitSQL = "SELECT pc_catid, pc_catname, pc_cattype 
                                                       FROM openemr_postcalendar_categories
                                                       WHERE pc_active = 1 and pc_cattype IN (0,3) and pc_constant_id  != 'no_show' ORDER BY pc_seq";

                                            $visitResult = sqlStatement($visitSQL);
                                            $therapyGroupCategories = [];

                                            while ($row = sqlFetchArray($visitResult)) {
                                                $catId = $row['pc_catid'];
                                                $name = $row['pc_catname'];

                                                if ($row['pc_cattype'] == 3) {
                                                    $therapyGroupCategories[] = $catId;
                                                }

                                                if ($catId === "_blank") {
                                                    continue;
                                                }

                                                if ($row['pc_cattype'] == 3 && !$GLOBALS['enable_group_therapy']) {
                                                    continue;
                                                }

                                                // Fetch acl for category of given encounter. Only if has write auth for a category, then can create an encounter of that category.
                                                $postCalendarCategoryACO = fetchPostCalendarCategoryACO($catId);
                                                if ($postCalendarCategoryACO) {
                                                    $postCalendarCategoryACO = explode('|', $postCalendarCategoryACO);
                                                    $authPostCalendarCategoryWrite = acl_check($postCalendarCategoryACO[0], $postCalendarCategoryACO[1], '', 'write');
                                                } else { // if no aco is set for category
                                                    $authPostCalendarCategoryWrite = true;
                                                }

                                                //if no permission for category write, don't show in drop-down
                                                if (!$authPostCalendarCategoryWrite) {
                                                    continue;
                                                }

                                                $optionStr = '<option value="%pc_catid%" %selected%>%pc_catname%</option>';
                                                $optionStr = str_replace("%pc_catid%", attr($catId), $optionStr);
                                                $optionStr = str_replace("%pc_catname%", text(xl_appt_category($name)), $optionStr);
                                                if ($viewmode) {
                                                    $selected = ($result['pc_catid'] == $catId) ? " selected" : "";
                                                } else {
                                                    $selected = ($GLOBALS['default_visit_category'] == $catId) ? " selected" : "";
                                                }

                                                  $optionStr = str_replace("%selected%", $selected, $optionStr);
                                                  echo $optionStr;
                                            }
                                            ?>
                                        </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <p>Sensitivity</p>
                                                  
                                                        <select name='form_sensitivity' id='form_sensitivity' class='form-control col-sm-12' >
                                            <?php
                                            foreach ($sensitivities as $value) {
                                                // Omit sensitivities to which this user does not have access.
                                                if (acl_check('sensitivities', $value[1])) {
                                                    echo "       <option value='" . attr($value[1]) . "'";
                                                    if ($viewmode && $result['sensitivity'] == $value[1]) {
                                                        echo " selected";
                                                    }

                                                    echo ">" . xlt($value[3]) . "</option>\n";
                                                }
                                            }

                                            echo "       <option value=''";
                                            if ($viewmode && !$result['sensitivity']) {
                                                echo " selected";
                                            }

                                            echo ">" . xlt('None'). "</option>\n";
                                            ?>
                                        </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <p>Date of Service</p>
                                                    <input type="date" name='form_date' class="form-control">
                                                </div>
                                                <div class="col-md-3">
                                                    <p>On Set/Hosp. Date</p>
                                                    <input type="date" name='form_onset_date' class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pt-3">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p>Facility</p>
                                                   
                                                    <select name='facility_id' id='facility_id' class='form-control col-sm-9' onChange="bill_loc()">
                                            <?php
                                            if ($viewmode) {
                                                $def_facility = $result['facility_id'];
                                            } else {
                                                $dres = sqlStatement("select facility_id from users where username = ?", array($_SESSION['authUser']));
                                                $drow = sqlFetchArray($dres);
                                                $def_facility = $drow['facility_id'];
                                            }
                                            $posCode = '';
                                            $facilities = $facilityService->getAllServiceLocations();
                                            if ($facilities) {
                                                foreach ($facilities as $iter) { ?>
                                            <option value="<?php echo attr($iter['id']); ?>"
                                                    <?php
                                                    if ($def_facility == $iter['id']) {
                                                        if (!$viewmode) {
                                                            $posCode = $iter['pos_code'];
                                                        }
                                                        echo "selected";
                                                    }?>>
                                                    <?php echo text($iter['name']); ?>
                                            </option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                                </div>


                                                <div class="col-md-6">
                                                    <p>Billing Facility</p>
                                                    <select name="" id="" class="form-control mt-2">
                                                        <option value="">Value 1</option>
                                                        <option value="">value 2</option>
                                                        <option value="">Value 3</option>
                                                    </select>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="div pt-3">
                                            <div class="row">
                                                <div class="col-12">
                                                    <p> Reason for Visit</p>
                                                    <textarea name="reason" id="" class="form-control" rows="4"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="div pt-3">
                                            <div class="row">

                                                <div class="col-12">
                                                    <p>Type</p>
                                                    <div class="info-update">
                                                        <div><input type="radio" name="newtype" value="problem"> <label>
                                                                Problem
                                                           </label></div>
                                                        <div><input type="radio" name="newtype" value="allergy"> <label>
                                                                Allergy
                                                           </label></div>
                                                        <div class=" "><input type="radio" name="newtype" value="medication"> <label>
                                                                Medication
                                                           </label></div>
                                                        <div class=" "><input type="radio" name="newtype" name="surgery"> <label>
                                                                Surgery
                                                           </label></div>
                                                        <div class=" "><input type="radio" name="newtype" name="dental"> <label>
                                                                Dental
                                                                  </label></div>

                                                                  
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pt-3">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p>Title</p>
                                                    <input type="text" name="form_title" class="form-control" placeholder="Title">
                                                </div>


                                            </div>
                                        </div>
                                        <div class="pt-3">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p>ICD Codes</p>
                                                    <input type="text" class="form-control" name='form_diagnosis'>
                                                    <!-- <input type='text' class="form-control" name='form_diagnosis' id='form_diagnosis'
                                                    onclick='sel_diagnosis()' title='<?php echo xla('Click to select or change coding'); ?>' readonly > -->
                                                   
                                                </div>


                                            </div>
                                        </div>
                                       
                                        <div class="pt-3">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p>Occurence</p>
                                                    <input type="number" name="form_occur" class="form-control">
                                                </div>


                                            </div>
                                        </div>
                                        <div class="pt-3">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p>Referred By</p>
                                                    <input type="text" name='form_referredby' class="form-control">
                                                </div>


                                            </div>
                                        </div>
                                        <div class="pt-3">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p>Comments</p>
                                                    <textarea name='form_comments' id="" class="form-control" rows="4"></textarea>
                                                </div>


                                            </div>
                                        </div>
                                        <div class="pt-3">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p>Outcome</p>
                                                    <input type="number" name="form_outcome" class="form-control">
                                                </div>


                                            </div>
                                        </div>
                                        <div class="pt-3">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p>Destination</p>
                                                    <input type="text" name="form_destination" class="form-control">
                                                </div>


                                            </div>
                                        </div>
                                        <div class="pt-4 pb-5">
                                            <input type="hidden" name="mode" value="new">
                                            <button class="form-save" type="submit">Save</button>
                                        </div>
                                    </div>
                                    </form>
                                </div>
                       

                        <div id="menu1" class="container tab-pane fade">
                            <form id="current_form" onsubmit="submit_current();">    

                                    <div>
                                        <h4>Visit Summary</h4>
                                        <div class="pt-4 pb-5">
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Facility</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                                </div>
                                                <div class="col-sm-12 pt-2"><textarea name="" id="" rows="4 " class="form-control active-text ">edit here paragraph shown here</textarea></div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Reason</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                                </div>
                                                <div class="col-sm-12 pt-2"><textarea name="" id="" rows="4 " class="form-control active-text ">edit here paragraph shown here</textarea></div>
                                            </div>
                                            <h4 class="mt-3">SOAP</h4>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Subjective</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                                </div>
                                                <div class="col-sm-12 pt-2">
                                                    <textarea name="subjective" id="" rows="4 " class="form-control active-text " placeholder="edit here paragraph shown here"></textarea>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Objective</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                                </div>
                                                <div class="col-sm-12 pt-2"><textarea name="objective" id="" rows="4 " class="form-control active-text " placeholder="edit here paragraph shown here"></textarea></div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Assessment</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                                </div>
                                                <div class="col-sm-12 pt-2">
                                                    <textarea name="assessment" id="" rows="4 " class="form-control active-text " placeholder="edit here paragraph shown here"></textarea></div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Plan</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                                </div>
                                                <div class="col-sm-12 pt-2">
                                                    <textarea name="plan" id="" rows="4 " class="form-control active-text " placeholder="edit here paragraph shown here"></textarea></div>
                                                    <input type="hidden" name="pat_id" value="<?= $pid ?>" >
                                                    <input type="hidden" name="activity" value="1" >
                                                    <input type="hidden" name="form_id" value="current_data">
                                            </div>
                                            <div class="mt-3">
                                                <h4>Vitals</h4>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="row mt-3">
                                                            <div class="col-sm-6">
                                                                <p class="fs-14"> Blood Pressure</p>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                                            </div>
                                                            <div class="col-sm-12 pt-2">
                                                                <input type="text" name="bp" class="form-control active-text">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="row mt-3">
                                                            <div class="col-sm-6">
                                                                <p class="fs-14"> Height</p>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                                            </div>
                                                            <div class="col-sm-12 pt-2">
                                                                <input type="text" name="height" class="form-control active-text">

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="row mt-3">
                                                            <div class="col-sm-6">
                                                                <p class="fs-14"> Temprature Method</p>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                                            </div>
                                                            <div class="col-sm-12 pt-2">
                                                                <input type="text" name="temp_method" class="form-control active-text">

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="row mt-3">
                                                            <div class="col-sm-6">
                                                                <p class="fs-14"> Temprature</p>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                                            </div>
                                                            <div class="col-sm-12 pt-2">
                                                                <input type="text" name="temp" class="form-control active-text">

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="row mt-3">
                                                            <div class="col-sm-6">
                                                                <p class="fs-14"> Weight</p>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                                            </div>
                                                            <div class="col-sm-12 pt-2">
                                                                <input type="text" name="weight" class="form-control active-text">

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="row mt-3">
                                                            <div class="col-sm-6">
                                                                <p class="fs-14"> Pulse </p>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                                            </div>
                                                            <div class="col-sm-12 pt-2">
                                                                <input type="text" name="pulse" class="form-control active-text">

                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            </div>

                                            <div class="pt-4 pb-5">
                                                <button class="form-save">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                    </form>
                                </div>
                    
                                <div id="menu2" class="container tab-pane fade">
                                    <div class="pt-4 pb-5">
                                        <div class="table-div">
                                            <table class="table table-form">
                                                <tbody>
                                                    <tr>
                                                        <th>Date.</th>
                                                        <th>Issue</th>
                                                        <th>Reason/form</th>
                                                        <th>provider</th>
                                                        <th>Billing</th>
                                                        <th>Insurance</th>
                                                    </tr>
                                                    <?php

                                                    
                                                    ?>
                                                    <tr>
                                                        <td>9-23-2019</td>
                                                        <td>Sadness</td>
                                                        <td>WH</td>
                                                        <td>(213)999-6666</td>
                                                        <td></td>
                                                        <th></th>
                                                    </tr>
                                                    <!-- <tr>
                                                        <td>9-23-2019</td>
                                                        <td>Sadness</td>
                                                        <td>WH</td>
                                                        <td>(213)999-6666</td>
                                                        <td></td>
                                                        <th></th>
                                                    </tr>
                                                    <tr>
                                                        <td>9-23-2019</td>
                                                        <td>Sadness</td>
                                                        <td>WH</td>
                                                        <td>(213)999-6666</td>
                                                        <td></td>
                                                        <th></th>
                                                    </tr> -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                                                </div>
                    </window-dashboard>
                </div>
            </div>
        </section>

        
        <!-- //visit_history -->

   
    
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
 


    </body>
    <?php
}
    ?>
    </body>

</html>

<!-- <script>
    var area = document.querySelector('.zoomable')
    panzoom(area, {
        maxZoom: 3.5,
        minZoom: .5
    });
    </script> -->
    
   
<script>

function submit_current(){



$webroot=  "<?php echo $GLOBALS['webroot'];?>";


$.ajax({
    type: 'POST',
    url: $webroot+"/interface/new/new_current_vitals_save.php",
    data: $('#current_form').serialize(),   
    success: function(data){
    // alert(data);
    // console.log(data);       
    }
});

}

// function sel_diagnosis() 
// {
    // $webroot=  "<?php echo $GLOBALS['webroot'];?>";
<?php
// $url = $webroot+'/interface/patient_file/encounter/find_code_dynamic.php?codetype=';

// if ($irow['type'] == 'medical_problem') {
    // $url .= urlencode(collect_codetypes("medical_problem", "csv"));
// } else {
//     $url .= urlencode(collect_codetypes("diagnosis", "csv"));
//     $tmp  = urlencode(collect_codetypes("drug", "csv"));
//     if ($irow['type'] == 'allergy') {
//         if ($tmp) {
//             $url .= ",$tmp";
//         }
//     } elseif ($irow['type'] == 'medication') {
//         if ($tmp) {
//             $url .= ",$tmp&default=$tmp";
//         }
//     }
// }
?>

// dlgopen(<?php echo js_escape($url); ?>, '_blank', 985, 800, '', <?php echo xlj("Select Codes"); ?>);
// }


// function sel_diagnosis() {
//     $webroot=  "<?php echo $GLOBALS['webroot'];?>";
//             dlgopen($webroot+'/interface/patient_file/encounter/find_code_popup.php', '_blank', 500, 400);
//         }

 </script>

 <!-- textarea_js -->
<script>

$(document).ready(function() {

$(".xx").click(function() {
    $(this)
        .closest(".row")
        .addClass("activatetextarea")
});
});
</script>
<!--//textarea_js -->