#!/bin/bash

# executable php-cli location
phpcli=`which php` 

# php script that run periodically
script='/home/elbas/public_html/afigi/payment/payment_alert.php'

# run the script
$phpcli -q $script

