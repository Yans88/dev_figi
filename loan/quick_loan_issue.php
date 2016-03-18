<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}
$my_nric = NRIC;
$draft = check_draft($my_nric);
if(!empty($draft)){
	$id_loan = $draft;
	redirect("./?mod=loan&sub=loan&act=draft&id=$id_loan");
}


$quick_issue = (defined('QUICK_LOAN_ENABLED') && QUICK_LOAN_ENABLED) ? 1 : 0;
$dept = USERDEPT;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_serialno = isset($_POST['serial_no']) ? $_POST['serial_no'] : null;
$_msg = null;
$today = date('j-M-Y H:i');
$this_time = date(' H:i:s');
$request = get_request($_id);
$need_approval = false;
$long_term = 0;



	//error_log('ql_issue: '.serialize($_POST));
if (isset($_POST['issue']) && ($_POST['issue'] == 1)){    
	if ($_id == 0){
		// create loan request
		$userid = $_POST['id_user'];
		//$start_date = $today.':00';
        $start_date = convert_date($_POST['loan_date'], 'Y-m-d').$this_time;
        $end_date = convert_date($_POST['date_to_be_returned'], 'Y-m-d').$this_time;
		$purpose = mysql_real_escape_string($_POST['purpose']);
		$remark = '';//mysql_real_escape_string($_POST['remark']);
		$id_department = $_POST['id_department'];
        $query = "INSERT INTO loan_request(requester, id_category, start_loan, end_loan, 
                    quantity, purpose, remark, request_date, status, without_approval, long_term, id_department) 
                    VALUES ($userid, $_POST[id_category], now(), '$end_date',
                    $_POST[quantity], '$purpose', '$remark',  now(), 'PENDING', 1, '$long_term', '$id_department')"; 

        mysql_query($query);
		//error_log(mysql_error().$query);
        if (mysql_affected_rows() > 0) {
            $submitted = true;
            $_id = mysql_insert_id();              
 		}
	}
	
    $items = get_item_from_serial_no($_items); // asset_no|serial_no,asset_no|serial_no,...
    if (count($items) > 0) { // selected item found
        // store loan-out
        $this_time = date(' H:i:s');
		$quick_issue = 1;//isset($_POST['quick_issue']) ? $_POST['quick_issue'] : 0;
        //$loan_date = convert_date($_POST['loan_date'], 'Y-m-d').$this_time;
        $return_date = convert_date($_POST['date_to_be_returned'], 'Y-m-d').$this_time;
        $chk = $_POST['loan_checklist'];
        $query = "REPLACE INTO loan_out(id_loan, name, nric, contact_no, id_location, id_department, loan_date, return_date, quick_issue, checklist) 
                  VALUES ($_id, '$_POST[name]', '$_POST[nric]', '$_POST[contact_no]', '$_POST[id_location]', '$_POST[id_department]', 
                  now(), '$return_date', $quick_issue,'$chk')";
        mysql_query($query);
        if (mysql_affected_rows()>0){
            $values = array();
            foreach ($items as $id_item){
                if (preg_match('/^[0-9]+$/', $id_item) > 0)
                    $values[] = "($_id, $id_item)";
            }
            
            // delete if existing items
            mysql_query("DELETE FROM loan_item WHERE id_loan = $_id");
            if (count($values)>0){
                $query = "INSERT INTO loan_item(id_loan, id_item) VALUES " . implode(', ', $values);
                mysql_query($query);
                //echo mysql_error().$query;
            }
            // update request
            $query = "UPDATE loan_request SET status = 'LOANED' WHERE id_loan=$_id";
            mysql_query($query);
            // update loan process for approval type, otherwise insert
            $admin_id = USERID;
			$issue_remark = mysql_real_escape_string($_POST['issue_remark']);
            if ($need_approval)
                $query = "UPDATE loan_process SET 
                          issued_by = $admin_id, 
                          issue_date = now(), 
                          issue_remark = '$_POST[issue_remark]', 
                          loaned_by = 0, 
                          loan_date = now(),  
                          loan_remark = '' 
                          WHERE id_loan = $_id";
            else
                $query = "REPLACE INTO loan_process(id_loan, issued_by, issue_date, issue_remark, loaned_by, loan_date, loan_remark) 
                          VALUES($_id, $admin_id, now(), '$issue_remark', 0, now(), '')";
            mysql_query($query);

            // update item's status
            if (count($items)>0){
                $item_status = ($long_term>0) ? ISSUED : ONLOAN;
                $query = "UPDATE item SET status_update = now(), 
                          id_status = '".$item_status."', issued_to = '$_POST[id_user]', issued_date = now(), id_location = '$_POST[id_location]' 
                          WHERE id_item in (" . implode(',', $items) . ")";
                mysql_query($query);
                /*
                // get cart id if any
                $query = "SELECT DISTINCT(id_cart) FROM mobile_cart_item 
                            WHERE id_item in (" . implode(',', $items) . ")";
                $rs = mysql_query($query);
                $carts = array();
                if ($rs && mysql_num_rows($rs)>0){
                    while ($rec = mysql_fetch_row($rs))
                        $carts[] = $rec[0];
                    if (count($carts)>0){
                        $query = "UPDATE mobile_cart SET cart_status = '".ONLOAN."' 
                                    WHERE id_cart in (" . implode(',', $carts) . ")";
                        mysql_query($query);
                    }
                }
                */
              //echo  mysql_error().$query;
            }
			// save item's accessories
			$accessories = explode('~~', $_POST['accs']);
			if (count($accessories)>0){
				$values = array();
				foreach($accessories as $line){
					if (preg_match('/^(\d+):(.+)$/', $line, $matches)){
						$id_item = $matches[1];
						$accs = explode('|', $matches[2]);
						foreach($accs as $idacc)
							$values[] = '(' . $_id . ',' . $idacc . ', '. $id_item.')';
					}
				}
				if (count($values)>0){
					$query = "INSERT INTO loan_item_accessories VALUES " . implode(',', $values);
					mysql_query($query);
					//error_log(mysql_error().$query);
				}
			}
            // send notification
            $request = get_request($_id);
            send_loan_issued_alert($request);
            // avoid refreshing the page
           goto_view($_id, LOANED);
        }
	} else $_msg = 'There is no item selected !';
	exit;
}

