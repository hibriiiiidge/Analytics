<?php
/**
 * エスケープ関数
 * @param $str string
 * @return $string
 */
function h($str){
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * SQL文生成
 * @param  $intger ランキングのサイト数 100
 * @return $string ex) "site1.title AS title1 ,site1.url AS url1 ,site1.no AS no1"
 */
function makeSelect($maxNum){
  $eachSelect = array();
  for ($i=1; $i <= $maxNum; $i++) {
    $eachSelect[] = "site".$i.".title AS title".$i." ,site".$i.".url AS url".$i." ,site".$i.".no AS no".$i;
  }
  return implode(",", $eachSelect);
}

/**
 * LEFT JOIN文生成
 * @param  $intger ランキングのサイト数 100
 * @return $string ex) "LEFT JOIN `site` AS site1 ON (site1.no = r.rank1)"
 */
function makeLeftJoin($maxNum){
  $eachLeftJoin= array();
  for ($i=1; $i <= $maxNum; $i++) {
    $eachLeftJoin[] = "LEFT JOIN `site` AS site".$i." ON (site".$i.".no = r.rank".$i.")";
  }
  return implode(" ", $eachLeftJoin);
}

/**
 * SQL文生成
 * @param  $intger ランキングのサイト数 100
 * @return $string ex) "rank1,rank2,rank3,rank4"
 */
function makeRankSelect($maxNum){
  $eachRankSelect = array();
  for ($i=1; $i <= $maxNum ; $i++) {
    $eachRankSelect[] = "rank".$i;
  }
  return implode(",", $eachRankSelect);
}

/**
 * キーワード一覧取得
 * @param  $pdo PDO Object
 * @return array 抽出データ
 */
function getKeywordsList($pdo){
  $sql = "SELECT
    ky.no       AS keyNo,
    ctgry.type  AS ctgry,
    ky.keyword  AS keyword,
    ky.rgst     AS rgst
FROM
    `keywords` AS ky
    LEFT JOIN `category` AS ctgry ON ctgry.no = ky.category
WHERE
    1";
  return $pdo->query($sql);
}

/**
 * キーワードNOに該当するキーワードを取得
 * @param  $pdo PDO Object
 * @param  $keywordNo int
 * @return string キーワード
 */
function getKeywordFromKeywordNo($pdo, $keywordNo){
  $kySql = "SELECT * FROM `keywords` WHERE no = :keyNo";
  $kyStmt = $pdo -> prepare($kySql);
  $kyStmt -> bindValue(':keyNo', $keywordNo, PDO::PARAM_INT);
  $kyStmt -> execute();
  while($row = $kyStmt -> fetch(PDO::FETCH_ASSOC)){
    $keyword = $row['keyword'];
  }
  return $keyword;
}

/**
 * 当日のランキングのタイトルを取得
 * @param  $maxNum サイト数
 * @param  当日の日付 ex)20170411
 * @param  $pdo PDO Object
 * @param  $keywordNo int
 * @return array
 */
function getTodayRankTitle($maxNum, $today, $pdo, $keywordNo){
  $select = makeSelect($maxNum);
  $leftJoin = makeLeftJoin($maxNum);
  //当日
  $listSql =
  "SELECT
      $select
    FROM `ranking` AS r
    $leftJoin
    WHERE
        DATE_FORMAT(r.rgst, '%Y%m%d') = $today
    AND
      r.keywords_no = :keyNo";

  $listStmt = $pdo -> prepare($listSql);
  $listStmt -> bindValue(':keyNo', $keywordNo, PDO::PARAM_INT); //@TODO バリデーション
  $listStmt -> execute();
  $list = array();
  $row = $listStmt->fetch(PDO::FETCH_ASSOC);
  for ($i=1; $i <= $maxNum ; $i++) {
    $rank = "rank".$i;
    $title = "title".$i;
    $url = "url".$i;
    $list[$rank] = ["title" => $row[$title], "url" => $row[$url]];
  }
  return $list;
}

/**
 * 当日のランキングを取得
 * @param  $pdo PDO Object
 * @param  $today 当日の日付 ex)20170411
 * @param  $maxRank サイト数
 * @param  $keywordNo int
 * @return array
 */
