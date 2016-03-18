#!/bin/sh

startdir="`pwd`"
currentdir=`dirname ${BASH_SOURCE[0]}`
php=`which php`

cd "$currentdir"
$php 'keyloan/reminder_return_alert.php'
$php 'loan/loan_duedate_report.php'
$php 'loan/loan_duedate_alert.php'
$php 'item/item_stocktake_autonotif.php'

cd "$startdir"