<?php
  session_start();
  if(isset($_SESSION['keywordNo'])){
    unset($_SESSION['keywordNo']);
  }

  include("config.php");
  include("db_connect.php");
  include("functions.php");

  //キーワード一覧を取得
  $stmt = getKeywordsList($pdo);
  $list = array();
  //View用にデータを整形
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
      検索ワードの追加<br/>
      <select class="" name="">
        <option value="">pc</option>
        <option value="">audio</option>
      </select>
      <input type="text" name="" value="" placeholder="検索ワード" width="100px">
      <button type="button" name="button">追加</button>
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
            <th>検索ワード</th>
            <th>日付</th>
          </tr>
          <?php for ($i=0; $i < count($list); $i++) : ?>
           <tr>
             <td><?php echo h($i)+1 ; ?></td>
             <td><?php echo h($list[$i]['ctgry']); ?></td>
             <td>
                <?php echo "<a href=/seo/rank/daily.php?keywordno=".$list[$i]['keyNo'].">".h($list[$i]['keyword'])."</a>"; ?>
             </td>
             <td><?php echo h($list[$i]['rgst']); ?></td>
           </tr>
         <?php endfor; ?>
        </tbody>
      </table>
    </div>
  </body>
</html>
