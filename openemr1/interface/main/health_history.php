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
// $pid=8;
$pid=$_SESSION['pid'];
$pid=8;

if($pid){
    $history_data = getHistoryData($pid);
    
}



$result_history = getHistoryData($pid);
if (!is_array($result_history)) {
    newHistoryData($pid);
    $result_history = getHistoryData($pid);
}
$condition_str = '';

                                        /*Get the constraint from the DB-> LBF forms accordinf the form_id*/
// $constraints = LBF_Validation::generate_validate_constraints("HIS");

 if (isset($result_history[$field_id])) {
        $currvalue = $result_history[$field_id];
 }



//   new function for medical submit

   
    if(isset($_POST['medical_submit']) && $_POST['medical_submit']=="1"){

        $pid=$_POST['pid'];
        $date=date("Y-m-d H:i:s");
      
        foreach ($_POST['medical_condition'] as $key => $value) {

            $condition = $_POST["medical_condition"][$key];
            $medical_des = $_POST["medical_des"][$key];
            $medical_id =$_POST["medical_id"][$key];
            $pid=$_POST["pid"];
            if($medical_id){

                $query = ("update medical_history  set
                            pid='" . add_escape_custom($pid) . "',
                            condition_name='" . add_escape_custom($condition) . "',
                            description='" . add_escape_custom($medical_des) . "',
                            edited_date='".$date."' where id=$medical_id
                            ");

                    sqlStatement($query);

            }else{

                $query = ("insert into medical_history  set
                            pid='" . add_escape_custom($pid) . "',
                            condition_name='" . add_escape_custom($condition) . "',
                            description='" . add_escape_custom($medical_des) . "',
                            created_date='".$date."',
                            edited_date='".$date."'
                            ");

                sqlInsert($query);

            }
        }
       


    }

    if(isset($_POST['surgical_submit']) && $_POST['surgical_submit']=="1"){
        $date=date("Y-m-d H:i:s");
        $pid=$_POST['pid'];
      
        foreach ($_POST['surgical_condition'] as $key => $value) {

            $condition = $_POST["surgical_condition"][$key];
            $surgical_des = $_POST["surgical_desc"][$key];
            $surgical_id =$_POST["surgical_id"][$key];
            $date =$_POST["surgical_date"][$key];
            $pid=$_POST["pid"];
            if($surgical_id){

                $query = ("update surgical_history  set
                            pid='" . add_escape_custom($pid) . "',
                            date='" . add_escape_custom($date) . "',
                            condition_name='" . add_escape_custom($condition) . "',
                            description='" . add_escape_custom($surgical_des) . "',
                            edited_date='".$date."' where id=$surgical_id
                            ");

                    sqlStatement($query);

            }else{

                $query = ("insert into surgical_history  set
                            pid='" . add_escape_custom($pid) . "',
                            date='" . add_escape_custom($date) . "',
                            condition_name='" . add_escape_custom($condition) . "',
                            description='" . add_escape_custom($surgical_des) . "',
                            created_date='".$date."',
                            edited_date='".$date."'
                            ");

                sqlInsert($query);

            }
        }
       


    }

    
    $rez = sqlStatement("SELECT *  FROM medical_history WHERE pid = '" . add_escape_custom($pid) . "'");
    $surgical_data = sqlStatement("SELECT *  FROM surgical_history WHERE pid = '" . add_escape_custom($pid) . "'");
       
        
   
    


?>
<!DOCTYPE html>
<html>

<head>
    <!-- <link rel="stylesheet"
        href="<?php echo $webroot; ?>/interface/main/messages/css/reminder_style.css?v=<?php echo $v_js_includes; ?>"
        type="text/css"> -->
    <!-- <link rel="stylesheet"
        href="<?php echo $GLOBALS['web_root']; ?>/library/css/bootstrap_navbar.css?v=<?php echo $v_js_includes; ?>"
        type="text/css"> -->

    <?php
    // Header::setupHeader(['datetime-picker', 'jquery-ui', 'jquery-ui-redmond', 'opener', 'moment']); ?>
    <script>
    var xljs1 = '<?php echo xla('
    Preferences updated successfully '); ?>';
    var format_date_moment_js = '<?php echo attr(DateFormatRead("validateJS")); ?>'; 
    <?php require_once "$srcdir/restoreSession.php"; ?>
    </script>

    <!-- <script type="text/javascript"
        src="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/js/reminder_appts.js?v=<?php echo $v_js_includes; ?>">
    </script> -->

    <link rel="shortcut icon" href="<?php echo $webroot; ?>/sites/default/favicon.ico" />

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="MedEx Bank">
    <meta name="author" content="OpenEMR: MedExBank">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/style.css">


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
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/addmore.js"></script>
    <!--  km -->

    <style>
        .hidedata{
            display:none;
        }
        table{
            width:100%;
        }
        .label_custom{
            width:33.33%;
        }
        .pb-2{
            border:10px solid transparent;
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
                    <window-dashboard title="" class="icon-hide">
                    <div class="head-component">
                            <div class="row">
                                <div class="col-6"></div>
                                <div class="col-6">
                                    <p class="text-white head-p"> </p>
                                </div>
                            </div>
                        </div>
                        <div class="body-compo">
                            <div class="container-fluid">
                                <ul class="nav  nav-justified compo-info mw-100" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#medical">Medical History</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#surgical">Surgical History</a>
                                    </li>
                                    <!-- <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#home">Risk Factors</a>
                                    </li> -->
                                    <!-- <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#menu1">Exams</a>
                                    </li> -->
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#menu2">Family History</a>
                                    </li>
                                    <!-- <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#menu3">Relatives</a>
                                    </li> -->
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#tab_Lifestyle">Life Style</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#menu6"  id="medicationData">Medication</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#menu5" id="allergyData">Allergies</a>
                                    </li>
                                    
                                </ul>
                                <div class="tab-content">
                                    <div id="medical" class="tab-pane active">
                                        <form action="<?php echo $GLOBALS['webroot']?>/interface/main/health_history.php" id="HIS" name='history_form' method='post'>

                                            <?php
                                               
                                                    // print_r($rez);
                                                    // print_r($rez->_queryID);
                                                   $count=$rez->_queryID->num_rows;
                                                   $i=1;
                                                   if($count){
                                               
                                                    while ($idrow = sqlFetchArray($rez)){
                                                        
                                                    
                                            ?>
                                            <div>
                                                <div class="row bodypart17">
                                                    <div class="col-sm-12 pt-2 delete-row">
                                                        <p>Name of Condition</p>
                                                        <input type="text" name="medical_condition[]" id=" " class="form-control active-text" value="<?php echo $idrow['condition_name'];?>">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx">
                                                    </div>
                                                    <div class="col-sm-12 pt-2 delete-row">
                                                        <p>Description</p>
                                                        <textarea name="medical_des[]" id="" rows="4 " class="form-control active-text pt-3"><?php echo $idrow['description'];?></textarea>
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove17 removeicon" ids="<?php echo $idrow['id'];?>"  alt="">
                                                    </div>
                                                </div>
                                                <input type="hidden" name="medical_id[]" value="<?php echo $idrow['id'];?>">

                                        
                                                <?php

                                                if($count !=$i ){
                                                    ?>

                                                    <br>
                                                    <hr>
                                                <?php
                                                }
                                                ?>
                                               
                                            </div>

                                            <?php
                                                    $i++;
                                                }
                                            }else{
                                                ?>
                                                <div>
                                                <div class="row bodypart17">
                                                    <div class="col-sm-12 pt-2 delete-row">
                                                        <p>Name of Condition</p>
                                                        <input type="text" name="medical_condition[]" id=" " class="form-control active-text" >
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx">
                                                    </div>
                                                    <div class="col-sm-12 pt-2 delete-row">
                                                        <p>Description</p>
                                                        <textarea name="medical_des[]" id="" rows="4 " class="form-control active-text pt-3"></textarea>
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove17 removeicon" alt="">
                                                    </div>
                                                </div>
                                                <input type="hidden" name="medical_id" value="">

                                                
                                            </div>
                                            <?php
                                            }
                                            ?>
                                            <div id="TextBoxContainer17"></div>
                                            <div>
                                                <div class="text-center p-3"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" id="btnadmit17" alt=""></div>
                                            </div>

                                            <input type="hidden" name="pid" value="<?php echo $pid?>">
                                            <input type="hidden" name="medical_submit" value="1">
                                            
                                            <div class="pt-4 pb-5"><button class="form-save" type="submit">Save</button></div>
                                        </form>
                                    </div>
                                    <div id="surgical" class="tab-pane">
                                        <form action="<?php echo $GLOBALS['webroot']?>/interface/main/health_history.php" id="HIS" name='history_form' method='post'>
                                        <?php
                                            
                                                $j=1;

                                                $count1=$surgical_data->_queryID->num_rows;
                                                if($count1){

                                                while ($sidrow = sqlFetchArray($surgical_data)){
                                                    
                                                    
                                            ?>
                                        <div>
                                            <div class="row bodypart18">
                                                <div class="col-sm-8 pt-2 ">
                                                    <p>Name of Condition</p>
                                                    <input type="text" name="surgical_condition[]" id="" value="<?php echo $sidrow['condition_name']?>" class="form-control active-text">

                                                </div>
                                                <div class="col-sm-4 pt-2 delete-row">
                                                    <p> Date</p>
                                                    <input type="date" name="surgical_date[]" id=" "  value="<?php echo $sidrow['date']?>" class="form-control active-text">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx">
                                                </div>
                                                <div class="col-sm-12 pt-2 delete-row">
                                                    <p>Description</p>
                                                    <textarea name="surgical_desc[]" id="" rows="4 " class="form-control active-text pt-3"><?php echo $sidrow['description']?></textarea>
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove18 removeicon" alt=""  ids="<?php echo $sidrow['id'];?>">
                                                </div>
                                                <input type="hidden" name="surgical_id[]" value="<?php echo $sidrow['id'];?>">

                                                <?php


                                                if($count1 !=$j ){
                                                    echo "<br>
                                                    <hr>";
                                                    ?>

                                                
                                                <?php
                                                }
                                                ?>
                                               
                                            </div>

                                            
                                        </div>
                                        <?php
                                         $j++;
                                            }
                                        }else{
                                        ?>
                                            <div class="row bodypart18">
                                                <div class="col-sm-8 pt-2 ">
                                                    <p>Name of Condition</p>
                                                    <input type="text" name="surgical_condition[]" id="" value="<?php echo $sidrow['condition_name']?>" class="form-control active-text">

                                                </div>
                                                <div class="col-sm-4 pt-2 delete-row">
                                                    <p> Date</p>
                                                    <input type="date" name="surgical_date[]" id=" "  value="<?php echo $sidrow['date']?>" class="form-control active-text">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx">
                                                </div>
                                                <div class="col-sm-12 pt-2 delete-row">
                                                    <p>Description</p>
                                                    <textarea name="surgical_desc[]" id="" rows="4 " class="form-control active-text pt-3"><?php echo $sidrow['description']?></textarea>
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove18 removeicon" alt="">
                                                </div>
                                                <input type="hidden" name="surgical_id[]" value="<?php echo $sidrow['id'];?>">
                                            </div>
                                            <?php
                                            }
                                            ?>
                                            
                                            <div id="TextBoxContainer18"></div>
                                            <div>
                                                <div class="text-center p-3"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" id="btnadmit18" alt=""></div>
                                            </div>

                                            <input type="hidden" name="pid" value="<?php echo $pid?>">
                                            <input type="hidden" name="surgical_submit" value="1">

                                        <div class="pt-4 pb-5"><button class="form-save">Save</button></div>
                                        </form>
                                    </div>
                                    <div id="home" class="tab-pane">
                                        <form action="../patient_file/history/history_save.php" id="HIS" name='history_form' method='post' onsubmit="submitme(<?php echo $GLOBALS['new_validate'] ? 1 : 0;?>,event,'HIS',constraints)">

                                            <div class="form-inputs pt-5 pb-5">
                                                <div class="riskfactors">
                                                <?php
                                                $field_id='usertext11';
                                                if (isset($result_history[$field_id])) {
                                                    $currvalue = $result_history[$field_id];
                                                }

                                                $cols = max(1, 0);
                                                $avalue = explode('|', $currvalue);
                                                $list_id='riskfactors';
                                               
                                                $field_id_esc= htmlspecialchars($field_id, ENT_QUOTES);
                                                $lres = sqlStatement("SELECT * FROM list_options " .
                                                "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
                                                $tdpct = (int) (100 / $cols);
                                                for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
                                                    $option_id = $lrow['option_id'];
                                                    $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);

                                                    
                                                    echo "<label class='checking'>". htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES)."
                                                            <input type='checkbox'  name='form_{$field_id_esc}[$option_id_esc]' id='form_{$field_id_esc}[$option_id_esc]' value='1'
                                                            ";
                                                            if (in_array($option_id, $avalue)) {
                                                                echo " checked";
                                                            }
                                                    echo ">
                                                            <span class='checkmark'></span>
                                                        </label>";

                                                    if($option_id_esc=='coc'){
                                                            echo"  
                                                            <div class='form-group'>
                                                                <input type='text' class='form-control' name='risk_coc_other' value='";
                                                                if($result_history['risk_coc_other']){ echo $result_history['risk_coc_other'];}
                                                                echo " ' >
                                                            </div>";
                                                          
                                                    }
                                                            
                                                    if($option_id_esc=='oth'){
                                                            echo "
                                                            <div class='form-group'>
                                                                <input type='text' class='form-control' name='risk_oth_other' value='";
                                                                if($result_history['risk_oth_other']){ echo $result_history['risk_oth_other'];}
                                                                echo "'>
                                                            </div> ";
                                                    }
                                                    
                                                }
                                                

                                            
                                                
                                                    
                                                
                                                    ?>
                                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                                    <input type="hidden" name="pid" value="<?php echo $pid;?>">
                                                    <div class="pt-4 pb-5"><button class="form-save" type="submit">Save</button></div>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                    <div id="menu1" class="container tab-pane fade">
                                        <?php
                                        $result_history = getHistoryData($pid);
                                        if (!is_array($result_history)) {
                                            newHistoryData($pid);
                                            $result_history = getHistoryData($pid);
                                        }
                                        $condition_str = '';

                                        /*Get the constraint from the DB-> LBF forms accordinf the form_id*/
                                        $constraints = LBF_Validation::generate_validate_constraints("HIS");

                                       

                                        ?>
                                        <script> var constraints = <?php echo $constraints;?>; </script>
                                        <form action="../patient_file/history/history_save.php" id="HIS" name='history_form' method='post' onsubmit="submitme(<?php echo $GLOBALS['new_validate'] ? 1 : 0;?>,event,'HIS',constraints)">
                                            <div class="pt-4 pb-4">
                                                <div>
                                                    <?php
                                                    $list_id='exams';
                                                    $field_id='exams';
                                                    if (isset($result_history[$field_id])) {
                                                        $currvalue = $result_history[$field_id];
                                                    }

                                                    $tmp = explode('|', $currvalue);
                                                    $avalue = array();
                                                    foreach ($tmp as $value) {
                                                        if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                                                            $avalue[$matches[1]] = $matches[2];
                                                        }


                                                    }

                                                    
                                                    $exams_list=sqlStatement("SELECT * FROM list_options " .
                                                    "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
                                                    

                        


                                                    while ($lrow = sqlFetchArray($exams_list)) {

                                                        $option_id = $lrow['option_id'];
                                                        $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
                                                        $restype = substr($avalue[$option_id], 0, 1);
                                                        $resnote = substr($avalue[$option_id], 2);
                                                        $field_id_esc= htmlspecialchars($field_id, ENT_QUOTES);

                                                        // Added 5-09 by BM - Translate label if applicable
                                                        echo "<div class='row mt-3'>
                                                        <div class='col-sm-6'>
                                                            <p class='fs-14'>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</p>
                                                        </div>
                                                        <div class='col-sm-6'>
                                                            <div class='row'>";
                                                            $text='';
                                                            for ($i = 0; $i < 3; ++$i) {
                                                            
                                                                switch ($i) {   
                                                                    case 0:
                                                                        $text='N/A'; 
                                                                        break;
                                                                    case 1:
                                                                        $text='Normal'; 
                                                                        break;
                                                                    case 2:
                                                                        $text='Abnormal'; 
                                                                        break;
                                                                }

                                                                $inputValue = htmlspecialchars($i, ENT_QUOTES);
                                                            echo"   <div class='col-4'><input type='radio' name='radio_{$field_id_esc}[$option_id_esc]' value='$inputValue' id='radio_{$field_id_esc}[$option_id_esc]' value='$inputValue'  $lbfonchange ";
                                                            if($restype == $i){
                                                                echo "checked";
                                                            }
                                                            echo "> $text
                                                                </div>";
                                                            }
                                                            echo "</div>
                                                        </div>
                                                        <div class='col-sm-12'>
                                                            <textarea name='form_{$field_id_esc}[$option_id_esc]' id='form_{$field_id_esc}[$option_id_esc]' class='form-control' rows='4' value='$resnote'>$resnote</textarea>
                                                        </div>
                                                    </div>";
                                                        // echo "<tr><td>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";

                                                        // for ($i = 0; $i < 3; ++$i) {
                                                        //     $inputValue = htmlspecialchars($i, ENT_QUOTES);
                                                        //     echo "<td><input type='radio'" .
                                                        //     " name='radio_{$field_id_esc}[$option_id_esc]'" .
                                                        //     " id='radio_{$field_id_esc}[$option_id_esc]'" .
                                                        //     " value='$inputValue' $lbfonchange";
                                                        //     if ($restype === "$i") {
                                                        //         echo " checked";
                                                        //     }

                                                        //     echo " $disabled /></td>";
                                                        // }

                                                        // $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
                                                        // $resnote = htmlspecialchars($resnote, ENT_QUOTES);
                                                        // echo "<td><input type='text'" .
                                                        // " name='form_{$field_id_esc}[$option_id_esc]'" .
                                                        // " id='form_{$field_id_esc}[$option_id_esc]'" .
                                                        // " size='$fldlength'" .
                                                        // " $string_maxlength" .
                                                        // " value='$resnote' $disabled /></td>";
                                                        // echo "</tr>";
                                                    ?>

                                                    <?php
                                                    }
                                                    ?>
                                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                                    <input type="hidden" name="pid" value="<?php echo $pid;?>">
                                                    <div class="pt-4 pb-5"><button class="form-save" type="submit">Save</button></div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div id="menu2" class="tab-pane fade">
                                        <form id="family_history" name="family_history" onsubmit="family_history_submit();">
                                            <div class="pt-4 pb-5">
                                                <div class="row mt-3">
                                                    <div class="col-sm-6">
                                                        <p class="fs-14">Father</p>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="text-right">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 pt-2">
                                                        <textarea name="history_father" id="" class="form-control active-text " rows="4 "><?php if(isset($history_data['history_father'])){ echo $history_data['history_father'];}?></textarea>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-6">
                                                        <p class="fs-14">Mother</p>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="text-right">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 pt-2">
                                                        <textarea name="history_mother" id="" class="form-control active-text " rows="4 "><?php if(isset($history_data['history_mother'])){echo $history_data['history_mother'];}?></textarea>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-6">
                                                        <p class="fs-14">Siblings</p>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="text-right">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 pt-2">
                                                        <textarea name="history_siblings" id="" class="form-control active-text " rows="4 "><?php if(isset($history_data['history_siblings'])){echo $history_data['history_siblings'];}?></textarea>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-6">
                                                        <p class="fs-14">Spouse</p>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="text-right">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 pt-2">
                                                        <textarea name="history_spouse" id="" class="form-control active-text " rows="4 "><?php if(isset($history_data['history_spouse'])){ echo $history_data['history_spouse'];}?></textarea>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-6">
                                                        <p class="fs-14">Offspring</p>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="text-right">
                                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 pt-2">
                                                        <textarea name="history_offspring" id="" class="form-control active-text " rows="4 "><?php if(isset($history_data['history_offspring'])){echo $history_data['history_offspring'];}?></textarea>
                                                    </div>
                                                </div>

                                            </div>
                                            <input type="hidden" name="pid" value="<?php echo $pid;?>">
                                            <input type="hidden" name="form_id" value="family_data">
                                            <div class="pt-4 pb-5"><button class="form-save" type="submit">Save</button></div>
                                        </form>
                                    </div>

                                     <!-- relatives_tab -->
                                    <div id="menu3" class="tab-pane fade ">
                                        <form id="relatives_tab" onsubmit="submit_relatives();">
                                        <div class="pt-4 pb-5">
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Cancer</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 pt-2">
                                                    <textarea name="cancer" id="" class="form-control active-text " placeholder="edit here paragraph shown here" rows="4 "></textarea>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Diabetes</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 pt-2">
                                                    <textarea name="diabetes" id="" class="form-control active-text " placeholder="edit here paragraph shown here" rows="4 "></textarea>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Heart Problems</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 pt-2">
                                                    <textarea name="heart_pblm" id="" class="form-control active-text " placeholder="edit here paragraph shown here" rows="4 "></textarea>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Epilepsy</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 pt-2">
                                                    <textarea name="epilepsy" id="" class="form-control active-text " placeholder="edit here paragraph shown here" rows="4 "></textarea>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Suicide</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 pt-2">
                                                    <textarea name="suicide" id="" class="form-control active-text " placeholder="edit here paragraph shown here" rows="4 "></textarea>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Tuberclosis</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 pt-2">
                                                    <textarea name="tuberclosis" id="" class="form-control active-text " placeholder="edit here paragraph shown here" rows="4 "></textarea>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">High Blood Pressure</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 pt-2">
                                                    <textarea name="bp" id="" class="form-control active-text " placeholder="edit here paragraph shown here" rows="4 "></textarea>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Stroke</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 pt-2">
                                                    <textarea name="stroke" id="" class="form-control active-text " placeholder="edit here paragraph shown here" rows="4 "></textarea>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-sm-6">
                                                    <p class="fs-14">Mental Illness</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-right">
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" class="xx" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 pt-2">
                                                    <textarea name="mental_illness" id="" class="form-control active-text " rows="4" placeholder="edit here paragraph shown here"></textarea>
                                                </div>
                                                <input type="hidden" value="1" name="pid">
                                            </div>                                        
                                        </div>
                                        <div class="pt-4 pb-5"><button class="form-save">Save</button></div>
                                        </form>
                                    </div>
                                    <div id="menu4" class=" tab-pane fade ">
                                        <div class="pt-4 pb-5 ">
                                            <div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-4">
                                                        <p class="fs-14">Tobacco</p>
                                                    </div>
                                                    <div class="col-sm-8 pb-2">
                                                        <div class="row">
                                                            <div class="col-sm-6  ">
                                                                <input type="radio" id="radio1" name="optradio" value="option1"> Quit
                                                                <div class="row d-inline-block">
                                                                    <div class="col-12 pl-4">
                                                                        <input type="date" class="form-control mt-0" name="" id="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> N/A
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> Yes
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> No
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <textarea name="" id="" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-4">
                                                        <p class="fs-14">Coffee</p>
                                                    </div>
                                                    <div class="col-sm-8 pb-2">
                                                        <div class="row">
                                                            <div class="col-sm-6  ">
                                                                <input type="radio" id="radio1" name="optradio" value="option1"> Quit
                                                                <div class="row d-inline-block">
                                                                    <div class="col-12 pl-4">
                                                                        <input type="date" class="form-control mt-0" name="" id="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> N/A
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> Yes
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> No
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <textarea name="" id="" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-4">
                                                        <p class="fs-14">Alcohol</p>
                                                    </div>
                                                    <div class="col-sm-8 pb-2">
                                                        <div class="row">
                                                            <div class="col-sm-6  ">
                                                                <input type="radio" id="radio1" name="optradio" value="option1"> Quit
                                                                <div class="row d-inline-block">
                                                                    <div class="col-12 pl-4">
                                                                        <input type="date" class="form-control mt-0" name="" id="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> N/A
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> Yes
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> No
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <textarea name="" id="" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-4">
                                                        <p class="fs-14">Recreational Drugs</p>
                                                    </div>
                                                    <div class="col-sm-8 pb-2">
                                                        <div class="row">
                                                            <div class="col-sm-6  ">
                                                                <input type="radio" id="radio1" name="optradio" value="option1"> Quit
                                                                <div class="row d-inline-block">
                                                                    <div class="col-12 pl-4">
                                                                        <input type="date" class="form-control mt-0" name="" id="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> N/A
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> Yes
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> No
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <textarea name="" id="" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-4">
                                                        <p class="fs-14">Counselling</p>
                                                    </div>
                                                    <div class="col-sm-8 pb-2">
                                                        <div class="row">
                                                            <div class="col-sm-6  ">
                                                                <input type="radio" id="radio1" name="optradio" value="option1"> Quit
                                                                <div class="row d-inline-block">
                                                                    <div class="col-12 pl-4">
                                                                        <input type="date" class="form-control mt-0" name="" id="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> N/A
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> Yes
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> No
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <textarea name="" id="" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-4">
                                                        <p class="fs-14">Exersice Patterns</p>
                                                    </div>
                                                    <div class="col-sm-8 pb-2">
                                                        <div class="row">
                                                            <div class="col-sm-6  ">
                                                                <input type="radio" id="radio1" name="optradio" value="option1"> Quit
                                                                <div class="row d-inline-block">
                                                                    <div class="col-12 pl-4">
                                                                        <input type="date" class="form-control mt-0" name="" id="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> N/A
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> Yes
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> No
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <textarea name="" id="" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-4">
                                                        <p class="fs-14">Hazardous Activities</p>
                                                    </div>
                                                    <div class="col-sm-8 pb-2">
                                                        <div class="row">
                                                            <div class="col-sm-6  ">
                                                                <input type="radio" id="radio1" name="optradio" value="option1"> Quit
                                                                <div class="row d-inline-block">
                                                                    <div class="col-12 pl-4">
                                                                        <input type="date" class="form-control mt-0" name="" id="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> N/A
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> Yes
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> No
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <textarea name="" id="" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-4">
                                                        <p class="fs-14">Sleep Patterns</p>
                                                    </div>
                                                    <div class="col-sm-8 pb-2">
                                                        <div class="row">
                                                            <div class="col-sm-6  ">
                                                                <input type="radio" id="radio1" name="optradio" value="option1"> Quit
                                                                <div class="row d-inline-block">
                                                                    <div class="col-12 pl-4">
                                                                        <input type="date" class="form-control mt-0" name="" id="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> N/A
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> Yes
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> No
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <textarea name="" id="" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-sm-4">
                                                        <p class="fs-14">Seatbelt Use</p>
                                                    </div>
                                                    <div class="col-sm-8 pb-2">
                                                        <div class="row">
                                                            <div class="col-sm-6  ">
                                                                <input type="radio" id="radio1" name="optradio" value="option1"> Quit
                                                                <div class="row d-inline-block">
                                                                    <div class="col-12 pl-4">
                                                                        <input type="date" class="form-control mt-0" name="" id="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> N/A
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> Yes
                                                            </div>
                                                            <div class="col-2"><input type="radio" id="radio1" name="optradio" value="option1"> No
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <textarea name="" id="" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>

                                                <div class="pt-4 pb-5"><button class="form-save">Save</button></div>
                                            </div>
                                        </div>
                                    </div>

                                      <!-- medication -->
                                    <div id="menu6" class="container tab-pane fade ">
                                        <div class="pt-4 pb-4">                                           
                                            <div id="medication_list"></div>
                                            <div id="addnew">
                                                <div class="text-center" id="addmore_btn1">
                                                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" alt="">
                                                </div>
                                                <div class="text-center">
                                                    <p class="fs-14">Add New</p>
                                                </div>
                                            </div>
                                            <div></div>
                                        </div>
                                        <div id="medication_data"></div>                                        
                                    </div>
                                    <!-- //medication -->
                                    <!-- allergy -->
                                    <div id="menu5" class=" tab-pane fade ">
                                        <div class="pt-4 pb-4">
                                            <!-- <div>
                                                <table class="table table-form">
                                                    <tbody class="repeat-row ">
                                                        <tr>
                                                            <th>Substance</th>
                                                            <th>Severity </th>
                                                            <th>Reaction</th>
                                                            <th>Comments</th>
                                                            <th></th>


                                                        </tr>
                                                        <tr class="tablerow ">

                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>

                                                            <td><img src="img/edit-text.svg" alt="" class="pr-2 hide-parent-open1"><img src="img/delete.svg" alt="" class="remove16"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div> -->
                                            <div id="allergy_list"></div>
                                            <div id="addPlus" class="hidedata">
                                                <div class="text-center" id="addmore_btn">
                                                    <img class="removeicon" src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" alt="">
                                                </div>
                                                <div class="text-center">
                                                    <p class="fs-14">Add New</p>
                                                </div>
                                            </div>
                                            <div></div>
                                        </div>
                                        <div id="allergy_data"></div>
                                        <!-- <div class="tableform1" style="display:none">
                                            <div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p>Allergy</p>
                                                        <textarea name="" id="" class="form-control pt-3" rows="3"></textarea>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p>Reaction</p>
                                                        <textarea name="" id="" class="form-control pt-3" rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p>Substance</p>
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <p>Severity</p>
                                                        <select name="" class="form-control">
                                                        <option value=""></option>
                                                        <option value=""></option>
                                                    </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p>Reaction</p>
                                                        <select name="" class="form-control">
                                                        <option value=""></option>
                                                        <option value=""></option>
                                                    </select>
                                                    </div>

                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p>Comments</p>
                                                        <textarea name="" id="" class="form-control pt-3" rows="3"></textarea>
                                                    </div>
                                                </div>

                                            </div>
                                        </div> -->
                                        <!-- <div class="pt-4 pb-5"><button class="form-save">Save</button></div> -->
                                    </div>
                                    <?php display_layout_tabs_data_editable_history('HIS', $result_history, $result2,$pid); ?>
                                </div>
                            </div>
                        </div>
               
                    </window-dashboard>
                </div>
            </div>
        </section>

   
   
  
    <?php
}
    ?>



<script>

$(document).ready(function() {

    $(".xx").click(function() {
        $(this)
            .closest(".row")
            .addClass("activatetextarea")
    });
});

function family_history_submit(){
    $webroot=  "<?php echo $GLOBALS['webroot'];?>";


    $.ajax({
        type: 'POST',
        url: $webroot+"/interface/main/history_details_save.php",
        data: $('#family_history').serialize(),   
        success: function(data){
        // alert(data);
        location.reload();

        // console.log(data);       
        }
    });
}


</script>

<script>

function submit_relatives(){

     $webroot=  "<?php echo $GLOBALS['webroot'];?>";
     $.ajax({
        type: 'POST',
         url: $webroot+"/interface/new/new_relatives_save.php",
        data: $('#relatives_tab').serialize(),   
         success: function(data){
         alert(data);
       console.log(data);
        
         }
     });

 }

 </script>

 <script>
     $(function() {
    $("#btnadmit17").bind("click", function() {
        var div = $("<div class='bodypart17' />");
        div.html(GetDynamicTextBox17(""));
        $("#TextBoxContainer17").append(div);
    });
    $("body").on("click", ".remove17", function() {
        if(confirm("Are you sure?")){
            thiss=$(this);
            $id=thiss.attr('ids');
            // alert($id);
            $webroot=  "<?php echo $GLOBALS['webroot'];?>";
            $.ajax({
                type: 'POST',
                url: $webroot+"/interface/main/delete_details.php",
                data: { id : $id, action : 'delete' , form: 'medical_history'},  
                success: function(data){
                // alert(data);
                // console.log(data);
                    thiss.closest(".bodypart17").remove();
                }
            });


           
        }

    });
});

function GetDynamicTextBox17(value) {
    return `<br> <hr>  <div class="row">
    <div class="col-sm-12 pt-2 delete-row">
        <p>Name of Condition</p>
        <input type="text" name="medical_condition[]" id=" " class="form-control">
        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx">
    </div>
    <div class="col-sm-12 pt-2 delete-row">
        <p>Description</p>
        <textarea name="medical_des[]" id="" rows="4 " class="form-control  pt-3"></textarea>
        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove17" alt="">
    </div>
</div>`
}

$(function() {

    
    $("#btnadmit18").bind("click", function() {
        var div = $("<div class='bodypart18' />");
        div.html(GetDynamicTextBox18(""));
        $("#TextBoxContainer18").append(div);
    });
    $("body").on("click", ".remove18", function() {
        if(confirm("Are you sure?")){
            thiss=$(this);
            $id=thiss.attr('ids');
          
            $webroot=  "<?php echo $GLOBALS['webroot'];?>";
            $.ajax({
                type: 'POST',
                url: $webroot+"/interface/main/delete_details.php",
                data: { id : $id, action : 'delete' , form: 'surgical_history'},  
                success: function(data){
              
                    thiss.closest(".bodypart18").remove();
                }
            });


           
        }
     
    });
});

function GetDynamicTextBox18(value) {

    
    return `<br> <hr>  <div class="row activatetextarea">
                <div class="col-sm-8 pt-2">
                    <p>Name of Condition</p>
                    <input type="text" name="surgical_condition[]" id=" " class="form-control active-text">
                </div>
                <div class="col-sm-4 pt-2 delete-row">
                    <p> Date</p>
                    <input type="date" name="surgical_date[]" id=" " class="form-control active-text">
                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx">
                </div>
                <div class="col-sm-12 pt-2 delete-row">
                    <p>Description</p>
                    <textarea name="surgical_desc[]" id="" rows="4 " class="form-control active-text pt-3"></textarea>
                    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove18 removeicon" alt="">
                </div>
</div>`
}
 



</script>
<script>
$(function() {
    $webroot="<?php echo $GLOBALS['webroot']?>";  
    $("#allergy_list").removeClass('hidedata'); 
    $("#allergy_data").addClass('hidedata'); 
    $('#addPlus').removeClass('hidedata');
    $('#allergy_list').load($webroot+"/interface/patient_file/summary/stats_full.php?active=all&category=allergy");


    //  MEDICATION
    $form_name='theform_medication';
        $webroot="<?php echo $GLOBALS['webroot']?>";
        $("#medication_list").removeClass('hidedata');
        $("#medication_data").addClass('hidedata');
        $('#medication_list').load($webroot+"/interface/patient_file/summary/stats_full_med.php?active=all&category=medication");
    // $("#menu5").click(function() {
    //     $form_name='theform_allergy';
    //     $("#allergy_list").removeClass('hidedata'); 
    //     $("#allergy_data").addClass('hidedata'); 
    //     $('#addPlus').removeClass('hidedata');
    //     $('#allergy_list').load($webroot+"/interface/patient_file/summary/stats_full.php?active=all&category=allergy");
    // });

    $('#addmore_btn').click(function(){
        
        $webroot="<?php echo $GLOBALS['webroot']?>";
        $("#allergy_list").addClass('hidedata');
        $("#allergy_data").removeClass('hidedata');
        $('#addPlus').addClass('hidedata');
        $("#allergy_data").load($webroot+"/interface/patient_file/summary/add_edit_issue.php?issue=0&thistype=allergy", function(){
          
        });
    });


    // $(".edit_data").click(function() { clickEdit(this.id,0); 
    
    // });

        // medication
    // $("#medicationData").click(function() {
    //     $form_name='theform_medication';
    //     $webroot="<?php echo $GLOBALS['webroot']?>";
    //     $("#medication_list").removeClass('hidedata');
    //     $("#medication_data").addClass('hidedata');
    //     $('#medication_list').load($webroot+"/interface/patient_file/summary/stats_full_med.php?active=all&category=medication");
    // });

    $('#addmore_btn1 ').click(function(){
        // alert();
        $webroot="<?php echo $GLOBALS['webroot']?>";
        $("#medication_list").addClass('hidedata');
        $("#addnew").addClass('hidedata');
        $("#medication_data").removeClass('hidedata');
        $("#medication_data").load($webroot+"/interface/patient_file/summary/add_edit_issue_med.php?issue=0&thistype=medication", function(){
            // alert();
        });
    });  

});
</script>




    </body>

</html>