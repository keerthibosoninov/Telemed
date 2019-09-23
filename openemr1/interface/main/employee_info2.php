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
include_once("$webroot/interface/new/new_injury_save.php");



use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

// test
$pid=1;

if($pid){
    $conset=sqlStatement("Select hipaa_voice,hipaa_mail,hipaa_allowsms,hipaa_allowemail,email_verified from patient_data where id=$pid");
    $conset_data=sqlFetchArray($conset);
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

    <?php 
    //Header::setupHeader(['datetime-picker', 'jquery-ui', 'jquery-ui-redmond', 'opener', 'moment']); ?>
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
                                        <a class="nav-link active" data-toggle="tab" href="#home">Demographics</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#menu1">Authorizations</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#menu2">Consent</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div id="home" class="container tab-pane active">
                                        <div class="form-inputs">
                                            <div class="pt-5">
                                                <p>Name</p>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" placeholder="First Name">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" placeholder="Middle Name">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" placeholder="Last Name">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-3">

                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <p>Gender</p>
                                                        <select name="" id="" class="form-control mt-2">
                                                            <option value="">male</option>
                                                            <option value="">Female</option>
                                                            <option value="">Other</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <p>DOB</p>
                                                        <input type="date" class="form-control pr-1 pl-1" placeholder="">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <p>Phone</p>
                                                        <input type="text" class="form-control " placeholder="Number">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p>Type</p>
                                                        <select name="" id="" class="form-control mt-2">
                                                                <option value="">Value 1</option>
                                                                <option value="">value 2</option>
                                                                <option value="">Value 3</option>
                                                            </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p>Social Security No.</p>
                                                        <input type="text" class="form-control" placeholder="email">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="div pt-3">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p> Email</p>
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="div pt-3">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p>Address</p>
                                                        <textarea name="" id="" class="form-control" rows="4"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-3">

                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <p>City</p>
                                                        <input type="text" class="form-control" placeholder="City">
                                                    </div>

                                                    <div class="col-md-3">
                                                        <p>State</p>
                                                        <select name="" id="" class="form-control mt-2">
                                                            <option value="">Value 1</option>
                                                            <option value="">value 2</option>
                                                            <option value="">Value 3</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p>Zip</p>
                                                        <input type="text" class="form-control" placeholder="Zip">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p>County</p>
                                                        <input type="text" class="form-control" placeholder="County">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-3">

                                                <div class="row">
                                                    <div class="col-md-3 doubt">
                                                        <p>Job Title</p>
                                                        <input type="text" class="form-control" placeholder="Job Title">
                                                        <img src="img/doubt.svg" class="doubt-img" alt="">
                                                    </div>

                                                    <div class="col-md-3">
                                                        <p>Employment Status</p>
                                                        <input type="text" class="form-control" placeholder="Employment Status">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p>Department</p>
                                                        <input type="text" class="form-control" placeholder="Department">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p>Date of Hire</p>
                                                        <input type="text" class="form-control" placeholder="Date of Hire">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-3">

                                                <div v-bind:class="['row',(showMailBox?'show-mail':'')]">
                                                    <div class="col-md-2">
                                                        <p class="pt-3">Send Link to</p>

                                                    </div>

                                                    <div class="col-md-3 add-more">
                                                        <select name="" id="" class="form-control mt-2">
                                                            <option value="">Email</option>
                                                            <option value="">value 2</option>
                                                            <option value="">Value 3</option>
                                                        </select>
                                                        <img src="img/addmore.svg" class="addmore-img" v-on:click="showMailBox = !showMailBox" alt="">
                                                    </div>
                                                    <div class="col-md-3 mail-hide">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                    <div class="col-md-2 p-0 mail-hide">
                                                        <div class="form-check "><label class="form-check-label fs-10 lh-25 pt-3
                                                            "><input type="checkbox" name="optradio" class="form-check-input w-auto">Add this ID to mailing list
                                                        </label></div>
                                                    </div>
                                                    <div class="col-md-2 pt-2">
                                                        <button class="form-save">Send</button>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="pt-4 pb-5">
                                                <button class="form-save">Save</button>
                                            </div>
                                        </div>

                                    </div>
                                    <div id="menu1" class="container tab-pane fade">
                                        <div class="pt-4 pb-4">
                                            <div class="table-div">
                                                <table class="table table-form">
                                                    <tr>
                                                        <th>Sl No.</th>
                                                        <th>Name</th>
                                                        <th>Relation</th>
                                                        <th>Contact Info</th>
                                                        <th>ROI</th>
                                                        <th><img src="img/delete.svg" alt=""></th>
                                                    </tr>
                                                    <tr>
                                                        <td>2345</td>
                                                        <td>Sasuke Uchiha</td>
                                                        <td>Friend</td>
                                                        <td>(213)999-6666</td>
                                                        <td>n/a</td>
                                                        <th><img src="img/delete.svg" alt=""></th>
                                                    </tr>
                                                    <tr>
                                                        <td>2345</td>
                                                        <td>Sasuke Uchiha</td>
                                                        <td>Friend</td>
                                                        <td>(213)999-6666</td>
                                                        <td>n/a</td>
                                                        <th><img src="img/delete.svg" alt=""></th>
                                                    </tr>
                                                    <tr>
                                                        <td>2345</td>
                                                        <td>Sasuke Uchiha</td>
                                                        <td>Friend</td>
                                                        <td>(213)999-6666</td>
                                                        <td>n/a</td>
                                                        <th><img src="img/delete.svg" alt=""></th>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div>
                                                <div class="text-center">
                                                    <img src="img/addmore.svg" alt="" v-on:click="addData = !addData" class="class-hide">
                                                </div>
                                                <div class="text-center">
                                                    <p class="fs-14">Add New</p>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="container-fluid">
                                                    <div v-bind:class="['row',(addData?'d-none':'')]">
                                                        <div class="col-md-3">
                                                            <input type="text" class="form-control" placeholder="name">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text" class="form-control" placeholder="Relation">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text" class="form-control" placeholder="Contact No.">
                                                        </div>
                                                        <div class="col-md-3 pt-2">
                                                            <Select class="form-control">
                                                            <option value="">ROI</option>
                                                        </Select>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="menu2" class="container tab-pane fade">
                                        <form id="employee_conset" onsubmit="return  submit_conset();">
                                        <div class="pt-4 pb-5">
                                                <div>
                                                    <div class="">

                                                        <div class="yes-no">
                                                            <div class="pt-3">
                                                                <p>Verbal Consent given for Telemedicine ?</p>
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <div class="form-check ">
                                                                            <label class="form-check-label">
                                                                                <input type="radio" class="form-check-input w-auto" name="hipaa_voice" value="YES" <?php if(isset($conset_data['hipaa_voice']) && $conset_data['hipaa_voice']=='YES'){ echo "checked";}?> >yes
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 text-right">
                                                                        <div class="form-check">
                                                                            <label class="form-check-label">
                                                                                <input type="radio" class="form-check-input w-auto" name="hipaa_voice" value="NO" <?php if(isset($conset_data['hipaa_voice']) && $conset_data['hipaa_voice']=='NO'){ echo "checked";}?>>No
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="pt-3">
                                                                <p>Written Consent given for Telemedicine ?</p>
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <div class="form-check ">
                                                                            <label class="form-check-label">
                                                                                    <input type="radio" class="form-check-input w-auto" name="hipaa_mail" value="YES" <?php if(isset($conset_data['hipaa_mail']) && $conset_data['hipaa_mail']=='YES'){ echo "checked";}?>>yes
                                                                                </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 text-right">
                                                                        <div class="form-check">
                                                                            <label class="form-check-label">
                                                                                    <input type="radio" class="form-check-input w-auto" name="hipaa_mail" value="NO" <?php if(isset($conset_data['hipaa_mail']) && $conset_data['hipaa_mail']=='NO'){ echo "checked";}?>>No
                                                                                </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="pt-3">
                                                                <p>Okay to Leave a Message?</p>
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <div class="form-check ">
                                                                            <label class="form-check-label">
                                                                                        <input type="radio" class="form-check-input w-auto" name="hipaa_allowsms" value="YES" <?php if(isset($conset_data['hipaa_allowsms']) && $conset_data['hipaa_allowsms']=='YES'){ echo "checked";}?>>yes
                                                                                    </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 text-right">
                                                                        <div class="form-check">
                                                                            <label class="form-check-label">
                                                                                        <input type="radio" class="form-check-input w-auto" name="hipaa_allowsms" value="NO" <?php if(isset($conset_data['hipaa_allowsms']) && $conset_data['hipaa_allowsms']=='NO'){ echo "checked";}?>>No
                                                                                    </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="pt-3">
                                                                <p>Okay to send E-Mail?</p>
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <div class="form-check ">
                                                                            <label class="form-check-label">
                                                                                            <input type="radio" class="form-check-input w-auto" name="hipaa_allowemail" value="YES" <?php if(isset($conset_data['hipaa_allowemail']) && $conset_data['hipaa_allowemail']=='YES'){ echo "checked";}?>>yes
                                                                                        </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 text-right">
                                                                        <div class="form-check">
                                                                            <label class="form-check-label">
                                                                                            <input type="radio" class="form-check-input w-auto" name="hipaa_allowemail" value="NO" <?php if(isset($conset_data['hipaa_allowemail']) && $conset_data['hipaa_allowemail']=='NO'){ echo "checked";}?>>No
                                                                                        </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="pt-3 pb-4">
                                                                <p> E-Mail Verified</p>
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <div class="form-check ">
                                                                            <label class="form-check-label">
                                                                                                <input type="radio" class="form-check-input w-auto" name="email_verified" value="YES" <?php if(isset($conset_data['email_verified']) && $conset_data['email_verified']=='YES'){ echo "checked";}?>>yes
                                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 text-right">
                                                                        <div class="form-check">
                                                                            <label class="form-check-label">
                                                                                                <input type="radio" class="form-check-input w-auto" name="email_verified" value="NO" <?php if(isset($conset_data['email_verified']) && $conset_data['email_verified']=='NO'){ echo "checked";}?>>No
                                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <input type="hidden" value="1" name="conset_insert">
                                                            <input type="hidden" value="<?php echo $pid?>" name="pid">
                                                            <button class="form-save " type="submit">Save</button>
                                                        </div>
                                                    </div>
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
   
<script>

function submit_conset(){



    $webroot=  "<?php echo $GLOBALS['webroot'];?>";


    $.ajax({
        type: 'POST',
        url: $webroot+"/interface/new/new_injury_save.php",
        data: $('#employee_conset').serialize(),   
        success: function(data){
        alert(data);
        console.log(data);
        
        }
    });

}

</script>

    </body>
    <?php
}
    ?>
    </body>

</html>