<?php
if (!defined('FIGIPASS')) exit;
$_limit = RECORD_PER_PAGE;
$_start = 0;
$_type = 'equipment';
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_sort = isset($_GET['sort']) ? $_GET['sort'] : 0;

$subjects = booking_subject_rows();
$item_count = count($subjects);
$total_page = ceil($item_count/$_limit);

if ($_page > 0) $_start = ($_page-1) * $_limit;
if ($_page > $total_page) 	$_page = $total_page;
/*
if ($_sort > 0) {
	$sort_order = ($order_status[$_type] == 'asc') ? 'desc' : 'asc';
	$buffer = ob_get_contents();
	ob_clean();
	$order_status[$_type] = $sort_order;
	$_SESSION['CATEGORY_ORDER_STATUS'] = serialize($order_status);
	echo $buffer;
}

$row_class = ' class="sort_'.$sort_order.'"';
*/
?>

<div class="submod_wrap">
	<div class="submod_links">
	<?php
		if (defined('PORTAL')){
			echo '<a href="./?mod=portal&portal=facility" class="button" > Booking Calendar </a>';
		} else {
			echo '<a href="./?mod=booking" class="button" > Cancel </a> ';
			echo '<a href="./?mod=booking&act=import_template" class="button" > Generate Template </a> ';
			echo '<a href="./?mod=booking&act=import" class="button" > Import Booking </a> ';
		}
	?>
	</div>
	<div class="submod_title"><h4 >Import Booking</h4></div>
	<div class="clear"> </div>
</div>

<h4 class="center">Subject List</h4>
<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />
<table width=400 cellpadding=2 cellspacing=1 class="itemlist grid" >
<tr height=30>
  <th width=30>No</th>
  <th> Subject Name</th>
  <th> Subject ID</th>
</tr>

<?php
$counter = $_start;
if ($item_count>0){
	foreach ($subjects as $rec) {
	  $counter++;
	  $_class = ($counter % 2 == 0) ? 'class="alt"':null;
	  echo <<<DATA
	  <tr $_class>
		<td align="right">$counter.</td>
		<td>$rec[subject_name]</td>
		<td align="center">$rec[id_subject]</td>
	  </tr>
DATA;
	}
} else {
	echo '<tr><td colspan=3 class="center">Data is not available!</td></tr>';
} 
?>

</table><br/>

<div id="add_subject" style="display: none; width: 280px; height: 190px;font-weight: bold;  margin-top:20px;">
<form id="frm_do" method="post">
<h4 style="color: #000" class="center">Create New Subject</h4> 
<p class="left">Subject Name: </p>
<p class="center"><input type="text" name="subject_name" id="new_name" style="width: 200px"></p>
<p class="center">
<button type="button" id="yes_do" name="create_yes" value="yes"> Create </button>
<button type="button" id="not_do" name="create_not" value="not"> Cancel  </button>
</p>
<p class="center" id="create_progress" style="margin-top: 10px;display: none">processing....</p>
</form>

<script>
var after_update = false;
$('#not_do').click(function(){
	$('#new_name').val('');
	$('#create_progress').hide();
	$('#yes_do').show();
	$('#not_do').text('Cancel');
	parent.jQuery.fancybox.close();
	if (after_update) location.reload();
});
$('#yes_do').click(function(){
	$('#create_progress').show();
	if (!$('#frm_do').hasClass('submitted')){
		$('#frm_do').addClass(' submitted');
		$('#frm_do').append('<input type="hidden" name="create" value=1>');
		$.post('./?mod=booking&sub=subject', $('#frm_do').serialize(), function(data){
			if (data.length>0){
				var msg = 'Subject data failed to be created!';
				if ('CREATE:OK'==data) { msg = 'Subject data has been created!'; after_update = true; }	
				$('#create_progress').html(msg);
				$('#yes_do').hide();
				$('#not_do').text('Close');
			} else
			$('#create_progress').hide();
		});
	}
});

$('#add_subject_btn').click(function(){
	$.fancybox.open({
		href: '#add_subject',
		padding: 5});
	$('#add_subject').find('input[name=subject_name]').focus();
});


var orgval = '';

$('a.edit').click(function(){
	var col = this.href.split('#');
	inlineedit(col[1]);
});

$('a.dele').click(function(){
	var col = this.href.split('#');
	var id = col[1];
    var name = $('#td'+id).text();
	var after_update = false;
	if (confirm('Do you sure delete subject "'+name+'"?')){
		$.post('./?mod=booking&sub=subject', {dele: 1,id_subject: id} , function(data){
			if (data.length>0){
				var msg = 'Subject data failed to be deleted!';
				if ('DELETE:OK'==data) { msg = 'Subject data has been deleted!'; after_update = true; }	
			} 
			alert(msg);
			if (after_update)
				parent.location.reload();
		});
		
	}

});

function inlineedit(id)
{
    if (orgval != '') return;
    orgval = $('#td'+id).text();
    $('#td'+id).html('<input type="text" name="name" value="'+orgval+'" style="width: 240px"> '+
            '<a href="#" onclick="process_it('+id+', true)" ><img src="images/ok.png" class="icon"></a> '+
            '<a href="#" onclick="process_it('+id+', false)"><img src="images/no.png" class="icon"></a>');
}

function process_it(id, ok)
{
    var dept = $(":input[name^='name']");
    var newval = orgval;
    if (ok){
        newval = dept.val();
        $.post("?mod=booking&sub=subject", {id_subject: id, subject_name: ""+newval+"", update: 1}, function(data){
            if (data.length>0 && data=='UPDATE:OK'){
                alert('Subject name updated!');
            } else {
                alert('Update Subject name fail!');
                newval = orgval;
            }            
        });
    }
    $('#td'+id).text(newval);
    orgval = '';
}

</script>
