<?php
  //TODO 全体リファクタリング+関数化
  //define(SITE_NUM, 2); //n*10が取得サイト数
  // define(MAX_SITE, 20); //表示対象サイト数

  include("config.php");
  include("db_connect.php");
  include("utility/functions.php");

  // phpQueryの読み込み
  require_once("utility/phpQuery-onefile.php");

  $date       = new Datetime();
  $dtYmd      = $date->format("Y-m-d");
  $dtHis      = $date->format("Y-m-d H:i:s");

  //targetWordsに関する処理
  //category テーブルから生きている$ctgryNoを取得
  $slctCtgSql ="SELECT no FROM `category` WHERE status <>'x'";
  $slctCtgStmt = $pdo -> prepare($slctCtgSql);
  $slctCtgStmt -> execute();
  while($row = $slctCtgStmt -> fetch(PDO::FETCH_ASSOC)){
    $ctgAry[] = $row['no'];
  }

  for ($i=0; $i < count($ctgAry); $i++) {
    //keywordsテーブルからcategoryに属するkeywordを取得
    $slctKwSql ="SELECT no, keyword FROM `keywords` WHERE category = :ctgryNo AND status <>'x'";
    $slctKwStmt = $pdo -> prepare($slctKwSql);
    $slctKwStmt -> bindValue(':ctgryNo', $ctgAry[$i], PDO::PARAM_INT);
    $slctKwStmt -> execute();
    while($row = $slctKwStmt -> fetch(PDO::FETCH_ASSOC)){
      $kwAry[$i][] = ["no" => $row['no'], "keyword" => $row['keyword']];
    }
    for ($j=0; $j < count($kwAry[$i]) ; $j++) {
      $tw         = $kwAry[$i][$j]['keyword'];
      $ctgryNo    = $ctgAry[$i];
      $kywdNo     = $kwAry[$i][$j]['no'];
      $halfWidthSpaceQ = mb_convert_kana($tw, 's');

      for ($k=0; $k < (MAX_SITE)/10 ; $k++) {
        $q = urlencode($halfWidthSpaceQ);
        //ターゲットURL
        $pageNo   = 10*$k;
        $targetUrl = "https://www.google.co.jp/search?q=".$q."&oq=".$q."&sourceid=chrome&ie=UTF-8&start=".$pageNo;
        // HTMLの取得
        $htmlData = file_get_contents($targetUrl);
        mb_language('Japanese');
        $doc = phpQuery::newDocument(mb_convert_encoding($htmlData, "HTML-ENTITIES", "auto"));

        //results_bidの項目をforeachで回す
        foreach ($doc["#res"]->find(".g") as $items) {
          sleep(1);
          $siteTitle  = pq($items)->find("a")->text();
          $siteURLAll = pq($items)->find("a")->attr('href');
          preg_match('(https?://[-_.!~*\'()a-zA-Z0-9;/?:@=+$,%#]+)', $siteURLAll, $siteURL);
          if(!preg_match("/https\:\/\/maps\.google\.co\.jp\/+/", $siteURL[0])){ //googleの地図広告を排除
            $jsonData['items'][$i][$j][] = ['title' => $siteTitle, 'url' => $siteURL[0]];
          }
        }
      }
      //var_dump($jsonData['items']);
      //取得したサイト（MAX_SITE件）がsiteテーブルに既に存在するかどうかの確認
      //存在しなかったらINSERT
      for ($m=0; $m < MAX_SITE ; $m++) {
        $trgtTitleAry   = explode("キャッシュ", $jsonData['items'][$i][$j][$m]['title']);
        $trgtTitle      = $trgtTitleAry[0];
        //$trgtTitle      = $jsonData['items'][$i][$j][$m]['title'];
        $trgtUrl        = $jsonData['items'][$i][$j][$m]['url'];

        $instSiteSql =
        "INSERT INTO
            `site`(title, url, category, rgst)
        SELECT
        *
        FROM
        (SELECT
            '".$trgtTitle."',
            '".$trgtUrl."',
            $ctgryNo,
            '".$dtHis."'
          ) AS TMP
        WHERE NOT EXISTS
          (SELECT * FROM `site` WHERE url = '".$trgtUrl."' AND status <>'x')";

        //var_dump($instSiteSql);
        $insrtSiteStmt = $pdo -> prepare($instSiteSql);
        $insrtSiteStmt->execute();
      }
      //print_r($jsonData['items']);
      //取得したサイトの取得順（rank順）にサイトNoを取得
      for ($n=0; $n < MAX_SITE; $n++) {
        $slctSql ="SELECT no FROM `site` WHERE `url` = :url AND status <>'x'";
        $slctStmt = $pdo -> prepare($slctSql);
        $slctStmt -> bindValue(':url', $jsonData['items'][$i][$j][$n]['url'], PDO::PARAM_INT);
        $slctStmt -> execute();
        while($row = $slctStmt -> fetch(PDO::FETCH_ASSOC)){
          $siteRankNo[$i][$j][] = $row['no'];
        }
      }
      //print_r($siteRankNo);
      //rankingテーブルにrankごとのサイトNoをインサート
      $rankClm = makeRanks(MAX_SITE, "rank");
      $rankVal = makeRanks(MAX_SITE, ":rank");
      $insrtRankSql = "INSERT INTO `ranking` ($rankClm, category_no, keywords_no, rgst) VALUES ($rankVal, :category_no, :keyword_no, :rgst)";
      $insrtRankStmt = $pdo -> prepare($insrtRankSql);
      for ($p=0; $p <MAX_SITE ; $p++) {
        $rankNum = $p+1;
        $rankBind = ':rank'.$rankNum;
        $insrtRankStmt -> bindValue($rankBind, $siteRankNo[$i][$j][$p], PDO::PARAM_INT);
      }
      $insrtRankStmt -> bindValue(':category_no', $ctgryNo, PDO::PARAM_INT);
      $insrtRankStmt -> bindValue(':keyword_no',  $kywdNo,  PDO::PARAM_INT);
      $insrtRankStmt -> bindValue(':rgst',        $dtHis,   PDO::PARAM_STR);
      $insrtRankStmt -> execute();
    }
  }

?>