$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';


$location_list = get_location_list();
if (count($location_list) == 0)
    $location_list[0] = '--- no location available! ---';

$now = time()+(3600);
$to_be_returned = date('d-M-Y H:00', $now);
$messages['loan_issue_note'] = get_text('loan_issue_note');
$forprint = false;
?>
<style>
.loan_table #edit_item { width: 200px; }
.scan {font-size: 14pt; font-weight: bold; padding: 7px 5px; width: 200px}
.hide { display: none; }
label[for=agree] {cursor: pointer;}
</style>

<h4 class="center">Quick Loan</h4>
<form method="post">
<input type="hidden" name="items" id="items" value="">
<input type="hidden" name="accs" id="accs" value="">
<input type="hidden" name="loan_checklist" id="loan_checklist" value="">
<input type="hidden" name="iditems" id="iditems" value="">
<input type="hidden" name="loan_date" id="loan_date" value="<?php echo $today?>">
<input type="hidden" name="issue" id="issue" value="">
<input type="hidden" name="id_user" id="id_user" value=0>
<input type="hidden" name="id_category" id="id_category" value=0>
<input type="hidden" name="id_department" id="id_department" value=0>
<input type="hidden" name="quantity" id="quantity" value=0>

<div class="loan_table hide middle" id="ql_table">
    <table width="100%" cellpadding=2 cellspacing=1 class="itemlist request hide" id="ql_request" >
	  <thead>
      <tr valign="top" align="left">
        <th align="left" colspan=2>Loan Request
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_request" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
        </th>
      </tr>  
	 </thead>
      <tbody id="loan_request">
      <tr valign="top" align="left" class="alt">
        <td align="left" width="17%">Request Date/Time</td>
        <td align="left" ><?php echo $today?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Requested By</td>
        <td align="left"><input type="text" name="name" id="requestor" value="" size=30></td>
      </tr>
      <tr valign="top" class="alt">  
        <td align="left">NRIC</td>
        <td align="left"><input type="text" name="nric" id="nric" value="" size=10></td>
      </tr>
       <tr valign="top">  
        <td align="left">Contact No</td>
        <td align="left"><input type="text" name="contact_no" id="contact_no" value="" size=16></td>
      </tr>
       <tr valign="top" class="alt">  
        <td align="left">Purpose</td>
        <td align="left" ><input type="text" name="purpose" id="purpose" value="" size=55 ></td>
      </tr>
	  <!--
      <tr valign="top" class="alt">  
        <td align="left">Department</td>
        <td align="left"><input type="text" name="department" id="department" size=20> </td>
      </tr>  
      <tr valign="top">  
        <td align="left" valign="middle">Request Remark</td>
        <td align="left" ><textarea cols=40 rows=2 name="remark" id="remark"></textarea></td>
      </tr>
	  -->
      </tbody>
    </table>
    <script>
    $('#btn_loan_request').click(function (e){
        toggle_fold(this);
    });
    </script>

    <table width="100%" cellpadding=2 cellspacing=1 class="itemlist issue hide" id="ql_issue">
    <tr>
        <th align="left" colspan=2>Loan-Out Details
            <div class="foldtoggle"><a id="btn_loan_issuance" rel="open" href="javascript:void(0)">&uarr;</a></div>
        </th>
    </tr>
    <tbody id="loan_issuance">
         <tr valign="top">  
           <td align="left" width="17%">To be Returned</td>
        <td align="left"><input type="text" name="date_to_be_returned" id="date_to_be_returned" size=20 value="<?php echo $to_be_returned?>">
            <a id="button_date_to_be_returned" href="javascript:void(0)"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/></a>
            <script>
			$('#button_date_to_be_returned').click(
			  function(e) {
				$('#date_to_be_returned').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y %H:%i"}).focus();
				e.preventDefault();
			  }
			  );
			 
        </script>
        </td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Location</td>
        <td align="left" >
           <select name="id_location" id="id_location">
		   <?php echo build_option($location_list );?>
           </select>
        </td>    
      </tr>
       <tr valign="top" align="left">
        <td align="left" colspan=2>
			<div class="clear"></div>
			<div class="leftcol" style="width:45%">Items:</div>
			<div class="rightcol" style="width:45%; text-align: right; "><a id="additem" style="cursor: pointer; text-decoration: underline">Add Item</a></div>
			<div class="clear"></div>
            <div id="item_list" style="padding-left: 0px"></div>
			<br>
        </td>
        </tr>
        
        <tr>
    <td colspan=2 valign="middle">	
     <table width="100%" cellpadding=2 cellspacing=1 class="issue" >
	<tr>
        <th align="left" colspan=4>Loan-Out Checklist
            <div class="foldtoggle"><a id="btn_loan_checklist" rel="open" href="javascript:void(0)">&uarr;</a></div>
        </th>
    </tr>	
		<tbody id="loan_checklist"></tbody>	
    </table>
    </td>
