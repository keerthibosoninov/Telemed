<?php
/**
 * Provides manual administration for codes
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("../../../custom/code_types.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

// gacl control
$thisauthview = acl_check('admin', 'superbill', false, 'view');
$thisauthwrite = acl_check('admin', 'superbill', false, 'write');

if (!($thisauthwrite || $thisauthview)) {
    echo "<html>\n<body>\n";
    echo "<p>" . xlt('You are not authorized for this.') . "</p>\n";
    echo "</body>\n</html>\n";
    exit();
}
// For revenue codes
$institutional = $GLOBALS['ub04_support'] == "1" ? true : false;

// Translation for form fields.
function ffescape($field)
{
    $field = add_escape_custom($field);
    return trim($field);
}

// Format dollars for display.
//
function bucks($amount)
{
    if ($amount) {
        $amount = oeFormatMoney($amount);
        return $amount;
    }

    return '';
}

$alertmsg = '';
$pagesize = 100;
$mode = $_POST['mode'];
$code_id = 0;
$related_code = '';
$active = 1;
$reportable = 0;
$financial_reporting = 0;
$revenue_code = '';

if (isset($mode) && $thisauthwrite) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $code_id    = empty($_POST['code_id']) ? '' : $_POST['code_id'] + 0;
    $code       = $_POST['code'];
    $code_type  = $_POST['code_type'];
    $code_text  = $_POST['code_text'];
    $modifier   = $_POST['modifier'];
    $superbill  = $_POST['form_superbill'];
    $related_code = $_POST['related_code'];
    $cyp_factor = is_numeric($_POST['cyp_factor']) ? $_POST['cyp_factor'] + 0 : 0;
    $active     = empty($_POST['active']) ? 0 : 1;
    $reportable = empty($_POST['reportable']) ? 0 : 1; // dx reporting
    $financial_reporting = empty($_POST['financial_reporting']) ? 0 : 1; // financial service reporting
    $revenue_code = $_POST['revenue_code'];

    $taxrates = "";
    if (!empty($_POST['taxrate'])) {
        foreach ($_POST['taxrate'] as $key => $value) {
            $taxrates .= "$key:";
        }
    }

    if ($mode == "delete") {
        sqlStatement("DELETE FROM codes WHERE id = ?", array($code_id));
        $code_id = 0;
    } else if ($mode == "add" || $mode == "modify_complete") { // this covers both adding and modifying
        $crow = sqlQuery("SELECT COUNT(*) AS count FROM codes WHERE " .
            "code_type = '"    . ffescape($code_type)    . "' AND " .
            "code = '"         . ffescape($code)         . "' AND " .
            "modifier = '"     . ffescape($modifier)     . "' AND " .
            "id != '"          . add_escape_custom($code_id) . "'");
        if ($crow['count']) {
            $alertmsg = xl('Cannot add/update this entry because a duplicate already exists!');
        } else {
            $sql =
                "code = '"         . ffescape($code)         . "', " .
                "code_type = '"    . ffescape($code_type)    . "', " .
                "code_text = '"    . ffescape($code_text)    . "', " .
                "modifier = '"     . ffescape($modifier)     . "', " .
                "superbill = '"    . ffescape($superbill)    . "', " .
                "related_code = '" . ffescape($related_code) . "', " .
                "cyp_factor = '"   . ffescape($cyp_factor)   . "', " .
                "taxrates = '"     . ffescape($taxrates)     . "', " .
                "active = "        . add_escape_custom($active) . ", " .
                "financial_reporting = " . add_escape_custom($financial_reporting) . ", " .
                "revenue_code = '" . ffescape($revenue_code) . "', " .
                "reportable = '"    . add_escape_custom($reportable) . "' ";
            if ($code_id) {
                $query = "UPDATE codes SET $sql WHERE id = ?";
                sqlStatement($query, array($code_id));
                sqlStatement("DELETE FROM prices WHERE pr_id = ? AND " .
                    "pr_selector = ''", array($code_id));
            } else {
                $code_id = sqlInsert("INSERT INTO codes SET $sql");
            }

            if (!$alertmsg) {
                foreach ($_POST['fee'] as $key => $value) {
                    $value = $value + 0;
                    if ($value) {
                        sqlStatement("INSERT INTO prices ( " .
                            "pr_id, pr_selector, pr_level, pr_price ) VALUES ( " .
                            "?, '', ?, ?)", array($code_id,$key,$value));
                    }
                }

                $code = $code_type = $code_text = $modifier = $superbill = "";
                $code_id = 0;
                $related_code = '';
                $cyp_factor = 0;
                $taxrates = '';
                $active = 1;
                $reportable = 0;
                $revenue_code = '';
            }
        }
    } else if ($mode == "edit") { // someone clicked [Edit]
        $sql = "SELECT * FROM codes WHERE id = ?";
        $results = sqlStatement($sql, array($code_id));
        while ($row = sqlFetchArray($results)) {
            $code         = $row['code'];
            $code_text    = $row['code_text'];
            $code_type    = $row['code_type'];
            $modifier     = $row['modifier'];
            // $units        = $row['units'];
            $superbill    = $row['superbill'];
            $related_code = $row['related_code'];
            $revenue_code = $row['revenue_code'];
            $cyp_factor   = $row['cyp_factor'];
            $taxrates     = $row['taxrates'];
            $active       = 0 + $row['active'];
            $reportable   = 0 + $row['reportable'];
            $financial_reporting  = 0 + $row['financial_reporting'];
        }
    } else if ($mode == "modify") { // someone clicked [Modify]
        // this is to modify external code types, of which the modifications
        // are stored in the codes table
        $code_type_name_external = $_POST['code_type_name_external'];
        $code_external = $_POST['code_external'];
        $code_id = $_POST['code_id'];
        $results = return_code_information($code_type_name_external, $code_external, false); // only will return one item
        while ($row = sqlFetchArray($results)) {
            $code         = $row['code'];
            $code_text    = $row['code_text'];
            $code_type    = $code_types[$code_type_name_external]['id'];
            $modifier     = $row['modifier'];
            // $units        = $row['units'];
            $superbill    = $row['superbill'];
            $related_code = $row['related_code'];
            $revenue_code = $row['revenue_code'];
            $cyp_factor   = $row['cyp_factor'];
            $taxrates     = $row['taxrates'];
            $active       = $row['active'];
            $reportable   = $row['reportable'];
            $financial_reporting  = $row['financial_reporting'];
        }
    }

    // If codes history is enabled in the billing globals save data to codes history table
    if ($GLOBALS['save_codes_history'] && $alertmsg=='' &&
        ( $mode == "add" || $mode == "modify_complete" || $mode == "delete" ) ) {
        $action_type= empty($_POST['code_id']) ? 'new' : $mode;
        $action_type= ($action_type=='add') ? 'update' : $action_type ;
        $code       = $_POST['code'];
        $code_type  = $_POST['code_type'];
        $code_text  = $_POST['code_text'];
        $modifier   = $_POST['modifier'];
        $superbill  = $_POST['form_superbill'];
        $related_code = $_POST['related_code'];
        $revenue_code = $_POST['revenue_code'];
        $cyp_factor = $_POST['cyp_factor'] + 0;
        $active     = empty($_POST['active']) ? 0 : 1;
        $reportable = empty($_POST['reportable']) ? 0 : 1; // dx reporting
        $financial_reporting = empty($_POST['financial_reporting']) ? 0 : 1; // financial service reporting
        $fee=json_encode($_POST['fee']);
        $code_sql= sqlFetchArray(sqlStatement("SELECT (ct_label) FROM code_types WHERE ct_id=?", array($code_type)));
        $code_name='';

        if ($code_sql) {
            $code_name=$code_sql['ct_label'];
        }

        $categorey_id= $_POST['form_superbill'];
        $categorey_sql=sqlFetchArray(sqlStatement("SELECT (title ) FROM list_options WHERE list_id='superbill'".
            " AND option_id=?", array($categorey_id)));

        $categorey_name='';

        if ($categorey_sql) {
            $categorey_name=$categorey_sql['title'];
        }

        $date=date('Y-m-d H:i:s');
        $date=oeFormatShortDate($date);
        $results =  sqlStatement(
            "INSERT INTO codes_history ( " .
            "date, code, modifier, active,diagnosis_reporting,financial_reporting,category,code_type_name,".
            "code_text,code_text_short,prices,action_type, update_by ) VALUES ( " .
            "?, ?,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,?)",
            array($date,$code,$modifier,$active,$reportable,$financial_reporting,$categorey_name,$code_name,$code_text,'',$fee,$action_type,$_SESSION['authUser'])
        );
    }
}

$related_desc = '';
if (!empty($related_code)) {
    $related_desc = $related_code;
}

$fstart = $_REQUEST['fstart'] + 0;
if (isset($_REQUEST['filter'])) {
    $filter = array();
    $filter_key = array();
    foreach ($_REQUEST['filter'] as $var) {
        $var = $var+0;
        array_push($filter, $var);
        $var_key = convert_type_id_to_key($var);
        array_push($filter_key, $var_key);
    }
}

$search = $_REQUEST['search'];
$search_reportable = $_REQUEST['search_reportable'];
$search_financial_reporting = $_REQUEST['search_financial_reporting'];

//Build the filter_elements array
$filter_elements = array();
if (!empty($search_reportable)) {
    $filter_elements['reportable'] = $search_reportable;
}

if (!empty($search_financial_reporting)) {
    $filter_elements['financial_reporting'] = $search_financial_reporting;
}

if (isset($_REQUEST['filter'])) {
    $count = main_code_set_search($filter_key, $search, null, null, false, null, true, null, null, $filter_elements);
}

if ($fstart >= $count) {
    $fstart -= $pagesize;
}

if ($fstart < 0) {
    $fstart = 0;
}

$fend = $fstart + $pagesize;
if ($fend > $count) {
    $fend = $count;
}
?>

<html>
<head>
    <title><?php echo xlt("Codes"); ?></title>

    <!-- <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
    <script type="text/javascript" src="../../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="../../../library/textformat.js"></script>
    <script type="text/JavaScript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery/dist/jquery.min.js"></script>
    <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-ui-themes/themes/base/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-ui/jquery-ui.min.js"></script> -->

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
    <!-- <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/addmore.js"></script> -->
    <!-- <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/js/panzoom.min.js"></script> -->
<style>
     /* input[type=text]{
            margin-top:0px;
        } */
    .ui-autocomplete { max-height: 350px; max-width: 35%; overflow-y: auto; overflow-x: hidden; }
    .form-save1 {
    background-color: #3C9DC5;
    padding: 6px;
    width: 150px;
    border: none;
    outline: none;
    color: white;
    margin-left: 394px;
    }
    .form-save2{
        background-color: #3C9DC5;
    padding: 6px;
    width: 150px;
    border: none;
    outline: none;
    color: white;
    }
    .dnone{
        display:none;
    }
    .dblock{
        display:block;
    }
    
