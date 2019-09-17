<?php

require_once("../interface/globals.php");
require_once "$srcdir/appointments.inc.php";


        $from_date=$to_date=date('Y-m-d');
        $search_like=$_POST['search'];
        $form_apptstatus=$_POST['form_apptstatus'];

        $patient=$provider=$facility= $with_out_provider=$with_out_facility=null;
        $appointments = fetchAppointments($from_date, $to_date, $patient, $provider, $facility, $form_apptstatus, $with_out_provider, $with_out_facility, $form_apptcat,false,0,null,null,$search_like);

        if ($show_available_times) {
            $availableSlots = getAvailableSlots($from_date, $to_date, $provider, $facility);
                                         $appointments = array_merge($appointments, $availableSlots);
        }

        // print_r( $appointments);

                                   
        $appointments = sortAppointments($appointments, $form_orderby);
        $pid_list = array();  // Initialize list of PIDs for Superbill option
        $apptdate_list = array(); // same as above for the appt details
        $totalAppontments = count($appointments);
        if($appointments){
            foreach ($appointments as $appointment) {
                array_push($pid_list, $appointment['pid']);
                array_push($apptdate_list, $appointment['pc_eventDate']);
                $patient_id = $appointment['pid'];
                $docname  = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];
        
                $errmsg  = "";
                $pc_apptstatus = $appointment['pc_apptstatus'];
                
                $starttime=$appointment['pc_startTime'];
                $startdate=$appointment['pc_eventDate'];

            echo "<div class='row task-box'>
            <div class='col-3 left-date text-white text-center'>

            
            
            
                <h5>  ".date('G:i',strtotime($starttime))." &nbsp;  ".text(date('a', $starttime))." </h5>
                <h5>  ".date('d',strtotime($starttime))." </h5>
                <h6> ". date('D',strtotime($starttime))." </h6>
            </div>
            <div class='col-7 task-para'>
                <div class=''>
                    <p>Meeting with   ".text($appointment['fname'] . ' ' . $appointment['lname'])  ."</p>
                </div>
            </div>
            <div class='col-2 task-para'>
                <div class='task-icon'>
                    <a href=''><img src='".$GLOBALS['assets_static_relative']."/img/ico-users.svg' alt=''></a>
                    <a href=''><img src='".$GLOBALS['assets_static_relative']." /img/ico-nouser.svg' alt=''></a>
                </div>
            </div>
                </div>";
            }
        } else{
            echo "<div class='row task-box no-result'>
            <h5 style='text-align: center;'>No Result</h5>
            </div>
            ";
        }  

?>