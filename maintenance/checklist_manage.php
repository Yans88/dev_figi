<?php
if (!defined('FIGIPASS')) exit;
$id_category = !empty($_POST['id_category']) ? $_POST['id_category'] : 0;
if (empty($id_category))
	$id_category = !empty($_GET['cat']) ? $_GET['cat'] : 0;

require 'maintenance_util.php';

$categories = category_list();
$no_category = empty($categories);
$categories = array('0' => '* select a category')+$categories;
$category = array();
if ($id_category>0)
	$category = get_category($id_category);
?>

<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />
<div class="submod_wrap">
	<div class="submod_title"><h4>Manage Checklist</h4></div>
	<div class="submod_links">
	<!--
		<a class="button" href="./?submod=maintenance&sub=checklist">Checking</a> 
		<a class="button" href="./?submod=maintenance&sub=manage">Manage Checklist</a>
	-->
	</div>
</div>

<div class="clear"> </div>
<div class="checking_list">
<form method="post">

<p class="center">
Checklist Category <?php echo build_combo('id_category', $categories, $id_category); ?> 
<?php 
//if (!$no_category) echo '<button>Change</button>'; 
echo '<br><br><a class="normal" href="#" id="add_category_btn" >Add Category</a>';
if ($id_category>0) {
	echo ' | <a class="normal" id="edit_category_btn" href="#'. $id_category.'">Edit Category</a>';
	echo ' | <a class="normal" id="clone_category_btn" href="#'. $id_category.'">Clone Category</a>';
	echo ' | <a class="normal" id="add_item_btn" href="#'. $id_category.'">Add Checklist Item</a>';
	//echo ' | <a class="normal" id="assign_btn" href="#'. $id_category.'">Assign Equipment</a>';
}
?>

</p>
</form>
<br>
<script type="text/javascript" src="js/jquery.json.js"></script>

<div id="add_category" style="display: none; width: 380px; height: 220px;font-weight: bold;  margin-top:20px;">
<form id="frm_do" method="post">
<h4 style="color: #000" class="center">Create New Category</h4> 
<table width="100%">
<tr><td>Category Name</td><td><input type="text" name="category_name" id="new_name" style="width: 200px"></td></tr>
<tr><td>Category Status</td><td><input type="checkbox" name="enabled" id="enabled" checked > <label for="enabled">Enabled</label> </td></tr>
<tr><td>Linkable to equipment</td><td><input type="checkbox" name="linkable_item" id="linkable_item" ><label for="linkable_item">Yes</label> </td></tr>
</table>
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
		$.post('./?mod=maintenance&sub=category', $('#frm_do').serialize(), function(data){
			if (data.length>0){
				var msg = 'Class data failed to be created!';
				if ('CREATE:OK'==data) { msg = 'Class data has been created!'; after_update = true; }	
				$('#create_progress').html(msg);
				$('#yes_do').hide();
				$('#not_do').text('Close');
			} else
			$('#create_progress').hide();
		});
	}
});

$('#change_btn').click(function(){
	var y = $('#year').val();
	if (parseInt(y)>2000){
		$('#frm_filter').submit();
	} else 
		alert('Please select a valid year.');
});

</script>
<br>
</div>

<div id="update_category" style="display: none; width: 380px; height: 220px;font-weight: bold;  margin-top:20px;">
<form id="frm_update" method="post">
<input type="hidden" name="id_category" value='<?php echo $id_category?>'>
<input type="hidden" name="update" value=1>
<h4 style="color: #000" class="center">Update Category</h4> 
<table width="100%">
<tr><td>Category Name</td><td><input type="text" name="category_name" style="width: 200px" value="<?php echo $category['category_name']?>"></td></tr>
<tr><td>Category Status</td><td><input type="checkbox" name="enabled" id="enabled" <?php echo ($category['enabled']>0) ? 'checked':null?> > <label for="enabled">Enabled</label> </td></tr>
<tr><td>Linkable with  equipment</td><td><input type="checkbox" name="linkable_item" id="linkable_item" <?php echo ($category['linkable_item']>0) ? 'checked':null?> ><label for="linkable_item">Yes</label> </td></tr>
</table>
<p class="center">
<button type="button" id="yes_update" name="yes" value="yes"> Save Changes </button>
<button type="button" id="not_update" > Cancel  </button>
</p>
<p class="center" id="update_progress" style="margin-top: 10px;display: none">processing....</p>
</form>
<script>
var after_update = false;
$('#not_update').click(function(){
	$('#new_name').val('');
	$('#update_progress').hide();
	$('#yes_update').show();
	$('#not_update').text('Cancel');
	parent.jQuery.fancybox.close();
	if (after_update) location.reload();
});
$('#yes_update').click(function(){
	$('#update_progress').show();
	if (!$('#frm_update').hasClass('submitted')){
		$('#frm_update').addClass(' submitted');
		$.post('./?mod=maintenance&sub=category', $('#frm_update').serialize(), function(data){
			if (data.length>0){
				var msg = 'Class data failed to be updated!';
				if ('UPDATE:OK'==data) { msg = 'Class data has been updated!'; after_update = true; }	
				$('#update_progress').html(msg);
				$('#yes_update').hide();
				$('#not_update').text('Close');
			} else
			$('#update_progress').hide();
		});
	}
});