</style>
    <script>
    <?php if ($institutional) { ?>
    $( function() {
        var cache = {};
        $( ".revcode" ).autocomplete({
            minLength: 1,
            source: function( request, response ) {
                var term = request.term;
                request.code_group = "revenue_code";
                if ( term in cache ) {
                  response( cache[ term ] );
                  return;
                }
                $.getJSON( "<?php echo $GLOBALS['web_root'] ?>/interface/billing/ub04_helpers.php", request, function( data, status, xhr ) {
                  cache[ term ] = data;
                  response( data );
                });
            }
        }).dblclick(function(event) {
            $(this).autocomplete('search'," ");
        });
    });
    <?php } ?>

        // This is for callback by the find-code popup.
        // Appends to or erases the current list of related codes.
        function set_related(codetype, code, selector, codedesc) {
            var f = document.forms[0];
            var s = f.related_code.value;
            if (code) {
                if (s.length > 0) s += ';';
                s += codetype + ':' + code;
            } else {
                s = '';
            }
            f.related_code.value = s;
            f.related_desc.value = s;
        }

        // This is for callback by the find-code popup.
        // Returns the array of currently selected codes with each element in codetype:code format.
        function get_related() {
            return document.forms[0].related_code.value.split(';');
        }

        // This is for callback by the find-code popup.
        // Deletes the specified codetype:code from the currently selected list.
        function del_related(s) {
            my_del_related(s, document.forms[0].related_code, false);
            my_del_related(s, document.forms[0].related_desc, false);
        }

        // This invokes the find-code popup.
        function sel_related() {
            var f = document.forms[0];
            var i = f.code_type.selectedIndex;
            var codetype = '';
            if (i >= 0) {
                var myid = f.code_type.options[i].value;
                <?php
                foreach ($code_types as $key => $value) {
                    $codeid = $value['id'];
                    $coderel = $value['rel'];
                    if (!$coderel) {
                        continue;
                    }

                    echo "  if (myid == $codeid) codetype = '$coderel';";
                }
                ?>
            }
            if (!codetype) {
                alert(<?php echo xlj('This code type does not accept relations.'); ?>);
                return;
            }
            dlgopen('find_code_dynamic.php', '_blank', 900, 600);
        }

        // Some validation for saving a new code entry.
        function validEntry(f) {
            if (!f.code.value) {
                alert(<?php echo xlj('No code was specified!'); ?>);
                return false;
            }
            <?php if ($GLOBALS['ippf_specific']) { ?>
            if (f.code_type.value == 12 && !f.related_code.value) {
                alert(<?php echo xlj('A related IPPF code is required!'); ?>);
                return false;
            }
            <?php } ?>
            return true;
        }

        function submitAdd() {
            var f = document.forms[0];
            if (!validEntry(f)) return;
            f.mode.value = 'add';
            f.code_id.value = '';
            f.submit();
        }

        function submitUpdate() {
            var f = document.forms[0];
            if (! parseInt(f.code_id.value)) {
                alert(<?php echo xlj('Cannot update because you are not editing an existing entry!'); ?>);
                return;
            }
            if (!validEntry(f)) return;
            f.mode.value = 'add';
            f.submit();
        }

        function submitModifyComplete() {
            var f = document.forms[0];
            f.mode.value = 'modify_complete';
            f.submit();
        }

        function submitList(offset) {
            var f = document.forms[0];
            var i = parseInt(f.fstart.value) + offset;
            if (i < 0) i = 0;
            f.fstart.value = i;
            f.submit();
        }

        function submitEdit(id) {
            var f = document.forms[0];
            f.mode.value = 'edit';
            f.code_id.value = id;
            f.submit();
        }

        function submitModify(code_type_name,code,id) {
            var f = document.forms[0];
            f.mode.value = 'modify';
            f.code_external.value = code;
            f.code_id.value = id;
            f.code_type_name_external.value = code_type_name;
            f.submit();
        }



        function submitDelete(id) {
            var f = document.forms[0];
            f.mode.value = 'delete';
            f.code_id.value = id;
            f.submit();
        }

        function getCTMask() {
            var ctid = document.forms[0].code_type.value;
            <?php
            foreach ($code_types as $key => $value) {
                $ctid   = $value['id'];
                $ctmask = $value['mask'];
                echo " if (ctid == " . js_escape($ctid) . ") return " . js_escape($ctmask) . ";\n";
            }
            ?>
            return '';
        }

    </script>

