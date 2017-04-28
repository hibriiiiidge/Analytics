<?php
  session_start();

  include("config.php");
  include("db_connect.php");
  include("functions.php");

  $keywordNo = $_GET['keywordno']; //@TODO　バリデーション
  $sTrgtYM   = $_GET['startTargetYearMonth'];
  $sTrgtY_M  = insertStr($_GET['startTargetYearMonth'], '-', 4, 0);
  $sTrgtY_M_D = $sTrgtY_M."-1";

  $sTrgtLastYM = date("Ym", strtotime(date($sTrgtY_M_D).'-1 month'));
  $sTrgtnextYM = date("Ym", strtotime(date($sTrgtY_M_D).'+1 month'));

  $sTrgtY = substr($sTrgtYM, 0, 4);
  $sTrgtM = substr($sTrgtYM, 4, 2);
  $sTrgtD = 1;
  $eTrgtD = date("d", strtotime('last day of '. $sTrgtY_M));

  //今月
  $thisMonth = date("Ym");

  //keyword
  $keyword = getKeywordFromKeywordNo($pdo, $keywordNo);

  //ranking
  $dbList = getMonthlyRankData(MAX_SITE, $sTrgtYM, $pdo, $keywordNo);
  //print_r($dbList);

  if(empty($dbList)){
    $beforeMesureDays = $eTrgtD;
  }
  else{
    $allTitleList = setTitleList(MAX_SITE, $dbList);

    $existNum = count($allTitleList[0]);
    //計測スタート日
    $date = new DateTime($dbList[0]['rgst']);
    $mesureStartDay = $date->format("d");
    //計測日以前 1日から$mesureStartDayまで
    $beforeMesureDays = $mesureStartDay - 1;
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body>
    <div class="">
      検索ワードの追加
    </div>
    <div class="">
      <div class="">
        <?php echo $sTrgtYM; ?>の推移
      </div>
      <div class="">
        キーワード：<?php echo $keyword;?>
      </div>
      <div class="">
        <?php
          echo '<a href="/seo/monthly.php?keywordno='.$keywordNo.'&startTargetYearMonth='.$sTrgtLastYM.'">先月の推移</a>';
          if($thisMonth !== $sTrgtYM){
            echo '<a href="/seo/monthly.php?keywordno='.$keywordNo.'&startTargetYearMonth='.$sTrgtnextYM.'">一月戻る</a>';
          }
         ?>
      </div>
      <table id="monthly">
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
            <tr>
              <td><?php echo $no; ?></td>
              <?php
                for ($i=0; $i < $beforeMesureDays ; $i++) {
                  echo "<td>×</td>";
                }
                for ($j=$mesureStartDay, $m=0; $j < $mesureStartDay+$existNum; $j++, $m++) {
                  $n = $no-1;
                  $targetTitle = $allTitleList[$n][$m];
                  if($targetTitle){
                      echo "<td><a href='/seo/site_detail.php?siteNo=$targetTitle[1]&startTargetYearMonth=$sTrgtYM'>$targetTitle[0]</a></td>";
                  }
                  else{
                      echo "<td>nodata</td>";
                  }
                }
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
