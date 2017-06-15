<?php
  session_start();
  if(isset($_SESSION['keywordNo'])){
    unset($_SESSION['keywordNo']);
  }

  include("config.php");
  include("db_connect.php");
  include("utility/functions.php");

  //セレクトタグの生成 @TODO 関数化
  $slctCtgSql = "SELECT no, type FROM `category` WHERE status <> 'x'";
  $ctgStmt = $pdo -> query($slctCtgSql);
  while($row = $ctgStmt -> fetch(PDO::FETCH_ASSOC)) {
    $ctgList[] = ["no" => $row['no'], "type" => $row['type']];
  }

  //キーワード一覧を取得
  $list = getKeywordsList($pdo);

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script src="js/seoScript.js" type="text/javascript"></script>
  </head>
  <body>
    <div class="">
      検索ワードの追加<br/>
      <select id="categoryNo" name="">
        <?php for ($i=0; $i < count($ctgList); $i++) {
          echo "<option value='".$ctgList[$i]['no']."'>".$ctgList[$i]['type']."</option>";
        } ?>
      </select>
      <input type="text" id="kw" name="kw" placeholder="検索ワード" width="100px">
      <button type="button" id="addKw" name="button">追加</button>
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
            <th>削除</th>
          </tr>
          <?php for ($i=0; $i < count($list); $i++) : ?>
           <tr class="trKws">
             <td class="tdNo"><?php echo h($i)+1 ; ?></td>
             <td class="tdCtg"><?php echo h($list[$i]['ctgry']); ?></td>
             <td class="tdKw">
                <?php
                  $dt = date("Y-m-d", strtotime($list[$i]['rgst']));
                  if( $dt == $dtYmd){
                    echo h($list[$i]['keyword']);
                  }
                  else{
                    echo "<a href=/seo/rank/daily.php?keywordNo=".$list[$i]['keyNo'].">".h($list[$i]['keyword'])."</a>";
                  }
                  ?>
             </td>
             <td class="tdRgst"><?php echo h($list[$i]['rgst']); ?></td>
             <td class="tdDel"><?php echo "<button type='button' name='button' value='".$list[$i]['keyNo']."'>削除</button>"; ?></td>
           </tr>
         <?php endfor; ?>
        </tbody>
      </table>
    </div>
  </body>
  <script>
      $(function(){
        $('#test').click(function() {
          var v = $('#test').val();
          alert(v);
        });
      });
    </script>
</html>