</head>
<body class="body_top" >
<section>
            <div class="body-content body-content2">
                <div class="container-fluid pb-4 pt-4">
                    <window-dashboard title="" class="icon-hide">
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
                                            <p class="text-white head-p">Codes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="body-compo" style="height:auto;">
                            <div class="container-fluid">
    <form method='post' action='superbill_custom_full.php' name='theform'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

    <input type='hidden' name='mode' value=''>
      
                <div class="row pt-4">
                                <div class="col-md-3">
                                <p>Type</p>
                    <select name='filter[]' multiple='multiple' size="3" class="form-control mt-2">
                        <?php
                        foreach ($code_types as $key => $value) {
                            echo "<option value='" . attr($value['id']) . "'";
                            if (isset($filter) && in_array($value['id'], $filter)) {
                                echo " selected";
                            }

                            echo ">" . xlt($value['label']) . "</option>\n";
                        }
                        ?>
                    </select>
                    </div>
                   
                    <div class="col-md-3">
                    <p>Code</p>
                    <input type="text" class="form-control pr-1 pl-1" name="search" size="5" value="<?php echo attr($search) ?>">
                    </div>
                    <div class="col-md-3">
                                    <div class="form-check">
                                        <label class="form-check-label">
                    <input type='checkbox' title='<?php echo xla("Only Show Diagnosis Reporting Codes") ?>' name='search_reportable' value='1'<?php if (!empty($search_reportable)) {
                        echo ' checked';
                                                  } ?> />
                    <?php echo xlt('Diagnosis Reporting'); ?>
                    </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <label class="form-check-label">
                    <input type='checkbox' title='<?php echo xla("Only Show Service Code Finance Reporting Codes") ?>' name='search_financial_reporting' value='1'<?php if (!empty($search_financial_reporting)) {
                        echo ' checked';
                                                  } ?> />
                    <?php echo xlt('Service Reporting'); ?>
                    </label>
                                    </div>
                                </div>
                    <input type='hidden' name='fstart' value='<?php echo attr($fstart) ?>'>               
            
                    </div> 
                    <div class="pt-4 pb-2">
                    <button type="submit" name="go" class="form-save1">Search</button>
                    </div>


        <div class="pt-4 pb-4">
        <div class="">
        <table class="table table-form">       
        <!-- <tr>
        <th> <?php echo xlt('Code'); ?> </th>
        <th> <?php echo xlt('Mod'); ?> </th>
        <?php if ($institutional) { ?>
            <th> <?php echo xlt('Revenue'); ?> </th>
        <?php } ?>
        <th> <?php echo xlt('Act'); ?> </th>
        <th> <?php echo xlt('Category'); ?> </th>
        <th> <?php echo xlt('Dx Rep'); ?> </th>
        <th> <?php echo xlt('Serv Rep'); ?> </th>
        <th> <?php echo xlt('Type'); ?> </th>
        <th> <?php echo xlt('Description'); ?> </th>
        <th> <?php echo xlt('Short Description'); ?> </th>
        <?php if (related_codes_are_used()) { ?>
            <th> <?php echo xlt('Related'); ?> </th>
        <?php } ?>
        <?php
        $pres = sqlStatement("SELECT title FROM list_options " .
            "WHERE list_id = 'pricelevel' AND activity = 1 ORDER BY seq, title");
        while ($prow = sqlFetchArray($pres)) {
            echo "  <th>" . text(xl_list_label($prow['title'])) . "</th>\n";
        }
        ?>
    </tr> -->
    <?php

    if (isset($_REQUEST['filter'])) {
        $res = main_code_set_search($filter_key, $search, null, null, false, null, false, $fstart, ($fend - $fstart), $filter_elements);
    }

    for ($i = 0; $row = sqlFetchArray($res); $i++) {
        $all[$i] = $row;
    }

    if (!empty($all)) {
        $count = 0;
        ?>
        <tr>
        <th> <?php echo xlt('Code'); ?> </th>
        <th> <?php echo xlt('Mod'); ?> </th>
        <?php if ($institutional) { ?>
            <th> <?php echo xlt('Revenue'); ?> </th>
        <?php } ?>
        <th> <?php echo xlt('Act'); ?> </th>
        <th> <?php echo xlt('Category'); ?> </th>
        <th> <?php echo xlt('Dx Rep'); ?> </th>
        <th> <?php echo xlt('Serv Rep'); ?> </th>
        <th> <?php echo xlt('Type'); ?> </th>
        <th> <?php echo xlt('Description'); ?> </th>
        <th> <?php echo xlt('Short Description'); ?> </th>
        <?php if (related_codes_are_used()) { ?>
            <th> <?php echo xlt('Related'); ?> </th>
        <?php } ?>
        <?php
        $pres = sqlStatement("SELECT title FROM list_options " .
            "WHERE list_id = 'pricelevel' AND activity = 1 ORDER BY seq, title");
        while ($prow = sqlFetchArray($pres)) {
            echo "  <th>" . text(xl_list_label($prow['title'])) . "</th>\n";
        }
        ?>
        </tr>
<?php
        foreach ($all as $iter) {
            $count++;

            $has_fees = false;
            foreach ($code_types as $key => $value) {
                if ($value['id'] == $iter['code_type']) {
                    $has_fees = $value['fee'];
                    break;
                }
            }

            echo " <tr>\n";
            echo "  <td  >" . text($iter["code"]) . "</td>\n";
            // echo "  <td ><input type='text' class='form-control active-text1' value='". text($iter["code"]) ."'></td>\n";

            echo "  <td  >" . text($iter["modifier"]) . "</td>\n";
            // echo "  <td ><input type='text' class='form-control active-text1' value='". text($iter["modifier"]) ."'></td>\n";

            if ($institutional) {
                echo "  <td  >" . ($iter['revenue_code'] > '' ? text($iter['revenue_code']) : 'none') ."</td>\n";
                // echo "  <td ><input type='text' class='form-control active-text1' value='". ($iter['revenue_code'] > '' ? text($iter['revenue_code']) : 'none') ."'></td>\n";

            }
            if ($iter["code_external"] > 0) {                
                echo "  <td  >" . ( ($iter["active"] || $iter["active"]==null) ? xlt('Yes') : xlt('No')) . "</td>\n";
                // echo "  <td ><input type='text' class='form-control active-text1' value='".  ( ($iter["active"] || $iter["active"]==null) ? xlt('Yes') : xlt('No')) ."'></td>\n";

            } else {
                echo "  <td  >" . ( ($iter["active"]) ? xlt('Yes') : xlt('No')) . "</td>\n";
                // echo "  <td ><input type='text' class='form-control active-text1' value='". ( ($iter["active"]) ? xlt('Yes') : xlt('No')) ."'></td>\n";

            }

            $sres = sqlStatement("SELECT title " .
                "FROM list_options AS lo " .
                "WHERE lo.list_id = 'superbill' AND lo.option_id = ?", array($iter['superbill']));
            if ($srow = sqlFetchArray($sres)) {
                echo "  <td  >" . text($srow['title']) . "</td>\n";
                // echo "  <td ><input type='text' class='form-control active-text1' value='". text($srow['title']) ."'></td>\n";

            } else {
                echo "  <td  >" . '' . "</td>\n";
                // echo "  <td ><input type='text' class='form-control active-text1' value=' '></td>\n";

            }
            echo "  <td  >" . ($iter["reportable"] ? xlt('Yes') : xlt('No')) . "</td>\n";
            // echo " <td ><input type='text' class='form-control active-text1' value='". ($iter["reportable"] ? xlt('Yes') : xlt('No')) ."'></td>\n";

            echo "  <td  >" . ($iter["financial_reporting"] ? xlt('Yes') : xlt('No')) . "</td>\n";
            // echo " <td ><input type='text' class='form-control active-text1' value='". ($iter["financial_reporting"] ? xlt('Yes') : xlt('No')) ."'></td>\n";

            echo "  <td  >" . text($iter['code_type_name']) . "</td>\n";
            // echo " <td ><input type='text' class='form-control active-text1' value='". text($iter['code_type_name']) ."'></td>\n";

            echo "  <td  >" . text($iter['code_text']) . "</td>\n";
            // echo " <td ><input type='text' class='form-control active-text1' value='". text($iter['code_text']) ."'></td>\n";

            echo "  <td  >" . text($iter['code_text_short']) . "</td>\n";
            // echo " <td ><input type='text' class='form-control active-text1' value='". text($iter['code_text_short']) ."'></td>\n";


            if (related_codes_are_used()) {
                // Show related codes.
                echo "  <td  >";
                $arel = explode(';', $iter['related_code']);
                foreach ($arel as $tmp) {
                    list($reltype, $relcode) = explode(':', $tmp);
                    $code_description = lookup_code_descriptions($reltype.":".$relcode);
                    echo text($relcode) . ' ' . text(trim($code_description)) . '<br />';
                }

                echo "</td>\n";
            }

            $pres = sqlStatement("SELECT p.pr_price " .
                "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
                "p.pr_id = ? AND p.pr_selector = '' AND p.pr_level = lo.option_id " .
                "WHERE lo.list_id = 'pricelevel' AND lo.activity = 1 ORDER BY lo.seq", array($iter['id']));
            while ($prow = sqlFetchArray($pres)) {
                echo "<td >" . text(bucks($prow['pr_price'])) . "</td>\n";
            }

            if ($thisauthwrite) {
                if ($iter["code_external"] > 0) {
                    echo "  <td align='right'><a class='link' href='javascript:submitModify(" . attr_js($iter['code_type_name']) . "," . attr_js($iter['code']) . "," . attr_js($iter['id']) . ")'>[" . xlt('Modify') . "]</a></td>\n";
                } else {
                    echo "<td><a class='link' href='javascript:submitEdit(" . attr_js($iter['id']) . ")'><img src='". $GLOBALS['assets_static_relative'] ."/img/edit-text.svg' class='remove16'></a><br><br><a class='link' href='javascript:submitDelete(" . attr_js($iter['id']) . ")'><img src='". $GLOBALS['assets_static_relative'] ."/img/delete.svg'  class='xxx pr-2'></a></td>";

                    // echo "  <td align='right'><a class='link' href='javascript:submitDelete(" . attr_js($iter['id']) . ")'>[" . xlt('Delete') . "]</a></td>\n";
                    // echo "  <td align='right'><a class='link' href='javascript:submitEdit(" . attr_js($iter['id']) . ")'>[" . xlt('Edit') . "]</a></td>\n";
                }
            }

            echo " </tr>\n";
        }
    }

    ?>

