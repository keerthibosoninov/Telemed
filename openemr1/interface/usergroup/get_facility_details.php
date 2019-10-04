<?php

require_once("../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/erx_javascript.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

if (isset($_POST["my_fid"])) {
    $my_fid = $_POST["my_fid"];
}
$my_fid =3;
 $facility = $facilityService->getById($my_fid);
echo attr($facility['name']) ."!";

?>