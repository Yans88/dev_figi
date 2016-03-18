<?php 
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_category = (!empty($_POST['id_category'])) ? $_POST['id_category'] : -1;
$dept = USERDEPT;
$msg = null;

if (!empty($_POST['submitcode'])){
    if (!empty($_POST['input'])){
        if ($_POST['searchBy'] == 'serial_no')
            $item_info = get_item_by_serial($_POST['input']);
        else
            $item_info = get_item_by_asset($_POST['input']);

        $machine = get_machrec_by_item($item_info['id_item']);
        ob_clean();
        if (!empty($machine['id_machine']))
            header('Location: ./?mod=machrec&sub=machine&act=view&id=' . $machine['id_machine']);
        else
            header('Location: ./?mod=machrec&sub=machine&act=info&by=' . $_POST['searchBy'] . '&value=' .  $_POST['input']);
        ob_end_flush();
        exit;                

    {
        /*
        $query = "SELECT id_item FROM item WHERE $_POST[searchBy] = '$_POST[input]'";
        $rs = mysql_query($query);
        if ($rs && mysql_num_rows($rs) > 0){
            $row = mysql_fetch_row($rs);
            $id_item = $row[0];
            // check if already registered as machine
            $id_machine = 0;
            $query = "SELECT id_machine FROM machine_info WHERE id_item = '$id_item' "; 
            $rs = mysql_query($query);
            if ($rs && mysql_num_rows($rs)){
                $row = mysql_fetch_row($rs);
                $id_machine = $row[0];
                $submitted = true;
            } else { // create if not exists
            
                $query = "INSERT INTO machine_info(id_item) VALUES ($id_item) "; 
                mysql_query($query);            
                if (mysql_affected_rows() > 0) {
                    $submitted = true;
                    $id_machine = mysql_insert_id();              
                }
            }
            if ($submitted && ($id_machine>0)){
                ob_clean();
                header('Location: ./?mod=machrec&sub=machine&act=issue&id=' . $id_machine);
                ob_end_flush();
                exit;                
            }
        }
        */
    }
    
    } else
        $msg = "Please put in a valid existing asset / serial number!";
}

$today  = time();
$today_str = date('j-M-Y H:i', $today);
$day_until = strtotime('+1 day', $today);
$day_until_str = date('j-M-Y H:i', $day_until);

?>

<style type="text/css">
  #start_date { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
  #end_date { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
    #findmachine .inputbox { font-size: 14px; }
</style>
<div id="create_machrec">
     <h2>Create New Machine Record</h2>
     <form method="post" id="form_machrec">
     <input type="hidden" name="submitcode" value="1">
<?php
    if ($_id == 0){
?>
<div id="findmachine">
    <br/> &nbsp; 
    Scan / Enter Machine(item) code:
    &nbsp;  <br/>   
    <input type="text" id="input" name="input" class="inputbox" autocomplete="off" 
    onKeyUp="suggest(this, this.value);" onBlur="fill('input', this.value);" >
   <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
    <br/>
    Find by:
    <input type="radio" name="searchBy" value="asset_no" checked> Asset No &nbsp;
   <input type="radio" name="searchBy" value="serial_no"> Serial No &nbsp;
    <br/>&nbsp;
    <br/>
        <button type='submit' name="create" id="create" >Create History Record</button>
    <script type="text/javascript">
    $(window).load(function(){$('#input').focus()});
    $('#create').click(function(){
        //alert($('<input type="radio" name="searchBy"').val())
        //location.href = './?mod=machrec&sub=machine&act=info&' +$('searchBy').val()+'='+$('#input').val();        
    });
    </script>
</div>
<?php
    } else  {
?>
     <table width="98%" class="itemlist" cellpadding=4 cellspacing=1 style="border: 1px solid #103821">
      <tr>
        <td width=130 align="left">Category</td>
        <td align="left">
		<select name="id_category" id="cat_machrec" >
		<?php 
			echo build_option(get_category_list('EQUIPMENT'), $_category)
			?>
		</select>
        </td>
      </tr>
      <tr class="alt">
        <td align="left">Period</td>
        <td align="left">
          <input type="text" size=20 id="start_date" name="start_date" value="<?php echo $today_str?>">
		  &nbsp;to&nbsp;
          <input type="text" size=20 id="end_date" name="end_date" value="<?php echo $day_until_str?>" >
		  <script>
				$('#start_date').AnyTime_picker({format: "%e-%b-%Y %H:%i "});
				$('#end_date').AnyTime_picker({format: "%e-%b-%Y "});
		</script>
  </td>
      </tr>
      <tr>
        <td align="left">Quantity</td>
        <td align="left"><input type="text" size=6 name="quantity" value=1></td>
      </tr>
      <tr>
        <td align="left">Requester</td>
        <td align="left">
        <!--
        <input type="text" name="requester" id="requester" autocomplete="off" size=30 
    onKeyUp="suggest(this, this.value);" onBlur="fill('requester', this.value);" >
   <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
    -->
    </td>
      </tr>
      <tr class="alt">
        <td align="left">Remarks / <br> Purpose of Use / <br> Special Requirements</td>
        <td align="left"><textarea rows=7 cols=63 name="remark">Walk-in request!
        </textarea></td>
      </tr>
      <tr>
        <td colspan=2 align="right"><button id="submit_machrec" type="button" onclick="submit_request(this.form)">Make Request</button></td>
      </tr>
    </table>
 <?php 
    } // id == 0
?>
     </form>
     &nbsp; <br/>
     <!--
     <div class="note"><?php echo @$messages['walkin_request_note']?>  </div>
     -->
  </div> 
<script type="text/javascript">
var isbn_length = <?php echo ISBN_LENGTH?>;
var nric_length = <?php echo NRIC_LENGTH?>;
var serial_length = <?php echo SERIAL_LENGTH?>;

function submit_request(frm)
{
    if (confirm("Are you sure make this request?")){
        frm.submitcode.value = 'submit';    
        frm.submit()
   }
}

function fill(id, thisValue) {
    $('#'+id).val(thisValue);
    setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
    var frm = document.forms[0];
    if(inputString.length == 0) {
        $('#suggestions').fadeOut();
    } else {
        var searchByRadio = $('input:radio[name=searchBy]');
        var searchBy = '';
        
        for (var i=0; i < searchByRadio.length; i++)
            if (searchByRadio[i].checked){
                searchBy = searchByRadio[i].value;
                break;
            }
        $.post("machrec/item_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", searchBy: ""+ searchBy +""}, function(data){
            if(data.length >0) {
                $('#suggestions').fadeIn();
                $('#suggestionsList').html(data);
                /*
                var pos =  $('#requester').offset();                       
                var w = $('#requester').width();
                var h = $('#requester').height();                                              
                $('#suggestions').css('position', 'absolute');
                $('#suggestions').offset({left:pos.left, top:pos.top + h + 5});
                $('#suggestions').width(w);
                */
            }
        });
    }
}

function check_entry()
{
    var v = $('#input').val();
    if ($('#nric').val() == ''){
        if (v.length >= nric_length)
            $('form').submit();
    } else {
        if (v.length >= serial_length)
            $('form').submit();    
    }
}


<?php
if ($msg != null)
    echo 'alert("'.$msg.'");';
?>
</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px;}
</style>