</tr>
        
	<tr class="alt">
		<td>Loan remark</td>
 	   <td><textarea name="issue_remark" cols=60 rows=2></textarea></td>
	</tr>
    </tbody>
</table>
	<div style="padding: 0" id="ql_submit" class="hide">
	<div class="note" id="issue_note" style="padding: 2px 2px"><?php echo $messages['loan_issue_note']?></div>
        <div style="width: 120px; float: right; text-align: right"> <button type="button" id="btn_submit" style="padding: 3px 15px; font-size: 12pt; margin-top: 4px;cursor: pointer; margin-right: 1px "> Submit </button></div>
        <div class="leftcol" style=" vertical-align: middle; margin-top: 10px"><input type="checkbox" id="agreement"> <label for="agreement">I have read, understand and fully agree with the Terms & Conditions written above.</label></div>
    </div>
    <div class="clear"></div>
	</div>
</div>
</form>
<br/><br/>
<br/><br/>
&nbsp;
<div id="dialog_accessories" class="dialog ui-helper-hidden">
<p>Select accessories for the selected item:</p>
<div id="accessories_option"> </div>
<div>
<br>
<button type="button" id="btn_setacc"> Set Accessories </button>
<button type="button" id="btn_close"> Close </button>
</div>
</div>
<div id="dialog_nric" class="dialog ui-helper-hidden">
	<div style="text-align: center">
		<p>Scan your NRIC before further process:</p>
		<p><input type="text" class="scan" name="scan_nric" id="scan_nric" ></p>
		<p id="dn_msg"></p>
		<br>
	</div>
</div>

<div id="dialog_item" class="dialog ui-helper-hidden">
	<div style="text-align: center">
		<p>Scan item asset no:</p>
		<p><input type="text" class="scan" name="scan_asset" id="scan_asset" ></p>
		<p id="di_msg"></p>
		<br>
	</div>
</div>

<?php 
if ($quick_issue!=1) { 
	echo '<script type="text/javascript" src="./js/signature2.js"></script>';
	echo '<script type="text/javascript" src="./js/signature.js"></script>';
}
?>
<script type="text/javascript">

