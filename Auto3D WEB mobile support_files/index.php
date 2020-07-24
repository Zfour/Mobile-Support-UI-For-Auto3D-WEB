
<!doctype html>

<html lang="ja-JP">

 <head>
  <title>Auto3D WEB</title>
  <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" />
  <link rel="stylesheet" href="css/auto3d.css" type="text/css" />
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Cache-Control" content="no-cache">
  <meta charset="utf-8"/>
 </head>

 <body>
  <div class="container-fluid">

   <h1>Auto3D WEB</h1>

   <div align="right">ようこそ！<u>499984532@qq.com</u> さん </a><span style><a href="logout.php">［ログアウト］</a></span></div>
   <div align="right"><a href="../contact.html" target="_new">［お問い合わせ］</a><span style><a href="../index.html" target="_new">［MDDｸﾘｴｲﾃｨﾌﾞHP］</a></span></div>
   

   <div class="row">

    <div class="col-lg-4">
     <div class="box2">
      <h3>1.パッケージの種類を選択</h3>
      <div class="row">
       <div class="col-sm-12">
        <select id="packageSelect" name="packageSelect" size="5" onclick="packageSelectOnClick()">
         <option value="0">ミルクカートン1000ml</option>
         <option value="1">ミルクカートン500ml</option>
        </select>
        <br>
        <br>
       </div>
       <div class="col-sm-12">
        <div id="readme_container"></div>
       </div>
      </div>
     </div>
    </div>

    <div class="col-lg-4">
     <div class="box2">
      <h3>2.詳細設定</h3>
      <div id="setting_container"></div>
      <img id="preview1" style="width: 300px;">
      <img id="preview2" style="width: 300px;">
     </div>
    </div>

    <div class="col-lg-4">
     <div class="box2">
      <h3>3.3DCG 計算</h3>
        <td>
        プレビューが表示されたら計算終了<br>※所要時間:通常30秒～1分
       </td>
       <br><br>
      <div style="text-align:center">
       <button class="square_btn" id="startAuto3D" onclick="startAuto3DOnClick()" style="font-size:1.2em">計算開始</button><br>
       <br>
       <img id="thumbnail" src="blank.png" />
      </div>
     </div>
 
     <div class="box2">
      <h3>4.保存</h3>
      画像データ１式（zip）をダウンロード<br>※回せる3D画像(Smart3D.html)はzip解凍後に閲覧可<br><br>
      <div style="text-align:center">
       <button class="square_btn" id="saveImage" onclick="saveImageOnClick()" style="font-size:1.2em">保存</button>
      </div>
      <br>
     </div>
     </div>

   </div>

  </div>

  <footer class="footer">
   <div class="container">
    <div style="text-align:center;" >Copyright &copy; 2020 MDD Creative All Rights Reserved.</div>
   </div>
  </footer>

 </body>

 <script type="text/javascript" src="js/bootstrap.min.js"></script>
 <script type="text/javascript" src="js/jquery-1.11.0.js"></script>
 <script type="text/javascript" src="js/adapter.js"></script>
 <script type="text/javascript" src="js/zip.js"></script>
 <script type="text/javascript">

  var downloadUrl  = "";
  var downloadData = null;

  window.onload = function()
  {
    selectPackage(0);

    $('#saveImage').attr('disabled', true);

    zip.workerScriptsPath = './js/';
  }

  function packageSelectOnClick()
  {
    selectPackage($('#packageSelect').val());
    $("#preview1").css('width', '0px');
    $("#preview2").css('width', '0px');
  }

  function startAuto3DOnClick()
  {
    var err = '';
     if ($('input[name="パッケージ名"]').val() == '')
      err += "・パッケージ名が空です。\n";
    if ($('input[name="image_front"]').val() == '')
      err += "・デザイン画像が未指定です。\n";

    if (err != '') {
      alert(err);
      reutrn;
    }

    $('#startAuto3D').attr('disabled', true);
    $('#saveImage').attr('disabled', true);
    $('#thumbnail').attr('width', '24px');
    $('#thumbnail').attr('height', '24px');
    $('#thumbnail').attr('src', './progress.gif');

    var formData = new FormData();  //($('#conditionForm').get()[0]);

    formData.append('pass_code', $('input[name="pass_code"]').val());

    if ($('input[name="image_front"]').val() !== undefined && $('input[name="image_front"]').val() !== '')
      formData.append('image_front', $('input[name="image_front"]').prop("files")[0]);

    if ($('input[name="image_back"]').val() !== undefined && $('input[name="image_back"]').val() !== '')
      formData.append('image_back', $('input[name="image_back"]').prop("files")[0]);

    if ($('input[name="image_design"]').val() !== undefined && $('input[name="image_design"]').val() !== '')
      formData.append('image_design', $('input[name="image_design"]').prop("files")[0]);

    formData.append('condition', encodeURI(makeCondition()));


    var xhr = new XMLHttpRequest();
    xhr.open('POST', "https://mdd-creative2.com/auto3d.php", true);
    xhr.responseType = 'blob';
    xhr.onload = function(e)
    {
      if (xhr.status == 200)
      {
        downloadData = xhr.response;
        if (downloadData.size < 500)
          serverError();

        zip.createReader(new zip.BlobReader(downloadData), function(reader)
        {
          reader.getEntries(function(entries)
          {
            for(i = 0; i < entries.length; ++i)
            {
              if (entries[i].filename.indexOf("Preview.jpg") == -1)
                continue ;

              entries[i].getData(new zip.Data64URIWriter("image/jpeg"), function(data)
              {
                $('#thumbnail').attr('width', '60%');
                $('#thumbnail').attr('height', '60%');
                $('#thumbnail').attr('src', data);
                reader.close(function() {});
              });

              break;
            }
          });
        }, function(error) {});
      }
      else
        serverError();

      $('#saveImage').attr('disabled', false);
      $('#startAuto3D').attr('disabled', false);
    };
    xhr.ontimeout = function()
    {
      if ($('#thumbnail').attr('src') == './progress.gif')
        serverError();

      $('#saveImage').attr('disabled', false);
      $('#startAuto3D').attr('disabled', false);
    }

    xhr.send(formData);
  }

  function saveImageOnClick()
  {
     if (window.navigator.msSaveBlob)
     {
       window.navigator.msSaveBlob(downloadData, "auto3d.zip");
     }
     else
     {
       if (downloadUrl !== "")
         (window.URL || window.webkitURL).revokeObjectURL(downloadUrl);
       downloadUrl = (window.URL || window.webkitURL).createObjectURL(downloadData);

       let link = document.createElement('a');
       link.setAttribute("type", "hidden");
       link.download = "auto3d.zip";
       link.href = downloadUrl;
       document.body.appendChild(link);
       link.click();
       link.remove();
     }
  }

  function selectPackage(packageIndex)
  {
    var packageReadme = 'package'+packageIndex+'_readme.html';
    var packageSetting = 'package'+packageIndex+'_setting.html';
    $('#readme_container').load(packageReadme);
    $('#setting_container').load(packageSetting);
  }

  function makeCondition()
  {
    var cond = '';
    cond += '変数名,値\r\n';

    cond += "UserId,";
    cond += "499984532@qq.com";
    cond += "\r\n";

    cond += "パッケージタイプ,";
    cond += $('input[name="パッケージタイプ"]').val();
    cond += "\r\n";

    cond += "パッケージ名,";
    cond += $('input[name="パッケージ名"]').val();
    cond += "\r\n";

    cond += appendCondition("幅");
    cond += appendCondition("高さ");
    cond += appendCondition("奥行き");
    cond += appendCondition("幅・奥行き比率");
    cond += appendCondition("ギザギザ大きさ");
    cond += appendCondition("ヘッダ長さ");
    cond += appendCondition("シール幅１");
    cond += appendCondition("シール幅２");
    cond += appendCondition("シール幅３");
    cond += appendCondition("明るさ");
    cond += appendCondition("デザインタイプ", "select");

    return cond;
  }

  function appendCondition(type, tag_name="input")
  {
    var cond = "";
    cond += type+",";
    if ($(tag_name+'[name='+type+']').val() === undefined || $(tag_name+'[name='+type+']').val() === '')
      cond += "0";
    else
      cond += $(tag_name+'[name='+type+']').val();
    cond += "\r\n";
    return cond;
  }

  function getType(v)
  {
    return Object.prototype.toString.call(v);
  }

  function serverError()
  {
    alert("サーバーが応答しません。");
    $('#thumbnail').attr('width', '10px');
    $('#thumbnail').attr('height', '10px');
    $('#thumbnail').attr('src', './blank.png');
  }

 </script>

</html>
