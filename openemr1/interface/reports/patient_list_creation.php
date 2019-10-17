<?php
/**
 * This report lists all the demographics allergies,problems,drugs and lab results
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Ensoftek, Inc
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("../drugs/drugs.inc.php");
require_once("$srcdir/payment_jav.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}
// String for csv_PA
function qescape($str)
{
    $str = str_replace('\\', '\\\\', $str);
    return str_replace('"', '\\"', $str);
}

$search_options = array("Demographics"=>xl("Demographics"),"Problems"=>xl("Problems"),"Medications"=>xl("Medications"),"Allergies"=>xl("Allergies"),"Lab results"=>xl("Lab Results"),"Communication"=>xl("Communication"));
$comarr = array("allow_sms"=>xl("Allow SMS"),"allow_voice"=>xl("Allow Voice Message"),"allow_mail"=>xl("Allow Mail Message"),"allow_email"=>xl("Allow Email"));
$_POST['form_details'] = true;

$sql_date_from = (!empty($_POST['date_from'])) ? DateTimeToYYYYMMDDHHMMSS($_POST['date_from']) : date('Y-01-01 H:i:s');
$sql_date_to = (!empty($_POST['date_to'])) ? DateTimeToYYYYMMDDHHMMSS($_POST['date_to']) : date('Y-m-d H:i:s');

$patient_id = trim($_POST["patient_id"]);
$age_from = $_POST["age_from"];
$age_to = $_POST["age_to"];
$sql_gender = $_POST["gender"];
$sql_ethnicity = $_POST["cpms_ethnicity"];
$sql_race=$_POST["race"];
$form_drug_name = trim($_POST["form_drug_name"]);
$form_diagnosis = trim($_POST["form_diagnosis"]);
$form_lab_results = trim($_POST["form_lab_results"]);
$form_service_codes = trim($_POST["form_service_codes"]);
$form_immunization = trim($_POST["form_immunization"]);
$communication = trim($_POST["communication"]);
// export_CSV_PA
if ($_POST['form_csvexport']) {


    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=patient_list_creation.csv");
    header("Content-Description: File Transfer");
}
else
{


?>
<html>
    <head>

        <title>
            <?php echo xlt('Patient List Creation'); ?>
        </title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/style.css">
    <!-- <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/employee_dashboard_style.css"> -->
    <!-- <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/emp_info_css.css"> -->
    <!-- <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/vue.js"></script> -->
    <!-- <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script> -->
    <!-- <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script> -->
    <!-- <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/main.js"></script> -->



        <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

        <script language="JavaScript">
            // function Form_Validate() {
            //     var d = document.forms[0];
            //     FromDate = d.date_from.value;
            //     ToDate = d.date_to.value;
            //     if ( (FromDate.length > 0) && (ToDate.length > 0) ) {
            //         if ( FromDate > ToDate ){
            //             alert(<?php echo xlj('To date must be later than From date!'); ?>);
            //             return false;
            //         }
            //     }
            //     $("#processing").show();
            //     return true;
            // }
        </script>

        <style type="text/css">
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
                #report_image {
                    visibility: hidden;
                    display: none;
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
        <script language="javascript" type="text/javascript">
            function submitForm() {
                var d_from = new String($('#date_from').val());
                var d_to = new String($('#date_to').val());

                var d_from_arr = d_from.split('-');
                var d_to_arr = d_to.split('-');

                var dt_from = new Date(d_from_arr[0], d_from_arr[1], d_from_arr[2]);
                var dt_to = new Date(d_to_arr[0], d_to_arr[1], d_to_arr[2]);

                var mili_from = dt_from.getTime();
                var mili_to = dt_to.getTime();
                var diff = mili_to - mili_from;

                $('#date_error').css("display", "none");

                if(diff < 0) //negative
                {
                    $('#date_error').css("display", "inline");
                }
                else
                {
                    $("#form_refresh").attr("value","true");
                    top.restoreSession();
                    $("#theform").submit();
                }
            }

            //sorting changes
            function sortingCols(sort_by,sort_order)
            {
                $("#sortby").val(sort_by);
                $("#sortorder").val(sort_order);
                $("#form_refresh").attr("value","true");
                $("#theform").submit();
            }

            $(function() {
                $(".numeric_only").keydown(function(event) {
                    //alert(event.keyCode);
                    // Allow only backspace and delete
                    if ( event.keyCode == 46 || event.keyCode == 8 ) {
                        // let it happen, don't do anything
                    }
                    else {
                        if(!((event.keyCode >= 96 && event.keyCode <= 105) || (event.keyCode >= 48 && event.keyCode <= 57)))
                        {
                            event.preventDefault();
                        }
                    }
                });
                <?php if ($_POST['srch_option'] == "Communication") { ?>
                    $('#com_pref').show();
                <?php } ?>

                $('.datetimepicker').datetimepicker({
                    <?php $datetimepicker_timepicker = true; ?>
                    <?php $datetimepicker_showseconds = true; ?>
                    <?php $datetimepicker_formatInput = true; ?>
                    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                });
            });

            function printForm(e){
                 var win = top.printLogPrint ? top : opener.top;
                 win.printLogPrint(window);
                  e.preventDefault();
              //var html=$("#table-div").html();
            //  window.open("patient_list_print.php?content="+html, '_blank');
            }
        </script>


    </head>

    <body class="body_top" style="font-family: 'Open Sans', sans-serif">
        <!-- Required for the popup date selectors -->

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
                                            <p class="text-white head-p">Patient List Creation</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="body-compo" style="height:auto;">
                            <div class="container-fluid">
        <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
        <!-- <span class='title'>
        <?php echo xlt('Report - Patient List Creation');?>
        </span> -->
        <!-- Search can be done using age range, gender, and ethnicity filters.
        Search options include diagnosis, procedure, prescription, medical history, and lab results.
        -->

        <div id="report_parameters_daterange">
            <p>
            <?php echo "<span style='margin-left:5px;'><b>".xlt('Date Range').":</b>&nbsp;".text(oeFormatDateTime($sql_date_from, "global", true)) .
              " &nbsp; " . xlt('to') . " &nbsp; ". text(oeFormatDateTime($sql_date_to, "global", true))."</span>"; ?>
            <span style="margin-left:5px; " ><b><?php echo xlt('Option'); ?>:</b>&nbsp;<?php echo text($_POST['srch_option']);
            if ($_POST['srch_option'] == "Communication" && $_POST['communication'] != "") {
                if (isset($comarr[$_POST['communication']])) {
                    echo "(".text($comarr[$_POST['communication']]).")";
                } else {
                    echo "(".xlt('All').")";
                }
            }  ?></span>
            </p>
        </div>
        <form name='theform' id='theform' method='POST' action='patient_list_creation.php' >
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <!-- <div id="report_parameters"> -->
                <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
                <input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>


                        <div id="report_parameter" class="pt-4 pb-4">
                                <div class="row">

                                    <div class="col-md-2">
                                        <p>From</p>

                                        <input type='text' class='datetimepicker form-control pr-1 pl-1' name='date_from' id="date_from" size='18' value='<?php echo attr(oeFormatDateTime($sql_date_from, 0, true)); ?>'>
                                    </div>
                                    <div class="col-md-2">
                                        <p>to</p>

                                        <input type='text' class='datetimepicker form-control pr-1 pl-1' name='date_to' id="date_to" size='18' value='<?php echo attr(oeFormatDateTime($sql_date_to, 0, true)); ?>'>
                                    </div>
                                    <div class="col-md-3">
                                        <p>Option</p>

                                            <select class="form-control mt-2" name="srch_option" id="srch_option" onchange="javascript:$('#sortby').val('');$('#sortorder').val('');if(this.value == 'Communication'){ $('#communication').val('');$('#com_pref').show();}else{ $('#communication').val('');$('#com_pref').hide();}">
                                        <?php foreach ($search_options as $skey => $svalue) { ?>
                                            <option <?php echo ($_POST['srch_option'] == $skey) ? 'selected' : ''; ?> value="<?php echo attr($skey); ?>"><?php echo text($svalue); ?></option>
                                        <?php } ?>
                                    </select>
                                    </div>
                                    <div class="col-md-3">
                                        <p>Employee ID</p>
                                        <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                        <input name='patient_id' class="numeric_only form-control" type='text' id="patient_id" title='<?php echo xla('Optional numeric patient ID'); ?>' value='<?php echo attr($patient_id); ?>' size='10' maxlength='20' />
                                    </div>
                                    <div class="col-md-2">
                                        <p>gender</p>

                                            <?php echo generate_select_list('gender', 'sex', $sql_gender, 'Select Gender', 'Unassigned', '', ''); ?>
                                        </div>


                                </div>
                                <div class="pt-4 pb-5">
                                    <div class="row">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-2">
                                            <button class="form-save" onclick='$("#form_csvexport").val(""); $("#form_refresh").attr("value","true");submitForm();'>SEARCH</button>

                                        </div>
                                        <div class="col-md-2">
                                            <button class="form-save" onclick="printForm(event)">PRINT</button>

                                        </div>
                                        <div class="col-md-2">
                                          <!--   <button onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();' class="form-save">Export to CSV</button> -->

                                          <button type="button" onclick="printcsv()" class="form-save">Export to CSV</button>
                                        </div>


                                    </div>

                                </div>

                        </div>


        <!-- end of parameters -->
        <?php
    } //end not export csv
    $exdata="";

    $exdata.=  '"' . xl('Date') . '",';
    $exdata.=  '"' . xl('Employee') . '",';
    $exdata.=  '"' . xl('ID') . '",';
    $exdata.=  '"' . xl('Age') . '",';
    $exdata.=  '"' . xl('Gender') . '",';
    $exdata.=  '"' . xl('Address') . '",';
    $exdata.=  '"' . xl('Provider') . '",';
    $exdata.=  '"' . xl('Contact') . '"' . "\\n";


        // SQL scripts for the various searches
        $sqlBindArray = array();
        if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
            if ($_POST['form_csvexport']) {
                // CSV headers:
                echo '"' . xl('Date') . '",';
                echo '"' . xl('Employee') . '",';
                echo '"' . xl('ID') . '",';
                echo '"' . xl('Age') . '",';
                echo '"' . xl('Gender') . '",';
                echo '"' . xl('Address') . '",';
                echo '"' . xl('Provider') . '",';
                echo '"' . xl('Contact') . '"' . "\n";



            }
            else
            {

            $sqlstmt = "select
						pd.date as patient_date,pd.street as p_address,pd.phone_contact as p_contact,
						concat(pd.lname, ', ', pd.fname) AS patient_name,
						pd.pid AS patient_id,
						DATE_FORMAT(FROM_DAYS(DATEDIFF('".date('Y-m-d H:i:s')."',pd.dob)), '%Y')+0 AS patient_age,
						pd.sex AS patient_sex,
						pd.race AS patient_race,pd.ethnicity AS patient_ethinic,
						concat(u.lname, ', ', u.fname)  AS users_provider";

            $srch_option = $_POST['srch_option'];
            switch ($srch_option) {
                case "Medications":
                case "Allergies":
                case "Problems":
                    $sqlstmt=$sqlstmt.",li.date AS lists_date,
						   li.diagnosis AS lists_diagnosis,
								li.title AS lists_title";
                    break;
                case "Lab results":
                    $sqlstmt = $sqlstmt.",pr.date AS procedure_result_date,
							pr.facility AS procedure_result_facility,
							pr.units AS procedure_result_units,
							pr.result AS procedure_result_result,
							pr.range AS procedure_result_range,
							pr.abnormal AS procedure_result_abnormal,
							pr.comments AS procedure_result_comments,
							pr.document_id AS procedure_result_document_id";
                    break;
                case "Communication":
                    $sqlstmt = $sqlstmt.",REPLACE(REPLACE(concat_ws(',',IF(pd.hipaa_allowemail = 'YES', 'Allow Email','NO'),IF(pd.hipaa_allowsms = 'YES', 'Allow SMS','NO') , IF(pd.hipaa_mail = 'YES', 'Allow Mail Message','NO') , IF(pd.hipaa_voice = 'YES', 'Allow Voice Message','NO') ), ',NO',''), 'NO,','') as communications";
                    break;
            }

            //from
            $sqlstmt=$sqlstmt." from patient_data as pd left outer join users as u on u.id = pd.providerid";
            //JOINS
            switch ($srch_option) {
                case "Problems":
                    $sqlstmt = $sqlstmt." left outer join lists as li on (li.pid  = pd.pid AND li.type='medical_problem')";
                    break;
                case "Medications":
                    $sqlstmt = $sqlstmt." left outer join lists as li on (li.pid  = pd.pid AND (li.type='medication')) ";
                    break;
                case "Allergies":
                    $sqlstmt = $sqlstmt." left outer join lists as li on (li.pid  = pd.pid AND (li.type='allergy')) ";
                    break;
                case "Lab results":
                    $sqlstmt = $sqlstmt." left outer join procedure_order as po on po.patient_id = pd.pid
							left outer join procedure_order_code as pc on pc.procedure_order_id = po.procedure_order_id
							left outer join procedure_report as pp on pp.procedure_order_id = po.procedure_order_id
							left outer join procedure_type as pt on pt.procedure_code = pc.procedure_code and pt.lab_id = po.lab_id
							left outer join procedure_result as pr on pr.procedure_report_id = pp.procedure_report_id";
                    break;
            }

            //WHERE Conditions started
            $whr_stmt="where 1=1";
            //adding date search
          $whr_stmt.=" and pd.date>='$sql_date_from' AND pd.date<='$sql_date_to'";
            switch ($srch_option) {
                case "Medications":
                case "Allergies":
                    $whr_stmt=$whr_stmt." AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND li.date <= ?";
                    array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
                    break;
                case "Problems":
                    $whr_stmt = $whr_stmt." AND li.title != '' ";
                    $whr_stmt=$whr_stmt." AND li.date >= ? AND li.date < DATE_ADD(?, INTERVAL 1 DAY) AND li.date <= ?";
                    array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
                    break;
                case "Lab results":
                    $whr_stmt=$whr_stmt." AND pr.date >= ? AND pr.date < DATE_ADD(?, INTERVAL 1 DAY) AND pr.date <= ?";
                    $whr_stmt= $whr_stmt." AND (pr.result != '') ";
                    array_push($sqlBindArray, $sql_date_from, $sql_date_to, date("Y-m-d H:i:s"));
                    break;
                case "Communication":
                    $whr_stmt .= " AND (pd.hipaa_allowsms = 'YES' OR pd.hipaa_voice = 'YES' OR pd.hipaa_mail  = 'YES' OR pd.hipaa_allowemail  = 'YES') ";
                    break;
            }

            if (strlen($patient_id) != 0) {
                $whr_stmt = $whr_stmt."   and pd.pid = ?";
                array_push($sqlBindArray, $patient_id);
            }

            if (strlen($age_from) != 0) {
                $whr_stmt = $whr_stmt."   and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 >= ?";
                array_push($sqlBindArray, $age_from);
            }

            if (strlen($age_to) != 0) {
                $whr_stmt = $whr_stmt."   and DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),pd.dob)), '%Y')+0 <= ?";
                array_push($sqlBindArray, $age_to);
            }

            if (strlen($sql_gender) != 0) {
                $whr_stmt = $whr_stmt."   and pd.sex = ?";
                array_push($sqlBindArray, $sql_gender);
            }

            if ($srch_option == "Communication" && strlen($communication) > 0) {
                if ($communication == "allow_sms") {
                    $whr_stmt .= " AND pd.hipaa_allowsms = 'YES' ";
                } else if ($communication == "allow_voice") {
                    $whr_stmt .= " AND pd.hipaa_voice = 'YES' ";
                } else if ($communication == "allow_mail") {
                    $whr_stmt .= " AND pd.hipaa_mail  = 'YES' ";
                } else if ($communication == "allow_email") {
                    $whr_stmt .= " AND pd.hipaa_allowemail  = 'YES' ";
                }
            }

            //Sorting By filter fields
            $sortby = $_POST['sortby'];
            $sortorder = $_POST['sortorder'];

             // This is for sorting the records.
            switch ($srch_option) {
                case "Medications":
                case "Allergies":
                case "Problems":
                    $sort = array("lists_date","lists_diagnosis","lists_title");
                    if ($sortby == "") {
                        $sortby = $sort[1];
                    }
                    break;
                case "Lab results":
                    $sort = array("procedure_result_date","procedure_result_facility","procedure_result_units","procedure_result_result","procedure_result_range","procedure_result_abnormal");
                    //$odrstmt = " procedure_result_result";
                    break;
                case "Communication":
                    //$commsort = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(','))";
                    $sort = array("patient_date","patient_name","patient_id","patient_age","patient_sex","users_provider", "communications");
                    if ($sortby == "") {
                        $sortby = $sort[6];
                    }

                    //$odrstmt = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) , communications";
                    break;
                case "Demographics":
                    $sort = array("patient_date","patient_name","patient_id","patient_age","patient_sex","patient_race","patient_ethinic","users_provider");
                    break;
            }

            if ($sortby == "") {
                $sortby = $sort[0];
            }

            if ($sortorder == "") {
                $sortorder = "asc";
            }

            for ($i = 0; $i < count($sort); $i++) {
                $sortlink[$i] = "<a href=\"#\" onclick=\"sortingCols(" . attr_js($sort[$i]) . ",'asc');\" ><img src='" .  $GLOBALS['images_static_relative'] . "/sortdown.gif' border=0 alt=\"".xla('Sort Up')."\"></a>";
            }

            for ($i = 0; $i < count($sort); $i++) {
                if ($sortby == $sort[$i]) {
                    switch ($sortorder) {
                        case "asc":
                            $sortlink[$i] = "<a href=\"#\" onclick=\"sortingCols(" . attr_js($sortby) . ",'desc');\" ><img src='" .  $GLOBALS['images_static_relative'] . "/sortup.gif' border=0 alt=\"".xla('Sort Up')."\"></a>";
                            break;
                        case "desc":
                            $sortlink[$i] = "<a href=\"#\" onclick=\"sortingCols('" . attr_js($sortby) . "','asc');\" onclick=\"top.restoreSession()\"><img src='" . $GLOBALS['images_static_relative'] . "/sortdown.gif' border=0 alt=\"".xla('Sort Down')."\"></a>";
                            break;
                    } break;
                }
            }

            switch ($srch_option) {
                case "Medications":
                case "Allergies":
                case "Problems":
                    $odrstmt = " ORDER BY lists_date asc";
                    break;
                case "Lab results":
                    $odrstmt = " ORDER BY procedure_result_date asc";
                    break;
                case "Communication":
                    $odrstmt = "ORDER BY ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) asc, communications asc";
                    break;
                case "Demographics":
                    $odrstmt = " ORDER BY patient_date asc";
                    //$odrstmt = " ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) , communications";
                    break;
            }

            if (!empty($_POST['sortby']) && !empty($_POST['sortorder'])) {
                if ($_POST['sortby'] =="communications") {
                    $odrstmt = "ORDER BY ROUND((LENGTH(communications) - LENGTH(REPLACE(communications, ',', '')))/LENGTH(',')) ".escape_sort_order($_POST['sortorder']).", communications ".escape_sort_order($_POST['sortorder']);
                } else {
                    $odrstmt = "ORDER BY ".escape_identifier($_POST['sortby'], $sort, true)." ".escape_sort_order($_POST['sortorder']);
                }
            }

            $sqlstmt=$sqlstmt." ".$whr_stmt." ".$odrstmt;
            //echo $sqlstmt."<hr>";
            $result = sqlStatement($sqlstmt, $sqlBindArray);
            //print_r($result);
            $row_id = 1.1;//given to each row to identify and toggle
            $img_id = 1.2;
            $k=1.3;

            if (sqlNumRows($result) > 0) {
                //echo $sqlstmt;
                //pd.date>='2019-09-16 07:41:16' AND pd.date<='2019-09-16 12:45:50'
                $patArr = array();

                $patDataArr = array();
                $smoke_codes_arr = getSmokeCodes();
                while ($row = sqlFetchArray($result)) {
                        $patArr[] = $row['patient_id'];
                        $patInfoArr = array();
                        $patInfoArr['patient_id'] = $row['patient_id'];
                        //Diagnosis Check
                    if ($srch_option == "Medications" || $srch_option == "Allergies" || $srch_option == "Problems") {
                        $patInfoArr['lists_date'] = $row['lists_date'];
                        $patInfoArr['lists_diagnosis'] = $row['lists_diagnosis'];
                        $patInfoArr['lists_title'] = $row['lists_title'];
                        $patInfoArr['patient_name'] = $row['patient_name'];
                        $patInfoArr['patient_age'] = $row['patient_age'];
                        $patInfoArr['patient_sex'] = $row['patient_sex'];
                        $patInfoArr['patient_race'] = $row['patient_race'];
                        $patInfoArr['patient_ethinic'] = $row['patient_ethinic'];
                        $patInfoArr['users_provider'] = $row['users_provider'];
                        $patInfoArr['p_address'] = $row['p_address'];
                        $patInfoArr['p_contact'] = $row['p_contact'];
                    } elseif ($srch_option == "Lab results") {
                        $patInfoArr['procedure_result_date'] = $row['procedure_result_date'];
                        $patInfoArr['procedure_result_facility'] = $row['procedure_result_facility'];
                        $patInfoArr['procedure_result_units'] = $row['procedure_result_units'];
                        $patInfoArr['procedure_result_result'] = $row['procedure_result_result'];
                        $patInfoArr['procedure_result_range'] = $row['procedure_result_range'];
                        $patInfoArr['procedure_result_abnormal'] = $row['procedure_result_abnormal'];
                        $patInfoArr['procedure_result_comments'] = $row['procedure_result_comments'];
                        $patInfoArr['procedure_result_document_id'] = $row['procedure_result_document_id'];
                        $patInfoArr['p_address'] = $row['p_address'];
                        $patInfoArr['p_contact'] = $row['p_contact'];
                    } elseif ($srch_option == "Communication") {
                        $patInfoArr['patient_date'] = $row['patient_date'];
                        $patInfoArr['patient_name'] = $row['patient_name'];
                        $patInfoArr['patient_age'] = $row['patient_age'];
                        $patInfoArr['patient_sex'] = $row['patient_sex'];
                        $patInfoArr['users_provider'] = $row['users_provider'];
                        $patInfoArr['communications'] = $row['communications'];
                        $patInfoArr['p_address'] = $row['p_address'];
                        $patInfoArr['p_contact'] = $row['p_contact'];
                    } elseif ($srch_option == "Demographics") {
                        $patInfoArr['patient_date'] = $row['patient_date'];
                        $patInfoArr['patient_name'] = $row['patient_name'];
                        $patInfoArr['patient_age'] = $row['patient_age'];
                        $patInfoArr['patient_sex'] = $row['patient_sex'];
                        $patInfoArr['patient_race'] = $row['patient_race'];
                        $patInfoArr['patient_ethinic'] = $row['patient_ethinic'];
                        $patInfoArr['users_provider'] = $row['users_provider'];
                        $patInfoArr['p_address'] = $row['p_address'];
                        $patInfoArr['p_contact'] = $row['p_contact'];
                    }
                    $patFinalDataArr[] = $patInfoArr;


                }
                ?>

                <br>

                <input type="hidden" name="sortby" id="sortby" value="<?php echo attr($sortby); ?>" />
                <input type="hidden" name="sortorder" id="sortorder" value="<?php echo attr($sortorder); ?>" />
                <div id = "report_result">
                    <!-- <table>
                        <tr>
                            <td class="text"><strong><?php echo xlt('Total Number of Patients')?>:</strong>&nbsp;<span id="total_patients"><?php echo text(count(array_unique($patArr))); ?></span></td>
                        </tr>
                    </table> -->
                    <div class="table-div " id="print_content">

                    <table class="table table-form">

                    <?php
                    if ($srch_option == "Medications" || $srch_option == "Allergies" || $srch_option == "Problems") { ?>
                        <thead>
                        <tr class="bg-transparent">
                            <th><?php echo xlt('Diagnosis Date'); ?></th>
                            <th><?php echo xlt('Diagnosis'); ?></th>
                            <th><?php echo xlt('Diagnosis Name');?></th>
                            <th><?php echo xlt('Employee'); ?></th>
                            <th><?php echo xlt('ID');?></th>
                            <th><?php echo xlt('Age');?></th>
                            <th><?php echo xlt('Gender');?></th>
                            <th><?php echo xlt('Provider');?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($patFinalDataArr as $patKey => $patDetailVal) { ?>
                                <tr>
                                    <td ><?php echo text(oeFormatDateTime($patDetailVal['lists_date'], "global", true)); ?></td>
                                    <td ><?php echo text($patDetailVal['lists_diagnosis']); ?></td>
                                    <td ><?php echo text($patDetailVal['lists_title']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_name']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_id']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_age']);?></td>
                                    <td ><?php echo text($patDetailVal['patient_sex']);?></td>
                                    <td ><?php echo text($patDetailVal['users_provider']);?></td>
                                </tr>
                        <?php	}
                        ?>
                        </tbody>
                        <?php
                    }
                     elseif ($srch_option == "Lab results") { ?>
                        <thead>
                       <tr class="bg-transparent">
                            <th><?php echo xlt('Date'); ?></th>
                            <th><?php echo xlt('Facility');?></th>
                            <th><?php echo xlt('Unit');?></th>
                            <th><?php echo xlt('Result');?></th>
                            <th><?php echo xlt('Range');?></th>
                            <th><?php echo xlt('Abnormal');?></th>
                            <th><?php echo xlt('Comments');?></th>
                            <th><?php echo xlt('Document ID');?></th>
                            <th><?php echo xlt('ID');?></th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php
                        foreach ($patFinalDataArr as $patKey => $labResInsideArr) {?>
                                <tr>
                                                    <td> <?php echo text(oeFormatDateTime($labResInsideArr['procedure_result_date'], "global", true));?>&nbsp;</td>
                                                    <td> <?php echo text($labResInsideArr['procedure_result_facility'], ENT_NOQUOTES); ?>&nbsp;</td>
                                                    <td> <?php echo generate_display_field(array('data_type'=>'1','list_id'=>'proc_unit'), $labResInsideArr['procedure_result_units']); ?>&nbsp;</td>
                                                    <td> <?php echo text($labResInsideArr['procedure_result_result']); ?>&nbsp;</td>
                                                    <td> <?php echo text($labResInsideArr['procedure_result_range']); ?>&nbsp;</td>
                                                    <td> <?php echo text($labResInsideArr['procedure_result_abnormal']); ?>&nbsp;</td>
                                                    <td> <?php echo text($labResInsideArr['procedure_result_comments']); ?>&nbsp;</td>
                                                    <td> <?php echo text($labResInsideArr['procedure_result_document_id']); ?>&nbsp;</td>
                                                    <td> <?php echo text($labResInsideArr['patient_id']); ?>&nbsp;</td>
                               </tr>
                                        <?php
                        }
                        ?>
                        </tbody>
                        <?php
                    } elseif ($srch_option == "Communication") { ?>
                         <thead>
                        <tr class="bg-transparent">
                            <th>Date</th>
                            <th>Employee</th>
                            <th>ID</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Provider</th>
                            <th >Communication</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patFinalDataArr as $patKey => $patDetailVal) { ?>
                                <tr >
                                    <td ><?php echo ($patDetailVal['patient_date'] != '') ? text(oeFormatDateTime($patDetailVal['patient_date'], "global", true)) : ""; ?></td>
                                    <td ><?php echo text($patDetailVal['patient_name']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_id']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_age']);?></td>
                                    <td ><?php echo text($patDetailVal['patient_sex']);?></td>
                                    <td ><?php echo text($patDetailVal['users_provider']);?></td>
                                    <td ><?php echo text($patDetailVal['communications']);?></td>
                               </tr>
                        <?php }
                        ?>
                        </tbody>
                            <?php

                    } elseif ($srch_option == "Demographics") { ?>
                        <thead>
                       <tr  class="bg-transparent">
                            <th>Date</th>
                            <th>Employee</th>
                            <th>ID</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Address</th>
                            <th>Provider</th>
                            <th>Contact </th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($patFinalDataArr as $patKey => $patDetailVal) {
                                ?>
                                <tr>
                                    <td ><?php echo ($patDetailVal['patient_date'] != '') ? text(oeFormatDateTime($patDetailVal['patient_date'], "global", true)) : ""; ?></td>
                                    <td ><?php echo text($patDetailVal['patient_name']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_id']); ?></td>
                                    <td ><?php echo text($patDetailVal['patient_age']);?></td>
                                    <td ><?php echo text($patDetailVal['patient_sex']);?></td>
                                    <!-- <td ><?php echo generate_display_field(array('data_type'=>'36','list_id'=>'race'), $patDetailVal['patient_race']); ?></td> -->
                                    <td ><?php echo text($patDetailVal['p_address']);?></td>
                                    <td><?php echo text($patDetailVal['users_provider']);?></td>
                                    <td ><?php echo text($patDetailVal['p_contact']);?></td>
                                </tr>
                            <?php }
                            ?>
                            </tbody>
                            <?php
                    } ?>

                    </table>
                </div>
                     <!-- Main table ends -->
                <?php
            } else {//End if $result?>
                    <table>
                        <tr>
                            <td>&nbsp;&nbsp;<?php echo xlt('No records found.')?></td>
                        </tr>
                    </table>
                <?php
            }
            ?>
                <!-- </div> -->

            <?php
            }
        } else {//End if form_refresh
            ?>
            <!-- <div class='text'> <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?> </div> -->
            <?php
        }
        ?>
        </form>
</div>
</div>
</window-dashboard>
</div>
</div>
<script>

  function printcsv()
  {
    var passedArray =<?php echo json_encode($patFinalDataArr); ?>;
     var myKeyVals = { sessionval : passedArray  }
     $.ajax({
          type: 'POST',
          url: "set_print_data.php",
          data: myKeyVals,
          dataType: "",
          success: function(result) {
            //alert(result);
            console.log(result);
             window.open("csv.php", '_blank');
          //  e.preventDefault();

            }
     });

}
</script>
</section>
    </body>
</html>