function getTodayRank($pdo, $today, $maxRank, $keywordNo){
  $todaySql = makeSQL($today, $maxRank);
  $todayStmt = $pdo -> prepare($todaySql);
  $todayStmt -> bindValue(':keyNo', $keywordNo, PDO::PARAM_INT);
  $todayStmt -> execute();
  $todayRow = $todayStmt->fetch(PDO::FETCH_ASSOC);
  $todayRank = array();
  for ($i=1; $i <= MAX_SITE ; $i++) {
    $rank = "rank".$i;
    $todayRank[$i] = $todayRow[$rank];
  }
  return $todayRank;
}

/**
 * 昨日のランキングを取得
 * @param  $pdo PDO Object
 * @param  $today 昨日の日付 ex)20170410
 * @param  $maxRank サイト数
 * @param  $keywordNo int
 * @return array
 */
function getYtdayRank($pdo, $ytday, $maxRank, $keywordNo){
  $ytdaySql = makeSQL($ytday, $maxRank);
  $ytdayStmt = $pdo -> prepare($ytdaySql);
  $ytdayStmt -> bindValue(':keyNo', $keywordNo, PDO::PARAM_INT);
  $ytdayStmt -> execute();
  $ytdayRow =  $ytdayStmt->fetch(PDO::FETCH_ASSOC);
  $ytdayRank = array();
  for ($i=1; $i <= MAX_SITE ; $i++) {
    $rank = "rank".$i;
    $ytdayRank[$i] = $ytdayRow[$rank];
  }
  return $ytdayRank;
}

/**
 * SQL文の生成
 * @param  $day date 20170411
 * @param  $maxRank サイト数
 * @return string
 */
function makeSQL($day, $maxRank){
  $ranks = makeRankSelect($maxRank);
  return
  "SELECT
      $ranks
  FROM
      `ranking`
  WHERE
    DATE_FORMAT(`rgst`, '%Y%m%d') = $day
      AND
    `keywords_no` = :keyNo";
}

/**
 * 昨日対比の増減数と増減マークの生成
 * @param  $todayRank 当日のランキング array
 * @param  $ytdayRank 前日のランキング array
 * @return $diff array
 */
function getRankDiff($todayRank, $ytdayRank){
  $diff = array();
  for ($i=1; $i <= MAX_SITE ; $i++) {
    $targetSite = $todayRank[$i];
    for ($j=1; $j <= MAX_SITE ; $j++) {
      $targetSiteYt = $ytdayRank[$j];
      $rank = "rank".$i;
      if($targetSite == $targetSiteYt){ //@TODO ランク圏外からのランクアップの場合の処理
        if($i < $j){
          $diffNum = $j - $i;
          $mark = "▲";
        }
        elseif($i > $j){
          $diffNum = $i - $j;
          $mark = "▼";
        }
        else{
          $diffNum = "";
          $mark = "-";
        }
        $diff[$rank] = ["diffNum" => $diffNum, "mark" => $mark];
      }
    }
  }
  return $diff;
}

/**
 * 昨日対比の増減数と増減マークの生成
 * @param  $maxNum サイト数
 * @param  $sTrgtYM スタートターゲット期間(Ym形式 ex.201704)
 * @param  $pdo PDO Object
 * @param  $keywordNo int
 * @return array
 */
function getMonthlyRankData($maxNum, $sTrgtYM, $pdo, $keywordNo){
  $select = makeSelect($maxNum);
  $leftJoin = makeLeftJoin($maxNum);
  $sql =
  "SELECT ".$select." ,r.rgst AS rgst
    FROM
      `ranking` AS r
      $leftJoin
    WHERE
      r.keywords_no = :keyNo
    AND
      (DATE_FORMAT(r.rgst, '%Y%m') = ".$sTrgtYM.")";
  $stmt = $pdo -> prepare($sql);
  $stmt -> bindValue(':keyNo', $keywordNo, PDO::PARAM_INT);
  $stmt -> execute();
  return $stmt -> fetchAll();
}

