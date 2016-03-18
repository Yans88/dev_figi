<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

$query  = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, 
            department_name, date_format(issued_date, '".DATE_FORMAT."') as issued_date_format,
            date_format(date_of_purchase, '".DATE_FORMAT."') as date_of_purchase_format, location_name, 
            date_format(warranty_end_date, '".DATE_FORMAT."') as warranty_end_date_format,
            date_format(status_update, '".DATE_FORMAT." %H:%s') as last_update_format, status_defect as update_defect,
            contact_no_1, contact_no_2, contact_email_1, contact_email_2, full_name issued_to,item_store_type.*,
            (SELECT department_name FROM department d WHERE d.id_department = id_owner) owner_department 
            FROM item 
            LEFT JOIN brand ON item.id_brand=brand.id_brand 
            LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor  
            LEFT JOIN item_store_type ON item.id_store=item_store_type.id_store  
            LEFT JOIN status ON item.id_status=status.id_status
            LEFT JOIN category ON item.id_category=category.id_category 
            LEFT JOIN department ON item.id_department=department.id_department 
            LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
            LEFT JOIN user ON item.issued_to=user.id_user 
            LEFT JOIN location ON item.id_location=location.id_location  
            WHERE item.id_item = $_id";
$rs = mysql_query($query);
//echo mysql_error();
$data_item = mysql_fetch_array($rs);
if (empty($data_item['status_name']))
    $data_item['status_name'] = '-- not set --';
$location_list = get_location_list();
if (count($location_list) == 0)
    $location_list[0] = '--- no location available! ---';
// get spec
$spec_list = get_specification_list($data_item['id_category']);
$spec_item = get_item_spec($data_item['id_item']);
$category = get_category($data_item['id_category']);
$purchase_dt = strtotime($data_item['date_of_purchase']);
$condemned_date = null;
if ($purchase_dt > 25200){
    $condemn_dt = date_add_months($purchase_dt, $category['condemn_period']);
    $condemned_date = date('d-M-Y', $condemn_dt);
}
if ($data_item['date_of_purchase_format'] == '1-Jan-1970')
    $data_item['date_of_purchase_format'] = null;
if ($data_item['warranty_end_date_format'] == '1-Jan-1970')
    $data_item['warranty_end_date_format'] = null;
if ($data_item['issued_date_format'] == '1-Jan-1970')
    $data_item['issued_date_format'] = null;
?>
<script type="text/javascript" src="./js/jquery.opacityrollover.js"></script>
<script type="text/javascript" src="./js/slimbox2.js"></script>
<link rel="stylesheet" href="<?php echo STYLE_PATH?>slimbox2.css" type="text/css" media="screen" title="no title" charset="utf-8" />

<form method="POST">

<table cellspacing=1 cellpadding=2>
<tr><th colspan=3 style="color: white">Individual Item Detail Info</th></tr>
<tr valign="top">
  <td width="70%">
  <table width="100%" >
  <tr valign="top">
    <td width="50%">
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1>
        <tr><th colspan=2>Item Movement</th></tr>
        <tr>
          <td width=120>Item ID </td>
          <td><?php echo $data_item['id_item']?></td>
        </tr>
        <tr class="alt">
          <td>*Asset No. </td>
          <td><?php echo $data_item['asset_no']?></td>
        </tr>
      <tr>
        <td>*Manufacturer Serial Number </td>
        <td><?php echo $data_item['serial_no']?></td>
      </tr>
      <tr class="alt">
        <td>Category</td>
        <td><?php echo $data_item['category_name']?></td>
      </tr>
      <tr>
        <td>Location</td>
        <td><?php echo $data_item['location_name']?></td>
      </tr>
      <tr class="alt">
        <td>Issued To </td>
        <td><?php echo $data_item['issued_to']?></td>
      </tr>
      <tr>
        <td>Date Of issued </td>
        <td><?php echo $data_item['issued_date_format'] ?></td>
      </tr>
      <tr class="alt">
        <td>Department</td>
        <td><?php echo $data_item['department_name']?></td>
      </tr>
<?php
    if (defined('EQUIPMENT_OWNERSHIP') && EQUIPMENT_OWNERSHIP){
?>
      <tr class="normal">
        <td>Owner Dept.</td>
        <td><?php echo $data_item['owner_department']?></td>
      </tr>
<?php
    }
