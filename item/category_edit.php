<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_type= !empty($_GET['type']) ? $_GET['type'] : 'equipment';
$_msg = null;
$_cat = isset($_POST['id_category']) ? $_POST['id_category'] : 0;
$dept = USERDEPT;
$loanable = !empty($_POST['loanable']) ? $_POST['loanable'] : 0; /** + **/


if (isset($_POST['save'])) {
    $code = mysql_real_escape_string($_POST['category_code']);
    $name = mysql_real_escape_string($_POST['category_name']);
    $condemn_period = isset($_POST['condemn_period']) ? $_POST['condemn_period'] : 0;
    $loan_period = isset($_POST['loan_period']) ? $_POST['loan_period'] : 0;
	//$category_parent = $_POST['id_parent'];
	$category_parent = ($_POST['id_parent']==null || $_POST['id_parent']==$_id) ? 0 : $_POST['id_parent']; //if id same with parent, parent to be 0
    if (isset($_POST['edit']) && ($_POST['edit']=='yes')){
        $query = "REPLACE INTO  category (id_category, category_name, category_type, category_code, id_department, condemn_period, id_parent, loanable, loan_period) 
                  VALUES ($_id, '$name', 'EQUIPMENT', '$code', '$dept', '$condemn_period', $category_parent, '$loanable', '$loan_period')";
        mysql_query($query);
        //echo mysql_error().$query;
    } else {
        if (isset($_POST['createnew']) && $_POST['createnew'] == 'yes'){ // create new category 
            $query = "INSERT INTO category (category_name, category_type, category_code, id_department, condemn_period, id_parent, loanable, loan_period) 
                      VALUES ('$name', 'EQUIPMENT', '$code', '$dept', '$condemn_period', $category_parent, '$loanable', '$loan_period')";
            mysql_query($query);
            //echo mysql_error();
            if (mysql_affected_rows()>0){
                $_id = mysql_insert_id();
				
				//if($category_parent==0) /** +here */
				//	mysql_query("UPDATE category SET id_parent=$_id WHERE id_category=$_id");

            } else $_id = 0;
            
        } else {
            $_id = $_cat;
            /*
            $category = get_category($_id);
            $code = $category['category_code'];
            $condemn_period = $category['condemn_period'];
            */
        }
        
        $query = "REPLACE INTO department_category (id_category, id_department) 
                  VALUES ($_id, '$dept')";
        mysql_query($query);
        //echo $query.mysql_error();
    }
    if (mysql_affected_rows()>0){
        $_msg = "Category info has been updated!";
        if ($_id == 0){
            $_msg = "New Item Category has been added!";
            //$_id = mysql_insert_id();
            user_log(LOG_CREATE, 'Create category '. $_POST['category_name']. '(ID:'. $_id.')');
        } else
            user_log(LOG_UPDATE, 'Update category '. $_POST['category_name']. '(ID:'. $_id.')');
    }
} else if (isset($_POST['delete'])) {
	ob_clean();
	header('Location: ./?mod=item&sub=category&act=del&id=' . $_id);
	ob_flush();
	ob_end_flush();
	exit;
}

$data = get_available_category_parent('EQUIPMENT', $dept, $_id, true); 

if ($_id > 0) {
  $query  = "SELECT * FROM category WHERE id_category = $_id";
  $rs = mysql_query($query);
  $data_item = mysql_fetch_array($rs);
  /** +here */
  /*
  if($data_item['id_parent']==0){
	mysql_query("UPDATE category SET id_parent=$_id WHERE id_category=$_id");
	$parent=$_id;
  }
  else $parent=$data_item['id_parent'];
  */

  //if id parent is 0 then it parent is id category 
  $parent=($data_item['id_parent']==0) ? $data_item['id_category'] : $data_item['id_parent'];

} else {
    $data_item['category_name'] = 'new category name';
    $data_item['category_code']= 'code';
    $data_item['condemn_period']= 36;
	$data_item['loanable']= 0;
	$data_item['loan_period']= 0;
} 

$caption = ($_id > 0) ? 'Edit Item Category' : 'Add New Item Category';
echo "<h4>$caption</h4>";
?>

