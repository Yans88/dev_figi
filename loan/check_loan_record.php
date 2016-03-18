<?php

include '../util.php';
include '../common.php';

$asset_no = !empty($_POST['asset']) ? $_POST['asset'] : null;
$nric = !empty($_POST['nric']) ? $_POST['nric'] : null;
if ($asset_no != null){
    $query = "SELECT li.id_loan, li.id_item, i.asset_no, status, lr.requester, u.full_name, u.nric 
			FROM loan_item li 
			LEFT JOIN item i ON i.id_item = li.id_item 
			LEFT JOIN loan_request lr ON lr.id_loan = li.id_loan 
			LEFT JOIN category ON lr.id_category = category.id_category
			LEFT JOIN user u ON u.id_user = lr.requester 
			WHERE category.category_type = 'EQUIPMENT' AND status IN ( 'LOANED', 'PARTIAL_IN') 
			AND i.asset_no = '".htmlspecialchars($asset_no, ENT_QUOTES)."' AND u.nric='".htmlspecialchars($nric,ENT_QUOTES)."'";
    $rs = mysql_query($query);
    
    if ($rs && (mysql_num_rows($rs) > 0)){
        $drs = mysql_fetch_assoc($rs);
		echo json_encode($drs['id_loan']);
    }
}
echo '';
