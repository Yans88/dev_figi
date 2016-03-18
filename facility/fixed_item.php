
<?php 

if (!defined('FIGIPASS')) exit;
if (!$i_can_create) {
   include 'unauthorized.php';
   return;
}
$_facility = !empty($_POST['id_facility']) ? $_POST['id_facility'] : null;
$location_list = get_location_list();
if (count($location_list) == 0)
	$location_list[0] = '--- no location available! ---';
else
	$location_list = array('0' => '* select a location')+$location_list;

if (!empty($_POST['cmd'])){
	if ($_POST['cmd']=='remove'){
		$regno= $_POST['regno'];
		$query = "DELETE FROM facility_fixed_item WHERE id_facility = '$_facility' AND register_number = '$regno'";
		mysql_query($query);
		
	} else 
	if ($_POST['cmd']=='move'){
		$regno = substr($_POST['regno'],0,1);
		$regno_to = $_POST['regno_to'];
		
		$choose_template = $_POST['choose_template'];
		$rows = array();
		$query = "SELECT * FROM facility_fixed_item WHERE id_facility = '$_facility' AND register_number IN ('$regno', '$regno_to')";
		$rs = mysql_query($query);
;
		if ($rs){
			while ($rec = mysql_fetch_assoc($rs))
				$rows[$rec['register_number']] = $rec;
			//print_r($rows);
			if (count($rows)==2){// need 2 records to swap
				$id_item = $rows[$regno]['id_item'];
				$rows[$regno]['id_item'] = $rows[$regno_to]['id_item'];
				$rows[$regno_to]['id_item'] = $id_item;
				foreach ($rows as $regno => $rec){
					$query = "REPLACE INTO facility_fixed_item(id_facility, register_number, id_item) 
								VALUE('$_facility', '$regno', '$rec[id_item]')";
					mysql_query($query);
					
				//echo mysql_error().$query;
				}
			}
		}
		
	} else 
	if ($_POST['cmd']=='update_number'){
		$newnoi = $_POST['update_number'];
		$oldnoi = $_POST['max_regno'];
		$choose_template = $_POST['choose_template'];
		
		
		
		if ($newnoi > $oldnoi){ // increase
			if ($oldnoi == 0)
				mysql_query("DELETE FROM facility_fixed_item WHERE id_facility = '$_facility'");
			$values = array();
			for ($i=$oldnoi+1; $i<=$newnoi; $i++){
				
				$values[] = "($_facility, $i, 0, $choose_template)";
			}
			//echo count($values);
			//echo "<script>alert('".count($values)."');</script>";
			if (count($values)){
				
				$query = "INSERT INTO facility_fixed_item (id_facility, register_number, id_item, template) VALUES ".implode(', ', $values);
				mysql_query($query);
				error_log($query);
			}
		} else if ($newnoi < $oldnoi) { // decrease
			$query = "DELETE FROM facility_fixed_item WHERE id_facility = '$_facility' AND register_number > $newnoi";
			mysql_query($query);
			//echo mysql_error().$query;
		}
	}
}

$max_regno = 0;
$total_item = 0;
$items = array();
$query = "SELECT f.id_facility, f.id_item, f.register_number, i.asset_no, l.location_name, f.template 
			FROM facility_fixed_item f 
			LEFT JOIN item i ON i.id_item = f.id_item
			LEFT JOIN location l ON l.id_location = f.id_facility 
			WHERE f.id_facility = '$_facility' 
			ORDER BY f.register_number ASC";
$rs = mysql_query($query);
if ($rs){
	$total_item = mysql_num_rows($rs);
	if ($total_item>0){
		while ($rec = mysql_fetch_assoc($rs)) {
			$items[$rec['register_number']] = $rec;
		}
		$query = "SELECT MAX(register_number) max_regno, MAX(template) as template FROM facility_fixed_item WHERE id_facility = '$_facility'";
		$rs = mysql_query($query);
		$rec = mysql_fetch_assoc($rs);
		$max_regno = $rec['max_regno'];
		$choose_template = $rec['template'];
	}
}


