<?php 

if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$dept = USERDEPT;
$department_list = get_department_list();

if (isset($_POST['save']) && $_POST['save'] == 1) {
	$_id = save_deskcopy_title($_id, $_POST);
	if ($_id > 0){
		echo '<script>alert("Deskcopy title data saved successfully");location.href="./?mod=deskcopy&act=view&id='.$_id.'"</script>';
		return;
	} else {
        echo '<script>alert("Fail to save deskcopy title data!")</script>';
    }
}

$serial_list = null;
if ($_id > 0) {
    $data_title = get_deskcopy($_id);
    $serials = get_deskcopy_serials($_id);
    $tmp = array();
    foreach ($serials as $rec)
        $tmp[] = $rec['serial_no'];
    $serial_list = implode(',', $tmp);
} 
else {

  $data_title['number_of_items'] = '0';
  $data_title['id_title'] = '0';
  $data_title['serial_no'] = '';
  $data_title['isbn'] = '';
  $data_title['title'] = '';
  $data_title['description'] = '';
  $data_title['author_name'] = '';
  $data_title['publisher_name'] = '';
  $data_title['status'] = 'Available for Loan';
  $data_title['last_update'] = date('m/d/Y');
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

function fillPublisher(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestionsPublisher').fadeOut();", 100);
}

function suggestPublisher(me, inputString){
	if(inputString.length == 0) {
		$('#suggestionsPublisher').fadeOut();
	} else {
		$.post("deskcopy/suggest_publisher.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestionsPublisher').fadeIn();
				$('#suggestionsPublisherList').html(data);
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

function view_log()
{
    location.href="./?mod=deskcopy&act=history&id=<?php echo $_id?>";
}

function delete_it()
{

    ok = confirm('Are you sure delete <?php echo $data_title['title']?>?');
    if (ok) 
        location.href="./?mod=deskcopy&act=del&id=<?php echo $_id?>";     
}

$.fn.selectRange = function(start, end) 
{
    return this.each(function() {
        if (this.setSelectionRange) {
            this.focus();
            this.setSelectionRange(start, end);
        } else if (this.createTextRange) {
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
    });
};

function loaned_by_update(out_to)
{
    var loaned_by = document.getElementById('loanedby');
    loaned_by.innerHTML = out_to.value;
}


function edit_item(item)
{
    //$('#edit_item').val(item);    
}

function del_item(item)
{
    //if (confirm("Are you sure delete the item?")){
        var items = $('#items').val();
        var recs = items.split(',');
        var newrecs = new Array();
        for (var i=0; i < recs.length; i++){
            if (recs[i].search(new RegExp(item)) == -1){
                newrecs.push(recs[i]);
            }
        }
        $('#items').val(newrecs);
        display_list(newrecs.join(','));
    //}
}

function add_item()
{
    var item = $('#edit_item').val();
    if (item == '') return;
    var items = $('#items').val();
    if  (items.search(new RegExp(item)) == -1){
        if (items == '') items = item;
        else items += ',' + item;
        $('#items').val(items);
        $('#edit_item').val('');
    } else
        alert('Serial no. already exists!');
    display_list(items);
    $('#edit_item').focus();
}

function display_list(items)
{
    var text = '';
    var recs = items.split(',');
    if (items != '' && recs.length > 0){
        for (var i=0; i < recs.length; i++){
            text += '<li class="an_item" id="' + recs[i] + '">' ;
            text += '<a onclick="del_item(\''+ recs[i] +'\')"><img class="icon" src="images/delete.png" alt="delete"></a> ';
            text += '<a onclick="edit_item(\''+ recs[i] +'\')">' + (i+1) + '. ' + recs[i] + '</a></li>';
        }
    } else
        text = '--- no item specified ---';
    $('#item_list').html(text);
}

</script>

<style>
#suggestions {margin-top: 0; };
#suggestionsList li {margin-top: 15px; border: 1px solid white; height: 25px;};
</style>
<br/>
<form method="POST" id="telo">
<input type="hidden" name="save" value=0>
<input type="hidden" name="items" id="items" value='<?php echo $serial_list?>'>
<table cellspacing=1 cellpadding=3 id="itemedit">
<tr><th colspan=2>Create New / Edit Title</th></tr>
<tr valign="top">
    <td width=420>
      <table width="100%" class="itemlist" cellpadding=3 cellspacing=1>
        <tr class="alt">
          <td width=100>ISBN</td>
          <td><input type="text" id="isbn" name="isbn" value="<?php echo $data_title['isbn']?>"></td>
        </tr>
      <tr class="normal">
        <td>Title</td>
        <td><input type="text" name="title" value="<?php echo $data_title['title']?>" size=45></td>
      </tr>
      <tr class="alt">
        <td>Author</td>
        <td>
			<input type="text" id="author" name="author" value="<?php echo $data_title['author_name']?>" size=30 
			 onKeyUp="suggest(this, this.value);" onBlur="fill('author', this.value);" autocomplete="off">
			<div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;"> 
				<div class="suggestionList" id="suggestionsList"> &nbsp; </div>
			</div>
		</td>
      </tr>
      <tr class="normal">
        <td>Description</td>
        <td><textarea cols=44 rows=3 name="description"><?php echo $data_title['description']?></textarea></td>
      </tr>
      </tr>
      <tr class="alt">
        <td>Publisher</td>
        <td>
			<input type="text" id="publisher" name="publisher" value="<?php echo $data_title['publisher_name']?>" size=30 
			 onKeyUp="suggestPublisher(this, this.value);" onBlur="fillPublisher('publisher', this.value);" autocomplete="off">
			<div class="suggestionsBox" id="suggestionsPublisher" style="display: none; z-index: 500;"> 
				<div class="suggestionList" id="suggestionsPublisherList"> &nbsp; </div>
			</div>
		</td>
      </tr>
      <tr class="normal">
        <td>Department</td>
        <td><?php echo isset($department_list[$dept]) ? $department_list[$dept] : null?></td>
      </tr>
  </table>
    </td>
      <td width=250>
        <table width="100%" class="deskcopy itemlist" cellpadding=3 cellspacing=1> 
          <tr class="normal" valign="middle"><td class="alt">Serial Numbers (<?php echo $data_title['number_of_items']?> items)</td></tr>
          <tr><td>        
          <div id="serialbox">
            <ul id="item_list" style="padding-left: 0px"></ul>
            </div>
            </td></tr>
          </table>
        </td>
  </tr>
  <tr valign="top">
    <td align="center">
      <button type="button" onclick="save_title();return false" >Save</button> &nbsp;
      <button type="reset" >Reset</button> &nbsp;
      <button type="button" onclick="cancel_it()">Cancel</button>
<?php if ($_id > 0) { ?>
      &nbsp;&nbsp;
      <button type="button" onclick="delete_it()">Delete</button>
      &nbsp;
      <button type="button" onclick="new_title()">New Title</button>            
<?php } ?>
    </td>
    <td>
        <input type="text" id="edit_item" name="serial_no" autocomplete="off" >
        <a href="javascript:void(0)" onclick="add_item()"><img class="icon" src="images/add.png"></a>
        
    </td>
  </tr>  

</table>
</form>
<br/>
<br/>
<script>
$("#isbn").focus();
display_list($("#items").val());
 
</script>