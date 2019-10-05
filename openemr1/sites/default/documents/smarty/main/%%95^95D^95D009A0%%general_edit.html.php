<?php /* Smarty version 2.6.31, created on 2019-10-05 13:29:07
         compiled from D:/xampp/htdocs/openemr_test/templates/insurance_companies/general_edit.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'text', 'D:/xampp/htdocs/openemr_test/templates/insurance_companies/general_edit.html', 18, false),array('modifier', 'upper', 'D:/xampp/htdocs/openemr_test/templates/insurance_companies/general_edit.html', 19, false),array('modifier', 'attr', 'D:/xampp/htdocs/openemr_test/templates/insurance_companies/general_edit.html', 20, false),array('modifier', 'attr_url', 'D:/xampp/htdocs/openemr_test/templates/insurance_companies/general_edit.html', 22, false),array('function', 'xlt', 'D:/xampp/htdocs/openemr_test/templates/insurance_companies/general_edit.html', 30, false),array('function', 'html_options', 'D:/xampp/htdocs/openemr_test/templates/insurance_companies/general_edit.html', 156, false),)), $this); ?>

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
														<?php $_from = $this->_tpl_vars['icompanies_all']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['insurancecompany_all']):
?>
														<tr  class="tablerow bodypart15">
															<td><?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany_all']->name)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</td>
															<td><?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany_all']->address->city)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['insurancecompany_all']->address->state)) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)))) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
&nbsp;</td>
															<td><?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany_all']->get_phone())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
</td>
															<td><?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany_all']->get_phone())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
</td>
															<td><a href="#" id="insurance_edit" onclick='insurance_edit("<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany_all']->id)) ? $this->_run_mod_handler('attr_url', true, $_tmp) : attr_url($_tmp)); ?>
");' insid='<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany_all']->id)) ? $this->_run_mod_handler('attr_url', true, $_tmp) : attr_url($_tmp)); ?>
'>
																	<img src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/img/edit-text.svg" alt="" class="xxx pr-2">
																</a>
																<img src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/img/delete.svg" class="remove15" alt="" ph_id="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany_all']->id)) ? $this->_run_mod_handler('attr_url', true, $_tmp) : attr_url($_tmp)); ?>
" url="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/templates/delete_details.php">
															</td>
														</tr>
														<?php endforeach; else: ?>
														<tr>
															<td colspan="4"><?php echo smarty_function_xlt(array('t' => 'No Insurance Companies Found'), $this);?>
</td>
														</tr>
														<?php endif; unset($_from); ?>
													</tbody>
										</table>
									</div>
									
									
								</div>
								<form name="insurancecompany" method="post" action="<?php echo $this->_tpl_vars['FORM_ACTION_ADD']; ?>
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
"  onKeyDown="PreventIt(event)">

											<!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
										</div>


									</div>
									<div class="row pt-3">
										<div class="col-md-12">
											<p>Address</p>
											<textarea id="" id="address_line1" name="address_line1"   onKeyDown="PreventIt(event)"  class="form-control pt-3" rows="3"><?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->line1)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
</textarea>
										</div>


									</div>
									<div class="row pt-4">
										<div class="col-md-4">
											<p>City</p>
											<input type="text" id="city" name="city" class="form-control pr-1 pl-1"  value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->city)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
"  onKeyDown="PreventIt(event)">
										</div>
										<div class="col-md-4">
											<p>State</p>
											<!-- <input type="text" maxlength="2" id="state" name="state" class="form-control"  onKeyDown="PreventIt(event)"> -->
											
											<select id="state" name="state" class="form-control mt-2">
													<?php echo $this->_tpl_vars['insurancecompany']->state_list($this->_tpl_vars['insurancecompany']->address->state); ?>

											</select>
										</div>
										<div class="col-md-4">
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
											<input type="text" placeholder="" class="form-control pr-1 pl-1"  id="email" name="email" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_email())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">
										</div>
										<div class="col-md-4">
											<p>Phone</p>
											<input type="text" id="phone" name="phone" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_phone())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">

										</div>
										<div class="col-md-4">
											<p>Fax</p>
											<input type="text"  id="fax" name="fax" placeholder="" class="form-control pr-1 pl-1" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_fax())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)">
										</div>

									</div>
									<div class="row pt-4">
										<div class="col-md-3">
											<p>Payer ID</p>
											<input type="text" id="cms_id" name="cms_id" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_cms_id())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
"  onKeyDown="PreventIt(event)">

											<!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
										</div>
										<?php if ($this->_tpl_vars['SUPPORT_ENCOUNTER_CLAIMS']): ?>
											<div class="form-group">
												<label for="alt_cms_id" class="control-label col-sm-2"><?php echo smarty_function_xlt(array('t' => 'Payer ID For Encounter Claims'), $this);?>
</label>
												<div class="col-sm-8">
													<input type="text" id="alt_cms_id" name="alt_cms_id" class="form-control"  onKeyDown="PreventIt(event)">
												</div>
											</div>
										<?php endif; ?>
										<?php if ($this->_tpl_vars['SUPPORT_ELIGIBILITY_REQUESTS']): ?>
										<div class="form-group">
											<label for="eligibility_id" class="control-label col-sm-2"><?php echo smarty_function_xlt(array('t' => 'Payer Id For Eligibility'), $this);?>
</label>
											<div class="col-sm-8">
												<input type="text" id="eligibility_id" name="eligibility_id" class="form-control"  onKeyDown="PreventIt(event)">
											</div>
										</div>
										<?php endif; ?>
										<div class="col-md-3">
											<p>Payer Type</p>
											<select id="ins_type_code" name="ins_type_code" class="form-control">
													<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['insurancecompany']->ins_type_code_array,'selected' => $this->_tpl_vars['insurancecompany']->get_ins_type_code()), $this);?>

												</select>
											
										</div>
										<div class="col-md-3">
											<p>ZX12 Partnerip</p>
											<select id="x12_default_partner_id" name="x12_default_partner_id" class="form-control">
													<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['x12_partners'],'selected' => $this->_tpl_vars['insurancecompany']->get_x12_default_partner_id()), $this);?>

											</select>
										
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
											<textarea id="note" name="note" class="form-control pt-3" rows="3"><?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_note())) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
</textarea>
										</div>


									</div>

                                    <input type="hidden" name="id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->id)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" />
                                    <input type="hidden" name="process" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['PROCESS'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" />

									<div class="pt-4 pb-2"><button class="form-save" href="javascript:submit_insurancecompany();"  onclick="top.restoreSession()">Save</button></div>
                                </form>

                              
                            
                            <?php echo '
                            <script language="javascript">
                                function submit_insurancecompany() {
                                    if(document.insurancecompany.name.value.length>0) {
                                        top.restoreSession();
                                        document.insurancecompany.submit();
                                        //Z&H Removed redirection
                                    } else{
                                        document.insurancecompany.name.style.backgroundColor="red";
                                        document.insurancecompany.name.focus();
                                    }
                                }
                            
                                function jsWaitForDelay(delay) {
                                    var startTime = new Date();
                                    var endTime = null;
                                    do {
                                        endTime = new Date();
                                    } while ((endTime - startTime) < delay);
                                }
							
							
							$("body").on("click", ".remove15", function() {
								thisss=$(this);
						
								if(confirm("Are You Sure want to delete?")){
								   $fid= $(this).attr(\'ph_id\');
								   $url=$(this).attr(\'url\');
								 
								   $.post($url,
									{
										mode:"insurance_delete",
										action: "Delete",
										id:  $fid,
									},
									function(data, status){
									
										thisss.closest(".bodypart15").remove();
									});
								   
								}
							  
								
							});
						
						</script>

                            '; ?>