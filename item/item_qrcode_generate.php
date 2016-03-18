<button onclick='return PrintDoc()'>Preview & Print</button>
<script>
function PrintDoc() {

        var toPrint = document.getElementById('printarea');

        var popupWin = window.open('', '_blank', 'scrollbars=1,max-width=695px,height=900px,location=no,left=200px');

        popupWin.document.open();

        popupWin.document.write('<html><title>Preview</title></head><body onload="window.print()">')

        popupWin.document.write(toPrint.innerHTML);

        popupWin.document.write('</html>');

        popupWin.document.close();

    }
</script>
<div id="printarea">
	<style>
		.papers{
			size:A4;
			max-width:188mm;
			height:1020px;
			background-color:#fff;
			clear:both;
			padding-top:20px;
			padding-left:25px;
			padding-bottom:20xp;
			margin-bottom:20px;
			font-size:10px;
			overflow:hidden;
			
			
		}

		.papers .listing{
			border:1px solid #000;
			border-radius:10px;
			moz-border-radius:10px;
			float:left;
			margin:1px;
			width:220px;
			height:150px;
			color:#000;
		}

		
		.papers img.qrcode{
			width:55px;height:55px;
			cursor:pointer;padding:3px;
			float:left;
		}

		.papers img.barcode{
			width:200px;height:20px;
			cursor:pointer;
			padding:0 0 0 10px;
			margin:0;clear:both;
			padding:0;float:left;
		}
		
		.papers .list-one		{padding:1px;}
		.papers .list-one p 	{text-align:left;padding-top:4px;margin:0;}
		.papers .list-two		{padding:0px;margin:0px;}
		.papers .list-two p 	{clear:both;padding:0px;text-align:center;}
		.papers .list-tree		{padding:0;margin:0px;}
		.papers .list-tree p	{clear:both;padding:0px;text-align:center;}
	</style>

<?php
$data_arr = array();
$data_array = $_POST['sel'];

$total_data = count($data_array);


if($total_data == null){
	echo "<script>alert('You must select the data first');location.href=document.URL;</script>";
}
for($i=0;$i<$total_data;$i++){

/*

	$query = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name  
			   FROM item 
			   LEFT JOIN category ON item.id_category=category.id_category 
			   LEFT JOIN department ON category.id_department = department.id_department
			   LEFT JOIN status ON item.id_status=status.id_status 
			   LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
			   LEFT JOIN brand ON item.id_brand=brand.id_brand 
			   LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
			   WHERE category_type = 'EQUIPMENT'";
			   // 
*/
	//NEW QUERY
	$query = "
	SELECT item.*, category.category_name, vendor.vendor_name, location.location_name, brand.brand_name, status.status_name, department.department_name
		FROM item 
		LEFT JOIN category ON category.id_category = item.id_category
		LEFT JOIN vendor ON vendor.id_vendor = item.id_vendor
		LEFT JOIN location ON location.id_location = item.id_location
		LEFT JOIN brand ON brand.id_brand = item.id_brand
		LEFT JOIN status ON status.id_status = item.id_status
		LEFT JOIN department ON department.id_department = item.id_department
	WHERE category.category_type = 'EQUIPMENT'";
	$query .= " AND item.asset_no = '".$data_array[$i]."' ";

	if (!empty($dept))
		$query .= ' AND item.id_department = '.$dept.' ';

		
	$query .= " ORDER BY $_orderby $_orderdir LIMIT $_limit ";
	
	
	//echo $query;
	$rs = mysql_query($query);
	$rec = mysql_fetch_array($rs);
	
	$qr->text($rec['asset_no']."\r\n".$rec['serial_no']."\r\n".$rec['department_name']."\r\n".$rec['category_name']."\r\n".$rec['brand_name']."\r\n".$rec['model_no']);
	$data_arr[] = "<div class='listing'>	
						<div style='text-align:center;'>Singapore Chinese Girls' School</div>
						<div class='list-one'>
							<img class='qrcode' src='".$qr->get_link()."' border='0'> 
							<p>
								".$rec['department_name']."<br />".$rec['category_name']."<br />".$rec['brand_name']."<br />".$rec['model_no']."
							
							</p>
						</div>
						<div class='list-two'>
							<img class='barcode' src='item/lib_barcode/barcode.php?text=".$rec['asset_no']."' alt='".$rec['asset_no']."'><p> ".$rec['asset_no']."</p>
						</div>
						<div class='list-tree'>
							<img class='barcode' src='item/lib_barcode/barcode.php?text=".$rec['serial_no']."' alt='".$rec['serial_no']."'><p> ".$rec['serial_no']."</p>
						</div>
					</div>";

//echo $query."<br />";
}



for ($o = 0; $o < $total_data; $o++){
				if($o % 18 == 0){
							
							echo "	<div class='papers'><div class='barlisting'>";
							
							if($o == 0){
								for($vv = 0; $vv<18;$vv++){
									echo $data_arr[$vv];
								}
							} 
							
							if($o == 18){
							
								for($ww = 18; $ww<36;$ww++){
									echo $data_arr[$ww];
								}
							
							}
							
							if($o == 36){
								for($xx = 36; $xx<54;$xx++){
									echo $data_arr[$xx];
								}
							}
							
							if($o == 54){
								for($yy = 54; $yy<72;$yy++){
									echo $data_arr[$yy];
								}
							}
							
							if($o == 72){
								for($zz = 72; $zz<90;$zz++){
									echo $data_arr[$zz];
								}
							}
							
							if($o == 90){
								for($aa = 90; $aa<107;$aa++){
									echo $data_arr[$aa];
								}
							}
							echo "</div></div>";
							
						} 
			}
			
?>
</div>