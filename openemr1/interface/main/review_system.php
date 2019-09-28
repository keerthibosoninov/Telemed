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




// km

// $ros = new FormROS();



// for test
$pid=1;

require_once("../forms/ros/FormROS.class.php");
$form = new FormROS(15);

$weight_ch=$form->get_weight_change();
$get_anorexia=$form->get_anorexia();
$get_night_sweats=$form->get_night_sweats();
$get_heat_or_cold=$form->get_heat_or_cold();
$get_weakness=$form->get_weakness();
$get_fever=$form->get_fever();
$get_insomnia=$form->get_insomnia();
$get_intolerance=$form->get_intolerance();
$get_fatigue=$form->get_fatigue();
$get_chills=$form->get_chills();
$get_irritability=$form->get_irritability();

//
$get_change_in_vision=$form->get_change_in_vision();
$get_irritation=$form->get_irritation();
$get_double_vision=$form->get_double_vision();
$get_glaucoma_history=$form->get_glaucoma_history();
$get_redness=$form->get_redness();
$get_blind_spots=$form->get_blind_spots();
$get_eye_pain=$form->get_eye_pain();
$get_excessive_tearing=$form->get_excessive_tearing();
$get_photophobia=$form->get_photophobia();
//

$get_hearing_loss=$form->get_hearing_loss();
$get_vertigo=$form->get_vertigo();
$get_sore_throat=$form->get_sore_throat();
$get_nosebleed=$form->get_nosebleed();
$get_discharge=$form->get_discharge();
$get_tinnitus=$form->get_tinnitus();
$get_sinus_problems=$form->get_sinus_problems();
$get_snoring=$form->get_snoring();
$get_pain=$form->get_pain();
$get_frequent_colds=$form->get_frequent_colds();
$get_post_nasal_drip=$form->get_post_nasal_drip();
$get_apnea=$form->get_apnea();
// //


$get_breast_mass=$form->get_breast_mass();
$get_abnormal_mammogram=$form->get_abnormal_mammogram();
$get_breast_discharge=$form->get_breast_discharge();
$get_biopsy=$form->get_biopsy();
$get_cough=$form->get_cough();
$get_wheezing=$form->get_wheezing();
$get_copd=$form->get_copd();
$get_sputum=$form->get_sputum();
$get_hemoptsyis=$form->get_hemoptsyis();
$get_shortness_of_breath=$form->get_shortness_of_breath();
$get_asthma=$form->get_asthma();

// //
$get_chest_pain=$form->get_chest_pain();
$get_pnd=$form->get_pnd();
$get_peripheal=$form->get_peripheal();
$get_history_murmur=$form->get_history_murmur();
$get_palpitation=$form->get_palpitation();
$get_doe=$form->get_doe() ;
$get_edema=$form->get_edema();
$get_arrythmia=$form->get_arrythmia();
$get_syncope=$form->get_syncope();
$get_orthopnea=$form->get_orthopnea();
$get_legpain_cramping=$form->get_legpain_cramping();
$get_heart_problem=$form->get_heart_problem();

//
$get_dysphagia=$form->get_dysphagia();
$get_belching=$form->get_belching();
$get_vomiting=$form->get_vomiting();
$get_food_intolerance=$form->get_food_intolerance();
$get_hematochezia=$form->get_hematochezia();
$get_constipation=$form->get_constipation();
$get_heartburn=$form->get_heartburn();
$get_flatulence=$form->get_flatulence();
$get_hematemesis=$form->get_hematemesis();
$get_hepatitis=$form->get_hepatitis();
$get_changed_bowel=$form->get_changed_bowel();
$get_bloating=$form->get_bloating();
$get_nausea=$form->get_nausea();
$get_gastro_pain=$form->get_gastro_pain();
$get_jaundice=$form->get_jaundice();
$get_diarrhea=$form->get_diarrhea();

//
$get_polyuria=$form->get_polyuria();
$get_hematuria=$form->get_hematuria();
$get_incontinence=$form->get_incontinence();
$get_polydypsia=$form->get_polydypsia();
$get_frequency=$form->get_frequency();
$get_renal_stones=$form->get_renal_stones();
$get_dysuria=$form->get_dysuria();
$get_urgency=$form->get_urgency();
$get_utis=$form->get_utis();

