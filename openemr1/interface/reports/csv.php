<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Disposition: attachment; filename=patient_list_creation.csv");
header("Content-Description: File Transfer");

$html=$_SESSION['print_data'];
// CSV headers:
echo '"' . 'Date' . '",';
echo '"' . 'Employee' . '",';
echo '"' . 'ID' . '",';
echo '"' . 'Age' . '",';
echo '"' . 'Gender' . '",';
echo '"' . 'Address' . '",';
echo '"' . 'Provider' . '",';
echo '"' . 'Contact' . '"' . "\n";

 foreach ($html as $patKey => $patDetailVal) {

echo '"' . $patDetailVal['patient_date'] . '",';
echo '"' . $patDetailVal['patient_name'] . '",';
echo '"' . $patDetailVal['patient_id'] . '",';
echo '"' . $patDetailVal['patient_age'] . '",';
echo '"' . $patDetailVal['patient_sex'] . '",';
echo '"' . $patDetailVal['p_address'] . '",';
echo '"' . $patDetailVal['users_provider'] . '",';
echo '"' . $patDetailVal['p_contact'] . '"' . "\n";
 }

?>
