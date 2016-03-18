<?php 

require_once('imageresize.php');

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$dept = USERDEPT;
$department_list = get_department_list();
$statuses = get_status_list();

if (isset($_POST['save']) && $_POST['save'] == 1) {
	//print_r($_POST);
    $_id = save_item($_id, $_POST);

	if ($_id > 0){
		echo '<script>location.href="./?mod=item&act=view&id='.$_id.'"</script>';
		return;
	}
}
$spec_list = get_specification_list();
if ($_id > 0) {
  $query  = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, 
             department_name, date_format(issued_date, '".DATE_FORMAT."') as issued_date_format,
             date_format(date_of_purchase, '".DATE_FORMAT."') as date_of_purchase_format, 
             date_format(warranty_end_date, '".DATE_FORMAT."') as warranty_end_date_format,
             date_format(status_update, '".DATE_FORMAT." %H:%s') as last_update_format, status_defect,
			 contact_no_1, contact_no_2, contact_email_1, contact_email_2, category.id_department, brand.id_manufacturer  			 
             FROM item 
             LEFT JOIN brand ON item.id_brand=brand.id_brand 
             LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
             LEFT JOIN status ON item.id_status=status.id_status
             LEFT JOIN category ON item.id_category=category.id_category 
             LEFT JOIN department ON category.id_department=department.id_department 
             LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer
             
             WHERE item.id_item = $_id";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    $data_item = mysql_fetch_array($rs);
    if ($data_item['id_status'] == CONDEMNED){
		echo '<script>location.href="./?mod=condemned&act=view&item='.$_id.'"</script>';
		return;
    }
  
} 
else {

  $data_item['id_item'] = '0';
  $data_item['asset_no'] = '';
	if (AUTO_GENERATED_ASSETNO)
  $data_item['asset_no'] = '-- Auto Generated --';
  $data_item['serial_no'] = '';
  $data_item['issued_to'] = '1';
  $data_item['issued_date'] = date('m/d/Y');
  $data_item['id_category'] = '0';
  $data_item['id_vendor'] = '0';
  $data_item['id_manufacturer'] = '0';
  $data_item['id_location'] = '';
  $data_item['model_no'] = '';
  $data_item['brief'] = '';
  $data_item['cost'] = '';
  $data_item['invoice'] = '';
  $data_item['date_of_purchase'] = date('m/d/Y');
  $data_item['warranty_periode'] = '0';
  $data_item['warranty_end_date'] = date('m/d/Y');
  $data_item['id_department'] = '0';
  $data_item['id_brand'] = '0';
  $data_item['id_status'] = '0';
  $data_item['status_name'] = 'Issued';
  $data_item['last_update'] = date('m/d/Y');
  $data_item['status_defect'] = '';
  $data_item['contact1_number'] = '';
  $data_item['contact1_email'] = '';
  $data_item['contact2_number'] = '';
  $data_item['contact2_email'] = '';
  $data_item['issued_date_format'] = '';
  $data_item['date_of_purchase_format'] = '';
  $data_item['warranty_end_date_format'] = '';
  $data_item['last_update_format'] = date('d-M-Y H:i');
  $data_item['id_store'] = 0;
  $data_item['hostname'] = '';
  
  //foreach ($spec_list as $k => $v)
   // $specs[$k] = '';
}
   
$location_list = get_location_list();
if (count($location_list) == 0)
	$location_list[0] = '--- no location available! ---';

$spec_list = get_specification_list($data_item['id_category']);
$spec_item = get_item_spec($data_item['id_item']);
$store_list = get_store_list();
$store_all = get_store();
$caption  = ($_id > 0) ? 'Edit Item'  : 'Add New Item';

?>
<style type="text/css">
  #issued_date { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
  #date_of_purchase { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
  #warranty_end_date { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
</style>

<script type="text/javascript">
 function save_item(){
  var frm = document.forms[0]
  frm.save.value = 1;
  frm.submit();
 }
 
function fill_loc(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("item/suggest_location.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			}
		});
	}
}

function cancel_it()
{
    location.href='./?mod=item';
}

function new_item()
{
    location.href='./?mod=item&act=edit';
}

function view_log()
{
    location.href="./?mod=item&act=history&id=<?php echo $_id?>";
}

function delete_it()
{
    ok = confirm('Are you sure you want to delete <?php echo $data_item['asset_no']?>?');
    if (ok) 
        location.href="./?mod=item&act=del&id=<?php echo $_id?>";     
}

function calculate_warranty()
{
    var dConv = new AnyTime.Converter({format:'%e-%b-%Y'});
    var val = parseInt($('#warranty_periode').val());
    var startdate = new Date(dConv.parse($('#date_of_purchase').val()).getTime());
    var enddate = startdate;
    var months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    enddate.setMonth(enddate.getMonth()+val);
    
    $('#warranty_end_date').val(enddate.getDate()+'-'+months[enddate.getMonth()]+'-'+enddate.getFullYear());
}

