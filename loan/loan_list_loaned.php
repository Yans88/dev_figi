<script>
function fill(id, thisValue, id_item) {
	$('#'+id).val(thisValue);
	$('#myid_item').val(id_item);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
    var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("loan/search_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", searchBy: ""+$('#searchby').val()+""}, function(data){
			if(data.length >0) {
				
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
				var pos =  $('#searchtext').offset();                       
				$('#suggestions').css('position', 'absolute');
				$('#suggestions').offset({left:pos.left});
			} else
                        $('#suggestions').fadeOut();
		});
	}
}

</script>

<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_do = isset($_GET['do']) ? $_GET['do'] : null;

if ($_do == 'export'){
    export_request_status(LOANED);
	}

$_searchby = !empty($_POST['searchby']) ? $_POST['searchby'] : null;
$_searchtext = !empty($_POST['searchtext']) ? $_POST['searchtext'] : null;
$myid_item = !empty($_POST['myid_item']) ? $_POST['myid_item'] : null;
$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_loaned_request();
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0) $_start = ($_page-1) * $_limit;
if($_searchby == 'issued_to'){
	$myid_item = $_searchtext;
}

$data = get_loaned_request($_start, $_limit,'return_date','ASC', $myid_item, $_searchby);

function count_loaned_request()
{
    $result = 0;
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    $query  = "SELECT COUNT(*) 
             FROM loan_request lr 
             LEFT JOIN category ON lr.id_category = category.id_category 
             LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan  
             WHERE category_type = 'EQUIPMENT' AND status IN ('LOANED', 'PARTIAL_IN') ";
    if (!SUPERADMIN)
        $query .= " AND category.id_department = $dept ";

	$rs = mysql_query($query);	
	//error_log(mysql_error().$query);
    if ($rs && (mysql_num_rows($rs)>0)){
		$rec = mysql_fetch_row($rs);
			$result = $rec[0];    
	}
	return $result;
}


function get_loaned_request($start = 0, $limit = RECORD_PER_PAGE, $ordby = 'return_date', $orddir = 'ASC', $id_item = null, $_searchby=null)
{
    $result = array();
    if (in_array($ordby, array('request_date', 'loan_date', 'end_loan')))
        $ordby = 'lr.' . $ordby;
    else if (in_array($ordby, array('return_date', 'loan_date'))) $ordby = 'lo.'.$ordby;
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    /**$query  = "SELECT lr.id_loan, date_format(lo.loan_date, '%d-%b-%Y %H:%i') as start_loan, date_format(lo.return_date, '%d-%b-%Y %H:%i') as end_loan, 
             date_format(request_date, '%d-%b-%Y %H:%i') as request_date, purpose, 
             user.full_name as requester, category_name, remark, status, long_term, loan_remark, issue_remark, lo.quick_issue,
			 (SELECT COUNT(*) FROM loan_item WHERE id_loan=lr.id_loan) AS quantity,
			 (SELECT COUNT(*) FROM loan_return_item WHERE id_loan=lr.id_loan) AS return_quantity, li.id_item 
             FROM loan_request lr 
             LEFT JOIN user ON requester = user.id_user 
             LEFT JOIN loan_item li ON li.id_loan = lr.id_loan
             LEFT JOIN category ON lr.id_category = category.id_category 
             LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan  
             LEFT JOIN loan_out lo ON lo.id_loan = lr.id_loan  
             WHERE category_type = 'EQUIPMENT' AND status IN ( 'LOANED', 'PARTIAL_IN') ";
			 **/
	
	$query  = "SELECT lr.id_loan, date_format(lo.loan_date, '%d-%b-%Y %H:%i') as start_loan, date_format(lo.return_date, '%d-%b-%Y %H:%i') as end_loan, 
             date_format(request_date, '%d-%b-%Y %H:%i') as request_date, purpose, 
             user.full_name as requester, category_name, remark, status, long_term, loan_remark, issue_remark, lo.quick_issue,
			 (SELECT COUNT(*) FROM loan_item WHERE id_loan=lr.id_loan) AS quantity,
			 (SELECT COUNT(*) FROM loan_return_item WHERE id_loan=lr.id_loan) AS return_quantity 
             FROM loan_request lr 
             LEFT JOIN user ON requester = user.id_user 
             LEFT JOIN category ON lr.id_category = category.id_category 
             LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan  
             LEFT JOIN loan_out lo ON lo.id_loan = lr.id_loan  
             WHERE category_type = 'EQUIPMENT' AND status IN ( 'LOANED', 'PARTIAL_IN') ";
	
	if(!empty($id_item)){
		if($_searchby == 'issued_to'){
			$query .= " AND user.full_name = '$id_item' ";
		}else
			$query .= " AND li.id_item = $id_item ";			
	}
    if (!SUPERADMIN)
        $query .= " AND category.id_department = $dept ";

	$query .= " ORDER BY $ordby $orddir LIMIT $start, $limit";
	
	$rs = mysql_query($query);	
	//print_r(mysql_error().$query);
    if ($rs && (mysql_num_rows($rs)>0))
		while ($rec = mysql_fetch_assoc($rs))
			$result[] = $rec;    
	return $result;
}

