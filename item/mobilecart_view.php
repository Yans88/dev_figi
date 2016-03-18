<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$_items = isset($_POST['items']) ? $_POST['items'] : null;

require_once 'mobilecart_util.php';
require_once 'item/item_util.php';
$dept = USERDEPT;
	
if ($_id > 0) {
  $query  = "SELECT *, (SELECT COUNT(*) FROM mobile_cart_item WHERE id_cart = $_id) AS number_of_item  
                FROM mobile_cart mc 
                LEFT JOIN status ON status.id_status = mc.cart_status 
                WHERE id_cart = $_id";
  $rs = mysql_query($query);
  $data_item = mysql_fetch_array($rs);
} else {

    echo <<<ERROR
<script type="text/javascript">
    alert("Mobile Cart is not specified. Please select a cart.");
    location.href="./?mod=item&sub=mobilecart&act=list";
</script>
ERROR;
    exit;
}

$items = array();
$caption = 'View Mobile Cart';
$cart_items = get_mobile_cart_items($_id);
//print_r($cart_items);
$no = 0;
$stats = array(
    'Issued' => 0,
    'Onloan' => 0,
    'Under Service' => 0,
    'Storage' => 0,
    'Condemned' => 0,
    'Available for Loan' => 0);
    
if (count($cart_items)>0){
    $item_list  = '<table width=100% cellspacing=1>';
    //$item_list .= '<tr><th>No</th><th>Asset No</th><th>Serial No</th><th>Category</th><th>Brand</th><th>Status</th></tr>';
    $item_list .= '<tr><th>No</th><th>Loan out date</th><th>Project return date</th><th>Issued to</th><th>Serial Number</th><th>Qty</th></tr>';
    $items = array();
    foreach ($cart_items as $item)
        $items[] = $item['id_item'];
    if (count($items)>0){
        $machrecs = get_machine_records($items);
    }
	$itemse = implode(',', $items);
	$id_loans = get_id_loan($itemse);	
	$data_loan = get_data_loan();
	
	$cnt_onloan = 0;
	
    foreach ($cart_items as $item){
        $no++;
		$id_item = $item['id_item'];
		$id_loan = $id_loans[$id_item];	
		$loan_date = $data_loan[$id_loan]['loan_date'];
		$return_date = $data_loan[$id_loan]['return_date'];
		$date_loan = date_create($loan_date);
		$date_loan = date_format($date_loan, "d-M-Y");
		$issued_to = $data_loan[$id_loan]['isued_to'];
		if(empty($loan_date)){
			$date_loan = '-';
		}
		
		if(empty($issued_to)){
			$issued_to = '-';
		}
		
		$date_return = date_create($return_date);
		$date_return = date_format($date_return, "d-M-Y");
		
		if(empty($return_date)){
			$date_return = '-';
		}
		
		$cnt = cnt_loan($id_loan, $itemse);
		
		$cnt_onloan += $cnt[$id_loan];
        $class_name = ($no % 2 == 0) ? 'alt' : 'normal';
        $machrec_link = null;
        if (isset($machrecs[$item['id_item']]) && ($machrecs[$item['id_item']] > 0))
            $machrec_link = '<a href="./?mod=machrec&sub=machine&act=view&id='.$machrecs[$item['id_item']].'"><img src="images/table.png" title="Machine Records" /></a>';
        $item_list .= <<<LIST
    <tr class="$class_name"><td align="right">$no.</td>
        <td align="center">$date_loan</td>       
        <td align="center">$date_return</td>
        <td>$issued_to</td>
		 <td>$item[serial_no]</td>
		 <td>1</td>
       
    </tr>
LIST;

        $stats[$item['status_name']]++;
    }
    $item_list .= '</table>';
    $item_list .=  '<br/>Statistics:<br/>';
    foreach ($stats as $k => $v)
        if ($v > 0)
            $item_list .= str_repeat('&nbsp;', 4) . $k . ' = ' . $v .'<br/>' ;
	$v = $v;
} else {
    $item_list = ' -- empty -- ';
}
$available_loan = $data_item['number_of_item'] - $cnt_onloan;
?>

<form method="POST">
<table width=600 class="itemlist" cellpadding=4 cellspacing=1>
<tr><th colspan=2><?php echo $caption?></th></tr>
<tr class="normal">
  <td width=110>Cart Name </td>
  <td><?php echo $data_item['cart_name']?></td>
</tr>
<tr class="alt">
  <td>Total Loan out </td>
  <td><?php echo $cnt_onloan;?></td>
</tr>
<tr class="normal">
  <td>Available for loan </td>
  <td><?php echo $available_loan;?></td>
</tr>
<tr class="alt" valign="top">
  <td colspan=2>Items </td>
</tr>
<tr class="normal" valign="top">
  <td colspan=2 valign="top"><?php echo $item_list?>
	<br/>&nbsp;<br/>
	<img src="images/table.png"> *) The Item has machine records.

  </td>
</tr>
</table>
<br/>
<button type="button" name="cancel" onclick="location.href='./?mod=item&sub=mobilecart'">Back to List</button>
<button type="button" name="edit" onclick="location.href='./?mod=item&sub=mobilecart&act=edit&id=<?php echo $_id?>'">Edit</button>
</form>
<br/>
<?php
if ($_msg != null)
	echo '<div class="error">' . $_msg . '</div>';
?>
