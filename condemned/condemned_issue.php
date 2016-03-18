<?php
$default_condemned_reason = 2; // id from table condemned_reason

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}

$dept = USERDEPT;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_reasons = isset($_POST['reasons']) ? $_POST['reasons'] : null;

$_serialno = isset($_POST['serial_no']) ? $_POST['serial_no'] : null;
$_msg = null;
$today = date('j-M-Y H:i');
if (isset($_POST['itemlist'])){
    $status_list = implode(', ', array(AVAILABLE_FOR_LOAN, STORAGE));

    $items = $_POST['itemlist']; 
    $query = "SELECT asset_no, serial_no, id_item, brand_name, model_no, 
                date_format(date_of_purchase, '%d-%b-%Y') date_of_purchase  
                FROM item 
                LEFT JOIN brand ON brand.id_brand = item.id_brand 
                WHERE  id_item IN ($items)  
                AND id_status IN ($status_list)  AND id_owner = $dept";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    $_items = '';
    $items =  Array();
    $reasons =  Array();
    while ($rec = mysql_fetch_row($rs)){
        $items[] = "$rec[0]|$rec[1]|$rec[2]|$rec[5]|$rec[3]|$rec[4]"; 
        $reasons[] = $default_condemned_reason;
    }
    if (count($items)>0){
        $_items = implode($items, ',');
        $_reasons = implode($reasons, ',');
    }
}
//$issue = get_condemned_issue($_id);
$need_approval = REQUIRE_CONDEMNED_APPROVAL;
if (isset($_POST['issue']) && ($_POST['issue'] == 1)){    
    $items = get_item_from_serial_no($_items); // asset_no|serial_no,asset_no|serial_no,...
	$reasons = $_POST['reason'];
    if (count($items) > 0) { // selected item found
        // create condemned issue
        $issued_by = USERID;
        $issue_date = date('Y-m-d H:i:s').$this_time;
        $query = "INSERT INTO condemned_issue(issue_datetime, issue_remark, issue_status, issued_by) 
                  VALUES ('$issue_date', '$_POST[issue_remark]', 'PENDING', '$issued_by')";
        mysql_query($query);
        //echo $query.mysql_error();
        if (mysql_affected_rows()>0){
            $_id = mysql_insert_id();
            $values = array();
            $tobe_condemned_ids = array();
            $i = 0;
            foreach ($items as $id_item){
                if (preg_match('/^[0-9]+$/', $id_item) > 0){
                    $values[] = "($_id, $id_item, $reasons[$id_item])";
                    $tobe_condemned_ids[] = $id_item;
                }
                $i++;
            }
            
            // delete if existing items
            mysql_query("DELETE FROM condemned_item WHERE id_issue = $_id");
            if (count($values)>0){
                $query = "INSERT INTO condemned_item(id_issue, id_item, id_reason) VALUES " . implode(', ', $values);
                mysql_query($query);
                //echo mysql_error().$query;
                // set the item status to TO_BE_CONDEMNED
                $query = 'UPDATE item SET id_status = ' . TO_BE_CONDEMNED . ', status_update = now(), status_defect = "'.mysql_escape_string($_POST['issue_remark']).'" WHERE id_item IN (' . implode(', ', $tobe_condemned_ids) . ')';
                mysql_query($query);

            }
            // store signature
            $query = "REPLACE INTO condemned_signature(id_issue, issue_signature)
                        VALUES ($_id, '$_POST[signature]')";
            mysql_query($query);

            // goto view page
            goto_view($_id, PENDING);
        }
	} else $_msg = 'There is no item selected !';
}

$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

$issued_by = FULLNAME;
$issue_date = $today;

$reason_list = get_reason_list();

?>
<script type="text/javascript"  >
var reason_labels = new Array();
var reasonlabels = new Array();
<?php
    $idx = 0;
    foreach($reason_list as $k => $v){
        //echo "reason_labels[$k] = '$v';\n";
        echo "reasonlabels[$idx] = {key: $k, val:'$v'};\n";
        $idx++;
    }
    
?>
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