/**
 * 文字列Aの中に特定の文字列Bをインサートする処理
 * @param  $text 文字列A
 * @param  $str  特定の文字列B
 * @param  $start 特定の文字列Bのスタート位置
 * @return string
 */
function insertStr($text, $str, $start){
  return substr_replace($text, $str, $start, 0);
}

/**
 * 昨日対比の増減数と増減マークの生成
 * @param  $maxNum サイト数
 * @param  $dbList データ一式
 * @return array
 */
function setTitleList($maxNum, $dbList){
  $allTitleListAry = array();
  for ($i=1; $i <= $maxNum ; $i++) {
    $title      = 'title'.$i;
    $siteNo     = 'no'.$i;
    $titleList  = array_column($dbList, $title); //特的のキーのみで配列を整形
    $siteNoList = array_column($dbList, $siteNo);
    $setList= array();
    foreach ($titleList as $key => $value) {
      $setList[] = ["0" => $value, "1" => $siteNoList[$key]];
    }
    array_push($allTitleListAry, $setList);
  }
  return $allTitleListAry;
}

/**
* サイト情報の取得
* @param  $pdo PDO Object
* @param  $siteNo int サイトNo
* @return array
*/
function getSiteInfoFromSiteNo($pdo, $siteNo){
  $infoSql = "SELECT * FROM `site` WHERE no = :siteNo";
  $infoStmt = $pdo -> prepare($infoSql);
  $infoStmt -> bindValue(':siteNo', $siteNo, PDO::PARAM_INT);
  $infoStmt -> execute();
  return $infoStmt -> fetch(PDO::FETCH_ASSOC);
}

/**
* サイト情報の取得
* @param  $pdo PDO Object
* @param  $cateNo int カテゴリーNo
* @return array
*/
function getKeywordsFromCategory($pdo, $cateNo){
  $kwSql =
  "SELECT
    *
  FROM
    `keywords`
  WHERE
    category = :cateNo";

  $kwStmt = $pdo -> prepare($kwSql);
  $kwStmt -> bindValue(':cateNo', $cateNo, PDO::PARAM_INT);
  $kwStmt -> execute();
  $kwdata = array();
  while($row = $kwStmt -> fetch(PDO::FETCH_ASSOC)){
    $kwdata[] = ["no" => $row['no'], "keyword" => $row['keyword']];
  }
  return $kwdata;
}

/**
* サイト情報の取得
* @param  $maxsite int サイト数
* @param  $sTrgtY_M date ターゲットとなる年月 ex) 2017-04
* @param  $pdo PDO Object
* @param  $kwdata array キーワード一覧の配列
* @param  $category int カテゴリ-No
* @return array
*/
function getRankDataFromKeywordsCategory($maxsite, $sTrgtY_M, $pdo, $kwdata, $category){
  $ranks = makeRankSelect(MAX_SITE);
  $rkSql =
  "SELECT
      $ranks
      ,ky.keyword
      ,r.rgst
  FROM
      `ranking` AS r
      LEFT JOIN `keywords` AS ky ON (ky.no = r.keywords_no)
  WHERE
      r.category_no = :cateNo
    AND
      DATE_FORMAT(r.rgst, '%Y-%m') = '$sTrgtY_M'
    AND
      r.keywords_no = :keyNo
    ORDER BY r.keywords_no, r.rgst ASC";
  $allKwRank = array();
  for ($i=0; $i < count($kwdata) ; $i++) {
    $rkStmt = $pdo -> prepare($rkSql);
    $rkStmt -> bindValue(':cateNo', $category, PDO::PARAM_INT); //@TODO バリデーション
    $rkStmt -> bindValue(':keyNo', $kwdata[$i]['no'], PDO::PARAM_INT);
    $rkStmt -> execute();
    $rkdata = array();
    while($row = $rkStmt -> fetch(PDO::FETCH_ASSOC)){
      $rkdata[] = $row;
    }
    $allKwRank[$i] = $rkdata;
  }
  return $allKwRank;
}

/**
 *
 */
function makeRanks($maxNum, $string){
 $eachRankSelect = array();
 for ($i=1; $i <= $maxNum ; $i++) {
   $eachRankSelect[] = $string.$i;
 }
 return implode(",", $eachRankSelect);
}
