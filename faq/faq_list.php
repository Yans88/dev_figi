<script>
function toggle_fold_faq(obj){	
    var rel = obj.rel;
    var dataid = obj.id.toString().substr(4);	
    if (rel == 'open'){
        $('#'+dataid).hide();
        obj.rel = "close";
        obj.innerHTML = "&darr;";
    } else {
        $('#'+dataid).show();
        obj.rel = "open";
        obj.innerHTML = "&uarr;";
    }
}
</script>
<style>
.add_faq{ float:right}
</style>
<?php
echo '<h2>FAQ</h2>';
if(SUPERADMIN){
	echo '<a class="button add_faq" href="./?mod=faq&act=edit">Add FAQ</a>';
}
$faq_list = get_faq_list();
while($rec = mysql_fetch_assoc($faq_list)){	
	?>

<table width="100%" class="itemlist loan issue" >
	<thead>
      <tr >
        <th height=30 style="text-align:left;">&nbsp;<?php echo strip_tags($rec['question']);?>
            <div class="foldtoggle"><a id="<?php echo 'btn_faq_'.$rec['id_faq'];?>" class="btn_loan_request" rel="close" href="javascript:void(0)">&uarr;</a></div>
         
        </th>
      </tr>  
	</thead>
      <tbody id="<?php echo 'faq_'.$rec['id_faq'];?>" class="loan_request">
      <tr  class="alt">
        <td align="left" style="padding-left:10px;">
			<?php echo $rec['answer'];?>
		</td>
       
      </tr>  
	  <?php if(SUPERADMIN) {?>
      <tr  class="alt">
        <td align="right" width=100 >
		
			<a class="button edit_faq" href="./?mod=faq&act=edit&id=<?php echo $rec['id_faq'];?>">Edit FAQ</a>
			<a class="button edit_faq" href="./?mod=faq&act=del&id=<?php echo $rec['id_faq'];?>" onclick="return confirm('Are you sure delete this FAQ ?')" title="delete">Delete FAQ</a>
		</td>       
      </tr>  
	  <?php } ?>
      </tbody>
 </table><br/>


<?php } ?>

<script>
	$('.loan_request').hide();
    $('.btn_loan_request').click(function (e){
        toggle_fold_faq(this);
    });
</script>