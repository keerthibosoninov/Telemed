<?php
$data=$_POST['sessionval'];
if($data)
{
    $data = trim(preg_replace('/\s+/', ' ', $data));
    $result=urlencode($data);
    print $result;
}
else
{
print 2;
}

?>