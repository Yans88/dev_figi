<?php
require 'util.php';
require 'common.php';
require 'user/user_util.php';
user_log(LOG_ACCESS, 'log-out from system');

session_destroy();
header('location: ./');  
?>