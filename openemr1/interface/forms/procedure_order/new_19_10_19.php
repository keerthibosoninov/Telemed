<?php
/**
 * Encounter form for entering procedure orders.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2010-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/options.inc.php");
require_once("../../orders/qoe.inc.php");
require_once("../../orders/gen_hl7_order.inc.php");
require_once("../../../custom/code_types.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

// Defaults for new orders.
$row = array(
    'provider_id' => $_SESSION['authUserID'],
    'date_ordered' => date('Y-m-d'),
    'date_collected' => date('Y-m-d H:i'),
);
$encounter=1;
$pid=1;
if (!$encounter) { // comes from globals.php
    die("Internal error: we do not seem to be in an encounter!");
}

function cbvalue($cbname)
{
    return $_POST[$cbname] ? '1' : '0';
}

function cbinput($name, $colname)
{
    global $row;
    $ret = "<input type='checkbox' name='" . attr($name) . "' value='1'";
    if ($row[$colname]) {
        $ret .= " checked";
    }
    $ret .= " />";
    return $ret;
}

function cbcell($name, $desc, $colname)
{
    return "<td width='25%' nowrap>" . cbinput($name, $colname) . text($desc) ."</td>\n";
}

function QuotedOrNull($fld)
{
    if (empty($fld)) {
        return null;
    }

    return $fld;
}

function getListOptions($list_id, $fieldnames = array('option_id', 'title', 'seq'))
{
    $output = array();
    $query = sqlStatement("SELECT " . implode(',', $fieldnames) . " FROM list_options where list_id = ? AND activity = 1 order by seq", array($list_id));
    while ($ll = sqlFetchArray($query)) {
        foreach ($fieldnames as $val) {
            $output[$ll['option_id']][$val] = $ll[$val];
        }
    }
    return $output;
}

$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : 0);

// If Save or Transmit was clicked, save the info.
//
if ($_POST['bn_save'] || $_POST['bn_xmit']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $ppid = (isset($_POST['form_lab_id']) ? $_POST['form_lab_id'] : '') + 0;

    $sets =
        "date_ordered = ?, " .
        "provider_id = ?, " .
        "lab_id = ?, " .
        "date_collected = ?, " .
        "order_priority = ?, " .
        "order_status = ?, " .
        "clinical_hx = ?, " .
        "patient_instructions = ?, " .
        "patient_id = ?, " .
        "encounter_id = ?, " .
        "history_order = ?";

    // If updating an existing form...
    //
    if ($formid) {
        $query = "UPDATE procedure_order SET $sets " .
            "WHERE procedure_order_id = ?";
        sqlStatement(
            $query,
            [
                QuotedOrNull((isset($_POST['form_date_ordered']) ? $_POST['form_date_ordered'] : '')),
                ((isset($_POST['form_provider_id']) ? $_POST['form_provider_id'] : '') + 0),
                $ppid,
                QuotedOrNull((isset($_POST['form_date_collected']) ? $_POST['form_date_collected'] : '')),
                (isset($_POST['form_order_priority']) ? $_POST['form_order_priority'] : ''),
                (isset($_POST['form_order_status']) ? $_POST['form_order_status'] : ''),
                (isset($_POST['form_clinical_hx']) ? $_POST['form_clinical_hx'] : ''),
                (isset($_POST['form_patient_instructions']) ? $_POST['form_patient_instructions'] : ''),
                $pid,
                $encounter,
                (isset($_POST['form_history_order']) ? $_POST['form_history_order'] : ''),
                $formid
            ]
        );
    } else {// If adding a new form...
        $query = "INSERT INTO procedure_order SET $sets";
        $formid = sqlInsert(
            $query,
            [
                QuotedOrNull((isset($_POST['form_date_ordered']) ? $_POST['form_date_ordered'] : '')),
                ((isset($_POST['form_provider_id']) ? $_POST['form_provider_id'] : '') + 0),
                $ppid,
                QuotedOrNull((isset($_POST['form_date_collected']) ? $_POST['form_date_collected'] : '')),
                (isset($_POST['form_order_priority']) ? $_POST['form_order_priority'] : ''),
                (isset($_POST['form_order_status']) ? $_POST['form_order_status'] : ''),
                (isset($_POST['form_clinical_hx']) ? $_POST['form_clinical_hx'] : ''),
                (isset($_POST['form_patient_instructions']) ? $_POST['form_patient_instructions'] : ''),
                $pid,
                $encounter,
                (isset($_POST['form_history_order']) ? $_POST['form_history_order'] : '')
            ]
        );
        addForm($encounter, "Procedure Order", $formid, "procedure_order", $pid, $userauthorized);
    }

    // Remove any existing procedures and their answers for this order and
    // replace them from the form.

    sqlStatement(
        "DELETE FROM procedure_answers WHERE procedure_order_id = ?",
        array($formid)
    );
    sqlStatement(
        "DELETE FROM procedure_order_code WHERE procedure_order_id = ?",
        array($formid)
    );

    for ($i = 0; isset($_POST['form_proc_type'][$i]); ++$i) {
        $ptid = $_POST['form_proc_type'][$i] + 0;
        if ($ptid <= 0) {
            continue;
        }

        $prefix = "ans$i" . "_";

        sqlBeginTrans();
        $procedure_order_seq = sqlQuery("SELECT IFNULL(MAX(procedure_order_seq),0) + 1 AS increment FROM procedure_order_code WHERE procedure_order_id = ? ", array($formid));
        $poseq = sqlInsert(
            "INSERT INTO procedure_order_code SET " .
            "procedure_order_id = ?, " .
            "diagnoses = ?, " .
            "procedure_order_title = ?, " .
            "procedure_code = (SELECT procedure_code FROM procedure_type WHERE procedure_type_id = ?), " .
            "procedure_name = (SELECT name FROM procedure_type WHERE procedure_type_id = ?)," .
            "procedure_order_seq = ? ",
            array($formid, $_POST['form_proc_type_diag'][$i], $_POST['form_proc_order_title'][$i], $ptid, $ptid, $procedure_order_seq['increment'])
        );
        sqlCommitTrans();

        // can't get insert_id on a table w/o an auto increment column so below. sjp
        $poseq = $procedure_order_seq['increment'];

        $qres = sqlStatement("SELECT " .
            "q.procedure_code, q.question_code, q.options, q.fldtype " .
            "FROM procedure_type AS t " .
            "JOIN procedure_questions AS q ON q.lab_id = t.lab_id " .
            "AND q.procedure_code = t.procedure_code AND q.activity = 1 " .
            "WHERE t.procedure_type_id = ? " .
            "ORDER BY q.seq, q.question_text", array($ptid));

        while ($qrow = sqlFetchArray($qres)) {
            $options = trim($qrow['options']);
            $qcode = trim($qrow['question_code']);
            $fldtype = $qrow['fldtype'];
            $data = '';
            if ($fldtype == 'G') {
                if ($_POST["G1_$prefix$qcode"]) {
                    $data = $_POST["G1_$prefix$qcode"] * 7 + $_POST["G2_$prefix$qcode"];
                }
            } else {
                $data = $_POST["$prefix$qcode"];
            }

            if (!isset($data) || $data === '') {
                continue;
            }

            if (!is_array($data)) {
                $data = array($data);
            }

            foreach ($data as $datum) {
                // Note this will auto-assign the seq value.
                sqlBeginTrans();
                $answer_seq = sqlQuery("SELECT IFNULL(MAX(answer_seq),0) + 1 AS increment FROM procedure_answers WHERE procedure_order_id = ? AND procedure_order_seq = ? AND question_code = ? ", array($formid, $poseq, $qcode));
                sqlStatement(
                    "INSERT INTO procedure_answers SET " .
                    "procedure_order_id = ?, " .
                    "procedure_order_seq = ?, " .
                    "question_code = ?, " .
                    "answer_seq = ?, " .
                    "answer = ?",
                    array($formid, $poseq, $qcode, $answer_seq['increment'], $datum)
                );
                sqlCommitTrans();
            }
        }
    }

    $alertmsg = '';
    if ($_POST['bn_xmit']) {
        $hl7 = '';
        $alertmsg = gen_hl7_order($formid, $hl7);
        if (empty($alertmsg)) {
            $alertmsg = send_hl7_order($ppid, $hl7);
        }

        if (empty($alertmsg)) {
            sqlStatement("UPDATE procedure_order SET date_transmitted = NOW() WHERE " .
                "procedure_order_id = ?", array($formid));
        }
    }

    formHeader("Redirecting....");
    if ($alertmsg) {
        echo "\n<script language='Javascript'>alert(";
        echo js_escape(xl('Transmit failed') . ': ' . $alertmsg);
        echo ")</script>\n";
    }

    // formJump();
    // formFooter();
    // exit;
}

if ($formid) {
    $row = sqlQuery(
        "SELECT * FROM procedure_order WHERE " .
        "procedure_order_id = ?",
        array($formid)
    );
}

$enrow = sqlQuery(
    "SELECT p.fname, p.mname, p.lname, fe.date FROM " .
    "form_encounter AS fe, forms AS f, patient_data AS p WHERE " .
    "p.pid = ? AND f.pid = p.pid AND f.encounter = ? AND " .
    "f.formdir = 'newpatient' AND f.deleted = 0 AND " .
    "fe.id = f.form_id LIMIT 1",
    array($pid, $encounter)
);
?>
<!DOCTYPE html>
<html>
<head>

    <?php Header::setupHeader(['datetime-picker']); ?>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/style.css">
    <!-- <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/employee_dashboard_style.css"> -->
    <!-- <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/css/emp_info_css.css"> -->
    <script type="text/javascript">
var gbl_formseq;

function initCalendars() {
    var datepicker = {
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    };
    var datetimepicker = {
        <?php $datetimepicker_timepicker = true; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    };
    $('.datepicker').datetimepicker(datepicker);
    $('.datetimepicker').datetimepicker(datetimepicker);
}

function initDeletes(){
    $(".itemDelete").on("click", function(event) {
        deleteRow(event);
    });
}

function deleteRow(event){
    event.preventDefault();event.stopPropagation();
    let target = $( event.currentTarget ).closest('tr').find("input[name^='form_proc_type_desc']").val();
    // let yn = confirm(<?php echo xlj("Confirm to remove item") ?> + "\n" + target);
    // if(yn)
        $( event.currentTarget ).closest(".proc-table").remove();
}
// This invokes the find-procedure-type popup.
// formseq = 0-relative index in the form.
function sel_proc_type(formseq) {
    let f = document.forms[0];
    gbl_formseq = formseq;
    let ptvarname = 'form_proc_type[' + formseq + ']';

    let title = <?php echo xlj("Find Procedure Order"); ?>;
    // This replaces the previous search for an easier/faster order picker tool.
    
    dlgopen('../../orders/find_order_popup.php' +
        '?labid=' + encodeURIComponent(f.form_lab_id.value) +
        '&order=' + encodeURIComponent(f[ptvarname].value) +
        '&formid=' + <?php echo js_url($formid); ?> +
        '&formseq=' + encodeURIComponent(formseq),
        '_blank', 850, 500, '', title);
}

// This is for callback by the find-procedure-type popup.
// Sets both the selected type ID and its descriptive name.
// Also set diagnosis if supplied in configuration and custom test groups.
function set_proc_type(typeid, typename, diagcodes = '', newCnt = 0) {
    let f = document.forms[0];
    let ptvarname = 'form_proc_type[' + gbl_formseq + ']';
    let ptdescname = 'form_proc_type_desc[' + gbl_formseq + ']';
    let ptcodes = 'form_proc_type_diag[' + gbl_formseq + ']';
    f[ptvarname].value = typeid;
    f[ptdescname].value = typename;
    if (diagcodes)
        f[ptcodes].value = diagcodes;
    if (newCnt > 1) {
        gbl_formseq = addProcLine(true);
    }
}

// This is also for callback by the find-procedure-type popup.
// Sets the contents of the table containing the form fields for questions.
function set_proc_html(s, js) {
    document.getElementById('qoetable[' + gbl_formseq + ']').innerHTML = s;
    initCalendars();
}

// New lab selected so clear all procedures and questions from the form.
function lab_id_changed() {
    let f = document.forms[0];
    for (let i = 0; true; ++i) {
        let ix = '[' + i + ']';
        if (!f['form_proc_type' + ix]) break;
        f['form_proc_type' + ix].value = '-1';
        f['form_proc_type_desc' + ix].value = '';
        document.getElementById('qoetable' + ix).innerHTML = '';
    }
}

function addProcedure() {
    $(".procedure-order-container").append($(".procedure-order").clone());
    let newOrder = $(".procedure-order-container .procedure-order:last");
    $(newOrder + " label:first").append("1");
}

function addProcLine(flag = false) {
    let f = document.forms[0];
    let e = document.getElementById("procedure_type_names");
    let prc_name = e.options[e.selectedIndex].value;
    // Compute i = next procedure item index.
    let i = 0;
    for (; f['form_proc_type[' + i + ']']; ++i) ;
    // build new item html.. a hidden html block to clone may be better here.
    let cell = "<div class='pt-4 pb-4 pa_pt-4 pa_pb-4 container-fluid'><table class='table table-condensed proc-table'><tr>" +
        "<td class='procedure-div'><input type='hidden' name='form_proc_order_title[" + i + "]' value='" + prc_name + "'>" +
        "<input type='text' class='form-control' name='form_proc_type_desc[" + i + "]' onclick='sel_proc_type(" + i + ")' " +
        "onfocus='this.blur()' title='<?php echo xla('Click to select the desired procedure'); ?>' placeholder='<?php echo xla('Click to select the desired procedure'); ?>'style='cursor:pointer;cursor:hand' readonly /> " +
        "<input type='hidden' name='form_proc_type[" + i + "]' value='-1' /></td>" +
        "<td class='diagnosis-div'><input type='text' class='form-control' name='form_proc_type_diag[" + i + "]' onclick='sel_related(this.name)'" +
        "title='<?php echo xla('Click to add a diagnosis'); ?>' placeholder='<?php echo xla('Click to add a diagnosis'); ?>' onfocus='this.blur()' style='cursor:pointer;cursor:hand' readonly /></td>" +    
        "<td ><img src='<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg' alt='' class='xxx pr-2'><img src='<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg' onclick='deleteRow(event)' class='itemDelete' alt=''></td>"+
        "<td><div id='qoetable[" + i + "]'></div></td></tr></table></div>";

    $(".procedure-order-container").append(cell); // add the new item to procedures list

    if (!flag) {// flag true indicates add procedure item from custom group callback with current index.
        sel_proc_type(i);
        return false;
    } else {
        return i;
    }
}

// The name of the form field for find-code popup results.
var rcvarname;

// This is for callback by the find-code popup.
// Appends to or erases the current list of related codes.
function set_related(codetype, code, selector, codedesc) {
    var f = document.forms[0];
    var s = f[rcvarname].value;
    if (code) {
        if (s.length > 0) s += ';';
        s += codetype + ':' + code;
    } else {
        s = '';
    }
    f[rcvarname].value = s;
}

// This invokes the find-code popup.
function sel_related(varname) {
    rcvarname = varname;
    // codetype is just to make things easier and avoid mistakes.
    // Might be nice to have a lab parameter for acceptable code types.
    // Also note the controlling script here runs from interface/patient_file/encounter/.
    let title = <?php echo xlj("Select Diagnosis Codes"); ?>;
    dlgopen('find_code_dynamic.php?codetype=' + <?php echo js_url(collect_codetypes("diagnosis", "csv")); ?>, '_blank', 985, 750, '', title);
}

// This is for callback by the find-code popup.
// Returns the array of currently selected codes with each element in codetype:code format.
function get_related() {
    return document.forms[0][rcvarname].value.split(';');
}

// This is for callback by the find-code popup.
// Deletes the specified codetype:code from the currently selected list.
function del_related(s) {
    my_del_related(s, document.forms[0][rcvarname], false);
}

var transmitting = false;

// Issue a Cancel/OK warning if a previously transmitted order is being transmitted again.
function validate(f) {
    <?php if (!empty($row['date_transmitted'])) { ?>
    if (transmitting) {
        if (!confirm(<?php echo xlj('This order was already transmitted on') ?> + ' ' +
                <?php echo js_escape($row['date_transmitted']) ?> + '. ' +
                <?php echo xlj('Are you sure you want to transmit it again?'); ?>)) {
            return false;
        }
    }
    <?php } ?>
    top.restoreSession();
    return true;
}

$(function () {
    // calendars need to be available to init dynamically for procedure item adds.
    initCalendars();
    initDeletes();
});

</script>
    <style>
    @media only screen and (max-width: 768px) {
        [class*="col-"] {
            width: 100%;
            text-align: left !Important;
        }
    }

    .qoe-table {
        margin-bottom: 0px;
    }

    .proc-table {
        margin-bottom: 5px;
    }

    .proc-table .itemDelete {
        width: 25px;
        color: #FF0000;

        cursor:pointer;
        cursor:hand;
    }

    </style>

<!-- PA -->
    <style>
    .css_button:hover, button:hover, input[type=button]:hover, input[type=submit]:hover {
            background: #3C9DC5;
            text-decoration: none;
    }
    .pa_row{
            display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
        padding: 5px;
    }
    .pa_md6, .pa_md3, .pa_md12, .pa_md4{
        width: 100%;
        padding: 0px 15px;
    }
    @media (min-width: 768px)
    {
    .pa_md6 {
        -ms-flex: 0 0 50%;
        flex: 0 0 50%;
        max-width: 50%;
    }
    .pa_md3 {
        -ms-flex: 0 0 25%;
        flex: 0 0 25%;
        max-width: 25%;
    }
    .pa_md4 {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
    }
    .pa_md12 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    }
    /* .pa_mt-2, .pa_my-2 {
        margin-top: .5rem!important;
    }
    .pa_form-control {
        display: block;
        width: 100%;
        height: calc(1.5em + .75rem + 2px);
        padding: .375rem .75rem;
        font-size: 1.5rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    } */
    .pa_pt-4, .pa_py-4 {
        padding-top: 1.5rem!important;
    }

    .pa_pb-2, .pa_py-2 {
        padding-bottom: 1.5rem!important;
    }
    .pa_pb-5, .pa_py-5 {
        padding-bottom: 3rem!important;
    }
    .pa_pt-2, .pa_py-2 {
        padding-top: 1.5rem!important;
    }
    .form-save {
        background-color: #3C9DC5;
        padding: 5px;
        width: 100%;
        border: none;
        outline: none;
        color: white;
    }

    input[type=text] {
        background: #fff;
        border: 1px solid #ced4da;
        padding: .375rem .75rem;
        /* margin: 3px; */
        /* height: calc(1.5em + .75rem + 2px); */
    }
    .mt-2{
        margin-top:.5rem!important;
    }
    .form-control {
        -webkit-box-shadow:none;
    }
    .pb-4, .py-4 {
        padding-bottom: 1.5rem!important;
    }

    th {
        padding: 1.25rem !important;
        vertical-align: top!important;
        border-top: 1px solid #dee2e6!important;
        text-align: inherit;
    }
    .table-form tr:nth-of-type(even) {
        background-color: #88c9ef57;
    }

    input[readonly] {
        background-color: #ffff!important;
    }
    .procedure-div, .diagnosis-div{
        width:28%;
    }
    
    </style>