//
$get_hesitancy=$form->get_hesitancy();
$get_nocturia=$form->get_nocturia();
$get_dribbling=$form->get_dribbling();
$get_erections=$form->get_erections();
$get_stream=$form->get_stream();
$get_ejaculations=$form->get_ejaculations();

// //
$get_g=$form->get_g();
$get_lc=$form->get_lc();
$get_lmp=$form->get_lmp();
$get_f_symptoms=$form->get_f_symptoms();
$get_p=$form->get_p();
$get_mearche=$form->get_mearche();
$get_f_frequency=$form->get_f_frequency();
$get_abnormal_hair_growth=$form->get_abnormal_hair_growth();
$get_ap=$form->get_ap();
$get_menopause=$form->get_menopause();
$get_f_flow=$form->get_f_flow();
$get_f_hirsutism=$form->get_f_hirsutism();

// //
$get_joint_pain=$form->get_joint_pain();
$get_m_warm=$form->get_m_warm();
$get_m_aches=$form->get_m_aches();
$get_swelling=$form->get_swelling();
$get_m_stiffness=$form->get_m_stiffness();
$get_fms=$form->get_fms();
$get_m_redness=$form->get_m_redness();
$get_muscle=$form->get_muscle();
$get_arthritis=$form->get_arthritis();

//
$get_loc=$form->get_loc();
$get_tia=$form->get_tia();
$get_paralysis=$form->get_paralysis();
$get_dementia=$form->get_dementia();
$get_seizures=$form->get_seizures();
$get_n_numbness=$form->get_n_numbness();
$get_intellectual_decline=$form->get_intellectual_decline();
$get_n_headache=$form->get_n_headache();
$get_stroke=$form->get_stroke();
$get_n_weakness=$form->get_n_weakness();
$get_memory_problems=$form->get_memory_problems();
// //
$get_s_cancer=$form->get_s_cancer();
$get_s_other=$form->get_s_other();
$get_psoriasis=$form->get_psoriasis();
$get_s_disease=$form->get_s_disease();
$get_s_acne=$form->get_s_acne();


// 
$get_p_diagnosis=$form->get_p_diagnosis();
$get_anxiety=$form->get_anxiety();
$get_p_medication=$form->get_p_medication() ;
$get_social_difficulties=$form->get_social_difficulties();
$get_depression=$form->get_depression();


