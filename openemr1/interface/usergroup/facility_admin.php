<?php
/**
 * facility_admin.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/erx_javascript.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

if (isset($_GET["fid"])) {
    $my_fid = $_GET["fid"];
}
?>
<html>
<head>


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

    <script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/AnchorPosition.js"></script>
    <script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/PopupWindow.js"></script>
    <script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/ColorPicker2.js"></script>

    <!-- validation library -->
    <!--//Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation-->
    <?php    $use_validate_js = 1;?>
    <?php  require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>
    <?php
    //Gets validation rules from Page Validation list.
    //Note that for technical reasons, we are bypassing the standard validateUsingPageRules() call.
    $collectthis = collectValidationPageRules("/interface/usergroup/facility_admin.php");
    if (empty($collectthis)) {
        $collectthis = "undefined";
    } else {
        $collectthis = json_sanitize($collectthis["facility-form"]["rules"]);
    }
    ?>

    <script type="text/javascript">

        /*
         * validation on the form with new client side validation (using validate.js).
         * this enable to add new rules for this form in the pageValidation list.
         * */
        var collectvalidation = <?php echo $collectthis; ?>;

        function submitform() {

            var valid = submitme(1, undefined, 'facility-form', collectvalidation);
            if (!valid) return;

            <?php if ($GLOBALS['erx_enable']) { ?>
            alertMsg = '';
            f = document.forms[0];
            for (i = 0; i < f.length; i++) {
                if (f[i].type == 'text' && f[i].value) {
                    if (f[i].name == 'facility' || f[i].name == 'Washington') {
                        alertMsg += checkLength(f[i].name, f[i].value, 35);
                        alertMsg += checkFacilityName(f[i].name, f[i].value);
                    }
                    else if (f[i].name == 'street') {
                        alertMsg += checkLength(f[i].name, f[i].value, 35);
                        alertMsg += checkAlphaNumeric(f[i].name, f[i].value);
                    }
                    else if (f[i].name == 'phone' || f[i].name == 'fax') {
                        alertMsg += checkPhone(f[i].name, f[i].value);
                    }
                    else if (f[i].name == 'federal_ein') {
                        alertMsg += checkLength(f[i].name, f[i].value, 10);
                        alertMsg += checkFederalEin(f[i].name, f[i].value);
                    }
                }
            }
            if (alertMsg) {
                alert(alertMsg);
                return false;
            }
            <?php } ?>

            top.restoreSession();

            let post_url = $("#facility-form").attr("action");
            let request_method = $("#facility-form").attr("method");
            let form_data = $("#facility-form").serialize();

            $.ajax({
                url: post_url,
                type: request_method,
                data: form_data
            }).done(function (r) { //
                dlgclose('refreshme', false);
            });
            return false;
        }

        $(function(){
            $("#cancel").click(function() {
                dlgclose();
            });

            /**
             * add required/star sign to required form fields
             */
            for (var prop in collectvalidation) {
                //if (collectvalidation[prop].requiredSign)
                if (collectvalidation[prop].presence)
                    jQuery("input[name='" + prop + "']").after('*');
            }
        });
        var cp = new ColorPicker('window');
        // Runs when a color is clicked
        function pickColor(color) {
            document.getElementById('ncolor').value = color;
        }
        var field;
        function pick(anchorname,target) {
            var cp = new ColorPicker('window');
            field=target;
            cp.show(anchorname);
        }
        function displayAlert()
        {
            if(document.getElementById('primary_business_entity').checked==false)
                alert(<?php echo xlj('Primary Business Entity tax id is used as the account id for NewCrop ePrescription.'); ?>);
            else if(document.getElementById('primary_business_entity').checked==true)
                alert(<?php echo xlj('Once the Primary Business Facility is set, changing the facility id will affect NewCrop ePrescription.'); ?>);
        }
    </script>

