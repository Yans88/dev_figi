<?php 

if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$dept = USERDEPT;
$data_item = get_expendable($_id);

if (count($data_item) == 0){
    echo '<script>location.href = "./?mod=expendable&sub=item";</script>';
    return;
}


?>
<link rel="stylesheet" href="<?php echo STYLE_PATH?>jquery.fancybox.css" type="text/css" media="screen" title="no title" charset="utf-8" />
<script type="text/javascript" src="./js/jquery.fancybox.pack.js"></script>
<script type="text/javascript">
 function save_item(){
  var frm = document.forms[0]
  frm.save.value = 1;
  frm.submit();
 }
 
function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("expendable/suggest_author.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			}
		});
	}
}

function cancel_it()
{
    location.href='./?mod=expendable';
}

function view_transaction_history()
{
    location.href='./?mod=expendable&act=history&id=<?php echo $_id?>';
}

function edit_it()
{
    location.href="./?mod=expendable&act=edit&id=<?php echo $_id?>";
}

function purchase(id)
{
    location.href="./?mod=expendable&act=purchase&id="+id;
}

function delete_it()
{

    ok = confirm('Are you sure delete <?php echo $data_item['item_name']?>?');
    if (ok) 
        location.href="./?mod=expendable&act=del&id=<?php echo $_id?>";     
}

function change_barcode(value)
{
    //$('#barcodeimg').src = "barcode.php?text=1&height=80&width=200&barcode="+value;
    var img = document.getElementById('barcodeimg');
    img.src = "barcode.php?text=1&&format=png&height=100&width=410&barcode="+value;
}

</script>

<br/>
<form method="POST" id="telo">
<input type="hidden" name="save" value=0>

<table border=0 cellspacing=1 cellpadding=2 id="itemedit">
<tr><th colspan=2>View Detailed Item</th></tr>
<tr valign="top">
    <td width=450>
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1>
        <tr class="alt">
          <td width=100>Item Code</td>
          <td><?php echo $data_item['item_code']?></td>
        </tr>
      <tr class="normal">
        <td>Title</td>
        <td><?php echo $data_item['item_name']?></td>
      </tr>
      <tr class="alt">
        <td>Category</td>
        <td><?php echo $data_item['category_name']?></td>
      </tr>
      <tr class="normal">
        <td>Total Quantity</td>
        <td><?php echo $data_item['item_stock']?></td>
      </tr>      
      </table>
      <br>
      <div class="barcode"><img src="" id="barcodeimg"></div>
    </td>
    </tr>
  <tr>
    <td align="center" >
<?php 
    if ($_id > 0) { 
        if ($i_can_update && !SUPERADMIN) {
?>
      &nbsp;&nbsp;
      <button type="button" onclick="edit_it()">Edit</button>
      &nbsp;&nbsp; 
      <button type="button" onclick="purchase(<?php echo $_id?>)">Purchase</button>            
<?php
        } // admin but not super
?>
      &nbsp;&nbsp; 
      <button type="button" onclick="cancel_it()">Cancel</button>            
      &nbsp;&nbsp; 
      <button type="button" onclick="view_transaction_history()">Transaction History</button>            
<?php } ?>
    </td>
  </tr>  
</table>
</form>
<br/>
<br/>
<script type="text/javascript">
	$(document).ready(function() {
		$(".various").fancybox({
		maxWidth	: 450,
		maxHeight	: 320,
		fitToView	: false,
		width		: '50%',
		height		: '70%',
		autoSize	: false,
		closeClick	: false,
		openEffect	: 'none',
		closeEffect	: 'none'
      });
	});
    change_barcode('<?php echo $data_item['item_code']?>');
</script>