</script>
<script type="text/javascript" src="./js/jquery.opacityrollover.js"></script>
<script type="text/javascript" src="./js/slimbox2.js"></script>
<script type="text/javascript" src='./js/jquery.MultiFile.js' language="javascript"></script>
<link rel="stylesheet" href="<?php echo STYLE_PATH?>slimbox2.css" type="text/css" media="screen" title="no title" charset="utf-8" />

<br/>
<form method="POST" id="telo" enctype="multipart/form-data">
<input type="hidden" name="save" value=0>
<input type="hidden" name="deleted_images" id="deleted_images" value=''>
<input type="hidden" name="deleted_attachments" id="deleted_attachments" value=''>

<table cellspacing=1 cellpadding=2 id="itemedit">
<tr><th colspan=4><?php echo $caption?></th></tr>
<tr valign="top">
    <td width=330>
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1 >
        <tr><th colspan=2>Item Movement</th></tr>
        <!--
        <tr>
          <td width=120>Item ID </td>
          <td><?php echo $data_item['id_item']?></td>
        </tr>
        -->
        <tr class="alt">
          <td>Asset No. </td>
          <td><input type="text" name="asset_no" value="<?php echo $data_item['asset_no']?>" size=25 
		<?php 
			if (AUTO_GENERATED_ASSETNO)  echo ' readonly '; ?>  ></td>
        </tr>
      <tr>
        <td>Manufacturer Serial Number </td>
        <td><input type="text" name="serial_no" value="<?php echo $data_item['serial_no']?>" size=25></td>
      </tr>
      <tr class="alt">
        <td>Category</td>
        <td><?php echo build_category_combo('EQUIPMENT', $data_item['id_category'], $dept)?></td>
      </tr>
      <tr>
        <td>Location</td>
        <td>
			<select name="id_location" id="id_location">
			<?php echo build_option($location_list, $data_item['id_location']);?>
			</select>
			<?php
			/*
			<input type="text" id="location" name="location" value="<?php echo $data_item['location']?>"
			 onKeyUp="suggest(this, this.value);" onBlur="fill_loc('location', this.value);" >
			<div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;"> 
				<div class="suggestionList" id="suggestionsList"> &nbsp; </div>
			</div>
			*/
			?>
		</td>
      </tr>
      <tr class="alt">
        <td>Issued To </td>
        <td><?php echo build_user_combo($data_item['issued_to'], 'issued_to')?></td>
      </tr>
      <tr>
        <td>Date Of issued </td>
        <td>
          <input type="text" id="issued_date" name="issued_date" size=14 value="<?php echo $data_item['issued_date_format']?>">
		  <script>$('#issued_date').AnyTime_picker({format: "%e-%b-%Y"});</script>
        </td>
      </tr>
      <tr class="alt">
        <td>Department</td>
        <td><?php echo isset($department_list[$dept]) ? $department_list[$dept] : null?></td>
      </tr>
      </table>
      <br/>
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1>
        <tr><th colspan=2>Item Vendor Detail</th></tr>
        <tr valign="top">
          <td width=120>Invoice/Po No </td>
          <td><input type="text" name="invoice" value="<?php echo $data_item['invoice']?>">
      <br/>
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1>
        <tr><th>Attachments</th></tr>
        <tr class="normal" >
          <td>
          <div id="imagelist" class="content">
            <div id="thumbs" class="navigation">
<?php
    $attachments = array();
    if ($_id > 0)
        $attachments = get_invoice_attachments($data_item['invoice']);
    $active =  ' class="active" ';
    if (count($attachments) > 0){
      echo '<ul class="attachments" >';
      foreach ($attachments as $attachment){        
          $href = './?mod=item&act=get_invoice_attachment&name=' .urlencode($attachment['filename']);
          echo '<li id="att'.$attachment['id_attach'].'"><a href="javascript:void(0)" onclick="delete_attacment('.$attachment['id_attach'].')"><img src="images/delete.png"></a> <a href="'.$href.'" rel="lightbox" >';
          echo $attachment['filename'].'</a></li>';
          $active = null;
      }
      echo '</ul>';
    } else
        echo '--attachment is not available!--';
