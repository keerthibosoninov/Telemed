<?php
/**
 * add or edit a medical problem.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/lists.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
require_once($GLOBALS['srcdir'].'/csv_like_join.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;



function QuotedOrNull($fld)
{
    if ($fld) {
        return "'".add_escape_custom($fld)."'";
    }

    return "NULL";
}







if ($_POST['submit_form']) {



    $issue = $_POST['issue'];
    $thispid = $_POST['thispid'];
    $thisenc = $_POST['thisenc'];



    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $i = 0;
    $text_type = "unknown";
    foreach ($ISSUE_TYPES as $key => $value) {
        if ($i++ == $_POST['form_type']) {
            $text_type = $key;
        }
    }

    $form_begin = fixDate($_POST['form_begin'], '');
    $form_end   = fixDate($_POST['form_end'], '');

    $form_injury_part = $_POST['form_medical_system'];
    $form_injury_type = $_POST['form_medical_type'];

    if ($issue) {
        $query = "UPDATE lists SET " .
        "type = '"        . add_escape_custom($text_type)                  . "', " .
        "title = '"       . add_escape_custom($_POST['form_title'])        . "', " .
        "comments = '"    . add_escape_custom($_POST['form_comments'])     . "', " .
        "begdate = "      . QuotedOrNull($form_begin)   . ", "  .
        "enddate = "      . QuotedOrNull($form_end)     . ", "  .
        "returndate = "   . QuotedOrNull($form_return)  . ", "  .
        "diagnosis = '"   . add_escape_custom($_POST['form_diagnosis'])    . "', " .
        "occurrence = '"  . add_escape_custom($_POST['form_occur'])        . "', " .
        "classification = '" . add_escape_custom($_POST['form_classification']) . "', " .
        "reinjury_id = '" . add_escape_custom($_POST['form_reinjury_id'])  . "', " .
        "referredby = '"  . add_escape_custom($_POST['form_referredby'])   . "', " .
        "injury_grade = '" . add_escape_custom($_POST['form_injury_grade']) . "', " .
        "injury_part = '" . add_escape_custom($form_injury_part)           . "', " .
        "injury_type = '" . add_escape_custom($form_injury_type)           . "', " .
        "outcome = '"     . add_escape_custom($_POST['form_outcome'])      . "', " .
        "destination = '" . add_escape_custom($_POST['form_destination'])   . "', " .
        "reaction ='"     . add_escape_custom($_POST['form_reaction'])     . "', " .
        "severity_al ='"     . add_escape_custom($_POST['form_severity_id'])     . "', " .
        "list_option_id ='"     . add_escape_custom($_POST['form_title_id'])     . "', " .
        "substance_al ='"     . add_escape_custom($_POST['substance'])     . "', " .
        "reaction_al ='"     . add_escape_custom($_POST['reaction_al'])     . "', " .
        "erx_uploaded = '0', " .
        "modifydate = NOW() " .
        "WHERE id = '" . add_escape_custom($issue) . "'";
        sqlStatement($query);
        if ($text_type == "medication" && enddate != '') {
            sqlStatement('UPDATE prescriptions SET '
            . 'medication = 0 where patient_id = ? '
            . " and upper(trim(drug)) = ? "
            . ' and medication = 1', array($thispid,strtoupper($_POST['form_title'])));
        }
    } else {

      
      echo  $issue = sqlInsert("INSERT INTO lists ( " .
        "date, pid, type, title, activity, comments, begdate, enddate, returndate, " .
        "diagnosis, occurrence, classification, referredby, user, groupname, " .
        "outcome, destination, reinjury_id, injury_grade, injury_part, injury_type, " .
        "reaction, severity_al, substance_al,reaction_al,list_option_id " .
        ") VALUES ( " .
        "NOW(), " .
        "'" . add_escape_custom($thispid) . "', " .
        "'" . add_escape_custom($text_type)                 . "', " .
        "'" . add_escape_custom($_POST['form_title'])       . "', " .
        "1, "                            .
        "'" . add_escape_custom($_POST['form_comments'])    . "', " .
        QuotedOrNull($form_begin)        . ", "  .
        QuotedOrNull($form_end)        . ", "  .
        QuotedOrNull($form_return)       . ", "  .
        "'" . add_escape_custom($_POST['form_diagnosis'])   . "', " .
        "'" . add_escape_custom($_POST['form_occur'])       . "', " .
        "'" . add_escape_custom($_POST['form_classification']) . "', " .
        "'" . add_escape_custom($_POST['form_referredby'])  . "', " .
        "'" . add_escape_custom($$_SESSION['authUser'])     . "', " .
        "'" . add_escape_custom($$_SESSION['authProvider']) . "', " .
        "'" . add_escape_custom($_POST['form_outcome'])     . "', " .
        "'" . add_escape_custom($_POST['form_destination']) . "', " .
        "'" . add_escape_custom($_POST['form_reinjury_id']) . "', " .
        "'" . add_escape_custom($_POST['form_injury_grade']) . "', " .
        "'" . add_escape_custom($form_injury_part)          . "', " .
        "'" . add_escape_custom($form_injury_type)          . "', " .
        "'" . add_escape_custom($_POST['form_reaction'])         . "', " .
        "'" . add_escape_custom($_POST['form_severity_id'])         . "', " .
        "'" . add_escape_custom($_POST['substance'])         . "', " .
        "'" . add_escape_custom($_POST['reaction_al'])         . "', " .
        "'" . add_escape_custom($_POST['form_title_id'])         . "' " .
        ")");
    }


    //  inserting reaction
    if($_POST['reaction_al']){
        $reaction_al=$_POST['reaction_al'];
        $title=ucwords($reaction_al);
        $pres = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND option_id = ? ", array('reaction',$reaction_al));
        if (sqlNumRows($pres) == 0) {
           
            $maxseq = sqlStatement("SELECT max(seq) as seq FROM list_options WHERE list_id = ? ", array('reaction'));
            $maxseq_m = sqlFetchArray($maxseq);
            // print_r( $maxseq_m);
            $date=date('Y-m-d H:i:s');
    
            $maxseq_max= $maxseq_m['seq']+ 10;
    
    
          echo  sqlInsert("INSERT INTO list_options (list_id, option_id, title, seq, edit_options, timestamp) VALUES ('reaction','$reaction_al','$title',$maxseq_max,1,'$date')"); 
        }
    }

    


   
  // For record/reporting purposes, place entry in lists_touch table.
    setListTouch($thispid, $text_type);

    if ($text_type == 'ippf_gcac') {
        issue_ippf_gcac_save($issue);
    }

    if ($text_type == 'contraceptive') {
        issue_ippf_con_save($issue);
    }

  // If requested, link the issue to a specified encounter.
    if ($thisenc) {
        $query = "INSERT INTO issue_encounter ( " .
        "pid, list_id, encounter " .
        ") VALUES ( ?,?,? )";
        sqlStatement($query, array($thispid,$issue,$thisenc));
    }

    $tmp_title = $ISSUE_TYPES[$text_type][2] . ": $form_begin " .
    substr($_POST['form_title'], 0, 40);


}



