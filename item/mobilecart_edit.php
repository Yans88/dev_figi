<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$_items = isset($_POST['items']) ? $_POST['items'] : null;

require_once 'mobilecart_util.php';
require_once 'item/item_util.php';
$dept = USERDEPT;
if (isset($_POST['save'])) {
    $data = $_POST;
    $data['id_department'] = $dept;
    $id = save_mobile_cart($_id, $data);
    if (($_id > 0) && ($id > 0)) {
        user_log(LOG_UPDATE, 'Update mobile cart '. $_POST['cart_name']. '(ID:'. $_id.')');
		$_msg = "Cart's name updated!";
    } if (($id > 0) && ($_id == 0)) {
        $_id = $id;
        user_log(LOG_CREATE, 'Create new mobile cart '. $_POST['cart_name']. '(ID:'. $_id.')');
		$_msg = "New Cart's has been saved!";
	}
    
    if (($_id>0) && !empty($_items)){
        $items  = get_item_from_serial_no($_items); // asset_no|serial_no,asset_no|serial_no,...
       
        if (count($items)>0){
            $values = array();
            foreach ($items as $id_item){
                if (preg_match('/^[0-9]+$/', $id_item) > 0)
                    $values[] = "($_id, $id_item)";
            }
            // delete if existing items
            mysql_query("DELETE FROM mobile_cart_item WHERE id_cart = $_id");
            if (count($values)>0){
                $query = "INSERT INTO mobile_cart_item(id_cart, id_item) VALUES " . implode(', ', $values);
                mysql_query($query);
                //echo mysql_error().$query;
            }
        }
    }
    
	ob_clean();
	header('Location: ./?mod=item&sub=mobilecart&act=list&id=' . $_id);
	ob_flush();
	ob_end_flush();
	exit;
} else if (isset($_POST['delete'])) {
	$_id = isset($_POST['id']) ? $_POST['id'] : 0;
	ob_clean();
	header('Location: ./?mod=item&sub=mobilecart&act=del&id=' . $_id);
	ob_flush();
	ob_end_flush();
	exit;
}		

$item_list = null;

if ($_id > 0) {
    $query  = "SELECT *,(SELECT COUNT(*) FROM mobile_cart_item WHERE id_cart = $_id) AS number_of_item  FROM mobile_cart WHERE id_cart = $_id";
    $rs = mysql_query($query);
    $data_item = mysql_fetch_array($rs);
    $caption = 'Edit Mobile Cart';
    
    $items = array();
    $cart_items = get_mobile_cart_items($_id);
    foreach ($cart_items as $item)
      $items[] = "$item[asset_no]|$item[serial_no]|$item[category_name]|$item[brand_name]";
    $item_list = implode(',', $items);
      
} else {

    $caption = 'Create New Mobile Cart';
    $data_item['id_cart'] = '0';
    $data_item['cart_name'] = '';
    $data_item['cart_status'] = '';
    $data_item['number_of_item'] = '20';
}
   
$statuses = get_status_list();

?>
<script type="text/javascript">

var department = '<?php echo $dept ?>';

 function save_item(){
  var frm = document.forms[0]
  frm.save.value = 1;
  frm.submit();
 }
 
 function del_item(item)
{
    if (confirm("Are you sure delete the item?")){
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
	}
}

function add_item()
{
	var item = $('#edit_item').val();
	if (item == '') return;
	var items = $('#items').val();
	var cols = item.match(/(.+) \((.+)\)/);
	if  (items.search(new RegExp(cols[1]+'|'+cols[2])) == -1){
		var rest = item.substring(item.indexOf(", ")+2);
		cols.shift();
		cols = cols.concat(rest.split(", "));
		if (items == '') items = cols.join('|');
		else items += ',' + cols.join('|');
		$('#items').val(items);
        $('#edit_item').val('');
	} else
        alert('Item already exists!');
    display_list(items);
    $('#edit_item').focus();
}

