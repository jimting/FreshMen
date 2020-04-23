<!DOCTYPE html>
<html lang="en">
<head>
	<title>結果</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="toastr.css" type="text/css" media="screen" />
	<script src="./toastr.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
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
	var result = <?php echo $_POST['data'];?>;
	var school_list = []; //結構是: school_list[PK1[school1_name,school2_name,point],PK2[school1_name,school2_name,point]] 利用point來比大小
	//point 是 school1 > school2的分數
	toastr.options = {positionClass: 'toast-top-full-width'};
	
	function init()
	{
		//例如有一位學生報名5間學校：ABCDE，最後錄取ACE間，其中他選擇C，則C>A、C>E
		//想知道本系>哪些系
		//以及哪些系>本系
		
		if(result["count"] > 0)
		{
			//資料標題
			var title = result['title'];
			var token = title.split(" ");
			var school_name = token[0];//校名
			var depart_name = token[1];//科系名
			var labels=[];
			var data=[];
			
			//抓出所有學生的志願序與選擇
			for(var i = 0; i < result["count"];i++)
			{
				
				var choose_school = [];
				var best_school = "";
				//結構是:choose_school[school1[name,choose],school2[name,choose]]
				//choose_school是每個學生的志願選擇，抓出資料後再進行比對
				//將會過濾沒有上榜的學校，沒上榜直接掰掰不列入考慮
				
				var count = Object.keys(result["student"][i]["wishList"]).length;//取得志願數
				
				for(var j = 0;j < count;j++)//抓到全部的志願
				{
					if(result["student"][i]["wishList"][j]["result"] !== "沒有上")//確定有上後加入到school
					{
						var school = [];
						school.push(result["student"][i]["wishList"][j]["school_name"]);
						school.push(result["student"][i]["wishList"][j]["choose"]);
						choose_school.push(school);
						
						if(result["student"][i]["wishList"][j]["choose"] == 1)
							best_school = result["student"][i]["wishList"][j]["school_name"];
					}
				}
				
				//alert(choose_school);				
				
				//處理學生的志願序，確認學校名稱後加入到school_list內
				for(var k = 0;k < choose_school.length;k++)
				{
					if(best_school != choose_school[k][0] && best_school!="")
					{
						var PK = best_school+">"+choose_school[k][0];
						if(!ifInSchoolList(best_school,choose_school[k][0],school_list))//如果沒建立過就先建立PK資料！
						{
							school = [best_school,choose_school[k][0],0];
							school_list.push(school);
						}
						
						
						//找到此學校在school_list的位置，並且判斷是否就讀，是就+1
						for(var j = 0;j < school_list.length;j++)
						{
							if(school_list[j][0] == best_school && school_list[j][1] == choose_school[k][0])
							{
								school_list[j][2] += 1;
							}
						}
					}
				}
				
			}
			
			//排序school_list
			for(var i = 0;i < school_list.length;i++)
			{
				for(var j = i+1;j < school_list.length;j++)
				{
					if(school_list[i][2] < school_list[j][2]) //swap
					{
						var tmp = school_list[i];
						school_list[i] = school_list[j];
						school_list[j] = tmp;
					}
						
				}
			}
			var list = [];
			for(var j = 0;j < school_list.length;j++)
			{
				if(school_list[j][1].indexOf(school_name) !== -1 && school_list[j][1].indexOf(depart_name) !== -1)
				{
					list.push(school_list[j]);
					var loss = 0;
					var win = 0;
					var resultString = "";
					resultString+="<td>"+school_list[j][1]+"</td><td>"+school_list[j][0]+"</td><td>"+school_list[j][2]+"</td>";
					loss = school_list[j][2];
					var status = 0;
					
					//處理此校名為token
					var token = school_list[j][0].split(" ");
					var this_school_name = token[0];//校名
					var this_depart_name = token[1];//科系名
					//alert(this_school_name);
					for(var i = 0;i < school_list.length;i++)
					{
						if(school_list[i][0].indexOf(school_name) !== -1 && school_list[i][0].indexOf(depart_name) !== -1 &&school_list[i][1].indexOf(this_school_name) !== -1 && school_list[i][1].indexOf(this_depart_name) !== -1 )
						{
							list.push(school_list[i]);
							resultString+="<td>"+school_list[i][2]+"</td>";
							status = 1;
							win = school_list[i][2];
							break;
						}
					}
					if(status == 0)
					{
						resultString+="<td>0</td>";
						win = 0;
					}
					if(loss > win)
						$('#result').append("<tr class='danger'>"+resultString+"</tr>");
					else if(loss <= win)
						$('#result').append("<tr class='success'>"+resultString+"</tr>");
				}
			}
			for(var j = 0;j < school_list.length;j++)
			{
				if(!ifInSchoolList(school_list[j][0],school_list[j][1],list))
				{
					if(school_list[j][0].indexOf(school_name) !== -1 && school_list[j][0].indexOf(depart_name) !== -1)
					{
						
						list.push(school_list[j]);
						$('#result').append("<tr class='success'><td>"+school_list[j][0]+"</td><td>"+school_list[j][1]+"</td><td>0</td><td>"+school_list[j][2]+"</td></tr>");
						
					}
					else
					{
						list.push(school_list[j]);
						$('#result3').append("<tr><td>"+school_list[j][0]+"<span style='color:blue;font-size:30px;'>勝</span>"+school_list[j][1]+"</td><td>"+school_list[j][2]+"</td></tr>");
					}
				}
			}
			
			toastr.success('<p>計算結果出爐啦！</p>');
			
		
			//圖表顯示
			
			$('#this_school').append(school_name+" "+depart_name+"與其他校系大比拚");
				
				
			//第一個pie顯示，就讀本校+系和非就讀本校+系的
			labels=[school_name+" "+depart_name,"其他校系"];
			data=[0,0];
			for(var j = 0;j < school_list.length;j++)
			{
				if(school_list[j][0].indexOf(school_name) !== -1 && school_list[j][0].indexOf(depart_name) !== -1)
				{
					data[0]+=1;
				}
				else
				{
					data[1]+=1;
				}
			}
			
			var ctx = document.getElementById('canvasPie1').getContext('2d');
			var pieChart = new Chart(ctx, {
				type: 'pie',
				data : {
				  labels:labels,
				  datasets: [{
					  //預設資料
					  data:data,
					  backgroundColor: [
					  //資料顏色
									"#00A1FF",
									"#FF0004"
					],
				}],
			   }
		   });
		   
		   //第二個pie顯示，就讀本校和非就讀本校的
		   labels=[school_name,"其他學校"];
			data=[0,0];
			for(var j = 0;j < school_list.length;j++)
			{
				if(school_list[j][0].indexOf(school_name)!==-1)
				{
					data[0]+=1;
				}
				else
				{
					data[1]+=1;
				}
			}
		   
			var ctx = document.getElementById('canvasPie2').getContext('2d');
			var pieChart = new Chart(ctx, {
				type: 'pie',
				data : {
				  labels:labels,
				  datasets: [{
					  //預設資料
					  data:data,
					  backgroundColor: [
					  //資料顏色
									"#00A1FF",
									"#FF0004"
					],
				}],
			   }
		   });
		   
		   //第三個pie顯示，就讀本系和非就讀本系的
		   labels=[depart_name,"其他科系"];
			data=[0,0];
			for(var j = 0;j < school_list.length;j++)
			{
				if(school_list[j][0].indexOf(depart_name)!==-1)
				{
					data[0]+=1;
				}
				else
				{
					data[1]+=1;
				}
			}
		   
			var ctx = document.getElementById('canvasPie3').getContext('2d');
			var pieChart = new Chart(ctx, {
				type: 'pie',
				data : {
				  labels:labels,
				  datasets: [{
					  //預設資料
					  data:data,
					  backgroundColor: [
					  //資料顏色
									"#00A1FF",
									"#FF0004"
					],
				}],
			   }
		   });
		}
		else
		{
			toastr.warning('<strong>請返回再試一次</strong>');
		}

	}
			
	function ifInSchoolList(school1,school2,list)
	{
		for(var i = 0;i < list.length;i++)
		{
			if(school1 == list[i][0] && school2 == list[i][1])
				return true;
		}
		return false;
	}
	
