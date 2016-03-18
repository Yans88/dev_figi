<?php
$id_book = isset($_GET['id']) ? $_GET['id'] : 0;
$book = book_info($id_book);
if (empty($book))
	redirect('./?mod=booking');
$id_facility = $book['id_facility'];
$attachments = book_attachment($id_book);
if (!empty($attachments)){
	$filelist = '<ul>';
	foreach($attachments as $rec){
		$filelist .= '<li><a class="fancybox" data-facncybox-group="gallery" href="'.$mod_url.'&act=attachment&name='.$rec['filename'].'">'.$rec['filename'].'</a></li>';
	}
	$filelist .= '</ul>';
} else $filelist = null;//'- NA -';
$_msg = null;
$msg = !empty($_SESSION['msg']) ? $_SESSION['msg'] : null;
if (!empty($msg)){
	$msg = unserialize($msg);
	$_msg = display_message($msg, true);
	if (isset($msg['new'])){
		if(ALTERNATE_PORTAL_STATUS == 'enable'){
		
			$_msg .= '
			<script>
			if (confirm("You just made a facility booking. Do you want to make loan some equipments?")){
				location.href = "./?mod=portal&portal=alternate";
			}
			</script>
			';
			
		} else {
		
			$_msg .= '
			<script>
			if (confirm("You just made a facility booking. Do you want to make loan some equipments?")){
				location.href = "./?mod=portal&portal=loan";
			}
			</script>
			';
			
		}
	}
	unset($_SESSION['msg']);
}
$equipment_list = get_equipments($book['id_facility']);
$periods = book_periods($id_book);
?>

<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />
<link rel="stylesheet" type="text/css" href="style/default/booking.css" media="screen" />
<div class="submod_wrap">
	<div class="submod_title"><h4 >Booking Detail</h4></div>
	<div class="clear"> </div>
</div>
<?php echo $_msg; ?>
<!--
<div id="wrap_header">
<div id="wrap_title">Book Detail</div>
<div class="clear"></div>
</div>
-->
<div id="resource_wrap">
<table id="resource">
<tr><td width=100>Booked by</td><td><?php echo $book['user_name']; ?></td></tr>
<tr><td>Booked on</td><td><?php echo $book['booking_date']; ?></td></tr>
<tr><td>Facility</td><td><?php echo $book['facility_name']; ?></td></tr>
<tr><td>Description</td><td><?php echo $book['description']; ?></td></tr>
<tr><td></td><td style="text-align: left"><button type="button" id="equipment"> Equipment </button></td><td>
<tr><td>Recurring</td><td>
<?php 
	echo ucwords(strtolower($book['recurring'])); 
 if ($book['recurring_times']>1) echo ' ( '.$book['recurring_times']. ' times )';
?> 
</td></tr>
<tr><td>Subject infused</td><td><?php echo !empty($book['subject_name']) ? $book['subject_name']:'-NA-'; ?> </td></tr>
<tr><td>Reason</td><td><?php echo $book['purpose']; ?></td></tr>
<tr><td>Instruction</td><td><?php echo $book['remark']; ?></td></tr>
<tr>
<td>Attachment</td>
<td>
<?php 
	if (!empty($filelist)) 
		echo '<div id="filelist">'.$filelist.'</div>'; 
	else echo '-NA-';
?>
</td>
</tr>
</table>

<table class="tbl_period_list" >
<tr class="head"><th width=80>Date</th><th width=90>Period</th><th>Subject</th><th>Reason</th><th>Instruction</th></tr>
<?php
	$sort_periods = array();
	//print_r($periods);
	$now = time();
	foreach($periods as $bd => $times){
		$rec = array_shift($times);
		array_push($sort_periods, $rec['booked_date']);
		$delink = ' ';
		if ($rec['booked_date']>$now) {
			if (!empty($times))
				$delink = '<a class="red" href="#'.$id_book.'-'.$rec['id_time'].'-'.$rec['booked_date'].'">x</a>';
		}
		echo "<tr><td>$rec[book_date]</td><td>$delink $rec[start_time] - $rec[end_time]</td><td>$rec[subject]</td><td>$rec[purpose]</td><td>$rec[remark]</td></tr>";
		foreach($times as $rec){
			if ($rec['booked_date']<=$now) $delink = ' ';
			else $delink = '<a class="red" href="#'.$id_book.'-'.$rec['id_time'].'-'.$rec['booked_date'].'">x</a>';
			echo "<tr><td></td><td>$delink $rec[start_time] - $rec[end_time]</td><td>$rec[subject]</td><td>$rec[purpose]</td><td>$rec[remark]</td></tr>";
			//array_push($sort_periods, $rec['booked_date']);
		}
	}
?>
</table>
<br>

<div style="float:left">
<button type="button" id="backbtn">Back</button>
</div>
<div style="float:right">
<?php
if($book['id_user']==USERID){
	rsort($sort_periods);
	$first_period = array_pop($sort_periods);
	$this_time = time();
	if (!empty($first_period)){
		if ($this_time < $first_period)
			echo '<button type="button" id="remove">Delete This Booking</button> ';
	}
}
?>
<button type="button" id="makebooking">Make New Booking</button>
<button type="button" id="booklist">Booking List</button>
</div>
<div class="clear"></div>
</div>
<div id="equipment_list" style="display: none">
<div class="center" style="padding: 3px 0"><strong>Equipment List Issued to "<?php echo $book['facility_name']?>"</strong></div>
<table width="100%">
<tr><th width=30>No</th><th>Category</th><th width=60>Quantity</th></tr> 
<?php
	if (!empty($equipment_list)){
		$no = 0;
		foreach ($equipment_list as $category_name => $quantity){
			$no++;
			echo '<tr><td>'.$no.'</td><td>'.$category_name.'</td><td class="center">'.$quantity.'</td></tr>';
		}
	} else echo '<tr><td colspan=3 class="center ">Data is not available!</td></tr>';
?>
</table>
</div>
<form id="frm_delete" method="post" action="">
<input type="hidden" name="id_book" value="<?php echo $id_book?>">
<input type="hidden" name="remove" value=1>
</form>
<form id="frm_delper" method="post" action="./?mod=booking&sub=period">
<input type="hidden" name="delper" value="">
</form>
<script>
$(function(){
	$('.fancybox').fancybox();

	$('#remove').click(function(){
		if (confirm('Do you sure delete this booking?')){
			$('#frm_delete').attr('action','<?php echo $mod_url?>&act=make');
			$('#frm_delete').submit();
		}
	});	
	$('#backbtn').click(function(){
	<?php if(ALTERNATE_PORTAL_STATUS == 'enable') { ?>
		top.location.href="<?php echo $mod_url?>&act=make";
	<?php } else { ?>
		location.href="<?php echo $mod_url?>&act=make";
	<?php } ?>
		//history.go(-1);
	});
	$('#makebooking').click(function(){
		location.href="<?php echo $mod_url?>&act=make";
	});
	$('#booklist').click(function(){
		location.href="<?php echo $mod_url?>&act=list";
	});
	$('#equipment').click(function(){
		$.fancybox.open({
			/*href: 'equipment_list.php?id='+id_book,*/
			href: '#equipment_list',
			/*type: 'iframe',*/
			padding: 5
			});
	});
	$('a.red').click(function(){
		if (confirm('Do you sure delete the period?')){
			var p = this.href.substring(this.href.lastIndexOf('#')+1);
			$('#frm_delper').find('input[name=delper]').val(p);
			$('#frm_delper').submit();
		}
	});
});
</script>
