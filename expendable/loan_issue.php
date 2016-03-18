<?php

if (!defined('FIGIPASS')) exit;

$dept = USERDEPT;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_msg = null;
$today = date('j-M-Y H:i');

$request = get_expendable_request($_id);
$item_list = get_expendable_loan_item($_id);
// echo print_r($item_list);
$need_approval = ($request['without_approval'] == 0);

if (isset($_POST['issue']) && ($_POST['issue'] == 1)){    
	//// store loan-out
	
	$this_time = date(' H:i:s');
	$loan_date = convert_date($_POST['loan_date'], 'Y-m-d').$this_time;
	$return_date = convert_date($_POST['date_to_be_returned'], 'Y-m-d').$this_time;
	$query = "REPLACE INTO expendable_loan_out(id_loan, name, nric, contact_no, id_location, id_department, loan_date, return_date ) 
			  VALUES ($_id, '$_POST[name]', '$_POST[nric]', '$_POST[contact_no]', '$_POST[id_location]', '$_POST[id_department]', 
			  '$loan_date', '$return_date' )";
	mysql_query($query);
	echo mysql_error();
	if (mysql_affected_rows()>0){
		// $values = array();
		// foreach ($items as $id_item){
			// if (preg_match('/^[0-9]+$/', $id_item) > 0)
				// $values[] = "($_id, $id_item)";
		// }
		// echo print_r($item_list);
		foreach($item_list as $key=>$row){
			$qty =  $_POST['qty'][$key];
			$query = "REPLACE INTO expendable_loan_item_out(id_loan, id_item,quantity) VALUES ('$row[id_loan]', '$row[id_item]','$qty')";
			$rs = mysql_query($query);
			if($rs&&mysql_affected_rows($rs)>0){
				$query = "UPDATE INTO expendable_loan_item set `quantity` = '$qty' where id_loan=$row[id_loan] and id_item=$row[id_item]";
				$rs = mysql_query($query);
				}
			// echo mysql_error();
				
		}
	
		
		
		// update request
		$query = "UPDATE expendable_loan_request SET status = 'LOANED', id_department = $_POST[id_department] WHERE id_loan=$_id";
		
		mysql_query($query);
		// update loan process for approval type, otherwise insert
		$admin_id = USERID;
		
			$query = "REPLACE INTO expendable_loan_process(id_loan, issued_by, issue_date, issue_remark, loaned_by, loan_date, loan_remark) 
					  VALUES($_id, $admin_id, now(), '$_POST[issue_remark]', 0, now(), '$_POST[loan_remark]')";
					  
		mysql_query($query);
		// echo mysql_error();
		// keep signature
		
			$query = "REPLACE INTO expendable_loan_signature(id_loan, issue_sign, loan_sign)
					  VALUES($_id, '$_POST[issue_signature]', '$_POST[loan_signature]')";
		mysql_query($query);
		// echo mysql_error();
		// send notification
		$request = get_request($_id);
		// send_loan_issued_alert($request);
		// avoid refreshing the page
		goto_view($_id, LOANED);
		
	}
}

$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';


$location_list = get_location_list();
if (count($location_list) == 0)
    $location_list[0] = '--- no location available! ---';


$users = get_user_list();
$approval['approved_by'] = null;
$approval['approval_date'] = null;
$approval['approval_remark'] = null;
$approval['approval_sign'] = null;
if ($need_approval && ($request['status'] == APPROVED)){
    $process = get_request_process($_id);
    
}
 
$issue['issued_by'] = FULLNAME;
$issue['issue_date'] = $today;
$issue['loan_date'] = $today;
$issue['name'] = $request['requester'];
$issue['nric'] = $request['nric'];
$issue['contact_no'] = $request['contact_no'];

$department_option = build_option(get_department_list(), $request['id_department']);

// list($start_loan, $start_time) = explode(' ', $request['start_loan']);
// list($end_loan, $end_time) = explode(' ', $request['end_loan']);

// $messages['loan_issue_note'] = get_text('loan_issue_note');
?>


<h4>Loan-Out Form</h4>
<form method="post">



<table  class="loan_table" cellpadding=2 cellspacing=1>
<tr valign="top"><td><?php display_expendable_request($request);?></td></tr>

