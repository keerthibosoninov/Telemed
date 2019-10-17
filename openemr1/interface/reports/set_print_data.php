<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$data=$_POST['sessionval'];
if($data)
  {
    $_SESSION['print_data']=($data);
//print_r($_SESSION['print_data']);
  }
else
{
print 2;
}

?>
