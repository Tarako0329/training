<?php
declare(strict_types=1);
require_once "config.php";

//トランザクション処理
log_writer2("\$POST",$_POST,"lv3");

//結果書き込み
if(isset($_SESSION['USER_ID'])){
	$id = $_SESSION['USER_ID'];
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
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
$status = "success";

if(U::exist($_POST["data"])){
	try{
		$db->begin_tran();
		$sql = "DELETE from ms_training where id = :id ";
		$db->UP_DEL_EXEC($sql,[":id" => $id]);

		foreach(json_decode($_POST["data"],true) as $row){
			$db->INSERT("ms_training",[
				"id" => $id,
				"shu" => $row["shu"],
				"sort" => $row["sort"],
				"display_hide1" => ($row["display_hide1"]===true)?"true":"false",
				"mokuhyou_type" => $row["mokuhyou_type"],
				"mokuhyou" => $row["mokuhyou"]
			]);
		}
		$db->commit_tran();
		$msg = "success";
	}catch(Exception $e){
		$msg = "catch Exception \$e：".$e;
		$db->rollback_tran($msg);
		$status = "error";
	}
}else{
	$msg = "ノーデータ";
}

$return_sts = array(
	"MSG" => $msg
	,"status" => $status
);

header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
exit();
?>