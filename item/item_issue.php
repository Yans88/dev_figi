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

if (isset($_POST['issue']) && ($_POST['issue'] == 1)){    
	
    $items = get_item_from_serial_no($_items); // asset_no|serial_no,asset_no|serial_no,...
    if (count($items) > 0) { // selected item found
        $loan_remark = @mysql_escape_string($_POST['loan_remark']);
        $issue_remark = @mysql_escape_string($_POST['issue_remark']);
        $src_category = $_POST['id_category'];
        $dst_category = $_POST['dest_cat'];
        $dst_department = $_POST['id_department'];
        $src_department = USERDEPT;
        $issued_to = $_POST['issuedto'];
        $issued_by = USERID;
        $query = "INSERT INTO item_issuance(src_department, src_category, dst_department, dst_category, issue_date, 
                    issued_by, loaned_by, issue_remark, loan_remark, status) 
                  VALUES ($src_department, $src_category, $dst_department, $dst_category, now(), $issued_by, 
                    '$issued_to', '$issue_remark', '$loan_remark', 'ISSUED')";
        mysql_query($query);
        if (mysql_affected_rows()>0){
            $_id = mysql_insert_id();
            $values = array();
            $ids = array();
            foreach ($items as $id_item){
                if (preg_match('/^[0-9]+$/', $id_item) > 0){
                    $values[] = "($_id, $id_item)";
                    $ids[] = $id_item;
                }
            }
            
            // delete if existing items
            mysql_query("DELETE FROM item_issuance_list WHERE id_issue = $_id");
            if (count($values)>0){
                $query = "INSERT INTO item_issuance_list(id_issue, id_item) VALUES " . implode(', ', $values);
                mysql_query($query);
                //echo mysql_error().$query;
            }
            
            // keep signatures
            $query = "REPLACE item_issuance_signature (id_issue, issue_sign, loan_sign, return_sign, receive_sign)
                        VALUE($_id, '$_POST[issue_sign]', '$_POST[loan_sign]', '', '')";
            mysql_query($query);
            //echo mysql_error().$query;
            // update item's status
            if (count($items)>0){
                $item_status = AVAILABLE_FOR_LOAN;
                $issued_to = $_POST['issuedto'];
                $query = "UPDATE item SET  id_category = $dst_category, status_update = now(), status_defect = '$issue_remark', 
                          id_status = '$item_status', issued_to = '$issued_to', issued_date = now(), id_department = '$dst_department'    
                          WHERE id_item IN (" . implode(',', $ids) . ")";
                mysql_query($query);

            }
            // send notification
            // avoid refreshing the page
            echo '<script>alert("Item issuance info has been saved!"); location.href="./?mod=item&act=view_issue&id='.$_id.'";</script>';
            exit;
        }
	} else $_msg = 'There is no item selected !';
}
/*
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';
*/
 
$issue['issued_by'] = FULLNAME;
$issue['issue_date'] = $today;
$issue['loan_date'] = $today;
$issue['loaned_by'] = FULLNAME;

$department_list = get_department_list();
$temp = array();
foreach ($department_list as $did => $dname)
    if ($did != USERDEPT)
        $temp[$did] = $dname;
$department_list = $temp;

?>

<style>
.loan_table #edit_item { width: 600px; }
.suggestionsBox { width: 598px; }
</style>
<div style="width: 800px; ">
<h4>Departmental Item Issuance</h4>
<a class="button" href="./?mod=item&act=issue">Create Item Issuance</a>
<a class="button" href="./?mod=item&act=issue_history">Issuance History</a>
<!--a class="button" href="./?mod=item&act=issue_return">Return Item</a-->
<a class="button" href="./?mod=item&sub=setting&act=option"> Options  </a> 
<form method="post">
<input type="hidden" name="items" id="items" value="">
<input type="hidden" name="iditems" id="iditems" value="">
<table  class="loan_table " cellpadding=2 cellspacing=1>
<tr>
    <td >
    <table width="100%" cellpadding=2 cellspacing=1 class="issue" >
    <tr>
        <th align="left" colspan=4>Select Items 
            <div class="foldtoggle"><a id="btn_select_item" rel="open" href="javascript:void(0)">&uarr;</a></div>
        </th>
    </tr>
    <tbody id="select_item">
       <tr valign="top" align="left" class="normal">
        <td align="left" width=100>Category</td>
        <td align="left">
            <?php echo build_category_combo('EQUIPMENT', $_cat, USERDEPT)?>
			<br><cite>* Category changing will remove selected items.</cite>
        </td>
        </tr>
       <tr valign="top" align="left" class="alt">
        <td align="left">Asset/Serial No</td>
        <td align="left" colspan=2>
            <input type="text" id="edit_item" name="serial_no" onKeyUp="suggest(this, this.value);" autocomplete="off" >
            <a href="javascript:void(0)" onclick="add_item()"><img class="icon" src="images/add.png"></a>
            <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
            </div>
        </td>
        </tr>
       <tr valign="top" align="left" class="normal">
        <td align="left" colspan=4>Item List</td>
        </tr>
       <tr valign="top" align="left" class="alt">
        <td align="left" colspan=4>
            <ul id="item_list" style="padding-left: 0px"></ul>
        </td>
        </tr>
        </tbody>
    </table>
    <table width="100%" cellpadding=2 cellspacing=1 class="issue" >
    <tr>
        <th align="left" colspan=4>Issued-Out To
            <div class="foldtoggle"><a id="btn_item_issuance" rel="open" href="javascript:void(0)">&uarr;</a></div>
        </th>
    </tr>
    <tbody id="item_issuance">    
      <tr valign="top">  
        <td align="left" width=100>Department</td>
        <td align="left">
            <select id="id_department" name="id_department">
            <?php echo build_option($department_list, $_dept); ?>   
            </select>
        </td>
        </tr>
      <tr valign="top">  
        <td align="left">Category</td>
        <td align="left" >
            <?php echo build_combo('dest_cat', get_category_list('EQUIPMENT', $_dept), $_cat)?>
        </td>
        </tr>  
        </tbody>
    </table>

    </td>
