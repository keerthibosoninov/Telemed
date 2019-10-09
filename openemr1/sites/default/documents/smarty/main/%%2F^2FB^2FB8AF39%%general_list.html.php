<?php /* Smarty version 2.6.31, created on 2019-10-07 07:37:27
         compiled from D:/xampp/htdocs/openemr_test/templates/documents/general_list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'headerTemplate', 'D:/xampp/htdocs/openemr_test/templates/documents/general_list.html', 13, false),array('function', 'xlj', 'D:/xampp/htdocs/openemr_test/templates/documents/general_list.html', 158, false),array('function', 'xlt', 'D:/xampp/htdocs/openemr_test/templates/documents/general_list.html', 162, false),array('function', 'datetimepickerSupport', 'D:/xampp/htdocs/openemr_test/templates/documents/general_list.html', 348, false),array('modifier', 'attr', 'D:/xampp/htdocs/openemr_test/templates/documents/general_list.html', 197, false),array('modifier', 'text', 'D:/xampp/htdocs/openemr_test/templates/documents/general_list.html', 234, false),array('modifier', 'js_escape', 'D:/xampp/htdocs/openemr_test/templates/documents/general_list.html', 262, false),array('modifier', 'js_url', 'D:/xampp/htdocs/openemr_test/templates/documents/general_list.html', 275, false),)), $this); ?>
<html>
<head>

<?php echo smarty_function_headerTemplate(array('assets' => 'datetime-picker|jquery-ui|jquery-ui-lightness'), $this);?>

<link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/dropzone/dist/dropzone.css">
<?php echo '
<style type="text/css">
/* .warn_diagnostic {
    margin: 10 auto 10 auto;
    color: rgb(255, 0, 0);
    font-size: 1.5em;
}
.ui-autocomplete {
    position: absolute;
    top: 0;
    left: 0;
    min-width:200px;
    cursor: default;
}
.ui-menu-item{
     min-width:200px;
}
.fixed-height{
min-width:200px;
padding: 1px;
max-height: 35%;
overflow: auto;
} */

#documents_list .treeMenuDefault {
    font-style: initial !important;
}
span{
    font-size:14px !important;
    padding-left: 10px;
    padding-top: 8px;
    color: #21252D;
}
body{
    font-family: \'Open Sans\', sans-serif;
    color: #21252D;


}
.treeMenuDefault span{
    margin-bottom: 10px;
    font-size: 14px;
    -webkit-transition: all .4s ease;
    transition: all .4s ease;
    position: relative;
    padding-top: 8px;
    padding-left: 10px;
}

.dragableAra {
    background: #ffffff;
    padding: 10px;
    text-align: center;
    height: 350px;
    position: relative;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

nobr{
    padding-top:10px;
    padding-left:10px;

}
.treeMenuDefault{
    /* margin:20px; */
    margin-bottom:5px;
    /* display: initial !important; */
}

#documents_list{
    width: 109% !important;
    border-right:1px solid silver !important;
    overflow:unset;
}
.text {
    color: #21252D;

}
.pt-4{
    font-weight: 100;
}

.dropzone{
    border:none !important;
}


.dz-default{
    background-color: #3C9DC5;
    padding: 4px;
    width: 100%;
    border: none;
    outline: none;
    color: white !important;
    pointer-events:none;
    width: 20%;
    margin: auto;
    margin-top: 19px;


}
.dz-message span{
    color: white !important;
}
.dropzone .dz-message {
    text-align: center;
    margin: auto !important; 
     margin-top: 20px !important;
}

.float_contents{
    float:right;
    margin-top: 20px;
}
.css_button{
    background:#3C9DC5 !important;
}

</style>
'; ?>


        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/css/style.css">
    
        <link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/css/employee_dashboard_style.css">
        <link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/css/emp_info_css.css">
    
        <script src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/assets/js/vue.js"></script>
    
       
<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/library/js/DocumentTreeMenu.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/dropzone/dist/dropzone.js"></script>



<script type="text/javascript">
    // dropzone javascript asset translation(s)
    // Dropzone.prototype.defaultOptions.dictDefaultMessage = <?php echo smarty_function_xlj(array('t' => 'Drop files here to upload'), $this);?>
