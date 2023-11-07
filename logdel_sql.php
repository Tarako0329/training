<?php
require "config.php";
//require "functions.php";
//トランザクション処理

//結果書き込み
if(isset($_SESSION['USER_ID'])){
	$id = $_SESSION['USER_ID'];
	decho ("session:".$id);
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
	decho ("クッキー:".$id);
}else{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: index.php");
	exit();
}


try{
  $pdo_h->beginTransaction();
	$sql = "delete from tr_log where id ='".$id."' and ymd = '".$_POST["k_ymd"]."' and jun = '".$_POST["k_jun"]."'";
	$stmt = $mysqli->query("LOCK TABLES tr_log WRITE");
	$stmt = $mysqli->prepare($sql);
	$stmt->execute();
	$stmt = $mysqli->query("UNLOCK TABLES");

}catch(Exception $e){
  $pdo_h->rollBack();
}
    //ログイン失敗
    //リダイレクト
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: index.php");

?>