<?php
if (!defined('FIGIPASS')) exit;
if (!empty($_SESSION['ACCESSORIES_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['ACCESSORIES_ORDER_STATUS']);
else
    $order_status = array('accessory_name' => 'asc', 'order_no' => 'asc');

$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_sort = isset($_GET['sort']) ? $_GET['sort'] : 0;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'order_no';
$_dept = isset($_POST['id_department']) ? $_POST['id_department'] : 0;
if (empty($_dept))
	$_dept = isset($_GET['dept']) ? $_GET['dept'] : $_dept;
$_cat = isset($_POST['id_category']) ? $_POST['id_category'] : 0;
if (empty($_cat))
	$_cat = isset($_GET['cat']) ? $_GET['cat'] : $_cat;
$_move = isset($_GET['move']) ? $_GET['move'] : null;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($_move != null){
  accessory_order($_cat, $_id, $_move);
}


$dept = ($_dept > 0) ? $_dept : USERDEPT ;
$department_list = get_department_list();
if (($dept == 0) && (count($department_list)>0)){
  $dkeys = array_keys($department_list);
  $dept = $dkeys[0];
} else
	$department_list [0] = '--none--';

$category_list = get_category_list('equipment', $dept);
if (count($category_list) == 0)
  $category_list[0] = '--none--';
elseif ($_cat == 0) {
  $dkeys = array_keys($category_list);
  $_cat = $dkeys[0];
}
//$dept = defined('USERDEPT') ? USERDEPT : 0;
$total_item = count_accessories($_cat);
$total_page = ceil($total_item/$_limit);

if ($_page > 0) $_start = ($_page-1) * $_limit;
if ($_page > $total_page) $_page = $total_page;
$sort_order = $order_status[$_orderby];
if ($_sort > 0) {
	$sort_order = ($order_status[$_orderby] == 'asc') ? 'desc' : 'asc';
	$buffer = ob_get_contents();
	ob_clean();
	$order_status[$_orderby] = $sort_order;
	$_SESSION['ACCESSORIES_ORDER_STATUS'] = serialize($order_status);
	echo $buffer;
}
$row_class = ' class="sort_'.$sort_order.'"';

$nav_link = "./?mod=item&sub=accessories&act=list&dept=$dept&cat=$_cat";

echo '<br/><form method="post">';
/*
if (SUPERADMIN) {
?>
Department : <?php echo build_combo('id_department', $department_list, $dept, 'department_change()') ?>
&nbsp; 
<?php
} //admin
*/
echo 'Accessories List for Category : ' . build_combo('id_category', $category_list, $_cat, 'category_change()') . '<br/>&nbsp;';
if ($i_can_create && !SUPERADMIN) {
?>
<div style="text-align: left; width: 500px">
<a class="button" href="javascript:void(0)" onclick="add_new()"> Add Accessory</a>
</div>
<?php
} 

if ($total_item > 0){
?>
<table width=500 cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30>
  <th <?php echo ($_orderby=='order_no') ? $row_class : ''?> width=40>
    <a href="./?mod=item&sub=accessories&act=list&page=<?php echo $_page?>&dept=<?php echo $dept?>&cat=<?php echo $_cat?>&sort=1&ordby=order_no">No</a>
  </th>
  <th <?php echo ($_orderby=='accessory_name') ? $row_class : '' ?>>
    <a href="./?mod=item&sub=accessories&act=list&page=<?php echo $_page?>&dept=<?php echo $dept?>&cat=<?php echo $_cat?>&sort=1&ordby=accessory_name">Accessory Name</a>
  </th>
  <th width=80>Action</th>
</tr>

<?php

$rs = get_accessories($_orderby, $sort_order, $_start, $_limit, $_cat);
$counter = $_start;
if (is_resource($rs))
while ($rec = mysql_fetch_array($rs))
{
  	$edit_link = '<img class="icon" src="images/editx.png" alt="edit"> <img class="icon" src="images/deletex.png" alt="edit">';
	$edit_link = '';
	if (!SUPERADMIN && $i_can_update){
		if ($rec['order_no']>1)
    		$edit_link .= "<a href='$nav_link&move=up&id=$rec[id_accessory]' title='edit'><img class='icon' src='images/up.png' alt='shift up'></a>";
		else
    		$edit_link .= "<img class='icon' src='images/upx.png' alt='shift up'>";
		if ($rec['order_no']<$total_item)
    		$edit_link .= "<a href='$nav_link&move=down&id=$rec[id_accessory]' title='edit'><img class='icon' src='images/down.png' alt='shift down'></a>";
		else
    		$edit_link .= "<img class='icon' src='images/downx.png' alt='shift up'>";
    	$edit_link .=<<<EDIT
    <a href="javascript:void(0)" onclick="inlineedit($rec[id_accessory])" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
    <a href="javascript:void(0)" onclick="delete_it($rec[id_accessory], '$rec[accessory_name]')" 
       title="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
EDIT;
	}
  $counter++;
  $_class = ($counter % 2 == 0) ? 'class="alt"':null;
  echo <<<DATA
  <tr $_class>
  <td align="right">$counter.</td>
  <td id="td$rec[id_accessory]">$rec[accessory_name]</td>
  <td align="center">$edit_link</td>
  </tr>
DATA;
}

echo '<tr ><td colspan=7 class="pagination">';
echo make_paging($_page, $total_page, $nav_link . '&page=');
echo  '</td></tr></table>';

} else {
  echo '<div class="error" style="margin-top: 40px;">Data is not available!.';
  if ($i_can_create && !SUPERADMIN)
 	 echo 'Click <a class="button" href="javascript:void(0)" onclick="add_new()">Create Accessory</a> to add new accessory </div>';
}
?>
</form>

<br/>
<script type="text/javascript">
var orgval = '';

function add_new()
{
	var value = prompt('Enter new accessory name: ');
	var category = $('#id_category').val();
	if (value != null){
		$.post("item/accessories_update.php", {id: 0, name: ""+value+"", cat: ""+category+""}, function(data){
			if (data.length>0 && parseInt(data) > 0){
				alert('New accessory added!');
				refresh_me();
			} else {
				alert('Fail to add new accessory!');
			}            
		});
	}
}

function delete_it(id, name)
{
	
	if (confirm('Are you sure you want to delete "'+name+'"?')){
		$.post("item/accessories_update.php", {id: ""+id+"", del: 1}, function(data){
			if (data.length>0 && parseInt(data) > 0){
				alert('Selected accessory has been deleted!');
				refresh_me();
			} else {
				alert('Fail to delete selected accessory!');
			}            
		});
	}

}

function inlineedit(id)
{
    if (orgval != '') return;
    orgval = $('#td'+id).text();
    //alert(orgval);
    $('#td'+id).html('<input type="text" name="name" value="'+orgval+'" size=30> '+
            '<a href="#" onclick="process_it('+id+', true)" ><img src="images/ok.png" class="icon"></a> '+
            '<a href="#" onclick="process_it('+id+', false)"><img src="images/no.png" class="icon"></a>');
}

function process_it(id, ok)
{
    var dept = $(":input[name^='name']");
    var newval = orgval;
	var category = $('#id_category').val();
    if (ok){
        newval = dept.val();
        $.post("item/accessories_update.php", {id: ""+id+"", name: ""+newval+"", cat: ""+category+""}, function(data){
            if (data.length>0 && parseInt(data) > 0){
                alert('Accessory updated!');
            } else {
                alert('Accessory update failed!');
                newval = orgval;
            }            
        });
    }
    $('#td'+id).text(newval);
    orgval = '';
}


function department_change()
{
    var d = $('#id_department')[0];
    var did = d.options[d.selectedIndex].value;
    $.post("item/get_category_by_department.php", {queryString: ""+did+""}, function(data){
        if(data.length >0) {
            $('#id_category').empty();
            $('#id_category').append(data);
            category_change();
            //var c = document.getElementById('id_category');
            /*
            if (c.options.length > 1)
              $('#change').removeAttr("disabled");
            else
              $('#change').attr("disabled","diname sabled");
              */
        }
    });
}

function category_change()
{
  var d = document.getElementById('id_department');
  var c = document.getElementById('id_category');
  if (c.options.length > 1) {
    var cv = c.options[c.selectedIndex].value;
    var dv 
    if (d) 
      dv = d.options[d.selectedIndex].value;
    else
      dv = '<?php echo USERDEPT?>';
    if (cv > 0)
      //location.href="./?mod=item&sub=accessories&dept="+dv+"&cat="+cv;
	  $('form').submit();
   }
}

function refresh_me()
{
	location.href="./?mod=item&sub=accessories&cat="+$('#id_category').val();
}

var d = document.getElementById('id_department');
var c = document.getElementById('id_category');
if (d && (d.options.length == 1) && (d.options[0].value == 0))
	$('#id_department').attr('disabled', 'disabled');
if (c && (c.options.length == 1) && (c.options[0].value == 0))
	$('#id_category').attr('disabled', 'disabled');
</script>