;
    Dropzone.prototype.defaultOptions.dictDefaultMessage = <?php echo smarty_function_xlj(array('t' => 'BROWSE'), $this);?>
;

</script>
<!-- <title><?php echo smarty_function_xlt(array('t' => 'Documents'), $this);?>
</title> -->
</head>
<!-- ViSolve - Call expandAll function on loading of the page if global value 'expand_document' is set -->
<?php if ($this->_tpl_vars['GLOBALS']['expand_document_tree']): ?>
  <body class="body_top" onload="javascript:objTreeMenu_1.expandAll();return false;">
<?php else: ?>
  <body class="body_top">
<?php endif; ?>

    

    <section>
        <div class="body-content body-content2">
            <div class="container-fluid pb-4 pt-4">
                <window-dashboard class="icon-hide">
                    <div class="head-component">
                        <div class="row">
                            <div class="col-6"></div>
                                <div class="col-6">
                                    <p class="text-white head-p"><?php echo smarty_function_xlt(array('t' => 'New Documents'), $this);?>
 </p>
                                </div>
                        </div>
                    </div>
                   
                    <div class="body-compo">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-4 border-right-silver ">
                                        <div class="folder-tree-wrapper">
                                            <div id="documents_list">
                                                    <!-- <fieldset> -->
                                                    <!-- <legend><?php echo smarty_function_xlt(array('t' => 'Documents List'), $this);?>
</legend> -->
                                                    <div style="padding: 0 10px">
                                                        <div class="ui-widget"style="float:right;">
                                                            <!-- <button id='pid' class="pBtn" type="button" style="float:right;">0</button> -->
                                                             <!-- <input id="selectPatient" type="text" placeholder="<?php echo ((is_array($_tmp=$this->_tpl_vars['place_hld'])) ? $this->_run_mod_handler('attr', true, $_tmp) : attr($_tmp)); ?>