?>

<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />
<link rel="stylesheet" type="text/css" href="style/default/student_usage.css">
<br/>
<form method="POST" id="facility_fix" style='color:#fff;'>
<table cellspacing=1 cellpadding=2 id="">
<input type="hidden" name="id_item" class="id_item" id="id_item">
	<tr>
        <td width=90>Facility/Room</td>
			<td>
				<select name="id_facility" id="id_facility" class="id_facility">
				<?php echo build_option($location_list, $_facility);?>
				</select>
			</td>
    </tr>
<?php if ($_facility>0){ ?>
	<tr>
		<td>Number of Items</td>
		<td>
			<input type="text" name="number_of_items" id="number_of_items" style="width: 20px" value="<?php echo $max_regno?>"> 
			
		</td>
	</tr>
	<tr>
		<td>Choose Template</td>
		<td>
			<?php
			for($f=1;$f<5;$f++){
				$template[$f] = "Template ". $f;
			}
			?>
			<select name='choose_template' id='choose_template'>
			<option value="0">-- Choose Template --</option>
				<?php echo build_option($template, $choose_template);?>
			</select>
			<button type="button" id="update_number">Update</button>
		</td>
	</tr>
	
<?php } ?>
	<tr>
		<td colspan='2' align='center'><a href='#show_list_of_have_template' id='show_list_of_have_template' class='button'>Show Facility have Template</a></td>
	</tr>
</table>

<input type="hidden" name="regno" value=0>
<input type="hidden" name="regno_to" value=0>
<input type="hidden" name="item" value=0>
<input type="hidden" name="cmd" value=''>
<input type="hidden" name="max_regno" value='<?php echo $max_regno?>'>
<input type="hidden" name="choose_template_2" value='<?php echo $choose_template;?>'>



