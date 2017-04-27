<?php
  session_start();

  include("config.php");
  include("db_connect.php");

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

  $stmt = $pdo->query($sql);
  $list = array();

  while($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
    $list[] = [ "keyNo" => $row['keyNo'], "ctgry" => $row['ctgry'] ,"keyword" => $row['keyword'], "rgst" => $row['rgst']];
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
        検索ワード一覧
      </div>
      <table>
        <tbody>
          <tr>
            <th>NO</th>
            <th>カテゴリー</th>
            <th>ワード</th>
            <th>日付</th>
          </tr>
          <?php for ($i=0; $i < count($list); $i++) : ?>
           <tr>
             <td><?php echo $i+1 ; ?></td>
             <td><?php echo $list[$i]['ctgry']; ?></td>
             <td>
                <?php $kwURL = "/seo/keywords.php?keywordno=".$list[$i]['keyNo'];
                      echo "<a href=$kwURL>"; ?>
                        <?php echo $list[$i]['keyword']; ?>
                <?php echo "</a>"; ?>
             </td>
             <td><?php echo $list[$i]['rgst']; ?></td>
           </tr>
         <?php endfor; ?>
        </tbody>
      </table>
    </div>
  </body>
</html>
