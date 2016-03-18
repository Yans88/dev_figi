<?php
/*
$month = isset($_GET['m']) ? $_GET['m'] : date('n');
$year  = isset($_GET['y']) ? $_GET['y'] : date('Y');
$fid = isset($_POST['fid']) ? $_POST['fid'] : 0;
*/
$filter = array('id_facility' => $_facility);
$start_of_month = mktime(0, 0, 1, $month, 1, $year);
$last_dom = date('t', $start_of_month);
$end_of_month = mktime(23, 59, 59, $month, $last_dom, $year);

if (USERGROUP!=GRPADM) $filter['id_user'] = USERID;
else {
	if (isset($_POST)){
		$filter['start'] = $start_of_month;
		$filter['end'] = $end_of_month;
	}
}
$books = booking_rows($filter);
//print_r($books);
?>
<div class="widget list" style="">
	<h4 class="widget-title">Booking List</h4>
	<div class="widget-body">
	<ul>
	<?php
		if (count($books)>0){
			foreach($books as $book){
				$link = "?mod=portal&portal=facility&act=view&id=$book[id_book]";
				echo "<li><a href='$link' title=\"booked on $book[book_date_display], facility: $book[facility_name]\" target='_parent'>$book[purpose] </a></li>";
			}
		} else
			echo '<li>Data is not available!</li>';
	?>
	</ul>
	</div>
</div>

<script>

$('#fid').change(function(){
    $('#bookingform').submit();
});

$('.date').click(function(){
	var id = this.id
	var d = id.substr(5);
	if (d.length>0 && $(this).parent().hasClass('allow-to-book'))
		location.href = "<?php echo $mod_url?>&d="+d;
});

$('#today').click(function(){
	var d = new Date();
	location.href = "<?php echo $mod_url?>&act=list&y="+d.getFullYear()+"&m="+(d.getMonth()+1);
});
</script>