</tr>
<tr>
    <td>
    <table >
    <tr valign="middle">
        <th width="25%">&nbsp;</th>
        <th width="25%" align="center">Issued By</th>
        <th width="25%" align="center">Issued To</th>
    </tr>
    <tr valign="top">
        <td>Name</td>
        <td><?php echo $issue['issued_by']?></td>
        <td><select id="issuedto" name="issuedto"></select></td>
    </tr>
    <tr valign="top" class="alt">
        <td>Remarks</td>
        <td><textarea name="issue_remark" cols=26 rows=3></textarea></td>
        <td><textarea name="loan_remark" cols=26 rows=3></textarea></td>
    </tr>
    <tr valign="top">
        <td>Signatures</td>
        <td>
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
    <td align="right" valign="middle">
        <button type="button" onclick="return submit_issue()">Submit Issue</button>
    </td>
</tr>
</table>
<Input type="hidden" name="issue">
<Input type="hidden" name="issue_sign">
<Input type="hidden" name="loan_sign">
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
    $.post("loan/get_item.php", {asset_no: ""+asset_no+"", serial_no: ""+serial_no +""}, function(data){
        
        if(data.length >0) {
            var rows = data.split(',');
            var items = $('#items').val();
            var cnt = 0;
            if (items.length>0)
                cnt=items.split(',').length;
			else {
			var cols = rows[0].split('|');
				category=cols[8];// capture first category from first item
			}
			//alert(items)
            var text,cols ;
            for (var i=0; i<rows.length;i++){
                cols = rows[i].split('|');

				if (items == '') items = cols.join('|');
                else items += ',' + cols.join('|');
            }
            $('#items').val(items);
            display_list(items);
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
		text += '<tr><th>No</th><th>Serial No</th><th>Asset No</th><th>Category</th><th>Brand</th><th>Model</th><th width=20>Del</th></tr>';
        for (var i=0; i < recs.length; i++){
            cols = recs[i].split('|'); // asset_no|serial_no|id_item|cart|category|brand|model|loan_period|id_category|accessories-list
			var accessories_list = '';
			var acc = '';
			text += '<tr class="an_item" id="' + cols[1] + '"><td>' ;
            text += (i+1) + '. </td><td>' + cols[1] +  '</td><td>' + cols[0] + '</td>';
			text += '<td>'+cols[4]+'</td><td>'+cols[5]+'</td><td>'+cols[6]+'</td>';
            text += '<td><a onclick="del_item(\''+ recs[i] +'\')"><img class="icon" src="images/delete.png" alt="delete"></a></td></tr> ';
        }
		text += '</table>';
    } else
        text = '--- no item specified ---';
    $('#item_list').html(text);
}


/*
$('#id_department').change(function (){
    var d = $('#id_department').get(0);
    var did = d.options[d.selectedIndex].value;
    //$('#submit_'+sect).attr("disabled","disabled");
    $.post("./item/get_category_by_department.php", {queryString: ""+did+"",type: ""+sect+""}, function(data){
        
        if(data.length >0) {
            $('#id_category').empty();
            $('#id_category').append('<option value=0> -- select a category --</option>');
            $('#id_category').append(data);
            
            var c = document.getElementById('cat_'+sect);
            if ((c.options.length > 0)){
              //$('#submit_'+sect).removeAttr("disabled");
              {
                      for (var i=0; i<c.options.length; i++){
                        if (c.options[i].value == cat){
                            c.options[i].selected = true;
                        }
                    }
                  if (parseInt(cat==0)) c.selectedIndex = 0;                    
                } 
            } //else
              
        } else {
            $('#id_category').empty();
            $('#id_category').append('<option value=0> -- category is not available --</option>');
        }
    });
});
*/
function in_array(search, stack)
{
	for(var i=0; i<stack.length; i++)
		if (stack[i] == search)
			return true;
	return false;
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
/*
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

function display_list(items)
{
    var text = '';
    var cols = '';
    var recs = items.split(',');
    if (items != '' && recs.length > 0){
    if ($('#item_list').text() == '--- no item specified ---')
        $('#item_list').text('');
        for (var i=0; i < recs.length; i++){
            cols = recs[i].split('|'); // asset_no|serial_no|id_item
            text += '<li class="an_item" id="' + cols[1] + '">' ;
            text += '<a onclick="del_item(\''+ recs[i] +'\')"><img class="icon" src="images/delete.png" alt="delete"></a> ';
            text += '<a onclick="edit_item(\''+ recs[i] +'\')">' + (i+1) + '. ' + cols[1] +  ' (' + cols[0] + ')</a></li>';
        }
    } else
        text = '--- no item specified ---';
    $('#item_list').html(text);
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
        var cat = $('#id_category option:selected').val();
		$.post("item/suggest_item.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", catId: ""+cat+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			} else
                $('#suggestions').fadeOut();
		});
	}
}
*/
function fill(id, thisValue, onclick) 
{
	if (thisValue.length>0 && onclick){
		var cols = thisValue.split('|');
		$('#'+id).val(cols[1] + ', ' + cols[0] + ', ' + cols[2] + ', ' + cols[3] + ', ' + cols[4]);
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
        var category = $('#id_category option:selected').val();
        var pd = {queryString: ""+inputString+"", inputId: ""+me.id+"", deptId: ""+department+""};
        if (category>0) pd.catId =""+category+"";
        //alert(category)
		$.post("loan/suggest_item.php", pd, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			} else
                $('#suggestions').fadeOut();
		});
	}
}


