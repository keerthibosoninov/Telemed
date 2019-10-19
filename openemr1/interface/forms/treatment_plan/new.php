<?php
/**
 * treatment plan form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Naina Mohamed <naina@capminds.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012-2013 Naina Mohamed <naina@capminds.com> CapMinds Technologies
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

formHeader("Form:Treatment Planning");
$returnurl = 'encounter_top.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : 0);
$obj = $formid ? formFetch("form_treatment_plan", $formid) : array();

// Get the providers list.
 $ures = sqlStatement("SELECT id, username, fname, lname FROM users WHERE " .
  "authorized != 0 AND active = 1 ORDER BY lname, fname");
    ?>
<html>

<head>

    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
    <link rel="stylesheet"
        href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.min.css">

    <script type="text/javascript"
        src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript"
        src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery/dist/jquery.min.js">
    </script>
    <script type="text/javascript"
        src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js">
    </script>



    <!-- km -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/vue.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>



    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/main.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/addmore.js"></script>

    <script language="JavaScript">
    $(function() {
        var win = top.printLogSetup ? top : opener.top;
        win.printLogSetup(document.getElementById('printbutton'));

        $('.datepicker').datetimepicker({
            <? php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'].
                '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });
    </script>


    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/style.css">

    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/employee_dashboard_style.css">
    <style>
        input[type=text] {
        background: #fff;
        border: 1px solid #ced4da;
        padding: 3px;
        margin: 3px;
        }

        .css_button:hover, button:hover, input[type=button]:hover, input[type=submit]:hover {
            background: #3C9DC5;
            text-decoration: none;
        }

        input[type=date]{
            margin-top:0px;
        }
    </style>

    <script>
        $(document).ready(function() {

        makeTextboxEditable();
        });

        function makeTextboxEditable() {
        $(".xx").click(function() {
            $(this)
                .closest(".row")
                .addClass("activatetextarea")
        });
        }
    </script>

</head>

<body class="body_top">
    <section>
        <div class="body-content body-content2">
            <div class="container-fluid pb-4 pt-4">
                <window-dashboard title="Education" class="icon-hide">
                    <div class="head-component">
                        <div class="row">
                            <div class="col-6"></div>
                            <div class="col-6">
                                <p class="text-white head-p">Observation </p>
                            </div>
                        </div>
                    </div>
                    <div class="body-compo">

                        <?php
                        echo "<form method='post' name='my_form' " .
                        "action='$rootdir/forms/treatment_plan/save_form.php?id=" . attr_url($formid) ."'>\n";
                        ?>
                        <input type="hidden" name="csrf_token_form"
                            value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                            <div class="row">
                                <div class="col-md-3">
                                    <p>Name</p>
                                    <?php if (is_numeric($pid)) {
                                        $result = getPatientData($pid, "fname,lname,squad");
                                        
                                    }
                                    ?>
                                    <input type="text" placeholder="" class="form-control" value="<?php echo text($result['fname'])." ".text($result['lname']);?>">
                                    <?php
                                       $patient_name=($result['fname'])." ".($result['lname']);
                                    ?>
                                    <input type="hidden" name="client_name" value="<?php echo attr($patient_name);?>">

                                </div>
                                <div class="col-md-3">
                                    <p>DOB</p>
                                    <?php if (is_numeric($pid)) {
                                        $result = getPatientData($pid, "*");
                                        
                                    }
                                    ?>
                                    <input type="date" placeholder="" class="form-control" value="<?php echo text($result['DOB']);?>">
                                    <?php
                                        $dob=($result['DOB']);
                                    ?>
                                    <input type="hidden" name="DOB" value="<?php echo attr($dob);?>">
                                
                                </div>
                                <div class="col-md-3">
                                    <p>Employee ID</p>
                                    <?php if (is_numeric($pid)) {
                                        $result = getPatientData($pid, "*");
                                        
                                    }
                                    ?>
                                    <input type="text" placeholder="" class="form-control" value="<?php echo text($result['pid']);?>">
                                    <?php 
                                        $patient_id=$result['pid'];
                                    ?>
                                    <input type="hidden" name="client_number" value="<?php echo attr($patient_id);?>">

                                </div>
                                <div class="col-md-3">
                                    <p>Admit Date</p>
                                    <input type="date" placeholder="" name='admit_date' class="form-control" value="<?php echo attr($obj{"admit_date"}); ?>">
                                    
                                </div>
                                <div class="col-md-3">
                                    <p>Provider</p>
                                    <select name='provider' id="" class="form-control">
                                        <?php
                                            while ($urow = sqlFetchArray($ures)) {
                                            echo "    <option value='" . attr($urow['lname']) . "'";
                                            if ($urow['lname'] == attr($obj{"provider"})) {
                                                echo " selected";
                                            }

                                            echo ">" . text($urow['lname']);
                                            if ($urow['fname']) {
                                                echo ", " . text($urow['fname']);
                                            }

                                            echo "</option>\n";
                                        }
                                        ?>


                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                    <div class="col-sm-6">
                                        <p class="fs-14">Presenting Issues</p>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                    </div>
                                    <div class="col-sm-12 pt-2">
                                        <textarea name="presenting_issues" rows="4" cols="60"
                                        wrap="virtual name" class="form-control active-text "><?php echo text($obj{"presenting_issues"});?></textarea>

                                    </div>
                            </div>
                            <div class="row mt-3">
                                    <div class="col-sm-6">
                                        <p class="fs-14">Patient History</p>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                    </div>
                                    <div class="col-sm-12 pt-2">
                                        <textarea class="form-control active-text " name="patient_history" rows="4" 
                                        wrap="virtual name"><?php echo text($obj{"patient_history"});?></textarea>
                                    </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-sm-6">
                                    <p class="fs-14">Medication</p>
                                </div>
                                <div class="col-sm-6">
                                    <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                </div>
                                <div class="col-sm-12 pt-2">
                                    <textarea  class="form-control active-text " name="medications" rows="4" 
                                        wrap="virtual name"><?php echo text($obj{"medications"});?></textarea>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-sm-6">
                                    <p class="fs-14">Diagnosis</p>
                                </div>
                                <div class="col-sm-6">

                                </div>
                                <div class="col-sm-12 pt-2">
                                    <input type="text" class="form-control" name="diagnosis" value="<?php echo text($obj{"diagnosis"});?>">
                                   
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-sm-6">
                                    <p class="fs-14">Treatment Requested</p>
                                </div>
                                <div class="col-sm-6">
                                    <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                </div>
                                <div class="col-sm-12 pt-2">
                                    <textarea class="form-control active-text" name="treatment_received" rows="4" 
                                        wrap="virtual name"><?php echo text($obj{"treatment_received"});?></textarea>
                                </div>
                            </div>
                            <div class="row  mt-3">
                                <div class="col-md-4">
                                    <p>Recommendation for Follow-up</p>
                                    
                                    <input type="date" placeholder="" name="followup_date" class="form-control" value="<?php echo text($obj{"followup_date"});?>">
                                </div>

                            </div>

                            <div class="pt-4 pb-5 col-md-12"><button class="form-save">Save</button></div>


                        </form>
                    </div>
                </window-dashboard>
            </div>
        </div>
    </section>
    <?php
formFooter();
?>