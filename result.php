<?php

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
	die('This file should only be run from a Web Browser');

/** Include PHPExcel */
require_once dirname(__FILE__) . '/PHPExcel/Classes/PHPExcel.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

//************************************************************************************
//****************把查詢資料進行排列，分為三個表單，並創表供下載**********************
//************************************************************************************

//第一表是此校系與他校系之輸贏資訊
//第二表是其他學校比較

//先拿取資料，將會GET查詢ID進行表單搜尋與創建
include "connect.php";

$ID = $_GET['ID'];
$sql = "select * from freshmen where ID = '$ID'";
if($stmt = $db->query($sql))
{
	if($result = mysqli_fetch_object($stmt))//抓到資料了
	{
		
		$school_list=[];
		$data = json_decode($result->Data);//將儲存的json轉檔
				
		$title = $data->{'title'};
		$token = strtok($title, " ");
		$school_name = $token; //校名
		$token = strtok(" ");
		$depart_name = $token; //科系名
		
		// 創表
		//表一
		
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet(0)->setTitle("本校系與他校系PK");
		$objPHPExcel->getActiveSheet(0)->getColumnDimension('A')->setWidth(50);
		$objPHPExcel->getActiveSheet(0)->getColumnDimension('B')->setWidth(50);
		$objPHPExcel->getActiveSheet(0)->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet(0)->getColumnDimension('D')->setWidth(20);
		$objPHPExcel->getActiveSheet(0)->setCellValue('A1','本校名稱');
		$objPHPExcel->getActiveSheet(0)->setCellValue('B1','他校名稱');
		$objPHPExcel->getActiveSheet(0)->setCellValue('C1','本校獲勝');
		$objPHPExcel->getActiveSheet(0)->setCellValue('D1','他校獲勝');

		
		//表二
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet(1)->setTitle('其他校系PK資訊');
		$objPHPExcel->getActiveSheet(1)->getColumnDimension('A')->setWidth(100);
		$objPHPExcel->getActiveSheet(1)->getColumnDimension('B')->setWidth(20);
		$objPHPExcel->getActiveSheet(1)->setCellValue('A1','校系名');
		$objPHPExcel->getActiveSheet(1)->setCellValue('B1','次數');
		
		
		//必須先拿到前端的"school_list"，再把資料塞進表格內
			
		//抓出所有學生的志願序與選擇
		for($i = 0; $i < $data->{"count"};$i++)
		{
				
			$choose_school = [];
			$best_school = "";
			//結構是:choose_school[school1[name,choose],school2[name,choose]]
			//choose_school是每個學生的志願選擇，抓出資料後再進行比對
			//將會過濾沒有上榜的學校，沒上榜直接掰掰不列入考慮
				
			$count = count($data->{"student"}[$i]->{"wishList"});//取得志願數
				
			for($j = 0;$j < $count;$j++)//抓到全部的志願
			{
				if($data->{"student"}[$i]->{'wishList'}[$j]->{'result'} !== "沒有上")//確定有上後加入到school
				{
					$school = [];
					array_push($school,$data->{'student'}[$i]->{'wishList'}[$j]->{'school_name'});
					array_push($school,$data->{'student'}[$i]->{'wishList'}[$j]->{'choose'});
					array_push($choose_school,$school);
						
					if($data->{'student'}[$i]->{'wishList'}[$j]->{'choose'} == 1)
						$best_school = $data->{'student'}[$i]->{'wishList'}[$j]->{'school_name'};
				}
			}
				
				
			//處理學生的志願序，確認學校名稱後加入到school_list內
			for($k = 0;$k < count($choose_school);$k++)
			{
				if($best_school != $choose_school[$k][0] && $best_school!="")
				{
					if(!ifInSchoolList($best_school,$choose_school[$k][0],$school_list))//如果沒建立過就先建立PK資料！
					{
						$school = [$best_school,$choose_school[$k][0],0];
						array_push($school_list,$school);
					}
						
						
					//找到此學校在school_list的位置，並且判斷是否就讀，是就+1
					for($j = 0;$j < count($school_list);$j++)
					{
						if($school_list[$j][0] == $best_school && $school_list[$j][1] == $choose_school[$k][0])
						{
							$school_list[$j][2] += 1;
						}
					}
				}
			}
				
		}
			
		//排序school_list
		for($i = 0;$i < count($school_list);$i++)
		{
			for($j = $i+1;$j < count($school_list);$j++)
			{
				if($school_list[$i][2] < $school_list[$j][2]) //swap
				{
					$tmp = $school_list[$i];
					$school_list[$i] = $school_list[$j];
					$school_list[$j] = $tmp;
				}	
			}
		}
			
		$count0 = 2;
		$count1 = 2;
		$count2 = 2;
		$list = [];
		for($j = 0;$j < count($school_list);$j++)
		{
			
			if(strpos($school_list[$j][1],$school_name) !== false && strpos($school_list[$j][1],$depart_name) !== false)
			{
				//push到list中
				array_push($list,$school_list[$j]);
				
				//加入到page1
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.$count1,$school_list[$j][1]);
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.$count1,$school_list[$j][0]);
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.$count1,$school_list[$j][2]);
				
				//處理此校系名為token
				$token = strtok($school_list[$j][0]," ");
				$this_school_name = $token; //此校名
				$token = strtok(" ");
				$this_depart_name = $token; //此科系名
				
				$status = 0;
				for($i = 0;$i < count($school_list);$i++)
				{
					if(strpos($school_list[$i][0],$school_name) !== false && strpos($school_list[$i][0],$depart_name) !== false && strpos($school_list[$i][1],$this_school_name) !== false && strpos($school_list[$i][1],$this_depart_name) !== false)
					{
						//push到list中
						array_push($list,$school_list[$i]);
						
						//加入到page1
						$objPHPExcel->setActiveSheetIndex(0);
						$objPHPExcel->getActiveSheet(0)->setCellValue('C'.$count1,$school_list[$i][2]);
						$status = 1;
						break;
					}
				}
				if($status == 0)
				{
					$objPHPExcel->setActiveSheetIndex(0);
						$objPHPExcel->getActiveSheet(0)->setCellValue('C'.$count1,0);
				}
				
				$count1++;
			}
		}
		
		$count0 = 2;
		$count2 = 2;
		for($j = 0;$j < count($school_list);$j++)
		{
			if(!ifInSchoolList($school_list[$j][0],$school_list[$j][1],$list))
			{
				if(strpos($school_list[$j][0],$school_name) !== false && strpos($school_list[$j][0],$depart_name) !== false )
				{
					array_push($list,$school_list[$j]);
					
					$objPHPExcel->setActiveSheetIndex(0);
					$objPHPExcel->getActiveSheet(0)->setCellValue('A'.$count1,$school_list[$j][0]);
					$objPHPExcel->getActiveSheet(0)->setCellValue('B'.$count1,$school_list[$j][1]);
					$objPHPExcel->getActiveSheet(0)->setCellValue('C'.$count1,$school_list[$j][2]);
					$objPHPExcel->getActiveSheet(0)->setCellValue('D'.$count1,0);
					$count1++;
				}
				else
				{
					//加入到page2
					$objPHPExcel->setActiveSheetIndex(1);
					$objPHPExcel->getActiveSheet(1)->setCellValue('A'.$count2,$school_list[$j][0]."『勝』".$school_list[$j][1]);
					$objPHPExcel->getActiveSheet(1)->setCellValue('B'.$count2,$school_list[$j][2]);
					$count2++;
				}
			}
		}
		
		// Redirect output to a client’s web browser (Excel5)
		ob_end_clean();
		header("Content-type: text/html; charset=utf-8");
		header("Content-Type: application/vnd.ms-excel");
		header("Content-Disposition: attachment;filename=".str_replace(" ","_",$title.".xls"));
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
		
	}
	else
	{
		echo "查無此查詢資料！請返回上一頁確認資訊後再試一次！";
	}
}


function ifInSchoolList($school1,$school2,$school_list)
{
	for($i = 0;$i < count($school_list);$i++)
	{
		if($school1 == $school_list[$i][0] && $school2 == $school_list[$i][1])
			return true;
	}
	return false;
}

?>