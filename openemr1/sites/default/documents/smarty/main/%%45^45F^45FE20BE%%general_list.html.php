<?php /* Smarty version 2.6.31, created on 2019-10-05 14:03:36
         compiled from D:/xampp/htdocs/openemr_test/templates/pharmacies/general_list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xlt', 'D:/xampp/htdocs/openemr_test/templates/pharmacies/general_list.html', 2, false),array('modifier', 'attr_url', 'D:/xampp/htdocs/openemr_test/templates/pharmacies/general_list.html', 17, false),array('modifier', 'text', 'D:/xampp/htdocs/openemr_test/templates/pharmacies/general_list.html', 18, false),array('modifier', 'upper', 'D:/xampp/htdocs/openemr_test/templates/pharmacies/general_list.html', 24, false),array('modifier', 'attr', 'D:/xampp/htdocs/openemr_test/templates/pharmacies/general_list.html', 145, false),)), $this); ?>
<!-- <a href="controller.php?practice_settings&<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
pharmacy&action=edit" onclick="top.restoreSession()" class="btn btn-default btn-add" > -->
<!-- <span><?php echo smarty_function_xlt(array('t' => 'Add a Pharmacy'), $this);?>
</span> -->
<!-- </a><br><br> -->

<!-- <table class="table table-responsive table-striped">
	<thead>
        <tr>
            <th><?php echo smarty_function_xlt(array('t' => 'Name'), $this);?>
</th>
            <th><?php echo smarty_function_xlt(array('t' => 'Address'), $this);?>
</th>
            <th><?php echo smarty_function_xlt(array('t' => 'Default Method'), $this);?>
</th>
        </tr>
    </thead>
    <tbody>
	<?php $_from = $this->_tpl_vars['pharmacies']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['pharmacy']):
?>
	<tr>
		<td>
		    <a href="<?php echo $this->_tpl_vars['CURRENT_ACTION']; ?>
action=edit&id=<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->id)) ? $this->_run_mod_handler('attr_url', true, $_tmp) : attr_url($_tmp)); ?>
" onclick="top.restoreSession()">
		        <?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->name)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>

		    </a>
		</td>
		<td>
		<?php if ($this->_tpl_vars['pharmacy']->address->line1 != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->line1)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
, <?php endif; ?>
		<?php if ($this->_tpl_vars['pharmacy']->address->city != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->city)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
, <?php endif; ?>
			<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->state)) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)))) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->zip)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
&nbsp;</td>
		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->get_transmit_method_display())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
&nbsp;
	<?php endforeach; else: ?></td>
	</tr>

	<tr>
		<td colspan="3"><b><?php echo smarty_function_xlt(array('t' => 'No Pharmacies Found'), $this);?>
<b></td>
	</tr>
	<?php endif; unset($_from); ?>
    </tbody>
</table> -->

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/css/style.css">

    <!-- <link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/css/employee_dashboard_style.css"> -->
    <!-- <link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/css/emp_info_css.css"> -->

    <script src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/js/vue.js"></script>

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
								<a class="nav-link" data-toggle="tab" id="menu_insurance" href="#menu1">Insurance</a>
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
												<?php $_from = $this->_tpl_vars['pharmacies']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['pharmacy']):
?>
												<tr class="tablerow bodypart14">
													<td><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->name)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</td>
													
													<td>
														<?php if ($this->_tpl_vars['pharmacy']->address->line1 != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->line1)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
, <?php endif; ?>
														<?php if ($this->_tpl_vars['pharmacy']->address->city != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->city)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
, <?php endif; ?>
														<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->state)) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)))) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->zip)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
&nbsp;
													</td>
													<td><?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->get_phone())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
&nbsp;</td>
													<td><a href="<?php echo $this->_tpl_vars['CURRENT_ACTION']; ?>
action=edit&id=<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->id)) ? $this->_run_mod_handler('attr_url', true, $_tmp) : attr_url($_tmp)); ?>
" onclick="top.restoreSession()">
															<img src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/img/edit-text.svg" alt="" class="xxx pr-2">
														</a>
														<img src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/img/delete.svg" class="remove14" alt="" ph_id="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->id)) ? $this->_run_mod_handler('attr_url', true, $_tmp) : attr_url($_tmp)); ?>
" url="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/templates/delete_details.php"></td>
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
								
								<form name="pharmacy" method="post" action="<?php echo $this->_tpl_vars['FORM_ACTION2']; ?>
"  class='form-horizontal' onsubmit="return top.restoreSession()">
										<input type="hidden" name="form_id" value="" />

									<!-- <div class="row">
										<div class="col-md-3">
											<div class="form-check">
												<label class="form-check-label">
												<input type="checkbox" class="form-check-input" value="">Deactivate
												</label>
											</div>
										</div>


									</div> -->
									<div class="row pt-3">
										<div class="col-md-12">
											<p>Name</p>
											<input type="text" id="name" name="name" class="form-control pr-1 pl-1" aria-describedby="nameHelpBox"  onKeyDown="PreventIt(event)">
										</div>


									</div>
									<div class="row pt-3">
										<div class="col-md-12">
											<p>Address</p>
											<textarea id="" class="form-control pt-3" rows="3" id="address_line1" name="address_line1" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pharmacy']->address->line1)) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" onKeyDown="PreventIt(event)"></textarea>
										</div>


									</div>
									<div class="row pt-4">
										<div class="col-md-4">
											<p>City</p>
											<input type="text" id="city" name="city" class="form-control  pr-1 pl-1"  onKeyDown="PreventIt(event)">

										   
										</div>
										<div class="col-md-4">
											<p>State</p>
											
											

											<select id="state" name="state" class="form-control mt-2">
												<?php echo $this->_tpl_vars['pharmacy']->state_list(); ?>


											</select>
										</div>
										<div class="col-md-4">
											<p>Zip</p>
											<input type="text" id="zip" name="zip" class="form-control pr-1 pl-1" onKeyDown="PreventIt(event)">

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
											<input type="text" id="email" name="email" class="form-control pr-1 pl-1" onKeyDown="PreventIt(event)">
										</div> 
										<div class="col-md-4">
											<p>Phone</p>
											<input type="text" id="phone" name="phone" class="form-control"  onKeyDown="PreventIt(event)">

										</div>
										<div class="col-md-4">
											<p>Fax</p>
											<input type="text"  id="faxno" name="faxno" class="form-control pr-1 pl-1" onKeyDown="PreventIt(event)">
										</div>

									</div> 
									<div class="row pt-4">
										<div class="col-md-4">
											<p>NPI</p>
												<input type="text" placeholder="" id="npi" name="npi"  class="form-control pr-1 pl-1">
										</div>
										<div class="col-md-4">
											<p>NCPDP</p>
												<input type="text" placeholder="" id="ncpdp" name="ncpdp" class="form-control pr-1 pl-1">
										</div>
	
	
									</div>
									<div class="row pt-3">
										<div class="col-md-12">
											<p>Notes</p>
											<textarea id="note" name="note" class="form-control pt-3" rows="3"></textarea>
										</div>


									</div>

									<input type="hidden" name="id" value="">
									<input type="hidden" name="process" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['PROCESS'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
">
									
									<div class="pt-4 pb-2"><button class="form-save" href="javascript:submit_pharmacy();" onclick="top.restoreSession()">Save</button></div>
								</form>
							</div>
							<div id="menu1" class="container tab-pane fade">
								<div id="ins_list_data"></div>
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

	$("#menu_insurance").click(function() {

	
		$("#ins_list_data").load("controller.php?practice_settings&insurance_company&action=list");
		
  	});

	  $(\'#insurance_edit\').click(function(e) {
		  e.preventDefault();

			
		$("#ins_list_data").load("controller.php?practice_settings&insurance_company&action=list");

	});

	function insurance_edit($insid){
	
		$("#ins_list_data").load("controller.php?practice_settings&insurance_company&action=edit&id="+$insid);
	}


	



	$("body").on("click", ".remove14", function() {
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
