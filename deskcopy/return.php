<?php
require_once('util.php');

define('IN_RETURN', false);

$_items     = !empty($_POST['items']) ? $_POST['items'] : null;
$_input     = !empty($_POST['input']) ? $_POST['input'] : null;
$_id_user   = !empty($_POST['id_user']) ? $_POST['id_user'] : 0;

$items = array();
if ($_items != null)
    $items = explode(',', $_items);

$hidden_fields = null;
if ($_input != null) {       
    $item = get_deskcopy_title_by_serial($_input);   
    
    if (!empty($item['id_item']) && ($item['id_item'] > 0) ){
        if (!in_array($item['id_item'], $items) && $item['status'] == 'On Loan'){
            $items[] = $item['id_item'];
            //print_r($item);
            // update item status and return date 
            $query = "UPDATE deskcopy_loan_item SET  return_date = now() 
                        WHERE return_date IS NULL AND id_item = " . $item['id_item'];
            mysql_query($query);
            
            $query = "UPDATE deskcopy_item SET  status = 'Available for Loan' WHERE id_item = " . $item['id_item'];
            mysql_query($query);
            // update stock
            $query = "UPDATE deskcopy_stock dcs, deskcopy_item dci SET stock = stock + 1 
                        WHERE dci.id_item = $item[id_item] AND dcs.id_title = dci.id_title ";
            mysql_query($query);
        }
    }
}

$scanned_list = '<div id="itemspace"></div>';
if (count($items) > 0){
    /*
    // clean up for empty id item, deletion, duplication
    $tmp = array();
    foreach($items as $id_item){
        $id_item = trim($id_item);
        if (empty($id_item) || (($_del_id > 0) && ($_del_id == $id_item)) ||
            in_array($id_item, $tmp)) continue;
        $tmp[] = $id_item;
    }
    $items = $tmp;
    */
    // get item's info
    if (count($items) > 0){
        $rs = get_deskcopy_items($items);
        if (count($rs) > 0){
            $scanned_list  = '<table class="deskcopy_item_list" cellpadding=3 cellspacing=1>';
            $scanned_list .= '<tr><th width=30>No</th><th width=100>Serial No.</th><th width=80>Book No.</th>
                              <th width=430>Title</th><th width=120>Author</th>';
            $no = 1;
            foreach ($rs as $rec){
                $dellink = '<a class="button" href="javascript:void(0)" onclick="del_this(' . $rec['id_item'] . ')">x</a>';
                $scanned_list .= '<tr><td align="center">' . ($no++) . '.</td><td>' .
                                 $rec['serial_no'] . '</td><td>' . $rec['isbn'] . '</td><td>' . $rec['title'] . '</td><td>' .
                                 $rec['author_name'] . '</td><td>RETURNED</td></tr>';
            }                
            $scanned_list .= '</table><br/>';
        }
    }
}

?>
<div id="form">
<form method="post" id="loanform">
<input type="hidden" id="del_id" name="del_id" value="0">
<input type="hidden" id="items" name="items" value="<?php echo implode(',', $items)?>">
<input type="hidden" id="id_user" name="id_user" value="<?php echo $_id_user?>">
<div id="cmdlabel">&nbsp;<br/>
<?php
    echo $scanned_list;
    if (count($items) == 0)
        echo 'Please Scan Your Item';
    else
        echo 'Scan Another Item';
?>
    <br/>    
    </div>    
    <input type="text" id="input" name="input" class="inputbox" autocomplete="off" onkeyup="check_entry()">
</form>
</div>

<script>
var isbn_length = <?php echo ISBN_LENGTH?>;
var nric_length = <?php echo NRIC_LENGTH?>;
var serial_length = <?php echo SERIAL_LENGTH?>;

function clear_this()
{
    $('#items').val('');
    $('#id_user').val(0);
    $('form').submit();
}

function check_entry()
{
    var v = $('#input').val();
    if (v.length >= serial_length)
        $('form').submit();
}

$('.inputbox').focus();
</script>