function display_list(items)
{
    var text = '';
    var cols = '';
    var recs = items.split(',');
	$('input[name=number_of_item]').val(recs.length);
    if (items != '' && recs.length > 0){
		text += '<table width="100%" class="itemlist grid">';
		text += '<tr><th width=30>No</th><th>Asset No</th><th>Serial No</th><th>Category</th><th>Brand</th><th width=15></th></tr>';
        for (var i=0; i < recs.length; i++){
            cols = recs[i].split('|'); // asset_no|serial_no|id_item|cat-name|brand-name
			var asser = cols[1]+cols[0];
            text += '<tr class="an_item " id="' + asser + '"><td>'+(i+1)+'</td> ';
            text += '<td class="">' + cols[1] + '</td>';
            text += '<td class="">' + cols[0] + '</td>';
            text += '<td>' + cols[2] + '</td>';
            text += '<td>' + cols[3] + '</td>';
            text += '<td><a onclick="del_item(\''+ recs[i] +'\')"><img class="icon" src="images/delete.png" alt="delete"></a></td></tr>';
        }
		text += '</table>';
    } else
        text = '--- no item specified ---';
    $('#item_list').html(text);
}

function fill(id, thisValue, onclick) 
{
	if (thisValue.length>0 && onclick){
		var cols = thisValue.split('|');
		$('#'+id).val(cols[1] + ' (' + cols[0] + '), ' + cols[3] + ', ' + cols[4] );
	}
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString)
{
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
        if (/,/.test(inputString)){
            var mathces = /.*, *(.+)/.exec(inputString);
            if (mathces != null)
                inputString = mathces[1];
        }
		$.post("item/mobilecart_suggest_item.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", searchBy: "serial_no", deptId: ""+department+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			}
		});
	}
}

</script>

<form method="POST">
<input type="hidden" name="items" id="items" value="<?php echo $item_list ?>">

<table width=600 class="itemlist" cellpadding=2 cellspacing=1>
<tr><th colspan=2><?php echo $caption?></th></tr>
<tr class="normal">
  <td width=110>Cart Name </td>
  <td><input type="text" name="cart_name" value="<?php echo $data_item['cart_name']?>" style="width: 400px"></td>
</tr>
<tr class="alt">
  <td>Number of Items </td>
  <td><input readonly type="text" name="number_of_item" value="<?php echo $data_item['number_of_item']?>" size=5></td>
</tr>
<?php
/*
<tr class="normal">
  <td>Status </td>
  <td>
        <select name="id_status">
            <option value=0>-- not set --</option>
            <?php echo build_option($statuses, $data_item['cart_status'])?>
        </select>
  </td>
</tr>
*/
    if ($_id > 0) {
?>
<tr class="normal" valign="top">
  <td>Items </td>
  <td>  </td>
</tr>
<tr class="alt" valign="top">
  <td colspan=2> <ul id="item_list" style="padding-left: 0px"></ul> </td>
</tr>
<tr class="normal" valign="top">
  <td>Select item</td>
  <td>
		<input type="text" id="edit_item" name="serial_no" onKeyUp="suggest(this, this.value);" autocomplete="off" style="width: 400px" class="mobilecart">
		<a href="javascript:void(0)" onclick="add_item()"><img class="icon" src="images/add.png"></a>
		<div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500; width: 400px"> 
			<img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
			<div class="suggestionList" id="suggestionsList"> &nbsp; </div>
		</div>
  </td>
</tr>
<?php
    }
?>

</table>
<br/>
<input type="hidden" name="id" value="<?php echo $_id?>" > 
<button type="submit" name="save" >Save</button>
<button type="button" name="cancel" onclick="location.href='./?mod=item&sub=mobilecart'">Cancel</button>
<?php
if ($_id > 0) {
echo <<<TEXT
<button type="submit" name="delete" 
	onclick="return confirm('Are you sure you want to delete $data_item[cart_name]?')">Delete</button>
TEXT;
}
?>
</form>
<br/>
<?php
if ($_msg != null)
	echo '<div class="error">' . $_msg . '</div>';
?>
<script type="text/javascript">
        display_list($("#items").val());
</script>
