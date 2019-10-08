<?php
/**
 * Pending orders.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2013 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

function thisLineItem($row)
{
    $provname = $row['provider_lname'];
    if (!empty($row['provider_fname'])) {
        $provname .= ', ' . $row['provider_fname'];
        if (!empty($row['provider_mname'])) {
            $provname .= ' ' . $row['provider_mname'];
        }
    }

    if ($_POST['form_csvexport']) {
        echo '"' . addslashes($row['patient_name'  ]) . '",';
        echo '"' . addslashes($row['pubpid'        ]) . '",';
        echo '"' . addslashes(oeFormatShortDate($row['date_ordered'  ])) . '",';
        echo '"' . addslashes($row['organization'  ]) . '",';
        echo '"' . addslashes($provname) . '",';
        echo '"' . addslashes($row['priority_name' ]) . '",';
        echo '"' . addslashes($row['status_name'   ]) . '"' . "\n";
    } else {
        ?>
   <tr>
    <td class="detail"><?php echo text($row['patient_name'  ]); ?></td>
    <td class="detail"><?php echo text($row['pubpid'        ]); ?></td>
    <td class="detail"><?php echo text(oeFormatShortDate($row['date_ordered'  ])); ?></td>
    <td class="detail"><?php echo text($row['organization'  ]); ?></td>
    <td class="detail"><?php echo text($provname); ?></td>
    <td class="detail"><?php echo text($row['priority_name' ]); ?></td>
    <td class="detail"><?php echo text($row['status_name'   ]); ?></td>
 </tr>
        <?php
    } // End not csv export
}

if (! acl_check('acct', 'rep')) {
    die(xlt("Unauthorized access."));
}

$form_from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility  = $_POST['form_facility'];

if ($_POST['form_csvexport']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=pending_orders.csv");
    header("Content-Description: File Transfer");
  // CSV headers:
    echo '"' . xl('Patient') . '",';
    echo '"' . xl('ID') . '",';
    echo '"' . xl('Ordered') . '",';
    echo '"' . xl('From') . '",';
    echo '"' . xl('Procedure') . '",';
    echo '"' . xl('Provider') . '",';
    echo '"' . xl('Priority') . '",';
    echo '"' . xl('Status') . '"' . "\n";
} else { // not export
    ?>
<html>
<head>
    <title><?php echo xlt('Pending Orders') ?></title>
    
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
            @media screen {
                #report_parameters_daterange {
                    visibility: hidden;
                    display: none;
                }
            }
   
         </style>
    <script language="JavaScript">

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
        
    </script>
</head>

<body style="font-family: 'Open Sans', sans-serif;">

<!-- <h2><?php echo xlt('Pending Orders')?></h2> -->
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
                                            <p class="text-white head-p">Pending Orders</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="body-compo" style="height:auto;">
                            <div class="container-fluid">
<form method='post' action='pending_orders.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<!-- <table>

 <tr>
  <td>
    <?php dropdown_facility($form_facility, 'form_facility', false); ?>
  </td>
  <td >
   &nbsp;<?php echo xlt('From')?>:
  </td>
  <td>
   <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
  </td>
  <td >
   &nbsp;<?php echo xlt('To')?>:
  </td>
  <td>
   <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
  </td>
 </tr>
 <tr>
  <td>
   <div class="btn-group" role="group">
    <button type='submit' class='btn btn-default btn-refresh' name='form_refresh'><?php echo xlt('Refresh'); ?></button>
    <button type='submit' class='btn btn-default btn-transmit' name='form_csvexport'><?php echo xlt('Export to CSV'); ?></button>
    <button type='button' class='btn btn-default btn-print' id='printbutton'><?php echo xlt('Print'); ?></button>
   </div>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table> -->
<div class="pt-4 pb-4">
                                <div id="report_parameter" class="row">
                                    <div class="col-md-1"></div>
                                    <div class="col-md-3">
                                        <p>Facility</p>
                                        <?php dropdown_facility($form_facility, 'form_facility', false); ?>
                                    </div>
                                    <div class="col-md-2">
                                        <p>From</p>
                                        <!-- <input type="date" placeholder="" class="form-control pr-1 pl-1"> -->
                                        <input type='date' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
                                    </div>
                                    <div class="col-md-2">
                                        <p>To</p> 
                                        <!-- <input type="date" placeholder="" class="form-control pr-1 pl-1"> -->
                                        <input type='date' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>

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

                                <div id="report_parameter" class="pt-4 pb-5">
                                    <div class="row">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-2"> <button type="submit" name='form_refresh' class="form-save">SEARCH</button></div>
                                        <div class="col-md-2"> <button type="submit" id='printbutton' class="form-save">PRINT</button></div>
                                        <div class="col-md-2"> <button type="submit" name='form_csvexport' class="form-save">Export to CSV</button></div>

                                    </div>

                                </div>
<div id = "report_result" class="table-div ">
 <table class="table table-form">
 <tr >
  <th><?php echo xlt('Employee'); ?></th>
  <th ><?php echo xlt('ID'); ?></th>
  <th ><?php echo xlt('Ordered'); ?></th>
  <th ><?php echo xlt('From'); ?></th>
  <th ><?php echo xlt('Provider'); ?></th>
  <th ><?php echo xlt('Priority'); ?></th>
  <th ><?php echo xlt('Status'); ?></th>
 </tr>
    <?php
} 
    if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $sqlBindArray = array();
    $query = "SELECT po.patient_id, po.date_ordered, " .
    "pd.pubpid, " .
    "CONCAT(pd.lname, ', ', pd.fname, ' ', pd.mname) AS patient_name, " .
    "u1.lname AS provider_lname, u1.fname AS provider_fname, u1.mname AS provider_mname, " .
    "pp.name AS organization, " .
    "lop.title AS priority_name, " .
    "los.title AS status_name, " .
    "pr.procedure_report_id, pr.date_report, pr.report_status " .
    "FROM procedure_order AS po " .
    "JOIN form_encounter AS fe ON fe.pid = po.patient_id AND fe.encounter = po.encounter_id " .
    "JOIN patient_data AS pd ON pd.pid = po.patient_id " .
    "LEFT JOIN users AS u1 ON u1.id = po.provider_id " .
    "LEFT JOIN procedure_providers AS pp ON pp.ppid = po.lab_id " .
    "LEFT JOIN list_options AS lop ON lop.list_id = 'ord_priority' AND lop.option_id = po.order_priority AND lop.activity = 1 " .
    "LEFT JOIN list_options AS los ON los.list_id = 'ord_status' AND los.option_id = po.order_status AND los.activity = 1 " .
    "LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id " .
    "WHERE " .
    "po.date_ordered >= ? AND po.date_ordered <= ? AND " .
    "( pr.report_status IS NULL OR pr.report_status = 'prelim' )";
    array_push($sqlBindArray, $form_from_date, $form_to_date);

  // TBD: What if preliminary and final reports for the same order?

    if ($form_facility) {
        $query .= " AND fe.facility_id = ?";
        array_push($sqlBindArray, $form_facility);
    }

    $query .= " ORDER BY pd.lname, pd.fname, pd.mname, po.patient_id, " .
    "po.date_ordered, po.procedure_order_id";

    $res = sqlStatement($query, $sqlBindArray);
    while ($row = sqlFetchArray($res)) {
        thisLineItem($row);
    }
} // end report generation

if (! $_POST['form_csvexport']) {
    ?>

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
} // End not csv export
?>
