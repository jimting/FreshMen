<?php
//資料庫設定
//資料庫位置
$db_server = "海大資工資料庫IP";
//資料庫名稱
$db_name = "freshmen";
//資料庫管理者帳號
$db_user = "資料庫帳號XD";
//資料庫管理者密碼
$db_passwd = "資料庫密碼";

//對資料庫連線
$db = mysqli_connect($db_server, $db_user, $db_passwd, $db_name);
if(mysqli_connect_errno($db))
	echo "無法對資料庫連線！" . mysqli_connect_error();
//        die("無法對資料庫連線");

//資料庫連線採UTF8
mysqli_set_charset($db,'utf8');

//選擇資料庫
if(!@mysqli_select_db($db,$db_name))
        die("無法使用資料庫");
?>