</form>
<br><br>


	<div id="fixed_item_list">

	<?php 
	

	
	if($choose_template == 1){
		echo "<div class='container_template_right_1'>";
	
	for($x=1;$x<=$max_regno;$x++){
		
		$assetNo = '--';
		$remove = '<a class="disabled">Move Item</a> &nbsp; <a class="disabled">Remove</a>';
		if (!empty($items[$x])){
			$rec = $items[$x];
			if (!empty($rec['id_item'])){
				$assetNo = $rec['asset_no'];
				$remove ='<a href="#move" id="move-'.$x.'-'.$choose_template.'" class="move">Move Item</a> &nbsp;';
				$remove .= '<a href="#remove" id="remove-'.$x.'" class="remove">Remove</a>';
			}
		}
		
		$assign_item = '<a href="#assign" id="assign-'.$x.'" class="assign">Assign Item</a>';
		
		if($x > 13){$css ="container_template_right_2";} else { $css ="container_template_right_1";}
		if(($x % 4 == 0) && ($x !=40)){ $y = " </div> <div class='".$css."'> "; } else { $y = ""; }
		if($x % 4 == 1) { $j="bottom";}
		if($x % 4 == 2) { $j="left";}
		if($x % 4 == 3) { $j="top";}
		if($x % 4 == 0) { $j="right";}
		
		echo "<div class='lab1_".$j."'> ";
			

$assetNo = '';
$remove = '<a class="disabled">Move Item</a> &nbsp; <a class="disabled">Remove</a>';
if (!empty($items[$x])){
	$rec = $items[$x];
	if (!empty($rec['id_item'])){
		$assetNo = $rec['asset_no'];
		$remove ='<a href="#move" id="move-'.$x.'-'.$choose_template.'" class="move">Move Item</a> &nbsp;';
		$remove .= '<a href="#remove" id="remove-'.$x.'" class="remove">Remove</a>';
	}
}
$assign_item = '<a href="#assign" id="assign-'.$x.'" class="assign">Assign Item</a>';

			echo $x.". ".$assetNo."<br />".$assign_item."<br />".$remove;
			
		echo "</div> ".$y;
		
	} 
	echo "</div>";
} else if($choose_template == 2){
	
	for($s=1;$s<=$max_regno;$s++){
	
	
		$assetNo = '';
		$remove = '<a class="disabled">Move Item</a> &nbsp; <a class="disabled">Remove</a>';
		if (!empty($items[$s])){
			$rec = $items[$s];
			if (!empty($rec['id_item'])){
				$assetNo = $rec['asset_no'];
				$remove ='<a href="#move" id="move-'.$s.'-'.$choose_template.'" class="move">Move Item</a> &nbsp;';
				$remove .= '<a href="#remove" id="remove-'.$s.'" class="remove">Remove</a>';
			}
		}
		$assign_item = '<a href="#assign" id="assign-'.$s.'" class="assign">Assign Item</a>';


		if($s >= 1 && $s<= 38){
		
			if($s % 2 == 0){ 
				//belakang
				if($s % 10 == 0){
					
					echo "<div class='css_fields_left_2'>";
					
					echo $s.". ".$assetNo."<br />
						".$assign_item."<br />
						".$remove;
						
						
					echo "	</div></div></div>";
				} else {
					
					echo "<div class='css_fields_left_2'>".$s.". ".$assetNo."<br />".$assign_item."<br />".$remove."</div></div>";
				}
			} else { 
				if($s % 10 == 1){
					if($s > 30){ 
						$css = "container_left"; 
					} else {
						$css = "container_left_1";
					}
					echo "<div class='".$css."'><div class='body'><div class='css_fields_left_1'>".$s.". ".$assetNo."<br />".$assign_item."<br />".$remove."</div>";
				} else {
					echo "<div class='body'><div class='css_fields_left_1'>".$s.". ".$assetNo."<br />".$assign_item."<br />".$remove."</div>";
				}
			}
		}
		
		if($s > 38 && $s<= 41){
		
			if($s % 2 == 0){
				echo "<div class='css_fields_left_2'>".$s.". ".$assetNo."<br />".$assign_item."<br />".$remove."</div></div>";
			} else {
				echo "</div><div class='container_left_center'><div class='body'><div class='css_fields_left_1'>".$s.". ".$assetNo."<br />".$assign_item."<br />".$remove."</div>";
			}
		
		}
		
		
		
	}

} else if($choose_template == 3){

	echo "<div class='container_template_left_1'>";
	
	for($x=1;$x<=$max_regno;$x++){
		
		$assetNo = '';
		$remove = '<a class="disabled">Move Item</a> &nbsp; <a class="disabled">Remove</a>';
		if (!empty($items[$x])){
			$rec = $items[$x];
			if (!empty($rec['id_item'])){
				$assetNo = $rec['asset_no'];
				$remove ='<a href="#move" id="move-'.$x.'-'.$choose_template.'" class="move">Move Item</a> &nbsp;';
				$remove .= '<a href="#remove" id="remove-'.$x.'" class="remove">Remove</a>';
			}
		}
		$assign_item = '<a href="#assign" id="assign-'.$x.'" class="assign">Assign Item</a>';
		
		if($x > 13){$css ="container_template_left_2";} else { $css ="container_template_left_1";}
		if(($x % 4 == 0) && ($x !=40)){ $y = " </div> <div class='".$css."'> "; } else { $y = ""; }
		if($x % 4 == 1) { $j="bottom";}
		if($x % 4 == 2) { $j="left";}
		if($x % 4 == 3) { $j="top";}
		if($x % 4 == 0) { $j="right";}
		
		echo "<div class='lab1_".$j."'> ";
			
			echo $x.". ".$assetNo."<br />".$assign_item."<br />".$remove;
		
		echo "</div> ".$y;
		
	} 
	echo "</div>";
	
} else if($choose_template == 4){
	
	for($s=1;$s<=$max_regno;$s++){

	
$assetNo = '';
$remove = '<a class="disabled">Move Item</a> &nbsp; <a class="disabled">Remove</a>';
if (!empty($items[$s])){
	$rec = $items[$s];
	if (!empty($rec['id_item'])){
		$assetNo = $rec['asset_no'];
		$remove ='<a href="#move" id="move-'.$s.'-'.$choose_template.'" class="move">Move Item</a> &nbsp;';
		$remove .= '<a href="#remove" id="remove-'.$s.'" class="remove">Remove</a>';
	}
}
$assign_item = '<a href="#assign" id="assign-'.$s.'" class="assign">Assign Item</a>';

		
		if($s >= 1 && $s<= 38){
			if($s % 2 == 0){ 
				
				//belakang
				if($s % 10 == 0){
					echo "<div class='css_fields_right_2'>".$s.". ".$assetNo."<br />".$assign_item."<br />".$remove."</div></div></div>";
				} else {
					echo "<div class='css_fields_right_2'>".$s.". ".$assetNo."<br />".$assign_item."<br />".$remove."</div></div>";
				}
			} else { 
				
				if($s % 10 == 1){
					if($s > 30){ 
						$css = "container_right"; 
					} else {
						$css = "container_right_1";
					}
					
					echo "<div class='".$css."'><div class='body'><div class='css_fields_left_1'>".$s.". ".$assetNo."<br />".$assign_item."<br />".$remove."</div>";
				} else {
				
					echo "<div class='body'><div class='css_fields_right_1'>".$s.". ".$assetNo."<br />".$assign_item."<br />".$remove."</div>";
				}
			}
		}
		
		if($s > 38 && $s<= 41){
		
			if($s % 2 == 0){
				echo "<div class='css_fields_left_2'>".$s.". ".$assetNo."<br />".$assign_item."<br />".$remove."</div></div>";
			} else {
				echo "</div><div class='container_left_center'><div class='body'><div class='css_fields_left_1'>".$s.". ".$assetNo."<br />".$assign_item."<br />".$remove."</div>";
			}
		
		}
		
	}
} else if ($max_regno>0){

		for ($regno = 1; $regno <= $max_regno; $regno++) {	
			$assetNo = '-';
			$remove = '<a class="disabled">Move Item</a> &nbsp; <a class="disabled">Remove</a>';
			if (!empty($items[$regno])){
				$rec = $items[$regno];
				if (!empty($rec['id_item'])){
					$assetNo = $rec['asset_no'];
					$remove ='<a href="#move" id="move-'.$regno.'" class="move">Move Item</a> &nbsp;';
					$remove .= '<a href="#remove" id="remove-'.$regno.'" class="remove">Remove</a>';
				}
			}
	echo <<<DATA
		<div class="fixed_item">
			<div class="register_number">$regno</div>
			<div class="asset_no"><span>Asset No.</span><br>$assetNo</div>
			<div class="buttons">
				<a href="#assign" id="assign-$regno" class="assign">Assign Item</a> &nbsp;
				$remove
			</div>
		</div>		
DATA;

		}
}
	?>
	</div>



