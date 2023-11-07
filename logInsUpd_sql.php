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

if(trim($_POST["shu2"]) == ""){
	//種目追加欄が空白の場合はリストの種目
	$shu = $_POST["shu1"];
}else{
	//種目追加欄が記入されてる場合は種目追加欄の種目
	$shu = $_POST["shu2"];
}

try{
	if(empty($_POST["NO"])){
		$sql = "select max(jun) as junban from tr_log where ymd = ? and id = ?;";
		echo $sql."<BR>";
		var_dump($_POST);
		$result = $pdo_h->prepare($sql);
		$result->bindValue(1, $_POST["ymd"], PDO::PARAM_STR);
		$result->bindValue(2, $id, PDO::PARAM_STR);
		$result->execute();
		$row_cnt = $result->rowCount();
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		var_dump($row);
		$jun=1;
		if($row_cnt!==0){
			$jun=$row[0]["junban"]+1;
		}
		echo "No:".$row[0]["junban"];
	
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
			
		$sql = "insert into tr_log(id,shu,jun,weight,rep,tani,rep2,sets,cal,ymd,memo,typ) values(?,?,?,?,?,?,?,?,?,?,?,?)";
	
		$pdo_h->beginTransaction();
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue(1, $id, PDO::PARAM_STR);
		$stmt->bindValue(2, $shu, PDO::PARAM_STR);
		$stmt->bindValue(3, $jun, PDO::PARAM_INT);
		$stmt->bindValue(4, $_POST["weight"], PDO::PARAM_INT);
		$stmt->bindValue(5, $_POST["rep"], PDO::PARAM_INT);
		$stmt->bindValue(6, $_POST["tani"], PDO::PARAM_STR);
		$stmt->bindValue(7, $rep2, PDO::PARAM_STR);
		$stmt->bindValue(8, $_POST["sets"], PDO::PARAM_INT);
		$stmt->bindValue(9, $cal, PDO::PARAM_INT);
		$stmt->bindValue(10, $_POST["ymd"], PDO::PARAM_STR);
		$stmt->bindValue(11, $_POST["memo"], PDO::PARAM_STR);
		$stmt->bindValue(12, $_POST["typ"], PDO::PARAM_STR);
		$stmt->execute();
		$pdo_h->commit();
	}else{
		$sql = "select max(jun) as junban from tr_log where  ymd = ? and id = ?;";
	
		//echo $sql;
		$result = $pdo_h->prepare($sql);
		$result->bindValue(1, $_POST["ymd"], PDO::PARAM_STR);
		$result->bindValue(2, $id, PDO::PARAM_STR);
		$result->execute();
		$row_cnt = $result->rowCount();
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		var_dump($row);
		if($_POST["motoYMD"] == $_POST["ymd"]){//日付の変更がない場合は元の順番で更新
			$jun=$_POST["NO"];
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
		$sql = $sql."shu = ?,";
		$sql = $sql."jun = ?,";
		$sql = $sql."weight = ?,";
		$sql = $sql."rep = ?,";
		$sql = $sql."rep2 = ?,";
		$sql = $sql."sets = ?,";
		$sql = $sql."cal = ?,";
		$sql = $sql."ymd = ?,";
		$sql = $sql."memo = ? ";
		$sql = $sql."where id =? and ymd = ? and jun = ?";
	
		//echo $sql;
	
		$pdo_h->beginTransaction();
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue(1, $shu, PDO::PARAM_STR);
		$stmt->bindValue(2, $jun, PDO::PARAM_INT);
		$stmt->bindValue(3, $_POST["weight"], PDO::PARAM_INT);
		$stmt->bindValue(4, $_POST["rep"], PDO::PARAM_INT);
		$stmt->bindValue(5, $rep2, PDO::PARAM_INT);
		$stmt->bindValue(6, $_POST["sets"], PDO::PARAM_INT);
		$stmt->bindValue(7, $cal, PDO::PARAM_INT);
		$stmt->bindValue(8, $_POST["ymd"], PDO::PARAM_STR);
		$stmt->bindValue(9, $_POST["memo"], PDO::PARAM_STR);
		$stmt->bindValue(10, $id, PDO::PARAM_STR);
		$stmt->bindValue(11, $_POST["motoYMD"], PDO::PARAM_STR);
		$stmt->bindValue(12, $jun, PDO::PARAM_INT);
		$stmt->execute();
		$pdo_h->commit();

	}
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: TOP.php?msg=success");
	exit();

}catch(Exception $e){
	echo $e;
  $pdo_h->rollBack();
	//header("HTTP/1.1 301 Moved Permanently");
	//header("Location: TOP.php?msg=error:".$e);
	exit();
}
    //ログイン失敗
    //リダイレクト

?>