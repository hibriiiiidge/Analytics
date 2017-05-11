<?php
  session_start();
  include("../config.php");
  include("../db_connect.php");
  include("../functions.php");

  $siteNo  = $_GET['siteNo']; //@TODO バリデーション
  $keywordNo = $_SESSION['keywordNo'];
  $sTrgtYM = $_GET['startTargetYearMonth'];
  $sTrgtY_M  = insertStr($_GET['startTargetYearMonth'], '-', 4, 0);
  $eTrgtD = date("d", strtotime('last day of '. $sTrgtY_M));
  $sTrgtY = substr($sTrgtYM, 0, 4);
  $sTrgtM = substr($sTrgtYM, 4, 2);
  $sTrgtD = 1;


  //サイト情報
  $site = getSiteInfoFromSiteNo($pdo, $siteNo);

  //サイトの属するカテゴリーが持つキーワード一覧
  $kwdata = getKeywordsFromCategory($pdo, $site['category']);

  //ランキングデータの抽出
  $allKwRank = getRankDataFromKeywordsCategory(MAX_SITE, $sTrgtY_M, $pdo, $kwdata, $site['category']);

  //各キーワードとサイトの計測を開始した日付を取得
  //そこから未計測日を算出し各々配列に整形
  $mesureStartDay = array();    //計測開始日の配列
  $beforeMesureDays = array();  //未計測日の配列
  for ($i=0; $i < count($kwdata) ; $i++) {
    if($allKwRank[$i][0]['rgst']){ //計測を開始した日付が存在していたら
      $date = new DateTime($allKwRank[$i][0]['rgst']); //計測開始日のDateオブジェクト生成
      $mesureStartDay[] = $date->format("d"); //計測開始日
      $beforeMesureDays[] = ($date->format("d"))-1; //未計測日
    }
    else{
      $mesureStartDay[] = ""; //開始日 ""
      $beforeMesureDays[] = $eTrgtD; //月末まで全て未計測日とする
    }
  }

  //キーワード毎ランキングの生成
  $eachRankList = array();
  for ($i=0; $i < count($allKwRank); $i++) {
    for ($j=0; $j < count($allKwRank[$i]); $j++) {
      $eachRankList[$allKwRank[$i][$j]['keyword']][] = array_search($site['no'], $allKwRank[$i][$j]);
    }
  }

 //当サイトの順位取得
  $compRankList = array();
  for ($i=0; $i < count($kwdata) ; $i++) {
    $compRankList[] = current(array_slice($eachRankList, $i, 1, true));
  }

  //計測日（計測した値が存在する日）
  $existDays = array();
  for ($i=0; $i < count($kwdata); $i++) {
    if($compRankList[$i]!==false){
      $existDays[] = count($compRankList[$i]);
    }
    else{
      $existDays[] = "";
    }
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="../css/style.css">
  </head>
  <body>
    <div class="">
      <a href="/seo/">TOP</a><br/>
      <?php echo '<a href="/seo/rank/monthly.php?keywordno='.$keywordNo.'&startTargetYearMonth='.$sTrgtYM.'">戻る</a>'?>
    </div>
    <div class="">
      サイトの詳細
      <div class="">
        title : <?php echo h($site['title']); ?>
      </div>
      <div class="">
        URL : <?php echo h($site['url']); ?>
      </div>
    </div>
    <div class="">
      <table id="detailTable">
        <thead>
          期間：<?php echo h($sTrgtYM); ?>
        </thead>
        <tbody>
          <tr>
            <th style="font-size:10px;">キーワード</th>
            <?php
            while (checkdate($sTrgtM, $sTrgtD, $sTrgtY)) {
              echo "<th>$sTrgtD</th>";
              $sTrgtD++;
            }
             ?>
          </tr>
          <?php for ($no=0; $no < count($kwdata); $no++) : ?>
            <tr class="detailKyWords">
              <td><?php echo h($kwdata[$no]['keyword']); ?></td>
              <?php
                for ($i=0; $i < $beforeMesureDays[$no] ; $i++) {
                  echo "<td>×</td>";
                }
                for ($j=$mesureStartDay[$no], $m=0; $j < $mesureStartDay[$no]+$existDays[$no]; $j++, $m++) {
                  $siteRank = $compRankList[$no][$m];
                  if($siteRank){
                    echo "<td>$siteRank</td>";
                  }
                  else{
                    echo "<td>圏外(nodata)</td>";
                  }
                }
                if($beforeMesureDays[$no] !== $eTrgtD){
                  for ($k=$mesureStartDay[$no]+$existDays[$no]; $k <= $eTrgtD; $k++) {
                    echo "<td> - </td>";
                  }
                }
              ?>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>
  </body>
</html>