</script>
<br>
</div>



<?php
if ($no_category){
	echo '<p class="center error space5-top">Category is not available, <span class="info"><a href="#add_category" class="fancybox">click</a> to create new category.</span></p>';

} else if ($id_category>0) { // selected category
	
	$query = 'SELECT * FROM checklist_item  ci LEFT JOIN checklist_type ct ON ct.id_type = ci.item_type  
				WHERE id_category = '.$id_category;
	$rs = mysql_query($query);
	$item_count = mysql_num_rows($rs);
	echo '<h4 class="center">Checlist Item List for Category "'.$category['category_name'].'"</h4>';
	if ($item_count>0){
		$no = 0;
		echo '<table class="itemlist middle grid" style="width: 500px">';
		echo '<tr><th>Checklist Item</th><th>Checklist Option</th><th width=60>Action</th></tr>';
		while ($rec = mysql_fetch_assoc($rs)){
			$option = str_replace(':', ', ',$rec['type_option']);
			$option = ucwords($option) . ' &nbsp; ('.ucfirst($rec['type_format']).' format)';
			$row_class = ($no++ % 2 == 0) ? 'alt' : 'normal';
			$action = '<a href="#" id="item-'.$rec['id_item'].'" class="edit_item_btn"> edit </a> ';
			$action .= '| <a href="#" id="delitem-'.$rec['id_item'].'" class="dele_item_btn"> del </a>';
			echo '<tr class="'.$row_class.'"><td>'.$rec['item_name'].'</td><td>'.$option.'</td><td class="center">'.$action.'</td></tr>';
		}
		echo '</table>';
?>

<?php
	} else if ($id_category>0) {
		echo '<p class="center error space5-top">Data is not available for category "'.$categories[$id_category].'".<br> <span class="info"><a href="#add_item" id="add_item_a">Click</a> to add item to the category.</span></p>';

	}
}
?>
</div>
<script>
	var id_category = '<?php echo $id_category?>';
	$(document).ready(function() {
		$('.fancybox').fancybox({padding: 5 });
	});

	$('#add_item_a').click(function(){
		$('#add_item_btn').trigger('click');
	});

	$('.edit_item_btn').click(function(){
		var id = this.id.substr(5);
		open_form('./?mod=maintenance&sub=checklist&act=edit_item&cat='+id_category+'&id='+id);
	});

	$('.dele_item_btn').click(function(){
		if (confirm('Do you sure delete the checklist item?')){
			var id_item = this.id.substr(8);
			$.post('./?mod=maintenance&sub=checklist&act=edit_item', {dele: 1,cat: id_category, id: id_item}, function(data){
				if (data.length>0){
					if (data=='|1|') alert('Selected checklist item has been deleted!');
					else alert('Selected checklist failed to be deleted!');
					location.reload();
				}	
			});
			}
	});

	$('#add_item_btn').click(function(){
		open_form('./?mod=maintenance&sub=checklist&act=edit_item&cat='+id_category);
	});

	function open_form(url){
		$.fancybox.open({
			href: url,
			type: 'iframe',
			width: 400,
			height: 320,
			padding: 1});
	}

	$('#add_category_btn').click(function(){
		$.fancybox.open({
			href: '#add_category',
			padding: 5});
            $('input[name=category_name]').focus();
	});
    
	$('#clone_category_btn').click(function(){
		if (confirm('Do you sure clone the selected category?')){
			var id_item = this.id.substr(8);
			$.post('./?mod=maintenance&sub=category', {clone: 1,cat: id_category}, function(data){
				if (data.length>0){
					if (data.substr(0, 8)=='CLONE:OK') {
                        var col = data.split(':');
                        alert('Checklist category cloning successfull!');
                        location.href = "./?mod=maintenance&sub=checklist&cat="+col[2];
                    } else alert('Error checklist category cloning!');
					//location.reload();
				}	
			});
        }
	});

	$('#edit_category_btn').click(function(){
		$.fancybox.open({
			href: '#update_category',
			padding: 5});
        $('input[name=category_name]').focus();
	});

	$('#id_category').change(function(){
		this.form.submit();
	});

</script>