<div id="manage_item" class="manage_student" style="display: none">
<h3>Manage Item Position</h3>
<h4>Move/Swap Item</h4>
<div>
    Enter destination reg. no. &nbsp; 
    <input type="text" style="width: 30px;" id="move_to_regno" name="move_to_regno"> 
    <button type="button" id="move">Move / Swap</button>
</div>
<div class="clear"></div>
<div id="msg" style="height: 20px;"></div>
</div>


<script>

$(document).ready(function(){
	$('#editFacility').hide();		
})

$('#id_facility').change(function(){	
	$('#facility_fix').submit();		
});

$('.assign').click(function(){
	var loc = $('#id_facility').val();
	var regno = this.id.substr(7);
	var template = $('#choose_template').val();
	
	$.fancybox.open({
		type: 'iframe',
		href: './?mod=facility&sub=fixed_item_assign&loc='+loc+'&regno='+regno+'&template='+template,
		padding: 5
		
	});
	
});

$('.move').click(function(){
	var loc = $('#id_facility').val();
	var regno = this.id.substr(5);
	var template =  this.id.substr(7);
	
    $('input[name=regno]').val(regno);
	$("#choose_template").val(template);
	
	$.fancybox.open({
		href: '#manage_item',
		padding: 5
	});
	$('input[name=move_to_regno]').focus();
});

