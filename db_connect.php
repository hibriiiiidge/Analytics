<?php
try {
  $dsn='mysql:host=localhost;dbname=seo_fixed_point;charset=utf8';
  $user=root;
  $pass=root;
  $pdo= new PDO($dsn, $user, $pass, array(PDO::ATTR_EMULATE_PREPARES => false));
}
catch (Exception $e) {
  exit('データベース接続失敗'.$e->getMessage());
}
