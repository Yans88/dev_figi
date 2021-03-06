<?php 

if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$dept = USERDEPT;
$data_title = get_deskcopy($_id);
$serials = get_deskcopy_serials($_id);

if (count($data_title) == 0){
    echo '<script>location.href = "./?mod=deskcopy&sub=title";</script>';
    return;
}


?>
<script>
 function save_title(){
  var frm = document.forms[0]
  frm.save.value = 1;
  frm.submit();
 }
 
function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("deskcopy/suggest_author.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			}
		});
	}
}

function cancel_it()
{
    location.href='./?mod=deskcopy';
}

function new_title()
{
    location.href='./?mod=deskcopy&act=edit';
}

function edit_it()
{
    location.href="./?mod=deskcopy&act=edit&id=<?php echo $_id?>";
}

function delete_it()
{

    ok = confirm('Are you sure delete <?php echo $data_title['title']?>?');
    if (ok) 
        location.href="./?mod=deskcopy&act=del&id=<?php echo $_id?>";     
}

function change_barcode(value)
{
    //$('#barcodeimg').src = "barcode.php?text=1&height=80&width=200&barcode="+value;
    var img = document.getElementById('barcodeimg');
    img.src = "barcode.php?text=1&&format=png&height=100&width=410&barcode="+value;
}

</script>

<br/>
<form method="POST" id="telo">
<input type="hidden" name="save" value=0>

<table border=0 cellspacing=1 cellpadding=2 id="itemedit">
<tr><th colspan=2>View Detailed Item</th></tr>
<tr valign="top">
    <td width=400>
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1>
        <tr class="alt">
          <td width=100>ISBN</td>
          <td><?php echo $data_title['isbn']?></td>
        </tr>
      <tr class="normal">
        <td>Title</td>
        <td><?php echo $data_title['title']?></td>
      </tr>
      <tr class="alt">
        <td>Author</td>
        <td><?php echo $data_title['author_name']?></td>
      </tr>
      <tr class="normal">
        <td>Description</td>
        <td><?php echo $data_title['description']?></td>
      </tr>      
      <tr class="alt">
        <td>Publisher</td>
        <td><?php echo $data_title['publisher_name']?></td>
      </tr>
      <tr class="normal">
        <td>Department</td>
        <td><?php echo $data_title['department_name']?></td>
      </tr>
      </table>
      <br>
      <div class="barcode"><img src="" id="barcodeimg"></div>
    </td>
    <td width=300 rowspan=2>
        <table cellspacing=1 cellpadding=2 width="100%" class="itemlist" >
<?php
?>
    <tr class="normal" valign="middle"><td class="alt">Serial Numbers (<?php echo $data_title['number_of_items']?> items)</td></tr>
    <tr><td><div id="serialbox" class="view">
            
<?php
    if (count($serials) > 0){
        echo '<table cellpadding=2 cellspacing=1 width="100%" class="deskcopy seriallist">
                <tr><th width=30>No</th><th>Serial No</th><th>Status</th></tr>';
        $no = 1;
        foreach ($serials as $rec){                
            $row_class = ($no % 2 == 0) ? ' class="alt"' : ' class="normal"' ;
            echo '<tr'.$row_class.'><td align="right">'.$no.'.</td><td>'.$rec['serial_no'].'</td><td>'.$rec['status'].'</td></tr>';
            $no++;
        }
        echo '</table></div>';
    }
?>
        </td>
      </tr>
      </table>
    </td>
    </tr>
  <tr>
    <td align="center" >
<?php if ($_id > 0) { ?>
      &nbsp;&nbsp;
      <button type="button" onclick="edit_it()">Edit</button>
      &nbsp;&nbsp; 
      <button type="button" onclick="new_title()">Create New</button>            
      &nbsp;&nbsp; 
      <button type="button" onclick="location.href='./?mod=deskcopy&act=history&id=<?php echo $_id?>';">Loan Record</button>            
      <!--
      &nbsp;&nbsp;
      <button type="button" onclick="location.href='./?mod=deskcopy&act=generate_bardode&id=<?php echo $_id?>';">View Serial</button>
      -->
<?php } ?>
    </td>
  </tr>  
</table>
</form>
<br/>
<br/>
<script>
    change_barcode('<?php echo $data_title['isbn']?>');
</script>