?>
            </div>
        </div>
        <div class="clear"></div>
      <br/>
        Add attachment, click button below: <input type="file" id="fattachment1" name="fattachment[]" class="multi max-5 accept-gif|jpg|jpeg|png|pdf|xls|doc|ppt|xlsx|docx|pptx" >
        <div id="fattachment-list"></div>
        <script type="text/javascript" language="javascript">
        $(function(){ // wait for document to load 
         $('#fattachment').MultiFile({ 
          list: '#fattachment-list'
         }); 
        });
    </script>        
          </td>
        </tr>
      </table>  
          </td>
        </tr>
        <tr class="alt">
          <td>Date of Purchase </td>
          <td>
            <input type="text" id="date_of_purchase" name="date_of_purchase" size=14 value="<?php echo $data_item['date_of_purchase_format']?>"  >
            <script>$('#date_of_purchase').AnyTime_picker({format: "%e-%b-%Y"});</script>
          </td>
        </tr>
        <tr>
          <td>Warranty Period</td>
          <td><input type="text" name="warranty_periode" id="warranty_periode" value="<?php echo $data_item['warranty_periode']?>" size=6 onkeyup="calculate_warranty()"> month(s)</td>
        </tr>
        <tr class="alt">
          <td>Warranty End Date </td>
          <td>
            <input type="text" id="warranty_end_date" name="warranty_end_date" size=14 value="<?php echo $data_item['warranty_end_date_format']?>">
			<script>$('#warranty_end_date').AnyTime_picker({format: "%e-%b-%Y"});</script>
          </td>
        </tr>
        <tr>
          <td>Vendor Name </td>
          <td><?php echo build_vendor_combo($data_item['id_vendor'])?></td>
        </tr>
      </table>      

    </td>
    <td width=5>&nbsp;</td>
    <td width=330>
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1>
        <tr><th colspan=2>Item Description</th></tr>
        <tr>
          <td width=120>Manufacturer</td>
          <td><?php echo build_manufacturer_combo($data_item['id_manufacturer'])?></td>
        </tr>
        <tr class="alt">
          <td>Brand</td>
          <td><?php echo build_brand_combo($data_item['id_manufacturer'], $data_item['id_brand'])?></td>
        </tr>
      <tr>
        <td>Model Number </td>
        <td><input type="text" name="model_no" value="<?php echo $data_item['model_no']?>"></td>
      </tr>
	  <tr>
        <td>Hostname</td>
        <td><input type="text" name="hostname" value="<?php echo $data_item['hostname']?>"></td>
      </tr>
      <tr class="alt" valign="top">
        <td>Brief description </td>
        <td><textarea name="brief" rows=2 cols=20><?php echo $data_item['brief']?></textarea></td>
      </tr>
      </table>

      <br/>
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1>
        <tr><th colspan=2>Item Condition</th></tr>
        <tr>
          <td width=120>Status</td>
          <td>
          <?php
          /*
            echo  $data_item['status_name'];
            */
        ?>
          <select name="id_status">
                <option value=0>-- not set --</option>
            <?php echo build_option($statuses, $data_item['id_status'])?>
                </select>
          </td>
        </tr>
        <tr class="alt">
          <td>Date of Last Status </td>
          <td>
            <!-- <input type="text" name="last_update" size=12 value="<?php echo $data_item['last_update_format']?>"> -->
            <?php echo $data_item['last_update_format']?>
          </td>
        </tr>
        <tr valign="top">
          <td>Defect Description </td>
          <td>
            <textarea name="status_defect" rows=3 cols=20>
            <?php echo $data_item['status_defect']?>
            </textarea>
            </td>
        </tr>
      </table>
      <br/>
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1>
        <tr><th colspan=2>Financial Info</th></tr>
        <tr>
          <td width=120>Item Cost</td>
          <td><input type="text" name="cost" onBlur="set_store(this.value)" value="<?php echo $data_item['cost']?>">*numeric only</td>
        </tr>
		 <tr>
          <td width=120>Store Type</td>
          <td><?php echo build_store_combo($data_item['id_store'])?></td>
        </tr>
      </table>
     </td>
<?php
if (count($spec_list)>0){
    echo <<<TABLE
  <td rowspan=2>
    <table  width="100%" cellspacing="2" cellpadding="2" class="itemlist">
    <tr height="25"><th align="left" colspan=2>&nbsp;Specifications</th></tr>
TABLE;
	$no = 0;
    foreach ($spec_list as $k => $v){
	  $no++;
      $idx = str_replace(' ', '_', $v);
	  $class_style = (($no % 2) == 0) ? 'class="alt"' : 'class="normal"';
      echo '<tr '.$class_style.'><td width=140>'.$v.'</td><td width=160><input type="text" name="'.$idx.'" value="';
        if (array_key_exists ($k, $spec_item))
            echo $spec_item[$k];
        echo '"> </td></tr>';
    }
    echo '</table></td>';
} 
?>
    
  </tr>
    <tr valign="top"><td colspan=3>
      <table width="100%" class="itemlist pictures" cellpadding=2 cellspacing=1>
        <tr><th>Pictures</th></tr>
        <tr class="normal">
          <td>
          <div id="imagelist" class="content">
            <div id="thumbs" class="navigation">
