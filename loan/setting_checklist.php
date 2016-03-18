<style>
form.loan_setting{
	width:800px;
}

.ct {
	color:#2A17E2;
}
#add_title:hover{
	color:#2A17E2;
	text-decoration:underline;
	cursor:pointer;
}
.add_chk{
	margin-left:10px;
}
#titlelist {}
#titlelist td{ border: 0px solid #667;}
.hide { display: none; }
.buttons { position: absolute; float: right}
.buttons a { font-size: smaller; padding: 1px 5px !important}
</style>

<?php
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$_msg = null;
$_dept = USERDEPT;
$_id = (!empty($_POST['id_check'])) ?  $_POST['id_check'] : 0;
$_cat = (!empty($_POST['id_category'])) ?  $_POST['id_category'] : 0;
$_title = (!empty($_POST['title'])) ?  $_POST['title'] : null;
$_mandatory= (!empty($_POST['is_mandatory'])) ?  $_POST['is_mandatory'] : 0;
$_enabled= (!empty($_POST['is_enabled'])) ? $_POST['is_enabled']:0;
$_parent= (!empty($_POST['id_parent'])) ? $_POST['id_parent']:0;
$category_list = get_category_list('EQUIPMENT', $_dept);
//error_log(serialize($_POST));
if  (isset($_POST['save'])){
	$_title = mysql_real_escape_string($_title);
	if ($_id>0){
		$query = "UPDATE loan_out_checklist SET title='$_title', is_mandatory=$_mandatory, is_enabled=$_enabled WHERE id_check=$_id";
		if (mysql_query($query) && mysql_affected_rows()>0)
			$_msg = 'Title has been updated!';
		else
			$_msg = 'Title failed to update!';
	} else {
		$query = "INSERT loan_out_checklist(title, id_category, is_mandatory, is_enabled, id_parent) 
					VALUE('$_title', '$_cat', '$_mandatory', '$_enabled', '$_parent')";
		if (mysql_query($query) && mysql_affected_rows()>0)
			$_msg = 'New title has been added into checklist!';
		else
			$_msg = 'Failed to add new title into checklist!';
	}
	error_log(mysql_error().$query);
	echo "
		<form method='post' id='reload'>
		<input type='hidden' name='id_category' value='$_cat'>
		</form>
		<script>
		alert(\"$_msg\");
		//location.href=\"./?mod=loan&sub=setting&act=checklist\";
		$('#reload').submit();
		</script>";
		exit;
} else 
if  (isset($_POST['remove'])){
	if ($_id>0){
		$rs = mysql_query("SELECT * FROM loan_out_checklist WHERE id_check=$_id");
		if ($rs && mysql_num_rows($rs)){
			$rec = mysql_fetch_assoc($rs);

			$query = "DELETE FROM loan_out_checklist WHERE id_check = '$_id' OR id_parent = '$_id'";
			if (mysql_query($query) && mysql_affected_rows()>0)
				$_msg = 'Selected title has been deleted!';	
			else
				$_msg = 'Failed to delete the title!';	
			error_log(mysql_error().$query);
		}
	}
}

$the_list = '';
$title_item = '';
$test = '';
if ($_cat>0){
	$items = array();
	$query = "SELECT * FROM loan_out_checklist WHERE id_category = '$_cat' ORDER BY id_parent, id_check ";
	$rs = mysql_query($query);
	//echo mysql_error().$query;
	if ($rs && mysql_num_rows($rs)>0){
		while ($rec = mysql_fetch_assoc($rs)){
			if ($rec['id_parent']==0){ // root/parent
				$rec['items'] = array();
				$items[$rec['id_check']] = $rec; 
			} else { // child
				$items[$rec['id_parent']]['items'][$rec['id_check']] = $rec; 
			}
		}

	$the_list = "<table width='100%' cellpadding=3 cellspacing=1 class='itemlist' id='titlelist'>";
	$the_list .= "<tr><th width=20>No</th><th colspan=2>Title</th><th width=100>Type of status</th>
	<th width=70>Status</th></tr>";
	$no = 1;
	$row = 1;
	$is_same_root = true;
	foreach ($items as $id_check => $rec){
		$_chk = $rec['id_check'];
		$is_mandatory = ($rec['is_mandatory']) ? 'Mandatory' : 'Optional';
		$is_enabled = ($rec['is_enabled']) ? 'Enabled' : 'Disabled';
		$cn = ($row % 2 == 0) ? 'alt' : '';
		if (empty($rec['title'])) $rec['title']='-';
		$the_list .= "<tr class='itemrow $cn' id='row-$id_check'><td class='center'>$no.</td>";
		$the_list .= "<td colspan=2 class='item'><span  class='ct'>$rec[title]</span> ";
		$the_list .= "<span class='hide buttons'><a href='#edit' id='ct_$id_check' class='button'>edit</a> <a class='button' href='#add' id='add_$id_check' title='add sub checklist item'>add item</a></span> </td>";
		$the_list .="<td class='cm center'>$is_mandatory</td><td class='ce center'>$is_enabled</td>
		</tr>";
		$row++;
		if (!empty($rec['items'])){
		//print_r($rec['items']);
			foreach ($rec['items'] as $id_child => $child){
				$is_mandatory = ($child['is_mandatory']) ? 'Mandatory' : 'Optional';
				$is_enabled = ($child['is_enabled']) ? 'Enabled' : 'Disabled';
				$cn = ($row % 2 == 0) ? 'alt' : '';
				$the_list .= "<tr class='itemrow $cn' id='row-$child[id_check]'><td></td><td width=30>&nbsp; </td>";
				//$the_list .= "<td class='ct' id='ct-$child[id_check]' >$child[title]</td>";
				$the_list .= "<td class='item'><span  class='ct'>$child[title]</span> ";
				$the_list .= "<span class='hide buttons'><a href='#edit' id='ct-$child[id_check]' class='button'>edit</a></span> </td>";
				$the_list .="<td class='cm center'>$is_mandatory</td><td class='ce center'>$is_enabled</td></tr>";
				//<td><a href='#' class='add_chk' id='add_$child[id_check]'><img class='icon' src='images/add.png'/></a></td>
				$row++;
			}
		}
		$no++;
	}
	$the_list .= '</table>';
} else
	$the_list = '<p class="msg center">Data is not available!</p>';
} // _cat > 0

$category_list = array('0' => '* select a category')+$category_list;
?>

<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />

<div class="middle" style="width: 100%">
<form id="settingform" method="post" >
<h4 class="center">Loan-Out Checklist Management</h4> 
<p class="center">
	Category &nbsp; <?php echo build_combo('id_category', $category_list, $_cat); ?> 
	&nbsp; &nbsp;<a href='#add' id='add_0' title='add (main/root) checklist item'>add checklist</a>
</p>
</form>
<div id="listspace">
<?php echo $the_list; ?>
</div>

<div id="editspace" style="display:none; width: 560px; " class="middle">
<form id="editform" method="post">
<input type="hidden" name="id_check" value=0>
<input type="hidden" name="id_parent" value=0>
<input type="hidden" name="id_category" value="<?php echo $_cat?>">
<div style="padding: 5px 0" class="center">
<strong>Add / Edit Checklist</strong>
</div>
<table width="100%" cellpadding=4>
<tr>
	<td >Title</td>
	<td><textarea id="title" name="title" rows=4 cols=65 ></textarea></td>
<tr>
<tr>
	<td >Mandatory</td>
	<td><input type="checkbox" name="is_mandatory" id="is_mandatory" value=1 checked >Yes</td>
<tr>
<tr>
	<td >Status</td>
	<td><input type="checkbox" name="is_enabled" id="is_enabled" value=1 checked>Enabled</td>
<tr>

<tr>
	<td colspan=2 class="center">
	<button type="button" id="cancel"> Cancel </button>
	<button type="button" id="remove_button"> Delete </button>
	<button type="reset" > Reset </button>
	<button type="button" id="save_button"> Save </button>
	</td>
</tr>
</table>
</form>
</div>
<br/>
<br/>

<script>

$('a[href=#add]').click(function(){
	$.fancybox.open({href: '#editspace', padding: 5});
	var id = this.id.substr(4);
	$('input[name=id_check]').val(0); 
	$('input[name=id_parent]').val(0); 
	//alert(id)
	if (id > 0)
		$('input[name=id_parent]').val(id); 
	$('#title').val(''); 
	$('#is_enabled').attr('checked', true);
	$('#is_mandatory').attr('checked', true);
});

$('.item').hover(function(){ 
	$(this).find('.buttons').show();
}, function(){ 
	$(this).find('.buttons').hide();
});

$('a[href=#edit]').click(function(){ // edit
	var id = this.id.substr(3);
	if (id > 0){
		$.fancybox.open({href: '#editspace', padding: 5});
		$('input[name=id_check]').val(id); 
		$('#title').val($(this).parent().siblings('.ct').text()); 
		var m = $(this).parent().parent().parent().find('.cm').text();
		var e = $(this).parent().parent().parent().find('.ce').text();
		$('#is_mandatory').removeAttr('checked');
		if (m=='Mandatory')
			$('#is_mandatory').attr('checked', true);
		$('#is_enabled').removeAttr('checked');
		if (e=='Enabled')
			$('#is_enabled').attr('checked', true);
	}
});

$('#cancel').click(function(){
	$.fancybox.close();
});

$('#save_button').click(function(){
	var title = $("#title").val();
	var rs = '';
	if (title.length==0){
		alert("Title is mandatory you can not leave it empty!");
	} else {	
		$('#editform').append('<input type="hidden" name="save" value=1>');
		$('#editform').submit();	
	}
});

$('#remove_button').click(function(){
	if (confirm("Do you sure delete this checklist item!")) {	
		$('#editform').append('<input type="hidden" name="remove" value=1>');
		$('#editform').submit();	
	}
});
/*
$('.add_chk').click(function(){
	var promp_chk =  prompt('Add item checklist: ');
	var id = $(this).attr('id');
	var id_chk = id.substr(4);  
	var key = 'save';
	var save ={item_ttl: ""+promp_chk+"", id_chk: ""+id_chk+"", key: ""+key+""};
	if (promp_chk) {
           console.log(save);		
			$.post('loan/setting_checklist_util.php',save, function (data) {
                if (data.length > 0) {
                    if (data == 'ok') {
                        alert('Item checklist has been save!');
                        location.reload();
                    };
                }
            });			
        }
});

$('.edit_ttl').click(function(){
	var data = $(this).attr('id');
	var data2 = data.split('_');
	var id_chk_item = data2[0];
	var title_item = data2[1];
	var key = 'edit';
	var promp_chk =  prompt('Edit item checklist: ' ,title_item);
	var save ={item_ttl: ""+promp_chk+"", id_chk_item: ""+id_chk_item+"", key: ""+key+""};
	if (promp_chk) {
           console.log(save);		
			$.post('loan/setting_checklist_util.php',save, function (data) {
                if (data.length > 0) {
                    if (data == 'ok') {
                        alert('Item checklist has been edit!');
                        location.reload();
                    };
                }
            });			
        }
});

$('.del_ttl').click(function(){
	var id_chk_item = $(this).attr('id');	
	var key = 'delete';
	var promp_chk =  confirm('Are you want to delete this checklist ?');
	var save ={id_chk_item: ""+id_chk_item+"", key: ""+key+""};
	if (promp_chk) {
           console.log(save);		
			$.post('loan/setting_checklist_util.php',save, function (data) {
                if (data.length > 0) {
                    if (data == 'ok') {
                        alert('Item checklist has been deleted!');
                        location.reload();
                    };
                }
            });			
        }
});

$('#add_title').click(function(){
	$('#title').removeAttr('disabled');
	$('#title').removeAttr('');
});
*/
$('#id_category').change(function(){
	$("#title").val('');
	$('#settingform').submit();	
})

$('#reset').click(function(){
	$("#title").val('');
});
/*
$('.itemrow').click(function(){
	var id = $(this).attr('id');
	var id_check = id.substr(4);
	var ct = $(this).find('.ct').text();
	var cm = $(this).find('.cm').text();
	var ce = $(this).find('.ce').text();
	$('#title').val(ct);	
	
	if (ce.toLowerCase()=='enabled')
		$('#is_enabled').attr('checked', true);	
	else
		$('#is_enabled').removeAttr('checked');	
	
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
    if(cancel.length == 0){
		//$('#btnset').prepend('<button type="button" id="remove" class="remove">Delete</button>');
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
*/

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
