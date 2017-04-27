<?php
  session_start();

  include("config.php");
  include("db_connect.php");
  include("functions.php");

  $keywordNo = $_GET['keywordno']; //@TODO　バリデーション
  //$limitS    = $_GET['limitS'];
  //$limitE    = $_GET['limitE'];
  $limitS = date("Ymd", strtotime('first day of +0 month'));
  $limitSY = date("Y");
  $limitSm = date("m");
  // $limitSY = 2016;
  // $limitSm = 2;
  $limitSd = 1;
  //$todayD = date("d");
  $todayD = 15;
  $limitE = date("Ymd", strtotime('last day of +0 month'));

  //keyword
  $keyword = getKeywordFromKeywordNo($pdo, $keywordNo);

  //ranking
  $sql =
  "SELECT
      no,
      ".makeRankSelect(MAX_SITE)."
      rgst
    FROM
      `ranking`
    WHERE
      keywords_no = :keyNo";
  $stmt = $pdo -> prepare($sql);
  $stmt -> bindValue(':keyNo', $keywordNo, PDO::PARAM_INT);
  $stmt -> execute();
  $dbList = array();
  while($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
    $dbList[] = ['rank1' => $row['rank1']];
    // $dbList[] = [makeDbList(MAX_SITE, $row)];
  }
  $rank1list = array_column($dbList, 'rank1');

  var_dump($dbList);

  function makeDbList($maxNum, $row){
    $eachDbList = array();
    for ($i=1; $i <= $maxNum ; $i++) {
      //$rank = "'rank".$i."'";
      //$eachDbList[] = "['rank".$i."' => $row['rank".$i."']]";
    }
    return implode(",", $eachDbList);
  }

  //var_dump($rank1list);
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
        今月の推移 <?php echo $limitS; ?> ~ <?php echo $limitE; ?>
      </div>
      <div class="">
        キーワード：<?php echo $keyword;?>
      </div>
      <table id="monthly">
        <tbody>
          <tr>
            <th>順位</th>
            <?php
            while (checkdate($limitSm, $limitSd, $limitSY)) {
              echo "<th>$limitSd</th>";
              $limitSd++;
            }
             ?>
          </tr>

          <tr>
           <td>1</td>
           <?php
            for ($i=0; $i < count($rank1list); $i++) {
                echo "<td>$rank1list[$i]</td>";
            }
           ?>
          </tr>

          <tr>
           <td>2</td>
           <td>G</td>
           <td>H</td>
           <td>I</td>
           <td>J</td>
           <td>K</td>
           <td>L</td>
          </tr>
          <tr>
           <td>3</td>
           <td>M</td>
           <td>N</td>
           <td>O</td>
           <td>P</td>
           <td>Q</td>
           <td>R</td>
          </tr>
        </tbody>
      </table>
    </div>
  </body>
</html>
