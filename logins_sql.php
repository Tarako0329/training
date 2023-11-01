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
	$sql = "select max(jun) as junban from tr_log where ymd = '".$_POST["ymd"]."';";
	//echo $sql;

	$result = $mysqli->query( $sql );
	$row_cnt = $result->num_rows;
	$row = $result->fetch_assoc(); 
	if($row_cnt==0){
		$jun=1;
	}else{
		$jun=$row["junban"]+1;
		//echo $row["junban"];
	}

	if($_POST["rep2"] == ""){
		$rep2 = 0;
	}else{
		$rep2 = $_POST["rep2"];
	}
	if($_POST["cal"] == ""){
		$cal = 0;
	}else{
		$cal = $_POST["cal"];
	}

	

	$sql = "insert into tr_log values('";
	$sql = $sql.$id."','";
	$sql = $sql.$shu."','";
	$sql = $sql.$jun."','";
	$sql = $sql.$_POST["weight"]."','";
	$sql = $sql.$_POST["rep"]."','";
	$sql = $sql.$_POST["tani"]."','";
	$sql = $sql.$rep2."','";
	$sql = $sql.$_POST["sets"]."','";
	$sql = $sql.$cal."','";
	$sql = $sql.$_POST["ymd"]."','";
	$sql = $sql.$_POST["memo"]."','";
	$sql = $sql.$_POST["typ"]."')";

	//echo $sql;

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