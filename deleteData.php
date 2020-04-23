<?php
	//存到資料庫裡
	include "connect.php";
	$ID = $_POST['ID'];
	$sql = "DELETE FROM freshmen WHERE ID = '$ID'";
	if(mysqli_query($db,$sql))
	{
		echo 1;
		return;
	}
	echo 0;
	return;
?>