</head>
<body class="body_top" >
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
                                <div class="pt-4 pb-4">
                                    <div class="">
                                        <table class="table table-form">
                                            <tbody id="TextBoxContainer13" class="repeat-row ">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Billing Address</th>
                                                    <th>Mailing Address</th>
                                                    <th>Phone</th>

                                                    <th></th>
                                                </tr>
                                                <?php
                                            $fres = 0;
                                            $fres = $facilityService->getAll();
                                            if ($fres) {
                                                $result2 = array();
                                                for ($iter3 = 0; $iter3 < sizeof($fres); $iter3++) {
                                                    $result2[$iter3] = $fres[$iter3];
                                                }

                                                foreach ($result2 as $iter3) {
                                                    $varstreet="";//these are assigned conditionally below,blank assignment is done so that old values doesn't get propagated to next level.
                                                    $varcity="";
                                                    $varstate="";
                                                    $varmstreet="";
                                                    $varmcity="";
                                                    $varmstate="";
                                                    $varstreet=$iter3["street"];
                                                    if ($iter3["street"]!="") {
                                                        $varstreet=$iter3["street"].",";
                                                    }

                                                    if ($iter3["city"]!="") {
                                                        $varcity=$iter3["city"].",";
                                                    }

                                                    if ($iter3["state"]!="") {
                                                        $varstate=$iter3["state"].",";
                                                    }

                                                    $varmstreet=$iter3["mail_street"];
                                                    if ($iter3["mail_street"] !="") {
                                                        $varmstreet=$iter3["mail_street"].",";
                                                    }

                                                    if ($iter3["mail_city"]!="") {
                                                        $varmcity=$iter3["mail_city"].",";
                                                    }

                                                    if ($iter3["mail_state"]!="") {
                                                        $varmstate=$iter3["mail_state"].",";
                                                    }
                                                ?>
                                                 <tr class="tablerow bodypart13">
                                                    <td><input type="text" class="form-control active-text1"  value="<?php echo xlt($iter3["name"]);?>"><a href="facility_admin.php?fid=<?php echo attr_url($iter3["id"]); ?>" class="medium_modal"></a></td>
                                                    <td><input type="text" class="form-control active-text1" value="<?php echo text($varstreet.$varcity.$varstate.$iter3["country_code"]." ".$iter3["postal_code"]); ?>"></td>
                                                    <td><input type="text" class="form-control active-text1" value="<?php echo text($varmstreet.$varmcity.$varmstate.$iter3['mail_zip']); ?>"></td>
                                                    <td><input type="text" class="form-control active-text1" value="<?php echo text($iter3["phone"]);?>"></td>
                                                    <td>
                                                    <a href="facility_admin.php?fid=<?php echo attr_url($iter3["id"]); ?>" class="medium_modal"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" fac_id="<?php echo attr_url($iter3["id"]); ?>" class="xxx pr-2 edit_data"></a>
                                                        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" fid="<?php echo attr_url($iter3["id"]); ?>" class="remove13" alt=""></td>
                                                </tr>
                                                <?php
                                                }
                                            }

                                            if (count($result2)<=0) {?>
                                            <tr height="25">
                                                <td colspan="3"  style="text-align:center;font-weight:bold;"> <?php echo xlt("Currently there are no facilities."); ?></td>
                                            </tr>
                                                <?php
                                            } ?>
                                               
                                            </tbody>
                                        </table>
                                    </div>
                                  
                                    <div>

                                    </div>
                                </div>  

                            <!-- form -->
                           

                                <form name='facility-form' id="facility-form" method='post' action="facilities.php">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <input type=hidden name=mode value="facility">
                                    <input type=hidden name=newmode value="admin_facility"> <!--    Diffrentiate Admin and add post backs -->
                                    <input type=hidden name=fid value="<?php echo attr($my_fid); ?>">
                                    <?php $facility = $facilityService->getById($my_fid); ?>
                                    <div class="pt-4 pb-4">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p>Name</p>
                                                <input type=entry name=facility class="form-control pr-1 pl-1" value="<?php echo attr($facility['name']); ?>" id="f_name">
                                            </div>


                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p>Address</p>
                                                <textarea id="" class="form-control pt-3" rows="3" name=street><?php echo attr($facility["street"]); ?></textarea>
                                            </div>


                                        </div>
                                        <div class="row pt-4">
                                            <div class="col-md-3">
                                                <p>City</p>
                                                <input type=entry size=20 name=city value="<?php echo attr($facility["city"]); ?>"  class="form-control mt-2">
                                              
                                            </div>
                                            <div class="col-md-3">
                                                <p>State</p>
                                                <select name="state" class="form-control mt-2">
                                                        <?php
                                                            $body = sqlStatement("SELECT option_id, title FROM list_options " .
                                                            "WHERE list_id = 'state' AND activity = 1 ORDER BY seq");
                                                            while ($orow = sqlFetchArray($body)) {
                                                                echo "    <option value='" . attr($orow['option_id']) . "'";
                                                                if ($orow['option_id'] == attr($facility["state"])) {
                                                                    echo " selected";
                                                                }

                                                                echo ">" . text($orow['title']) . "</option>\n";
                                                            }
                                                          
                                                        ?>
                                                        </select>
                                               
                                            </div>
                                            <div class="col-md-3">
                                                <p>Zip</p>
                                                <input type=entry size=20 name=postal_code value="<?php echo attr($facility["postal_code"]); ?>"  class="form-control mt-2">
                                               
                                            </div>
                                            <div class="col-md-3">
                                                <p>E-Mail</p>
                                                <input type=entry size=20 name=email value="<?php echo attr($facility["email"]); ?>"  class="form-control pr-1 pl-1">
                                                <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                            </div>

                                        </div>
                                        <div class="row pt-4">
                                            <div class="col-md-4">
                                                <p>Phone</p>
                                                <input type=entry name=phone value="<?php echo attr($facility['phone']); ?>" class="form-control pr-1 pl-1">
                                            </div>
                                            <div class="col-md-4">
                                                <p>Fax</p>
                                                <input type=entry name=fax  value="<?php echo attr($facility['fax']); ?>" class="form-control pr-1 pl-1">
                                            </div>
                                            <div class="col-md-4">
                                                <p>Website</p>
                                                <input type=entry  name=website value="<?php echo attr($facility["website"]); ?>" class="form-control pr-1 pl-1">
                                            </div>

                                        </div>
                                        <div class="row pt-4">
                                            <div class="col-md-4">
                                                <p>Tax ID</p>
                                                <select name=tax_id_type class="form-control mt-2">
                                                    <option value="EI" <?php echo $ein;?>><?php echo xlt('EIN'); ?></option>
                                                    <option value="SY" <?php echo $ssn;?>><?php echo xlt('SSN'); ?></option>
                                                </select>
                                                <!-- <input type=entry size=11 name=federal_ein value="<?php echo attr($facility["federal_ein"]); ?>" class="form-control pr-1 pl-1"> -->
                                               
                                            </div>
                                            <div class="col-md-4">
                                                <p>Facility NPI</p>
                                                <input type=entry size=20 name=facility_npi value="<?php echo attr($facility["facility_npi"]); ?>"  class="form-control pr-1 pl-1">
                                            </div>
                                            <div class="col-md-4">
                                                <p>Facility Taxonomy</p>
                                                <input type=entry size=20 name=facility_taxonomy value="<?php echo attr($facility["facility_taxonomy"]); ?>" class="form-control pr-1 pl-1">

                                            </div>

                                        </div>
                                        <div class="row pt-4 pb-4">
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                    <!-- <input type='checkbox' name='billing_location' value = '1'> -->
                                                    <input type="checkbox" class="form-check-input" <?php echo ($facility['billing_location'] != 0) ? 'checked' : ''; ?> name='billing_location' value = '1'>Billing Location
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input" name='service_location' <?php echo ($facility['service_location'] == 1) ? 'checked' : ''; ?> value = '1'>Service Location
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input" name='accepts_assignment' value = '1'  <?php echo ($facility['accepts_assignment'] == 1) ? 'checked' : ''; ?>>Accept Assignment only if Billing Location
                                                    </label>
                                                </div>
                                            </div>
                                            <?php
                                            $disabled='';
                                            $resPBE = $facilityService->getPrimaryBusinessEntity(array("excludedId" => $my_fid));
                                            if (!empty($resPBE) && sizeof($resPBE)>0) {
                                                $disabled='disabled';
                                            }
                                            ?>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input" name='primary_business_entity' id='primary_business_entity' value='1' <?php echo ($facility['primary_business_entity'] == 1) ? 'checked' : ''; ?>
                                                        <?php if ($GLOBALS['erx_enable']) { ?>
                                                            onchange='return displayAlert()'
                                                        <?php } ?> <?php echo $disabled;?>>Primary Business Entity
                                                    </label>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row pt-4">
                                            <div class="col-md-3">
                                                <p>POS Code</p>
                                                <select name="pos_code" class="form-control mt-2">
                                                    <?php
                                                    $pc = new POSRef();

                                                    foreach ($pc->get_pos_ref() as $pos) {
                                                        echo "<option value=\"" . attr($pos["code"]) . "\" ";
                                                        if ($facility['pos_code'] == $pos['code']) {
                                                            echo "selected";
                                                        }
                                                        echo ">" . text($pos['code'])  . ": ". text($pos['title']);
                                                        echo "</option>\n";
                                                    }

                                                    ?>
                                                    </select>
                                               
                                            </div>
                                            <div class="col-md-3">
                                                <p>Hourly Rate</p>
                                                <input type="entry" name="rate" value="<?php echo attr($facility['hourly_rate']); ?>" class="form-control pr-1 pl-1">

                                                <!-- <select name="" id="" class="form-control mt-2">
                                                    <option value="">value</option> 
                                                    <option value="">value</option>
                                                    <option value="">value</option>
                                                </select> -->
                                            </div>
                                            <div class="col-md-3">
                                                <p>Billing Attn</p>
                                                <input type="entry" value="<?php echo attr($facility['attn']); ?>" name="attn" size="45" class="form-control pr-1 pl-1">
                                                <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                            </div>
                                            <div class="col-md-3">
                                                <p>CLIA Number</p>
                                                <input type="entry" name="domain_identifier" value="<?php echo attr($facility['domain_identifier']); ?>" class="form-control pr-1 pl-1">
                                                <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                            </div>
                                            <div class="col-md-3">
                                                <p>Facility ID</p>
                                                <input type="entry" name="facility_id" value="<?php echo attr($facility['facility_code']); ?>" class="form-control pr-1 pl-1">
                                                <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                            </div>
                                            <div class="col-md-3">
                                                <p>OID</p>
                                                <input type="entry"class="form-control pr-1 pl-1" name="oid" value="<?php echo attr($facility["oid"]) ?>">
                                                <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                            </div>

                                        </div>



                                    </div>

                                    <div class="pt-4 pb-2"><button class="form-save" onclick="submitform();" name='form_save' id='form_save'>Save</button></div>
                                    
                                </form>

                                
                            </div>
                        </div>
                    </window-dashboard>
                </div>
            </div>
        </section>
        <script language="JavaScript">

$(function() {
    // $("#insertrow3").bind("click", function() {
    //     console.log("fgf")
    //     var div = $("<tr class='bodypart13 tablerow' />");
    //     div.html(GetDynamicTextBox13(""));
    //     $("#TextBoxContainer13").append(div);
    //     makeTextboxEditable1();
    // });
    $("body").on("click", ".remove13", function() {
        thisss=$(this);

        if(confirm("Are You Sure want to delete?")){
           $fid= $(this).attr('fid');
         
           $.post("facilities.php",
            {
                mode:"facility_delete",
                action: "Delete",
                fid:  $fid,
            },
            function(data, status){
                thisss.closest(".bodypart13").remove();
            });
           
        }
      
        
    });
});
</script>
</body>
</html>
