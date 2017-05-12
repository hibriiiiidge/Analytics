$(function(){
  //キーワード追加ボタンがクリックされたら
  $('body').on('click', '#addKw', addKeyword);
  //削除ボタンがクリックされたら
  $('body').on('click', 'button', delKeyword);


  /**
   * キーワード追加ボタンがクリックされたら
   */
  function addKeyword(){
    //@TODO kwのバリデーション
    var ctgNo = $("#categoryNo").val();
    var kw    = $("#kw").val();
    $("tr.trKws:last").after($('<tr>').attr({class:'trKws'}));
    $("#kw").val("");
    $.ajax({
      url: '/seo/api/addKeyword.php',
      type: 'post',
      dataType: 'json',
      async: false,
      data:{
        ctgNo: ctgNo,
        kw: kw
      }
    })
    .done(function(res){
      if(res.res == "exist"){ alert("既に存在します。"); return false;}
      if(res.res == "success"){
        //console.log(res.list);

        var today = new Date();
        var todayM = ("0"+(today.getMonth()+1)).slice(-2);
        var todayD = ("0"+today.getDate()).slice(-2);
        var todayDt = (today.getFullYear()+"-"+todayM+"-"+todayD);

        $(".trKws").html("");
        var listAry = res.list;
        makeTable(listAry, todayDt);
      };
    })
    .fail(function(res){
      alert("登録できませんでした。システム管理者まで問い合わせ下さい。");
    });
  }

  /**
   * 削除ボタンがクリックされたら
   */
  function delKeyword(){
    var kwNo = $(this).val();
    $.ajax({
      url: '/seo/api/delKeyword.php',
      type: 'post',
      dataType: 'json',
      async: false,
      data:{
        kwNo: kwNo
      }
    })
    .done(function(res){
      if(res.res == "success"){
        //console.log(res.list);
        var today = new Date();
        var todayM = ("0"+(today.getMonth()+1)).slice(-2);
        var todayD = ("0"+today.getDate()).slice(-2);
        var todayDt = (today.getFullYear()+"-"+todayM+"-"+todayD);

        $(".trKws").html("");
        var listAry = res.list;
        makeTable(listAry, todayDt);
      };
    })
    .fail(function(res){
      alert("削除できませんでした。システム管理者まで問い合わせ下さい。");
    });
  }

  /**
   * テーブルを生成する処理
   * @param array listAry res.list
   */
  function makeTable(listAry, todayDt){
    $.each(listAry, function(i, val){
      $(".trKws").eq(i).append($('<td>').text(i+1).attr({class:'tdNo'}));
      $(".trKws").eq(i).append($('<td>').text(val.ctgry).attr({class:'tdCtg'}));
      $(".trKws").eq(i).append($('<td>').attr({class:'tdKw'}));
      var dt = (val.rgst).slice(0, 10);
      if(dt ==  todayDt){
        $(".tdKw").eq(i).closest(".tdKw").append($('<a>').text(val.keyword));
      }
      else{
        $(".tdKw").eq(i).closest(".tdKw").append($('<a>').text(val.keyword).attr({href:"/seo/rank/daily.php?keywordno="+val.keyNo}));
      }

      $(".trKws").eq(i).append($('<td>').text(val.rgst).attr({class:'tdRgst'}));
      $(".trKws").eq(i).append($('<td>').html('<button type="button" name="button" value="'+val.keyNo+'">削除</button>').attr({class:'tdDel'}));
    });
  }
});
