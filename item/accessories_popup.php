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

$category_list = get_category_list(null, $dept);
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

ob_clean();
include 'header_popup.php';

?>
<div align="right">
	<h3 style="display:inline">Select Accessories &nbsp;</h3>
</div>
<form method="post">
<p style="text-align: left">
Category: <?php echo build_combo('id_category', $category_list, $_cat, 'category_change()');?>
</p>
<?php
if ($total_item > 0){
?>
<table width="100%" cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30>
  <th width=30> No </th>
  <th > Accessory Name </th>
  <th width=20></th>
</tr>

<?php

$rs = get_accessories($_orderby, $sort_order, $_start, $_limit, $_cat);
$counter = $_start;
if (is_resource($rs))
while ($rec = mysql_fetch_array($rs))
{
  $counter++;
  $_class = ($counter % 2 == 0) ? 'class="alt"':null;
  echo <<<DATA
  <tr $_class>
  <td align="right">$counter.</td>
  <td id="td$rec[id_accessory]">$rec[accessory_name]</td>
  <td align="center"><input type="checkbox" name="accessories_check" id="acc-$rec[id_accessory]" value="$rec[accessory_name]"></td>
  </tr>
DATA;
}

?>
<tr >
	<td colspan=3 class="pagination">
		<table width="100%" cellpadding=0 cellspacing=0>
		<tr>
			<td width="50%" align="left">
			<?php echo make_paging($_page, $total_page, $nav_link . '&page=');?>
			</td>
			<td align="right">
				<button type=button onclick="use_accessories()">Use Accessories</button>
			</td>
		</tr>
	</td>
</tr>
</table>

<?php
} else {
  echo '<div class="error" style="margin-top: 40px;">Data is not available!.</div>';
}
?>
</form>

<br/>
<script type="text/javascript">

function use_accessories()
{
	var checks = $('input[type="checkbox"]');
	var checked = new Array();
	var words = new Array();
	for (i=0; i<checks.length; i++){
		if (checks[i].checked){
			checked.push(checks[i].id);
			words.push(checks[i].value);
		}
	}
	if (checked.length>0){
		var acclis = window.opener.add_accessories(checked, words);

	}
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
