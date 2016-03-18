<?php
include '../common.php';
include '../authcheck.php';

if (!defined('FIGIPASS')){
	echo '<script>location.href="../";</script>';
	exit ;
}

if(!defined('SUPERADMIN'))
	define('SUPERADMIN',1);

if(USERGROUP != SUPERADMIN && USERGROUP != GRPADM && USERGROUP != GRPHOD){
	echo '<script>location.href="../";</script>';
	exit;
}


$_id_location = $_REQUEST['id_location'];


$_count_id = count($_id_location);

$style="<style>table tr td{margin-bottom:100px;} body{font-family:  arial, helvetica, serif, verdana; font-size: 12px;}
.logo,.maintitle{padding:0 0 0 0;} .content td{padding:5px 0 5px 0;} thead th{padding:0 0 0px 0;} tbody th{padding:15px 0 0 0;} th{font-size: 13px; text-align:left;} .counter{ border:0px solid #333; width:1150px; margin:0 auto;} .counter header{margin-bottom:15px;}</style>";


function print_content($id_location){

$currentUser = ucfirst(strtolower(FULLNAME));
//echo date('l jS \of F Y h:i:s A');
$currentTime = date('jS F Y H:i').'hrs';

$logo=<<<LOGO
<div class="logo" style="margin-bottom:20px;"><img src="../images/logo_print.png"></div>
LOGO;

$mainTitle=<<<TTL
<table class="maintitle" width="400px">
<thead>
<tr><th colspan="2">Stock Take Report</th><th></th></tr>
<tr><th width="170px">Item Status of</th><th>$currentTime</th></tr>
</thead>
<tbody>
<tr><th>Date of Stock Take</th><th>_______________________</th></tr>
<!--<tr><th>Stock Take done by</th><th>$currentUser</th></tr>-->
<tr><th>Stock Take done by</th><th>_______________________</th></tr>
</tbody>
</table>
TTL;

	$query_all = "SELECT item.*,asset_no, serial_no, category_name, brand_name, model_no, status_name, full_name AS issued_to 
				FROM item 
				INNER JOIN category ON category.id_category=item.id_category
				INNER JOIN brand ON brand.id_brand=item.id_brand 
				INNER JOIN status ON status.id_status=item.id_status
				INNER JOIN user ON user.id_user=item.issued_to
			 WHERE category_type = 'EQUIPMENT' AND id_location='$id_location'";

	$query_loca = "SELECT * FROM location WHERE id_location='$id_location'";
	$exe_loca = mysql_query($query_loca);
	$location = (object)mysql_fetch_array($exe_loca);

	$exe_all=mysql_query($query_all);
		$content="<table class='content' style='border-collapse:collapse;margin-top:22px;margin-bottom:30px;width:1150px'><tr><td colspan='11' style='font-size:13px;padding-bottom:10px;'>Location:  <b>{$location->location_name}</b></td></tr>
		<tr style='font-size:13px;font-weight:bold;text-align:left;'><td width='35px'>S/No.</td><td width='125px'>Asset No.</td><td width='180px'>Serial No.</td><td width='100px'>Category</td><td width='80px'>Brand</td><td width='120px'>Model No.</td><td>Status</td><td width='100px'> Issued To</td><td width='90px'>At Location? </td><td width='120px'>Remarks</td></tr>";
		$no=1;
		while($r=mysql_fetch_array($exe_all)){
			$content.="<tr style='font-size:13px;'><td>$no</td><td>$r[asset_no]</td><td>$r[serial_no]</td><td>$r[category_name]</td><td>$r[brand_name]</td><td>$r[model_no]</td><td>$r[status_name]</td><td>$r[issued_to]</td><td style='border:1px solid #666;'>&nbsp;</td><td style='border:1px solid #666;'>&nbsp;</td></tr>";
			$no++;
		}
		$content.="</table>";
		$content.="<div class='pagebreak'></div>";

		$header = "<header>$mainTitle</header>";
		if(mysql_num_rows($exe_all)>0) //if data not empty
			return $logo."\n".$header."\n".$content;
}
$style_path = defined('STYLE_PATH') ? STYLE_PATH : '';
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>FiGi Productivity Tools</title>
<link rel="shortcut icon" type="image/x-icon" href="../images/figiicon.ico" />
<link rel="stylesheet" href="../<?php echo $style_path;?>/style_print.css" type="text/css"  />
<script>
function print_it(){
    var btn = document.getElementById("printbutton");
    if (btn){
        btn.style.display = "none";
        print();
    }
}
</script>
<?php echo $style; ?>
<style>
@media screen {
    .pagebreak	{ height:10px; background:url(img/page-break.gif) 0 center repeat-x; border-top:1px dotted #999; margin-bottom:13px; }
}
@media print {
    .pagebreak { height:0; page-break-before:always; margin:0; border-top:none; }
}
</style>
</head>
<body>
<div class='counter'>
	<content>
	<?php
		foreach($_id_location as $id){
		
			if(!empty($id)){
				echo print_content($id);
			}
			
		}
	?>
	</content>
	<button id="printbutton" class="print" onclick="print_it()" >Click to Print (button disappear)</button>
</div>
</body>
</html>