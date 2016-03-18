<?php 

if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unitem_nameized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_qty = isset($_POST['quantity']) ? $_POST['quantity'] : 1;
$_input = isset($_POST['input']) ? $_POST['input'] : null;
$_msg = null;
$dept = USERDEPT;
$type = 'consumable';

$data_item = array();
$rs = get_usage_item($_id);
if ($rs)
    $data_item = mysql_fetch_assoc($rs);
?>
<br/>
<form method="POST" id="telo">
<input type="hidden" name="save" value=0>
<input type="hidden" name="items" id="items" value=''>
<table cellspacing=1 cellpadding=3 id="itemedit">
<tr><th colspan=2>Detail View Usage Info</th></tr>
<?php
    if ($_id == 0){
?>
<tr><td colspan=2>
    <br/> &nbsp; 
    Scan / Enter purchased item code:
    &nbsp;  <br/>   
    <input type="text" id="input" name="input" class="inputbox" autocomplete="off" onkeyup="check_entry()">
    <script type="text/javascript">
    $(window).load(function(){$('#input').focus()});
    </script>
</td></tr>
<?php
    } else  {
?>
<tr valign="top">
    <td width=320>
      <table width="100%" class="itemlist" cellpadding=4 cellspacing=1>
        <tr class="normal">
          <td width=100>Transaction No</td>
          <td>CUR<?php echo $data_item['id_trx']?></td>
        </tr>
        <tr class="alt">
          <td width=100>Transaction Date</td>
          <td><?php echo $data_item['trx_time']?></td>
        </tr>
      <tr class="normal">
        <td>User Name</td>
        <td><?php echo $data_item['user_name']?></td>
      </tr>
       <tr class="alt">
        <td>Location</td>
        <td><?php echo $data_item['location']?></td>
      </tr>
<?php
    $signature = get_consumer_signature($_id);
    if ($signature != null){
?>
      <tr class="alt">
        <td>Signature</td>
        <td> &nbsp;<br/><img src="<?php echo $signature?>" class="signature" width=200 height=80/>&nbsp;<br/></td>
      </tr>
<?php
    }
?>
 </table>
        </td>    
        <td width=300>
      <table width="100%" class="itemlist" cellpadding=3 cellspacing=1>
        <tr class="normal">
          <td colspan=3 align="center" height=25><strong>List of Issued-out Items</strong></td>
        </tr>
        <tr class="alt">
          <th width=100>Item Code</th>
          <th>Item Name</th>
          <th width=30>Qty</th>
        </tr>    
      <tr class="normal">
        <td><?php echo $data_item['item_code']?></td>
        <td><?php echo $data_item['item_name']?></td>
        <td align="center"><?php echo $data_item['quantity']?></td>
      </tr>
<?php
    while ($data_item = mysql_fetch_assoc($rs)){
?>
      <tr class="normal">
        <td><?php echo $data_item['item_code']?></td>
        <td><?php echo $data_item['item_name']?></td>
        <td align="center"><?php echo $data_item['quantity']?></td>
      </tr>
<?php
    }// while
?>
</table>
        </td>
  </tr>
  <tr valign="top">
    <td align="center">
      <button type="button" onclick="cancel_it()">Back to Item List</button>
    </td>
  </tr>  
<?php 
    } // id == 0
?>
</table>
</form>
<br/>
<br/>
<script type="text/javascript">
$("#item_code").focus();
display_list($("#items").val());
 

var isbn_length = <?php echo ISBN_LENGTH?>;
var nric_length = <?php echo NRIC_LENGTH?>;
var serial_length = <?php echo SERIAL_LENGTH?>;

function check_entry()
{
    var v = $('#input').val();
    if ($('#nric').val() == ''){
        if (v.length >= nric_length)
            $('form').submit();
    } else {
        if (v.length >= serial_length)
            $('form').submit();    
    }
}

function cancel_it()
{
    location.href='./?mod=consumable';
}

</script>