"> -->
                                                        </div>
                                                        <a id="list_collapse" href="#" onclick="javascript:objTreeMenu_1.collapseAll();return false;">&nbsp;(<?php echo smarty_function_xlt(array('t' => 'Collapse all'), $this);?>
)</a>
                                                        <?php echo $this->_tpl_vars['tree_html']; ?>

                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        <div class="dragableAra">
                                                <?php if (! $this->_tpl_vars['activity']): ?>
                                                <p>	<label for="uploadFile" class="pt-4 pb-3">Upload Document to Categories</label></p>
                                                <div>
                                                    <img src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/public/images/cloud-upload.svg" alt="">
                                                </div>
                                                <div class="pt-4">
                                                    <p>Drag & Drop files here</p>
                                                </div>
                                                <?php endif; ?>

                                            <!-- <input type="file" class="dragAndDrop" id="uploadFile"> -->
                                            
                                            <!-- <div class="pt-4">
    
                                                <div class="some-30">
                                                    <button class="form-save point-none">BROWSE</button>
                                                </div>
    
                                            </div>

                                            <label for="uploadFile" class="pt-4 pb-3">Upload Document to Categories</label> -->

                                            <div id="documents_actions">

                                
                                                <div style="padding: 0 10px">
                                                    <?php if ($this->_tpl_vars['message']): ?>
                                                        <div class='text' style="margin-bottom:-10px; margin-top:-8px; padding:10px;"><i><?php echo ((is_array($_tmp=$this->_tpl_vars['message'])) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</i></div><br>
                                                    <?php endif; ?>
                                                    <?php if ($this->_tpl_vars['messages']): ?>
                                                        <div class='text' style="margin-bottom:-10px; margin-top:-8px; padding:10px;"><i><?php echo ((is_array($_tmp=$this->_tpl_vars['messages'])) ? $this->_run_mod_handler('text', true, $_tmp) : text($_tmp)); ?>
</i></div><br>
                                                    <?php endif; ?>
                                                    <?php echo $this->_tpl_vars['activity']; ?>

                                                </div>
                                                   
                                    
                                                </div>
                                        </div>
    
                                        <div class="uploadedFile">
                                            <img src="" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </window-dashboard>
            </div>

        </div>
    </section>



<script type="text/javascript">
var curpid = <?php echo ((is_array($_tmp=$this->_tpl_vars['cur_pid'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
var newVersion= <?php echo ((is_array($_tmp=$this->_tpl_vars['is_new'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
var demoPid = <?php echo ((is_array($_tmp=$this->_tpl_vars['demo_pid'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
var inUseMsg = <?php echo ((is_array($_tmp=$this->_tpl_vars['used_msg'])) ? $this->_run_mod_handler('js_escape', true, $_tmp) : js_escape($_tmp)); ?>
;
<?php echo '
if(curpid == demoPid && !newVersion){
    $(".ui-widget").hide();
}
else{
    $("#pid").text(curpid);
}
$(function() {
    $( "#selectPatient" ).autocomplete({
    	source: "'; ?>
<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/library/ajax/document_helpers.php?csrf_token_form=" + <?php echo ((is_array($_tmp=$this->_tpl_vars['CSRF_TOKEN_FORM'])) ? $this->_run_mod_handler('js_url', true, $_tmp) : js_url($_tmp)); ?>
<?php echo ',
    	focus: function(event, sel) {
            event.preventDefault();
        },
        select: function(event, sel) {
            event.preventDefault();
            if (sel.item.value == \'00\' && ! sel.item.label.match('; ?>
<?php echo smarty_function_xlj(array('t' => 'Reset'), $this);?>
<?php echo ')){
            	alert(inUseMsg);
            	return false;
            }
            $(this).val(sel.item.label);
            location.href = "'; ?>
<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
<?php echo '/controller.php?document&list&patient_id=" + encodeURIComponent(sel.item.value) + "&patient_name=" + encodeURIComponent(sel.item.label);
            $("#pid").text(sel.item.value);
        },
        minLength: 3
    }).autocomplete("widget").addClass("fixed-height");
 });
$(".pBtn").click(function(event) {
    var $input = $("#selectPatient");
        $input.val(\'*\');
        $input.autocomplete(\'search\'," ");
        $input.val(\'\');
});
$("#list_collapse").detach().appendTo("#objTreeMenu_1_node_1 nobr");

// functions to view and pop out documents as needed.
//
$(function () {
    $("img[id^=\'icon_objTreeMenu_\']").tooltip({
        items: $("img[src*=\'file3.png\']"),
        content: '; ?>
<?php echo smarty_function_xlj(array('t' => "Double Click on this icon to pop up document in a new viewer."), $this);?>
<?php echo '
    });

    $("img[id^=\'icon_objTreeMenu_\']").on(\'dblclick\', function (e) {
        let popsrc = $(this).next("a").attr(\'href\') || \'\';
        let diview = $(this).next("a").text();
        let dflg = false;
        if (!popsrc.includes(\'&view&\')) {
            return false;
        } else if (diview.toLowerCase().includes(\'.dcm\') || diview.toLowerCase().includes(\'.zip\')) {
            popsrc = "'; ?>
<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
<?php echo '/library/dicom_frame.php?web_path=" + encodeURIComponent(popsrc);
            dflg = true;
        }
        popsrc = popsrc.replace(\'&view&\', \'&retrieve&\') + \'as_file=false\';
        let poContentModal = function () {
            let wname = \'_\' + Math.random().toString(36).substr(2, 6);
            let opt = "menubar=no,location=no,resizable=yes,scrollbars=yes,status=no";
            window.open(popsrc, wname, opt);
        };

        let btnText = '; ?>
<?php echo smarty_function_xlj(array('t' => 'Full Screen'), $this);?>
<?php echo ';
        let btnClose = '; ?>
<?php echo smarty_function_xlj(array('t' => 'Close'), $this);?>
<?php echo ';
        let size = \'modal-xl\';
        let sizeHeight = \'full\';
        if (dflg) {
            size = \'modal-md\';
        }
        dlgopen(popsrc, \'popdoc\', size, 600, \'\', \'\', {
            buttons: [
                {text: btnText, close: true, style: \'primary btn-xs\', click: poContentModal},
                {text: btnClose, close: true, style: \'default btn-xs\'}
            ],
            sizeHeight: sizeHeight,
            allowResize: true,
            allowDrag: true,
            dialogId: \'\',
            type: \'iframe\'
        });
        return false;
    });
});

$(function(){'; ?>

    <?php echo smarty_function_datetimepickerSupport(array(), $this);?>

<?php echo '});'; ?>


</script>
</body>
</html>