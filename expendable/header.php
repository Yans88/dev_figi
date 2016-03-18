<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="X-UA-Compatible" content="chrome=1">
<title>FiGi :: DeskCopy</title>
<link rel="shortcut icon" type="image/x-icon" href="../images/figiicon.ico" />
<link rel="stylesheet" href="<?php echo $figi_dir . 'style/' . STYLE ?>/deskcopy.css" type="text/css" media="screen" />
<script type="text/javascript" src="<?php echo $figi_dir ?>js/jquery.js"></script>
</head>
<body>
<div id="contentcenter" align="center" >
<div id="header">
	<div class="modbutton <?php echo $toggle?>" onclick="location.href='./?mod=<?php echo $toggle?>';">
    For <?php echo ($_mod == 'return') ? 'Loan' : 'Return'?>, Click Here
    </div>
</div>
<div id="title">
<?php if ($_mod == 'loan') { ?>
    FiGi DeskCopy <span class="underline">LOAN</span> Module
<?php } else { ?>    
    FiGi DeskCopy <span class="underline">RETURN</span> Module
<?php } ?>
</div>
<div id="toc" class="clear"></div>
<div id="content">
