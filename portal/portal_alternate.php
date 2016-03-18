<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>

<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />
<link rel="stylesheet" type="text/css" href="style/default/student_usage.css">

<style>
.container_expand{
	margin:0 auto;
}

.content_expand_left{
	border:1px solid #fff;
	width:auto;
	text-align:center;
	margin:0 auto;
	overflow:hidden;
	color:#FFF;
}
.content_expand1{
	clear:both;
	padding:10px;
	display:none;
}
.content_expand2{
	clear:both;
	padding:10px;
	display:none;
}
.content_expand3{
	clear:both;
	padding:10px;
	display:none;
}
.content_expand4{
	clear:both;
	padding:10px;
	display:none;
}
#expander1{
	width:auto;
	padding:5px;
	cursor:pointer;
}
#expander2{
	width:auto;
	padding:5px;
	cursor:pointer;
}
#expander3{
	width:auto;
	padding:5px;
	cursor:pointer;
}
#expander4{
	width:auto;
	padding:5px;
	cursor:pointer;
}

.title_expand{
	float:left;
	width:auto;
	padding:5px;
}
.make_center{
	margin:0 auto;
	padding:10px;
	overflow:hidden;
	width:100%;
	font-size:18px;
	font-wight:bold;
	background:#AAB9C6;
}
.ifreame_style{
	width:100%;
	height:500px;
	border:none;
	
	font-size:10px;
}
</style>
<div class="submod_title"><h4 >Alternate Request Portal</h4></div>
<div class="clear"> </div>
<div class="container_expand">
<div class='content_expand_left'>
	<div class="make_center"> <span id="expander1" >+</span> Facility </div>
	<div class="content_expand1" id="content_expand1">
	<!--MAKE BOOKING-->
	
	<iframe src="<?php echo $base_url;?>?mod=portal&portal=booking_alternate&act=make" class="ifreame_style"></iframe>
	
	</div>
	
</div>
<div class='content_expand_left'>
	<div class="make_center"> <span id="expander2" >+</span> Services </div>
	<div class="content_expand2" id="content_expand2">
		<?php include "portal_service.php";?>
	</div>
</div>
<div class='content_expand_left'>
	<div class="make_center"> <span id="expander3" >+</span> Loan </div>
	<div class="content_expand3" id="content_expand3">
		<?php include "portal_loan.php";?>
	</div>
</div>
<div class='content_expand_left'>
	<div class="make_center"> <span id="expander4" >+</span> Fault </div>
	<div class="content_expand4" id="content_expand4">
		
		<?php 
		
		//TEST1
		
		include "portal_fault.php";
		//END TEST1
		?>
	</div>
</div>

</div>


<script type="text/javascript">
	$("#expander1").click(function(){
		var a = this.id.substr(8);
		call_expander(a);
	});
	
	$("#expander2").click(function(){
		var a = this.id.substr(8);
		call_expander(a);
	});
	
	$("#expander3").click(function(){
		var a = this.id.substr(8);
		call_expander(a);
	});
	
	$("#expander4").click(function(){
		var a = this.id.substr(8);
		call_expander(a);
	});
	
	function call_expander(num){
		var x = $("#expander"+num).text();
		if(x=="+"){
			$("#content_expand"+num).show("slow");
			$("#expander"+num).text("-");
		} else {
			$("#content_expand"+num).hide("slow");
			$("#expander"+num).text("+");
		}
	}
	
</script>

<?php
if(ALTERNATE_PORTAL_STATUS != 'enable'){
	header("location: ./?mod=portal");
}
?>