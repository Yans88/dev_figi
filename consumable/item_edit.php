<?php 

if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unitem_nameized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$dept = USERDEPT;
$type = 'consumable';

if (isset($_POST['save']) && $_POST['save'] == 1) {
	$_id = save_consumable_item($_id, $_POST);
	if ($_id > 0){
		echo '<script>alert("Consumable item data saved successfully");location.href="./?mod=consumable&act=view&id='.$_id.'"</script>';
		return;
	} else {
        echo '<script>alert("Fail to save consumable item data!")</script>';
    }
}

if ($_id > 0) {
    $data_item = get_consumable($_id);
} 
else {

  $data_item['item_stock'] = '0';
  $data_item['id_item'] = '0';
  $data_item['serial_no'] = '';
  $data_item['item_code'] = '';
  $data_item['id_category'] = 0;
  $data_item['description'] = '';
  $data_item['item_name'] = '';
  $data_item['vendor_name'] = '';
  $data_item['status'] = 'Available for Loan';
  $data_item['last_update'] = date('m/d/Y');
}


?>

<script type="text/javascript">
function save_item(){
    var frm = document.forms[0];
    if (frm.item_code.value == ''){
        alert('Item Code is mandatory, can not leave blank!');
        frm.item_code.focus();
        return false;
    }
    if (frm.item_name.value == ''){
        alert('Item Name is mandatory, can not leave blank!');
        frm.item_name.focus();
        return false;
    }
    if (frm.item_name.value == ''){
        alert('Item Name is mandatory, can not leave blank!');
        frm.item_name.focus();
        return false;
    }
    frm.save.value = 1;
    frm.submit();
    return true;
}  
 
function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("consumable/suggest_item_name.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			}
		});
	}
}

function cancel_it()
{
    location.href='./?mod=consumable';
}

function new_item()
{
    location.href='./?mod=consumable&act=edit';
}

function view_log()
{
    location.href="./?mod=consumable&act=history&id=<?php echo $_id?>";
}

function delete_it()
{

    ok = confirm('Are you sure delete <?php echo $data_item['item_name']?>?');
    if (ok) 
        location.href="./?mod=consumable&act=del&id=<?php echo $_id?>";     
}

$.fn.selectRange = function(start, end) 
{
    return this.each(function() {
        if (this.setSelectionRange) {
            this.focus();
            this.setSelectionRange(start, end);
        } else if (this.createTextRange) {
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
    });
};

function loaned_by_update(out_to)
{
    var loaned_by = document.getElementById('loanedby');
    loaned_by.innerHTML = out_to.value;
}


function edit_item(item)
{
    //$('#edit_item').val(item);    
}

function del_item(item)
{
    //if (confirm("Are you sure delete the item?")){
        var items = $('#items').val();
        var recs = items.split(',');
        var newrecs = new Array();
        for (var i=0; i < recs.length; i++){
            if (recs[i].search(new RegExp(item)) == -1){
                newrecs.push(recs[i]);
            }
        }
        $('#items').val(newrecs);
        display_list(newrecs.join(','));
    //}
}

function add_item()
{
    var item = $('#edit_item').val();
    if (item == '') return;
    var items = $('#items').val();
    if  (items.search(new RegExp(item)) == -1){
        if (items == '') items = item;
        else items += ',' + item;
        $('#items').val(items);
        $('#edit_item').val('');
    } else
        alert('Serial no. already exists!');
    display_list(items);
    $('#edit_item').focus();
}

function display_list(items)
{
    var text = '';
    var recs = items.split(',');
    if (items != '' && recs.length > 0){
        for (var i=0; i < recs.length; i++){
            text += '<li class="an_item" id="' + recs[i] + '">' ;
            text += '<a onclick="del_item(\''+ recs[i] +'\')"><img class="icon" src="images/delete.png" alt="delete"></a> ';
            text += '<a onclick="edit_item(\''+ recs[i] +'\')">' + (i+1) + '. ' + recs[i] + '</a></li>';
        }
    } else
        text = '--- no item specified ---';
    $('#item_list').html(text);
}

</script>

<style>
#suggestions {margin-top: 0; };
#suggestionsList li {margin-top: 15px; border: 1px solid white; height: 25px;};
</style>
<br/>
<form method="POST" id="telo">
<input type="hidden" name="save" value=0>
<input type="hidden" name="items" id="items" value=''>
<table cellspacing=1 cellpadding=3 id="itemedit">
<tr><th colspan=2>Create New / Edit Item</th></tr>
<tr valign="top">
    <td width=420>
      <table width="100%" class="itemlist" cellpadding=3 cellspacing=1>
        <tr class="alt">
          <td width=100>Item Code</td>
          <td><input type="text" id="item_code" name="item_code" value="<?php echo $data_item['item_code']?>" autocomplete="off"></td>
        </tr>
      <tr class="normal">
        <td>Item Name</td>
        <td>
			<input type="text" id="item_name" name="item_name" value="<?php echo $data_item['item_name']?>" size=30 
			 onKeyUp="suggest(this, this.value);" onBlur="fill('item_name', this.value);" autocomplete="off">
			<div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;"> 
				<div class="suggestionList" id="suggestionsList"> &nbsp; </div>
			</div>
		</td>
      </tr>
      <tr class="normal">
        <td>Quantity</td>
        <td><input type="text" name="item_stock" value="<?php echo $data_item['item_stock']?>" size=6></td>
      </tr>
      <tr class="alt">
        <td>Category</td>
        <td><?php echo build_category_combo($type, $data_item['id_category'], $dept)?></td>
      </tr>
  </table>
        </td>
  </tr>
  <tr valign="top">
    <td align="center">
      <button type="button" onclick="save_item();return false" >Save</button> &nbsp;
      <button type="reset" >Reset</button> &nbsp;
      <button type="button" onclick="cancel_it()">Cancel</button>
<?php if ($_id > 0) { ?>
      &nbsp;&nbsp;
      <button type="button" onclick="delete_it()">Delete</button>
<?php } ?>
    </td>
  </tr>  

</table>
</form>
<br/>
<br/>
<script>
$("#item_code").focus();
display_list($("#items").val());
 
</script>