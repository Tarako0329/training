<?php
require_once "config.php";

//トランザクション処理
log_writer2("\$POST",$_POST,"lv3");

//結果書き込み
if(isset($_SESSION['USER_ID'])){
	$id = $_SESSION['USER_ID'];
}else if (check_auto_login($_COOKIE['token'])===true) {
	$id = $_SESSION['USER_ID'];
}else{
	$return_sts = array(
		"MSG" => "UserIDが取得できませんでした"
		,"status" => "error"
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
	exit();
}

//トレーニング種目別の目標をマスタにセット
$shu = $_POST["shu"];
$mokuhyou_type = $_POST["mokuhyou_type"];
$mokuhyou = $_POST["mokuhyou"];

try{
	$db->begin_tran();
	$sql = 'UPDATE ms_training set mokuhyou_type = :mokuhyou_type,mokuhyou=:mokuhyou where id = :id and shu = :shu';
	$db->UP_DEL_EXEC($sql,[":mokuhyou_type" => $mokuhyou_type,":mokuhyou" => $mokuhyou,":id" => $id,":shu" => $shu]);
	$db->commit_tran();
	$return_sts = array(
		"MSG" => "success"
		,"status" => "success"
	);
}catch(\Throwable $e){
	U::send_E($e,"種目マスタの目標更新に失敗", "種目マスタの目標更新に失敗しました。");
	$msg = "catch Exception \$e：".$e->getMessage();
	$db->rollback_tran($e->getMessage());
	$return_sts = array(
		"MSG" => $msg
		,"status" => "error"
	);
}

header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
