<?php 

$tbl = '';
if (!defined('FIGIPASS')) exit;
ob_clean();
require 'header_popup.php';
require 'maintenance_util.php';

$id_location = !empty($_GET['loc']) ? $_GET['loc'] : 0;
$id_category = !empty($_GET['cat']) ? $_GET['cat'] : 0;

$equipment = null;
$query = "SELECT cce.*, i.asset_no, i.serial_no
            FROM checklist_checking_equipment cce LEFT JOIN item i  ON i.id_item = cce.id_item
            WHERE cce.id_location = $id_location AND cce.id_category = $id_category";
$rs = mysql_query($query);
if ($rs && mysql_numrows($rs)>0)
    $equipment = mysql_fetch_assoc($rs);

$location_list = get_location_list();
$location_name = $location_list[$id_location];
        
$category = get_category($id_category);
$category_name = $category['category_name'];
?>

<script type="text/javascript" src="./js/jquery.opacityrollover.js"></script>
<br/>
<h4>Link an Item to Checklist Category</h4>
<form method="POST" id="">
<input type="hidden" name="id_item" id="id_item">
<input type="hidden" name="id_location" id="id_location" value="<?php echo $id_location?>">
<input type="hidden" name="id_category" id="id_category" value="<?php echo $id_category?>">

<table cellspacing=1 cellpadding=2 class="item" width="98%">
   <tr>
        <td width=80>Location</td>
        <td><?php echo $location_name;?></td>
    </tr>  
   <tr>
        <td>Category</td>
        <td><?php echo $category_name;?></td>
    </tr>  
<?php if (!empty($equipment)){ ?>
   <tr>
        <td>Linked Item</td>
        <td><a target="new_win" href="./?mod=item&act=view&id=<?php echo $equipment['id_item']?>"><?php echo $equipment['asset_no']?></a></td>
    </tr>  
<?php }// equipment ?>
    <tr>
        <td valign="top">Find Item </td>
        <td><span class="field-note info">(* type asset no / serial no )</span></td>
    </tr>  
    <tr>
        <td colspan=2 class="center">
             <input type="text" id="edit_item" name="serial_no" style="width: 98%;" onKeyUp="suggest(this, this.value);" autocomplete="off" >
            <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500; left: 0px; width:auto;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsList"></div>
            </div>
        </td>
    </tr>  
</table>
<p class="center">
<button onclick="add_item()" type="button">Link Selected Item to Checklist</button>
</p>
<br>
<p class="center" id="progress" style="display: none"><img src="images/fancybox_loading.gif"></p>
</form>
<br><br>

<div>
<script>
$(document).ready(function(){
	
});

function suggest(me, inputString)
{
	var id_location = $("#id_location").val();
	if (inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
        if (/,/.test(inputString)){
            var mathces = /.*, *(.+)/.exec(inputString);
            if (mathces != null)
                inputString = mathces[1];
        }
        var pd = {queryString: ""+inputString+"", inputId: ""+me.id+"", loc_id: id_location };
		$.post("facility/suggest_item.php", pd, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			} else
                $('#suggestions').fadeOut();
		});
	}
}

function fill(id, thisValue, onclick) 
{
	$('#id_item').val('');
	$('#item_edit').val('');
	if (thisValue.length>0 && onclick){
		var cols = thisValue.split('|');
		$('#'+id).val(cols[1] + ', ' + cols[0] + ', ' + cols[2] + ', ' + cols[3] + ', ' + cols[4]);
		$('#id_item').val(cols[5]);
		$('#item_edit').val(cols[5]);
	}
	setTimeout("$('#suggestion').fadeOut();", 100);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function add_item(){
	var id_location = $("#id_location").val();
	var id_category = $('#id_category').val();
	var id_item = $('#id_item').val();
	var data = {id_location: id_location, id_item: id_item, id_category: id_category, assign: 1};
    var close_btn = '<br><p class="center"><button type="button" onclick="javascript:parent.location.reload()">Close</button></p>';
	if (id_location > 0 && id_category > 0 && id_item > 0){
        $('#progress').show();
        $.post("./?mod=maintenance&sub=category", data, function(data){
            //$('#progress').hide();
            if (data.length>0){
                if ('ASSIGN:OK' == data)
                    $('#progress').html('Item has been assigned to location and category.'+close_btn);
                else if ('ASSIGN:EXISTS' == data)
                    $('#progress').html('Error: Item has been assigned!'+close_btn);
                else
                    $('#progress').html('Error: Item assigns has been failed!'+close_btn);
        
            }
		});
        
	} else {
		alert("Please input the asset number or serial no!")
	}	
}

$('#edit_item').focus();
</script>
