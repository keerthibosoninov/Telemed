<?php
/**
 * This module shows relative insurance usage by unique patients
 * that are seen within a given time period.  Each patient that had
 * a visit is counted only once, regardless of how many visits.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../library/acl.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Might want something different here.
//
// if (! acl_check('acct', 'rep')) die("Unauthorized access.");


if ($_POST['form_csvexport']) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=insurance_distribution.csv");
    header("Content-Description: File Transfer");
    // CSV headers:
    if (true) {
        echo '"Insurance",';
        echo '"Charges",';
        echo '"Visits",';
        echo '"Patients",';
        echo '"Pt Pct"' . "\n";
    }
} else {
    ?>
<html>
<head>

<title><?php echo xlt('Patients document upload'); ?></title>


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

    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/pat_css/style.css">
   
    <!--  km -->

    <?php Header::setupHeader('datetime-picker'); ?>



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
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}


.css_button:hover, button:hover, input[type=button]:hover, input[type=submit]:hover {
    background: #3C9DC5;
    text-decoration: none;
}

#report_parameters {
    background-color: transparent !important;
    margin-top: 10px;
}
.dragAndDrop[type="file"] {
    opacity: 0;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
</style>
</head>

<body class="body_top">

<section>


        <section>
            <div class="body-content body-content2" style="margin-left:0%"> 
            <div class="container-fluid pb-4 pt-4">
                    <window-dashboard title="" class="icon-hide">
                        <div class="head-component">
                            <div class="row">
                                <div class="col-6"></div>
                                <div class="col-6">
                                    <p class="text-white head-p">Patient Document Upload </p>
                                </div>
                            </div>
                        </div>
                        <div class="body-compo">
                            <div id="report_parameters_daterange">
                                <span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Patient Insurance Distribution'); ?></span>
                                <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt("to") . " &nbsp; ". text(oeFormatShortDate($form_to_date)); ?>
                          
                            </div>
                            <form name='theform' method='post' action='insurance_allocation_report.php' id='theform' onsubmit='return top.restoreSession()'>

                            <div class="row">
                                <div class="col-4 border-right-silver ">
                                    <div class="folder-tree-wrapper">
                                        <ul class="folder-tree">
                                            <li class="expanded"><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder-m.svg" alt=""></span> Categories
                                                <div class="arrow"><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder-plus.svg" alt=""></span></div>

                                                <ul>

                                                    <li class="expanded"><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> Advance Directive
                                                        <div class="arrow"><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder-plus.svg" alt=""></span></div>
                                                        <ul>
                                                            <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> CCD</li>
                                                            <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span>CCDA</li>
                                                            <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> CCR</li>

                                                        </ul>

                                                    </li>
                                                    <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> Eye Module
                                                        <div class="arrow"><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder-plus.svg" alt=""></span></div>
                                                        <ul>
                                                            <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> Lab Report </li>
                                                            <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> Medical Record</li>


                                                        </ul>

                                                    </li>
                                                    <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> Onsite Portal
                                                        <div class="arrow"><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder-plus.svg" alt=""></span></div>
                                                        <ul>
                                                            <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> lorem </li>
                                                            <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> lorem</li>


                                                        </ul>

                                                    </li>
                                                    <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> Patient Information
                                                        <div class="arrow"><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder-plus.svg" alt=""></span></div>
                                                        <ul>
                                                            <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> lorem </li>
                                                            <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> lorem</li>


                                                        </ul>

                                                    </li>

                                                </ul>

                                            </li>
                                            <!-- <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> New Folder (1)</li>
                                            <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> New Folder (2)</li>
                                            <li><span><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/folder.svg" alt=""></span> folder</li> -->
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="dragableAra">
                                        <input type="file" class="dragAndDrop" id="uploadFile">
                                        <label for="uploadFile" class="pt-4 pb-3">Upload Document to Categories</label>
                                        <div>
                                            <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/pat_images/cloud-upload.svg" alt="">
                                        </div>
                                        <div class="pt-4">
                                            <p>Drag & Drop files here</p>
                                        </div>
                                        <div class="pt-4">

                                            <div class="some-30">
                                                <button class="form-save point-none">BROWSE</button>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="uploadedFile">
                                        <img src="" alt="">
                                    </div>
                                </div>
                            </div>


                            </form>
                        </div>
                    </window-dashboard >
                </div>
            </div>
        </section>
    </body>

    
    
</html>
    <?php
} // end not export
?>
<!--  -->
 