<tr>
    <td >
    <table width="100%" cellpadding=2 cellspacing=1 class="issue" >
    <tr>
        <th align="left" colspan=4>Loan-Out Details
            <div class="foldtoggle"><a id="btn_loan_issuance" rel="open" href="javascript:void(0)">&uarr;</a></div>
        </th>
    </tr>
    <tbody id="loan_issuance">
      <tr valign="top">  
        <td align="left" width="14%">Loan Out to</td>
        <td align="left" width="30%"><input type="text" name="name" size=28 id='refname' onchange="loaned_by_update(this)" value="<?php echo $issue['name']?>"></td>
        <td align="left" colspan=2><strong>Projected Date to return</strong></td>
        </tr>  
      <tr valign="top" class="alt">  
        <td align="left">NRIC</td>
        <td align="left">
            <input type="text" name="nric" size=28 value="<?php echo $issue['nric']?>" autocomplete="off" 
            onKeyUp="suggest_NRIC(this, this.value);" onBlur="fill_NRIC('nric', this.value);">
			<div class="suggestionsBox" id="suggestionsNRIC" style="display: none; z-index: 500;"> 
				<div class="suggestionList" id="suggestionsListNRIC"> &nbsp; </div>
			</div>            
        </td>            
        <td align="right">Sign Out</td>
        <td align="left"><input type="text" name="loan_date" id="loan_date" size=14 value="<?php echo $start_loan?>">
            <a id="button_loan_date" href="javascript:void(0)"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/></a>
            <script>
			$('#button_loan_date').click(
			  function(e) {
				$('#loan_date').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y"}).focus();
				e.preventDefault();
			  } );
            </script>
            
        </td>         
      </tr>  
      <tr valign="top">  
        <td align="left">Contact No.</td>
        <td align="left"><input type="text" name="contact_no" size=28  value="<?php echo $issue['contact_no']?>"></td>    
        <td align="right">To be Returned</td>
        <td align="left"><input type="text" name="date_to_be_returned" id="date_to_be_returned" size=14 value="<?php echo $end_loan?>">
            <a id="button_date_to_be_returned" href="javascript:void(0)"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/></a>
            <script>
			$('#button_date_to_be_returned').click(
			  function(e) {
				$('#date_to_be_returned').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y"}).focus();
				e.preventDefault();
			  } );
        </script>
        </td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Department</td>
        <td align="left">
			<select name="id_department" id="id_department">
			<option value=0></option>
			<?php echo $department_option ?>
                    </select>
		</td>  
			<td align="left" colspan=2 rowspan=2><u>Item List</u>
				<table width="80%">
					<thead>
						<tr>
							<th>Items</th>
							<th>Item Code</th>
							<th>Quantity</th>
						</tr>
					</thead>
					
					<?php foreach($item_list as $key=>$row){?>
					<tr>
						<td width="50%">
							<?php echo $row['item_name'] ?>
						</td>
						<td>
							<?php echo $row['item_code'] ?>
						</td>
						<td>
							<input type="number" name="qty[<?php echo $key;?>]" max="<?php echo get_expendable_stock($row['id_item']) ?>" value="<?php echo $row['quantity'] ?>">
						</td>
					</tr>
					<?php } ?>
					
				</table>
			</td>		
      </tr>  
      <tr valign="top">  
        <td align="left">Location</td>
        <td align="left" colspan=1>
           <select name="id_location" id="id_location">
		   <?php echo build_option($location_list );?>
           </select>
        </td>    
      </tr>
		
        </tbody>
        </table>
    </td>
</tr>
<tr>
    <td colspan=2>
<!-- signature -->
        <table width="100%">

<tr valign="middle">
    <th width="25%" rowspan=5>&nbsp;</th>
    <th width="25%" >&nbsp;</th>
    <th width="25%" align="center" >Issued By</th>
    <th width="25%" align="center">Loaned By</th>
</tr>
<tr valign="top">
    <td>Name</td>
    <td><?php echo $issue['issued_by']?></td>
    <td id="loanedby"></td>
</tr>
<tr valign="top" class="alt">
    <td>Date/Time Signature</td>
    <td><?php echo $issue['issue_date']?></td>
    <td><?php echo $issue['loan_date']?></td>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td><textarea name="issue_remark" cols=26 rows=3></textarea></td>
    <td><textarea name="loan_remark" cols=26 rows=3></textarea></td>
</tr>
<tr valign="top" class="alt">
    <td>Signatures</td>
    <td>
        <!--<div id="container" style="width:201px">
                <canvas id="imageView" height=80 width=200></canvas>
                <div style="text-align: right; position: absolute; top: 0; left: 182px;">
                    <a href="javascript:ResetSignature()" class="button clearsign" title="Clear signature space">X</a>
                </div>
        </div>-->
		<div id="signature-pad" class="m-signature-pad" style='width: 200px;height: 80px;'>
			<div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>

    </td>
    <td>
        <!--<div id="container2" style="width:201px">
            <canvas id="imageView2" height=80 width=200></canvas>
            <div style="text-align: right; position: absolute; top: 0; left: 182px;">
                <a href="javascript:ResetSignature2()" class="button clearsign" title="Clear signature space">X</a>
            </div>
        </div>-->
		<div id="signature-pad2" class="m-signature-pad" style='width: 200px;height: 80px;'>
			<div class="m-signature-pad--body">
			 <canvas id="imageView2" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
    </td>
</tr>
  
</table>
</td>
</tr>
<tr>
    <td colspan=2 valign="middle">
    <table cellpadding=2 cellspacing=1 >
        <tr>
            <td width="83%"><div class="note" id="issue_note" ><?php echo $messages['loan_issue_note']?></div></td>
            <td align="center"><input type="image" onclick="return submit_issue()" src="images/submit.png" ></td>
        </tr>
    </table>
    </td>