// 
$get_thyroid_problems=$form->get_thyroid_problems();
$get_diabetes=$form->get_diabetes();
$get_abnormal_blood=$form->get_abnormal_blood();





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
                                <div>
                                    <form name="ros" class="form-horizontal" method="post" action="<?php echo $GLOBALS['webroot']; ?>/interface/forms/ros/save.php" onsubmit="return top.restoreSession()">
                                        <div class="row pt-5 pb-5">

                                            <div class="col-lg-6">
                                            <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo">
                                                        <p>Constitutional</p>
                                                    </div>
                                                    <div id="demo" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Weight Change</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="weight_change" <?php if($weight_ch=='N/A') echo "checked"; ?>  name="weight_change" value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="weight_change" <?php if($weight_ch=='YES') echo "checked"; ?> name="weight_change" value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="weight_change" <?php  if($weight_ch=='NO') echo "checked"; ?>  name="weight_change" value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Anorexia</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="anorexia" <?php if($get_anorexia=='N/A') echo "checked"; ?> name="anorexia" value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="anorexia" <?php if($get_anorexia=='YES') echo "checked"; ?> name="anorexia" value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="anorexia" <?php  if($get_anorexia=='NO') echo "checked"; ?> name="anorexia" value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Night Sweats</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="night_sweats" <?php if($get_night_sweats=='N/A') echo "checked"; ?> name="night_sweats" value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="night_sweats"  <?php if($get_night_sweats=='YES') echo "checked"; ?> name="night_sweats" value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="night_sweats" <?php  if($get_night_sweats=='NO') echo "checked"; ?> name="night_sweats" value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Heat or Cold</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="heat_or_cold" <?php if($get_heat_or_cold=='N/A') echo "checked"; ?> name="heat_or_cold" value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="heat_or_cold"  <?php if($get_heat_or_cold=='YES') echo "checked"; ?> name="heat_or_cold" value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="heat_or_cold" <?php  if($get_heat_or_cold=='NO') echo "checked"; ?> name="heat_or_cold" value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Weakness</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="weakness" <?php if($get_weakness=='N/A') echo "checked"; ?> name="weakness" value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="weakness"  <?php if($get_weakness=='YES') echo "checked"; ?> name="weakness" value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="weakness" <?php  if($get_weakness=='NO') echo "checked"; ?> name="weakness" value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Fever</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="fever" <?php if($get_fever=='N/A') echo "checked"; ?> name="fever" value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="fever" <?php if($get_fever=='YES') echo "checked"; ?> name="fever" value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="fever" <?php  if($get_fever=='NO') echo "checked"; ?> name="fever" value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Insomnia</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="insomnia" <?php if($get_insomnia=='N/A') echo "checked"; ?> name="insomnia" value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="insomnia" <?php if($get_insomnia=='YES') echo "checked"; ?> name="insomnia" value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="insomnia" <?php  if($get_insomnia=='NO') echo "checked"; ?> name="insomnia" value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Intolerance</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="intolerance" <?php if($get_intolerance=='N/A') echo "checked"; ?> name="intolerance" value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="intolerance" <?php if($get_intolerance=='YES') echo "checked"; ?> name="intolerance" value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="intolerance"  <?php  if($get_intolerance=='NO') echo "checked"; ?> name="intolerance" value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Fatigue</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="fatigue" <?php if($get_fatigue=='N/A') echo "checked"; ?> name="fatigue" value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="fatigue" <?php if($get_fatigue=='YES') echo "checked"; ?> name="fatigue" value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="fatigue" <?php  if($get_fatigue=='NO') echo "checked"; ?>  name="fatigue" value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Chills</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="chills" <?php if($get_chills=='N/A') echo "checked"; ?> name="chills" value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="chills" <?php if($get_chills=='YES') echo "checked"; ?> name="chills" value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="chills" <?php  if($get_chills=='NO') echo "checked"; ?>  name="chills" value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Irritability</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="irritability" <?php if($get_irritability=='N/A') echo "checked"; ?> name="irritability" value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="irritability" <?php if($get_irritability=='YES') echo "checked"; ?> name="irritability" value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="irritability" <?php  if($get_irritability=='NO') echo "checked"; ?>  name="irritability" value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!--  constitution  -->
                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo3">
                                                        <p>E.N.T.</p>
                                                    </div>
                                                    <div id="demo3" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Hearing Loss</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hearing_loss" name="hearing_loss" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hearing_loss" name="hearing_loss" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hearing_loss" name="hearing_loss" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Vertigo</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="vertigo" name="vertigo" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="vertigo" name="vertigo" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="vertigo" name="vertigo" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Sore Throat</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="sore_throat" name="sore_throat" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="sore_throat" name="sore_throat" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="sore_throat" name="sore_throat" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Nosebleed</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="nosebleed" name="nosebleed" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="nosebleed" name="nosebleed" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="nosebleed" name="nosebleed" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Discharge</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="discharge" name="discharge" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="discharge" name="discharge" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="discharge" name="discharge" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Tinnitus</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="tinnitus" name="tinnitus" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="tinnitus" name="tinnitus" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="tinnitus" name="tinnitus" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Sinus Problems</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="sinus_problems" name="sinus_problems" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="sinus_problems" name="sinus_problems" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="sinus_problems" name="sinus_problems" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Snoring</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="snoring" name="snoring" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="snoring" name="snoring" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="snoring" name="snoring" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Pain</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="pain" name="pain" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="pain" name="pain" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="pain" name="pain" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Frequent Colds</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="frequent_colds" name="frequent_colds" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="frequent_colds" name="frequent_colds" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="frequent_colds" name="frequent_colds" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Post Nasal Drip</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="post_nasal_drip" name="post_nasal_drip" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="post_nasal_drip" name="post_nasal_drip" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="post_nasal_drip" name="post_nasal_drip" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Apnea</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="apnea" name="apnea" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="apnea" name="apnea" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="apnea" name="apnea" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo4">
                                                        <p>Cardiovascular</p>
                                                    </div>
                                                    <div id="demo4" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Chest Pain</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="chest_pain" name="chest_pain" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="chest_pain" name="chest_pain" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="chest_pain" name="chest_pain" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">PND</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="pnd" name="pnd" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="pnd" name="pnd" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="pnd" name="pnd" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Peripheral</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="peripheal" name="peripheal" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="peripheal" name="peripheal" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="peripheal" name="peripheal" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">History of Heart Murmur</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="history_murmur" name="history_murmur" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="history_murmur" name="history_murmur" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="history_murmur" name="history_murmur" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Palpitation</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="palpitation" name="palpitation" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="palpitation" name="palpitation" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="palpitation" name="palpitation" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">DOE</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="doe" name="doe" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="doe" name="doe" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="doe" name="doe" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Edema</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="edema" name="edema" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="edema" name="edema" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="edema" name="edema" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Arrythmia</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="arrythmia" name="arrythmia" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="arrythmia" name="arrythmia" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="arrythmia" name="arrythmia" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Syncope</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="syncope" name="syncope" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="syncope" name="syncope" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="syncope" name="syncope" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Orthopnea</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="orthopnea" name="orthopnea" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="orthopnea" name="orthopnea" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="orthopnea" name="orthopnea" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Leg Pain/Cramping</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="legpain_cramping" name="legpain_cramping" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="legpain_cramping" name="legpain_cramping" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="legpain_cramping" name="legpain_cramping" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Heart Problem</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="heart_problem" name="heart_problem" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="heart_problem" name="heart_problem" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="heart_problem" name="heart_problem" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo5">
                                                        <p>Genito Urinary - General</p>
                                                    </div>
                                                    <div id="demo5" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Polyuria</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="polyuria" name="polyuria" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="polyuria" name="polyuria" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="polyuria" name="polyuria" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Hematuria</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hematuria" name="hematuria" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hematuria" name="hematuria" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hematuria" name="hematuria" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Incontinence</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="incontinence" name="incontinence" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="incontinence" name="incontinence" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="incontinence" name="incontinence" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Polydypsia</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="polydypsia" name="polydypsia" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="polydypsia" name="polydypsia" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="polydypsia" name="polydypsia" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Frequency</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="frequency" name="frequency" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="frequency" name="frequency" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="frequency" name="frequency" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Renal Stones</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="renal_stones" name="renal_stones" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="renal_stones" name="renal_stones" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="renal_stones" name="renal_stones" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Dysuria</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="dysuria" name="dysuria" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="dysuria" name="dysuria" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="dysuria" name="dysuria" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Urgency</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="urgency" name="urgency" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="urgency" name="urgency" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="urgency" name="urgency" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">UTIs</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="utis" name="utis" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="utis" name="utis" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="utis" name="utis" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo6">
                                                        <p>Genito Urinary - Male</p>
                                                    </div>
                                                    <div id="demo6" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Hesitancy</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hesitancy" name="hesitancy" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hesitancy" name="hesitancy" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hesitancy" name="hesitancy" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Nocturia</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="nocturia" name="nocturia" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="nocturia" name="nocturia" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="nocturia" name="nocturia" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Dribbling</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="dribbling" name="dribbling" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="dribbling" name="dribbling" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="dribbling" name="dribbling" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Erections</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="erections" name="erections" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="erections" name="erections" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="erections" name="erections" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Stream</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="stream" name="stream" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="stream" name="stream" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="stream" name="stream" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Ejaculations</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="ejaculations" name="ejaculations" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="ejaculations" name="ejaculations" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="ejaculations" name="ejaculations" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo7">
                                                        <p>Musculoskeletal</p>
                                                    </div>
                                                    <div id="demo7" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Chronic Joint Pain</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="joint_pain" name="joint_pain" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="joint_pain" name="joint_pain" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="joint_pain" name="joint_pain" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Warm</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="m_warm" name="m_warm" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="m_warm" name="m_warm" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="m_warm" name="m_warm" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Aches</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="m_aches" name="m_aches" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="m_aches" name="m_aches" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="m_aches" name="m_aches" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Swelling</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="swelling" name="swelling" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="swelling" name="swelling" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="swelling" name="swelling" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Stiffness</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="m_stiffness" name="m_stiffness" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="m_stiffness" name="m_stiffness" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="m_stiffness" name="m_stiffness" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">FMS</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="fms" name="fms" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="fms" name="fms" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="fms" name="fms" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Redness</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="m_redness" name="m_redness" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="m_redness" name="m_redness" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="m_redness" name="m_redness" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Muscle</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="muscle" name="muscle" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="muscle" name="muscle" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="muscle" name="muscle" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Arthritis</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="arthritis" name="arthritis" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="arthritis" name="arthritis" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="arthritis" name="arthritis" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo66">
                                                        <p>Psychiatric</p>
                                                    </div>
                                                    <div id="demo66" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Psychiatric Diagnosis</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="p_diagnosis" name="p_diagnosis" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="p_diagnosis" name="p_diagnosis" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="p_diagnosis" name="p_diagnosis" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Anxiety</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="anxiety" name="anxiety" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="anxiety" name="anxiety" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="anxiety" name="anxiety" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Psychiatric Medication</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="p_medication" name="p_medication" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="p_medication" name="p_medication" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="p_medication" name="p_medication" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Social Difficulties</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="social_difficulties" name="social_difficulties" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="social_difficulties" name="social_difficulties" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="social_difficulties" name="social_difficulties" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Depression</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="depression" name="depression" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="depression" name="depression" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="depression" name="depression" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo77">
                                                        <p>Endocrine</p>
                                                    </div>
                                                    <div id="demo77" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Thyroid Problems</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="thyroid_problems" name="thyroid_problems" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="thyroid_problems" name="thyroid_problems" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="thyroid_problems" name="thyroid_problems" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Diabetes</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="diabetes" name="diabetes" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="diabetes" name="diabetes" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="diabetes" name="diabetes" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Abnormal Blood Test</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="abnormal_blood" name="abnormal_blood" <?php if($get_irritability=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="abnormal_blood" name="abnormal_blood" <?php if($get_irritability=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="abnormal_blood" name="abnormal_blood" <?php  if($get_irritability=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo1">
                                                        <p>Eyes</p>
                                                    </div>
                                                    <div id="demo1" class="collapse collapse-border show">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Change in Vision</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="change_in_vision" name="change_in_vision" <?php if($get_change_in_vision=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="change_in_vision" name="change_in_vision" <?php if($get_change_in_vision=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="change_in_vision" name="change_in_vision" <?php  if($get_change_in_vision=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Irritation</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="irritation" name="irritation" <?php if($get_irritation=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="irritation" name="irritation" <?php if($get_irritation=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="irritation" name="irritation" <?php  if($get_irritation=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Double Vision</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="double_vision" name="double_vision" <?php if($get_double_vision=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="double_vision" name="double_vision" <?php if($get_double_vision=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="double_vision" name="double_vision" <?php  if($get_double_vision=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Family History of Glaucoma</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="glaucoma_history" name="glaucoma_history" <?php if($get_glaucoma_history=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="glaucoma_history" name="glaucoma_history" <?php if($get_glaucoma_history=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="glaucoma_history" name="glaucoma_history" <?php  if($get_glaucoma_history=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Redness</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="redness" name="redness" <?php if($get_redness=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="redness" name="redness" <?php if($get_redness=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="redness" name="redness" <?php  if($get_redness=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Blind Spots</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="blind_spots" name="blind_spots" <?php if($get_blind_spots=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="blind_spots" name="blind_spots" <?php if($get_blind_spots=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="blind_spots" name="blind_spots" <?php  if($get_blind_spots=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Eye Pain</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="eye_pain" name="eye_pain" <?php if($get_eye_pain=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="eye_pain" name="eye_pain" <?php if($get_eye_pain=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="eye_pain" name="eye_pain" <?php  if($get_eye_pain=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Excessive Tearing</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="excessive_tearing" name="excessive_tearing" <?php if($get_excessive_tearing=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="excessive_tearing" name="excessive_tearing" <?php if($get_excessive_tearing=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="excessive_tearing" name="excessive_tearing" <?php  if($get_excessive_tearing=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Photophobia</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="photophobia" name="photophobia" <?php if($get_photophobia=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="photophobia" name="photophobia" <?php if($get_photophobia=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="photophobia" name="photophobia" <?php  if($get_photophobia=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo8">
                                                        <p>Respiratory</p>
                                                    </div>
                                                    <div id="demo8" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Cough</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="cough" name="cough" <?php if($get_cough=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="cough" name="cough" <?php if($get_cough=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="cough" name="cough" <?php  if($get_cough=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Wheezing</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="wheezing" name="wheezing" <?php if($get_wheezing=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="wheezing" name="wheezing" <?php if($get_wheezing=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="wheezing" name="wheezing" <?php  if($get_wheezing=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">COPD</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="copd" name="copd" <?php if($get_copd=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="copd" name="copd" <?php if($get_copd=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="copd" name="copd" <?php  if($get_copd=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Sputum</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="sputum" name="sputum" <?php if($get_sputum=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="sputum" name="sputum" <?php if($get_sputum=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="sputum" name="sputum" <?php  if($get_sputum=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Hemoptysis</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hemoptsyis" name="hemoptsyis" <?php if($get_hemoptsyis=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hemoptsyis" name="hemoptsyis" <?php if($get_hemoptsyis=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hemoptsyis" name="hemoptsyis" <?php  if($get_hemoptsyis=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Shortness of Breath</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="shortness_of_breath" name="shortness_of_breath" <?php if($get_shortness_of_breath=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="shortness_of_breath" name="shortness_of_breath" <?php if($get_shortness_of_breath=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="shortness_of_breath" name="shortness_of_breath" <?php  if($get_shortness_of_breath=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Asthma</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="asthma" name="asthma" <?php if($get_asthma=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="asthma" name="asthma" <?php if($get_asthma=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="asthma" name="asthma" <?php  if($get_asthma=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo10">
                                                        <p>Gastro Intestinal</p>
                                                    </div>
                                                    <div id="demo10" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Dysphagia</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="dysphagia" name="dysphagia" <?php if($get_dysphagia=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="dysphagia" name="dysphagia" <?php if($get_dysphagia=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="dysphagia" name="dysphagia" <?php  if($get_dysphagia=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Belching</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="belching" name="belching" <?php if($get_belching=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="belching" name="belching" <?php if($get_belching=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="belching" name="belching" <?php  if($get_belching=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Vomiting</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="vomiting" name="vomiting" <?php if($get_vomiting=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="vomiting" name="vomiting" <?php if($get_vomiting=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="vomiting" name="vomiting" <?php  if($get_vomiting=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Food Intolerance</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="food_intolerance" name="food_intolerance" <?php if($get_food_intolerance=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="food_intolerance" name="food_intolerance" <?php if($get_food_intolerance=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="food_intolerance" name="food_intolerance" <?php  if($get_food_intolerance=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Hematochezia</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hematochezia" name="hematochezia" <?php if($get_hematochezia=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hematochezia" name="hematochezia" <?php if($get_hematochezia=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hematochezia" name="hematochezia" <?php  if($get_hematochezia=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Constipation</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="constipation" name="constipation" <?php if($get_constipation=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="constipation" name="constipation" <?php if($get_constipation=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="constipation" name="constipation" <?php  if($get_constipation=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Heartburn</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="heartburn" name="heartburn" <?php if($get_heartburn=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="heartburn" name="heartburn" <?php if($get_heartburn=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="heartburn" name="heartburn" <?php  if($get_heartburn=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Flatulence</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="flatulence" name="flatulence" <?php if($get_flatulence=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="flatulence" name="flatulence" <?php if($get_flatulence=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="flatulence" name="flatulence" <?php  if($get_flatulence=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Hematemesis</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hematemesis" name="hematemesis" <?php if($get_hematemesis=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hematemesis" name="hematemesis" <?php if($get_hematemesis=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hematemesis" name="hematemesis" <?php  if($get_hematemesis=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Hepatitis</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hepatitis" name="hepatitis" <?php if($get_hepatitis=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hepatitis" name="hepatitis" <?php if($get_hepatitis=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="hepatitis" name="hepatitis" <?php  if($get_hepatitis=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Changed Bowel</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="changed_bowel" name="changed_bowel" <?php if($get_changed_bowel=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="changed_bowel" name="changed_bowel" <?php if($get_changed_bowel=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="changed_bowel" name="changed_bowel" <?php  if($get_changed_bowel=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Bloating</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="bloating" name="bloating" <?php if($get_bloating=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="bloating" name="bloating" <?php if($get_bloating=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="bloating" name="bloating" <?php  if($get_bloating=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Nausea</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="nausea" name="nausea" <?php if($get_nausea=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="nausea" name="nausea" <?php if($get_nausea=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="nausea" name="nausea" <?php  if($get_nausea=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Pain</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="gastro_pain" name="gastro_pain" <?php if($get_gastro_pain=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="gastro_pain" name="gastro_pain" <?php if($get_gastro_pain=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="gastro_pain" name="gastro_pain" <?php  if($get_gastro_pain=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Jaundice</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="jaundice" name="jaundice" <?php if($get_jaundice=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="jaundice" name="jaundice" <?php if($get_jaundice=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="jaundice" name="jaundice" <?php  if($get_jaundice=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Diarrhea</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="diarrhea" name="diarrhea" <?php if($get_diarrhea=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="diarrhea" name="diarrhea" <?php if($get_diarrhea=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="diarrhea" name="diarrhea" <?php  if($get_diarrhea=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo12">
                                                        <p>Genito Urinary - Female</p>
                                                    </div>
                                                    <div id="demo12" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Female G</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="g" name="g" <?php if($get_g=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="g" name="g" <?php if($get_g=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="g" name="g" <?php  if($get_g=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Female LC</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="lc" name="lc" <?php if($get_lc=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="lc" name="lc" <?php if($get_lc=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="lc" name="lc" <?php  if($get_lc=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">LMP</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="lmp" name="lmp" <?php if($get_lmp=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="lmp" name="lmp" <?php if($get_lmp=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="lmp" name="lmp" <?php  if($get_lmp=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Symptoms</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="f_symptoms" name="f_symptoms" <?php if($get_f_symptoms=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="f_symptoms" name="f_symptoms" <?php if($get_f_symptoms=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="f_symptoms" name="f_symptoms" <?php  if($get_f_symptoms=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Female P</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="p" name="p" <?php if($get_p=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="p" name="p" <?php if($get_p=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="p" name="p" <?php  if($get_p=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Menarche</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="mearche" name="mearche" <?php if($get_mearche=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="mearche" name="mearche" <?php if($get_mearche=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="mearche" name="mearche" <?php  if($get_mearche=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Frequency</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="f_frequency" name="f_frequency" <?php if($get_f_frequency=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="f_frequency" name="f_frequency" <?php if($get_f_frequency=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="f_frequency" name="f_frequency" <?php  if($get_f_frequency=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Abnormal Hair Growth</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="abnormal_hair_growth" name="abnormal_hair_growth" <?php if($get_abnormal_hair_growth=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="abnormal_hair_growth" name="abnormal_hair_growth" <?php if($get_abnormal_hair_growth=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="abnormal_hair_growth" name="abnormal_hair_growth" <?php  if($get_abnormal_hair_growth=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Female AP</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="ap" name="ap" <?php if($get_ap=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="ap" name="ap" <?php if($get_ap=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="ap" name="ap" <?php  if($get_ap=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Menopause</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="menopause" name="menopause" <?php if($get_menopause=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="menopause" name="menopause" <?php if($get_menopause=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="menopause" name="menopause" <?php  if($get_menopause=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Flow</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="f_flow" name="f_flow" <?php if($get_f_flow=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="f_flow" name="f_flow" <?php if($get_f_flow=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="f_flow" name="f_flow" <?php  if($get_f_flow=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">F/H Female Hirsutism/Striae</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="f_hirsutism" name="f_hirsutism" <?php if($get_f_hirsutism=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="f_hirsutism" name="f_hirsutism" <?php if($get_f_hirsutism=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="f_hirsutism" name="f_hirsutism" <?php  if($get_f_hirsutism=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo13">
                                                        <p>Neurologic</p>
                                                    </div>
                                                    <div id="demo13" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">LOC</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="loc" name="loc" <?php if($get_loc=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="loc" name="loc" <?php if($get_loc=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="loc" name="loc" <?php  if($get_loc=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">TIA</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="tia" name="tia" <?php if($get_tia=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="tia" name="tia" <?php if($get_tia=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="tia" name="tia" <?php  if($get_tia=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Paralysis</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="paralysis" name="paralysis" <?php if($get_paralysis=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="paralysis" name="paralysis" <?php if($get_paralysis=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="paralysis" name="paralysis" <?php  if($get_paralysis=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Dementia</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="dementia" name="dementia" <?php if($get_dementia=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="dementia" name="dementia" <?php if($get_dementia=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="dementia" name="dementia" <?php  if($get_dementia=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Seizures</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="seizures" name="seizures" <?php if($get_seizures=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="seizures" name="seizures" <?php if($get_seizures=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="seizures" name="seizures" <?php  if($get_seizures=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Numbness</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="n_numbness" name="n_numbness" <?php if($get_n_numbness=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="n_numbness" name="n_numbness" <?php if($get_n_numbness=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="n_numbness" name="n_numbness" <?php  if($get_n_numbness=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Intellectual Decline</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="intellectual_decline" name="intellectual_decline" <?php if($get_intellectual_decline=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="intellectual_decline" name="intellectual_decline" <?php if($get_intellectual_decline=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="intellectual_decline" name="intellectual_decline" <?php  if($get_intellectual_decline=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Headache</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="n_headache" name="n_headache" <?php if($get_n_headache=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="n_headache" name="n_headache" <?php if($get_n_headache=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="n_headache" name="n_headache" <?php  if($get_n_headache=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Stroke</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="stroke" name="stroke" <?php if($get_stroke=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="stroke" name="stroke" <?php if($get_stroke=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="stroke" name="stroke" <?php  if($get_stroke=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Weakness</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="n_weakness" name="n_weakness" <?php if($get_n_weakness=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="n_weakness" name="n_weakness" <?php if($get_n_weakness=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="n_weakness" name="n_weakness" <?php  if($get_n_weakness=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Memory Problems</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="memory_problems" name="memory_problems" <?php if($get_memory_problems=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="memory_problems" name="memory_problems" <?php if($get_memory_problems=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="memory_problems" name="memory_problems" <?php  if($get_memory_problems=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                           
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="review-box">
                                                    <div class="name-box" data-toggle="collapse" data-target="#demo14">
                                                        <p>Skin</p>
                                                    </div>
                                                    <div id="demo14" class="collapse show collapse-border">
                                                        <div class="container-fluid">
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Cancer</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="s_cancer" name="s_cancer" <?php if($get_s_cancer=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="s_cancer" name="s_cancer" <?php if($get_s_cancer=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="s_cancer" name="s_cancer" <?php  if($get_s_cancer=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Other</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="s_other" name="s_other" <?php if($get_s_other=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="s_other" name="s_other" <?php if($get_s_other=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="s_other" name="s_other" <?php  if($get_s_other=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Psoriasis</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="psoriasis" name="psoriasis" <?php if($get_psoriasis=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="psoriasis" name="psoriasis" <?php if($get_psoriasis=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="psoriasis" name="psoriasis" <?php  if($get_psoriasis=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Disease</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="s_disease" name="s_disease" <?php if($get_s_disease=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="s_disease" name="s_disease" <?php if($get_s_disease=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="s_disease" name="s_disease" <?php  if($get_s_disease=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="col-sm-4">
                                                                    <p class="fs-14">Acne</p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="s_acne" name="s_acne" <?php if($get_s_acne=='N/A') echo "checked"; ?> value="N/A"> N/A
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="s_acne" name="s_acne" <?php if($get_s_acne=='YES') echo "checked"; ?> value="YES"> Yes
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <input type="radio" class="" id="s_acne" name="s_acne" <?php  if($get_s_acne=='NO') echo "checked"; ?> value="NO"> No
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>











                                        </div>

                                        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
                                        <input type="hidden" name="id" value="" />
                                        <input type="hidden" name="pid" value="<?php echo $pid;?>">
                                        <input type="hidden" name="process" value="true">
                                     
                                        <div class="pt-4 pb-5"><button class="form-save" type="submit">Save</button></div>
                                    </form>
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






    </body>

</html>