<h2 style="color:#FFFFFF">Search User</h2>
<form method="post" >
<table>
  <tr><td>	
    <div id="suggest">
      <input type="text" name="username"  id="kode" size="15"/>         
      <!--
      <input type="text" onKeyUp="suggest(this.value);" name="username"  onBlur="fill2();" id="kode" size="15"/>         
      <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>         
      </div>        
      -->
    </div></td>
    <td><input type="submit" name="search" value="search"></td>
  </tr>
</table>
</form>	
	
	
<?php	
if (isset($_POST['search'])){

echo <<<HTML
<table width="100%" cellpadding="2" cellspacing="2" class="userlist" >';
<tr height="20">
  <th width="130px">*Name</th>
  <th width="120px">*User Name</th>
  <th width="120px">Contact No.</th>
  <th>Email</th>
  <th width="100px">Category</th>
  <th width="80px">Loan History</th>
  <th width="80px">Action</th>
</tr>
HTML;

$query = "SELECT * FROM user WHERE username LIKE '%".$_POST['username']."%' ORDER BY id";
$rs = mysql_query($query);
$numrows = mysql_num_rows($rs);
if ($numrows>0){
  $warnaGenap = "#E5E5E5";
  $warnaGanjil = "#EAEAFF";
  $counter = 1;
  while ($queryusertable=mysql_fetch_array($rs)) {
    $warna = ($counter % 2 == 0) ? $warnaGenap : $warnaGanjil;
    echo <<<HTML
    <tr bgcolor="$warna">
      <td>$queryusertable[1]</td>
      <td>$queryusertable[2]</td>
      <td>$queryusertable[4]</td>
      <td>$queryusertable[5]</td>
      <td>$queryusertable[6]</td>
      <td align="center"><a href="?mod=user&act=loan&id=$queryusertable[id]">view</a></td>
      <td align="center">
        <a href="?mod=user&act=edit&id=$queryusertable[id]" >view</a> | 
        <a href="?mod=user&act=del&id=$queryusertable[id]" 
           onclick="return confirm('Are you sure you want to delete $queryusertable[2]?')">delete</a>
      </td>
    </tr>
HTML;
	} // while
}// if numrows
else	{
	echo '<tr><td colspan=7 align="Center">User "'.$_POST['username'].'" is not Availabe!</td></tr>';
	}
	echo "</table>";
}// if search
	
?>