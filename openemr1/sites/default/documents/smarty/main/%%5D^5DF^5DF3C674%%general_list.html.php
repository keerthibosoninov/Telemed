<?php /* Smarty version 2.6.31, created on 2019-10-05 07:27:07
         compiled from D:/xampp/htdocs/openemr_test/templates/practice_settings/general_list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xlt', 'D:/xampp/htdocs/openemr_test/templates/practice_settings/general_list.html', 5, false),array('function', 'headerTemplate', 'D:/xampp/htdocs/openemr_test/templates/practice_settings/general_list.html', 7, false),)), $this); ?>
<!DOCTYPE html>
<html>
<head>

    <title><?php echo smarty_function_xlt(array('t' => 'Practice Settings'), $this);?>
</title>

    <!-- <?php echo smarty_function_headerTemplate(array('assets' => 'bootstrap-sidebar|common'), $this);?>
 -->

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

</head>
<body class="body_top" >


<!-- <div class="container-fluid"> -->
    <!-- <div class="row"> -->
        <!-- <div class="col-xs-12 col-sm-2 sidebar sidebar-<?php echo $this->_tpl_vars['direction']; ?>
 sidebar-sm-show">
            <ul class="nav navbar-stacked">
                <li><a href="<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
pharmacy&action=list"><?php echo smarty_function_xlt(array('t' => 'Pharmacies'), $this);?>
</a></li>
                <li><a href="<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
insurance_company&action=list"><?php echo smarty_function_xlt(array('t' => 'Insurance Companies'), $this);?>
</a></li>
                <li><a href="<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
insurance_numbers&action=list"><?php echo smarty_function_xlt(array('t' => 'Insurance Numbers'), $this);?>
</a></li>
                <li><a href="<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
x12_partner&action=list"><?php echo smarty_function_xlt(array('t' => 'X12 Partners'), $this);?>
</a></li>
                <li><a href="<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
document&action=queue"><?php echo smarty_function_xlt(array('t' => 'Documents'), $this);?>
</a></li>
                <li><a href="<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
hl7&action=default"><?php echo smarty_function_xlt(array('t' => 'HL7 Viewer'), $this);?>
</a></li>
            </ul>
        </div> -->
        <div class="col-xs-12 ">
            <div class="page-header section-header">
                <!-- <h2><?php echo $this->_tpl_vars['ACTION_NAME']; ?>
</h2> -->
            </div>
            <div>
                <?php echo $this->_tpl_vars['display']; ?>

            </div>
        </div>
    <!-- </div> -->
<!-- </div> -->
</body>
</html>