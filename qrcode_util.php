<?php
	require_once('../tcpdf/phpqrcode/qrlib.php'); 
	require_once('./item/item_util.php');
	require_once('./common.php');
	
	function get_qrcode($text,$field){
		
		$item = get_item_by_asset($text);
		// $_qrcode = "http://demo.figi.sg/?mod=item&act=view&id=$item[id_item]";
		$_qrcode = "";
		$ff = explode(',',$field);
		foreach($ff as $row){
			$_qrcode .= $item[$row].',';
		}
		
		// outputs image directly into browser, as PNG stream 
		QRcode::png($_qrcode,null,QR_ECLEVEL_L,4);
	}
	function get_qrcode_stream($text,$field){
		$item = get_item_by_asset($text);
		// $_qrcode = "http://demo.figi.sg/?mod=item&act=view&id=$item[id_item]";
		$_qrcode = "";
		$ff = explode(',',$field);
		foreach($ff as $row){
			$_qrcode .= $item[$row].',';
		}
		ob_start();
		QRCode::png($_qrcode, null);
		$imageString = base64_encode( ob_get_contents() );
		ob_end_clean();
		return $imageString;
	}
	function create_qrcode_sheet($data, $col = 2, $space = 20,$field)
	{
		if (!is_array($data) || empty($data))
			return null;
		$c = 0;
		$im = null;
		
		foreach ($data as $code){
			$bc = get_qrcode_stream($code,$field);
			$dat = base64_decode($bc);
			$bc = imagecreatefromstring($dat);
			$w = imagesx($bc);
			$h = imagesy($bc);
			if($im == null){
				$r = ceil(count($data) / $col);
            
				$im = imagecreate(($w + $space) * $col,($h + $space) * $r);
				$bg = imagecolorallocate ($im, 255, 255, 255);
				$fg = imagecolorallocate ($im, 0, 0, 0);
				
				$y = $space / 2;
				$x = $space / 2;
			}
			if ($c >= $col){
				$y += $h + $space;
				$x = $space / 2;
				$c = 0;
			}
			imagecopy($im, $bc, $x, $y, 0, 0, $w, $h);
			$x += $w + $space;
			$c++;
		}	
		return $im;
	}
		
		
?>