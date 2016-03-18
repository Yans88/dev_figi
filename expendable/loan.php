<?php
require_once('util.php');

define('IN_LOAN', true);

$_input = isset($_POST['input']) ? $_POST['input'] : null;
$_nric = isset($_POST['nric']) ? $_POST['nric'] : null;
$_id_user = isset($_POST['id_user']) ? $_POST['id_user'] : 0;
$_del_id = isset($_POST['del_id']) ? $_POST['del_id'] : 0;
$_full_name = isset($_POST['full_name']) ? $_POST['full_name'] : null;
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_confirm = !empty($_POST['confirm']) ? ($_POST['confirm'] == 1) : false;

$items = explode(',', $_items);
if ($_confirm){
    $query = "INSERT INTO deskcopy_loan (loan_start, id_user)
                VALUES (now(), $_id_user)";
    mysql_query($query);
    if (mysql_affected_rows()>0){
        $id_loan = mysql_insert_id();
        // store loaned items
        $values = array();
        $stock_queries = array();
        foreach ($items as $id_item){
            $values[] = "($id_loan, $id_item)";
            $stock_queries[] = "UPDATE deskcopy_stock dcs, deskcopy_item dci SET stock = stock - 1 
                                WHERE dci.id_item = $id_item AND dcs.id_title = dci.id_title ";
        }
        if (count($values) > 0){
            $query  = 'INSERT INTO deskcopy_loan_item (id_loan, id_item) ';
            $query .= 'VALUES ' . implode(',', $values);
            mysql_query($query);
            //echo mysql_error();
        }
        // change loaned item's status 
        $query  = "UPDATE deskcopy_item SET status = 'On Loan' ";
        $query .= "WHERE id_item IN (" . implode(',', $items) . ")";
        mysql_query($query);
        // update stock
        foreach($stock_queries as $query)
            mysql_query($query);
        
        $_input = '';
        $_nric = '';
        $_id_user = '';
        $_items = '';
        $_full_name = '';
        $items = array();
    }
}

$hidden_fields = null;
if (($_nric == null) && ($_input !=null)){ // scan nric
    // check if nric is known
    include $figi_dir . 'user/user_util.php';
    $user = get_user_by_nric($_input);
    if (!empty($user['id_user'])){
        $_nric = $_input;
        $_id_user = $user['id_user'];
        $_full_name = $user['full_name'];
    }
} else if ($_nric != null){ // scan item
    $item = get_deskcopy_title_by_serial($_input, 'Available for Loan');
    
    if (!empty($item['id_item'])){
        if (!in_array($item['id_item'], $items))
            $items[] = $item['id_item'];
    }
}

$scanned_list = '<div id="itemspace"></div>';
if (count($items) > 0){
    // clean up for empty id item, deletion, duplication
    $tmp = array();
    foreach($items as $id_item){
        $id_item = trim($id_item);
        if (empty($id_item) || (($_del_id > 0) && ($_del_id == $id_item)) ||
            in_array($id_item, $tmp)) continue;
        $tmp[] = $id_item;
    }
    $items = $tmp;
    // get item's info
    $query  = 'SELECT dci.*, department_name, author_name, dct.*    
               FROM deskcopy_item dci 
               LEFT JOIN deskcopy_title dct ON dct.id_title = dci.id_title 
               LEFT JOIN deskcopy_author dca ON dca.id_author = dct.id_author 
               LEFT JOIN department ON dct.id_department = department.id_department 
               WHERE id_item IN (' . implode(',', $items) . ')';
    $rs = mysql_query($query);
    
    $no = 1;
    if ($rs && mysql_num_rows($rs)>0){
        $scanned_list  = '<table class="deskcopy_item_list" cellpadding=3 cellspacing=1>';
        $scanned_list .= '<tr><th width=30>No</th><th width=100>Serial No.</th><th width=80>Book No.</th>
                          <th width=430>Title</th><th width=120>Author</th>';
        while ($rec = mysql_fetch_assoc($rs)){
            $dellink = '<a class="button" href="javascript:void(0)" onclick="del_this(' . $rec['id_item'] . ')">x</a>';
            $scanned_list .= '<tr><td align="center">' . ($no++) . '.</td><td>' .
                             $rec['serial_no'] . '</td><td>' . $rec['isbn'] . '</td><td>' . $rec['title'] . '</td><td>' .
                             $rec['author_name'] . '</td><td>' . $dellink . '</td></tr>';
        }                
        $scanned_list .= '</table><br/>';
    }
}

?>
<div id="form">
<form method="post" id="loanform">
<input type="hidden" id="del_id" name="del_id" value="0">
<input type="hidden" id="confirm" name="confirm" value="0">
<input type="hidden" id="nric" name="nric" value="<?php echo $_nric?>">
<input type="hidden" id="items" name="items" value="<?php echo implode(',', $items)?>">
<input type="hidden" id="id_user" name="id_user" value="<?php echo $_id_user?>">
<input type="hidden" id="full_name" name="full_name" value="<?php echo $_full_name?>">
<?php
    if ($_confirm){
        echo $scanned_list.'<div id="cmdlabel">Thank you for using FiGi</div>';
    } else {
        if ($_id_user != 0) {
?>
    <div id="cmdlabel">Welcome <?php echo $_full_name?>,<br/>
<?php
    echo $scanned_list;
    if (count($items) == 0)
        echo 'You may scan your item now';
    else
        echo 'Scan another item';
?>
    <br/>    
    </div>
    
<?php 
        } else  { 
        echo $scanned_list;

?>
    <div id="cmdlabel">Please scan your NRIC</div>
    <br/>    
<?php 
        }
?>
    <input type="text" id="input" name="input" class="inputbox" autocomplete="off" onkeyup="check_entry()">
<?php
    } // not confirm
?>
</form>
</div>

<script>
var isbn_length = <?php echo ISBN_LENGTH?>;
var nric_length = <?php echo NRIC_LENGTH?>;
var serial_length = <?php echo SERIAL_LENGTH?>;

function del_this(id)
{
    $('#del_id').val(id);
    $('form').submit();
}

function cancel_this()
{
    if (confirm("Are you sure cancel this loan?")){
        new_loan();
    }
}

function new_loan()
{
    $('#nric').val('');
    $('#items').val('');
    $('#full_name').val('');
    $('#id_user').val(0);
    $('form').submit();
}

function confirm_this()
{
    //if (confirm("Are you sure confirm this loan?")){
        $('#confirm').val(1);
        $('form').submit();
    //}
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

$('.inputbox').focus();

</script>