<?php
    $pics = get_pictures($_id);
    $w = THUMB_WIDTH;
    $h = THUMB_HEIGHT;
    $active =  ' class="active" ';
    if (count($pics) > 0){
      echo '<ul class="thumbs" >';
      foreach ($pics as $pic_id =>  $pic_name){        
          $href = './?mod=item&act=show_image&name=' .$pic_name;
          echo '<li id="tn'.$pic_id.'"><a class="thumb" href="'.$href.'" target="image_view" rel="lightbox" title="'.$pic_name.'">';
          echo '<img '.$active.' width='.$w.' height='.$h.' src="./?mod=item&act=show_image&thumb=1&id=' .$pic_id.'"></a>';
          echo '<a href="javascript:void(0)" class="delete" onclick="delete_image('.$pic_id.')"><img src="images/delete.png"></a></li>';
          $active = null;
      }
      echo '</ul>';
    } else
        echo '--picture is not available!--';
?>
            </div>
        </div>
        <div class="clear"></div>
      <br/>
        Add picture, click button below:  <input type="file" id="fimage" name="fimage[]" class="multi max-3 accept-gif|jpg|jpeg|png" >
        <div id="fimage-list"></div>
        <script type="text/javascript" language="javascript">
        $(function(){ // wait for document to load 
         $('#fimage').MultiFile({ 
          list: '#fimage-list'
         }); 
        });
        //$('div.navigation').css({'width' : '300px', 'float' : 'left'});
    </script>        
          </td>
        </tr>
      </table>     

  </td></tr>
  <tr><td colspan=4>&nbsp;</td></tr>
  <tr>
    <td colspan=4 align="center">
      <button type="button" onclick="save_item();return false" >Save Item</button> &nbsp;&nbsp;
      <button type="reset" >Reset</button> &nbsp;&nbsp;
      <button type="button" onclick="cancel_it()">Cancel</button>
<?php if ($_id > 0) { ?>
        <!--
      &nbsp;&nbsp;&nbsp;&nbsp;
      <button type="button" onclick="delete_it()">Delete Item</button>
      -->
      &nbsp;&nbsp;&nbsp;&nbsp;
      <button type="button" onclick="view_log()">Loan Record</button>
      &nbsp;&nbsp; &nbsp;&nbsp;
      <button type="button" onclick="new_item()">New Item</button>            
<?php } ?>
    </td>
  </tr>  
</table>
</form>
<br/>
<br/>
<div id="imageholder">
    <img id="image" src="" >
    <button id="close" type="button" onclick="HideImage()">X</button>
</div>

<script>
var imgpre 
function HideImage()
{
    $('#imageholder').hide();
    $('#imagelist').focus();
    
}

function RePosition()
{
    var img = document.getElementById('image');
    img.src = imgpre.src;
	var ww = $(window).width();
	var wh = $(window).height();
    
	var mw = $('#imageholder').width();
	var mh = $('#imageholder').height();
    //alert('ww: ' + ww + ', wh: ' + wh + ' -- mw: ' + mw + ', mh: ' + mh + ', iw: '+ img.width);
    $('#imageholder').css('left', 0);
    $('#imageholder').css('top', 0);
    alert(mw +','+mh)
    $('#image').css('left', (ww-mw) /2);
    $('#image').css('top', (wh-mh) /2);
    $('#imageholder').show();
    $('#close').css('left', parseInt($('#image').css('left'))+6);
    $('#close').css('top', parseInt($('#image').css('top'))+6 );
    $('#close').focus();
    if (imgpre)
      imgpre.onload = null;
}

function showFullImage(href)
{
	if (href == '') return;
    imgpre = new Image();
    imgpre.onload = RePosition;
    imgpre.src = href;    
}

function delete_image(id)
{
  var di = $('#deleted_images').val();
  if (di != '') di += ',';
  di += id;
  $('#deleted_images').val(di);
  //hide the thumbnail
  $('#tn'+id).hide();
}

function delete_attacment(id)
{
  var di = $('#deleted_attachments').val();
  if (di != '') di += ',';
  di += id;
  $('#deleted_attachments').val(di);
  //hide from the list
  $('#att'+id).hide();
}

function change_fimage(me)
{
  $('#preview').src = 'file://' +me.value;
}
function set_store(value){
	var val = parseInt(value);
	<?php 
	$i = 0;
	foreach($store_all as $row){
	$min = "val>".$row['min_value'];
	$max = ($row['max_value']>1) ? " && val<".$row['max_value'] : "";
	$ifs = ($i > 0) ? 'else if' : 'if';
	$string = $ifs.'('.$min.$max.'){$(\'#id_store option[value="'.$row['id_store'].'"]\').attr("selected", "selected");exit;}';
	echo $string;
	}?>
}
</script>