</table>
</div>
</div>
    <div id="addn">
        <div class="text-center">
        <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" id="addnew" alt="">
        </div>
        <div class="text-center">
        <p class="fs-14">Add New</p>
        </div>
    </div>
<div class="pt-4 pb-4 dnone" id="form_save">
       
<div class="row">
                                    <div class="col-md-3">
                                        <p>Type</p>

                    <?php if ($mode != "modify") { ?>
                    <select name="code_type" size="3" class="form-control mt-2">
                        <?php } ?>

                        <?php $external_sets = array(); ?>
                        <?php foreach ($code_types as $key => $value) { ?>
                            <?php if (!($value['external'])) { ?>
                                <?php if ($mode != "modify") { ?>
                                    <option value="<?php  echo attr($value['id']) ?>"<?php if ($code_type == $value['id']) {
                                        echo " selected";
                                                   } ?>><?php echo xlt($value['label']) ?></option>
                                <?php } ?>
                            <?php } ?>
                            <?php if ($value['external']) {
                                array_push($external_sets, $key);
                            } ?>
                        <?php } // end foreach ?>

                        <?php if ($mode != "modify") { ?>
                    </select>
                        </div>
                <?php } ?>

                    <?php if ($mode == "modify") { ?>
                        <input type='text' size='4' name='code_type' readonly='readonly' style='display:none' value='<?php echo attr($code_type) ?>' />
                        <?php echo text($code_type_name_external) ?>
                    <?php } ?>
                    <div class="col-md-3">
                    <p>Code</p>

                    <?php if ($mode == "modify") { ?>
                        <input type='text' class="form-control pr-1 pl-1" size='6' name='code' readonly='readonly' value='<?php echo attr($code) ?>' />
                    <?php } else { ?>
                        <input class="form-control pr-1 pl-1" type='text' size='6' name='code' value='<?php echo attr($code) ?>'
                               onkeyup='maskkeyup(this,getCTMask())'
                               onblur='maskblur(this,getCTMask())'
                        />
                    <?php } ?>
                    </div>
                    <div class="col-md-3">
                    
                    <?php if (modifiers_are_used()) { ?>
                        <p>Modifier</p>
                        <?php if ($mode == "modify") { ?>
                            <input class="form-control pr-1 pl-1" type='text' size='6' name='modifier' readonly='readonly' value='<?php echo attr($modifier) ?>'>
                        <?php } else { ?>
                            <input class="form-control pr-1 pl-1" type='text' size='6' name='modifier' value='<?php echo attr($modifier) ?>'>
                        <?php } ?>
                    <?php } else { ?>
                        <input type='hidden' name='modifier' value=''>
                    <?php } ?>
                    </div>                                                           
                                                                
                <div class="col-md-3">
                                        <p>Category</p>
                                        
                                        <?php
                    generate_form_field(array('data_type'=>1,'field_id'=>'superbill','list_id'=>'superbill'), $superbill);
                    ?>
                                    </div>
                                    </div>                                                                      

            <!-- <tr>
                <td><?php echo xlt('Description'); ?>:</td>
                <td></td>
                <td>
                    <?php if ($mode == "modify") { ?>
                        <input class="form-control pr-1 pl-1" type='text' size='50' name="code_text" readonly="readonly" value='<?php echo attr($code_text) ?>'>
                    <?php } else { ?>
                        <input class="form-control pr-1 pl-1" type='text' size='50' name="code_text" value='<?php echo attr($code_text) ?>'>
                    <?php } ?>
                <?php if ($institutional) { ?>
                    <?php echo xlt('Revenue Code'); ?>:
                    <?php if ($mode == "modify") { ?>
                        <input class="form-control pr-1 pl-1" type='text' size='6' name="revenue_code" readonly="readonly" value='<?php echo attr($revenue_code) ?>'>
                    <?php } else { ?>
                        <input class="form-control pr-1 pl-1" type='text' size='6' class='revcode' name="revenue_code" title='<?php echo xla('Type to search and select revenue code'); ?>' value='<?php echo attr($revenue_code) ?>'>
                    <?php } ?>
                <?php } ?>
                </td>
            </tr> -->

            <div class="row pt-4 pb-4">
            <div class="col-md-3">
                    <div class="form-check">
                    <label class="form-check-label">
                <input type='checkbox' name='active' value='1'<?php if (!empty($active) || ($mode == 'modify' && $active == null)) {
                        echo ' checked';
                    } ?>>Active
                   
                    </label>
                    </div>
                    </div>
                    <div class="col-md-3">
                    <div class="form-check">
                    <label class="form-check-label">
                    <input type='checkbox' title='<?php echo xla("Syndromic Surveillance Report") ?>' name='reportable' value='1'<?php if (!empty($reportable)) {
                        echo ' checked';
                                                  } ?> >Diagnosis Reporting
                    </label>
                    </div>
                    </div>
                    <div class="col-md-3">
                    <div class="form-check">
                    <label class="form-check-label">
                    <input type='checkbox' title='<?php echo xla("Service Code Finance Reporting") ?>' name='financial_reporting' value='1'<?php if (!empty($financial_reporting)) {
                        echo ' checked';
                                                  } ?>>Service Reporting                   
                    </label>
                    </div>
                    </div>
                </div>

             <?php if (empty($GLOBALS['ippf_specific'])) {
                // echo " style='display:none'";
               } ?>
                <!-- <td><?php echo xlt('CYP Factor'); ?>:</td> -->
                <!-- <td></td>
                <td> -->
                    <input type='text' style='display:none' size='10' maxlength='20' name="cyp_factor" value='<?php echo attr($cyp_factor) ?>'>
                <!-- </td>
            </tr> -->


             <?php if (!related_codes_are_used()) {
                // echo " style='display:none'";
               } ?>
                <!-- <?php echo xlt('Relate To'); ?> -->
               
                    <input style='display:none' class="form-control pr-1 pl-1" type='text' size='50' name='related_desc'
                           value='<?php echo attr($related_desc) ?>' onclick="sel_related()"
                           title='<?php echo xla('Click to select related code'); ?>' readonly />
                    <input type='hidden' name='related_code' value='<?php echo attr($related_code) ?>' />
                


            <div class="row pt-4">

