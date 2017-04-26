<?php
  define(MAX_SITE, "5");

  include("db_connect.php");
  include("functions.php");

  //$today = date("Ymd");@TODO 本来はこっち
  //$ytday = date("Ymd", strtotime("-1 day"));
  $today = "20170411";
  $ytday = "20170410";

  //keyword @TODO 関数化
  $kySql = "SELECT * FROM `keywords` WHERE no = :keyNo";
  $kyStmt = $pdo -> prepare($kySql);
  $kyStmt -> bindValue(':keyNo', $_GET['keyword'], PDO::PARAM_INT);
  $kyStmt -> execute();
  while($row = $kyStmt -> fetch(PDO::FETCH_ASSOC)){
    $keyword = $row['keyword'];
  }

  //site list
  $list = getTodayRankTitle(MAX_SITE, $today ,$pdo);

  //前日差異
  //当日のランキング
  $todayRank = getTodayRank($pdo, $today, MAX_SITE, $_GET['keyword']); //@TODO バリデーション

  //前日のランキング @TODO リファクタリング
  $ytdayRank = getYtdayRank($pdo, $ytday, MAX_SITE, $_GET['keyword']);

  //差分
  $diff = getRankDiff($todayRank, $ytdayRank);

  $compList = array_merge_recursive($list, $diff);

  //var_dump($compList);

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <div class="">
      キーワード：<?php echo $keyword;?>
    </div>
    <div class="">
      期間：<?php echo $today; ?>
    </div>
    <table>
      <tbody>
        <tr>
          <th>NO</th>
          <th>Up/Down</th>
          <th>タイトル</th>
          <th>URL</th>
        </tr>
        <?php for ($i=1; $i <= count($compList); $i++) : $rank = "rank".$i; ?>
         <tr>
           <td><?php echo $i ; ?></td>
           <td><?php echo $compList[$rank]['diffNum']; echo $compList[$rank]['mark']; ?></td>
           <td><?php echo $compList[$rank]['title']; ?></td>
           <td><?php echo $compList[$rank]['url']; ?></td>
         </tr>
       <?php endfor; ?>
      </tbody>
    </table>

  </body>
</html>
