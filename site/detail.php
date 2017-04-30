<?php
  session_start();
  include("../config.php");
  include("../db_connect.php");
  include("../functions.php");

  $siteNo = $_GET['siteNo']; //@TODO バリデーション
  $trgtDt = $_GET['startTargetYearMonth'];

  $infoSql = "SELECT * FROM `site` WHERE no = :siteNo";
  $infoStmt = $pdo -> prepare($infoSql);
  $infoStmt -> bindValue(':siteNo', $siteNo, PDO::PARAM_INT);
  $infoStmt -> execute();
  $site = $infoStmt -> fetch(PDO::FETCH_ASSOC);
  //var_dump($row);

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
      サイトの詳細
      <div class="">
        title : <?php echo h($site['title']); ?>
      </div>
      <div class="">
        URL : <?php echo h($site['url']); ?>
      </div>
    </div>
    <div class="">
      <table>
        <thead>
          期間：<?php echo h($trgtDt); ?>
        </thead>
        <tbody>
          <tr>
            <th>Rank</th>
          </tr>
            <tr>
              <td></td>
            </tr>
        </tbody>
      </table>
    </div>
  </body>
</html>