var category = 0;
var department = '<?php echo $dept ?>';
var quick_issue = <?php  echo $quick_issue?>;
var asset_len = <?php  echo ASSETNO_LENGTH?>;
var nric_len = <?php  echo NRIC_LENGTH?>;
var item_accs = [];
var all_accs = [];
var all_accs_text = [];
var window_item = false;
var find_item_invisible = true;     
var current_acc_index = -1;


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
}

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

$('#edit_item').keyup(function(){
	var len = $(this).val().length;
	//if (len==asset_len) alert('submit')
});

$('#scan_asset').keyup(function(event){
	var len = $(this).val().length;
	if (len==asset_len || event.keyCode == '13'){
		event.preventDefault();
		append_item(); 
	}
});

function load_customer(nric)
{
    $.post("loan/get_user.php", {nric: ""+nric+""}, function(data){
         
        if(data.length >0) {
			var user = JSON.parse(data);
			$('#dn_msg').html(user.full_name);
			$('#id_user').val(user.id_user);
			$('#id_department').val(user.id_department);
			$('#requestor').val(user.full_name);
			$('#nric').val(user.nric);
			$('#contact_no').val(user.contact_no);
			$('#department').val(user.department);
			$('#ql_request').show();
			$('#ql_table').show();
			dialog_nric.dialog('close'); 
			dialog_item.dialog('open');

        } else {
			$('#dn_msg').html('NRIC is not recognized!!');
		}
    });
}


$('#scan_nric').keyup(function(event){
	var nric = $(this).val();
	if (nric.length>=nric_len || event.keyCode == '13'){
		event.preventDefault();
		continue_quick_loan();
		;
	}
});


