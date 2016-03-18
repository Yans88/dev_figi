<?php
if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_serialno = isset($_GET['serialno']) ? $_GET['serialno'] : null;
$item = get_consumable($_id);

echo '<H2>Inventory Record of "'.$item['item_name'].'"</H2>';
echo '<H5>Current Quantity: '.$item['item_stock'].'</H5>';
echo '<div style="width: 720px"><h3>Purchase Record</h3></div>';

$query = "SELECT *, date_format(trx_time, '%e-%b-%Y %H:%i') trx_time  
            FROM consumable_item_in cii 
            LEFT JOIN consumable_item ci ON ci.id_item= cii.id_item 
            LEFT JOIN category cat ON cat.id_category = ci.id_category 
            WHERE cii.id_item = $_id 
            ORDER BY trx_time DESC ";
$rs = mysql_query($query); 
$rows = mysql_num_rows($rs);    
if ( $rows > 0){		
    
?>
<table border="0" style="width: 720px" class="itemlist" cellpadding=2 cellspacing=1>
  <tr>
    <th width=30>No</th>
    <th width=80>Item Code</th>
    <th >Item Name</th>
    <th width=30>Qty</th>
    <th width=120>Purchase Date</th>
    <th width=120>DO No.</th>
    <th width=40>Action</th>
   </tr>

<?php
$no = 0;
while ($rec = mysql_fetch_assoc($rs)){
    $no++;
	if (!SUPERADMIN && $i_can_update && $i_can_delete)
		$edit_link = <<<EDIT
<a href="./?mod=consumable&act=purchase_edit&id=$rec[id_trx]" item_name="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
EDIT;
//<a href="./?mod=consumable&act=del&id=$rec[id_item]" 
//       onclick="return confirm('Are you sure delete &quot;$rec[item_name]&quot;?')" item_name="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
    
    echo <<<ROW
    <tr>
        <td>CPR$no</td>
        <td>$rec[item_code]</td>
        <td>$rec[item_name]</td>
        <td align="center">$rec[quantity]</td>
        <td>$rec[trx_time]</td>
        <td>$rec[do_no]</td>
        <td align="center">
        <a href="./?mod=consumable&act=purchase_view&id=$rec[id_trx]" item_name="view"><img class="icon" src="images/loupe.png" alt="view" ></a> 
        $edit_link
        </td>
    </tr>
ROW;
    }
    echo "</table>";
} // loan info found out 
else
    echo '<div class="error">This Item does not have purchasing history.</div>';
    
echo '<br/>&nbsp;<br/><div style="width: 720px"><h3>Issued Out Record</h3></div>';

$query = "SELECT *, date_format(trx_time, '%e-%b-%Y %H:%i') trx_time  
            FROM consumable_item_out cio 
            LEFT JOIN consumable_item_out_list ciol ON ciol.id_trx = cio.id_trx 
            LEFT JOIN consumable_item ci ON ci.id_item= ciol.id_item 
            LEFT JOIN category cat ON cat.id_category = ci.id_category 
            WHERE ciol.id_item = $_id 
            ORDER BY trx_time DESC ";
$rs = mysql_query($query); 
//echo mysql_error().$query;
$rows = mysql_num_rows($rs);    
if ( $rows > 0){		
    
?>
&nbsp;
<table border="0" style="width: 720px" class="itemlist" cellpadding=2 cellspacing=1>
  <tr>
    <th width=50>Trx. No</th>
    <th width=110>Trx. Date</th>
    <th width=120>User Name</th>
    <th width=80>Item Code</th>
    <th >Item Name</th>
    <th width=60>Quantity</th>
    <th width=50 align="center">Action</th>
   </tr>

<?php
$no = 0;
while ($rec = mysql_fetch_assoc($rs)){
    $no++;
    $edit_link = '';
    echo <<<ROW
    <tr>
        <td>CUR$rec[id_trx]</td>
        <td>$rec[trx_time]</td>
        <td>$rec[user_name]</td>
        <td>$rec[item_code]</td>
        <td>$rec[item_name]</td>
        <td align="center">$rec[quantity]</td>
	    <td align="center">
			<a href="./?mod=consumable&act=usage_view&id=$rec[id_trx]" item_name="view"><img class="icon" src="images/loupe.png" alt="view" ></a> 
			$edit_link
	    </td>
    </tr>
ROW;
}
    echo "</table>";
} // loan info found out 
else
    echo '<div class="error">This Item does not have usage history.</div>';


?> 

&nbsp;