function submit_issue()
{
    var frm = document.forms[0]
    var items_val = $('#items').val();
    /*
    if (frm.name.value == ''){
        alert('Please fill in Loan Out to!');
        return false;
    }
    if (frm.nric.value == ''){
        alert('Please fill in NRIC!');
        return false;
    }
    */
    if (items_val == ''){
        alert('Please add an Item!');
        return false;
    }
    var items = items_val.split(',');
    /*
    if (items.length != $('#quantity').val()){
        if (!confirm('Quantity required and Number of Inserted Items different. Continue?'))
            return false;
    }
    */
    if (isCanvasEmpty || isCanvas2Empty){
        alert('Please sign-in for issuer and requester!');
        return false;
    }
    var ok = confirm('Are you sure proceed this Issuance?');
    if (!ok)
        return false;
    
    var cvs = document.getElementById('imageView');
    frm.issue_sign.value = cvs.toDataURL("image/png");
    cvs = document.getElementById('imageView2');
    frm.loan_sign.value = cvs.toDataURL("image/png");    
    frm.issue.value = 1;
    frm.submit();
    return false;
}


//loaned_by_update(document.getElementById('refname'));
display_list($("#items").val());
$('#edit_item').focus();

$('#btn_item_issuance').click(function (e){
    toggle_fold(this);
});

$('#btn_select_item').click(function (e){
    toggle_fold(this);
});

$('#id_department').change(function(e){
    var dept = this.options[this.selectedIndex].value;
	
    $.post("item/get_category_by_department.php", {queryString: ""+dept}, function(data){
		if(data.length >0) {
            $('#dest_cat').html(data);
        }
		else $('#dest_cat').html('<option disabled>-- category is not available --</option>');
    });
    $.post("item/get_admin_by_department.php", {queryString: ""+dept}, function(data){
        if(data.length >0) {
            $('#issuedto').html(data);
        }
		else $('#issuedto').html('<option disabled>-- user is not available --</option>');
    });
});

$('#id_department').trigger('change');

$('#id_category').change(function (e){
	/*	
	if ($('#items').val()!=''){
		var ok = confirm('If you change category, all item in the list will be removed. Click Ok to continue!');
		if (!ok){
			var dd = $('#id_category').get(0);
			e.preventDefault();
		}
	}
	*/
    $('#items').val('');
    display_list('');
    current_category = $('#id_category').val();
});

</script>

</div>
