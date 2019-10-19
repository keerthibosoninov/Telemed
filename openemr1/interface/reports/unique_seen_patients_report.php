<?php
/**
 * This report lists patients that were seen within a given date
 * range.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date = (!empty($_POST['form_from_date'])) ?  DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$form_to_date   = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-12-31');

if ($_POST['form_labels']) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=unique_seen_emp.csv");
    header("Content-Description: File Transfer");

    echo '"' . xl('Visit Date') . '",';
    echo '"' . xl('Employee') . '",';
    echo '"' . xl('Visits') . '",';
    echo '"' . xl('Age') . '",';
    echo '"' . xl('Gender') . '",';
    echo '"' . xl('Race') . '",';
    echo '"' . xl('Primary Insurance') . '",';
    echo '"' . xl('Secondary Insurance') . '"' . "\n";
} else {
    ?>
<html>
<head>
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
   #report_result {
      margin-top: 30px;
   }
}

/* specifically exclude some from the screen */
@media screen {
   #report_parameters_daterange {
       visibility: hidden;
       display: none;
   }
}

        .custom-btn{
            margin-top: 1rem;
        }
        
        thead{
            padding: .75rem;
            border-top: 1px solid #dee2e6;
        }
        .form-save{
            padding-top: 6px;
            padding-bottom: 6px;
        }

        body{
            font-size: 16px;
        }

        .table-div{
            height:auto;
            overflow:auto;
        }

</style>

<title><?php echo xlt('Front Office Receipts'); ?></title>
    
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
   
    <?php Header::setupHeader('datetime-picker'); ?>
    
<script language="JavaScript">

$(function() {
    // var win = top.printLogSetup ? top : opener.top;
    // win.printLogSetup(document.getElementById('printbutton'));

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
     });
});
function printForm(e){
                 var win = top.printLogPrint ? top : opener.top;
                 win.printLogPrint(window);
                 e.preventDefault();
            }

</script>

<style type="text/css">
.table-div {
    height: auto;
    overflow: auto;
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
}

/* specifically exclude some from the screen */
@media screen {
   #report_parameters_daterange {
       visibility: hidden;
       display: none;
   }
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
    .form-save{
        padding: 5px;
    font-family: 'Open Sans', sans-serif;
    font-size: 16px;
    }

</style>
</head>

<body class="body_top" style="font-family: 'Open Sans', sans-serif;">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<!-- <span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Unique Seen Patients'); ?></span> -->

<div id="report_parameters_daterange">
    <?php echo text(oeFormatShortDate($form_from_date)) ." &nbsp; " . xlt("to") . " &nbsp; ". text(oeFormatShortDate($form_to_date)); ?>
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
                                            <p class="text-white head-p">Unique Seen Employees</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="body-compo" style="height:auto;">
                            <div class="container-fluid">
<form name='theform' method='post' action='unique_seen_patients_report.php' id='theform' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<!-- <div id="report_parameters"> -->
<!-- <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_labels' id='form_labels' value=''/> -->

<!-- <table>
<tr>
 <td width='410px'>
   <div style='float:left'>

   <table class='text'>
       <tr>
           <td class='control-label'>
                <?php echo xlt('Visits From'); ?>:
           </td>
           <td>
             <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
           </td>
           <td class='control-label'>
                <?php echo xlt('To'); ?>:
           </td>
           <td>
             <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
           </td>
       </tr>
   </table>

   </div>

 </td>
 <td align='left' valign='middle' height="100%">
   <table style='border-left:1px solid; width:100%; height:100%' >
       <tr>
           <td>
               <div class="text-center">
         <div class="btn-group" role="group">
                     <a href='#' class='btn btn-default btn-save' onclick='$("#form_refresh").attr("value","true"); $("#form_labels").val(""); $("#theform").submit();'>
                        <?php echo xlt('Submit'); ?>
                     </a>
                    <?php if ($_POST['form_refresh']) { ?>
                        <a href='#' class='btn btn-default btn-print' id='printbutton'>
                                <?php echo xlt('Print'); ?>
                        </a>
                        <a href='#' class='btn btn-default btn-transmit' onclick='$("#form_labels").attr("value","true"); $("#theform").submit();'>
                            <?php echo xlt('Labels'); ?>
                        </a>
                    <?php } ?>
         </div>
               </div>
           </td>
       </tr>
   </table>
 </td>
</tr>
</table> -->
<div class="pt-4 pb-4" >
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_labels' id='form_labels' value=''/>
                                <div class="row" id="report_parameter">
                                    <div class="col-md-4"></div>

                                    <div class="col-md-2">

                                        <p class="">Visits</p>
                                    </div>
                                </div>
                                <div class="row" id="report_parameter">
                                    <div class="col-md-4"></div>

                                    <div class="col-md-2">
                                        <p>From</p>
                                        <!-- <input type="date" placeholder="" class="form-control pr-1 pl-1"> -->
                                        <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
                                    </div>
                                    <div class="col-md-2">
                                        <p>To</p> 
                                        <!-- <input type="date" placeholder="" class="form-control pr-1 pl-1"> -->
                                        <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>

                                    </div>




                                </div>

                                <div id="report_parameter" class="pt-4 pb-5 custom-btn">
                                    <div class="row">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-2"> <button onclick='$("#form_refresh").attr("value","true"); $("#form_labels").val(""); $("#theform").submit();' class="form-save">SEARCH</button></div>
                                        <div class="col-md-2"> <button class="form-save" onclick="printForm(event)" id='printbutton'>PRINT</button></div>
                                        <div class="col-md-2"> <button class="form-save" onclick='$("#form_labels").attr("value","true"); $("#theform").submit();'>Export to CSV</button></div>

                                    </div>

                                </div>