function loaned_items($id = 0, $colorize=false)
{
    $result = '';
    $query = "SELECT li.id_item, i.asset_no, i.serial_no, lr.id_item return_id 
                FROM loan_item li 
                LEFT JOIN loan_return_item lr ON lr.id_loan= li.id_loan AND lr.id_item = li.id_item 
                LEFT JOIN item i ON li.id_item = i.id_item 
                WHERE li.id_loan = $id  ";
	//$query .= $searchBy;
    $rs = mysql_query($query);
   // error_log(mysql_error().$query);
    $rows = array();
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs)){
            //$rows[] = $rec['asset_no'];
			$pr_class = null;
			if ($colorize && $rec['id_item']==$rec['return_id']) $pr_class = 'partial_in'; 
			$rows[] = '<span class="'.$pr_class.'">'.$rec['asset_no'].'</span> ';
		}
   	if (count($rows)>0) $result = implode("<br>", $rows);
    return $result;
}


$counter = 0;
if ($total_item > 0) {

?>
<form method="post" id="#frm_loaned">
<input type="hidden" id="myid_item" name="myid_item" value="<?php echo $myid_item;?>"/>
<div class="searchbox" >
    Search by
    <select name="searchby" id="searchby">
    <option value="asset_no" <?php if ($_searchby == 'asset_no') echo 'selected'?> >Asset No</option>
    <option value="serial_no" <?php if ($_searchby == 'serial_no') echo 'selected'?>>Serial No</option>
    <option value="issued_to" <?php if ($_searchby == 'issued_to') echo 'selected'?>>Issued to</option>
    </select>
    <input type="text" id="searchtext" name="searchtext" class="searchinput" size=20 value="<?php echo $_searchtext?>" 
    onKeyUp="suggest(this, this.value);" onBlur="fill('searchtext', this.value);" autocomplete=off style="width: 140px">
    <input type="image" src="images/loupe.png" class="searchsubmit" width=12 height=12>
    <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
    
</div>
<div class="clear"></div>
</form>
<table class="itemlist grid loan" width="100%">
<tr>
  <th width=35>No</th>
  <th width=120>Date of Request</th>
  <th>Requestor</th>
  <th width=120>Loan Start Date</th>
  <th width=120>Projected Loan End Date</th>
  <th>Category</th>
  <th width=25>Quantity<br>(Out/In)</th>
  <th>Remarks</th>
  <th>Asset No.</th>
  <th width=50>Action</th>
</tr>
<?php

    foreach ($data as $rec) {
        $items = loaned_items($rec['id_loan'],true);
        $_class = ($counter % 2 == 0) ? 'alt':null;
		$ql_class = null;
		if ($rec['quick_issue']==1) $ql_class = 'quick_issue';
		//if ($rec['status']=='PARTIAL_IN') $_class .= ' partial_in';
        echo <<<DATA
	<tr class="$_class" >
	<td align="center" class="$ql_class">$transaction_prefix$rec[id_loan]</td>
	<td align="center" title="">$rec[request_date]</td>
	<td>$rec[requester]</td>
	<td align="center">$rec[start_loan]</td>
	<td align="center">$rec[end_loan]</td>
	<td>$rec[category_name]</td>
	<td align="center">$rec[quantity] / $rec[return_quantity]</td>
	<td>$rec[loan_remark]</td>
	<td align="center" >$items</td>
	<td align="center">
	<a href="./?mod=loan&sub=loan&act=view_issue&id=$rec[id_loan]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
DATA;
        if ($i_can_update) {
			if ($rec['quick_issue']==1)
				echo ' <a href="./?mod=loan&sub=quick_loan_return&id='.$rec['id_loan'].'" title="return" ><img class="icon" src="images/undo.png" alt="return"></a> ';
			else
				echo ' <a href="./?mod=loan&sub=loan&act=return&id='.$rec['id_loan'].'" title="return" ><img class="icon" src="images/undo.png" alt="return"></a> ';
        }
        echo '</td></tr>';
        $counter++;
    }
    echo '<tfoot><tr ><td colspan=10>';
	echo '<div class="pagination">';
    echo make_paging($_page, $total_page, './?mod=loan&sub=loan&act=list&status='.strtolower(LOANED).'&page=');
	echo '</div>';
    echo  '<div class="exportdiv"><a href="./?mod=loan&sub=loan&act=list&status=loaned&do=export" class="button">Export Data</a></div>';
	echo '</td></tr></tfoot></table>';
	echo '<div style="text-align: left"><cite>*Quick Loan is denoted by Yellow highlight at Loan Transaction No.<br> *Partially-Returned Items are denoted by Red-coloured Asset Nos.</cite></div>';
    
} else {
	echo '<p align="Center" ><h3>Requests already Loaned Out </h3><br><p class="error">Data is not available!</p></p>';

}
?>

<br><br>&nbsp;<br>
