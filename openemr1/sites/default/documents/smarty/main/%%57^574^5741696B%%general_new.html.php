<?php /* Smarty version 2.6.31, created on 2019-10-01 10:31:14
         compiled from D:%5Cxampp%5Chtdocs%5Copenemr_test%5Cinterface%5Cforms%5Cvitals/templates/vitals/general_new.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'headerTemplate', 'D:\\xampp\\htdocs\\openemr_test\\interface\\forms\\vitals/templates/vitals/general_new.html', 12, false),array('function', 'xlj', 'D:\\xampp\\htdocs\\openemr_test\\interface\\forms\\vitals/templates/vitals/general_new.html', 26, false),array('function', 'xlt', 'D:\\xampp\\htdocs\\openemr_test\\interface\\forms\\vitals/templates/vitals/general_new.html', 93, false),array('function', 'xla', 'D:\\xampp\\htdocs\\openemr_test\\interface\\forms\\vitals/templates/vitals/general_new.html', 146, false),array('modifier', 'attr', 'D:\\xampp\\htdocs\\openemr_test\\interface\\forms\\vitals/templates/vitals/general_new.html', 135, false),array('modifier', 'string_format', 'D:\\xampp\\htdocs\\openemr_test\\interface\\forms\\vitals/templates/vitals/general_new.html', 224, false),array('modifier', 'substr', 'D:\\xampp\\htdocs\\openemr_test\\interface\\forms\\vitals/templates/vitals/general_new.html', 323, false),array('modifier', 'date_format', 'D:\\xampp\\htdocs\\openemr_test\\interface\\forms\\vitals/templates/vitals/general_new.html', 354, false),array('modifier', 'js_escape', 'D:\\xampp\\htdocs\\openemr_test\\interface\\forms\\vitals/templates/vitals/general_new.html', 354, false),array('modifier', 'js_url', 'D:\\xampp\\htdocs\\openemr_test\\interface\\forms\\vitals/templates/vitals/general_new.html', 468, false),)), $this); ?>
<html>
<head>
<!-- <?php echo smarty_function_headerTemplate(array('assets' => 'datetime-picker'), $this);?>
 -->



        
<?php echo '
<script type="text/javascript">
function vitalsFormSubmitted() {
    var invalid = "";

    var elementsToValidate = new Array();

    elementsToValidate[0] = new Array();
    elementsToValidate[0][0] = \'weight_input\';
    elementsToValidate[0][1] = '; ?>
<?php echo smarty_function_xlj(array('t' => 'Weight'), $this);?>
<?php echo ' + \' (\' + '; ?>
<?php echo smarty_function_xlj(array('t' => 'lbs'), $this);?>
<?php echo ' + \')\';

    elementsToValidate[1] = new Array();
    elementsToValidate[1][0] = \'weight_input_metric\';
    elementsToValidate[1][1] = '; ?>
<?php echo smarty_function_xlj(array('t' => 'Weight'), $this);?>
<?php echo ' + \' (\' + '; ?>
<?php echo smarty_function_xlj(array('t' => 'kg'), $this);?>
<?php echo ' + \')\';

    elementsToValidate[2] = new Array();
    elementsToValidate[2][0] = \'height_input\';
    elementsToValidate[2][1] = '; ?>
<?php echo smarty_function_xlj(array('t' => "Height/Length"), $this);?>
<?php echo ' + \' (\' + '; ?>
<?php echo smarty_function_xlj(array('t' => 'in'), $this);?>
<?php echo ' + \')\';

    elementsToValidate[3] = new Array();
    elementsToValidate[3][0] = \'height_input_metric\';
    elementsToValidate[3][1] = '; ?>
<?php echo smarty_function_xlj(array('t' => "Height/Length"), $this);?>
<?php echo ' + \' (\' + '; ?>
<?php echo smarty_function_xlj(array('t' => 'cm'), $this);?>
<?php echo ' + \')\';

    elementsToValidate[4] = new Array();
    elementsToValidate[4][0] = \'bps_input\';
    elementsToValidate[4][1] = '; ?>
<?php echo smarty_function_xlj(array('t' => 'BP Systolic'), $this);?>
<?php echo ';

    elementsToValidate[5] = new Array();
    elementsToValidate[5][0] = \'bpd_input\';
    elementsToValidate[5][1] = '; ?>
<?php echo smarty_function_xlj(array('t' => 'BP Diastolic'), $this);?>
<?php echo ';

    for (var i = 0; i < elementsToValidate.length; i++) {
        var current_elem_id = elementsToValidate[i][0];
        var tag_name = elementsToValidate[i][1];

        document.getElementById(current_elem_id).classList.remove(\'error\');

        if (isNaN(document.getElementById(current_elem_id).value)) {
            invalid += '; ?>
<?php echo smarty_function_xlj(array('t' => 'The following field has an invalid value'), $this);?>
<?php echo ' + ": " + tag_name + "\\n";
            document.getElementById(current_elem_id).className = document.getElementById(current_elem_id).className + " error";
            document.getElementById(current_elem_id).focus();
        }
    }

    $wt_m=$(\'#wt_measure\').val();
    $weightmatrix=$(\'#weight_input_metric\').val();
  
    if($wt_m==\'kg\'){
        convKgtoLb(\'weight_input\');
    }else{
        $(\'#weight_input\').val($weightmatrix);
    }

    $ht_measure=$(\'#ht_measure\').val();
    $heightmatrix=$(\'#height_input_metric\').val();
    if($ht_measure==\'cm\'){
        convCmtoIn(\'height_input\');
    }else{
        $(\'#height_input\').val($heightmatrix);
    }

   
    if (invalid.length > 0) {
        invalid += "\\n" + '; ?>
<?php echo smarty_function_xlj(array('t' => "Please correct the value(s) before proceeding!"), $this);?>
<?php echo ';
        alert(invalid);

        return false;
    } else {

        return top.restoreSession();
    }
}
</script>

'; ?>


<title><?php echo smarty_function_xlt(array('t' => 'Vitals'), $this);?>
</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
/public/assets/css/style.css">
    
        <link rel="stylesheet" href="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
/public/assets/css/employee_dashboard_style.css">
        <!-- <link rel="stylesheet" href="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
/public/assets/css/emp_info_css.css"> -->
    
        <script src="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
/public/assets/js/vue.js"></script>
    
        <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
        <script src="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
/public/assets/js/main.js"></script>
        <script src="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
/public/assets/js/addmore.js"></script>
        <script src="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
/public/assets/js/panzoom.min.js"></script>

        <style type="text/css" title="mystyles" media="all">
        </style>


</head>
<body>


            <section>
                <div class="body-content body-content2">
                    <div class="container-fluid pb-4 pt-4">
                        <window-dashboard title="Vitals" class="icon-hide">
                            <div class="head-component">
                                <div class="row">
                                    <div class="col-6"></div>
                                        <div class="col-6">
                                            <p class="text-white head-p"><?php echo smarty_function_xlt(array('t' => 'Vitals'), $this);?>
 </p>
                                        </div>
                                </div>
                            </div>
                           
                            <div class="body-compo">
                                <form name="vitals" id="vitals" method="post" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
/interface/forms/vitals/save.php" onSubmit="return vitalsFormSubmitted()">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['CSRF_TOKEN_FORM'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
">
                                    <div class="container-fluid pt-4 pb-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-right">
                                                    <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'Weight'), $this);?>
</p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <input type="text"  class="form-control" size='5' name='weight' id='weight_input_metric' value="<?php if ($this->_tpl_vars['vitals']->get_weight() != 0): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_weight())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
<?php endif; ?>"  title='<?php echo smarty_function_xla(array('t' => "Decimal pounds or pounds and ounces separated by #(e.g. 5#4)"), $this);?>
'>

                                                        <input type="hidden"  class="form-control" size='5' name='weight' id='weight_input' value="<?php if ($this->_tpl_vars['vitals']->get_weight() != 0): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_weight())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
<?php endif; ?>"  title='<?php echo smarty_function_xla(array('t' => "Decimal pounds or pounds and ounces separated by #(e.g. 5#4)"), $this);?>
'>
                                                    </div>
                                                    <div class="col-3">
                                                        <select name="" id="wt_measure" class="form-control mt-2">
                                                            <option value="lbs" ><?php echo smarty_function_xlt(array('t' => 'lbs'), $this);?>
</option>
                                                            <option value="kg" ><?php echo smarty_function_xlt(array('t' => 'kg'), $this);?>
</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>  
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-right">
                                                    <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'Height'), $this);?>
</p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <input type="text" class="form-control"  size='5' name='height' id='height_input_metric' value="<?php if ($this->_tpl_vars['vitals']->get_height() != 0): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_height())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
<?php endif; ?>" />

                                                        <input type="hidden" class="form-control"  size='5' name='height' id='height_input' value="<?php if ($this->_tpl_vars['vitals']->get_height() != 0): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_height())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
<?php endif; ?>" />
                                                    </div>
                                                    <div class="col-3">
                                                        <select name="" id="ht_measure" class="form-control mt-2">
                                                            <option value="in"><?php echo smarty_function_xlt(array('t' => 'in'), $this);?>
</option>
                                                            <option value="cm"><?php echo smarty_function_xlt(array('t' => 'cm'), $this);?>
</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> 
                                         <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-right">
                                                    <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'BP Systolic'), $this);?>
</p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-4">
                                                            <input type="text" class="form-control" size='5' name='bps' id='bps_input' value="<?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_bps())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
"/>
                                                    </div>
                                                    <div class="col-3">
                                                        <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'mmHg'), $this);?>
</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>  
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-right">
                                                    <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'BP Diastolic'), $this);?>
</p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <input type="text"  class="form-control" size='5' name='bpd' id='bpd_input' value="<?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_bpd())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
"/> 
                                                    </div>
                                                    <div class="col-3">
                                                        <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'mmHg'), $this);?>
</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> 
                                         <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-right">
                                                    <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'Pulse'), $this);?>
</p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <input type="text" size='5' class="form-control" name='pulse' id='pulse_input' value="<?php if ($this->_tpl_vars['vitals']->get_pulse() != 0): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['vitals']->get_pulse())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.0f") : smarty_modifier_string_format($_tmp, "%.0f")))) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
<?php endif; ?>"/>
                                                    </div>
                                                        
                                                    <div class="col-3">
                                                        <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'per min'), $this);?>
</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                         <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-right">
                                                    <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'Respiration'), $this);?>
</p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <input type="text" size='5' class="form-control" name='respiration' id='respiration_input' value="<?php if ($this->_tpl_vars['vitals']->get_respiration() != 0): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['vitals']->get_respiration())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.0f") : smarty_modifier_string_format($_tmp, "%.0f")))) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
<?php endif; ?>"/>
                                                    </div>
                                                    <div class="col-3">
                                                        <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'per min'), $this);?>
</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                             <!--  -->
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-right">
                                                    <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'Temperature'), $this);?>
</p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <input type="text"  class="form-control" size='5' name='temperature' id='temperature_input' value="<?php if ($this->_tpl_vars['vitals']->get_temperature() != 0): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_temperature())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
<?php endif; ?>" onChange="convFtoC('temperature_input');"/>
                                                    </div>
                                                    <div class="col-3">
                                                        <select name="" id="" class="form-control mt-2">
            
                                                            <option value="">f</option>
                                                            <option value="">c</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-right">
                                                    <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'Temperature Location'), $this);?>
</p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <input type="text" name="" class="form-control" id="">
                                                    </div>
                                                    <div class="col-3">
                                                        <select name="temp_method" id='temp_method' class="form-control mt-2">
                                                            <option value="Oral"              <?php if ($this->_tpl_vars['vitals']->get_temp_method() == 'Oral' || $this->_tpl_vars['vitals']->get_temp_method() == 2): ?> selected<?php endif; ?>><?php echo smarty_function_xlt(array('t' => 'Oral'), $this);?>

                                                            <option value="Tympanic Membrane" <?php if ($this->_tpl_vars['vitals']->get_temp_method() == 'Tympanic Membrane' || $this->_tpl_vars['vitals']->get_temp_method() == 1): ?> selected<?php endif; ?>><?php echo smarty_function_xlt(array('t' => 'Tympanic Membrane'), $this);?>

                                                            <option value="Rectal"            <?php if ($this->_tpl_vars['vitals']->get_temp_method() == 'Rectal' || $this->_tpl_vars['vitals']->get_temp_method() == 3): ?> selected<?php endif; ?>><?php echo smarty_function_xlt(array('t' => 'Rectal'), $this);?>

                                                            <option value="Axillary"          <?php if ($this->_tpl_vars['vitals']->get_temp_method() == 'Axillary' || $this->_tpl_vars['vitals']->get_temp_method() == 4): ?> selected<?php endif; ?>><?php echo smarty_function_xlt(array('t' => 'Axillary'), $this);?>

                                                            <option value="Temporal Artery"   <?php if ($this->_tpl_vars['vitals']->get_temp_method() == 'Temporal Artery'): ?> selected<?php endif; ?>><?php echo smarty_function_xlt(array('t' => 'Temporal Artery'), $this);?>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                    
                                        <div class="row">
                                            <div class="col-md-4">
                                                        <div class="text-right">
                                                            <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'Oxygen Saturation'), $this);?>
</p>
                                                        </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-4">
                                                            <input type="text" size='5'
                                                            name='oxygen_saturation' class="form-control" id='oxygen_saturation_input' value="<?php if ($this->_tpl_vars['vitals']->get_oxygen_saturation() != 0): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['vitals']->get_oxygen_saturation())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.0f") : smarty_modifier_string_format($_tmp, "%.0f")))) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
<?php endif; ?>"/>
                                                    </div>
                                                    <div class="col-3">
                                                        <p class="pt-3">%</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-right">
                                                    <p class="pt-3"><?php echo smarty_function_xlt(array('t' => 'BMI'), $this);?>
</p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-4">
                                                            <input type="text" size='5' class="form-control" name='BMI' id='BMI_input' value="<?php if ($this->_tpl_vars['vitals']->get_BMI() != 0): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['vitals']->get_BMI())) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 5) : substr($_tmp, 0, 5)))) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
<?php endif; ?>"/>
                                                    </div>
                                                    <div class="col-3">
            
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="id" id='id' value="<?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_id())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" />
                                        <input type="hidden" name="activity" id='activity' value="<?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_activity())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
">
                                        <input type="hidden" name="pid" id='pid' value="<?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_pid())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
">
                                        <input type="hidden" name="process" id='process' value="true">

                                        <div class="pt-4 pb-5">
                                            <button class="form-save"  name="Submit" id="submit" type="submit">Save</button>
                                        </div>
                                    </div>

                                </form>
                            </div>
                                  
                        </window-dashboard>
                    </div>
                </div>
            </section>

  
</body>

<script language="javascript">
var formdate = <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['vitals']->get_date())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y%m%d") : smarty_modifier_date_format($_tmp, "%Y%m%d")))) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
// vitals array elements are in the format:
//   date-height-weight-head_circumference
var vitals = new Array();
// get values from the current form elements
vitals[0] = formdate + '-' + <?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_height())) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
 + '-' + <?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_weight())) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
 + '-' + <?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_head_circ())) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
// historic values
<?php $_from = $this->_tpl_vars['results']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['result']):
?>
vitals[vitals.length] = <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['result']['date'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y%m%d") : smarty_modifier_date_format($_tmp, "%Y%m%d")))) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
 + '-' + <?php echo ((is_array($_tmp=$this->_tpl_vars['result']['height'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
 + '-' + <?php echo ((is_array($_tmp=$this->_tpl_vars['result']['weight'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
 + '-' + <?php echo ((is_array($_tmp=$this->_tpl_vars['result']['head_circ'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
<?php endforeach; endif; unset($_from); ?>
var patientAge= <?php echo ((is_array($_tmp=$this->_tpl_vars['patient_age'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
var patient_dob= <?php echo ((is_array($_tmp=$this->_tpl_vars['patient_dob'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
var webroot = <?php echo ((is_array($_tmp=$this->_tpl_vars['FORM_ACTION'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
var pid = <?php echo ((is_array($_tmp=$this->_tpl_vars['vitals']->get_pid())) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
var cancellink = <?php echo ((is_array($_tmp=$this->_tpl_vars['DONT_SAVE_LINK'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
var birth_xl= <?php echo smarty_function_xlj(array('t' => "Birth-24 months"), $this);?>
;
var older_xl= <?php echo smarty_function_xlj(array('t' => "2-20 years"), $this);?>
;
<?php echo '
function addGCSelector()
{
    var options=new Array();
    var birth={\'display\':birth_xl,\'param\':\'birth\'};
    var age2={\'display\':older_xl,\'param\':\'2-20\'}
    if((patientAge.toString().indexOf(\'24 month\')>=0) || (patientAge.toString().indexOf(\'month\')==-1))
        {
            var dob_data=patient_dob.split("-");
            var dob_date=new Date(dob_data[0],parseInt(dob_data[1])-1,dob_data[2]);
            options[0]=age2;
            for(var idx=0;idx<vitals.length;idx++)
                {
                    var str_data_date=vitals[idx].split("-")[0];
                    var data_date=new Date(str_data_date.substr(0,4),parseInt(str_data_date.substr(4,2))-1,str_data_date.substr(6,2));
                    if(((data_date-dob_date)/86400000)<=2*365)
                        {
                            idx=vitals.length;
                            options[1]=birth
                        }
                }
        }
        else
        {
            options[0]=birth;
        }
        var chart_buttons_cell=$("#pdfchart").parent("td");
        var select=$("<select id=\'chart_type\'></select>");
        chart_buttons_cell.prepend(select);
        for(idx=0;idx<options.length;idx++)
            {
                var option=$("<option value=\'"+options[idx].param+"\'>"+options[idx].display+"</option>");
                select.append(option);
            }
        select.find("option:first").attr("selected","true");
        if(options.length<2)
            {
                select.css("display","none");
            }
}

$(function(){
    $("#growthchart").on("click", function() { ShowGrowthchart(); });
    $("#pdfchart").on("click", function() { ShowGrowthchart(1); });
    $("#htmlchart").on("click", function() { ShowGrowthchart(2); });
    $("#cancel").on("click", function() { location.href=cancellink; });
    addGCSelector();

    $(\'.datetimepicker\').datetimepicker({
        '; ?>
<?php  $datetimepicker_timepicker = true;  ?>
        <?php  $datetimepicker_showseconds = false;  ?>
        <?php  $datetimepicker_formatInput = false;  ?>
        <?php  require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php');  ?>
        <?php  // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma  ?><?php echo '
    });

});

// $(\'#submit\').click(function(e){
// $( "form" ).submit(function(e){
//     e.preventDefault();
//     $wt_m=$(\'#wt_measure\').val();
//     $weightmatrix=$(\'#weight_input_metric\').val();
  
//     if($wt_m==\'kg\'){
//         convKgtoLb(\'weight_input\');
//     }else{
//         $(\'#weight_input\').val($weightmatrix);
//     }

//     $ht_measure=$(\'#ht_measure\').val();
//     $heightmatrix=$(\'#height_input_metric\').val();
//     if($ht_measure==\'cm\'){
//         convCmtoIn(\'height_input\');
//     }else{
//         $(\'#height_input\').val($heightmatrix);
//     }

//     // $("#vitals").submit();
//     $( "form" ).submit();
 
  
// })

function ShowGrowthchart(doPDF) {
    // get values from the current form elements
    '; ?>

    vitals[0] = formdate+'-'+$("#height_input").val()+'-'+$("#weight_input").val()+'-'+$("#head_circ_input").val();
    <?php echo '
    // build the data string
    var datastring = "";
    for(var i=0; i<vitals.length; i++) {
        datastring += vitals[i]+"~";
    }
    newURL = webroot + \'/interface/forms/vitals/growthchart/chart.php?pid=\' + encodeURIComponent(pid) + \'&data=\' + encodeURIComponent(datastring);
    if (doPDF == 1) newURL += "&pdf=1";
    if (doPDF == 2) newURL += "&html=1";
    newURL += "&chart_type=" + encodeURIComponent($("#chart_type").val()) + "&csrf_token_form=" + '; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['CSRF_TOKEN_FORM'])) ? $this->_run_mod_handler('js_url', true, $_tmp) : js_url($_tmp)); ?>
<?php echo ';
    // do the new window stuff
    top.restoreSession();
    window.open(newURL, \'_blank\', "menubar=1,toolbar=1,scrollbars=1,resizable=1,width=600,height=450");
}

function convLbtoKg(name) {
    var lb = $("#"+name).val();
    var hash_loc=lb.indexOf("#");
    if(hash_loc>=0)
    {
        var pounds=lb.substr(0,hash_loc);
        var ounces=lb.substr(hash_loc+1);
        var num=parseInt(pounds)+parseInt(ounces)/16;
        lb=num;
        $("#"+name).val(lb);
    }
    if (lb == "0") {
        $("#"+name+"_metric").val("0");
    }
    else if (lb == parseFloat(lb)) {
    kg = lb*0.45359237;
        kg = kg.toFixed(2);
        $("#"+name+"_metric").val(kg);
    }
    else {
        $("#"+name+"_metric").val("");
    }

    if (name == "weight_input") {
        calculateBMI();
    }
}

function convKgtoLb(name) {
    var kg = $("#"+name+"_metric").val();

    if (kg == "0") {
        $("#"+name).val("0");
    }
    else if (kg == parseFloat(kg)) {
        lb = kg/0.45359237;
        lb = lb.toFixed(2);
        $("#"+name).val(lb);
    }
    else {
        $("#"+name).val("");
    }

    if (name == "weight_input") {
        calculateBMI();
    }
}

function convIntoCm(name) {
    var inch = $("#"+name).val();

    if (inch == "0") {
        $("#"+name+"_metric").val("0");
    }
    else if (inch == parseFloat(inch)) {
        cm = inch*2.54;
        cm = cm.toFixed(2);
        $("#"+name+"_metric").val(cm);
    }
    else {
        $("#"+name+"_metric").val("");
    }

    if (name == "height_input") {
        calculateBMI();
    }
}

function convCmtoIn(name) {
    var cm = $("#"+name+"_metric").val();

    if (cm == "0") {
        $("#"+name).val("0");
    }
    else if (cm == parseFloat(cm)) {
        inch = cm/2.54;
        inch = inch.toFixed(2);
        $("#"+name).val(inch);
    }
    else {
        $("#"+name).val("");
    }

    if (name == "height_input") {
        calculateBMI();
    }
}

function convFtoC(name) {
    var Fdeg = $("#"+name).val();
    if (Fdeg == "0") {
        $("#"+name+"_metric").val("0");
    }
    else if (Fdeg == parseFloat(Fdeg)) {
        Cdeg = (Fdeg-32)*0.5556;
        Cdeg = Cdeg.toFixed(2);
        $("#"+name+"_metric").val(Cdeg);
    }
    else {
        $("#"+name+"_metric").val("");
    }
}

function convCtoF(name) {
    var Cdeg = $("#"+name+"_metric").val();
    if (Cdeg == "0") {
        $("#"+name).val("0");
    }
    else if (Cdeg == parseFloat(Cdeg)) {
        Fdeg = (Cdeg/0.5556)+32;
        Fdeg = Fdeg.toFixed(2);
        $("#"+name).val(Fdeg);
    }
    else {
        $("#"+name).val("");
    }
}

function calculateBMI() {
    var bmi = 0;
    var height = $("#height_input").val();
    var weight = $("#weight_input").val();
    if(height == 0 || weight == 0) {
        $("#BMI").val("");
    }
    else if((height == parseFloat(height)) && (weight == parseFloat(weight))) {
        bmi = weight/height/height*703;
        bmi = bmi.toFixed(1);
        $("#BMI_input").val(bmi);
    }
    else {
        $("#BMI_input").val("");
    }
}

</script>
'; ?>


</html>