<?php /* Smarty version 2.6.31, created on 2019-10-05 13:22:26
         compiled from D:/xampp/htdocs/openemr_test/templates/pharmacies/general_edit.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xlt', 'D:/xampp/htdocs/openemr_test/templates/pharmacies/general_edit.html', 93, false),array('function', 'html_options', 'D:/xampp/htdocs/openemr_test/templates/pharmacies/general_edit.html', 448, false),array('modifier', 'text', 'D:/xampp/htdocs/openemr_test/templates/pharmacies/general_edit.html', 125, false),array('modifier', 'upper', 'D:/xampp/htdocs/openemr_test/templates/pharmacies/general_edit.html', 129, false),array('modifier', 'attr_url', 'D:/xampp/htdocs/openemr_test/templates/pharmacies/general_edit.html', 132, false),array('modifier', 'attr', 'D:/xampp/htdocs/openemr_test/templates/pharmacies/general_edit.html', 155, false),)), $this); ?>
 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
 <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/css/style.css">

 <script src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/js/vue.js"></script>

 <?php echo '
<script language="javascript">

function submit_pharmacy()
{
    if(document.pharmacy.name.value.length>0)
    {
        top.restoreSession();
        document.pharmacy.submit();
        //Z&H Removed redirection
    }
    else
    {
        document.pharmacy.name.style.backgroundColor="red";
        document.pharmacy.name.focus();
    }
}

 function Waittoredirect(delaymsec) {
     var st = new Date();
     var et = null;
     do {
     et = new Date();
     } while ((et - st) < delaymsec);
 }



 function tabToggle(){
       
    $("#ins_list_data").load("controller.php?practice_settings&insurance_company&action=list"); 
    $(\'#cleardata\').empty();
    }

    function insurance_edit($insid){
	
    $("#ins_list_data").load("controller.php?practice_settings&insurance_company&action=edit&id="+$insid);
}

$("body").on("click", ".remove14", function() {
    alert();
        thisss=$(this);

        if(confirm("Are You Sure want to delete?")){
           $fid= $(this).attr(\'ph_id\');
		   $url=$(this).attr(\'url\');
         
           $.post($url,
            {
                mode:"pharmacy_delete",
                action: "Delete",
                id:  $fid,
            },
            function(data, status){
			
                thisss.closest(".bodypart14").remove();
            });
           
        }
      
        
    });

</script>
'; ?>



 <section>
    <div class="body-content body-content2">
        <div class="container-fluid pb-4 pt-4">
            <window-dashboard  class="icon-hide">
                <div class="head-component">
                    <div class="row">
                        <div class="col-6"></div>
                            <div class="col-6">
                                <p class="text-white head-p"><?php echo smarty_function_xlt(array('t' => 'Pharmacies and Insurance'), $this);?>
 </p>
                            </div>
                    </div>
                </div>
               
                <div class="body-compo">
                    <div class="container-fluid">
                        <ul class="nav  nav-justified compo-info" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#home">Pharmacy</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#menu1" id="menu_insurance" onclick="tabToggle();">Insurance</a>
                            </li>
    
                        </ul>
                        <div class="tab-content">
                            <div id="home" class="container tab-pane active">
                                <div class="pt-4 pb-4">
                                    <div class="">
                                        <table class="table table-form">
                                            <thead id="TextBoxContainer14" class="repeat-row ">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Address</th>
                                                    <th>Phone</th>
                                                    <th></th>
                                                </tr>
                                              </thead>
                                              <tbody>
                                                <?php $_from = $this->_tpl_vars['pharmacies_all']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['pharmacy_all']):
?>
                                                <tr class="tablerow bodypart14">
                                                    <td><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy_all']->name)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</td>
                                                    <td>
                                                        <?php if ($this->_tpl_vars['pharmacy_all']->address->line1 != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy_all']->address->line1)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
, <?php endif; ?>
                                                        <?php if ($this->_tpl_vars['pharmacy_all']->address->city != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy_all']->address->city)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
, <?php endif; ?>
                                                        <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['pharmacy_all']->address->state)) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)))) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy_all']->address->zip)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
&nbsp;
                                                    </td>
                                                    <td><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy_all']->get_phone())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
&nbsp;</td>
                                                    <td><a href="<?php echo $this->_tpl_vars['CURRENT_ACTION']; ?>
action=edit&id=<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy_all']->id)) ? $this->_run_mod_handler('attr_url', true, $_tmp) : attr_url($_tmp)); ?>
" onclick="top.restoreSession()">
                                                            <img src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/img/edit-text.svg" alt="" class="xxx pr-2">
                                                        </a>
                                                        <img src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/img/delete.svg" class="remove14" ph_id="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->id)) ? $this->_run_mod_handler('attr_url', true, $_tmp) : attr_url($_tmp)); ?>
" url="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/templates/delete_details.php" alt=""></td>
                                                    <?php endforeach; else: ?>
                                               
                                                </tr>
                                            
                                                <tr>
                                                    <td colspan="3"><b><?php echo smarty_function_xlt(array('t' => 'No Pharmacies Found'), $this);?>
<b></td>
                                                </tr>
                                                <?php endif; unset($_from); ?>
                                                </tbody>

                                            
                                        </table>
                                    </div>
                                   
                                    <div>

                                    </div>
                                </div>
                                <form name="pharmacy" method="post" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
"  class='form-horizontal' onsubmit="return top.restoreSession()">
                                        <input type="hidden" name="form_id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->id)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" />

                                    <!-- <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" value="">Deactivate
                                                </label>
                                            </div>
                                        </div> -->


                                    <!-- </div> -->
                                    <div class="row pt-3">
                                        <div class="col-md-12">
                                            <p>Name</p>
                                            <input type="text" id="name" name="name" class="form-control pr-1 pl-1" aria-describedby="nameHelpBox"  value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->name)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
"   onKeyDown="PreventIt(event)">
                                        </div>


                                    </div>
                                    <div class="row pt-3">
                                        <div class="col-md-12">
                                            <p>Address</p>
                                            <textarea id="" class="form-control pt-3" rows="3" id="address_line1" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->line1)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" name="address_line1" class="form-control" onKeyDown="PreventIt(event)"><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->line1)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
</textarea>
                                        </div>


                                    </div>
                                    <div class="row pt-4">
                                        <div class="col-md-4">
                                            <p>City</p>
                                            <input type="text" id="city" name="city" class="form-control  pr-1 pl-1" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->city)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
"  onKeyDown="PreventIt(event)">

                                           
                                        </div>
                                        <div class="col-md-4">
                                            <p>State</p>
                                            <select id="state" name="state" class="form-control mt-2">
                                                    <?php echo $this->_tpl_vars['pharmacy']->state_list($this->_tpl_vars['pharmacy']->address->state); ?>

                                                   

                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <p>Zip</p>
                                            <input type="text" id="zip" name="zip"  value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->zip)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" class="form-control pr-1 pl-1" onKeyDown="PreventIt(event)">

                                        </div>
                                        <!-- <div class="col-md-3">
                                            <p>E-Mail</p>
                                            <input type="text" id="email" name="email" class="form-control pr-1 pl-1" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->email)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">
                                        </div> -->

                                    </div>
                                    <div class="row pt-4">
                                        <div class="col-md-4">
                                            <p>Email</p>
                                            <input type="text" id="email" name="email" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->email)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" class="form-control pr-1 pl-1" onKeyDown="PreventIt(event)">

                                            <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                        </div> 
                                        <div class="col-md-4">
                                            <p>Phone</p>
                                            <input type="text" id="phone" name="phone" class="form-control pr-1 pl-1" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->get_phone())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
"  onKeyDown="PreventIt(event)">

                                        </div>
                                        <div class="col-md-4">
                                            <p>Fax</p>
                                            <input type="text" placeholder=""  id="faxno" name="faxno" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->faxno)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)" class="form-control pr-1 pl-1">
                                        </div>

                                    </div> 
                                    <div class="row pt-4">
                                        <div class="col-md-4">
                                            <p>NPI</p>
                                                <input type="text" placeholder="" id="npi" name="npi" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->get_npi())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)" class="form-control pr-1 pl-1">
                                        </div>
                                        <div class="col-md-4">
                                            <p>NCPDP</p>
                                                <input type="text" placeholder="" id="ncpdp" name="ncpdp" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->get_ncpdp())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)" class="form-control pr-1 pl-1">
                                        </div>
    
    
                                    </div>
                                    <div class="row pt-3">
                                        <div class="col-md-12">
                                            <p>Notes</p>
                                            <textarea id="note" name="note" class="form-control pt-3" rows="3"><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->get_note())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
</textarea>
                                        </div>


                                    </div>

                                    <input type="hidden" name="id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->id)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
">
                                    <input type="hidden" name="process" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['PROCESS'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
">
                                    
                                    <div class="pt-4 pb-2"><button class="form-save"  href="javascript:submit_pharmacy();" onclick="top.restoreSession()">Save</button></div>
                                </form>
                            </div>
                            <div id="menu1" class="container tab-pane fade">
                                <div id="ins_list_data"></div>
                                <div id="cleardata">
                                    <div class="pt-4 pb-4">
                                        <div class="">
                                            <table class="table table-form">
                                                <thead id="TextBoxContainer15" class="repeat-row ">
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Billing Address</th>
                                                        <th>Mailing Address</th>
                                                        <th>Phone</th>

                                                        <th></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                            <?php $_from = $this->_tpl_vars['icompanies']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['insurancecompany']):
?>
                                                            <tr>
                                                                <td><?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->name)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</td>
                                                                <td><?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->city)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->state)) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)))) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
&nbsp;</td>
                                                                <td><?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_x12_default_partner_name())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
&nbsp;</td>
                                                                <td><?php if ($this->_tpl_vars['insurancecompany']->get_inactive() == 1): ?><?php echo smarty_function_xlt(array('t' => 'Yes'), $this);?>
<?php endif; ?>&nbsp;</td>
                                                                <td><a href="<?php echo $this->_tpl_vars['CURRENT_ACTION']; ?>
action=edit&id=<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->id)) ? $this->_run_mod_handler('attr_url', true, $_tmp) : attr_url($_tmp)); ?>
" onclick="top.restoreSession()">
                                                                        <img src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/img/edit-text.svg" alt="" class="xxx pr-2">
                                                                    </a>
                                                                    <img src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/img/delete.svg" class="remove15" alt="">
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; else: ?>
                                                            <tr>
                                                                <td colspan="4"><?php echo smarty_function_xlt(array('t' => 'No Insurance Companies Found'), $this);?>
</td>
                                                            </tr>
                                                            <?php endif; unset($_from); ?>
                                                        </tbody>
                                                    <!-- <tr class="tablerow bodypart15">
                                                        <td><input type="text" class="form-control active-text1" value="imaging"></td>
                                                        <td><input type="text" class="form-control active-text1" value="ICD456"></td>
                                                        <td><input type="text" class="form-control active-text1" value="WH"></td>
                                                        <td><input type="text" class="form-control active-text1" value="imaging"></td>


                                                        <td>
                                                            <img src="img/edit-text.svg" alt="" class="xxx pr-2"><img src="img/delete.svg" class="remove15" alt=""></td>

                                                    </tr> -->


                                                </tbody>
                                            </table>
                                        </div>
                                        <div>
                                            <div class="text-center">
                                                <img src="img/addmore.svg" id="insertrow5" alt="">
                                            </div>
                                            <div class="text-center">
                                                <p class="fs-14">Add New</p>
                                            </div>
                                        </div>
                                        <div>

                                        </div>
                                    </div>


                                    <form name="insurancecompany" method="post" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
" class='form-horizontal' onsubmit="return top.restoreSession()">
                                        <input type="hidden" name="form_id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->id)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
">
                                        <?php if ($this->_tpl_vars['insurancecompany']->get_inactive() == 1): ?>
                                        <div class="form-group">
                                            <label for="inactive" class="control-label col-sm-2"><?php echo smarty_function_xlt(array('t' => 'Reactivate'), $this);?>
</label>
                                            <div class="col-sm-8">
                                                <input type="checkbox" id="inactive" name="inactive" class="checkbox" value="0" />
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($this->_tpl_vars['insurancecompany']->get_inactive() == 0): ?>
                                        <div class="form-group">
                                            <label for="inactive" class="control-label col-sm-2"><?php echo smarty_function_xlt(array('t' => 'Deactivate'), $this);?>
</label>
                                            <div class="col-sm-8">
                                                <input type="checkbox" id="inactive" name="inactive" class="checkbox" value="1" />
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p>Name</p>
                                                <input type="text" id="name" name="name" class="form-control pr-1 pl-1" aria-describedby="nameHelpBox" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_name())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">

                                                <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                            </div>


                                        </div>
                                        <div class="row pt-3">
                                            <div class="col-md-12">
                                                <p>Attn.</p>
                                                <input type="text" id="attn" name="attn" class="form-control pr-1 pl-1" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_attn())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">

                                                <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                            </div>


                                        </div>
                                        <div class="row pt-3">
                                            <div class="col-md-12">
                                                <p>Address</p>
                                                <!-- <input type="text" id="address_line1" name="address_line1" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->line1)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)"> -->

                                                <textarea id="" id="address_line1" name="address_line1"  value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->line1)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)" class="form-control pt-3" rows="3"><?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->line1)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
</textarea>
                                            </div>


                                        </div>
                                        <div class="row pt-4">
                                            <div class="col-md-3">
                                                <p>City</p>
                                                <input type="text" id="city" name="city" class="form-control pr-1 pl-1" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->city)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">

                                                <!-- <select name="" id="" class="form-control mt-2">
                                                    <option value="">value</option> 
                                                    <option value="">value</option>
                                                    <option value="">value</option>
                                                </select> -->
                                            </div>
                                            <div class="col-md-3">
                                                <p>State</p>
                                                <input type="text" maxlength="2" id="state" name="state" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->state)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">

                                                <!-- <select name="" id="" class="form-control mt-2">
                                                    <option value="">value</option> 
                                                    <option value="">value</option>
                                                    <option value="">value</option>
                                                </select> -->
                                            </div>
                                            <div class="col-md-3">
                                                <p>Zip</p>
                                                <input type="text" id="zip" name="zip" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->zip)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">

                                                <!-- <select name="" id="" class="form-control mt-2">
                                                    <option value="">value</option> 
                                                    <option value="">value</option>
                                                    <option value="">value</option>
                                                </select> -->
                                            </div>
                                            <!-- <div class="col-md-3">
                                                <p>E-Mail</p>
                                                <input type="text" placeholder="" class="form-control pr-1 pl-1">
                                            </div> -->

                                        </div>
                                        <div class="row pt-4">
                                            <div class="col-md-4">
                                                <p>Email</p>
                                                <input type="text" placeholder="" class="form-control pr-1 pl-1">
                                            </div>
                                            <div class="col-md-4">
                                                <p>Phone</p>
                                                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_phone())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">

                                                <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                            </div>
                                            <div class="col-md-4">
                                                <p>Fax</p>
                                                <input type="text" placeholder="" class="form-control pr-1 pl-1">
                                            </div>

                                        </div>
                                        <div class="row pt-4">
                                            <div class="col-md-3">
                                                <p>Payer ID</p>
                                                <input type="text" id="cms_id" name="cms_id" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_cms_id())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">

                                                <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
                                            </div>
                                            <?php if ($this->_tpl_vars['SUPPORT_ENCOUNTER_CLAIMS']): ?>
                                                <div class="form-group">
                                                    <label for="alt_cms_id" class="control-label col-sm-2"><?php echo smarty_function_xlt(array('t' => 'Payer ID For Encounter Claims'), $this);?>
</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" id="alt_cms_id" name="alt_cms_id" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_alt_cms_id())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($this->_tpl_vars['SUPPORT_ELIGIBILITY_REQUESTS']): ?>
                                            <div class="form-group">
                                                <label for="eligibility_id" class="control-label col-sm-2"><?php echo smarty_function_xlt(array('t' => 'Payer Id For Eligibility'), $this);?>
</label>
                                                <div class="col-sm-8">
                                                    <input type="text" id="eligibility_id" name="eligibility_id" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_eligibility_id())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <div class="col-md-3">
                                                <p>Payer Type</p>
                                                <select id="ins_type_code" name="ins_type_code" class="form-control">
                                                        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['insurancecompany']->ins_type_code_array,'selected' => $this->_tpl_vars['insurancecompany']->get_ins_type_code()), $this);?>

                                                    </select>
                                                <!-- <select name="" id="" class="form-control mt-2">
                                                    <option value="">value</option> 
                                                    <option value="">value</option>
                                                    <option value="">value</option>
                                                </select> -->
                                            </div>
                                            <div class="col-md-3">
                                                <p>ZX12 Partnerip</p>
                                                <select id="x12_default_partner_id" name="x12_default_partner_id" class="form-control">
                                                        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['x12_partners'],'selected' => $this->_tpl_vars['insurancecompany']->get_x12_default_partner_id()), $this);?>

                                                </select>
                                                <!-- <select name="" id="" class="form-control mt-2">
                                                    <option value="">value</option> 
                                                    <option value="">value</option>
                                                    <option value="">value</option>
                                                </select> -->
                                            </div>
                                            <?php if ($this->_tpl_vars['SUPPORT_ELIGIBILITY_REQUESTS']): ?>
                                            <div class="form-group">
                                                <label for="x12_default_eligibility_id" class="control-label col-sm-2"><?php echo smarty_function_xlt(array('t' => 'Default Eligibility X12 Partner'), $this);?>
</label>
                                                <div class="col-sm-8">
                                                    <select id="x12_default_eligibility_id" name="x12_default_eligibility_id" class="form-control">
                                                        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['x12_partners'],'selected' => $this->_tpl_vars['insurancecompany']->get_x12_default_eligibility_id()), $this);?>

                                                    </select>
                                                </div>
                                            </div>
                                            <?php endif; ?>


                                        </div>
                                        <div class="row pt-3">
                                            <div class="col-md-12">
                                                <p>Notes</p>
                                                <textarea id="" class="form-control pt-3" rows="3"></textarea>
                                            </div>


                                        </div>

                                        <input type="hidden" name="id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->id)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" />
                                        <input type="hidden" name="process" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['PROCESS'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" />

                                        <div class="pt-4 pb-2"><button class="form-save" href="javascript:submit_insurancecompany();"  onclick="top.restoreSession()">Save</button></div>
                                    </form>
                                </div>
                            </div>

                        </div>
                      
                    </div>
                </div>
            </window-dashboard>
        </div>
    </div>
</section>


<?php echo '
<script language="javascript">
// function submit_pharmacy()
// {
//     if(document.pharmacy.name.value.length>0)
//     {
//         top.restoreSession();
//         document.pharmacy.submit();
//         //Z&H Removed redirection
//     }
//     else
//     {
//         document.pharmacy.name.style.backgroundColor="red";
//         document.pharmacy.name.focus();
//     }
// }

//  function Waittoredirect(delaymsec) {
//      var st = new Date();
//      var et = null;
//      do {
//      et = new Date();
//      } while ((et - st) < delaymsec);
//  }


//  $("#menu_insurance").click(function() {

// 	alert();
//     $("#ins_list_data").load("controller.php?practice_settings&insurance_company&action=list");

//     });

//    function tabToggle(){
//         alert();
//     $("#ins_list_data").load("controller.php?practice_settings&insurance_company&action=list"); 
//     }

    // $(\'#insurance_edit\').click(function(e) {
    // e.preventDefault();

        
    // $("#ins_list_data").load("controller.php?practice_settings&insurance_company&action=list");

    // });

    // function insurance_edit($insid){

    // $("#ins_list_data").load("controller.php?practice_settings&insurance_company&action=edit&id="+$insid);
    // }
</script>
'; ?>