<style>
table.itemlist td {padding: 4px }
li { padding: 5px }
</style>
<div id="itemedit" style="width: 500px; padding: 5px">
<form method="POST">
<br/>
<table width="99%" class="itemlist " cellpadding=4 cellspacing=1>
<tr>
  <td>Category Name </td>
  <td>
  <?php if ($_id > 0) { ?>
  <input type="text" size=30 id="category_name" name="category_name" value="<?php echo $data_item['category_name']?>">
  <?php } else { ?>
  <ul style="list-style: none; margin: 2px; margin-left: 0; padding: 0">
    <li><input type="radio" name="createnew" value="no" id="createold">Pick from un-assigned categories<br/>
        &nbsp; &nbsp; &nbsp; <?php echo build_combo('id_category', get_available_category_list('EQUIPMENT', $dept))?>, or
    </li>
    <li><input type="radio" name="createnew" value="yes" id="createnew">Create new one: <br/>    
        &nbsp; &nbsp; &nbsp; <input type="text" size=30 id="category_name" name="category_name" value="<?php echo $data_item['category_name']?>">
    </li>
    <?php } ?>
  </td>
</tr>
<tr class="alt">
  <td>Category Code </td>
  <td><input type="text" size=30 name="category_code" id="category_code" value="<?php echo $data_item['category_code']?>"></td>
</tr>
<tr class="normal">
  <td>Condemn Period</td>
  <td><input type="text" size=6 name="condemn_period" value="<?php echo $data_item['condemn_period']?>"> months</td>
</tr>
<tr class="alt">
<td>Parent</td><td><?php echo build_combo_tree('id_parent', $data, $parent); //echo build_combo('id_parent', $data, $parent)  ?></td>
</tr>
<tr class="normal">
  <td>Loanable</td>
  <td><input type="checkbox" name="loanable" id="loanable" value="1" <?php if($data_item['loanable'] >0) echo 'checked'; ?> /><span id="loanable_label"> <?php if($data_item['loanable'] >0) echo 'Yes'; else echo 'No '; ?></span><span style="margin-left:15px;font-size:10px;color:#000033;">*check the checkbox if you want to activate</span></td>
</tr>
<tr class="alt">
  <td>Loan Period</td>
  <td><input type="text" size=6 name="loan_period" id="loan_period" value="<?php echo $data_item['loan_period']?>"> days</td>
</tr>
</table>
<br/>
<?php if ($_id>0) echo '<input type="hidden" name="edit" value="yes" >'; ?>
<input type="hidden" name="id" value="<?php echo $_id?>" > 
<button type="submit" name="save">Save</button> 
<button type="button" name="cancel" onclick="location.href='./?mod=item&sub=category&type=<?php echo $_type?>'">Cancel</button> 
<?php
if ($_id > 0) {
echo <<<TEXT
<button type="submit" name="delete" 
	onclick="return confirm('Are you sure you want to delete $data_item[category_name]?')">Delete</button> 
TEXT;
}
?>
</form>
<br/>
</div>

<script>
var dept = '<?php echo USERDEPT?>';
 function save_item(){
  var frm = document.forms[0]
  frm.save.value = 1;
  frm.submit();
 }

$('#id_category').change(function(e){
    var idx = $(this).val();
    idx = document.getElementById('id_category').selectedIndex;
    if (idx>-1)
        $('#category_name').val(document.getElementById('id_category').options[idx].text);
    $('#createold').attr('checked', true);
});

$('#category_name').change(function(e){
    $('#createnew').attr('checked', true);
});
$(':radio[name="createnew"]').change(function(e){
    if ($(this).val() == 'no'){
        $('#category_code').attr('disabled', true);
		$('#id_parent').attr('disabled', true);
	}
    else{
        $('#category_code').removeAttr('disabled');
		$('#id_parent').removeAttr('disabled');
	}
});

$(':checkbox[name="loanable"]').change(function(){
	if($(this).is(':checked'))
		$(this).next().text(' Yes');
	else
		$(this).next().text(' No ');
});

$('#loanable').change(function(){

	if ($(this).attr('checked')){
		$('#loan_period').removeAttr('disabled');
		$('#loanable_label').html(' Yes');
	} else {
		$('#loan_period').attr('disabled', 'disabled');
		$('#loanable_label').html(' No ');
	}
});

$('#loanable').trigger('change');

<?php
if ($_msg != null) 	echo 'alert("' . $_msg . '"); location.href = "./?mod=item&sub=category&act=list";';
?>
</script>
