<?php /* Smarty version 2.6.31, created on 2019-10-07 06:40:10
         compiled from D:/xampp/htdocs/openemr_test/templates/documents/general_upload.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', 'D:/xampp/htdocs/openemr_test/templates/documents/general_upload.html', 31, false),array('function', 'xlt', 'D:/xampp/htdocs/openemr_test/templates/documents/general_upload.html', 67, false),array('modifier', 'attr', 'D:/xampp/htdocs/openemr_test/templates/documents/general_upload.html', 31, false),array('modifier', 'attr_url', 'D:/xampp/htdocs/openemr_test/templates/documents/general_upload.html', 42, false),array('modifier', 'text', 'D:/xampp/htdocs/openemr_test/templates/documents/general_upload.html', 71, false),)), $this); ?>



<!-- <form method=post enctype="multipart/form-data" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
" onsubmit="return top.restoreSession()">
	<input type="hidden" name="MAX_FILE_SIZE" value="64000000" />
	<div class="text dragableAra">	
		<p>
			<label for="uploadFile" class="pt-4 pb-3">Upload Document to Categories</label>
				<div>
					<img src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/images/cloud-upload.svg" alt="">
				</div>
				<div class="pt-4">
					<p>Drag & Drop files here</p>
				</div>
				<div class="pt-4">

					<div class="some-30">
							<input type="file" name="file[]" id="source-name" multiple="true" class="form-save point-none" value="BROWSE">&nbsp;
					</div>

				</div>
			<p><input type="submit" value="<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Upload')) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp))), $this);?>
" /></p>
	</div>

	<input type="hidden" name="patient_id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['patient_id'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" />
	<input type="hidden" name="category_id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['category_id'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" />
	<input type="hidden" name="process" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['PROCESS'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
" />
</form> -->


<!-- Drag and drop uploader -->
<div id="autouploader">
	<form method="post" enctype="multipart/form-data" action="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/library/ajax/upload.php?patient_id=<?php echo ((is_array($_tmp=$this->_tpl_vars['patient_id'])) ? $this->_run_mod_handler('attr_url', true, $_tmp) : attr_url($_tmp)); ?>
&parent_id=<?php echo ((is_array($_tmp=$this->_tpl_vars['category_id'])) ? $this->_run_mod_handler('attr_url', true, $_tmp) : attr_url($_tmp)); ?>
&csrf_token_form=<?php echo ((is_array($_tmp=$this->_tpl_vars['CSRF_TOKEN_FORM'])) ? $this->_run_mod_handler('attr_url', true, $_tmp) : attr_url($_tmp)); ?>
" class="dropzone">
		<input type="hidden" name="MAX_FILE_SIZE" value="64000000" >
		<p>	<label for="uploadFile" class="pt-4 pb-3">Upload Document to Categories</label></p>
		<div>
			<img src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/images/cloud-upload.svg" alt="">
		</div>
		<div class="pt-4">
			<p>Drag & Drop files here</p>
		</div>
	</form>
</div>

<!-- Section for document template download -->
<!-- <form method='post' action='interface/patient_file/download_template.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['CSRF_TOKEN_FORM'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
">
<input type='hidden' name='patient_id' value='<?php echo ((is_array($_tmp=$this->_tpl_vars['patient_id'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
' />
<p class='text bold'>
</p> -->

<!-- </form> -->
<!-- End document template download section -->

<?php if (! empty ( $this->_tpl_vars['file'] )): ?>
	<div class="text bold">
		<br/>
		<?php echo smarty_function_xlt(array('t' => 'Upload Report'), $this);?>

	</div>
	<?php $_from = $this->_tpl_vars['file']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['file']):
?>
		<div class="text">
			<?php if ($this->_tpl_vars['error']): ?><i><?php echo ((is_array($_tmp=$this->_tpl_vars['error'])) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</i><br/><?php endif; ?>
			<?php echo smarty_function_xlt(array('t' => 'ID'), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_id())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
<br>
			<?php echo smarty_function_xlt(array('t' => 'Patient'), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_foreign_id())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
<br>
			<?php echo smarty_function_xlt(array('t' => 'URL'), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_url())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
<br>
			<?php echo smarty_function_xlt(array('t' => 'Size'), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_size())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
<br>
			<?php echo smarty_function_xlt(array('t' => 'Date'), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_date())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
<br>
			<?php echo smarty_function_xlt(array('t' => 'Hash'), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_hash())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
<br>
			<?php echo smarty_function_xlt(array('t' => 'MimeType'), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_mimetype())) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
<br>
			<?php echo smarty_function_xlt(array('t' => 'Revision'), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->revision)) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
<br><br>
		</div>
	<?php endforeach; endif; unset($_from); ?>
<?php endif; ?>
<h3><?php echo $this->_tpl_vars['error']; ?>
</h3>