function condemned_by_update(out_to)
{
    var condemned_by = document.getElementById('condemnedby');
    condemned_by.innerHTML = out_to.value;
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


function get_item_info(asset_no, serial_no, reason)
{
    $.post("condemned/get_item.php", {asset_no: ""+asset_no+"", serial_no: ""+serial_no +""}, function(data){
        
        if(data.length >0) {
            var rows = data.split(',');
            var items = $('#items').val();
            var reasons = $('#reasons').val();
            var cnt = 0;
            if (items.length>0)
                cnt=items.split(',').length;
            var text,cols ;
            for (var i=0; i<rows.length;i++){
                cols = rows[i].split('|');
                /*
                cnt++;
                text = '<li class="an_item" id="' + cols[1] + '">' ;
                text += '<a onclick="del_item(\''+ rows[i]+'\')"><img class="icon" src="images/delete.png" alt="delete"></a> ';
                text += '<a onclick="edit_item(\''+ rows[i] +'\')">' + (cnt) + '. ' + cols[1] +  ' (' + cols[0] + ')</a></li>';
               // $('#item_list').append(text);
               */
                if (items == '') items = cols.join('|');
                else items += ',' + cols.join('|');
                if (reasons == '') reasons = reason;
                else reasons += ',' + reason;
            }
            $('#items').val(items);
            $('#reasons').val(reasons);
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

function createReasonSelect(name, id, selected)
{
    var select = '<select name="'+name+'" id="'+id+'" class="reasonoption">';
	var defsel = '';
    for(var i=0; i<reasonlabels.length; i++){
		defsel =  (reasonlabels[i].key==selected) ? ' selected' : '';
        select += '<option value="'+reasonlabels[i].key+'" '+defsel+'>'+reasonlabels[i].val+'</option>';
    }
    select += '</select>';
    return select;
}

function display_list(items)
{
    var text = '';
    var cols = '';
    var recs = items.split(',');
    var reason = '';
    var areason = -1;
    var rows = '';
    if (items != '' && recs.length > 0){
        var reasons = $('#reasons').val().split(',');
        if ($('#item_list').text() == '--- no item specified ---')
            $('#item_list').text('');
        for (var i=0; i < recs.length; i++){
            cols = recs[i].split('|'); // asset_no|serial_no|id_item
            reason = createReasonSelect('reason['+cols[2]+']', 'reason-'+i, reasons[i]);
            text = '<a href="javascript:void(0)" onclick="del_item(\''+ recs[i] +'\')"><label style="color: red; font-weight: bold">X</label></a> ';
            rows += '<tr><td align="right">'+(i+1)+'. '+text+'</td><td>'+cols[0]+'</td><td>'+cols[1]+'</td><td>'+cols[3];
            rows += '</td><td>'+cols[4]+'</td><td>'+cols[5]+'</td><td>'+reason+'</td></tr>';
        }
    } else
        text = '--- no item specified ---';
    //$('#item_list').html(text);
    var header = ' <tr><th width=30>No</th><th>Asset No</th><th>Serial No</th>';
    header += '<th width=80>Date of Purchase</th><th>Brand</th><th>Model No</th><th>Reason</th></tr>';
    $('#table_item_list').html(header+rows);
	$('.reasonoption').change(reasonchange);
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
		$.post("condemned/suggest_item.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", deptId: ""+department+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			} else
                        $('#suggestions').fadeOut();
		});
	}
}

</script>
<h4>Condemned Issuance</h4>
<form method="post">
<input type="hidden" name="signature" value="">
<input type="hidden" name="issue">
<input type="hidden" name="items" id="items" value="<?php echo $_items?>">
<input type="hidden" name="reasons" id="reasons" value="<?php echo $_reasons?>">
<input type="hidden" name="iditems" id="iditems" value="">
<table  class="condemned_table" cellpadding=2 cellspacing=1>
<tr valign="top">
    <td colspan=2>
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="top" align="left">
        <th align="left" width=135 >Prepared By</td>
        <th align="left" colspan=2><?php echo $issued_by ?></th>
      </tr>  
      <tr valign="top">  
        <td align="left">Date/Time of Issue</td>
        <td align="left"><?php echo $issue_date ?></td>
      <td align="left" rowspan=2 width=200>Signature:<br/>
       <div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Remarks on</td>
        <td align="left"><textarea cols=50 rows=4 name="issue_remark" id="issue_remark"></textarea></td>    
      </tr>
    </table>
    <br/>
    </td>
</tr>
<tr><td colspan=2><strong>List of Items to be condemned:   </strong><br/>
    <table width="100%" cellpadding=3 cellspacing=1 id="table_item_list" style="font-size:8pt">
    </table>
    &nbsp; <br/>
</td></tr>
<tr>
    <td width=135>Asset no/Serial no</td>
    <td>
        <input type="text" id="edit_item" name="serial_no" onKeyUp="suggest(this, this.value);" size=50 autocomplete="off" >
        <a href="javascript:void(0)" id="btn_add_item"><img class="icon" src="images/add.png"></a>
        <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;"> 
            <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
            <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
        </div>
    </td>
</tr>
<!--
<tr>
    <td>Reason</td>
    <td>
        <?php  echo build_combo('id_reason', $reason_list); ?>
    </td>
</tr>
-->
<tr>
    <td colspan=2 valign="middle" align="right">
    <a class="button" href="./?mod=condemned" id="a_cancel">Cancel</a> &nbsp;
    <a class="button" href="javascript:void(0)" id="a_submit">Submit</a>&nbsp;
    </td>
</tr>
</table>
</form>
<br/><br/>
<script type="text/javascript" src="./js/signature.js"></script>
<script type="text/javascript">
display_list($("#items").val());
$('#edit_item').focus();
$('#a_submit').click(function(){
    var frm = document.forms[0]
    var items_val = $('#items').val();
    if ($('#issue_remark').val() == ''){
        alert('Put some remark for this condemnation!');
        return false;
    }
    if (items_val == ''){
        alert('Please add Item to be condemned!');
        return false;
    }
    if (isCanvasEmpty){
        alert('Please put signature to proceed!');
        return false;
    }

    var ok = confirm('Are you sure proceed this Condemned Recommendation ?');
    if (!ok)
        return false;
    
    var cvs = document.getElementById('imageView');
    document.forms[0].signature.value = cvs.toDataURL("image/png");
    frm.issue.value = 1;
    frm.submit();
    return false;
});

$('#btn_add_item').click(function (e){
        var item = $('#edit_item').val();
        if (item == '') return;
        var items = $('#items').val();
        var cols = item.match(/([^ ].+) *\((.+)\)/);
        if ((cols.length > 2) &&  (items.search(new RegExp(cols[1]+'|'+cols[2])) == -1)){
            get_item_info(cols[1], cols[2], 1);//$('#id_reason').val()
            $('#edit_item').val('');
            $('#edit_item').focus();
        } else
            alert('Item already exists in the list. Put another item!');
});


function reasonchange(){
	var idx = this.id.substr(7);
    var reasons = $('#reasons').val().split(',');
	reasons[idx] = $(this).val();
	$('#reasons').val(reasons.join(','));
}

$('.reasonoption').change(reasonchange);

</script>
