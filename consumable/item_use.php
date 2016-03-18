<?php
//require_once('util.php');

define('USE_ITEM', true);

$need_signature = defined('CONSUMABLE_NEED_SIGNATURE') ? CONSUMABLE_NEED_SIGNATURE : false;
$_input = isset($_POST['input']) ? $_POST['input'] : null;
$_nric = isset($_POST['nric']) ? $_POST['nric'] : null;
$_id_user = isset($_POST['id_user']) ? $_POST['id_user'] : 0;
$_del_id = isset($_POST['del_id']) ? $_POST['del_id'] : 0;
$_full_name = isset($_POST['full_name']) ? $_POST['full_name'] : null;
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_confirm = !empty($_POST['confirm']) ? ($_POST['confirm'] == 1) : false;
$_manage = !empty($_POST['manage']) ? ($_POST['manage'] == 1) : false;
$_signature = isset($_POST['user_signature']) ? $_POST['user_signature'] : null;

$items = explode(',', $_items);
$quantities = array();
$hidden_fields = null;
if ($_input != null){ // scan item
    $item = get_consumable_item_by_code($_input);
    if (!empty($item['id_item'])){
        //if (!in_array($item['id_item'], $items))
            $items[] = $item['id_item'];
    }
}


$scanned_list = '<div id="itemspace"></div>';
if (count($items) > 0){
    // clean up for empty id item, deletion, calculation
    $tmp = array();
    foreach($items as $id_item){
        $id_item = trim($id_item);
        if (empty($id_item) || (($_del_id > 0) && ($_del_id == $id_item))) continue;
        //echo $id_item . ', ';
        $tmp[] = $id_item;
        $stock = get_consumable_stock($id_item);
        if (isset($quantities[$id_item])){
            if ($stock >= ($quantities[$id_item]+1))
                $quantities[$id_item]++;
        } else {
            if ($stock > 0)
                $quantities[$id_item] = 1;
        }
    }
    $items = $tmp;
    
    if ($_confirm){
		// keep item-out trx
		$query = "INSERT INTO consumable_item_out (user_name, location)
					VALUES ('$_POST[user_name]', '$_POST[location]')";
		mysql_query($query);
		if (mysql_affected_rows() > 0){
            $idtrx = mysql_insert_id();
			// keep items
	 		foreach ($quantities as $id_item => $quantity ){
				$query = "INSERT INTO consumable_item_out_list (id_trx, id_item, quantity)
							VALUES ($idtrx, $id_item, $quantity)";
				mysql_query($query);
                // update  stock
                $query = "UPDATE consumable_item SET item_stock = item_stock - $quantity WHERE id_item = $id_item";
                mysql_query($query);                
             
            }
			// store signature
            if ($need_signature && ($_signature != null)){
                $query = "INSERT INTO consumable_user_signature (id_trx, signature) 
                            VALUES ($idtrx, '$_signature') ";
                mysql_query($query);
            }
         }   
        $_input = '';
        $_nric = '';
        $_id_user = '';
        $_items = '';
        $_full_name = '';
        $items = array();
    }


    // get item's info
	$query  = "SELECT dci.*, department_name, category_name 
                FROM consumable_item dci 
                LEFT JOIN category cat ON cat.id_category = dci.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   
                WHERE id_item IN (" . implode(',', $items) . ')';
    $rs = mysql_query($query);
    
    $no = 1;
    if ($rs && mysql_num_rows($rs)>0){
        $scanned_list  = '<table class="consumable_item_list" cellpadding=3 cellspacing=3 width="800">';
        $scanned_list .= '<tr><th width=30>No</th><th width=120>Part No.</th>
                          <th >Name</th><th width=100>Quantity</th><th width=20></th></tr>';
        while ($rec = mysql_fetch_assoc($rs)){
            $dellink = '<a class="button delete" href="javascript:void(0)" onclick="del_this(' . $rec['id_item'] . ')">x</a>';
            $scanned_list .= '<tr><td align="center">' . ($no++) . '.</td><td>' .
                             $rec['item_code'] . '</td><td>' . $rec['item_name'] . '</td><td align="center" onclick="update_quantity(' . $rec['id_item'] . ')">' . 
                             $quantities[$rec['id_item']] . '</td><td>' . $dellink . '</td></tr>';
        }                
        $scanned_list .= '</table><br/>';
    }
}

?>
<br/>
<br/>
<div id="form">
<form method="post" id="consumableform">
<input type="hidden" id="user_signature" name="user_signature" value="">
<input type="hidden" id="del_id" name="del_id" value="0">
<input type="hidden" id="manage" name="manage" value="0">
<input type="hidden" id="confirm" name="confirm" value="0">
<input type="hidden" id="nric" name="nric" value="<?php echo $_nric?>">
<input type="hidden" id="items" name="items" value="<?php echo implode(',', $items)?>">
<input type="hidden" id="id_user" name="id_user" value="<?php echo $_id_user?>">
<input type="hidden" id="full_name" name="full_name" value="<?php echo $_full_name?>">
<?php
    echo $scanned_list;
    if ($_confirm){
        echo '<div id="cmdlabel">Item usage recorded! Click <a href="./?mod=consumable&act=use">here</a> to make new record.</div>';
    } 
    else if ($_manage){
?>

<table class="consumable_form itemlist" cellpadding=5 cellspacing=0>
    <tr><th colspan=2>Fill form to completion</th></tr>
    <tr><td colspan=2 align="right"><br/> &nbsp;</td></tr>
    <tr class="alt"><td>User</td><td>
        <input type="text" id="user_name" name="user_name" size=34 
         onKeyUp="suggest(this, this.value);" onBlur="fill('user_name', this.value);" autocomplete="off">
        <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;"> 
            <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
        </div>
    </td></tr>
    <tr class="normal"><td>Location</td><td>
            <input type="text" id="location" name="location" size=34 autocomplete="off" 
            onKeyUp="suggest_loc(this, this.value);" onBlur="fill_loc('location', this.value);">
			<div class="suggestionsBox" id="suggestionsLoc" style="display: none; z-index: 500;"> 
				<div class="suggestionList" id="suggestionsListLoc"> &nbsp; </div>
			</div>            
    </td></tr>
<?php
    if ($need_signature) {
?>
    <tr class="alt" valign="top"><td>Signature</td><td>
        <div id="signature-pad" class="m-signature-pad" style='width: 200px;height: 80px;'>
			<div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
		</div>
        <script type="text/javascript" src="./js/signature.js"></script>
    </td></tr>
<?php 
    } // need_signature
?>
    <tr><td colspan=2 align="center"><input type="image" name="submit" id="submit" onclick="return confirm_this()" src="images/submit.png" /></td></tr>
</table>

<?php
    } // manage    
    else {
        if (count($items) == 0)
            echo 'Scan an item: ';
        else
            echo 'Scan another item: ';
?>
    <br/>    
    <br/>    
    <input type="text" id="input" name="input" class="inputbox" autocomplete="off" onkeyup="check_entry()">
<?php
    if (count($items)>0){
        echo '<br/>&nbsp;<br/>or click <a href="javascript:manage_this()" class="button manage">Manage</a> to proceed';

    } // item   > 0
    }
?>
</form>
</div>
<br>&nbsp;<br>

<script type="text/javascript">
var isbn_length = <?php echo ISBN_LENGTH?>;
var nric_length = <?php echo NRIC_LENGTH?>;
var serial_length = <?php echo SERIAL_LENGTH?>;
var need_signature = '<?php echo $need_signature ?>';

function update_quantity(id)
{
    var list = $('#items').val();
    var items = list.split(',');
    var oldqty = 0;
    var i;
    for (i=0; i< items.length; i++)
        if (items[i] == id){
            oldqty++;
            items[i] = 'empty';
        }
    var newqty = prompt('Update quantity: ', oldqty);
    
    if (newqty != null && !isNaN(newqty)){
        var newitems =  new Array();
        for (i=0; i < items.length; i++)
            if (items[i] != 'empty')
                newitems.push(items[i]);
        for (i=0; i < newqty; i++)
            newitems.push(id);    
        $('#items').val(newitems.join(','));
        $('form').submit();
    }
    if (isNaN(newqty)){
        alert('Please enter correct number of quantity');
    }
}

function del_this(id)
{
    $('#del_id').val(id);
    $('form').submit();
}

function cancel_this()
{
    if (confirm("Are you sure cancel this loan?")){
        new_loan();
    }
}

function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("user/user_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			}
		});
	}
}

function fill_loc(id, thisValue) 
{
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestionsLoc').fadeOut();", 100);
}

function suggest_loc(me, inputString)
{
	if(inputString.length == 0) {
		$('#suggestionsLoc').fadeOut();
	} else {
		$.post("item/suggest_location.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestionsLoc').fadeIn();
				$('#suggestionsListLoc').html(data);
			}
		});
	}
}

function manage_this()
{
    //if (confirm("Are you sure confirm this loan?")){
        $('#manage').val(1);
        $('form').submit();
    //}
}

function confirm_this()
{
    if ($('#user_name').val() == ''){
        alert('Please fill in name of the user!');
        return false;
    }
    if ((need_signature == 1) && isCanvasEmpty){
        alert('Please sign-in for user!');
        return false;
    }
    if (confirm("Are you sure confirm this request?")){
        $('#confirm').val(1);
        var cvs = document.getElementById('imageView');
        $('#user_signature').val(cvs.toDataURL("image/png"));
        $('form').submit();
        return true;
    }
    
    return false;
}

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

$('.inputbox').focus();

</script>
