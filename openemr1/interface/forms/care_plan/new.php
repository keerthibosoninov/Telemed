<?php
/**
 * Care plan form new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
$_SESSION["pid"]=1;
$_SESSION["encounter"]=1;
$returnurl = 'encounter_top.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : 0);
if ($formid) {
    $sql = "SELECT * FROM `form_care_plan` WHERE id=? AND pid = ? AND encounter = ?";
    $res = sqlStatement($sql, array($formid,$_SESSION["pid"], $_SESSION["encounter"]));
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }
    $check_res = $all;
}
$check_res = $formid ? $check_res : array();
$sql1 = "SELECT option_id AS `value`, title FROM `list_options` WHERE list_id = ?";
$result = sqlStatement($sql1, array('Plan_of_Care_Type'));
foreach ($result as $value) :
    $care_plan_type[] = $value;
endforeach;
?>
<html>
    <head>
        <title><?php echo xlt("Care Plan Form"); ?></title>

        <?php //Header::setupHeader(['datetime-picker']);?>
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
<!-- textarea_css -->
<style>
    .activatetextarea .active-text {
    pointer-events: all;
    border: 1px solid silver;
    background-color: #ececec00
}
.xx {
    cursor: pointer;
}
.active-text {
    pointer-events: none;
    border: 0px solid silver;
    background-color: #ececec;
}
.form-save1 {
    background-color: #3C9DC5;
    padding: 4px;
    width: 993px;
    border: none;
    outline: none;
    color: white;
    margin-left: 16px;
}
</style>
<!-- //textarea_css -->



        <style type="text/css" title="mystyles" media="all">
            @media only screen and (max-width: 768px) {
                [class*="col-"] {
                width: 100%;
                text-align:left!Important;
            }
            }
        </style>

        <script type="text/javascript">
            function duplicateRow(e) {
                var newRow = e.cloneNode(true);
                e.parentNode.insertBefore(newRow, e.nextSibling);
                changeIds('tb_row');
                changeIds('description');
                changeIds('code');
                changeIds('codetext');
                changeIds('code_date');
                changeIds('displaytext');
                changeIds('care_plan_type');
                changeIds('count');
                removeVal(newRow.id);
            }
            function removeVal(rowid)
            {
                rowid1 = rowid.split('tb_row_');
                document.getElementById("description_" + rowid1[1]).value = '';
                document.getElementById("code_" + rowid1[1]).value = '';
                document.getElementById("codetext_" + rowid1[1]).value = '';
                document.getElementById("code_date_" + rowid1[1]).value = '';
                document.getElementById("displaytext_" + rowid1[1]).innerHTML = '';
                document.getElementById("care_plan_type_" + rowid1[1]).value = '';
            }
            function changeIds(class_val) {
                var elem = document.getElementsByClassName(class_val);
                for (var i = 0; i < elem.length; i++) {
                    if (elem[i].id) {
                        index = i + 1;
                        elem[i].id = class_val + "_" + index;
                    }
                    if(class_val == 'count') {
                      elem[i].value = index;
                    }
                }
            }
            function deleteRow(rowId)
            {
                if (rowId != 'tb_row_1') {
                    var elem = document.getElementById(rowId);
                    elem.parentNode.removeChild(elem);
                }
            }
            function sel_code(id)
            {
                id = id.split('tb_row_');
                var checkId = '_' + id[1];
                document.getElementById('clickId').value = checkId;
                dlgopen('<?php echo $GLOBALS['webroot'] . "/interface/patient_file/encounter/" ?>find_code_popup.php?codetype=SNOMED-CT,LOINC,CPT4', '_blank', 700, 400);
            }
            function set_related(codetype, code, selector, codedesc) {
                var checkId = document.getElementById('clickId').value;
                document.getElementById("code" + checkId).value = code;
                document.getElementById("codetext" + checkId).value = codedesc;
                document.getElementById("displaytext" + checkId).innerHTML  = codedesc;
            }
            $(function () {
                // special case to deal with static and dynamic datepicker items
                $(document).on('mouseover','.datepicker', function(){
                    $(this).datetimepicker({
                        <?php $datetimepicker_timepicker = false; ?>
                        <?php $datetimepicker_showseconds = false; ?>
                        <?php $datetimepicker_formatInput = false; ?>
                        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                    });
                });
            });
        </script>
    </head>
    <body class="body_top">
    <section>
            <div class="body-content body-content2">
                <div class="container-fluid pb-4 pt-4">
                    <window-dashboard title="Care plan" class="icon-hide">
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
                                            <p class="text-white head-p">Care Plan</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <div class="body-compo">
                        
                        <?php echo "<form method='post' name='my_form' " . "action='$rootdir/forms/care_plan/save.php?id=" . attr_url($formid) . "'>\n"; ?>
                        <div class="container-fluid">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <!-- <fieldset> -->
                    <!-- <legend><?php echo xlt('Enter Details'); ?></legend> -->
                    <?php
                    if (!empty($check_res)) {
                        foreach ($check_res as $key => $obj) {
                            ?>
                    <div class="tb_row" id="tb_row_<?php echo attr($key) + 1; ?>">
                    <div class="form-group">
                        <div class=" forms col-xs-3">
                            <label for="code_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Code'); ?>:</label>
                            <input type="text" id="code_<?php echo attr($key) + 1; ?>"  name="code[]" class="form-control code" value="<?php echo attr($obj{"code"}); ?>"  onclick='sel_code(this.parentElement.parentElement.parentElement.id);'>
                            <span id="displaytext_<?php echo attr($key) + 1; ?>"  class="displaytext help-block"></span>
                            <input type="hidden" id="codetext_<?php echo attr($key) + 1; ?>" name="codetext[]" class="codetext" value="<?php echo attr($obj{"codetext"}); ?>">
                        </div>
                        
                        <div class="forms col-xs-2">
                            <label for="code_date_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Date'); ?>:</label>
                            <input type='date' id="code_date_<?php echo attr($key) + 1; ?>" name='code_date[]' class="form-control code_date datepicker" value='<?php echo attr($obj{"date"}); ?>' title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
                        </div>
                        <div class="forms col-xs-2">
                            <label for="care_plan_type_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Type'); ?>:</label>
                            <select name="care_plan_type[]" id="care_plan_type_<?php echo attr($key) + 1; ?>" class="form-control care_plan_type">
                                <option value=""></option>
                                <?php foreach ($care_plan_type as $value) :
                                    $selected = ($value['value'] == $obj{"care_plan_type"}) ? 'selected="selected"' : '';
                                    ?>
                                    <option value="<?php echo attr($value['value']);?>" <?php echo $selected;?>><?php echo text($value['title']);?></option>
                                <?php endforeach;?>
                                </select>
                        </div>
                        <div class="forms col-xs-4">
                            <label for="description_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Description'); ?>:</label>
                            <textarea name="description[]"  id="description_<?php echo attr($key) + 1; ?>" class="form-control description" rows="3" ><?php echo text($obj{"description"}); ?></textarea>
                        </div>
                        <div class="forms col-xs-1" style="padding-top:35px">
                            <i class="fa fa-plus-circle fa-2x" aria-hidden="true" onclick="duplicateRow(this.parentElement.parentElement.parentElement);" title='<?php echo xla('Click here to duplicate the row'); ?>'></i>
                            <i class="fa fa-times-circle fa-2x text-danger"  aria-hidden="true" onclick="deleteRow(this.parentElement.parentElement.parentElement.id);"  title='<?php echo xla('Click here to delete the row'); ?>'></i>
                        </div>
                        <div class="clearfix"></div>
                        <input type="hidden" name="count[]" id="count_<?php echo attr($key) + 1; ?>" class="count" value="<?php echo attr($key) + 1;?>">
                    </div>
                    </div>
                            <?php
                        }
                    } else {
                        ?>
                        <!-- plan section -->
                        <div class="row">
                          
                            <div class="col-md-4">
                                <p>Code</p>
                                <input type="text" id="code_1"  name="code[]" class="form-control code" value="<?php echo attr($obj{"code"}); ?>"  onclick='sel_code(this.parentElement.parentElement.parentElement.id);'>
                                <span id="displaytext_1"  class="displaytext help-block"></span>
                                <input type="hidden" id="codetext_1" name="codetext" class="form-control" value="<?php echo attr($obj{"codetext"}); ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <p>Date</p>
                                <input type='date' id="code_date_1"  name='code_date[]' class="form-control" value='<?php echo attr($obj{"date"}); ?>' />
                            </div>
                            <div class="col-md-4">
                                <p>Type</p>
                                <select name="care_plan_type[]" id="care_plan_type_1" class="form-control mt-2">
                                    <option value=""></option>
                                    <?php foreach ($care_plan_type as $value) :
                                        $selected = ($value['value'] == $obj{"care_plan_type"}) ? 'selected="selected"' : '';
                                        ?>
                                        <option value="<?php echo attr($value['value']);?>" <?php echo $selected;?>><?php echo text($value['title']);?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                                    </div>

                                    <div class="row mt-3">
                                <div class="col-sm-6">
                                    <p class="fs-14">Description</p>
                                </div>
                                <div class="col-sm-6">
                                    <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                </div>
                                <div class="col-sm-12 pt-2">

                                <textarea name="description[]"  id="description_1" class="form-control active-text" rows="3" ><?php echo text($obj{"description"}); ?></textarea>
                            </div>
                                    </div>
                                    <div id="TextBoxContainer7" class="repeat-row"></div>
                            <div class="text-center p-3">
                                <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" id="carePlan" alt="">
                            </div>

                            <!-- <div class="forms col-xs-1 " style="padding-top:35px">
                                <i class="fa fa-plus-circle fa-2x" aria-hidden="true" onclick="duplicateRow(this.parentElement.parentElement.parentElement);" title='<?php echo xla('Click here to duplicate the row'); ?>'></i>
                                <i class="fa fa-times-circle fa-2x text-danger"  aria-hidden="true" onclick="deleteRow(this.parentElement.parentElement.parentElement.id);"  title='<?php echo xla('Click here to delete the row'); ?>'></i>
                            </div> -->
                            <div class="row mt-3">
                                <div class="col-sm-6">
                                    <p class="fs-14">Clinical Instructions</p>
                                </div>
                                <div class="col-sm-6">
                                    <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xx"></div>
                                </div>
                                <div class="col-sm-12 pt-2">
                                    <!-- <textarea name="clinic_instr" id="" rows="4 " class="form-control active-text" placeholder="edit here paragraph shown here"></textarea></div> -->
                                    <textarea name="instruction" id ="instruction"  class="form-control active-text" cols="80" rows="5" ><?php echo text($check_res['instruction']); ?></textarea>

                            </div>
                            <!-- <div class="clearfix"></div> -->
                            <!-- <input type="hidden" name="count[]" id="count_1" class="count" value="1"> -->
                            <input type="hidden" name="count[]" id="count_<?php echo attr($key) + 1; ?>" class="count" value="<?php echo attr($key) + 1;?>">

                        
<!-- plan section end -->


                    <?php }
                    ?>
                <!-- </fieldset> -->
                <!-- <fieldset>
                        <legend class=""><?php echo xlt('Instructions'); ?></legend>
                            <div class="form-group">
                                <div class="col-sm-10 col-sm-offset-1">
                                    <textarea name="instruction" id ="instruction"  class="form-control" cols="80" rows="5" ><?php echo text($check_res['instruction']); ?></textarea>
                                </div>
                            </div>
                    </fieldset> -->
                 <!-- <div class="form-group clearfix">
                    <div class="col-sm-12 position-override">
                        <div class="btn-group oe-opt-btn-group-pinch" role="group">
                            <button type="submit" onclick="top.restoreSession()" class="btn btn-default btn-save"><?php echo xlt('Save'); ?></button>
                            <button type="button" class="btn btn-link btn-cancel oe-opt-btn-separate-left" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel');?></button>
                            <input type="hidden" id="clickId" value="">
                        </div>
                    </div>
                </div> -->
                <div class="pt-4 pb-5">
                    <button type="submit" class="form-save1">Save</button>
                    <input type="hidden" id="clickId" value="">
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

<!-- textarea_js -->
<script>

$(document).ready(function() {

$(".xx").click(function() {
    $(this)
        .closest(".row")
        .addClass("activatetextarea")
});
});
</script>
<!--//textarea_js -->

 <!-- careplan js--------------------------- -->
<script>
$(function() {
    $("#carePlan").bind("click", function() {
        var div = $("<div class='bodypart7' />");
        div.html(GetDynamicTextBox7(""));
        $("#TextBoxContainer7").append(div);
        makeTextboxEditable();
    });
    $("body").on("click", ".remove7", function() {
        $(this).closest(".bodypart7").remove();
    });
});

function GetDynamicTextBox7(value) {
    return `  <div class="row">
    <div class="col-md-4">
        <p>Code</p>
        <input type="text" name="code[]" placeholder="" class="form-control">
    </div>
    <div class="col-md-4">
        <p>Date</p>
        <input type="date" name="code_date[]" placeholder="" class="form-control">
    </div>
    <div class="col-md-4">
        <p>Type</p>
        <select name="care_plan_type[]" id="care_plan_type_1" class="form-control mt-2">
                                    <option value=""></option>
                                    <?php foreach ($care_plan_type as $value) :
                                        $selected = ($value['value'] == $obj{"care_plan_type"}) ? 'selected="selected"' : '';
                                        ?>
                                        <option value="<?php echo attr($value['value']);?>" <?php echo $selected;?>><?php echo text($value['title']);?></option>
                                    <?php endforeach;?>
                                </select>
    </div>
</div>
<div class="row mt-3">
    <div class="col-sm-6">
        <p class="fs-14">Description</p>
    </div>
    <div class="col-sm-6">
        <div class="text-right"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" id="carePlan" class="xx"></div>
    </div>
    <div class="col-sm-12 pt-2 delete-row">    
    <textarea name="description[]"  id="description_1" class="form-control" rows="4" placeholder="edit here paragraph shown here"></textarea>

    <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="remove7" alt="">
    </div>
</div>`

}
</script>
<!-- //carepan js end---------------- -->
