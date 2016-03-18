<?php
if (!defined('FIGIPASS')) exit;

$dept = defined('USERDEPT') ? USERDEPT : 0;


//$locations[0] = '-- all locations --';
$locations[0] = '-- Choose Location --';
$locations += get_location_list();

$ipAddress = gethostbyname($_SERVER['SERVER_NAME']);
//echo $ipAddress;


?>
<br/>
<div id="submodhead" >
<link rel="stylesheet" type="text/css" href="<?php echo STYLE_PATH ?>jquery.multicombo.css" media="screen" />
<script type="text/javascript" src="./js/jquery.multicombo.js"></script>

<script>
$(function() {

	$("#printview").click(function(){
		var post_data = $("#stocktake").serialize();
		var url=$(this).attr('link')+'?'+post_data;
		window.open(url, '_blank');

		$("#stocktake").submit(function() {
			return false;
		});

	/*
		function submitForm(url){
			$.post(url, $("#stocktake").serialize(), function(result) {
				window.open(url, '_blank');
			});
		}

		submitForm(url);		
		*/
	});

	$("#id_location").comboMulti();

});
</script>
<form id="stocktake" method="POST">
<div style="text-align: left; float: left; width: 80%;  font-weight:bold" >
    Take Report by Location <?php echo build_combo('id_location', $locations, $_id, 'reload_location(this)');?> <button link="<?php echo FIGI_URL; ?>/preview/item_stock-take_preview.php" id="printview">View</button>
</div>
</form>
</div>
