<?php
require "config.php";
//トランザクション処理
log_writer2("\$POST",$_POST,"lv3");

//結果書き込み
if(isset($_SESSION['USER_ID'])){
	$id = $_SESSION['USER_ID'];
	decho ("session:".$id);
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
	decho ("クッキー:".$id);
}else{
	$return_sts = array(
		"MSG" => "UserIDが取得できませんでした"
		,"status" => "error"
		//,"filter" => $shu
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
	exit();
}

//種目追加欄が空白の場合はリストの種目,種目追加欄が記入されてる場合は種目追加欄の種目
$shu = ($_POST["shu2"] == "")? $_POST["shu1"]:$_POST["shu2"];
$rep2 = ($_POST["rep2"] == "")? 0:$_POST["rep2"];
$cal = ($_POST["cal"] == "")?0:$_POST["cal"];
$type = (!empty($_POST["jiju"]))?"2":$_POST["typ"];

try{
	if(empty($_POST["NO"])){
		$sql = "select max(jun) as junban from tr_log where ymd = ? and id = ?;";

		$result = $pdo_h->prepare($sql);
		$result->bindValue(1, $_POST["ymd"], PDO::PARAM_STR);
		$result->bindValue(2, $id, PDO::PARAM_STR);
		$result->execute();
		$row_cnt = $result->rowCount();
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		
		$jun=1;
		if($row_cnt!==0){
			$jun=$row[0]["junban"]+1;
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
		$stmt->bindValue(12, $type, PDO::PARAM_STR);
		$stmt->execute();
	}else{
		$sql = "select max(jun) as junban from tr_log where  ymd = ? and id = ?;";
	
		$result = $pdo_h->prepare($sql);
		$result->bindValue(1, $_POST["ymd"], PDO::PARAM_STR);
		$result->bindValue(2, $id, PDO::PARAM_STR);
		$result->execute();
		$row_cnt = $result->rowCount();
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		
		if($_POST["motoYMD"] == $_POST["ymd"]){//日付の変更がない場合は元の順番で更新
			$jun=$_POST["NO"];
		}else{
			$jun=($row_cnt==0)?1:$jun=$row["junban"]+1;
		}
	
		$sql = "update tr_log set ";
		$sql = $sql."shu = :shu,";
		$sql = $sql."jun = :jun,";
		$sql = $sql."weight = :weight,";
		$sql = $sql."rep = :rep,";
		$sql = $sql."rep2 = :rep2,";
		$sql = $sql."sets = :sets,";
		$sql = $sql."tani = :tani,";
		$sql = $sql."cal = :cal,";
		$sql = $sql."ymd = :ymd,";
		$sql = $sql."typ = :typ,";
		$sql = $sql."memo = :memo ";
		$sql = $sql."where id =:id and ymd = :motoYMD and jun = :NO";
	
		$pdo_h->beginTransaction();
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("shu", $shu, PDO::PARAM_STR);
		$stmt->bindValue("jun", $jun, PDO::PARAM_INT);
		$stmt->bindValue("weight", $_POST["weight"], PDO::PARAM_INT);
		$stmt->bindValue("rep", $_POST["rep"], PDO::PARAM_INT);
		$stmt->bindValue("rep2", $rep2, PDO::PARAM_INT);
		$stmt->bindValue("sets", $_POST["sets"], PDO::PARAM_INT);
		$stmt->bindValue("tani", $_POST["tani"], PDO::PARAM_INT);
		$stmt->bindValue("cal", $cal, PDO::PARAM_INT);
		$stmt->bindValue("ymd", $_POST["ymd"], PDO::PARAM_STR);
		$stmt->bindValue("typ", $type, PDO::PARAM_INT);
		$stmt->bindValue("memo", $_POST["memo"], PDO::PARAM_STR);
		$stmt->bindValue("id", $id, PDO::PARAM_STR);
		$stmt->bindValue("motoYMD", $_POST["motoYMD"], PDO::PARAM_STR);
		$stmt->bindValue("NO", $_POST["NO"], PDO::PARAM_INT);
		$stmt->execute();

	}

	if(!empty($_POST["condition"])){
		//デリイン
		$sql = "delete from tr_condition where id = ? and ymd = ?";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue(1, $id, PDO::PARAM_STR);
		$stmt->bindValue(2, $_POST["ymd"], PDO::PARAM_STR);
		$stmt->execute();

		$sql = "insert into tr_condition values(?,?,?)";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue(1, $id, PDO::PARAM_STR);
		$stmt->bindValue(2, $_POST["ymd"], PDO::PARAM_STR);
		$stmt->bindValue(3, $_POST["condition"], PDO::PARAM_STR);
		$stmt->execute();
	}


	//$pdo_h->commit();

	//種目マスタ追加
	$sql = "select shu from ms_training where id = :id and shu = :shu";
	
	$result = $pdo_h->prepare($sql);
	$result->bindValue("id", $id, PDO::PARAM_STR);
	$result->bindValue("shu", $shu, PDO::PARAM_STR);
	$result->execute();
	$row = $result->fetchAll(PDO::FETCH_ASSOC);

	if($row[0]["shu"]==$shu){
		//skip
	}else{
		$sql = "select max(sort)+1 as next from ms_training where id = :id and sort < 100 group by id;";
	
		$result = $pdo_h->prepare($sql);
		$result->bindValue("id", $id, PDO::PARAM_STR);
		$result->execute();
		$row_cnt = $result->rowCount();
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
	
		if($row_cnt==0){
			$next = 1;
		}else{
			$next = $row[0]["next"];
		}
	
	
		$sql = 'INSERT INTO ms_training(id,shu,sort) VALUES(:id,:shu,'.$next.')';
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue(1, $id, PDO::PARAM_STR);
		$stmt->bindValue(2, $shu, PDO::PARAM_STR);
		$stmt->execute();
	}


	$pdo_h->commit();
	
	$return_sts = array(
		"MSG" => "success"
		,"status" => "success"
		,"filter" => $shu
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

	exit();

}catch(Exception $e){
	$msg = "catch Exception \$e：".$e." [SQL = ".$sql." ]";
  $pdo_h->rollBack();
	log_writer2("\$e",$e,"lv1");
	$return_sts = array(
		"MSG" => $msg
		,"status" => "error"
		//,"filter" => $shu
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

	exit();
}
?>