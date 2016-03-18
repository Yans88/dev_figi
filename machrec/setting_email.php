<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}

$_email = (!empty($_GET['email'])) ?  $_GET['email'] : null;

if  (isset($_POST['addEmail'])){
    $_email = (!empty($_POST['email'])) ?  $_POST['email'] : null;
    $query = "SELECT count(*) FROM loan_email WHERE email = '$_email'";
    $rs = mysql_query($query);
    $rec = mysql_fetch_row($rs);
    if ($rec[0] == 0){
        $query = "INSERT INTO loan_email(email) VALUES('$_email')";
        mysql_query($query);
        if (mysql_affected_rows()>0)
            $_msg = 'Email has been added!';
    } else
        $_msg = 'Email is already exist!';
    echo <<<ALERT
<script>
    alert("$_msg");
    location.href="./?mod=loan&sub=setting&act=email&email=$_email";
</script>
ALERT;
    return;
} else if (isset($_POST['delEmail'])){
    $_email = (!empty($_POST['email'])) ?  $_POST['email'] : null;
    $query = "DELETE FROM loan_email WHERE email = '$_email'";
    mysql_query($query);
    if (mysql_affected_rows()>0)
        $_msg = "Email '$_email' has been deleted!";
    echo <<<ALERT1
<script>
    alert("$_msg");
    location.href="./?mod=loan&sub=setting&act=email&email=$_email";
</script>
ALERT1;
    return;
}

$data = get_email_tobe_notified();
?>
<br/>
<h4 style="color: #fff">Email Setting for Loan Notification</h4>
<?php
if ($i_can_update) { // admin
    $emails = get_email_list();
    $email_combo = build_combo('email', $emails, $_email);
    
    echo '<div style="width:300px">
            <form method="post">'.
            $email_combo . '&nbsp;<input type=submit name="addEmail" value="Add Email">
            </form>
            </div>';

} //can add
?>
<script>
function delete_email(email){
    var ok = confirm("Are you sure want to remove email '"+email+"'?");
    if (ok){
        var frm = document.getElementById("frmDel");
        frm.email.value = email;
        frm.submit();
        return false;

    }
    return false;
}
</script>
<form method="post" id="frmDel">
<input type="hidden" name="email" value="">
<input type="hidden" name="delEmail" value="1">
</form>
<table width="300" cellpadding=2 cellspacing=1 class="userlist" >
<tr height=30 valign="top">
  <th width=25>No</th>
  <th width=80>Email</th>
  <th width=80>Action</th>
</tr>
<?php
if (count($data)==0)
    echo '<tr><td colspan=3 align="center">No email set, notification will be disabled!</td></tr>';
else {
    $row = 1;
    foreach ($data as $email){
        $class = ($row % 2 == 0) ? ' class="alt"' : null;
        $link = '<a href="javascript:void(0)" onclick="delete_email(\''.$email.'\');">delete</a>';
        echo <<<ROW
<tr $class>
    <td align="center">$row.</td>
    <td>$email</td>
    <td align="center">$link</td>
</tr>
ROW;
        $row++;
    }
}
?>