<!-- //PA -->
<?php
$name = $enrow['fname'] . ' ';
$name .= (!empty($enrow['mname'])) ? $enrow['mname'] . ' ' . $enrow['lname'] : $enrow['lname'];
$date = xl('on') . ' ' . oeFormatShortDate(substr($enrow['date'], 0, 10));
$title = array(xl('Procedure Order for'), $name, $date);
$heading =  join(" ", $title);
?>
<?php
$arrOeUiSettings = array(
    'heading_title' => $heading,
    'include_patient_name' => false,// use only in appropriate pages
    'expandable' => false,
    'expandable_files' => array("fee_sheet_new_xpd"),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => false,
    'help_file_name' => ""
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>
<body class="body_top">
<section>
            <div class="body-content body-content2">
                <div class="container-fluid pb-4 pt-4 pa_pb-4 pa_pt-4">
                    <window-dashboard title="" class="icon-hide">
                    <div class="head-component">
                            <div class="row">
                                <div class="col-6"></div>
                                <div class="col-6">
                                    <p class="text-white head-p">Procedure Order</p>
                                </div>
                            </div>
                        </div>
                        <div class="body-compo" style="height:auto;">
                        <div id="container_div" class="">
                            <form class="form-horizontal" method="post" action="<?php echo $rootdir ?>/forms/procedure_order/new.php?id=<?php echo attr_url($formid); ?>" onsubmit="return validate(this)">
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                            
                                    <!-- PA -->
                            <div class="row pa_row">
                                <div class="col-md-6 pa_md6">
                                    <p>Ordering Provider</p>
                                    
                                    <?php generate_form_field(array('data_type' => 10, 'field_id' => 'provider_id'), $row['provider_id']); ?>
                                </div>
                                <div class="col-md-3 pa_md3">
                                    <p>Order Date</p>
                                    
                                    <input type='text' class='datepicker form-control pa_form-control' name='form_date_ordered' id='form_date_ordered' value="<?php echo attr($row['date_ordered']); ?>" title="<?php echo xla('Date of this order'); ?>"/>
                                </div>
                                <div class="col-md-3 pa_md3">
                                    <p>Send to</p>
                                  
                                    <select name='form_lab_id' onchange='lab_id_changed()' class='form-control mt-2 pa_form-control pa_mt-2'>
                                    <?php
                                    $ppres = sqlStatement("SELECT ppid, name FROM procedure_providers " .
                                        "ORDER BY name, ppid");
                                    while ($pprow = sqlFetchArray($ppres)) {
                                        echo "<option value='" . attr($pprow['ppid']) . "'";
                                        if ($pprow['ppid'] == $row['lab_id']) {
                                            echo " selected";
                                        }
                                        echo ">" . text($pprow['name']) . "</option>";
                                    }
                                    ?>
                                </select>
                                </div>
                            </div>

                            <!--  -->
                            <div class="row pa_row">
                                <div class="col-md-3 pa_md3">
                                    <p>Internal Time Collected</p>
                                    <input class='datetimepicker form-control pa_form-control'
                                       type='text'
                                       name='form_date_collected'
                                       id='form_date_collected'
                                       value="<?php echo attr(substr($row['date_collected'], 0, 16)); ?>"
                                       title="<?php echo xla('Date and time that the sample was collected'); ?>"/>
                                </div>
                                <div class="col-md-3 pa_md3">
                                    <p>Priority</p>
                                   
                                    <?php
                                    generate_form_field(array('data_type' => 1, 'field_id' => 'order_priority',
                                    'list_id' => 'ord_priority'), $row['order_priority']);
                                    ?>
                                </div>
                                <div class="col-md-3 pa_md3">
                                    <p>Status</p>
                                 
                                    <?php
                                    generate_form_field(array('data_type' => 1, 'field_id' => 'order_status',
                                    'list_id' => 'ord_status'), $row['order_status']);
                                    ?>
                                </div>
                                <div class="col-md-3 pa_md3">
                                    <p>History Order</p>
                                 
                                    <?php
                                    $historyOrderOpts = array(
                                    'data_type' => 1,
                                    'field_id' => 'history_order',
                                    'list_id' => 'boolean'
                                    );
                                    generate_form_field($historyOrderOpts, $row['history_order']); 
                                    ?>
                                </div>
                            </div>
                           
                            <div class="row pa_row">
                                <div class="col-md-12 pa_md12">
                                    <p>Clinical History</p>
                                    
                                    <textarea name="form_clinical_hx" id="form_clinical_hx" class="form-control pa_form-control" rows="4"><?php echo attr($row['clinical_hx']); ?></textarea>
                                </div>

                            </div>
                          
                            <!-- PA_last_table -->
                            <div class="procedure-order-container">
                                <div class="row pa_row">
                            <div class="col-md-4 pa_md4">
                                    <p>Procedure Type</p>                                   
                                    <?php $procedure_order_type = getListOptions('order_type', array('option_id', 'title')); ?>
                                    <select name="procedure_type_names" id="procedure_type_names" class='form-control pa_form-control'>
                                    <?php foreach ($procedure_order_type as $ordered_types) { ?>
                                        <option value="<?php echo attr($ordered_types['option_id']); ?>"><?php echo text(xl_list_label($ordered_types['title'])); ?></option>
                                    <?php } ?>
                                </select>
                                <?php                       
                        $oparr = array();
                        if ($formid) {
                            $opres = sqlStatement(
                                "SELECT " .
                                "pc.procedure_order_seq, pc.procedure_code, pc.procedure_name, " .
                                "pc.diagnoses, pc.procedure_order_title, " .
                                "(SELECT pt.procedure_type_id FROM procedure_type AS pt WHERE " .
                                "(pt.procedure_type LIKE 'ord%' OR pt.procedure_type LIKE 'for%') AND pt.lab_id = ? AND " .
                                "pt.procedure_code = pc.procedure_code ORDER BY " .
                                "pt.activity DESC, pt.procedure_type_id LIMIT 1) AS procedure_type_id " .
                                "FROM procedure_order_code AS pc " .
                                "WHERE pc.procedure_order_id = ? " .
                                "ORDER BY pc.procedure_order_seq",
                                array($row['lab_id'], $formid)
                            );
                            while ($oprow = sqlFetchArray($opres)) {
                                $oparr[] = $oprow;
                            }
                        }
                        if (empty($oparr)) {
                            $oparr[] = array('procedure_name' => '');
                        }
                        ?>                       
                    </div>
                    </div>
                        <?php
                        $i = 0;
                        foreach ($oparr as $oprow) {
                            $ptid = -1; 
                            if (!empty($oprow['procedure_type_id'])) {
                                $ptid = $oprow['procedure_type_id'];
                            }
                            ?>
                            <div class="pt-4 pb-4 pa_pt-4 pa_pb-4 container-fluid">
                            <!-- <div class="table-div "> -->
                            <table class="table table-condensed proc-table table-form" id="procedures_item_<?php echo (string) attr($i) ?>">
                            <tbody id="TextBoxContainer10" class="repeat-row ">
                                            <tr>
                                            
                                                <th>Procedure Test</th>
                                                <th>Dignostic Codes</th>
                                                <th>Order Questions</th>
                                                <th></th>

                                            </tr>
                            <tr class="tablerow bodypart10">
                                
                                <td class="procedure-div">
                                    <?php if (empty($formid) || empty($oprow['procedure_order_title'])) : ?>
                                        <input type="hidden" name="form_proc_order_title[<?php echo attr($i); ?>]" value="Procedure">
                                    <?php else : ?>
                                        <input type='hidden' name='form_proc_order_title[<?php echo attr($i); ?>]' value='<?php echo attr($oprow['procedure_order_title']) ?>'>
                                    <?php endif; ?>
                                    <input type='text' name='form_proc_type_desc[<?php echo attr($i); ?>]'
                                           value='<?php echo attr($oprow['procedure_name']) ?>'
                                           onclick="sel_proc_type(<?php echo attr_js($i); ?>)"
                                           onfocus='this.blur()'
                                           title='<?php echo xla('Click to select the desired procedure'); ?>'
                                           placeholder='<?php echo xla('Click to select the desired procedure'); ?>'
                                           style='cursor:pointer;cursor:hand' class='form-control pa_form-control' readonly/>
                                    <input type='hidden' name='form_proc_type[<?php echo attr($i); ?>]' value='<?php echo attr($ptid); ?>'/>
                                </td>
                                <td class="diagnosis-div">
                                    <input class='form-control pa_form-control' type='text' name='form_proc_type_diag[<?php echo attr($i); ?>]'
                                           value='<?php echo attr($oprow['diagnoses']) ?>' onclick='sel_related(this.name)'
                                           title='<?php echo xla('Click to add a diagnosis'); ?>'
                                           placeholder='<?php echo xla('Click to select the desired procedure'); ?>'
                                           onfocus='this.blur()'
                                           style='cursor:pointer;cursor:hand' readonly/>
                                </td>
                                <td>
                                    <input class='form-control pa_form-control' type='text'>
                                    <div class="table-responsive" id='qoetable[<?php echo attr($i); ?>]'>
                                        <?php
                                        $qoe_init_javascript = '';
                                        echo generate_qoe_html($ptid, $formid, $oprow['procedure_order_seq'], $i);
                                        if ($qoe_init_javascript) {
                                            echo "<script language='JavaScript'>$qoe_init_javascript</script>";
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td ><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/edit-text.svg" alt="" class="xxx pr-2"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/delete.svg" class="itemDelete" alt=""></td>
                            </tr>
                                </tbody>
                            </table>
                                    <!-- </div> -->
                                    
                                    </div>
                            <?php
                            ++$i;
                        }
                        ?>                                            
                        </div>
                        <div>
                                    <div class="text-center">
                                        <img onclick="addProcLine()" src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/addmore.svg" id="insertrow" alt="">
                                    </div>
                                    <div class="text-center">
                                        <p class="fs-14">Add New</p>
                                    </div>
                                </div>
                            
                            <div class="pt-4 pb-2 pa_pt-4 pa_pb-2 col-md-12">
                                <button type="submit" class="form-save" name='bn_save' value="save" onclick='transmitting = false;'><?php echo xlt('Save'); ?></button>
                           
                            </div><br>
                            <div class="pt-2 pb-5 pa_pt-2 pa_pb-5 col-md-12">
                                <button type="submit" class="form-save" name='bn_xmit' value="transmit" onclick='transmitting = true;'><?php echo xlt('Save and Send'); ?></button>

                            </div>



                
                <!-- <fieldset>
                    <legend><?php xl('Procedure Order Details', 'e'); ?></legend>
                    <div class="col-md-12 procedure-order-container table-responsive">
                        <div class="form-group">
                            <?php $procedure_order_type = getListOptions('order_type', array('option_id', 'title')); ?>
                            <label for="procedure_type_names" class="control-label col-sm-2 col-sm-offset-3"><?php echo xlt('Procedure Type'); ?></label>
                            <div class="col-sm-3">
                                <select name="procedure_type_names" id="procedure_type_names" class='form-control'>
                                    <?php foreach ($procedure_order_type as $ordered_types) { ?>
                                        <option value="<?php echo attr($ordered_types['option_id']); ?>"><?php echo text(xl_list_label($ordered_types['title'])); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    
                        <?php                       
                        $oparr = array();
                        if ($formid) {
                            $opres = sqlStatement(
                                "SELECT " .
                                "pc.procedure_order_seq, pc.procedure_code, pc.procedure_name, " .
                                "pc.diagnoses, pc.procedure_order_title, " .                               
                                "(SELECT pt.procedure_type_id FROM procedure_type AS pt WHERE " .
                                "(pt.procedure_type LIKE 'ord%' OR pt.procedure_type LIKE 'for%') AND pt.lab_id = ? AND " .
                                "pt.procedure_code = pc.procedure_code ORDER BY " .
                                "pt.activity DESC, pt.procedure_type_id LIMIT 1) AS procedure_type_id " .
                                "FROM procedure_order_code AS pc " .
                                "WHERE pc.procedure_order_id = ? " .
                                "ORDER BY pc.procedure_order_seq",
                                array($row['lab_id'], $formid)
                            );
                            while ($oprow = sqlFetchArray($opres)) {
                                $oparr[] = $oprow;
                            }
                        }
                        if (empty($oparr)) {
                            $oparr[] = array('procedure_name' => '');
                        }
                        ?>
                        <?php
                        $i = 0;
                        foreach ($oparr as $oprow) {
                            $ptid = -1; 
                            if (!empty($oprow['procedure_type_id'])) {
                                $ptid = $oprow['procedure_type_id'];
                            }
                            ?>
                            <table class="table table-condensed proc-table" id="procedures_item_<?php echo (string) attr($i) ?>">
                                <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th><?php echo xlt('Procedure Test'); ?></th>
                                    <th><?php echo xlt('Diagnosis Codes'); ?></th>
                                    <th><?php echo xlt("Order Questions"); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                            <tr>
                                <td class="itemDelete"><i class="fa fa-remove fa-lg"></i></td>
                                <td class="procedure-div">
                                    <?php if (empty($formid) || empty($oprow['procedure_order_title'])) : ?>
                                        <input type="hidden" name="form_proc_order_title[<?php echo attr($i); ?>]" value="Procedure">
                                    <?php else : ?>
                                        <input type='hidden' name='form_proc_order_title[<?php echo attr($i); ?>]' value='<?php echo attr($oprow['procedure_order_title']) ?>'>
                                    <?php endif; ?>
                                    <input type='text' name='form_proc_type_desc[<?php echo attr($i); ?>]'
                                           value='<?php echo attr($oprow['procedure_name']) ?>'
                                           onclick="sel_proc_type(<?php echo attr_js($i); ?>)"
                                           onfocus='this.blur()'
                                           title='<?php echo xla('Click to select the desired procedure'); ?>'
                                           placeholder='<?php echo xla('Click to select the desired procedure'); ?>'
                                           style='cursor:pointer;cursor:hand' class='form-control' readonly/>
                                    <input type='hidden' name='form_proc_type[<?php echo attr($i); ?>]' value='<?php echo attr($ptid); ?>'/>
                                </td>
                                <td class="diagnosis-div">
                                    <input class='form-control' type='text' name='form_proc_type_diag[<?php echo attr($i); ?>]'
                                           value='<?php echo attr($oprow['diagnoses']) ?>' onclick='sel_related(this.name)'
                                           title='<?php echo xla('Click to add a diagnosis'); ?>'
                                           onfocus='this.blur()'
                                           style='cursor:pointer;cursor:hand' readonly/>
                                </td>
                                <td>
                                    <div class="table-responsive" id='qoetable[<?php echo attr($i); ?>]'>
                                        <?php
                                        $qoe_init_javascript = '';
                                        echo generate_qoe_html($ptid, $formid, $oprow['procedure_order_seq'], $i);
                                        if ($qoe_init_javascript) {
                                            echo "<script language='JavaScript'>$qoe_init_javascript</script>";
                                        }
                                        ?>
                                    </div>
                                </td>
                            </tr>
                                </tbody>
                            </table>
                            <?php
                            ++$i;
                        }
                        ?>
                        </div>
                </fieldset> -->
                <!-- <div class="form-group clearfix">
                    <div class="col-sm-12 text-left position-override">
                        <div class="btn-group btn-group-pinch" role="group">
                            <button type="button" class="btn btn-default btn-add" onclick="addProcLine()"><?php echo xlt('Add Procedure'); ?></button>
                            <button type="submit" class="btn btn-default btn-save" name='bn_save' value="save" onclick='transmitting = false;'><?php echo xlt('Save'); ?></button>
                            <button type="submit" class="btn btn-default btn-transmit" name='bn_xmit' value="transmit" onclick='transmitting = true;'><?php echo xlt('Save and Send'); ?></button>
                           </div>
                    </div>
                </div> -->
            </form>
<?php $oemr_ui->oeBelowContainerDiv();?>
</div>
</div>
</window-dashboard>
</div>
</div>
</section>

<script>
$(document).ready(function() {

makeTextboxEditable1();
});

function makeTextboxEditable1() {
$(".xxx").click(function() {
    $(this)
        .closest(".tablerow")
        .addClass("activatetextarea1")
});
}
</script>
</body>
</html>
