<?php
	//存到資料庫裡
	include "connect.php";
	$data = $_POST['data'];
	$sql = "insert into freshmen (Data) values ('$data')";
	if(mysqli_query($db,$sql))
	{
		echo 1;
		return;
	}
	echo 0;
	return;
?>