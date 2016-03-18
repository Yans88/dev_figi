#!/bin/sh

echo 'check backup schedule....'
d=`pwd`
php=`which php`
$php $d/periodic_backup.php
