<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 2016/11/3
 * Time: 下午2:32
 */

$key = '3gzdien9krwkyu91oqyq3bkwkh207qs9';
print_r($_POST);

$sign = $_POST['sign'];

unset($_POST['sign']);

ksort($_POST);

foreach ($_POST as $value){
    $str .= $value;
}

echo $str.$key;
echo "\n<br/>";
echo md5($str.$key);