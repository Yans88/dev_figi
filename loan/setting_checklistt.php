<?php
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$_msg = null;
$_dept = USERDEPT;
$_id = (!empty($_POST['id_check'])) ?  $_POST['id_check'] : 0;
$_cat = (!empty($_POST['id_category'])) ?  $_POST['id_category'] : null;
$_title = (!empty($_POST['title'])) ?  $_POST['title'] : null;
$_mandatory= (!empty($_POST['is_mandatory'])) ?  $_POST['is_mandatory'] : 0;
$category_list = get_category_list('EQUIPMENT', $_dept);
error_log(serialize($_POST));
if  (isset($_POST['save'])){
	if ($_id>0){
		$query = "UPDATE loan_out_checklist SET title='$_title',id_category=$_cat, is_mandatory=$_mandatory WHERE id_check=$_id";
		if (mysql_query($query) && mysql_affected_rows()>0)
			$_msg = 'Title has been updated!';
		else
			$_msg = 'Title failed to update!';
	} else {
		$query = "INSERT loan_out_checklist(title, is_mandatory, id_category, id_department) VALUE('$_title', $_mandatory, $_cat, $_dept)";
		if (mysql_query($query) && mysql_affected_rows()>0)
			$_msg = 'New title has been added into checklist!';
		else
			$_msg = 'Failed to add new title into checklist!';
	}
} else 
if  (isset($_POST['remove'])){
	if ($_id>0){
		$query = "DELETE FROM loan_out_checklist  WHERE id_check=$_id";
		if (mysql_query($query) && mysql_affected_rows()>0)
			$_msg = 'Selected title has been deleted!';	
		else
			$_msg = 'Failed to delete the title!';	
		error_log(mysql_error().$query);
	}
}

$the_list = '';
$query = "SELECT * FROM loan_out_checklist WHERE id_department = $_dept and id_category = ".$_POST['id_category'];
$query_category = "SELECT category_name FROM category WHERE id_category = ".$_POST['id_category'];
$res_category = mysql_query($query_category);
$rec_category = mysql_fetch_assoc($res_category);
if (($rs=mysql_query($query))&&mysql_num_rows($rs)>0){
	$the_list = "<table width='100%' cellpadding=3 cellspacing=1 class='itemlist'>";
	$the_list .= "<tr><th width=40>No</th><th width=200>Title</th><th width=140>Category</th><th width=80>Status</th></tr>";
	$no = 1;
	while ($rec = mysql_fetch_assoc($rs)){
		$is_mandatory = ($rec['is_mandatory']) ? 'Mandatory' : 'Optional';
		$cn = ($no % 2 == 0) ? 'alt' : '';
		$the_list .= "<tr class='itemrow $cn' id='row-$rec[id_check]'><td>$no</td><td class='$rec[title] ct'>$rec[title]<input type='text' class='ttl' name='ttl[]' style='display:none;' value='$rec[title]'></td><td>$rec_category[category_name]</td><td class='cm'>$is_mandatory</td></tr>";
		$no++;
	}
	$the_list .= '</table>';
} else
	$the_list = '<p >Data is not available!</p>';
?>
<form id="settingform" method="post" class="loan_setting">
<fieldset>
<legend class="legend">Loan-Out Checklist</legend>
Category: <?php echo build_combo('id_category', $category_list, $_cat); ?><!--- <button>Change</button>--><br><br>
<?php echo $the_list; ?>
</fieldset>
<fieldset>
<legend class="legend">Add Title Checklist</legend>
<input type="hidden" id="id_check" name="id_check" value="">
<!-- Category: <?php echo build_combo('id_category', $category_list, $_cat); ?><button>Change</button><br><br> -->
<textarea id="title" name="title" rows=2 cols=45 value=""><?php echo $_title?></textarea><br>
<input type="checkbox" name="is_mandatory" id="is_mandatory" value=1 <?php echo ($_mandatory>0) ? 'checked' : null?> >Mandatory
</fieldset>
<fieldset class="footer" id="btnset">
<button type="reset" id="reset"> Reset </button>
<button type="button" id="save_button"> Save Title </button>
</fieldset>
</form>
<br/>
<br/>

<script>
$('#save_button').click(function(){
	var title = $("#title").val();
	var data_title = $('input:text[name=ttl[]]');	
	var rs = '';
	if (title.length==0){
		alert("Title is mandatory you can not leave it empty!");
		return;
	}
	$.each(data_title, function(key, object) {
		if(($(this).val()) != title){
			rs = '';
		}else{
			rs = 'failed';
			alert('Please input the different title');	
		}
	});
	if(rs != 'failed' || rs == ''){	
		$('#settingform').append('<input type="hidden" name="save" value=1>');
		$('#settingform').submit();	
	}
});

$('#reset').click(function(){
	$("#title").val('');
});

$('#id_category').change(function(){
	$("#title").val('');
	$('#settingform').submit();	
})


$('.itemrow').click(function(){
	var id = $(this).attr('id');
	var id_check = id.substr(4);
	var ct = $(this).find('.ct').text();
	var cm = $(this).find('.cm').text();
	$('#title').val(ct);	
	
	if (cm.toLowerCase()=='mandatory')
		$('#is_mandatory').attr('checked', true);	
	else
		$('#is_mandatory').removeAttr('checked');	
	//$('#settingform').append('<input type="text" id="id_check" "name="id_check" value='+id_check+'>');
	$('#id_check').val(id_check);
	//$('#cancel').show();
    //$('#remove').show();
	var del = document.getElementsByClassName("remove");
	var cancel = document.getElementsByClassName("cancel");
	//alert(cancel.length);
    if(cancel.length == 0 || del.length == 0){
		$('#btnset').prepend('<button type="button" id="remove" class="remove">Delete</button>');
	    $('#btnset').prepend('<button type="button" id="cancel" class="cancel">Cancel</button> ');
		$('#cancel').click(function(){
		$('#reset').trigger('click');
		//$('#id_check').remove();
		$('#cancel').remove();
		$('#remove').remove();
	});
	$('#remove').click(function(){
	     var idCheck = $('#id_check').val();
		if(idCheck.length == 0){
			alert("Please select one for delete");
			return;
		}
	     if (confirm('Do you sure delete the title?')){
		$('#settingform').append('<input type="hidden" name="remove" value=1>');
		$('#settingform').submit();
	}
   });
	}
  });

<?php
if (!empty($_msg)){
	echo "alert(\"$_msg\");\r\n";
	//echo "location.href = \"?mod=loan&sub=setting&act=checklist\";";
	?>
	$("#title").val('');
	<?php
}
?>
</script>