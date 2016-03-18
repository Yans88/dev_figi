
<?php
//require_once('util.php');
//if (!STOCK_TAKING_APP){
if (!defined('STOCK_TAKING') || !STOCK_TAKING){
//if(!defined('STOCK_TAKING_APP') || !STOCK_TAKING_APP){ 
	echo 'Unknown Module'; 
	exit;
}
define('USE_ITEM', true);

$need_signature = defined('CONSUMABLE_NEED_SIGNATURE') ? CONSUMABLE_NEED_SIGNATURE : false;
$_input = isset($_POST['input']) ? $_POST['input'] : null;
$_nric = isset($_POST['nric']) ? $_POST['nric'] : null;
$_id_user = isset($_POST['id_user']) ? $_POST['id_user'] : 0;
$_del_id = isset($_POST['del_id']) ? $_POST['del_id'] : 0;

$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_keyitem = isset($_POST['keyitem']) ? $_POST['keyitem'] : null;
$_opsi_key = isset($_POST['opsi_key']) ? $_POST['opsi_key'] : $_POST['selected_opsi_key'];
$_confirm = !empty($_POST['confirm']) ? ($_POST['confirm'] == 1) : false;
$_manage = !empty($_POST['manage']) ? ($_POST['manage']) : false;
$_signature = isset($_POST['user_signature']) ? $_POST['user_signature'] : null;
$username = FULLNAME;

$items = explode(',', $_items);
$items = array_unique($items); //delete same value in array
$quantities = array();
$hidden_fields = null;

if ($_input != null){ // scan item
    $by = $_opsi_key;
	$item = get_item_by($_input, $by);
	$countExist = exist_item_stock_take($item[0]['id_item']);
    //echo $countExist; 
	if($countExist==0){
		if (!empty($item[0]['id_item'])){
				$items[] = $item[0]['id_item'];
		}
	}
}

