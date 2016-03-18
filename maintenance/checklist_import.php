<?php
if (!defined('FIGIPASS')) exit;
require 'maintenance_util.php';


$key_import = !empty($_POST['key_import']) ? $_POST['key_import'] : 0;


if ($key_import == 'import'){
	$target_dir = $root_path."maintenance/uploads/";
	$target_file = $target_dir . basename($_FILES["csv"]["name"]);
	
	if (move_uploaded_file($_FILES["csv"]["tmp_name"], $target_file)) {			
			$dt_maintenace = add_data_maintenance($target_file);
	}
}

function add_data_maintenance($path){
	$row = 1;	
	$_result = array();
	$_data = array();
	$dt = array();
	if (($handle = fopen($path, "r")) !== FALSE) {		
		$fp = file($path);	
		
		while (($data = fgetcsv($handle, 1000, ",",'"')) !== FALSE) {
			if($row > 1){
			$location = htmlspecialchars($data[0], ENT_QUOTES);
			$create_by = htmlspecialchars($data[1], ENT_QUOTES); //nric_create
			$_create_on = htmlspecialchars($data[2], ENT_QUOTES);
			$modify_by = htmlspecialchars($data[3], ENT_QUOTES); //nric_modify			
			$_modify_on = htmlspecialchars($data[4], ENT_QUOTES);
			$assets = htmlspecialchars($data[5], ENT_QUOTES);
			$result = htmlspecialchars($data[6], ENT_QUOTES);
			$remark = htmlspecialchars($data[7], ENT_QUOTES);
			$create_on = convert_date($_create_on, 'Y-m-d H:i:s');
			$modify_on = convert_date($_modify_on, 'Y-m-d H:i:s');
			$id_term = 0;			
			$_data = array(
				'location' => $location,
				'create_by' => $create_by,
				'create_on' => $create_on,
				'modify_by' => $modify_by,
				'assets' => $assets,
				'modify_on' => $modify_on,
				'result' => $result,
				'remark' => $remark
			);
			$dt[] = $_data;
		}
		$_result = array();
		foreach($dt as $_dt=>$my_data){
			$id_location = get_id_location($my_data['location']);
			if(!empty($id_location)){
				$create_by = get_id_user($my_data['create_by']);
				if(!empty($create_by)){
					$modify_id = get_id_user($my_data['modify_by']);
					if(!empty($modify_id)){						
						$id_asset = get_id_asset($my_data['assets']);
						if(!empty($id_asset)){
							$_result['valid_data'][] = $my_data + array('class' => 'valid_data', 
								'msg' => 'Data is ok', 'id_location' => $id_location, 'id_create' => $create_by,
								'id_modify' => $modify_id, 'id_asset' => $id_asset);
						}else{
							$_result['invalid_asset_id'][] = $my_data + array('class' => 'invalid_asset_id','msg' => 'Invalid asset no');
						}
					}else{
						$_result['invalid_modify_id'][] = $my_data + array('class' => 'invalid_modify_id','msg' => 'Invalid NRIC(modify by)');
					}
				}else{
					$_result['invalid_create_id'][] = $my_data + array('class' => 'invalid_create_id', 'msg' => 'Invalid NRIC(create by)');
				}
			}else{
				$_result['invalid_loc'][] = $my_data + array('class' => 'invalid_loc', 'msg' => 'Invalid Location Name');
			}			
		}
		$row++;		
	}
	}
	return $_result;
}

function get_id_user($nric){
	$query = "select id_user from user where nric = '$nric'";
	$mysql_query = mysql_query($query);
	$data =mysql_fetch_array($mysql_query);	
	return $data['id_user'];
}
	
function get_id_asset($assets){
	$_assets = '0'.$assets;
	$query = "select id_item from item where asset_no = '$assets' or asset_no = '$_assets'";
	$mysql_query = mysql_query($query);
	
	$data =mysql_fetch_array($mysql_query);	
	return $data['id_item'];	
}