<div class="col-md-4">
    <p>Fees</p>
    <!-- <input type="text" placeholder="" class="form-control pr-1 pl-1"> -->
    <?php
                    $pres = sqlStatement("SELECT lo.option_id, lo.title, p.pr_price " .
                        "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
                        "p.pr_id = ? AND p.pr_selector = '' AND p.pr_level = lo.option_id " .
                        "WHERE lo.list_id = 'pricelevel' AND lo.activity = 1 ORDER BY lo.seq, lo.title", array($code_id));
                    for ($i = 0; $prow = sqlFetchArray($pres); ++$i) {
                        if ($i) {
                            // echo "&nbsp;&nbsp;";
                        }

                        // echo text(xl_list_label($prow['title'])) . " ";
                        echo "<input class='form-control pr-1 pl-1' type='text' size='6' name='fee[" . attr($prow['option_id']) . "]' " .
                            "value='" . attr($prow['pr_price']) . "' >\n";
                    }
                    ?>
</div>


</div>
           

            <?php
            $taxline = '';
            $pres = sqlStatement("SELECT option_id, title FROM list_options " .
                "WHERE list_id = 'taxrate' AND activity = 1 ORDER BY seq");
            while ($prow = sqlFetchArray($pres)) {
                if ($taxline) {
                    $taxline .= "&nbsp;&nbsp;";
                }

                $taxline .= "<input type='checkbox' name='taxrate[" . attr($prow['option_id']) . "]' value='1'";
                if (strpos(":$taxrates", $prow['option_id']) !== false) {
                    $taxline .= " checked";
                }

                $taxline .= " />\n";
                $taxline .=  text(xl_list_label($prow['title'])) . "\n";
            }

            if ($taxline) {
                ?>
                <!-- <tr>
                    <td><?php echo xlt('Taxes'); ?>:</td>
                    <td></td>
                    <td>
                        <?php echo $taxline ?>
                    </td>
                </tr> -->
                <?php
            } ?>

                                                              
        </div>
        <div class="pt-4 pb-2">
        <div class="row">
                    
        <input type="hidden" name="code_id" value="<?php echo attr($code_id) ?>"><br>
                    <input type="hidden" name="code_type_name_external" value="<?php echo attr($code_type_name_external) ?>">
                    <input type="hidden" name="code_external" value="<?php echo attr($code_external) ?>">
                    <?php if ($thisauthwrite) { ?>
                        <?php if ($mode == "modify") { ?>
                            <a href='javascript:submitModifyComplete();' class='link'><button class="form-save">Update</button></a>
                        <?php } else { ?>
                            <div class="col-md-4"></div>
                            <div class="col-md-2">
                            <!-- <a href='javascript:submitUpdate();' class='form-save2'>Update</a> -->
                            <button onclick="javascript:submitUpdate();" class="form-save">Update</button>
                        </div>
                        <div class="col-md-2">
                            <!-- <a href='javascript:submitAdd();' class='form-save2'>Save</a> -->
                            <button onclick="javascript:submitAdd();" class="form-save">Save</button>
                        </div>
                        <?php } ?>
                    <?php } ?>
                       
                        </div>

</form>





</div>
</div>

</window-dashboard>
</div>
</div>
</section>

<script language="JavaScript">
    <?php
    if ($alertmsg) {
        echo "alert(" . js_escape($alertmsg) . ");\n";
    }
    ?>

$('#addnew').on('click', function () {
        $("#form_save").addClass("dblock");     
        $("#addn").addClass("dnone");
    });
</script>

</body>
</html>