$scanned_list = '<div id="itemspace"></div>';
if (count($items) > 0){
    // clean up for empty id item, deletion, calculation
    $tmp = array();
    foreach($items as $id_item){
        $id_item = trim($id_item);
        if (empty($id_item) || (($_del_id > 0) && ($_del_id == $id_item))) continue;

        $tmp[] = $id_item;
		$quantities[$id_item]=null;
		/*
        $stock = get_item($id_item);
        if (isset($quantities[$id_item])){
            if ($stock >= ($quantities[$id_item]+1))
                $quantities[$id_item]++;
        } else {
            if ($stock > 0)
                $quantities[$id_item] = 1;
        }
		*/
    }
    $items = $tmp;
    if ($_confirm && count(array_filter($items)) >0){
		//insert to table confirm stock take
		if($_manage ==1){ //manage multiple
			foreach($items as $id_item){
				$query = "INSERT INTO item_stock_take(id_item, status_take, user_name, remarks_take)
							VALUES ('$id_item', '$_POST[status_take]', '$username', '$_POST[remarks]')";
				mysql_query($query);
				$affected = mysql_affected_rows();
			
				if ($affected > 0){
					$idtrx = mysql_insert_id();
					// store signature
					if ($need_signature && ($_signature != null)){
						$query = "INSERT INTO item_stock_take_signature (id_trx, signature) 
									VALUES ($idtrx, '$_signature') ";
						mysql_query($query);
					}
				}
			}
			$value_removed = $items;
		}
		elseif($_manage ==2){ 
		//manage per 1 row
				$query = "SELECT * FROM item WHERE asset_no='$_keyitem' OR serial_no='$_keyitem'";
				$exec = mysql_query($query);
				$r = mysql_fetch_assoc($exec);

				$query = "INSERT INTO item_stock_take(id_item, status_take, user_name, remarks_take)
							VALUES ($r[id_item], '$_POST[status_take]', '$username', '$_POST[remarks]')";
				mysql_query($query);
				$affected = mysql_affected_rows();
				
				if ($affected > 0){
					$idtrx = mysql_insert_id();
					// store signature
					if ($need_signature && ($_signature != null)){
						$query = "INSERT INTO item_stock_take_signature (id_trx, signature) 
									VALUES ($idtrx, '$_signature') ";
						mysql_query($query);
					}
				}
				
				$value_removed = array($r['id_item']);
		}



        $_input = '';
        $_nric = '';
        $_id_user = '';
        $_items = '';
        $_full_name = '';
        $items = array_diff($items, $value_removed); //delete some values have been inserted in database 
    }


    // get item's info
	$query="SELECT item.*, full_name, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name, location_name 
               FROM item 
               LEFT JOIN category ON item.id_category=category.id_category 
               LEFT JOIN department ON category.id_department = department.id_department 
               LEFT JOIN status ON item.id_status=status.id_status 
               LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
               LEFT JOIN brand ON item.id_brand=brand.id_brand 
               LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
			   LEFT JOIN user ON item.issued_to=user.id_user
			   LEFT JOIN location ON item.id_location=location.id_location
               WHERE id_item IN (" . implode(',', $items) . ") AND id_item NOT IN (SELECT id_item from item_stock_take)
			   ORDER BY asset_no,serial_no DESC";
	$item_arr = get_item_by('querytag', '', $query);
	
    $no = 1;
    if (count($item_arr)>0){
        $scanned_list  = '<table class="consumable_item_list" cellpadding=3 cellspacing=3 width="800">';
        $scanned_list .= '<tr><th width=30>No</th><th width=120>Asset No.</th>
                          <th >Serial No.</th><th>Issued To</th><th>Location</th><th>Department</th><th width=100>Category</th><th width=120>Action</th></tr>';
        foreach($item_arr as $key => $rec){
			$viewitem = ' <a class="button" target="_blank" href="?mod=item&act=view&id='.$rec['id_item'].'"><image src="images/loupe.png"></a>';
            $dellink = '<a title="view" class="button delete" href="javascript:void(0)" onclick="del_this(' . $rec['id_item'] . ')"><image style="padding:2px 0 2px 0;" src="images/delete2.png"></a>';
            $managekey = $_opsi_key=='asset' ? $rec['asset_no'] : $rec['serial_no'];
			$manage = ' <a title="Manage" href="javascript:manage_this(2,\''.$managekey.'\')" class="button"><image src="images/sync_icon_small.png"></a>';
			$scanned_list .= '<tr><td align="center">' . ($no++) . '.</td><td>' .
                             $rec['asset_no'] . '</td><td>' . $rec['serial_no'] . '</td><td>' . $rec['full_name'] . '</td><td>' . $rec['location_name'] . '</td><td>' . $rec['department_name'] . '</td><td align="center">' . 
                             $rec['category_name'] . '</td><td>' . $dellink . $viewitem . $manage . '</td></tr>';
        }                
        $scanned_list .= '</table><br/>';
    }
}

?>
<br/>
<br/>

<script language="JavaScript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script language="JavaScript" src="//ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
<script language="JavaScript" src="./js/ScriptCam-master/scriptcam.js"></script>	
<script>
            $(document).ready(function() {
                $("#webcam").scriptcam({
                    onError:onError,
                    cornerRadius:0,
                    onWebcamReady:onWebcamReady,
					showDebug: true,
					path: './js/ScriptCam-master/',
					readBarCodes:'CODE_128,QR_CODE'


                });
            });
            function onError(errorId,errorMsg) {
                alert(errorMsg);
            }          
            function changeCamera() {
                $.scriptcam.changeCamera($('#cameraNames').val());
            }
            function onWebcamReady(cameraNames,camera,microphoneNames,microphone,volume) {
                $.each(cameraNames, function(index, text) {
                    $('#cameraNames').append( $('<option></option>').val(index).html(text) )
                });
                $('#cameraNames').val(camera);
            }
        </script>