function get_id_location($location){
	$query = "select id_location from location where location_name = '$location'";
	$mysql_query = mysql_query($query);
	$data =mysql_fetch_array($mysql_query);	
	return $data['id_location'];
}

$valid_data = array();
if(!empty($dt_maintenace)){
	echo '<h4 class="center">Preview data upload</h4>';    
    echo '<div class="space5-top center">';    
    echo '<table id="itemlist" cellpadding="0" cellspacing="0" class="itemlist">';
    echo '<tr><th>No.</th><th>Location</th>
	<th>NRIC(create by)</th><th>Create On</th><th>NRIC(modify by)</th><th>Modify On</th><th>Asset No</th><th>Result</th><th>Remark</th>
	<th>Status</th></tr>'; 
	$no = 1;
$i=1;	
	foreach($dt_maintenace as $_maintenance=>$maintenance){		
		foreach($maintenance as $_all=>$all){			
			if($all['class'] == 'valid_data'){
				$id_location = $all['id_location'];
				$id_create = $all['id_create'];
				$id_modify = $all['id_modify'];
				$id_asset = $all['id_asset'];
				$create_on = $all['create_on'];
				$modify_on = $all['modify_on'];
				$result = $all['result'];
				$remark = $all['remark'];
				$valid_data[] = $id_location.','.$id_create.','.$create_on.','.$id_asset.','.$id_modify.','.$modify_on.','.$result.','.$remark;				
			}
			$_valid = implode(';', $valid_data);
			 echo <<<REC
    <tr><td>$no.</td>
        <td>$all[location]</td> <td>$all[create_by]</td> 	  
        <td>$all[create_on]</td> <td>$all[modify_by]</td> 	  
        <td>$all[modify_on]</td> <td>$all[assets]</td> 
		<td>$all[result]</td> <td>$all[remark]</td><td>$all[msg]</td>  	  
    </tr>
REC;
	$no++;
		}
	}
	echo '</table></div><br/>';
	
	if(count($valid_data) > 0){
		echo '<div style="float:right;"><input type="button" name="btn_cancel" id="btn_cancel" value="Cancel" >&nbsp;';
		echo '<input type="button" name="btn_submit" id="btn_submit" value="Confirm" ></div>';
	}	
}else{

?>

<div class="submod_wrap">
	<div class="submod_title"></div>
	<div class="submod_links">	
	</div>
</div>
<form method="POST" enctype="multipart/form-data">
<table>
	<tr>
		<td align="center"><h3>Select a CSV file</h3></td>		
	</tr>
	<tr>
		<td align="center">
			<input type="hidden" name="key_import" id="key_import" value="">
			<input type="file" name="csv" id="csv" value="Select file">
		</td>		
	</tr>
	<tr>
		<td align="center">
			<input type="button" name="btn_import" id="btn_import" value=" Import(s) " > 
		</td>
	</tr>
	<tr>
		<td align="center">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td align="center">
			To download CSV template, Click <a href="./?mod=maintenance&sub=checklist&act=import_template">Here</a>
		</td>
		
	</tr>
</table>
<?php } ?>
<script>

var data_valid = '<?php echo $_valid;?>';
$('#btn_submit').click(function(){	
	$.post("maintenance/maintenance_submit_import.php", {data: ""+data_valid+""}, function(data){
		if(data == 'ok'){
			location.href = '?mod=maintenance';	
		}else{
			alert('Import data failed');
		}
	})
});

$('#id_location').change(function(){
	this.form.submit();
});
$('#btn_import').click(function(){	
	var csv = $('#csv').val();
	$('#key_import').val('import');
	
	
	if(csv.length == ''){
		alert('Please select a csv file to be uploaded!');
		return false;
	}else{
		this.form.submit();	
	}	
	this.form.submit();	
});

	function checkfile(frm){
		if (frm.csv.files.length == 0){
			alert('Please select a csv file to be uploaded!');
			return false;
		}
		return true;
	}
</script>
