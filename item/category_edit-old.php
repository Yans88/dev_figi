<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_type= !empty($_GET['type']) ? $_GET['type'] : 'equipment';
$_msg = null;
$_cat = isset($_POST['id_category']) ? $_POST['id_category'] : 0;
$dept = USERDEPT;

if (isset($_POST['save'])) {
    $code = mysql_real_escape_string($_POST['category_code']);
    $name = mysql_real_escape_string($_POST['category_name']);
    $condemn_period = isset($_POST['condemn_period']) ? $_POST['condemn_period'] : 0;
    if (isset($_POST['edit']) && ($_POST['edit']=='yes')){
        $query = "REPLACE INTO  category (id_category, category_name, category_type, category_code, id_department, condemn_period, id_parent) 
                  VALUES ($_id, '$name', 'EQUIPMENT', '$code', '$dept', '$condemn_period', 0)";
        mysql_query($query);
        //echo mysql_error();
    } else {
        if (isset($_POST['createnew']) && $_POST['createnew'] == 'yes'){ // create new category 
            $query = "INSERT INTO category (category_name, category_type, category_code, id_department, condemn_period, id_parent) 
                      VALUES ('$name', 'EQUIPMENT', '$code', '$dept', '$condemn_period', 0)";
            mysql_query($query);
            //echo mysql_error();
            if (mysql_affected_rows()>0){
                $_id = mysql_insert_id();
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
	
if ($_id > 0) {
  $query  = "SELECT * FROM category WHERE id_category = $_id";
  $rs = mysql_query($query);
  $data_item = mysql_fetch_array($rs);
  
} else {

    $data_item['category_name'] = 'new category name';
    $data_item['category_code']= 'code';
    $data_item['condemn_period']= '36';
}
   
$caption = ($_id > 0) ? 'Edit Item Category' : 'Add New Item Category';
echo "<h4>$caption</h4>";
?>

<div id="itemedit" style="width: 500px">
<form method="POST">
<br/>
<table width="99%" class="itemlist " cellpadding=4 cellspacing=0>
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
    if ($(this).val() == 'no')
        $('#category_code').attr('disabled', true);
    else
        $('#category_code').removeAttr('disabled');
});
<?php
if ($_msg != null) 	echo 'alert("' . $_msg . '"); location.href = "./?mod=item&sub=category&act=list";';
?>
</script>