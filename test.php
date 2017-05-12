<?php
$siteURL = "https://maps.google.co.jp/maps?um";
//$siteURL = "tet頑張る";
$res = preg_match("/https\:\/\/maps\.google\.co\.jp\/+/", $siteURL);
//$res = preg_match("/test/", $siteURL);
var_dump($res);
// if(){
//   echo "マッチ!";
// }
// else{
//   echo "マッチしていない！";
// }

 ?>
