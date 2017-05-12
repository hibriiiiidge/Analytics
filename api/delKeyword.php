<?php
  include("../config.php");
  include("../db_connect.php");
  include("../utility/functions.php");

  header(" Content-Type:application/json; charset=utf-8");

  //@TODO class化した方が良い キーワード関連
  $keywordNo    = $_POST['kwNo']; //@TODO バリデーション

  //対象キーワードを論理削除する処理
  try {
    $upKwStsSql =
    "UPDATE
      `keywords`
    SET
      `status` = 'x', `updt` = :updt
    WHERE
      no = :kwNo";
    $upKwStmt = $pdo -> prepare($upKwStsSql);
    $upKwStmt -> bindValue(':updt',  $dtHis,      PDO::PARAM_STR);
    $upKwStmt -> bindValue(':kwNo',  $keywordNo,  PDO::PARAM_INT);
    $upKwStmt -> execute();
    $list = getKeywordsList($pdo);
    echo json_encode(array("res" => "success", "list" => $list), JSON_UNESCAPED_UNICODE);
  }
  catch (Exception $e) {
    //接続に失敗
    echo json_encode(array("res" => "失敗しました。"), JSON_UNESCAPED_UNICODE);
  }

 ?>