$('#move').click(function(){
    var move_to_regno = $('#move_to_regno').val();
	var template = $('#choose_template').val();
    if (move_to_regno > 0){
		$('input[name=cmd]').val('move');
		$('input[name=regno_to]').val(move_to_regno);
		$('input[name=choose_template]').val(template);
		
        $('#facility_fix').submit();
    } else {
        alert('Enter correct registration number for destination!');
        $('#move_to_regno').focus();
    }
});


$('.remove').click(function(){
	if (confirm('Do you sure remove the item from the position?')){
		var form = $('#facility_fix').get(0);
		var regno = this.id.substr(7);
		form.regno.value = regno;
		form.cmd.value = 'remove';
		form.submit();
	}
});

$('#show_list_of_have_template').click(function(){

	$.fancybox.open({
		href: '#list_of_have_template',
		padding: 2
		
	});

});

$('#choose_template').change(function(){
	
	var id= this.value;
	$.fancybox.open({
		href: '#show_image_'+id,
		padding: 2
		
	});
	
});

$('#update_number').click(function(){
	var old_noi = parseInt('<?php echo $max_regno;?>');
	var noi = parseInt($('#number_of_items').val());
	var choose_template = parseInt('<?php echo $choose_template;?>');
	
	var msg = '';
	if (old_noi>noi)
		msg = 'New number of items is less than current, it will remove items with register number '+(noi+1)+' onwards. ';
	if (old_noi==0||confirm(msg+'Do you sure update number of items for this location?')){
		$('#facility_fix').append('<input type="hidden" name="update_number" value='+noi+'>');
		
		
		$('input[name=cmd]').val('update_number');
		$('#facility_fix').submit();
	}
});
</script>
<?php
//==================
/*
if (!empty($items[$s])){
	$rec = $items[$s];
	if (!empty($rec['id_item'])){
		$assetNo = $rec['asset_no'];
		$remove ='<a href="#move" id="move-'.$s.'-'.$choose_template.'" class="move">Move Item</a> &nbsp;';
		$remove .= '<a href="#remove" id="remove-'.$s.'" class="remove">Remove</a>';
	}
}
$assign_item = '<a href="#assign" id="assign-'.$s.'" class="assign">Assign Item</a>';
*/ 
//====================

?>

<div id='show_image_1' style='display:none;'>
	<img src='<?php echo $base_url;?>/images/1.jpg'>
</div>
<div id='show_image_2' style='display:none;'>
	<img src='<?php echo $base_url;?>/images/2.jpg'>
</div>
<div id='show_image_3' style='display:none;'>
	<img src='<?php echo $base_url;?>/images/3.jpg'>
</div>
<div id='show_image_4' style='display:none;'>
	<img src='<?php echo $base_url;?>/images/4.jpg'>
</div>

<div style='display:none;min-height:300px;' id="list_of_have_template" class='manage_student'>
<h4>List Of Facility Already Have Template</h4>
<table class='itemlist' width='300px' cellspacing='5' cellpadding='5'>
<tr>
	<th>No</th><th>Location</th> <th>Template</th>
</tr>
<?php
	$xd = get_list_room_have_template();
	$counter = 0;
	foreach($xd as $data_fixedItem => $value){
	$counter++;
		echo"
		<tr>
			 <td align='center'>".$counter."</td><td align='left'>".$value[0]."</td> <td align='center'>Template ".$value[1]."</td>
		</tr>
		";
		
	}
?>
</table>
</div>

<div class="clear"></div>