?>
      </table>
      <br/>
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1>
        <tr><th colspan=2>Item Vendor Detail</th></tr>
        <tr valign="top">
          <td width=120>Invoice/PO No </td>
          <td><?php echo $data_item['invoice']?>
        <?php
            $attachments = get_invoice_attachments($data_item['invoice']);
            //print_r($attachments);
            $active =  ' class="active" ';
            if (count($attachments) > 0){
                echo <<<ATTACH
              <table width="100%" class="itemlist attachments" cellpadding=2 cellspacing=1>
                <tr class="normal">
                  <td><label>Attachments:</label>
                  <div id="imagelist" class="content">
                    <div id="thumbs" class="navigation">
                    <ul class="attachments view" >
ATTACH;
            $row = 0;
              foreach ($attachments as $attachment){        
                $row++;
                  $href = './?mod=item&act=get_invoice_attachment&name=' . urlencode($attachment['filename']);
                  echo '<li id="tn'.$attachment['id_attach'].'"> '.$row . '. <a rel="lightbox" href="'.$href.'" targe="blank">';
                  echo $attachment['filename'].'</a></li>';
                  $active = null;
              }
              echo <<<ATTACH1
                    </ul></div></div>
                  </td>
                </tr>
              </table>
              <script type="text/javascript" charset="utf-8">
               // $('div.navigation').css({'width' : '300px', 'float' : 'left'});
                var onMouseOutOpacity = 0.67;
                        $('#thumbs ul.thumbs li').opacityrollover({
                            mouseOutOpacity:   onMouseOutOpacity,
                            mouseOverOpacity:  1.0,
                            fadeSpeed:         'fast',
                            exemptionSelector: '.selected'
                        });
            </script>            
ATTACH1;
          } 
        ?>

          </td>
        </tr>
        <tr class="alt">
          <td>Date of Purchase </td>
          <td><?php echo $data_item['date_of_purchase_format']?></td>
        </tr>
        <tr>
          <td>Warranty Period</td>
          <td><?php echo $data_item['warranty_periode']?></td>
        </tr>
        <tr class="alt">
          <td>Warranty End Date </td>
          <td><?php echo $data_item['warranty_end_date_format']?></td>
        </tr>
        <tr>
          <td>Vendor Name </td>
          <td><?php echo $data_item['vendor_name']?></td>
        </tr>
        <tr class="alt">
          <td>Contact No 1 </td>
          <td><?php echo $data_item['contact_no_1']?></td>
        </tr>
        <tr>
          <td>Contact Email 1</td>
          <td><?php echo $data_item['contact_email_1']?></td>
        </tr>
        <tr class="alt">
          <td>Contact No 2 </td>
          <td><?php echo $data_item['contact_no_2']?></td>
        </tr>
        <tr>
          <td>Contact Email 2 </td>
          <td><?php echo $data_item['contact_email_2']?></td>
        </tr>
      </table>    

      </td>
    <td width="1%">&nbsp;</td>
    <td width="49%">
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1>
        <tr><th colspan=2>Item Description</th></tr>
        <tr>
          <td width=120>Manufacturer</td>
          <td><?php echo $data_item['manufacturer_name']?></td>
        </tr>
        <tr class="alt">
          <td>Brand</td>
          <td><?php echo $data_item['brand_name']?></td>
        </tr>
      <tr>
        <td>Model Number </td>
        <td><?php echo $data_item['model_no']?></td>
      </tr>
	  <tr>
        <td>Hostname </td>
        <td><?php echo $data_item['hostname']?></td>
      </tr>
	  
      <tr class="alt" valign="top">
        <td>Brief description </td>
        <td><?php echo $data_item['brief']?></td>
      </tr>
      </table>
      <br/>
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1>
        <tr><th colspan=2>Item Condition</th></tr>
        <tr>
          <td width=120>Status</td>
          <td><?php echo $data_item['status_name']?></td>
        </tr>
        <tr class="alt">
          <td>Date of Last Status </td>
          <td><?php echo $data_item['last_update_format']?></td>
        </tr>
        <tr>
          <td>Defect Description </td>
          <td><?php echo $data_item['status_defect']?></td>
        </tr>
        <tr>
          <td>Projected date to be condemned </td>
          <td><?php echo $condemned_date?></td>
        </tr>
      </table>
      <br/>
      <table width="100%" class="itemlist" cellpadding=2 cellspacing=1>
        <tr><th colspan=2>Financial Info</th></tr>
        <tr>
          <td width=120>Item Cost</td>
          <td><?php echo $data_item['cost']?></td>
        </tr>
		<tr>
          <td width=120>Store Type</td>
          <td><?php echo $data_item['title'].' - '.$data_item['information']?></td>
        </tr>
      </table>     
      <br/>
      </td>
  </tr>
  <tr valign="top">
    <td colspan=4>
      <table class="itemlist" cellpadding=2 cellspacing=1 width="100%">
      <tr><th colspan=2>Barcode</th></tr>
      <tr><td>
      <input type="radio" name="barcodesrc" value="<?php echo $data_item['asset_no']?>" onclick="change_barcode(this.value)" checked>Asset No 
      </td><td>
      <input type="radio" name="barcodesrc" value="<?php echo $data_item['serial_no']?>" onclick="change_barcode(this.value)">Serial No
      </tr></td>
      <tr><td colspan=2><div class="barcode"><img src="" id="barcodeimg"></div></td></tr>
      </table>
	  <table class="itemlist" cellpadding=2 cellspacing=1 width="100%">
      <tr><th colspan=2>QR Code</th></tr>
      <tr><td>
      <tr><td colspan=2><div class="barcode"><img src="./qrcode.php?text=1&format=png&qrcode=<?php echo $data_item['asset_no']?>"></div></td></tr>
      </table>
      <br/>
      <table width="100%" class="itemlist pictures" cellpadding=2 cellspacing=1>
        <tr><th>Pictures</th></tr>
        <tr class="alt">
          <td >
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
          echo '<li><a class="thumb" href="'.$href.'" target="image_view" rel="lightbox" title="'.$pic_name.'">';
          echo '<img '.$active.' width='.$w.' height='.$h.' src="./?mod=item&act=show_image&thumb=1&id=' .$pic_id.'"></a></li>';
          $active = null;
      }
      echo '</ul>';
    } else
        echo '--picture is not available!--';