function del_item(item)
{
    if (confirm("Are you sure delete the item?")){
        var items = $('#items').val();
        var recs = items.split(',');
        var newrecs = new Array();
        var cols = item.split('|');
        var cart = cols[3];
        var new_all_accs = new Array();
        var new_all_accs_text = new Array();

                
        for (var i=0; i < recs.length; i++){
             cols = recs[i].split('|');
            if ((recs[i] == item) || ((cart>0) && (cols[3]==cart))) continue;
            newrecs.push(recs[i]);
			new_all_accs.push(all_accs[i]); 
			new_all_accs_text.push(all_accs_text[i]); 
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
		//var cols = item.match(/([^ ].+) *\((.+)\)/);
        var cols = item.match(/([^,]+), *([^,]+)/);
        if (cols.length > 2) {
			if (items.search(new RegExp(cols[1]+'|'+cols[2])) == -1){
				get_item_info(cols[1], cols[2]);
				$('#edit_item').val('');
				$('#edit_item').focus();
			} else {
				alert('Asset No / Serial No already exist in the list!');
			}
        }
}

function get_item_info(asset_no, serial_no)
{
	$('#edit_item').attr('disabled', 'disabled');
	var tr = $('tr[class="an_item"]').length;
    $.post("loan/get_item.php", {asset_no: ""+asset_no+"", serial_no: ""+serial_no +""}, function(data){
         
		// asset_no|serial_no|id_item|cart|category|brand|model|loan_period|id_category|accessories-list
        if(data.length >0) {

			$('#di_msg').html('');
            var rows = data.split(',');
            var items = $('#items').val();
            var cnt = 0;
            if (items.length>0)
                cnt=items.split(',').length;
			else {
				// capture first item category
                var cols = rows[0].split('|');
				category = cols[8];
			}
	    if(tr == 0){
				$.post("loan/get_checklist.php", {category: ""+category+""}, function(dataa){
					if(dataa.length > 0){
						display_checklist(dataa);
					}
				});
			}	
            var text,cols ;
            for (var i=0; i<rows.length;i++){
                cols = rows[i].split('|');
				if (cols[8]==category){
					if (items == '') {
						items = cols.join('|');
						$('#id_category').val(cols[8]);	
						var period = cols[7] * 86400000;
						var dateFormat = "%e-%b-%Y %H:%I";
						var dateConv = new AnyTime.Converter({format:dateFormat});
						var fromDay = dateConv.parse($("#loan_date").val()).getTime();
						var dayLater = new Date(fromDay+period);
						$('#date_to_be_returned').val(dateConv.format(dayLater)).AnyTime_noPicker().AnyTime_picker({format: dateFormat});
					}
					else items += ',' + cols.join('|');
				} else {
					//alert('Your scanned item has different category!');
					var item = serial_no || asset_no;
					$('#di_msg').html('Scanned item "'+item+'" has different category!');
					i=items.length;
				}
            }
            $('#items').val(items);
            display_list(items);
			all_accs[cnt] = '';
			all_accs_text[cnt] = '';
			//$('#cat-'+cols[8]).trigger('click');
        } else {
			$('#di_msg').html('Asset no does not available!');
		}
		$('#edit_item').removeAttr('disabled');
    });
}

function display_list(items)
{
    var text = '';
    var cols = '';
    var recs = items.split(',');
    if (items != '' && recs.length > 0){
        text  ='<table width="100%" cellpadding=2>';
		text += '<tr><th>No</th><th>Serial No</th><th>Asset No</th><th>Category</th><th>Brand</th><th>Model</th><th>Accessories</th><th width=20>Del</th></tr>';
        for (var i=0; i < recs.length; i++){
            cols = recs[i].split('|'); // asset_no|serial_no|id_item|cart|category|brand|model|loan_period|id_category|accessories-list
			var accessories_list = '';
			var acc = '';
			if (all_accs_text[i]) acc = all_accs_text[i].join(', ');
			accessories_list = '<span id="acclist-'+i+'">'+acc+'</span><a href="#'+i+'" onclick="addacc(this)" id="cat-'+cols[8]+'" style="font-weight: bold"> + </a>';
			text += '<tr class="an_item" id="' + cols[1] + '"><td>' ;
            text += (i+1) + '. </td><td>' + cols[1] +  '</td><td>' + cols[0] + '</td>';
			text += '<td>'+cols[4]+'</td><td>'+cols[5]+'</td><td>'+cols[6]+'</td><td>'+accessories_list+'</td>';
            text += '<td><a onclick="del_item(\''+ recs[i] +'\')"><img class="icon" src="images/delete.png" alt="delete"></a></td></tr> ';
        }
		text += '</table>';
    } else
        text = '--- no item specified ---';
    $('#item_list').html(text);
    $('#quantity').val(recs.length);
}

$('#btn_submit').click(function(){
    var frm = document.forms[0]
    var items_val = $('#items').val();
    var radios = document.getElementsByClassName('chk');
    var items = [];
    if (items_val == ''){
        alert('Please add Serial No of Item!');
        return false;
    }
    else  items = items_val.split(',');
	
	var acc_text = '';
	var checklist = new Array;
    for (var i = 0;i < radios.length; i++) {
        if (radios[i].checked) {
           radioValues = radios[i].value;
	       checklist[i]=radioValues;
	       test = checklist;	  
        }	 
  }
$('#loan_checklist').val(checklist);   
	for (var i=0; i<all_accs.length; i++){
		var row = items[i].split('|');
		if (all_accs[i].length>0)
			acc_text += row[2]+':'+all_accs[i].join('|')+'~~';
		else
			acc_text += row[2]+':~~';
	}
	$('#accs').val(acc_text);
	var purpose = $('#purpose').val();
    if ( purpose.replace(' ', '')==''){
        alert('Please enter the purpose of loan!');
        return false;
    }
    var agreement = $('#agreement');
    var agree = false;
    if (agreement != undefined)
        agree = agreement.get(0).checked;

    if (!agree){
        alert('You must agree to the terms and conditions to continue!.');
        return false;
    }

 	var ok = confirm('Are you sure proceed this Loan-Out?');
    if (!ok)
        return false;
	
	
    frm.issue.value = 1;
    frm.submit();
    return false;
});

function addacc(me){
	var id_cat = me.id.substr(4);
	var id_idx = me.href.substr(me.href.lastIndexOf('#')+1);
	current_acc_index = id_idx;

	if (id_cat > 0)
		load_accessories(id_cat);
	else 
		alert("Item's category is unknown!");
}

function load_accessories(cat){
	$.post("loan/get_accessories.php", {id_category: cat}, function(data){
		if(data.length >0) {
			display_accessories_dialog(data);
		} else alert('This item does not has accessories!');
	})
}


function display_accessories_dialog(data){
	$('#dialog_accessories').dialog({
		modal: true, width: 400, height: 200,
		title: 'Add Accessories'});

	var rows = data.split('|');
	var thelist = '<ul>';
	for (var i=0; i<rows.length; i++){
		var cols = rows[i].split('~');
		var cbid = 'cbacc-'+cols[0];
		thelist += '<li><input type="checkbox" name="acc" class="cbacc" value="'+cols[0]+'" id="'+cbid+'"> <label for="'+cbid+'">'+cols[1]+'</li>';
	}
	thelist += '</ul>';
	$('#accessories_option').html(thelist);
}

$('#btn_close').click(function(){
	$('#dialog_accessories').dialog('close');
	current_acc_index = -1;
});

$('#btn_setacc').click(function(){
	var selected = Array();
	var selected_text = Array();
	$('.cbacc').each(function(id, elm){
		if ($(elm).attr('checked')){
			selected.push($(elm).attr('value'));
			selected_text.push($('label[for='+$(this).attr('id')+']').html());
		}
	});
	$('#dialog_accessories').dialog('close');
	
	if (current_acc_index>-1){
		all_accs[current_acc_index] = selected;
		all_accs_text[current_acc_index] = selected_text;
		var text= '';
		if (selected_text.length>0)
			text = selected_text.join(', ');
		$('#acclist-'+current_acc_index).html(text+' ');
		
	}
	current_acc_index = -1;
	//alert(all_accs)
});



function display_find_item_dialog()
{
	var w = $('#find_item_dialog');
	if (find_item_invisible){
		w.show();
		$('#dialog_outer').css('z-index', 1000 );
	} else {
		w.hide();
		$('#dialog_outer').css('z-index', -10);
	}
	find_item_invisible = !find_item_invisible;
}

//loaned_by_update(document.getElementById('refname'));
display_list($("#items").val());
$('#edit_item').focus();

$('#btn_loan_issuance').click(function (e){
	toggle_fold(this);
});

function append_item(){
	var asset_no = $('#scan_asset').val();
	var items  = $('#items').val();
	if (asset_no.length>0){
		var exists = false;
		if (items.length>0)
			exists = items.search(asset_no) != -1;

		if (!exists){
			//$('#di_msg').append('<li>'+asset_no+' will be added to item list.</li>');
			$('#di_msg').append('getting info for '+asset_no+' ...');
			get_item_info(asset_no, '');
			//$(":button:contains('Done')").focus();
			
		} else
			alert(asset_no+' is already in the list!.');
	}
	$('#scan_asset').val('');
}

var dialog_item =
	$('#dialog_item').dialog({
		modal: true, width: 400, 
		autoOpen: false, 
		buttons:  {
			'Add Item': append_item,
			'Done': function() { $('#scan_asset').val(''); dialog_item.dialog('close'); }
		},
		title: 'Scan Item'});
	
var dialog_nric =
	$('#dialog_nric').dialog({
		modal: true, width: 400, 
		title: 'Scan NRIC',
		autoOpen: false, 
		buttons:  {
			'Continue': continue_quick_loan ,
			'Cancel': function() { 
				location.href = './?mod=loan';
				$('#scan_nric').val(''); dialog_nric.dialog('close'); 
				}
		},
		close: function(){
			if ($('#scan_nric').val().length<3)
				location.href = '?mod=loan';	
		}
		});

function continue_quick_loan() { 
	var nric = $('#scan_nric').val();
	load_customer(nric);
	$('#ql_issue').show();
	$('#ql_submit').show();
}

function display_checklist(chk)
{
    var text = '';
    var cols = '';	
    var recs = chk.split(',');
    if (chk != '' && recs.length > 0){
        text  ='<tr><td>&nbsp;<b>Title</b></td><td><b>Yes</b></td><td><b>No</b></td><td><b>NA</b></td></tr>  ';		
        for (var i=0; i < recs.length; i++){
			var clss = (i % 2 == 0) ? 'alt' : 'top';
            cols = recs[i].split('|');	
			var mandatory = (cols[2] == 1) ? 'chk mandatory' : 'chk';
			var requiree = (cols[2] == 1) ? 'required="required"' : '';
			text += '<tr class="'+clss+' '+i+'">' ;
            text += '<td width="83%"><li style="margin-top:0px;">'+cols[1]+'</li></td>';
			text += '<td><input type="radio" '+requiree+' class="'+mandatory+'" name="'+cols[1]+'" id="chk" value="1_'+cols[0]+'"></td>';	
			text += '<td><input type="radio" '+requiree+' class="'+mandatory+'" name="'+cols[1]+'" id="chk" value="0_'+cols[0]+'"></td>';	
			text += '<td><input type="radio" '+requiree+' class="'+mandatory+'" name="'+cols[1]+'" id="chk" value="2_'+cols[0]+'" checked></td></tr>';	
        }
		
    } else
        text = '--- no item specified ---';
    $('tbody[id="loan_checklist"]').html(text);
}		

$('#additem').click(function(){
	dialog_item.dialog('open');
});


dialog_nric.dialog('open');
$('#scan_nric').focus();
</script>
