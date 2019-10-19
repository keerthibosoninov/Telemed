<?php

require_once('../../globals.php');
$eid= $_POST['eid'];
if ($_POST['form_action'] == "delete") { //    DELETE EVENT(s)
        // =======================================
        //  multi providers event
        // =======================================
        if ($GLOBALS['select_multi_providers']) {
            // what is multiple key around this $eid?
            $row = sqlQuery("SELECT pc_multiple FROM openemr_postcalendar_events WHERE pc_eid = ?", array($eid));

            // obtain current list of providers regarding the multiple key
            $providers_current = array();
            $up = sqlStatement("SELECT pc_aid FROM openemr_postcalendar_events WHERE pc_multiple=?", array($row['pc_multiple']));
            while ($current = sqlFetchArray($up)) {
                $providers_current[] = $current['pc_aid'];
            }

            // establish a WHERE clause
            if ($row['pc_multiple']) {
                $whereClause = "pc_multiple = '{$row['pc_multiple']}'";
            } else {
                $whereClause = "pc_eid = '$eid'";
            }

            if ($_POST['recurr_affect'] == 'current') {
                // update all existing event records to exlude the current date
                foreach ($providers_current as $provider) {
                    // update the provider's original event
                    // get the original event's repeat specs
                    $origEvent = sqlQuery("SELECT pc_recurrspec FROM openemr_postcalendar_events ".
                    " WHERE pc_aid <=> ? AND pc_multiple=?", array($provider,$row['pc_multiple']));
                    $oldRecurrspec = unserialize($origEvent['pc_recurrspec'], ['allowed_classes' => false]);
                    $selected_date = date("Y-m-d", strtotime($_POST['selected_date']));
                    if ($oldRecurrspec['exdate'] != "") {
                        $oldRecurrspec['exdate'] .= ",".$selected_date;
                    } else {
                            $oldRecurrspec['exdate'] .= $selected_date;
                    }

                    // mod original event recur specs to exclude this date
                        sqlStatement("UPDATE openemr_postcalendar_events SET " .
                        " pc_recurrspec = ? ".
                        " WHERE ". $whereClause, array(serialize($oldRecurrspec)));
                }
            } else if ($_POST['recurr_affect'] == 'future') {
                // update all existing event records to stop recurring on this date-1
                $selected_date = date("Y-m-d", (strtotime($_POST['selected_date'])-24*60*60));
                foreach ($providers_current as $provider) {
                    // In case of a change in the middle of the event
                    if (strcmp($_POST['event_start_date'], $_POST['selected_date'])!=0) {
                        // update the provider's original event
                        sqlStatement("UPDATE openemr_postcalendar_events SET " .
                        " pc_enddate = ? " .
                        " WHERE " . $whereClause, array($selected_date));
                    } else { // In case of a change in the event head
                        sqlStatement("DELETE FROM openemr_postcalendar_events WHERE ".$whereClause);
                    }
                }
            } else {
                // really delete the event from the database
                sqlStatement("DELETE FROM openemr_postcalendar_events WHERE ".$whereClause);
            }
        } else { //  single provider event
            if ($_POST['recurr_affect'] == 'current') {
                // mod original event recur specs to exclude this date
                // get the original event's repeat specs
                $origEvent = sqlQuery("SELECT pc_recurrspec FROM openemr_postcalendar_events WHERE pc_eid = ?", array($eid));
                $oldRecurrspec = unserialize($origEvent['pc_recurrspec'], ['allowed_classes' => false]);
                $selected_date = date("Ymd", strtotime($_POST['selected_date']));
                if ($oldRecurrspec['exdate'] != "") {
                    $oldRecurrspec['exdate'] .= ",".$selected_date;
                } else {
                    $oldRecurrspec['exdate'] .= $selected_date;
                }

                    sqlStatement("UPDATE openemr_postcalendar_events SET " .
                    " pc_recurrspec = ? ".
                    " WHERE pc_eid = ?", array(serialize($oldRecurrspec),$eid));
            } else if ($_POST['recurr_affect'] == 'future') {
                // mod original event to stop recurring on this date-1
                $selected_date = date("Ymd", (strtotime($_POST['selected_date'])-24*60*60));
                sqlStatement("UPDATE openemr_postcalendar_events SET " .
                    " pc_enddate = ? ".
                    " WHERE pc_eid = ?", array($selected_date,$eid));
            } else {
                // fully delete the event from the database
                sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?", array($eid));
            }
        }
    }

    ?>