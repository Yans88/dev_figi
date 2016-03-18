<?php

if (!defined('FIGIPASS')) exit;

//$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_what = isset($_GET['what']) ? $_GET['what'] : null;
if ($_what == null)
$_what = isset($_POST['what']) ? $_POST['what'] : null;

$_msg = null;
$_username = null;
$dept = USERDEPT;
?>

<h2>User Loan Record Search</h2>
<form method="post">
Search User Name: <input type="text" name="what" id="what" value="<?php echo $_what?>" autocomplete="off"  
    onKeyUp="suggest(this, this.value);" onBlur="fill('what', this.value);" >
   <input type="submit" name="search" value="Search...">
   <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
</form>
<br/>
<?php

if ($_what != null){
	$query = 'SELECT u.id_user, u.full_name, d.department_name 
				FROM user u 
				LEFT JOIN department d ON u.id_department = d.id_department 
				WHERE full_name like "%'.$_what.'%" 
				ORDER BY full_name';
	//user_name like "%' .$_what . '%" or 
	$res = mysql_query($query);
	//echo mysql_error().$query;
	if (mysql_num_rows($res) > 0) {
		echo <<<TABLE
<table cellpadding=2 cellspacing=1 class="userlist" width=500>
<tr>
	<th>Full Name</th>
	<th>Department</th>
	<th>Equipment Loan History</th>
	<th>Deskcopy Loan History</th>
</tr>
TABLE;
        $no = 1;
		while ($rec = mysql_fetch_assoc($res)){
            $class  = ($no % 2 == 0) ? ' class="alt"' : ' class="normal"';
			$department = !empty($rec['department_name']) ? $rec['department_name'] : 'n/a';
			echo <<<ROW
<tr $class>
	<td>$rec[full_name]</td>
	<td>$rec[department_name]</td>
	<td align="center"><a href="./?mod=user&act=loan&id=$rec[id_user]"><img class="icon" src="images/loupe.png"></a></td>
	<td align="center"><a href="./?mod=user&act=deskcopy_loan&id=$rec[id_user]"><img class="icon" src="images/loupe.png"></a></td>
</tr>
ROW;
		}
		echo '</table>';
		
	} else
		echo '<div class="error">Data is not available!</div>';
}
?>
<script>
function fill(id, thisValue) {
    $('#'+id).val(thisValue);
    setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
    var frm = document.forms[0];
    if(inputString.length == 0) {
        $('#suggestions').fadeOut();
    } else {
        $.post("user/user_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
            if(data.length >0) {
                $('#suggestions').fadeIn();
                $('#suggestionsList').html(data);
                var pos =  $('#what').offset();                       
                var w = $('#what').width();
                var h = $('#what').height();                                              
                $('#suggestions').css('position', 'absolute');
                $('#suggestions').offset({left:pos.left, top:pos.top + h + 5});
                $('#suggestions').width(w);
            }
        });
    }
}
$('#what').focus();
</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px;}
</style>
