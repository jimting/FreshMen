<!DOCTYPE html>
<html lang="en">
<head>
  <title>成績搜尋</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="toastr.css" type="text/css" media="screen" />
  <script src="./toastr.js"></script>
  <style>
    /* Set height of the grid so .sidenav can be 100% (adjust if needed) */
    .row.content {height: 1500px}
    
    /* Set gray background color and 100% height */
    .sidenav {
      background-color: #f1f1f1;
      height: 100%;
    }
    
    /* Set black background color, white text and some padding */
    footer {
      background-color: #555;
      color: white;
      padding: 15px;
    }
    
    /* On small screens, set height to 'auto' for sidenav and grid */
    @media screen and (max-width: 767px) {
      .sidenav {
        height: auto;
        padding: 15px;
      }
      .row.content {height: auto;} 
    }
  </style>
</head>
<script>
	var title;
	var student;
	var userJsonData;
	
	toastr.options = {
				positionClass: 'toast-top-full-width',
			}
	$(document).ready(function()
	{
				
		$("#StartBtn").click(function()
		{
			toastr.success('<strong>資料處理中，請耐心等候</strong>');
			$.ajax({
				url: "getList.php",
				type: "GET",
				data: 
				{
					url:$('#inputURL').val()
				},
				dataType: "json",
				success: function(result) 
				{
					
					if(result["count"] > 0)
					{
						toastr.success('<strong>抓取資料成功！</strong><br><p>請繼續資料處理步驟</p>');
						title = result["title"];
						userJsonData = JSON.stringify(result);
						
						//清空result
						$('#result').text("");
						$('#student').text("");
						$('#info').text("");
						
										
						$('#result').append("<h2><strong>"+title+"</strong></h2>");
						
						$('#result').append("<h2><strong>總共報考學生數:"+result["count"]+"人</strong></h2>");
						
						$('#result').append('<form action="javascript:saveAndRedirect(\'getResult.php\');"><button id="getResultBtn" type="submit" class="btn btn-lg btn-default">開始計算結果</button><input type="hidden" name="data" value=\''+userJsonData+'\'/></form>');
						
						
						$('#student').text("");
						
						$('#student').append('<table class="table table-hover"><thead><tr><th>排序</th><th>考生姓名</th><th>本校正備取狀態</th><th>最終選擇學校</th><th>詳細資訊</th></tr></thead><tbody id="student_result">');
						////json結構:{"student":[{"name":"王大明","this_school":"正5","school_count":3,"area":"基隆市考區","number":"123456","wishList":[{"school_name":"x1大學","result":"正1","choose":false},{"school_name":"x2大學","result":"備3","choose":true}]}]}
						
						for(var i = 0; i < result["count"];i++)
						{
							
							//產生隱藏資訊
							var tmpString = "";
							var choose_school = "";
							tmpString += '<div id="s'+i+'" class="modal fade" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">'+result["student"][i]["name"]+'</h4></div><div class="modal-body"><p>准考證號碼:'+result["student"][i]["number"]+'</p><p>考區:'+result["student"][i]["area"]+'</p><p>總共報了'+result["student"][i]["school_count"]+'間學校</p><p>志願:</p>';
							
							var count = Object.keys(result["student"][i]["wishList"]).length;
							
							for(var j = 0;j < count;j++)
							{
								tmpString+='<p>'+result["student"][i]["wishList"][j]["school_name"]+':'+result["student"][i]["wishList"][j]["result"]+":";
								if(result["student"][i]["wishList"][j]["choose"] == 1)
								{
									tmpString+='就讀</p>';
									choose_school = result["student"][i]["wishList"][j]["school_name"];
								}
								else
									tmpString+='放棄</p>';
							}
							tmpString+='</div></div></div></div>';
							$('#info').append(tmpString);
							
							$('#student_result').append('<tr><td>'+i+'</td><td>'+result["student"][i]["name"]+'</td><td>'+result["student"][i]["this_school"]+'</td><td>'+choose_school+'</td><td><button type="button" data-toggle="modal" data-target="#s'+i+'" class="btn btn-lg btn-default">查看資訊</button></td></tr>');
							
						}
						
						$('#student').append('</tbody></table>');
						
						
									
					}
					else
					{
						toastr.warning('<strong>請確認網址再試一次</strong><br><p>是否複製錯網址了？</p>');
					}
				},
				error: function() 
				{
					toastr.warning('<strong>請輸入正確的網址再試一次</strong><br><p>這個網址無法正常打開</p>');
				}
			});
							
		});
				
	});
	function saveAndRedirect(path) 
	{
		//儲存資料到資料庫
		$.ajax({
			url: "insertData.php",
			type: "POST",
			data: 
			{
				data:userJsonData
			},
			dataType: "json",
			success: function(result) 
			{
				if(result==1)
				{
					toastr.success('<p>資料儲存成功！</p>');
					
					var form = document.createElement("form");
					form.setAttribute("method", "post");
					form.setAttribute("action", path);

					var hiddenField = document.createElement("input");
					hiddenField.setAttribute("type", "hidden");
					hiddenField.setAttribute("name", "data");
					hiddenField.setAttribute("value", userJsonData);

					form.appendChild(hiddenField);

					document.body.appendChild(form);
					form.submit();
				}
				else if(result == 0)
					toastr.warning('<p>存入資料庫出了點問題！</p><br>請聯絡工程師修護');
					
			}
		});
	
		
		
	}	
</script>
<body>

<div class="container-fluid">
  <div class="row content">
    <div class="col-sm-3 sidenav">
      <h4>大學部入學成績搜尋</h4>
      <ul class="nav nav-pills nav-stacked">
        <li class="active"><a href="crawler.php">首頁</a></li>
        <li><a href="history.php">搜尋歷史</a></li>
		<li><a href="usingstep.pdf">使用手冊</a></li>
		<li><a href="https://freshman.tw/cross/107/021172">海大資工107榜單</a></li>
      </ul><br>
      <div class="input-group">
        <input type="text" class="form-control" id="inputURL" placeholder="輸入搜尋網址">
        <span class="input-group-btn">
          <button class="btn btn-default" type="button" id="StartBtn">
            <span class="glyphicon glyphicon-search"></span>
          </button>
        </span>
      </div>
    </div>

    <div class="col-sm-9">
		<div id="result"></div>
		
		<div id="student"></div>
		
		<div id="info"></div>
		
    </div>
  </div>
</div>

<footer class="container-fluid">
  <p>2018 - Copyright - JT</p>
</footer>

</body>
</html>