<div id="form">
<form method="post" id="consumableform">
<input type="hidden" id="user_signature" name="user_signature" value="">
<input type="hidden" id="del_id" name="del_id" value="0">
<input type="hidden" id="manage" name="manage" value="<?php echo $_manage; ?>">
<input type="hidden" id="confirm" name="confirm" value="0">
<input type="hidden" id="nric" name="nric" value="<?php echo $_nric?>">
<input type="hidden" id="items" name="items" value="<?php echo implode(',', $items)?>">
<input type="hidden" id="keyitem" name="keyitem" value="<?php echo $_keyitem; ?>">
<input type="hidden" id="selected_opsi_key" name="selected_opsi_key" value="<?php echo $_opsi_key; ?>">
<input type="hidden" id="id_user" name="id_user" value="<?php echo $_id_user?>">
<?php
    echo $scanned_list;
    if ($_confirm && count(array_filter($items))==0){
        echo '<div id="cmdlabel">Item usage recorded! Click <a href="./?mod=item&act=stocktake">here</a> to make new record.</div>';
    } 
    else if (!$_confirm && $_manage){
?>
<table id="itemedit" cellpadding=3 cellspacing=1>
<tr><td>
<table class="itemlist" cellpadding=5 cellspacing=0>
    <tr><th>Fill form to completion</th><th style="text-align:right;"><label id="close">close</label></th></tr>
    <tr><td colspan=2 align="right"><br/> &nbsp;</td></tr>
	<?php if($_keyitem){?>
	<tr class="normal"><td><?php echo $_opsi_key=='asset' ? 'Asset No.' : 'Serial No.'; ?></td><td>
            <input type="text" id="itemcode" name="itemcode" value="<?php echo $_keyitem; ?>" size=24 autocomplete="off" readonly>           
    </td></tr>
	<?php } ?>
	<tr class="normal"><td>Status</td><td>
        <label><input type="radio" name="status_take" value="<?php echo VALID; ?>" checked>Valid</label>&nbsp;
		<label><input type="radio" name="status_take" value="<?php echo INVALID; ?>">Invalid</label>
    </td></tr>
	<tr class="alt"><td>Remarks</td><td>
            <textarea name="remarks"></textarea>           
    </td></tr>
<?php
    if ($need_signature) {
?>
    <tr class="normal" valign="top"><td>Signature</td><td>
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
    <tr><td colspan=2 align="right"><br/><input type="image" name="submit" id="submit" onclick="return confirm_this()" src="images/submit.png" /><br/>&nbsp;</td></tr>
</table>
</td></tr>
</table>

<?php
    } // manage    
    else {
        if (count($items) == 0){
            echo 'Scan an item by ';
			
			echo ' <select name="opsi_key"><option value="asset">asset</option><option value="serial">serial</option></select>';
		}
        else
            echo 'Scan another item by '.$_opsi_key;
?>
    <br/>    
    <br/>  
		<div style="width:330px">
			<div id="webcam">
			</div>
		</div>
		<p><a href='./?mod=item&act=setting_stocktake' class="button" >Setting</a></p>
		<div style="width:135px;">
			<p><button class="btn btn-small" id="btn1" onclick="fill_input($.scriptcam.getBarCode());check_entry();return false;" >Decode image</button></p>
			
		</div>	
    <input type="text" id="input" name="input" class="inputbox" autocomplete="off" onKeyUp="check_entry()">
	
		
<?php
    if (count($items)>0){
        echo '<br/>&nbsp;<br/>or click <a href="javascript:manage_this(1)" class="button manage">Manage</a> to proceed';

    } // item   > 0
    }
?>
</form>
</div>
<!--<script type="text/javascript" src="./js/plugin/delayePlugin.js"></script>-->
<script type="text/javascript">
$(function(){
	$("label[id='close']").hover(
		function() {
			$(this).css({'color':'blue','cursor':'pointer'});
		},
		function() {
			$(this).css({'color':'#fff','cursor':'pointer'});
		}
	);
	$("label[id='close']").click(function(){
		//$("table[id='itemedit']").toggle();
		$('#manage').val(0);
        $('form').submit();
	});
	
	// $("#input").delayKeyup(function(){
		// $('form').submit();
	// }, 5000);
});

var isbn_length = <?php echo ISBN_LENGTH?>;
var nric_length = <?php echo NRIC_LENGTH?>;
var asset_length = 15;
var serial_length = <?php echo SERIAL_LENGTH?>;
var need_signature = '<?php echo $need_signature ?>';
function check_entry()
{
    var v = $('#input').val();
    if (v.length >= asset_length)
		$('form').submit();    
    
}
function fill_input(text){
	var text_ex = text.split(',');
	$('#input').val(text_ex[0]);
	return text_ex;
}
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

function manage_this(id,key)
{
	$('#keyitem').val(key);
	$('#manage').val(id);
	$('form').submit();
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

$('.inputbox').focus();

</script>