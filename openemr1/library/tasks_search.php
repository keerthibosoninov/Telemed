<?php


require_once("../interface/globals.php");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");


    $active = 'all';
    $show_all = 'yes';
    $search_like=$_POST['search'];
    $date_task_n=$_POST['task_date'];

    $date_task=date('Y-m-d',strtotime($date_task_n));
    $result = getPnotesByUser($active, $show_all, $_SESSION['authUser'], false, $sortby, $sortorder, $begin, $listnumber,$search_like,$date_task);
    if($result){
       
            while ($myrow = sqlFetchArray($result)) {
           
                $name = $myrow['user'];
                $name = $myrow['users_lname'];
                if ($myrow['users_fname']) {
                    $name .= ", " . $myrow['users_fname'];
                }
                $patient = $myrow['pid'];
                if ($patient > 0) {
                    $patient = $myrow['patient_data_lname'];
                    if ($myrow['patient_data_fname']) {
                        $patient .= ", " . $myrow['patient_data_fname'];
                    }
                } else {
                    $patient = "* " . xl('Patient must be set manually') . " *";
                }

                $starttime=$myrow['date'];

                echo "<div class='row task-box'>
                    <div class='col-3 left-date text-white text-center'>
                        <h5>  ".date('G:i',strtotime($starttime))." &nbsp;  ".text(date('a', $starttime))." </h5>
                        <h5>  ".date('d',strtotime($starttime))." </h5>
                        <h6> ". date('D',strtotime($starttime))." </h6>
                    </div>
                    <div class='col-7 task-para'>
                        <div class=''>
                        <p>".$myrow['title']."</p>
                        </div>
                    </div>
                    <div class='col-2 task-para'>
                        <div class='d-block pt-4'>
                            <a href=''><img src='".$GLOBALS['assets_static_relative']."/img/seen.svg' alt=''></a>
                            <p>view</p>
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