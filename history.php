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
	function deleteData(id) 
	{
		if(confirm("確定要刪除嗎？刪除後的資料可以再建立一次找回"))
		{
			//儲存資料到資料庫
			$.ajax({
				url: "deleteData.php",
				type: "POST",
				data: 
				{
					ID:id
				},
				dataType: "text",
				success: function(result) 
				{
					if(result==1)
					{
						toastr.success('<p>資料刪除成功！</p>');
						setTimeout("location.href='history.php'",2000);
					}
					else if(result == 0)
						toastr.warning('<p>刪除資料出了點問題！</p><br>請聯絡工程師修護');
				}
			});
		}
	
		
		
	}	
	function rediret(data)
	{
		var form = document.createElement("form");
		form.setAttribute("method", "post");
		form.setAttribute("action", "getResult.php");

		var hiddenField = document.createElement("input");
		hiddenField.setAttribute("type", "hidden");
		hiddenField.setAttribute("name", "data");
		hiddenField.setAttribute("value", data);

		form.appendChild(hiddenField);

		document.body.appendChild(form);
		form.submit();
	}
</script>
<body>

<div class="container-fluid">
  <div class="row content">
    <div class="col-sm-3 sidenav">
      <h4>大學部入學成績搜尋</h4>
      <ul class="nav nav-pills nav-stacked">
        <li><a href="crawler.php">首頁</a></li>
        <li class="active"><a href="history.php">搜尋歷史</a></li>
		<li><a href="usingstep.pdf">使用手冊</a></li>
		<li><a href="https://freshman.tw/cross/107/021172">海大資工107榜單</a></li>
      </ul>
    </div>

    <div class="col-sm-9" height="500px">
		<div id="result">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>查詢名稱</th>
						<th>詳細資訊</th>
						<th>刪除資料</th>
					</tr>
				</thead>
				<tbody id="student_result">
				<?php
					//這邊要印出所有的資料!!
					include "connect.php";
					$sql = "select * from freshmen";
					if($stmt = $db->query($sql))
					{
						while($result = mysqli_fetch_object($stmt))
						{
							$data = json_decode($result->Data);
							echo '<tr><td>'.$data->{'title'}.'</td><td><form method="POST" action="getResult.php"><button type="submit" class="btn btn-lg btn-default">查看資訊</button><input name="data" value=\''.$result->Data.'\' type="hidden" /></form><dl><a href="result.php?ID='.$result->ID.'" class="btn btn-lg btn-default">下載excel資料表</a></td><td><button class="btn btn-lg btn-default" onclick=deleteData(\''.$result->ID.'\') >刪除資料</button></td></tr>';
						}
					}
				?>
				</tbody>
			</table>
		</div>
	</div>
  </div>
</div>

<footer class="container-fluid">
  <p>2018 - Copyright - JT</p>
</footer>

</body>
</html>