?>
            </div>
        </div>
        </td>
        </tr>
      </table>
      <br/>
    </td>
  </tr>
  <tr>
    <td colspan=4 align="center">
<?php
  if ($i_can_update ) {
?>
      <a class="button" href="./?mod=item&act=edit" >New Item</a>
      &nbsp;&nbsp;
<?php
    if ($data_item['id_status'] != CONDEMNED) {
?>
      <a class="button" href="./?mod=item&act=edit&id=<?php echo $_id?>" >Edit Item</a>
      &nbsp;&nbsp;
<?php
    }
    if (USERDEPT == $data_item['id_owner']){
        echo  '<a class="button" href="./?mod=item&act=issue&id='.$_id.'" >Issued to ..</a> &nbsp;&nbsp;';

    }
	if( !SUPERADMIN && (USERDEPT > 0)){
      $id_machine = check_machine_record($_id);
      if ($id_machine > 0){
        echo '<a class="button" href="./?mod=machrec&sub=machine&act=issue&id='.$id_machine.'">Send for Repair</a>&nbsp;&nbsp;';
      } else {
        echo '<a class="button" href="./?mod=machrec&sub=machine&act=info&by=asset_no&value='.$data_item['asset_no'].'">Create Machine Record</a>&nbsp;&nbsp;';
      }
	}
  } //i_can_update
?>
      <a class="button" href="./?mod=item&act=history&id=<?php echo $_id?>">Loan Record</a>
      &nbsp;&nbsp;
    </td>
  </tr>  
  </table>  
  </td>
<?php
if (count($spec_list)>0){
    echo <<<TABLE
  <td>
  <table  width="100%" cellspacing=1 cellpadding=2 class="itemlist">
    <tr height="25"><th colspan=2>&nbsp;Specifications</th></tr>
TABLE;

  $no = 0;
  foreach ($spec_list as $k => $v){
    $no++;
    $class_style = (($no % 2) == 0) ? 'class="alt"' : 'class="normal"';
    echo '<tr '.$class_style.'><td width=120>'.$v.'</td><td width=160>';
    if (array_key_exists ($k, $spec_item))
      echo $spec_item[$k];
    echo '</td></tr>';
  }
    echo '</table> </td>';
}
?>
    
   
  </tr>
</table>
</form>
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
     
    //var mw = imgpre.width;//$('#image').width();
    //var mh = imgpre.height;//$('#image').height();
    var mw = $('#imageholder').width();
    var mh = $('#imageholder').height();
    alert('ww: ' + ww + ', wh: ' + wh + ' -- mw: ' + mw + ', mh: ' + mh + ', iw: '+ img.width);
    $('#imageholder').css('left', 0);
    $('#imageholder').css('top', 0);
    
    $('#image').css('left', (ww-mw) /2);
    $('#image').css('top', (wh-mh) /2);
    $('#imageholder').show();
    $('#close').css('left', parseInt($('#image').css('left'))+6);
    $('#close').css('top', parseInt($('#image').css('top'))+6 );
    $('#close').focus();
    imgpre.onload = null;
}

function showFullImage(href)
{
    if (href == '') return;
    imgpre = new Image();
    imgpre.onload = RePosition;
    imgpre.src = href;    
}

function change_barcode(value)
{
    //$('#barcodeimg').src = "barcode.php?text=1&height=80&width=200&barcode="+value;
    var img = document.getElementById('barcodeimg');
    //img.src = "barcode.php?text=1&format=png&height=100&width=440&barcode="+value;
    img.src = "gb.php?text=1&format=png&barcode="+value;
}
change_barcode('<?php echo $data_item['asset_no']?>');

$('#barcodeimg').click(function(e){
    var href = e.target.src;
    var win = window.open(href, 'barcodeprint', 'menubar=no,toolbar=no,statusbar=no,width=400,height=60');
    //win.document.write('<p align="center"><img src="'+href+'"></p>');
    win.print();
    sleep(2000);
    win.close();   
});

</script>