</tr>
</table>
<Input type="hidden" name="issue">
<Input type="hidden" name="issue_signature">
<Input type="hidden" name="loan_signature">
</form>
<br/><br/>
<script type="text/javascript" src="./js/signature2.js"></script>
<script type="text/javascript" src="./js/signature.js"></script>
<script type="text/javascript">


var department = '<?php echo $dept ?>';
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

function in_array(search, stack)
{
	for(var i=0; i<stack.length; i++)
		if (stack[i] == search)
			return true;
	return false;
}


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
    if (confirm("Are you sure delete the item?")){
        var items = $('#items').val();
        var recs = items.split(',');
        var newrecs = new Array();
        var cols = item.split('|');
        var cart = cols[3];
                
        for (var i=0; i < recs.length; i++){
             cols = recs[i].split('|');
            if ((recs[i] == item) || ((cart>0) && (cols[3]==cart))) continue;
            newrecs.push(recs[i]);
        }
        $('#items').val(newrecs);
        display_list(newrecs.join(','));
	}
}

function add_item()
{
        var item = $('#edit_item').val();
        if (item == '') return;
        var items = $('#items').val();
        var cols = item.match(/([^ ].+) *\((.+)\)/);
        if ((cols.length > 2) &&  (items.search(new RegExp(cols[1]+'|'+cols[2])) == -1)){
            get_item_info(cols[1], cols[2]);
            $('#edit_item').val('');
            $('#edit_item').focus();
        }
}

function get_item_info(asset_no, serial_no)
{
    $.post("loan/get_item.php", {asset_no: ""+asset_no+"", serial_no: ""+serial_no +""}, function(data){
        
        if(data.length >0) {
            var rows = data.split(',');
            var items = $('#items').val();
            var cnt = 0;
            if (items.length>0)
                cnt=items.split(',').length;
            var text,cols ;
            for (var i=0; i<rows.length;i++){
                cols = rows[i].split('|');
                cnt++;
                text = '<li class="an_item" id="' + cols[1] + '">' ;
                text += '<a onclick="del_item(\''+ rows[i]+'\')"><img class="icon" src="images/delete.png" alt="delete"></a> ';
                text += '<a onclick="edit_item(\''+ rows[i] +'\')">' + (cnt) + '. ' + cols[1] +  ' (' + cols[0] + ')</a></li>';
               // $('#item_list').append(text);
                if (items == '') items = cols.join('|');
                else items += ',' + cols.join('|');
            }
            $('#items').val(items);
            display_list(items);

        }
    });
}

function add_item_old()
{
	var item = $('#edit_item').val();
	if (item == '') return;
	var items = $('#items').val();
	var cols = item.match(/([^ ]+) *\((.+)\)/);
	if  (items.search(new RegExp(cols[1]+'|'+cols[2])) == -1){
		cols.shift();
		if (items == '') items = cols.join('|');
		else items += ',' + cols.join('|');
		$('#items').val(items);
        $('#edit_item').val('');
	} else
        alert('Email already exists!');
    display_list(items);
    $('#edit_item').focus();
}


function fill(id, thisValue, onclick) 
{
	if (thisValue.length>0 && onclick){
		var cols = thisValue.split('|');
		$('#'+id).val(cols[1] + ' (' + cols[0] + ')');
	}
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString)
{
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
        if (/,/.test(inputString)){
            var mathces = /.*, *(.+)/.exec(inputString);
            if (mathces != null)
                inputString = mathces[1];
        }
		$.post("loan/suggest_item.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", deptId: ""+department+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			} else
                $('#suggestions').fadeOut();
		});
	}
}

function fill_loc(id, thisValue) 
{
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestionsLoc').fadeOut();", 100);
}

function suggest_loc(me, inputString)
{
	if(inputString.length == 0) {
		$('#suggestionsLoc').fadeOut();
	} else {
		$.post("item/suggest_location.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestionsLoc').fadeIn();
				$('#suggestionsListLoc').html(data);
			}
		});
	}
}

function fill_NRIC(id, thisValue) 
{
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestionsNRIC').fadeOut();", 100);
}

function suggest_NRIC(me, inputString)
{
	if(inputString.length == 0) {
		$('#suggestionsNRIC').fadeOut();
	} else {
		$.post("item/suggest_nric.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestionsNRIC').fadeIn();
				$('#suggestionsListNRIC').html(data);
			}
		});
	}
}

//////////////////////////////////////////////////////////



function submit_issue()
{
    
	var frm = document.forms[0]
    
    if (isCanvasEmpty || isCanvas2Empty){
        alert('Please sign-in for issuer and requester!');
        return false;
    }
    var ok = confirm('Are you sure proceed this Loan-Out?');
    if (!ok)
        return false;
    
    var cvs = document.getElementById('imageView');
    frm.issue_signature.value = cvs.toDataURL("image/png");
    cvs = document.getElementById('imageView2');
    frm.loan_signature.value = cvs.toDataURL("image/png");    
    frm.issue.value = 1;
    frm.submit();
    return false;
}
////////////////////////////////////

    loaned_by_update(document.getElementById('refname'));
    // display_list($("#items").val());
    $('#edit_item').focus();

    $('#btn_loan_issuance').click(function (e){
        toggle_fold(this);
    });

</script>