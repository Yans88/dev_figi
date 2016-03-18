<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}
$dept = USERDEPT;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_serialno = isset($_POST['serial_no']) ? $_POST['serial_no'] : null;
$_dept = isset($_POST['id_department']) ? $_POST['id_department'] : null;
$_cat = isset($_POST['id_category']) ? $_POST['id_category'] : null;
$_msg = null;
$today = date('j-M-Y H:i');

$issue = get_item_issue($_id);
$items = get_item_issue_list($_id);
$item_list = null;
$ids = array();
$no = 0;
foreach($items as $rec){
    $no++;
    $item_list .= $no . '. ' . $rec['asset_no'] . ' ('.$rec['serial_no'].')<br/>';
    $ids[] = $rec['id_item'];
}
$issue['item_list'] = $item_list;

if (isset($_POST['returns']) && ($_POST['returns'] == 1)){    
	
    //$items = get_item_from_serial_no($_items); // asset_no|serial_no,asset_no|serial_no,...
    if (count($ids) > 0) { // selected item found
        $return_remark = @mysql_escape_string($_POST['return_remark']);
        $receive_remark = @mysql_escape_string($_POST['receive_remark']);
        $returned_by = @mysql_escape_string($_POST['returned_by']);
        $received_by = USERID;
        $query = "INSERT INTO item_issuance_return(id_issue, return_date, returned_by, received_by, return_remark, receive_remark) 
                  VALUES ($_id, now(), '$returned_by', $received_by, '$return_remark', '$receive_remark')";
        mysql_query($query);
        if (mysql_affected_rows()>0){
            $values = array();
            foreach ($ids as $id_item){
                if (preg_match('/^[0-9]+$/', $id_item) > 0){
                    $values[] = "($_id, $id_item)";
                }
            }
            // keep signatures
            $query = "UPDATE item_issuance_signature SET return_sign = '$_POST[return_sign]', receive_sign = '$_POST[receive_sign]'
                        WHERE id_issue = $_id";
            mysql_query($query);
            
            // delete if existing items
            mysql_query("DELETE FROM item_issuance_return_list WHERE id_issue = $_id");
            if (count($values)>0){
                $query = "INSERT INTO item_issuance_return_list(id_issue, id_item) VALUES " . implode(', ', $values);
                mysql_query($query);
                //echo mysql_error().$query;
            }
            
            // update item's status
            if (count($items)>0){
                $item_status = STORAGE;
                $issued_to = FULLNAME;
                $id_category = $issue['src_category'];
                $remark = "returned from $issue[department_name]";
                $query = "UPDATE item SET  id_category = $id_category, status_update = now(), status_defect = '$remark', 
                          id_status = '$item_status', issued_to = '$issued_to', issued_date = now()   
                          WHERE id_item IN (" . implode(',', $ids) . ")";
                mysql_query($query);

            }
            //update issue status
            $query = "UPDATE item_issuance SET status = 'RETURNED' WHERE id_issue = $_id";
            mysql_query($query);
            
            // send notification
            // avoid refreshing the page
            echo '<script>alert("Item issuance return info has been saved!"); location.href="./?mod=item&act=view_issue&id='.$_id.'";</script>';
            exit;
        }
	} else $_msg = 'There is no item selected !';
}



?>

<h4>Departmental Item Issuance (Return)</h4>
<div style="width: 800px; text-align: left; ">
<a class="button" href="./?mod=item&act=issue">Create Item Issuance</a>
<a class="button" href="./?mod=item&act=issue_history">Issuance History</a>
<a class="button" href="./?mod=item&act=issue_return">Return Item</a>
</div>
<form method="post">
<input type="hidden" name="items" id="items" value="">
<input type="hidden" name="iditems" id="iditems" value="">
<table  class="loan_table" cellpadding=2 cellspacing=1>
<tr><td ><?php view_issue($issue); ?></td></tr>
<tr><td ><?php view_issued_to($issue); ?></td></tr>
<tr><td ><?php view_issue_signature($issue); ?></td></tr>
<tr><td >
    <table width="100%">
    <tr valign="middle">
        <th >&nbsp;</th>
        <th width="200" align="center">Returned By</th>
        <th width="200" align="center">Received By</th>
    </tr>
    <tr valign="top">
        <td>Name</td>
        <td>
            <input type="text" id="returned_by" name="returned_by" size=20 value="<?php echo $issue['loaned_by_name']?>" onKeyUp="suggest(this, this.value);" autocomplete="off" >
            <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 100;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
            </div>
            
        </td>
        <td><?php echo FULLNAME?></td>
    </tr>
    <tr valign="top" class="alt">
        <td>Remarks</td>
        <td><textarea name="return_remark" cols=26 rows=3></textarea></td>
        <td><textarea name="receive_remark" cols=26 rows=3></textarea></td>
    </tr>
    <tr valign="top">
        <td>Signatures</td>
        <td>
            <div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
        </td>
        <td>
           <div class="m-signature-pad--body">
			 <canvas id="imageView2" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
        </td>
    </tr>
    </table>
    </td>
</tr>
<tr>
    <td align="right" valign="middle">
        <button type="button" onclick="return submit_return()">Submit</button>
    </td>
</tr>
</table>
<Input type="hidden" name="returns">
<Input type="hidden" name="return_sign">
<Input type="hidden" name="receive_sign">
</form>
<br/><br/>
<script type="text/javascript" src="./js/signature2.js"></script>
<script type="text/javascript" src="./js/signature.js"></script>
<script>
function fill(id, thisValue, onclick) 
{
	if (thisValue.length>0){
		
		$('#'+id).val(thisValue);
	}
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString)
{
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
        var cat = $('#id_category option:selected').val();
		$.post("user/user_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			}
		});
	}
}

function submit_return()
{
    var frm = document.forms[0]
    var items_val = $('#items').val();
    
    if (frm.name.value == ''){
        alert('Please fill in Loan Out to!');
        return false;
    }
    if (isCanvasEmpty || isCanvas2Empty){
        alert('Please sign-in for returner and reveiver!');
        return false;
    }
    var ok = confirm('Are you sure proceed this Issuance return?');
    if (!ok)
        return false;
    
    var cvs = document.getElementById('imageView');
    frm.return_sign.value = cvs.toDataURL("image/png");
    cvs = document.getElementById('imageView2');
    frm.receive_sign.value = cvs.toDataURL("image/png");    
    frm.returns.value = 1;
    frm.submit();
    return false;
}

</script>

