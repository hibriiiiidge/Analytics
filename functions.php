<?php
function makeSelect($maxNum){
  $eachSelect = array();
  for ($i=1; $i <= $maxNum; $i++) {
    $eachSelect[] = "site".$i.".title AS title".$i.",site".$i.".url AS url".$i;
  }
  return implode(",", $eachSelect);
}

function makeLeftJoin($maxNum){
  $eachLeftJoin= array();
  for ($i=1; $i <= $maxNum; $i++) {
    $eachLeftJoin[] = "LEFT JOIN `site` AS site".$i." ON (site".$i.".no = r.rank".$i.")";
  }
  return implode(" ", $eachLeftJoin);
}

function makeRankSelect($maxNum){
  $eachRankSelect = array();
  for ($i=1; $i <= $maxNum ; $i++) {
    $eachRankSelect[] = "rank".$i;
  }
  return implode(",", $eachRankSelect);
}

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
        r.dt_no = $today
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


function getTodayRank($pdo, $today, $maxRank, $keyword){
  $todaySql = makeSQL($today, $maxRank);
  $todayStmt = $pdo -> prepare($todaySql);
  $todayStmt -> bindValue(':keyNo', $keyword, PDO::PARAM_INT);
  $todayStmt -> execute();
  $todayRow = $todayStmt->fetch(PDO::FETCH_ASSOC);
  $todayRank = array();
  for ($i=1; $i <= MAX_SITE ; $i++) {
    $rank = "rank".$i;
    $todayRank[$i] = $todayRow[$rank];
  }
  return $todayRank;
}

function getYtdayRank($pdo, $ytday, $maxRank, $keyword){
  $ytdaySql = makeSQL($ytday, $maxRank);
  $ytdayStmt = $pdo -> prepare($ytdaySql);
  $ytdayStmt -> bindValue(':keyNo', $keyword, PDO::PARAM_INT);
  $ytdayStmt -> execute();
  $ytdayRow =  $ytdayStmt->fetch(PDO::FETCH_ASSOC);
  $ytdayRank = array();
  for ($i=1; $i <= MAX_SITE ; $i++) {
    $rank = "rank".$i;
    $ytdayRank[$i] = $ytdayRow[$rank];
  }
  return $ytdayRank;
}

function makeSQL($day, $maxRank){
  $ranks = makeRankSelect($maxRank);
  return
  "SELECT
      $ranks
  FROM
      `ranking`
  WHERE
    `dt_no` = $day
      AND
    `keywords_no` = :keyNo";
}

function getRankDiff($todayRank, $ytdayRank){
  $diff = array();
  for ($i=1; $i <= MAX_SITE ; $i++) {
    $targetSite = $todayRank[$i];
    for ($j=1; $j <= MAX_SITE ; $j++) {
      $targetSiteYt = $ytdayRank[$j];
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
        $rank = "rank".$i;
        $diff[$rank] = ["diffNum" => $diffNum, "mark" => $mark];
      }
    }
  }
  return $diff;
}

?>