<!-- end of parameters -->

<!-- <div id="report_results"> -->
<div id="report_result" class="table-div ">
<table class="table table-form">

<thead>
<th> <?php echo xlt('Visit Date'); ?> </th>
<th> <?php echo xlt('Employee'); ?> </th>
<th > <?php echo xlt('Visits'); ?> </th>
<th > <?php echo xlt('Age'); ?> </th>
<th> <?php echo xlt('Gender'); ?> </th>
<th> <?php echo xlt('Race'); ?> </th>
<th> <?php echo xlt('Primary Insurance'); ?> </th>
<th> <?php echo xlt('Secondary Insurance'); ?> </th>
</thead>
<tbody>
    <?php
} // end not generating labels

if ($_POST['form_refresh'] || $_POST['form_labels']) {
    $totalpts = 0;

    $query = "SELECT " .
    "p.pid, p.fname, p.mname, p.lname, p.DOB, p.sex, p.ethnoracial, " .
    "p.street, p.city, p.state, p.postal_code, " .
    "count(e.date) AS ecount, max(e.date) AS edate, " .
    "i1.date AS idate1, i2.date AS idate2, " .
    "c1.name AS cname1, c2.name AS cname2 " .
    "FROM patient_data AS p " .
    "JOIN form_encounter AS e ON " .
    "e.pid = p.pid AND " .
    "e.date >= ? AND " .
    "e.date <= ? " .
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
    $res = sqlStatement($query, array($form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59'));

    $prevpid = 0;
    while ($row = sqlFetchArray($res)) {
        if ($row['pid'] == $prevpid) {
            continue;
        }

        $prevpid = $row['pid'];

        $age = '';
        if ($row['DOB']) {
            $dob = $row['DOB'];
            $tdy = $row['edate'];
            $ageInMonths = (substr($tdy, 0, 4)*12) + substr($tdy, 5, 2) -
                   (substr($dob, 0, 4)*12) - substr($dob, 5, 2);
            $dayDiff = substr($tdy, 8, 2) - substr($dob, 8, 2);
            if ($dayDiff < 0) {
                --$ageInMonths;
            }

            $age = intval($ageInMonths/12);
        }

        if ($_POST['form_labels']) {
            // echo '"' . $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] . '","' .
            //  $row['street'] . '","' . $row['city'] . '","' . $row['state'] . '","' .
            //  $row['postal_code'] . '"' . "\n";
            echo '"' . addslashes(oeFormatShortDate(substr($row['edate'], 0, 10))) . '",';
        echo '"' . addslashes(($row['lname']) . ', ' . text($row['fname']) . ' ' . text($row['mname'])) . '",';
        echo '"' . addslashes($row['ecount'  ]) . '",';
        echo '"' . addslashes($age) . '",';
        echo '"' . addslashes($row['sex']) . '",';
        echo '"' . addslashes($row['ethnoracial' ]) . '",';
        echo '"' . addslashes($row['cname1' ]) . '",';
        echo '"' . addslashes($row['cname2'   ]) . '"' . "\n";
        } else { // not labels
            ?>
       <tr>
        <td>
            <?php echo text(oeFormatShortDate(substr($row['edate'], 0, 10))); ?>
   </td>
   <td>
            <?php echo text($row['lname']) . ', ' . text($row['fname']) . ' ' . text($row['mname']); ?>
   </td>
   <td >
            <?php echo text($row['ecount']); ?>
   </td>
   <td>
            <?php echo text($age); ?>
   </td>
   <td>
            <?php echo text($row['sex']); ?>
   </td>
   <td>
            <?php echo text($row['ethnoracial']); ?>
   </td>
   <td>
            <?php echo text($row['cname1']); ?>
   </td>
   <td>
            <?php echo text($row['cname2']); ?>
   </td>
  </tr>
            <?php
        } // end not labels
        ++$totalpts;
    }

    if (!$_POST['form_labels']) {
        ?>
   <!-- <tr class='report_totals'>
    <td colspan='2'>
        <?php echo xlt('Total Number of Patients'); ?>
  </td>
  <td style="padding-left: 20px;">
        <?php echo text($totalpts); ?>
  </td>
  <td colspan='5'>&nbsp;</td>
 </tr> -->

        <?php
    } // end not labels
} // end refresh or labels

if (!$_POST['form_labels']) {
    ?>
</tbody>
</table>
</div>
</div> 
</form>
</div>
</div>

</window-dashboard>
</div>
</div>
</section>

</body>

</html>
    <?php
} // end not labels
?>
