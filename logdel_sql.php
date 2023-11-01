<?php
require "config.php";
//require "functions.php";
//トランザクション処理

//結果書き込み

if(trim($_POST["shu2"]) == ""){
	//種目追加欄が空白の場合はリストの種目
	$shu = $_POST["shu1"];
}else{
	//種目追加欄が記入されてる場合は種目追加欄の種目
	$shu = $_POST["shu2"];
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