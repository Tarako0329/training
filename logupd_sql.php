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
	if($_POST["k_ymd"] == $_POST["ymd"]){
		$jun=$_POST["k_jun"];
	}else{
		if($row_cnt==0){
			$jun=1;
		}else{
			$jun=$row["junban"]+1;
			//echo $row["junban"];
		}
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

	$sql = "update tr_log set ";
	$sql = $sql."shu = '".$shu."',";
	$sql = $sql."jun = '".$jun."',";
	$sql = $sql."weight = '".$_POST["weight"]."',";
	$sql = $sql."rep = '".$_POST["rep"]."',";
	$sql = $sql."rep2 = '".$rep2."',";
	$sql = $sql."sets = '".$_POST["sets"]."',";
	$sql = $sql."cal = '".$cal."',";
	$sql = $sql."ymd = '".$_POST["ymd"]."',";
	$sql = $sql."memo = '".$_POST["memo"]."' ";
	$sql = $sql."where id ='".$id."' and ymd = '".$_POST["k_ymd"]."' and jun = '".$_POST["k_jun"]."'";

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