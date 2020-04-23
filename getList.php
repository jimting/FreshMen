<?php
//TODO : 取得頁面資料，回傳所需資料的Json。
$url = $_GET['url'];
$html = file_get_contents($url);
$dom = new DOMDocument();
@$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
// grab all the on the page


$xpath = new DOMXPath($dom);

//這裡抓到標題列，確認有幾個欄位
$hrefs = $xpath->evaluate("/html/body//tr");
$title = $xpath->evaluate("/html/head/title");
//第一個item是thead的，跳過～從$i = 1開始爬
//json結構:{"student":[{"name":"王大明","this_school":"正5","school_count":3,"area":"基隆市考區","number":"123456","wishList":[{"school_name":"x1大學","result":"正1","choose":false},{"school_name":"x2大學","result":"備3","choose":true}]}]}

$string = "";

//資訊儲存宣告
$name = "";
$this_school="";
$school_count = 0;
$area = "";
$number = "";
$wishList = array();
$schoolList = array();
$count = 0;
echo "{\"title\":\"".$title->item(0)->nodeValue."\",\"student\":[";
for ($i = 1; $i < $hrefs->length; $i++) 
{
	//特定參數初始化
	$schoolList = array();
	$school_name = "";
	$result = "";
	$choose = 0;
	
	$href = $hrefs->item($i);
	
	$status = 0;
	//這邊要檢查是否是showPhoto(包含姓名的tr)
	$classCheck = $href->getAttribute("class");
	if(strpos($classCheck, 'showPhoto') !== false)
	{
		$status = 1;
	}
	
	//含有<span class="crown"></span>的是學生所選擇的的志願
	
	switch($status)
	{
		case 1://代表是姓名那欄
		
			if($name !== "" || $i == $hrefs->length-1)//如果非第一個同學 或是 如果是最後一個td，加上結束符號
			{
				$string .= "{\"name\":\"".$name."\",\"this_school\":\"".$this_school."\",\"school_count\":\"".$school_count."\",\"area\":\"".$area."\",\"number\":\"".$number."\",\"wishList\":[";
				
				foreach($wishList as $k)
				{
					$string .= "{\"school_name\":\"".$k[0]."\",";
					$string .= "\"result\":\"".$k[1]."\",";
					$string .= "\"choose\":\"".$k[2]."\"}";
					if(array_search($k, $wishList) != count($wishList)-1)
						$string .= ",";
					
				}
				
				$string .= "]},";
				
				$count++;
				//輸出結果後初始化參數
				//資訊儲存宣告
				$name = "";
				$this_school="";
				$school_count = 0;
				$area = "";
				$number = "";
				$wishList = array();
			}
			
			//td[0]:span:他在查詢學校的正取備狀態
			//td[1]:span:准考證號 / div:考區
			//td[2]:span:隱藏的資訊(總共報了幾間學校) 純文字:名字
			//td[3]:a:上的學校與科系 / 如果有span:代表是最後選擇的志願
			//td[4]:此志願的正備取狀態
			$td=$href->getElementsByTagName('td');
			//開始抓取值
			$this_school = $td->item(0)->nodeValue;
			if($this_school == null)
				$this_school = "沒有上";
			$number = $td->item(1)->getElementsByTagName('span')->item(0)->nodeValue;
			$school_count = $td->item(2)->getElementsByTagName('span')->item(0)->nodeValue;
			$name = substr($td->item(2)->nodeValue,0,-1);//把後面的志願數拿掉XD
			if($name == null)
				$name = "查無此人姓名";
			$area = $td->item(1)->getElementsByTagName('div')->item(0)->nodeValue;
			$wishCheckTmp = $dom->saveHTML($td->item(3));
			if(strpos($wishCheckTmp, 'crown') !== false)//代表選擇了此間學校
				$choose = 1;
			$school_name = $td->item(3)->getElementsByTagName('a')->item(0)->nodeValue;
			$result = $td->item(4)->nodeValue;
			if($result == null)
				$result = "沒有上";
			
			array_push($schoolList,$school_name,$result,$choose);
			array_push($wishList,$schoolList);
			
			break;
			
		case 0://代表不是姓名那欄
			//td[0]:a:上的學校與科系 / 如果有span:代表是最後選擇的志願
			//td[1]:此志願的正備取狀態
			$td=$href->getElementsByTagName('td');
			$wishCheckTmp = $dom->saveHTML($td->item(0));
			if(strpos($wishCheckTmp, 'crown') !== false)//代表選擇了此間學校
				$choose = 1;
			$school_name = $td->item(0)->getElementsByTagName('a')->item(0)->nodeValue;
			$result = $td->item(1)->nodeValue;
			if($result == null)
				$result = "沒有上";
			
			array_push($schoolList,$school_name,$result,$choose);
			array_push($wishList,$schoolList);
			break;
	}
	
}
$string = substr($string,0,-1);
$string .= "],\"count\":".$count."}";
echo $string;
?>