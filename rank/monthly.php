<?php
  session_start();

  include("../config.php");
  include("../db_connect.php");
  include("../utility/functions.php");

  $keywordNo = $_GET['keywordno']; //@TODO バリデーション
  $_SESSION['keywordNo'] = $keywordNo;
  $sTrgtYM   = $_GET['startTargetYearMonth'];
  //201704を2017-04の形式に整形
  $sTrgtY_M  = insertStr($_GET['startTargetYearMonth'], '-', 4, 0);
  //更に月初日形式に整形 2017-04-01
  $sTrgtY_M_D = $sTrgtY_M."-1";
  $sTrgtLastYM = date("Ym", strtotime(date($sTrgtY_M_D).'-1 month')); //1ヶ月前
  $sTrgtnextYM = date("Ym", strtotime(date($sTrgtY_M_D).'+1 month')); //1ヶ月後
  $sTrgtY = substr($sTrgtYM, 0, 4); //年の取得 201704->2017
  $sTrgtM = substr($sTrgtYM, 4, 2); //月の取得 201704->04
  $sTrgtD = 1;  //月初の1日
  $eTrgtD = date("d", strtotime('last day of '. $sTrgtY_M)); //末日取得
  $thisMonth = date("Ym"); //今月

  //キーワード取得
  $keyword = getKeywordFromKeywordNo($pdo, $keywordNo);

  //月毎のランキング取得 キーワードを軸に各サイトのランキング
  $dbList = getMonthlyRankData(MAX_SITE, $sTrgtYM, $pdo, $keywordNo);

  //テーブル生成のための配列データの整形、及び、未計測日・計測日・未経過日の各日数の取得
  if(empty($dbList)){
    //データがなかった場合 未計測日=月間の日数
    $beforeMesureDays = $eTrgtD;
  }
  else{
    $allTitleList = setTitleList(MAX_SITE, $dbList);
    //計測日数
    $existNum = count($allTitleList[0]);
    //計測スタート日
    $date = new DateTime($dbList[0]['rgst']);
    $mesureStartDay = $date->format("d");
    //未計測日の日数計算 1日から計測開始日($mesureStartDay)まで
    $beforeMesureDays = $mesureStartDay - 1;
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="../css/style.css">
    <style media="screen">
      .site5{
      background-color: red;
      }
      .site3{
      background-color: yellow;
      }
    </style>
  </head>
  <body>
    <div class="">
      <a href="/seo/">TOP</a><br/>
      <?php echo '<a href="/seo/rank/daily.php?keywordno='.$keywordNo.'">戻る</a>'?>
    </div>
    <div class="">
      検索ワードの追加
    </div>
    <div class="">
      <div class="">
        <?php echo h($sTrgtYM); ?>の推移
      </div>
      <div class="">
        キーワード：<?php echo h($keyword);?>
      </div>
      <div class="">
        <?php
          echo '<a href="/seo/rank/monthly.php?keywordno='.h($keywordNo).'&startTargetYearMonth='.h($sTrgtLastYM).'">先月の推移</a>';
          if($thisMonth !== $sTrgtYM){
            //今月のデータで無かった場合に戻るボタン１つ戻るボタンの表示
            echo '<a href="/seo/rank/monthly.php?keywordno='.h($keywordNo).'&startTargetYearMonth='.h($sTrgtnextYM).'">一月戻る</a>';
          }
         ?>
      </div>
      <table id="monthlyTable">
        <tbody>
          <tr>
            <th>順位</th>
            <?php
            while (checkdate($sTrgtM, $sTrgtD, $sTrgtY)) {
              echo "<th>$sTrgtD</th>";
              $sTrgtD++;
            }
             ?>
          </tr>

          <?php for ($no=1; $no <= MAX_SITE; $no++) : ?>
            <tr class="monthyRank">
              <td><?php echo h($no); ?></td>
              <?php
                //未計測日（計測日前）の場合は "×" を表示
                for ($i=0; $i < $beforeMesureDays ; $i++) {
                  echo "<td>×</td>";
                }
                //計測日の場合は、サイトタイトルを表示
                for ($j=$mesureStartDay, $m=0; $j < $mesureStartDay+$existNum; $j++, $m++) {
                  $n = $no-1;
                  $targetTitle = $allTitleList[$n][$m];
                  $title = cutStr($targetTitle[0], 24);
                  echo "<td class='site$targetTitle[1]'><a href='/seo/site/detail.php?siteNo=$targetTitle[1]&startTargetYearMonth=$sTrgtYM'>$title</a></td>";
                }
                //計測日が存在する場合は、月末までの表示を "-"にする
                if($mesureStartDay){
                  for ($k=$mesureStartDay+$existNum; $k <= $eTrgtD; $k++) {
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
