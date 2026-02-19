<?php
require_once "config.php";
require_once "database.php";
$db = new Database();

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
$shu = $_POST["shu"];
$mokuhyou_type = $_POST["mokuhyou_type"];
$mokuhyou = $_POST["mokuhyou"];

try{
	//$pdo_h->beginTransaction();
	//種目マスタ追加
	$db->begin_tran();
	$sql = 'UPDATE ms_training set mokuhyou_type = :mokuhyou_type,mokuhyou=:mokuhyou where id = :id and shu = :shu';
	$db->UP_DEL_EXEC($sql,[":mokuhyou_type" => $mokuhyou_type,":mokuhyou" => $mokuhyou,":id" => $id,":shu" => $shu]);
	$db->commit_tran();
	/*
	$stmt = $pdo_h->prepare($sql);
	$stmt->bindValue("mokuhyou_type", $mokuhyou_type, PDO::PARAM_STR);
	$stmt->bindValue("mokuhyou", $mokuhyou, PDO::PARAM_INT);
	$stmt->bindValue("id", $id, PDO::PARAM_STR);
	$stmt->bindValue("shu", $shu, PDO::PARAM_STR);
	$stmt->execute();

	$pdo_h->commit();
	*/
	$return_sts = array(
		"MSG" => "success"
		,"status" => "success"
	);
	/*
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

	exit();
	*/
}catch(Exception $e){
	$msg = "catch Exception \$e：".$e;
	$db->rollback_tran($msg);
	$return_sts = array(
		"MSG" => $msg
		,"status" => "error"
	);
	/*
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

	exit();
	*/
}

header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