</script>
<body onload=init()>

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

    <div class="col-sm-9">
		<div>
			<div style="width:33%;height:400px;float:left;">
				<p style="text-align:center;"><strong>本校系與其他校系之比例圖表</strong></p>
				<canvas id="canvasPie1"></canvas>
			</div>
			<div style="width:33%;height:400px;float:left;">
			<p style="text-align:center;"><strong>本校與他校之比例圖表</strong></p>
				<canvas id="canvasPie2"></canvas>
			</div>
			<div style="width:33%;height:400px;float:left;">
			<p style="text-align:center;"><strong>本系與他系之比例圖表</strong></p>
				<canvas id="canvasPie3"></canvas>
			</div>
			<br>
		</div>
		<br>
		
		<div>
			<table class="table table-hover">
				<strong><caption id="this_school" style="color:blue;font-size:40px;"></caption></strong>
				<thead>
					<tr>
						<th>本校</th>
						<th>他校</th>
						<th>輸</th>
						<th>贏</th>
					</tr>
				</thead>
				<tbody id="result">
				</tbody>
			</table>
		</div>
		<br><br>
		<div>
			<table class="table table-hover">
				<strong><caption style="color:green;font-size:40px;">其他學校比拚</caption></strong>
				<thead>
					<tr>
						<th>學校名稱</th>
						<th>總數</th>
					</tr>
				</thead>
				<tbody id="result3">
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
