<?php
/**
 * This is a report of sales by item description.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2015-2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_provider  = $_POST['form_provider'];
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
    $form_details  = $_POST['form_details']      ? true : false;
} else {
    $form_details = false;
}

function bucks($amount)
{
    if ($amount) {
        return oeFormatMoney($amount);
    }
}

function display_desc($desc)
{
    if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
        $desc = $matches[1];
    }

    return $desc;
}

function thisLineItem($patient_id, $encounter_id, $rowcat, $description, $transdate, $qty, $amount, $irnumber = '')
{
    global $product, $category, $producttotal, $productqty, $cattotal, $catqty, $grandtotal, $grandqty;
    global $productleft, $catleft;

    $invnumber = $irnumber ? $irnumber : "$patient_id.$encounter_id";
    $rowamount = sprintf('%01.2f', $amount);

    $patdata = sqlQuery("SELECT " .
    "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
    "p.street, p.city, p.state, p.postal_code, " .
    "p.ss, p.sex, p.status, p.phone_home, " .
    "p.phone_biz, p.phone_cell, p.hipaa_notice " .
    "FROM patient_data AS p " .
    "WHERE p.pid = ? LIMIT 1", array($patient_id));

    $pat_name = $patdata['fname'] . ' ' . $patdata['mname'] . ' ' . $patdata['lname'];

    if (empty($rowcat)) {
        $rowcat = xl('None');
    }

    $rowproduct = $description;
    if (! $rowproduct) {
        $rowproduct = xl('Unknown');
    }

    if ($product != $rowproduct || $category != $rowcat) {
        if ($product) {
            // Print product total.
            if ($_POST['form_csvexport']) {
                if (! $_POST['form_details']) {
                    echo '"' . display_desc($category) . '",';
                    echo '"' . display_desc($product)  . '",';
                    echo '"' . $productqty             . '",';
                    echo '"';
                    echo bucks($producttotal);
                    echo '"' . "\n";
                }
            } else {
                ?>
       <tr bgcolor="#ddddff">
        <td class="detail">
                <?php echo text(display_desc($catleft));
                $catleft = " "; ?>
  </td>
  <td class="detail" colspan="3">
                <?php
                if ($_POST['form_details']) {
                    echo xlt('Total for') . ' ';
                }

                echo text(display_desc($product)); ?>
  </td>
                <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
  &nbsp;
  </td>
    <?php } ?>
  <td align="right">
   &nbsp;
  </td>
  <td align="right">
                <?php echo text($productqty); ?>
  </td>
  <td align="right">
                <?php echo text(bucks($producttotal)); ?>
  </td>
 </tr>
                <?php
            } // End not csv export
        }

        $producttotal = 0;
        $productqty = 0;
        $product = $rowproduct;
        $productleft = $product;
    }

    if ($category != $rowcat) {
        if ($category) {
            // Print category total.
            if (!$_POST['form_csvexport']) {
                ?>

       <tr bgcolor="#ffdddd">
        <td class="detail">
         &nbsp;
        </td>
        <td class="detail" colspan="3">
                <?php echo xlt('Total for category') . ' ';
                echo text(display_desc($category)); ?>
  </td>
                <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
   &nbsp;
  </td>
    <?php } ?>
  <td align="right">
   &nbsp;
  </td>
  <td align="right">
                <?php echo text($catqty); ?>
  </td>
  <td align="right">
                <?php echo text(bucks($cattotal)); ?>
  </td>
 </tr>
                <?php
            } // End not csv export
        }

        $cattotal = 0;
        $catqty = 0;
        $category = $rowcat;
        $catleft = $category;
    }

    if ($_POST['form_details']) {
        if ($_POST['form_csvexport']) {
            echo '"' . display_desc($category) . '",';
            echo '"' . display_desc($product) . '",';
            echo '"' . oeFormatShortDate(display_desc($transdate)) . '",';
            if ($GLOBALS['sales_report_invoice'] == 1 || $GLOBALS['sales_report_invoice'] == 2) {
                echo '"' . $pat_name . '",';
            }

            if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {
                echo '"' . display_desc($invnumber) . '",';
            }

            if ($GLOBALS['sales_report_invoice'] == 1) {
                echo '"' . $patient_id . '",';
            }

           // echo '"' . display_desc($invnumber) . '",';
            echo '"' . display_desc($qty) . '",';
            echo '"';
            echo bucks($rowamount);
            echo '"' . "\n";
        } else {
            ?>

     <tr>
      <td class="detail">
            <?php echo text(display_desc($catleft));
            $catleft = " "; ?>
  </td>
  <td class="detail">
            <?php echo text(display_desc($productleft));
            $productleft = " "; ?>
  </td>
  <td>
            <?php echo text(oeFormatShortDate($transdate)); ?>
  </td>
            <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
   &nbsp;
  </td>
        <?php } ?>
            <?php if ($GLOBALS['sales_report_invoice'] == 1 || $GLOBALS['sales_report_invoice'] == 2) { ?>
  <td>
                <?php echo text($pat_name); ?>
  </td>
        <?php } ?>
  <td class="detail">
            <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) { ?>
   <a href='../patient_file/pos_checkout.php?ptid=<?php echo attr_url($patient_id); ?>&enc=<?php echo attr_url($encounter_id); ?>'>
                <?php echo text($invnumber); ?></a>
    <?php }

            if ($GLOBALS['sales_report_invoice'] == 1) {
                echo text($patient_id);
            }
            ?>
      </td>
            <?php if ($GLOBALS['sales_report_invoice'] == 0) {?>
  <td>
   &nbsp;
  </td>
        <?php } ?>
      <td align="right">
            <?php echo text($qty); ?>
      </td>
      <td align="right">
            <?php echo text(bucks($rowamount)); ?>
      </td>
     </tr>
            <?php
        } // End not csv export
    } // end details
    $producttotal += $rowamount;
    $cattotal     += $rowamount;
    $grandtotal   += $rowamount;
    $productqty   += $qty;
    $catqty       += $qty;
    $grandqty     += $qty;
} // end function

if (! acl_check('acct', 'rep')) {
    die(xlt("Unauthorized access."));
}

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility  = $_POST['form_facility'];

if ($_POST['form_csvexport']) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=sales_by_item.csv");
    header("Content-Description: File Transfer");
    // CSV headers:
    if ($_POST['form_details']) {
        echo '"Category",';
        echo '"Item",';
        echo '"Date",';
        if ($GLOBALS['sales_report_invoice'] == 1 || $GLOBALS['sales_report_invoice'] == 2) {
            echo '"Name",';
        }

        if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {
            echo '"Invoice",';
        }

        if ($GLOBALS['sales_report_invoice'] == 1) {
            echo '"ID",';
        }

        echo '"Qty",';
        echo '"Amount"' . "\n";
    } else {
        echo '"Category",';
        echo '"Item",';
        echo '"Qty",';
        echo '"Total"' . "\n";
    }
} else { // end export
    ?>
<html>
<head>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/employee_dashboard_style.css">

    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/vue.js"></script>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/main.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/addmore.js"></script>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/style.css">

    <title><?php echo xlt('Sales by Item'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <style type="text/css">
        /* specifically include & exclude from printing */
        @media print {
            #report_parameters {
                visibility: hidden;
               display: none;
            }
            #report_parameters_daterange {
                visibility: visible;
                display: inline;
            }
            #report_results {
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

        table.mymaintable, table.mymaintable td {
            border: 1px solid #aaaaaa;
            border-collapse: collapse;
        }
        table.mymaintable td {
            padding: 1pt 4pt 1pt 4pt;
        }

        .css_button:hover, button:hover, input[type=button]:hover, input[type=submit]:hover {
            background: #3C9DC5;
            text-decoration: none;
        }

        #report_parameters {
            background-color: transparent !important;
            margin-top: 10px;
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

    <script language="JavaScript">
        $(function() {
            oeFixedHeaderSetup(document.getElementById('mymaintable'));
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

<title><?php echo xlt('Sales by Item') ?></title>

<body  class="body_top">
        <section>
            <div class="body-content body-content2">
                <div class="container-fluid pb-4 pt-4">
                    <window-dashboard title="" class="icon-hide">
                        <div class="head-component">
                            <div class="row">
                                <div class="col-6"></div>
                                <div class="col-6">
                                    <p class="text-white head-p">Sales by Item </p>
                                </div>
                            </div>
                        </div>
                        <div class="body-compo">
                            <div id="report_parameters_daterange">
                                <span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Sales by Item'); ?></span>
                                <?php echo text(oeFormatShortDate($form_from_date)) ." &nbsp; " . xlt('to') . " &nbsp; ". text(oeFormatShortDate($form_to_date)); ?>
                            </div>
                            <form method='post' action='sales_by_item.php' id='theform' onsubmit='return top.restoreSession()'>
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
                                <input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
                                <div class="container-fluid">

                                    <div class="pt-4 pb-4">
                                        <div id="report_parameters">
                                            <div class="row">
                                                <div class="col-md-1"></div>
                                                <div class="col-md-3">
                                                    <p>Facility</p>
                                                    <?php dropdown_facility($form_facility, 'form_facility', true); ?>
                                                </div>
                                                <div class="col-md-2">
                                                    <p>From</p>
                                                    <input type='date' class='datepicker form-control pr-1 pl-1' name='form_from_date' id="form_from_date"  value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>

                                                    <!-- <input type="date" placeholder="" class="form-control pr-1 pl-1"> -->
                                                </div>
                                                <div class="col-md-2">
                                                    <p>To</p>
                                                    <input type='date' class='datepicker form-control  pr-1 pl-1' name='form_to_date' id="form_to_date"  value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>

                                                     <!-- <input type="date" placeholder="" class="form-control pr-1 pl-1"> -->
                                                    </div>
                                                <div class="col-md-3">
                                                    <p>Provider</p>
                                                    <?php
                                                    if (acl_check('acct', 'rep_a')) {
                                                    // Build a drop-down list of providers.
                                                        $query = "select id, lname, fname from users where " .
                                                        "authorized = 1 order by lname, fname";
                                                        $res = sqlStatement($query);
                                                        echo "<select name='form_provider' class='form-control mt-2'>\n";
                                                        echo "<option value=''>-- " . xlt('All Providers') . " --\n";
                                                        while ($row = sqlFetchArray($res)) {
                                                            $provid = $row['id'];
                                                            echo "    <option value='". attr($provid) ."'";
                                                            if ($provid == $_REQUEST['form_provider']) {
                                                                echo " selected";
                                                            }

                                                            echo ">" . text($row['lname']) . ", " . text($row['fname']) . "\n";
                                                        }

                                                        echo "   </select>\n";
                                                    } else {
                                                        echo "<input type='hidden' name='form_provider' value='" . attr($_SESSION['authUserID']) . "'>";
                                                    }
                                                    ?>
                                                   
                                                </div>



                                            </div>

                                            <div class="pt-4 pb-5 custom-btn">
                                                <div class="row">
                                                    <div class="col-md-4"></div>
                                                    <div class="col-md-2"> <button class="form-save" onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value",""); $("#theform").submit();'>SEARCH</button></div>
                                                    <div class="col-md-2"> <button class="form-save" id='printbutton'>PRINT</button></div>
                                                    <!-- <div class="col-md-2"> <button class="form-save" onclick='$("#form_refresh").attr("value",""); $("#form_csvexport").attr("value","true"); $("#theform").submit();'>CSV Export</button></div> -->
                                                    <div class="col-md-4"></div>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="table-div ">
                                            <table class="table table-form">
                                                <thead>

                                                    <tr>
                                                        <th>Category</th>
                                                        <th>Item</th>
                                                        <th>Quantity</th>
                                                        <th>Amount</th>

                                                    </tr>

                                                </thead>
                                                <?php
                                                }
                                                ?>
                                               
                                                <?php
                                                
                                                if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
                                                    $from_date = $form_from_date . ' 00:00:00';
                                                    $to_date = $form_to_date . ' 23:59:59';
                                                    $category = "";
                                                    $catleft = "";
                                                    $cattotal = 0;
                                                    $catqty = 0;
                                                    $product = "";
                                                    $productleft = "";
                                                    $producttotal = 0;
                                                    $productqty = 0;
                                                    $grandtotal = 0;
                                                    $grandqty = 0;

                                                    $sqlBindArray = array();
                                                    $query = "SELECT b.fee, b.pid, b.encounter, b.code_type, b.code, b.units, " .
                                                    "b.code_text, fe.date, fe.facility_id, fe.provider_id, fe.invoice_refno, lo.title " .
                                                    "FROM billing AS b " .
                                                    "JOIN code_types AS ct ON ct.ct_key = b.code_type " .
                                                    "JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter " .
                                                    "LEFT JOIN codes AS c ON c.code_type = ct.ct_id AND c.code = b.code AND c.modifier = b.modifier " .
                                                    "LEFT JOIN list_options AS lo ON lo.list_id = 'superbill' AND lo.option_id = c.superbill AND lo.activity = 1 " .
                                                    "WHERE b.code_type != 'COPAY' AND b.activity = 1 AND b.fee != 0 AND " .
                                                    "fe.date >= ? AND fe.date <= ?";
                                                    array_push($sqlBindArray, $from_date, $to_date);
                                                    // If a facility was specified.
                                                    if ($form_facility) {
                                                        $query .= " AND fe.facility_id = ?";
                                                        array_push($sqlBindArray, $form_facility);
                                                    }

                                                    if ($form_provider) {
                                                        $query .= " AND fe.provider_id = ?";
                                                        array_push($sqlBindArray, $form_provider);
                                                    }

                                                    $query .= " ORDER BY lo.title, b.code, fe.date, fe.id";
                                                    //
                                                    $res = sqlStatement($query, $sqlBindArray);
                                                    while ($row = sqlFetchArray($res)) {
                                                        thisLineItem(
                                                            $row['pid'],
                                                            $row['encounter'],
                                                            $row['title'],
                                                            $row['code'] . ' ' . $row['code_text'],
                                                            substr($row['date'], 0, 10),
                                                            $row['units'],
                                                            $row['fee'],
                                                            $row['invoice_refno']
                                                        );
                                                    }

                                                    //
                                                    $sqlBindArray = array();
                                                    $query = "SELECT s.sale_date, s.fee, s.quantity, s.pid, s.encounter, " .
                                                    "d.name, fe.date, fe.facility_id, fe.provider_id, fe.invoice_refno " .
                                                    "FROM drug_sales AS s " .
                                                    "JOIN drugs AS d ON d.drug_id = s.drug_id " .
                                                    "JOIN form_encounter AS fe ON " .
                                                    "fe.pid = s.pid AND fe.encounter = s.encounter AND " .
                                                    "fe.date >= ? AND fe.date <= ? " .
                                                    "WHERE s.fee != 0";
                                                    array_push($sqlBindArray, $from_date, $to_date);
                                                    // If a facility was specified.
                                                    if ($form_facility) {
                                                        $query .= " AND fe.facility_id = ?";
                                                        array_push($sqlBindArray, $form_facility);
                                                    }

                                                    if ($form_provider) {
                                                        $query .= " AND fe.provider_id = ?";
                                                        array_push($sqlBindArray, $form_provider);
                                                    }

                                                    $query .= " ORDER BY d.name, fe.date, fe.id";
                                                    //
                                                    $res = sqlStatement($query, $sqlBindArray);
                                                    while ($row = sqlFetchArray($res)) {
                                                        thisLineItem(
                                                            $row['pid'],
                                                            $row['encounter'],
                                                            xl('Products'),
                                                            $row['name'],
                                                            substr($row['date'], 0, 10),
                                                            $row['quantity'],
                                                            $row['fee'],
                                                            $row['invoice_refno']
                                                        );
                                                    }

                                                    if ($_POST['form_csvexport']) {
                                                        if (! $_POST['form_details']) {
                                                            echo '"' . display_desc($product) . '",';
                                                            echo '"' . $productqty            . '",';
                                                            echo '"';
                                                            echo bucks($producttotal);
                                                            echo '"' . "\n";
                                                        }
                                                    } else {
                                                    ?>

                                                    <!-- <tr>
                                                        <td><?php echo xlt('Category'); ?></td>
                                                        <td>Item 1</td>
                                                        <td>3</td>
                                                        <td>578</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Category</td>
                                                        <td>Item 2</td>
                                                        <td>4</td>
                                                        <td>5748</td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td colspan="2">Total for Category</td>

                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td colspan="2">
                                                             Total</td>

                                                        <td>57483</td>
                                                    </tr> -->
                                                    <tbody>                                      
                                                        <tr >
                                                            <td >
                                                                    <?php echo text(display_desc($catleft));
                                                                    $catleft = " "; ?>
                                                            </td>
                                                            <td colspan="3">
                                                                    <?php
                                                                    if ($_POST['form_details']) {
                                                                        echo xlt('Total for') . ' ';
                                                                    }

                                                                    echo text(display_desc($product)); ?>
                                                            </td>
                                                            <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
                                                            <td>&nbsp;</td>
                                                            <?php } ?>
                                                            <td> &nbsp;</td>
                                                            <td><?php echo text($productqty); ?></td>
                                                            <td><?php echo text(bucks($producttotal)); ?></td>
                                                        </tr>

                                                        <tr >
                                                            <td> &nbsp;</td>
                                                            <td colspan="3"><b><?php echo xlt('Total for category') . ' ';
                                                                echo text(display_desc($category)); ?>
                                                                </b>
                                                            </td>
                                                            <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
                                                            <td>&nbsp;</td>
                                                            <?php } ?>
                                                            <td>&nbsp;</td>
                                                            <td ><b><?php echo text($catqty); ?></b></td>
                                                            <td><b><?php echo text(bucks($cattotal)); ?></b></td>
                                                        </tr>

                                                        <tr>
                                                            <td> &nbsp;</td>
                                                            <td colspan="4"><b><?php echo xlt('Grand Total'); ?></b></td>
                                                            <?php if ($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
                                                            <td>&nbsp;</td>
                                                            <?php } ?>
                                                            <!-- <td>&nbsp;</td> -->
                                                            <td><b><?php echo text($grandqty); ?> </b></td>
                                                            <td><b><?php echo text(bucks($grandtotal)); ?> </b></td>
                                                        </tr>
                                                        <?php $report_from_date = oeFormatShortDate($form_from_date)  ;
                                                        $report_to_date = oeFormatShortDate($form_to_date)  ;
                                                        ?>
                                                        <!-- <div align='right'><span class='title' >
                                                        <?php echo xlt('Report Date'). ' '; ?><?php echo text($report_from_date);?> - <?php echo text($report_to_date);?></span></div> -->
                                                        <?php
                                                    } // End not csv export
                                                }

                                                if (! $_POST['form_csvexport']) {
                                                    if ($_POST['form_refresh']) {
                                                ?>

                                                </tbody>
                                            </table>
                                        </div> <!-- report results -->
                                        <?php
                                        } else { ?>
                                        <div class='text'></div>
                                        <?php
                                        } ?>
             
                                    </div>
                                </div>

                             
                            </form>
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
                                  
                        
                        
