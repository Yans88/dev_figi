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
$_searchby = !empty($_POST['searchby']) ? $_POST['searchby'] : null;
$_searchtext = !empty($_POST['searchtext']) ? $_POST['searchtext'] : null;
$myid_item = !empty($_POST['myid_item']) ? $_POST['myid_item'] : null;

$completed_status = array(COMPLETED, PARTIAL_IN);
if ($_do == 'export'){
    export_request_status($completed_status);
}


$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_request_by_status($completed_status);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0) $_start = ($_page-1) * $_limit;
$_studentLoan = (!empty($_GET['student_loan'])) ? $_GET['student_loan'] : 0;

$data = get_request_by_status($completed_status, $_start, $_limit, 'return_date', 'DESC', $myid_item, $_searchby, $_studentLoan );

function completed_items($id = 0, $colorize=false)
{
    $result = '';
    $query = "SELECT li.id_item, i.asset_no, i.serial_no, lr.id_item return_id 
                FROM loan_item li 
                LEFT JOIN loan_return_item lr ON lr.id_loan= li.id_loan AND lr.id_item = li.id_item 
                LEFT JOIN item i ON li.id_item = i.id_item 
                WHERE li.id_loan = $id  ";
    $rs = mysql_query($query);
    //echo(mysql_error().$query);
    $rows = array();
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs)){
			$pr_class = null;
			if ($colorize && $rec['id_item']==$rec['return_id']) $pr_class = 'partial_in'; 
			$rows[] = '<span class="'.$pr_class.'">'.$rec['asset_no'].'</span> ';
		}
    if (count($rows)>0) $result = implode("<br>", $rows);
    return $result;
}

if (REQUIRE_LOAN_APPROVAL)
    $caption = 'Acknowledgement Returned Items';
else
    $caption = 'Returned Items';

$counter = 0;
if ($total_item > 0){
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

<table  class="itemlist grid loan" width="100%" >
<thead>
<tr>
  <th width=35>No</th>
  <th width=170>Date of Actual Return</th>
  <th>Requestor</th>
  <th width=120>Loan Start Date</th>
  <th width=120>Loan End Date</th>
  <th>Category</th>
  <th>Asset No.</th>
  <th width=40>Action</th>
</tr>
</thead>
<?php
    foreach ($data as $rec) {
        $items = completed_items($rec['id_loan'], true);
		$_class = ($counter % 2 == 0) ? 'alt':null;
		if ($rec['quick_issue']==1) $_class .= ' quick_issue';
        echo <<<DATA
	<tr class="$_class ">
	<td align="center">$transaction_prefix$rec[id_loan]</td>
	<td align="center">$rec[return_date]</td>
	<td>$rec[requester]</td>
	<td align="center">$rec[start_loan]</td>
	<td align="center">$rec[end_loan]</td>
	<td>$rec[category_name]</td>
	<td align="center" >$items</td>
	<td align="center">
    <a href="./?mod=loan&sub=loan&act=view_complete&id=$rec[id_loan]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
DATA;
/*
        if (REQUIRE_LOAN_APPROVAL && ((USERGROUP == GRPHOD) || $i_can_delete)){
            echo ' <br/><a href="./?mod=loan&sub=loan&act=acknowledge&id='.$rec['id_loan'].'" >acknowledge</a>';
        }
    */
        echo '</td></tr>';
        $counter++;
    }
    echo '<tfoot><tr><td colspan=8>';
	echo '<div class="pagination">';
    echo make_paging($_page, $total_page, './?mod=loan&sub=loan&act=list&status='.strtolower(COMPLETED).'&page=');
	echo '</div>';
    echo '<div class="exportdiv"><a href="./?mod=loan&sub=loan&act=list&status=completed&do=export" class="button">Export Data</a></div>';
	echo '</td></tra</tfoot></table>';
	echo '<div style="text-align: left"><cite>*Quick Loan is denoted by Yellow highlight at Loan Transaction No.<br> *Partially-Returned Items are denoted by Red-coloured Asset Nos.</cite></div>';
} else {
	echo '<p align="Center"><h3>'.$caption.'</h3><br><p class="error">Data is not available!</p></p>';

}
?>
<br>&nbsp;
<br>&nbsp;
<br>&nbsp;
<br>&nbsp;
