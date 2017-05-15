<?php
  include("../config.php");
  include("../db_connect.php");
  include("../utility/functions.php");

  header(" Content-Type:application/json; charset=utf-8");

  //@TODO class化した方が良い キーワード関連
  //var_dump($_POST);
  $categoryNo = $_POST['ctgNo']; //@TODO バリデーション
  $keyword    = mb_convert_kana($_POST['kw'], s);

  try {
    $slctKwSql = "SELECT count(*) AS cnt FROM `keywords` WHERE keyword = :keyword AND status <> 'x'";
    $slctkyStmt = $pdo -> prepare($slctKwSql);
    $slctkyStmt -> bindValue(':keyword', $keyword, PDO::PARAM_INT);
    $slctkyStmt -> execute();
    $row = $slctkyStmt -> fetch(PDO::FETCH_ASSOC);
    //既にキーワードが存在するかどうかの確認
    //var_dump($row['cnt']);
    if($row['cnt']){
      //存在する場合
      echo json_encode(array("res" => "exist"), JSON_UNESCAPED_UNICODE);
    }
    else{
      //存在しない場合
      $insrtKwSql = "INSERT INTO `keywords` (keyword, category, rgst) VALUES (:keyword, :category, :rgst)";
      $insrtKwStmt = $pdo -> prepare($insrtKwSql);
      $insrtKwStmt -> bindValue(':keyword',   $keyword,     PDO::PARAM_INT);
      $insrtKwStmt -> bindValue(':category',  $categoryNo,  PDO::PARAM_INT);
      $insrtKwStmt -> bindValue(':rgst',      $dtHis,       PDO::PARAM_STR);
      $insrtKwStmt -> execute();
      $list = getKeywordsList($pdo);
      echo json_encode(array("res" => "success", "list" => $list), JSON_UNESCAPED_UNICODE);
    }
  }
  catch (Exception $e) {
    //接続に失敗
    echo json_encode(array("res" => "失敗しました。"), JSON_UNESCAPED_UNICODE);
  }
 ?>
