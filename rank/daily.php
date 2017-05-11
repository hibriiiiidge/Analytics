<?php
  session_start();

  //define(MAX_SITE, "5");

  include("../config.php");
  include("../db_connect.php");
  include("../functions.php");

  $today = date("Ymd");//@TODO 本来はこっち
  $ytday = date("Ymd", strtotime("-1 day"));
  //$today = "20170411";
  //$ytday = "20170410";

  $keywordNo = $_GET['keywordno']; //@TODO バリデーション
  $trgtYMD = date("Ymd", strtotime('first day of +0 month'));
  $sTrgtYM = date("Ym", strtotime('first day of +0 month'));
  $eTrgtYMD = date("Ymd", strtotime('last day of +0 month'));

  //keyword
  $keyword = getKeywordFromKeywordNo($pdo, $keywordNo);

  //site list
  $list = getTodayRankTitle(MAX_SITE, $today ,$pdo, $keywordNo);

  //前日差異
  //当日のランキング
  $todayRank = getTodayRank($pdo, $today, MAX_SITE, $keywordNo);

  //前日のランキング @TODO リファクタリング
  $ytdayRank = getYtdayRank($pdo, $ytday, MAX_SITE, $keywordNo);

  //差分
  $diff = getRankDiff($todayRank, $ytdayRank);
  //整形後の配列
  $compList = array_merge_recursive($list, $diff);
  //var_dump($diff);

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
      <a href="/seo/">戻る</a>
    </div>
    <div class="">
      キーワード：<?php echo h($keyword);?>
    </div>
    <div class="">
      期間：<?php echo h($today); ?>
    </div>
    <div class="">
      <?php
        echo '<a href="/seo/rank/monthly.php?keywordno='.h($keywordNo).'&startTargetYearMonth='.h($sTrgtYM).'">今月の推移</a>';
       ?>
    </div>
    <table id="dailyTable">
      <tbody>
        <tr>
          <th>NO</th>
          <th>Up/Down</th>
          <th>タイトル</th>
          <th>URL</th>
        </tr>
        <?php for ($i=1; $i <= count($compList); $i++) : $rank = "rank".$i; ?>
         <tr class="dailyRank">
           <td><?php echo h($i) ; ?></td>
           <td>
             <?php
              if (!$compList[$rank]['mark']) {
                echo "圏外▲";
              }
              echo h($compList[$rank]['diffNum']);
              echo h($compList[$rank]['mark']);
              ?>
           </td>
           <td><?php echo h($compList[$rank]['title']); ?></td>
           <td>
             <?php echo "<a href=".h($compList[$rank]['url']).">".h($compList[$rank]['url'])."</a>"; ?>
           </td>
         </tr>
       <?php endfor; ?>
      </tbody>
    </table>

  </body>
</html>
