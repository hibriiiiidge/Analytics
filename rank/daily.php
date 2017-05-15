<?php
  session_start();

  //define(MAX_SITE, "5");

  include("../config.php");
  include("../db_connect.php");
  include("../utility/functions.php");

  $keywordNo  = $_GET['keywordNo']; //@TODO バリデーション
  $targetDate = $_GET['targetDate'];

  if($targetDate){
    //特定の日付のランキングを表示する場合
    $today = $targetDate;
    $ytday = date("Ymd", strtotime('-1 day', strtotime($targetDate)));
  }
  else{
    //TOPページからのページ遷移
    $today = date("Ymd");//@TODO 本来はこっち
    $ytday = date("Ymd", strtotime("-1 day"));
  }

  //$today = "20170411";
  //$ytday = "20170410";

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
  //配列の値があるかどうかフィルタリング
  //前日のランキングがない場合（登録して初めての集計の場合）の表示のため
  $ytdayChk = array_filter($ytdayRank);

  //差分
  $diff = getRankDiff($todayRank, $ytdayRank);
  //整形後の配列
  $compList = array_merge_recursive($list, $diff);

  //var_dump($todayRank);

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
      <form class="" action="/seo/rank/daily.php" method="get">
        <input type="text" id="" name="targetDate">
        <input type="hidden" name="keywordNo" value="1">
        <input type="submit" value="取得">
      </form>
    </div>
    <div class="">
      <?php
        echo '<a href="/seo/rank/monthly.php?keywordNo='.h($keywordNo).'&startTargetYearMonth='.h($sTrgtYM).'">今月の推移</a>';
       ?>
    </div>
    <table id="dailyTable">
      <colgroup>
        <col width="30">
        <col width="85">
        <col>
        <col width="300">
      </colgroup>

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
                $spMark = $ytdayChk ? "圏外▲":"初▲"; //前日の集計が自体が存在しない場合->初, 集計対象外の順位からのランクアップの場合->圏外
                echo $spMark;
              }
              echo h($compList[$rank]['diffNum']);
              echo h($compList[$rank]['mark']);
              ?>
           </td>
           <td>
             <?php
              $title = cutStr($compList[$rank]['title'], 50);
              echo "<a href='/seo/site/detail.php?siteNo=".h($compList[$rank]['no'])."&keywordNo=".h($keywordNo)."&startTargetYearMonth=".$sTrgtYM."' target='blank'>".h($title)."</a>";
             ?>
           </td>
           <td>
             <?php
              $url = cutStr($compList[$rank]['url'], 50);
              echo "<a href=".h($compList[$rank]['url'])." target='blank'>".h($url)."</a>";
             ?>
           </td>
         </tr>
       <?php endfor; ?>
      </tbody>
    </table>

  </body>
</html>
