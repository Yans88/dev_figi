<?php
if (!defined('FIGIPASS')) exit;

$id_location = !empty($_POST['id_location']) ? $_POST['id_location'] : 0;
$id_location = !empty($_GET['loc']) ? $_GET['loc'] : 0;
//$location_list = array('0' => '* select a location') + get_location_list();
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'l.location_name';
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_changeorder = isset($_GET['chgord']) ? $_GET['chgord'] : 1;
$_searchtext = !empty($_POST['searchtext']) ? $_POST['searchtext'] : null;
$_exports = !empty($_POST['export_id']) ? $_POST['export_id'] : null;
$to_exports = !empty($_POST['to_exports']) ? $_POST['to_exports'] : null;
//echo $_searchtext;
$_limit = 50;
$_start = 0;
$search = $_searchtext;
$total_item = cnt_location_checking($_searchtext);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;

if ($_changeorder > 0){
    $sort_order = 'ASC';
	$chgord = 0;
}else{	
	$sort_order = 'DESC';
	$chgord = 1;
}
//$order_link = './?mod=maintananec&sub=checking&chgord=1&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page='.$_page.'&ordby=';
$order_link = './?mod=maintenance&chgord='.$chgord.'&searchtext='.$search.'&page='.$_page.'&ordby=';
$checklists = array();	
$rs = get_location_checking($_searchtext, $_orderby, $sort_order, $_start, $_limit);
	while ($rec = mysql_fetch_assoc($rs)){
		$checklists[] = $rec;
	}

if (!empty($checklists)){
    $userlist = get_user_list();
}

if ($to_exports == 'exports'){
    $exp = export_data_maintanance($_exports);
}

?>

<script>
var dept = '<?php echo $dept?>';
function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
    var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("maintenance/facility_suggest.php", {queryString: ""+inputString+""}, function(data){
			if(data.length >0) {
				
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
				var pos =  $('#searchtext').offset();                       
				$('#suggestions').css('position', 'absolute');
				$('#suggestions').offset({left:pos.left});
			} else
                        $('#suggestions').fadeOut();
		});
	}
}
</script>
<div class="submod_wrap">
	<div class="submod_title"><h4>Maintenance Checking</h4></div>
	<div class="submod_links">	
	</div>
</div>
<br/>
<form method="post" id="frm_check">
<div class="searchbox" >
<br/>
    Search
    <input type="text" id="searchtext" name="searchtext" class="searchinput" size=20 value="<?php echo $_searchtext?>" 
    onKeyUp="suggest(this, this.value);" onBlur="fill('searchtext', this.value);" autocomplete=off style="width: 140px">
    <input type="image" src="images/loupe.png" class="searchsubmit" width=12 height=12>
    <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
</div>

<div class="clear"> </div>
<div class="checking_list">


<?php

if (!empty($checklists)){
    $latest = $checklists[0];
	$chk_max = get_max_checking();
    $t = strtotime($latest['modified_on']);
    $modified_on = date('d-M-Y H:i', $t);
    echo '<div class="space5-top center middle" style="width: 800px">';  
    echo '<div class="space5-top center">';    
    echo '<table class="itemlist " style="width: 100%">';
    echo '<tr><th width=40>No</th><th><a href="'.$order_link.'l.location_name">Location</a></th>
	<th><a href="'.$order_link.'modified_on_format">Last Checked</a></th><th>Checked by</th><th>Action</th><th>Select</th></tr>';    
    $counter = $_start+1;		
    foreach ($checklists as $rec){
        $row_class = ($counter % 2 == 0) ? 'alt' : '';
		$id_location = $rec['id_location'];
		$id_check = $chk_max[$id_location]['id_check'];
		$chk_by = $chk_max[$id_location]['fullname'];
		if(empty($chk_by)) $chk_by = 'NA';
        $modified_by_name = $userlist[$rec['modified_by']];
        $link = '<a href="./?mod=maintenance&sub=checking&act=view&id='.$id_check.'&loc='.$id_location.'">view</a>';
		$date_modified = "NA";	
		$for_exports = $id_check.'_'.$id_location;
		if(!empty($rec['modified_on_format'])){
			$last_check = strtotime($rec[modified_on_format]);
			$modified_on_format = date('d-M-Y H:i', $last_check);
			$date_modified = '<a href="./?mod=maintenance&sub=checking&act=view&id='.$id_check.'&loc='.$id_location.'">'.$modified_on_format.'</a>';
		}
        echo <<<REC
    <tr class="$row_class">
        <td class="right">$counter. &nbsp;</td>
        <td>$rec[location_name]</td>          
        <td>$date_modified</td>
		<td>$chk_by</td> 
	   <td class="center"><a href="./?mod=maintenance&sub=checking&act=start&loc=$id_location">Check Now</a></td>
	   <td class="center"><input type="checkbox" name="export_id[]" id="$id_location" class="export_id" value="$for_exports"></td>
    </tr>
REC;
        $counter++;
    }
	echo '<tr ><td colspan=11>';
	echo '<div class="pagination">';
	
	echo make_paging($_page, $total_page, './?mod=maintenance&page=');
	echo '</div>';
	echo  '</td></tr></table>';	
	echo '</div>';
	echo '</div>';

} else if ($id_location = 0)
	echo '<p class="error center ">The location never been checked for maintenance!. Click <a href="#add">here</a> to create a checklist.</p>';
else 
	echo '<p class="error center">Please select location for maintenance check!.</p>';
?>
</div>

</form>

<script>
$('#id_location').change(function(){
	this.form.submit();
});

$('a[href=#export]').click(function(){
	var selected = new Array();
	
    $('.export_id').each(function (index) {
       if ($(this).attr('checked')) selected.push($(this).get(0).id)
    });
	if(selected.length < 1){
		alert('Please select a location to export');
	}else{
		$('#frm_check').append('<input type="hidden" value="exports" name="to_exports">');
		$('#frm_check').submit();
	}
});

$('a[href=#add]').click(function(){
	var id_location = $(this).get(0).id;
	location.href = './?mod=maintenance&sub=checking&act=start&loc='+id_location;
});

$('.btn_category_toggle').click(function (e){
    toggle_fold(this);
});


</script>
