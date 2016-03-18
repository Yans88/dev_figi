<?php

ob_start();
define('FIGIPASS', true);
define('LOG_ENABLED', true);

require '../common.php';
require '../user/user_util.php';
require 'item_util.php';
require 'item_sync_util.php';

$_cmd  = isset($_GET['cmd']) ? strtolower($_GET['cmd']) : null;
$_dept = isset($_POST['id_department']) ? $_POST['id_department'] : 0;
$_dept = isset($_GET['dept']) ? $_GET['dept'] : $_dept;
if (isset($_GET['json']))
	define('JSON', true);
else
	define('JSON', false);

ob_clean();
error_log(serialize($_REQUEST));

switch($_cmd){
case 'get-stat':
case 'get-status': $response = status_export(); break;
case 'get-loc':
case 'get-location': $response = location_export(); break;
case 'get-dept':
case 'get-department': $response = department_export(); break;
case 'get-cat':
case 'get-category': $response = category_export($_dept); break;
case 'get-item': $response = get_department_item(); break;
/* obsolate 
case 'store-item': $response = item_import($_dept); break; 
*/

case 'start-stocktake': $response = stocktake_start(); break;
case 'end-stocktake': $response = stocktake_end(); break;
case 'authenticate': $response = authenticate_me($_REQUEST); break;
default: $response = 'invalid command!';
}
echo $response;
ob_end_flush();
exit;

?>
