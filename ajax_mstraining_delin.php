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


try{
	if(!empty($_POST["data"])){
		//デリイン
		$pdo_h->beginTransaction();
		$sql = "delete from ms_training where id = :id ";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue(1, $id, PDO::PARAM_STR);
		$stmt->execute();

		$sql = "insert into ms_training(id,shu,sort,display_hide1,mokuhyou_type,mokuhyou) values(:id,:shu,:sort,:display_hide1,:mokuhyou_type,:mokuhyou)";
		foreach(json_decode($_POST["data"],true) as $row){
			$display_hide1 = ($row["display_hide1"]===true)?"true":"false";
			$stmt = $pdo_h->prepare($sql);
			$stmt->bindValue('id', $id, PDO::PARAM_STR);
			$stmt->bindValue('shu', $row["shu"], PDO::PARAM_STR);
			$stmt->bindValue('sort', $row["sort"], PDO::PARAM_STR);
			$stmt->bindValue('display_hide1', $display_hide1, PDO::PARAM_STR);
			$stmt->bindValue('mokuhyou_type', $row["mokuhyou_type"], PDO::PARAM_STR);
			$stmt->bindValue('mokuhyou', $row["mokuhyou"], PDO::PARAM_INT);
			$stmt->execute();
		}
		$msg = "success";
	}else{
		$msg = "ノーデータ";
	}

	$pdo_h->commit();

	$return_sts = array(
		"MSG" => "success"
		,"status" => "success"
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

	exit();

}catch(Exception $e){
	$msg = "catch Exception \$e：".$e." [SQL = ".$sql." ]";
  $pdo_h->rollBack();
	$return_sts = array(
		"MSG" => $msg
		,"status" => "error"
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

	exit();
}
?>